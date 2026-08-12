<?php

namespace FluentForm\App\Modules\MCP\Support;

defined('ABSPATH') || exit;

/**
 * Safety rails for mutating MCP tools. Annotations are UX hints, not safety —
 * this is where real protection lives.
 *
 * EVERY write routes through Mutation::runGuarded, not just the destructive
 * ones. The reason is prompt injection: entry field values are written by
 * anonymous members of the public and land in the agent's context (fenced by
 * MCPHelper::untrusted(), but a fence is only a hint). The dry-run ->
 * confirm_token round-trip is the one mechanism here that forces a second pass
 * through the operator before anything is written, so an instruction smuggled
 * into a form submission cannot complete a write on its own.
 *
 * Two mechanisms:
 *  1. Dry-run + confirmation token bound to the entity's CURRENT state, so an
 *     agent can never act on stale data (the fingerprint must still match at
 *     execute time, else a fresh preview is forced).
 *  2. Idempotency keys, so a retried mutation returns the cached result instead
 *     of running twice.
 *
 * CONTRACT: every ability that writes MUST route through Mutation::runGuarded
 * (which calls confirm() before mutating) and merge schemaProps() into its
 * input schema — here or in Pro via fluentform/mcp_loaded. A tool whose
 * annotations lack `readonly` and which does not do this is a bug; the test
 * suite asserts the pairing.
 */
class WriteGuard
{
    const CONFIRM_TTL = 300;

    const IDEM_TTL = 86400;

    /**
     * How long one execution may hold its claim before another request may
     * reclaim it.
     *
     * This is a LEASE, and the trade-off is deliberate: without an expiry a
     * request killed mid-write (OOM, timeout, fatal) would block its entity
     * forever. The cost is that a write which genuinely outlives the lease can
     * be taken over while still running, so at-most-once degrades to
     * at-least-once in that window. Sized well above any write here (the
     * slowest is a full form save) and above the usual PHP max_execution_time,
     * so outliving it means the request is almost certainly already dead — and
     * Mutation::runGuarded records lease_expired_mid_write in the audit when it
     * happens, so the case is visible rather than silent.
     */
    const CLAIM_TTL = 120;

    /** Shared prefix so claimKey() and the sweep can never drift apart. */
    const CLAIM_PREFIX = 'ff_mcp_claim_';

    /** Marks an idempotency record whose mutation started but never reported. */
    const IN_FLIGHT = '__ff_mcp_in_flight';

    /**
     * The three params every guarded write shares, so the confirmation contract
     * is declared once instead of copy-pasted into each tool's input_schema.
     * Merge into a definition's `properties`.
     */
    public static function schemaProps()
    {
        return [
            'dry_run'         => ['type' => 'boolean', 'description' => 'Preview the change without writing; returns a confirm_token.'],
            'confirm_token'   => ['type' => 'string', 'description' => 'The token from the dry_run preview, required to execute.'],
            'idempotency_key' => ['type' => 'string', 'description' => 'Optional; a retry with the same key will not act twice.'],
        ];
    }

    public static function preview($tool, $entityKey, $fingerprint, array $preview)
    {
        $token = substr(wp_hash($tool . '|' . $entityKey . '|' . $fingerprint . '|' . wp_generate_uuid4()), 0, 32);

        set_transient(self::confirmKey($tool, $entityKey), [
            'token'       => $token,
            'fingerprint' => $fingerprint,
        ], self::CONFIRM_TTL);

        return [
            'dry_run'            => true,
            'preview'            => $preview,
            'confirm_token'      => $token,
            'expires_in_seconds' => self::CONFIRM_TTL,
            'next_step'          => 'Call this tool again with the same parameters plus confirm_token (and an idempotency_key) to execute.',
        ];
    }

    /**
     * Validate a confirm_token against the entity's current fingerprint.
     *
     * @return true|\WP_Error
     */
    public static function confirm($tool, $entityKey, $currentFingerprint, $token)
    {
        if (empty($token)) {
            return MCPHelper::error(
                ErrorCodes::CONFIRMATION_REQUIRED,
                __('This action changes data. Call again with dry_run:true to preview, then pass the returned confirm_token to execute.', 'fluentform'),
                ['next_step' => 'set dry_run:true']
            );
        }

        $stored = get_transient(self::confirmKey($tool, $entityKey));

        if (!is_array($stored) || empty($stored['token'])) {
            return MCPHelper::error(
                ErrorCodes::CONFIRMATION_EXPIRED,
                __('Your confirmation has expired. Run a fresh dry_run to preview and get a new confirm_token.', 'fluentform'),
                ['next_step' => 'set dry_run:true']
            );
        }

        if (!hash_equals((string) $stored['token'], (string) $token)) {
            return MCPHelper::error(
                ErrorCodes::CONFIRMATION_INVALID,
                __('The confirm_token does not match. Run a fresh dry_run.', 'fluentform'),
                ['next_step' => 'set dry_run:true']
            );
        }

        if ((string) $stored['fingerprint'] !== (string) $currentFingerprint) {
            delete_transient(self::confirmKey($tool, $entityKey));
            return MCPHelper::error(
                ErrorCodes::STATE_CHANGED,
                __('The record changed since you previewed it. Run a fresh dry_run to see the current state before executing.', 'fluentform'),
                ['next_step' => 'set dry_run:true']
            );
        }

        delete_transient(self::confirmKey($tool, $entityKey));

        return true;
    }

    /**
     * Cached result of an earlier execution with the same idempotency key, or
     * null. Checked BEFORE confirm-token validation (tokens are single-use, so
     * a lost-response retry only ever has a consumed token) and before entity
     * resolution (the entity may no longer exist after a destructive write).
     */
    public static function replay($tool, $entityKey, $key)
    {
        if (empty($key)) {
            return null;
        }

        $cached = get_transient(self::idemKey($tool, $entityKey, $key));
        if (false === $cached) {
            return null;
        }

        // An attempt was recorded but never completed — see idempotent().
        if (is_array($cached) && !empty($cached[self::IN_FLIGHT])) {
            return MCPHelper::error(
                ErrorCodes::EXECUTION_UNKNOWN,
                __('An earlier attempt with this idempotency_key started but its outcome was never recorded, so it may or may not have completed. Check the current state before acting: if the change is already there, nothing more is needed; if it is not, retry with a NEW idempotency_key.', 'fluentform'),
                ['retryable' => false, 'next_step' => 'verify the current state, then retry with a new idempotency_key']
            );
        }

        return is_array($cached) ? array_merge($cached, ['idempotent_replay' => true]) : $cached;
    }

    /**
     * Run a mutation at most once per idempotency key.
     *
     * The claim in Mutation::runGuarded stops two callers running at the same
     * time. This closes the other half: a crash BETWEEN the durable write and
     * the recording of its result. Writing the result afterwards is not enough —
     * if the process dies in that window the retry finds no record and creates a
     * second form.
     *
     * So the attempt is recorded first and replaced by the result on success. A
     * retry that finds only the marker is told the outcome is UNKNOWN rather
     * than being allowed to duplicate the write. A mutation that fails cleanly
     * wrote nothing, so its marker is cleared and the key stays usable; one that
     * throws keeps the marker, because a partial write cannot be ruled out.
     */
    public static function idempotent($tool, $entityKey, $key, callable $fn)
    {
        if (empty($key)) {
            return $fn();
        }

        $replay = self::replay($tool, $entityKey, $key);
        if (null !== $replay) {
            return $replay;
        }

        $cacheKey = self::idemKey($tool, $entityKey, $key);
        set_transient($cacheKey, [self::IN_FLIGHT => true, 'started_at' => time()], self::IDEM_TTL);

        $result = $fn();

        if (is_wp_error($result)) {
            delete_transient($cacheKey);
        } else {
            set_transient($cacheKey, $result, self::IDEM_TTL);
        }

        return $result;
    }

    /**
     * Take the exclusive right to execute this (tool, entity) once, or fail.
     *
     * Neither confirm() nor idempotent() is atomic on its own: both are
     * read-check-write against transients, so two genuinely concurrent retries
     * carrying the same confirm_token and idempotency_key could both pass every
     * check and both run the mutation. For a repeat delete that is harmless; for
     * create-form or a notification create it is a duplicate durable record,
     * which is exactly what idempotency_key promises will not happen.
     *
     * Atomicity comes from the UNIQUE index on wp_options.option_name: INSERT
     * IGNORE inserts for exactly one caller and reports zero affected rows for
     * every other. Deliberately NOT get_transient/set_transient (same
     * read-then-write race we are closing) and not wp_cache_add (a no-op across
     * requests without a persistent object cache).
     *
     * The row stores its own expiry so a request that dies mid-write cannot
     * deadlock the key: a claim older than CLAIM_TTL is reclaimed.
     *
     * @return bool True when this caller may proceed.
     */
    public static function claim($tool, $entityKey)
    {
        global $wpdb;

        if (!isset($wpdb) || !is_object($wpdb)) {
            return 'no-db'; // Nothing to serialize on; behave as before.
        }

        $key = self::claimKey($tool, $entityKey);

        // The receipt identifies THIS claimant. Without it, reclaim and release
        // can only address the key, and two holders become indistinguishable.
        $receipt = self::receipt();

        if ($wpdb->query($wpdb->prepare(
            "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'no')",
            $key,
            $receipt
        ))) {
            return $receipt;
        }

        // Held. Take it over only if the holder's lease has expired, and only
        // by compare-and-swapping against the exact value we just observed: one
        // UPDATE, so of two reclaimers seeing the same expired value only the
        // first matches the predicate and the second affects zero rows.
        //
        // The previous delete-then-insert was NOT this: each reclaimer deleted
        // the row the other had just inserted, and both reported success.
        $observed = $wpdb->get_var($wpdb->prepare(
            "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
            $key
        ));

        if (null !== $observed && self::expiryOf($observed) <= time()) {
            $won = $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value = %s",
                $receipt,
                $key,
                $observed
            ));

            if ($won) {
                // We now own the contested key, so sweeping other stranded rows
                // cannot interfere with it. Done here as well as on the lost
                // path so cleanup does not wait for genuine contention, while
                // staying off the uncontended fast path above.
                self::sweepExpiredClaims($key);

                return $receipt;
            }
        }

        // The row may have been released or swept between our two statements,
        // in which case the CAS matched nothing but nobody actually holds it.
        // One more insert distinguishes that from a genuine loss.
        if ($wpdb->query($wpdb->prepare(
            "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'no')",
            $key,
            $receipt
        ))) {
            return $receipt;
        }

        // Genuinely lost. This is the rare path, so it is the cheap place to
        // hang the sweep — and it runs only after our own claim is resolved, so
        // it can never interfere with the key being contested.
        self::sweepExpiredClaims($key);

        return false;
    }

    /**
     * Release a claim, but only if we still own it.
     *
     * Conditioned on the receipt, not just the key: a holder whose lease expired
     * mid-write and whose key was legitimately taken over would otherwise delete
     * the REPLACEMENT owner's live claim on its way out, dropping a second
     * request straight into the critical section.
     *
     * @return bool False when we no longer owned the claim — meaning our lease
     *              expired mid-write and another request may have run too.
     */
    public static function release($tool, $entityKey, $receipt)
    {
        global $wpdb;

        // No DB means claim() never serialized anything, so there is no lease to
        // have lost — report success rather than a spurious lease_expired audit.
        if (!isset($wpdb) || !is_object($wpdb)) {
            return true;
        }

        if (empty($receipt)) {
            return false;
        }

        return (bool) $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s",
            self::claimKey($tool, $entityKey),
            $receipt
        ));
    }

    /** A value unique to one claimant: random token plus this lease's expiry. */
    private static function receipt()
    {
        return wp_generate_uuid4() . '|' . (time() + self::CLAIM_TTL);
    }

    private static function expiryOf($receipt)
    {
        $pos = strrpos((string) $receipt, '|');

        return false === $pos ? 0 : (int) substr((string) $receipt, $pos + 1);
    }

    /**
     * Drop claim rows stranded by a fatal mid-write, never touching the key the
     * caller is contesting. Bounded per call, and non-autoloaded rows so it
     * never costs a page load.
     */
    private static function sweepExpiredClaims($exceptKey)
    {
        global $wpdb;

        // The LIKE pattern is an argument, not inlined: a literal % inside a
        // prepare() query is a placeholder to WordPress and must be written %%
        // or passed in. esc_like escapes the underscores in the prefix too.
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options}
              WHERE option_name LIKE %s
                AND option_name != %s
                AND CAST(SUBSTRING_INDEX(option_value, '|', -1) AS UNSIGNED) < %d
              LIMIT 50",
            $wpdb->esc_like(self::CLAIM_PREFIX) . '%',
            $exceptKey,
            time()
        ));
    }

    private static function claimKey($tool, $entityKey)
    {
        // Not user-scoped: two sessions of the same operator, or two workers
        // handling one agent's retry, must contend for the same claim.
        return self::CLAIM_PREFIX . md5($tool . '|' . $entityKey);
    }

    private static function confirmKey($tool, $entityKey)
    {
        return 'ff_mcp_confirm_' . get_current_user_id() . '_' . md5($tool . '|' . $entityKey);
    }

    private static function idemKey($tool, $entityKey, $key)
    {
        return 'ff_mcp_idem_' . get_current_user_id() . '_' . md5($tool . '|' . $entityKey . '|' . $key);
    }
}
