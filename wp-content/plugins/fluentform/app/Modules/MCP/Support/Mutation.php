<?php

namespace FluentForm\App\Modules\MCP\Support;

defined('ABSPATH') || exit;

/**
 * The audit + write path for MCP tools. Every mutation an agent performs, and
 * every read of entry data, goes through here so these concerns live in one
 * place:
 *
 *   - Accountability: one audit record per write (and per entry read, via
 *     auditRead), into FluentForm's own
 *     fluentform_logs (component "MCP"), so it shows up in Tools → Logs next to
 *     every other form event — who (wp user), what tool, redacted params,
 *     success/failure — without a separate log file to find.
 *   - Safety for destructive writes: runGuarded() routes through WriteGuard's
 *     dry-run → confirm_token → idempotency flow before mutating.
 *
 * Reversible writes (status change, note, create) use run(): execute, then
 * audit. Destructive writes (permanent delete, bulk) use runGuarded().
 */
class Mutation
{
    const AUDIT_COMPONENT = 'MCP';

    /**
     * Longest string kept in an audit row. Params are logged verbatim, so a note
     * body, an email template, or a CSS blob would otherwise be copied whole
     * into fluentform_logs on every call.
     */
    const MAX_LOGGED_VALUE = 200;

    /**
     * Run a reversible mutation and audit the outcome.
     *
     * @param string         $tool   Ability name.
     * @param array          $params Raw tool params (redacted before logging).
     * @param callable       $apply  Performs the mutation; returns an envelope array or WP_Error.
     * @param array|callable $target ['form_id'=>, 'entry_id'=>] for log linkage, or a
     *                               callable($result) that derives it (e.g. create-form,
     *                               whose form id only exists after $apply runs).
     * @return array|\WP_Error
     */
    public static function run($tool, array $params, callable $apply, $target = [])
    {
        $result = $apply();
        self::audit($tool, self::resolveTarget($target, $result), $params, $result);

        return $result;
    }

    /**
     * Run a destructive mutation behind WriteGuard. The tool must expose dry_run
     * and confirm_token in its schema. First call with dry_run:true returns a
     * preview + confirm_token bound to $fingerprint; the second call (same params
     * + confirm_token) executes once and audits.
     *
     * @param string   $tool        Ability name.
     * @param array    $params      Raw tool params (dry_run, confirm_token, idempotency_key).
     * @param string   $entityKey   Stable id of the target, e.g. "submission:42".
     * @param string   $fingerprint State string that must still match at execute time.
     * @param callable $preview     Returns the preview payload (array).
     * @param callable $apply       Performs the mutation; returns an envelope array or WP_Error.
     * @param array|callable $target Audit linkage (see run()).
     * @return array|\WP_Error
     */
    public static function runGuarded($tool, array $params, $entityKey, $fingerprint, callable $preview, callable $apply, $target = [])
    {
        if (!empty($params['dry_run'])) {
            return WriteGuard::preview($tool, $entityKey, $fingerprint, $preview());
        }

        $idemKey = isset($params['idempotency_key']) ? $params['idempotency_key'] : '';

        // Replay before confirm: tokens are single-use, so a lost-response retry
        // arrives with a consumed token and would otherwise die as "expired".
        $replay = WriteGuard::replay($tool, $entityKey, $idemKey);
        if (null !== $replay) {
            return $replay;
        }

        // Everything from here down — consuming the single-use confirm token,
        // checking the idempotency cache, and running the mutation — has to be
        // one critical section. Each step is individually read-then-write, so
        // without this two concurrent retries carrying the same token and key
        // both pass every check and both execute.
        $receipt = WriteGuard::claim($tool, $entityKey);
        if (!$receipt) {
            // The winner may have finished between our replay check and here.
            $replay = WriteGuard::replay($tool, $entityKey, $idemKey);
            if (null !== $replay) {
                return $replay;
            }

            return MCPHelper::error(
                ErrorCodes::CONCURRENT_REQUEST,
                __('An identical write is already in progress. Wait for it to finish, then retry with the same idempotency_key to collect its result.', 'fluentform'),
                ['retryable' => true, 'next_step' => 'retry with the same idempotency_key']
            );
        }

        try {
            $token   = isset($params['confirm_token']) ? $params['confirm_token'] : '';
            $confirm = WriteGuard::confirm($tool, $entityKey, $fingerprint, $token);
            if (is_wp_error($confirm)) {
                return $confirm;
            }

            $result = WriteGuard::idempotent($tool, $entityKey, $idemKey, $apply);

            // release() reports whether we still owned the claim. False means
            // the lease expired while $apply was running and someone else may
            // have taken over and run too — a lease is the price of not
            // deadlocking on a crashed request, so record it rather than
            // pretend at-most-once held.
            $held = WriteGuard::release($tool, $entityKey, $receipt);
            $released = true;

            self::audit(
                $tool,
                self::resolveTarget($target, $result),
                $params,
                $result,
                $held ? [] : ['lease_expired_mid_write' => true]
            );

            return $result;
        } finally {
            // finally, not a trailing call: a throw from $apply must not strand
            // the claim for CLAIM_TTL. AbilitiesRegistrar catches the throwable
            // above us, so without this the key would look held to every retry.
            if (empty($released)) {
                WriteGuard::release($tool, $entityKey, $receipt);
            }
        }
    }

    /**
     * Record that an agent READ entry data.
     *
     * Writes were audited from the start, but the headline capability here is
     * "an AI can read your entries" — and reading is how personal data actually
     * leaves. Without this, an agent could page through every submission on the
     * site and leave no trace anywhere.
     *
     * Deliberately cheap and payload-free: one row per call carrying the tool,
     * the actor and how many records were returned — never the records
     * themselves, which would copy the entries into the log table.
     *
     * @param string $tool   Ability name.
     * @param array  $target ['form_id' =>, 'entry_id' =>] for log linkage.
     * @param array  $detail Small scalars describing the read (e.g. count).
     */
    public static function auditRead($tool, array $target = [], array $detail = [])
    {
        /**
         * Filter whether MCP reads are logged. Busy sites that page through
         * entries constantly can turn this off, at the cost of losing the only
         * record of what an agent looked at.
         *
         * @since 6.2.5
         *
         * @param bool   $enabled Default true.
         * @param string $tool    Ability name.
         */
        if (!apply_filters('fluentform/mcp_audit_reads', true, $tool)) {
            return;
        }

        $payload = array_merge([
            'actor'   => 'mcp',
            'user_id' => get_current_user_id(),
            'tool'    => $tool,
            'result'  => 'read',
        ], $detail);

        try {
            do_action('fluentform/log_data', [
                'title'            => $tool,
                'status'           => 'info',
                'description'      => wp_json_encode($payload),
                'parent_source_id' => isset($target['form_id']) ? (int) $target['form_id'] : null,
                'source_id'        => isset($target['entry_id']) ? (int) $target['entry_id'] : null,
                'source_type'      => 'mcp',
                'component'        => self::AUDIT_COMPONENT,
            ]);
        } catch (\Throwable $e) {
            // Never let auditing failure surface to the agent.
            return;
        }
    }

    private static function resolveTarget($target, $result)
    {
        if (is_callable($target)) {
            $target = $target($result);
        }

        return is_array($target) ? $target : [];
    }

    /**
     * Write one audit row via FluentForm's log pipeline. Best-effort: a logging
     * failure must never break the tool call, so it's swallowed.
     */
    private static function audit($tool, array $target, array $params, $result, array $extra = [])
    {
        $isError = is_wp_error($result);

        $payload = [
            'actor'   => 'mcp',
            'user_id' => get_current_user_id(),
            'tool'    => $tool,
            'params'  => self::redact($params),
            'result'  => $isError ? 'error' : 'success',
        ];
        if ($isError) {
            $code                  = $result->get_error_code();
            $payload['error_code'] = $code ? $code : 'error';
        }

        $payload = array_merge($payload, $extra);

        try {
            do_action('fluentform/log_data', [
                'title'            => $tool,
                'status'           => $isError ? 'failed' : 'success',
                'description'      => wp_json_encode($payload),
                'parent_source_id' => isset($target['form_id']) ? (int) $target['form_id'] : null,
                'source_id'        => isset($target['entry_id']) ? (int) $target['entry_id'] : null,
                'source_type'      => 'mcp',
                'component'        => self::AUDIT_COMPONENT,
            ]);
        } catch (\Throwable $e) {
            // Never let auditing failure surface to the agent.
            return;
        }
    }

    /**
     * Recursively mask values whose key looks like a credential, so a token an
     * agent passes (or a tool echoes) never lands in the audit log in clear.
     */
    public static function redact($data, $depth = 0)
    {
        if ($depth > 6 || !is_array($data)) {
            return $data;
        }

        $sensitive = '/(pass(word)?|secret|token|api[_-]?key|authorization|bearer|nonce)/i';

        $out = [];
        foreach ($data as $key => $value) {
            if (is_string($key) && preg_match($sensitive, $key)) {
                $out[$key] = '[redacted]';
                continue;
            }
            if (is_array($value)) {
                $out[$key] = self::redact($value, $depth + 1);
                continue;
            }
            $out[$key] = self::truncate($value);
        }

        return $out;
    }

    /**
     * Trim an over-long scalar for the audit row, keeping enough to identify
     * what was written without copying the whole payload into the log.
     */
    private static function truncate($value)
    {
        if (!is_string($value) || mb_strlen($value) <= self::MAX_LOGGED_VALUE) {
            return $value;
        }

        // mb_substr, not substr: cutting a multibyte character in half yields
        // invalid UTF-8, and json_encode() then returns false — which would
        // silently drop the entire audit description rather than shortening it.
        return mb_substr($value, 0, self::MAX_LOGGED_VALUE) . '… [' . mb_strlen($value) . ' characters total]';
    }
}
