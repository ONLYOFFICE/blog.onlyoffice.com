<?php
/**
 * Per-language translation job state.
 *
 * One Action Scheduler task is queued per language, but the plugin used to keep
 * the state of every language in two shared post-meta rows —
 * _ai_translation_in_progress (flat list of codes) and _ai_translation_results
 * (map code => message). Every worker did a read-modify-write on both, so two
 * workers finishing at the same time lost each other's update:
 *
 *   worker fr: reads [fr,de] -> removes fr -> writes [de]
 *   worker de: reads [fr,de] -> removes de -> writes [fr]   (overwrites)
 *   result:    fr stays in _ai_translation_in_progress forever
 *
 * The metabox then showed a permanent "translating..." spinner with no checkbox
 * and no way to retry or cancel — the "translation hangs sometimes" reports.
 *
 * State is now one meta row per language (_oait_lang_<code>). A worker only
 * ever writes the row of the language it is translating, so the read-modify-
 * write races cannot happen and no locking is needed. Each row carries
 * timestamps, so a job that never reports back can be detected and released by
 * the stale sweep instead of hanging until someone edits the database.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OAIT_Job_State {

    /** Meta key prefix; the language code is appended (e.g. _oait_lang_zh-hans). */
    const META_PREFIX = '_oait_lang_';

    /** Pre-refactor meta keys, migrated on first read and then removed. */
    const LEGACY_IN_PROGRESS = '_ai_translation_in_progress';
    const LEGACY_RESULTS     = '_ai_translation_results';
    const MIGRATED_FLAG      = '_oait_state_migrated';

    const STATUS_IDLE      = 'idle';
    const STATUS_QUEUED    = 'queued';
    const STATUS_RUNNING   = 'running';
    const STATUS_SUCCESS   = 'success';
    const STATUS_ERROR     = 'error';
    const STATUS_CANCELLED = 'cancelled';

    /** Default minutes before a queued/running language is considered stuck. */
    const DEFAULT_STALE_MINUTES = 20;

    /** Posts whose legacy meta has already been checked this request. */
    private static $migrated = array();

    /**
     * Empty state for a language that has never been touched.
     *
     * @param string $lang WPML language code.
     * @return array
     */
    public static function defaults( $lang ) {
        return array(
            'lang'     => $lang,
            'status'   => self::STATUS_IDLE,
            'message'  => '',
            'post_id'  => 0,
            'attempts' => 0,
            'started'  => 0,
            'updated'  => 0,
            // Identifies the worker process that owns a 'running' row, so the
            // shutdown handler never releases a row owned by another worker.
            'run'      => '',
        );
    }

    /**
     * Read the state of a single language.
     *
     * @param int    $post_id Source post ID.
     * @param string $lang    WPML language code.
     * @return array
     */
    public static function get( $post_id, $lang ) {
        self::maybe_migrate( $post_id );

        $raw = get_post_meta( $post_id, self::META_PREFIX . $lang, true );
        if ( ! is_array( $raw ) ) {
            return self::defaults( $lang );
        }

        return array_merge( self::defaults( $lang ), $raw );
    }

    /**
     * Merge a patch into the state of a single language and persist it.
     *
     * @param int    $post_id Source post ID.
     * @param string $lang    WPML language code.
     * @param array  $patch   Fields to overwrite.
     * @return array The stored state.
     */
    public static function set( $post_id, $lang, array $patch ) {
        $state = array_merge( self::get( $post_id, $lang ), $patch );

        $state['lang']    = $lang;
        $state['updated'] = isset( $patch['updated'] ) ? (int) $patch['updated'] : time();

        update_post_meta( $post_id, self::META_PREFIX . $lang, $state );

        return $state;
    }

    /**
     * Persist a patch only while the language is still 'running' under the
     * given run token. Used by the worker so that a cancel issued mid-flight,
     * or a re-queue that took ownership of the language, is not overwritten by
     * a late write from the previous worker.
     *
     * @param int    $post_id   Source post ID.
     * @param string $lang      WPML language code.
     * @param string $run_token Token of the calling worker.
     * @param array  $patch     Fields to overwrite.
     * @return bool True if the patch was stored.
     */
    public static function set_if_running( $post_id, $lang, $run_token, array $patch ) {
        $state = self::get( $post_id, $lang );

        if ( self::STATUS_RUNNING !== $state['status'] || $state['run'] !== $run_token ) {
            return false;
        }

        self::set( $post_id, $lang, $patch );

        return true;
    }

    /**
     * Drop the stored state of a language.
     *
     * @param int    $post_id Source post ID.
     * @param string $lang    WPML language code.
     */
    public static function clear( $post_id, $lang ) {
        delete_post_meta( $post_id, self::META_PREFIX . $lang );
    }

    /**
     * Drop the stored state of every language (used when a translated post is
     * deleted and the source should offer a clean retry).
     *
     * @param int $post_id Source post ID.
     */
    public static function clear_all( $post_id ) {
        foreach ( array_keys( OAIT_Translator::LANGUAGES ) as $lang ) {
            self::clear( $post_id, $lang );
        }
    }

    /**
     * Read the state of every known language, after migrating legacy meta and
     * releasing stuck jobs.
     *
     * @param int $post_id Source post ID.
     * @return array Map of language code => state.
     */
    public static function all( $post_id ) {
        self::sweep_stale( $post_id );

        $states = array();
        foreach ( array_keys( OAIT_Translator::LANGUAGES ) as $lang ) {
            $states[ $lang ] = self::get( $post_id, $lang );
        }

        return $states;
    }

    /**
     * Is the language queued or running?
     *
     * @param array $state State array.
     * @return bool
     */
    public static function is_active( array $state ) {
        return in_array( $state['status'], array( self::STATUS_QUEUED, self::STATUS_RUNNING ), true );
    }

    /**
     * Language codes that are currently queued or running for a post.
     *
     * @param int $post_id Source post ID.
     * @return string[]
     */
    public static function active_languages( $post_id ) {
        $active = array();

        foreach ( self::all( $post_id ) as $lang => $state ) {
            if ( self::is_active( $state ) ) {
                $active[] = $lang;
            }
        }

        return $active;
    }

    /**
     * Seconds a queued/running language may go without an update before it is
     * treated as stuck.
     *
     * Must stay comfortably above the API request timeout: a worker cannot
     * refresh its row while it is blocked on the HTTP call, so a sweep that
     * fires sooner would release a job that is still working.
     *
     * @return int
     */
    public static function stale_timeout() {
        $minutes = self::clamp_stale_minutes( get_option( 'oait_stale_timeout', self::DEFAULT_STALE_MINUTES ) );
        $seconds = $minutes * MINUTE_IN_SECONDS;

        // Never sweep before the request itself could have finished.
        $floor = OAIT_Translator::get_request_timeout() + 120;
        if ( $seconds < $floor ) {
            $seconds = $floor;
        }

        return (int) apply_filters( 'oait_stale_timeout', $seconds, $minutes );
    }

    /** Lower bound for the stuck-job timeout, in minutes. */
    const MIN_STALE_MINUTES = 2;

    /** Upper bound for the stuck-job timeout, in minutes. */
    const MAX_STALE_MINUTES = 240;

    /**
     * Bring a stored or submitted stuck-job timeout into the supported range.
     * Applied on read as well as on save, matching
     * OAIT_Translator::clamp_request_timeout().
     *
     * @param mixed $minutes Raw value.
     * @return int Minutes.
     */
    public static function clamp_stale_minutes( $minutes ) {
        $minutes = (int) $minutes;

        if ( $minutes <= 0 ) {
            return self::DEFAULT_STALE_MINUTES;
        }

        return max( self::MIN_STALE_MINUTES, min( self::MAX_STALE_MINUTES, $minutes ) );
    }

    /**
     * Release languages that have been queued or running for longer than the
     * stale timeout.
     *
     * This is what makes the plugin recover on its own: a worker killed by OOM,
     * a pod restart, or an Action Scheduler task that was never picked up all
     * leave a row stuck at 'queued'/'running'. Flipping it to 'error' makes the
     * metabox render a checkbox again so the language can be re-queued.
     *
     * @param int $post_id Source post ID.
     * @return string[] Language codes that were released.
     */
    public static function sweep_stale( $post_id ) {
        self::maybe_migrate( $post_id );

        $timeout  = self::stale_timeout();
        $now      = time();
        $released = array();

        foreach ( array_keys( OAIT_Translator::LANGUAGES ) as $lang ) {
            $state = self::get( $post_id, $lang );

            if ( ! self::is_active( $state ) ) {
                continue;
            }

            $age = $now - (int) $state['updated'];
            if ( $age <= $timeout ) {
                continue;
            }

            $message = self::STATUS_QUEUED === $state['status']
                ? sprintf( 'Timed out waiting in the queue for %d min — the task was never picked up.', (int) round( $age / MINUTE_IN_SECONDS ) )
                : sprintf( 'Timed out after %d min with no response from the worker.', (int) round( $age / MINUTE_IN_SECONDS ) );

            self::set( $post_id, $lang, array(
                'status'  => self::STATUS_ERROR,
                'message' => $message,
                'run'     => '',
            ) );

            $released[] = $lang;
        }

        if ( ! empty( $released ) ) {
            error_log( sprintf(
                'OAIT: Released stuck translation languages for post %d after %ds: %s',
                $post_id,
                $timeout,
                implode( ',', $released )
            ) );
        }

        return $released;
    }

    /**
     * Convert the pre-refactor shared meta rows into per-language rows.
     *
     * Anything that was still listed in _ai_translation_in_progress is recorded
     * as an error rather than as running: the original rows carry no timestamp,
     * so there is no way to tell a job that started a minute ago from one that
     * has been wedged for a week. Marking them failed unblocks every post that
     * is stuck today, at the cost of a redundant retry for the rare job that
     * really was in flight during the upgrade.
     *
     * @param int $post_id Source post ID.
     */
    public static function maybe_migrate( $post_id ) {
        if ( isset( self::$migrated[ $post_id ] ) ) {
            return;
        }
        self::$migrated[ $post_id ] = true;

        if ( get_post_meta( $post_id, self::MIGRATED_FLAG, true ) ) {
            return;
        }

        $legacy_progress = get_post_meta( $post_id, self::LEGACY_IN_PROGRESS, true );
        $legacy_results  = get_post_meta( $post_id, self::LEGACY_RESULTS, true );

        $legacy_progress = is_array( $legacy_progress ) ? $legacy_progress : array();
        $legacy_results  = is_array( $legacy_results ) ? $legacy_results : array();

        // Posts the plugin never touched have nothing to convert. Returning
        // before the flag is written keeps the metabox from adding a meta row
        // to every post that is merely opened in the editor; the two reads
        // above are served from the primed meta cache on later requests.
        if ( empty( $legacy_progress ) && empty( $legacy_results ) ) {
            return;
        }

        $now = time();

        foreach ( array_keys( OAIT_Translator::LANGUAGES ) as $lang ) {
            $state = self::defaults( $lang );

            if ( isset( $legacy_results[ $lang ] ) && is_string( $legacy_results[ $lang ] ) ) {
                $message = $legacy_results[ $lang ];

                if ( 0 === strpos( $message, 'error' ) ) {
                    $state['status']  = self::STATUS_ERROR;
                    $state['message'] = trim( preg_replace( '/^error:?\s*/i', '', $message ) );
                } else {
                    $state['status'] = self::STATUS_SUCCESS;
                    if ( preg_match( '/post ID:\s*(\d+)/i', $message, $m ) ) {
                        $state['post_id'] = (int) $m[1];
                    }
                }

                $state['attempts'] = 1;
                $state['started']  = $now;
                $state['updated']  = $now;
            } elseif ( in_array( $lang, $legacy_progress, true ) ) {
                $state['status']   = self::STATUS_ERROR;
                $state['message']  = 'Interrupted before the plugin was upgraded — re-run the translation.';
                $state['attempts'] = 1;
                $state['started']  = $now;
                $state['updated']  = $now;
            } else {
                // Nothing recorded for this language; leave the row unwritten.
                continue;
            }

            update_post_meta( $post_id, self::META_PREFIX . $lang, $state );
        }

        update_post_meta( $post_id, self::MIGRATED_FLAG, 1 );
        delete_post_meta( $post_id, self::LEGACY_IN_PROGRESS );
        delete_post_meta( $post_id, self::LEGACY_RESULTS );
    }
}
