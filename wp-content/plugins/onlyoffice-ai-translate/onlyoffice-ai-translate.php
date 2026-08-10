<?php
/**
 * Plugin Name: ONLYOFFICE AI Translate
 * Description: Automatically translates posts to all WPML languages using the OpenAI chat completions API.
 * Version: 1.1.0
 * Author: ONLYOFFICE
 * Text Domain: onlyoffice-ai-translate
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'OAIT_VERSION', '1.1.0' );
define( 'OAIT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OAIT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/** Action Scheduler hook and group used for every translation task. */
define( 'OAIT_ASYNC_HOOK', 'oait_translate_post_async' );
define( 'OAIT_ASYNC_GROUP', 'oait' );

/**
 * Check if WPML is active before loading the plugin.
 */
function oait_check_dependencies() {
    if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__( 'ONLYOFFICE AI Translate requires WPML to be installed and activated.', 'onlyoffice-ai-translate' );
            echo '</p></div>';
        } );
        return false;
    }
    return true;
}

/**
 * Initialize the plugin.
 */
function oait_init() {
    if ( ! oait_check_dependencies() ) {
        return;
    }

    require_once OAIT_PLUGIN_DIR . 'includes/class-translator.php';
    require_once OAIT_PLUGIN_DIR . 'includes/class-job-state.php';
    require_once OAIT_PLUGIN_DIR . 'includes/class-wpml-integration.php';
    require_once OAIT_PLUGIN_DIR . 'includes/class-admin-settings.php';
    require_once OAIT_PLUGIN_DIR . 'includes/class-bulk-actions.php';

    // Settings page
    new OAIT_Admin_Settings();

    // Bulk actions and editor button
    new OAIT_Bulk_Actions();

    // Auto-translate on publish
    add_action( 'transition_post_status', 'oait_on_post_publish', 10, 3 );

    // Action Scheduler handler
    add_action( OAIT_ASYNC_HOOK, 'oait_handle_async_translation', 10, 2 );

    // AJAX handler for manual translation
    add_action( 'wp_ajax_oait_translate_post', 'oait_ajax_translate_post' );

    // AJAX handler to stop queued/running translations
    add_action( 'wp_ajax_oait_cancel_translation', 'oait_ajax_cancel_translation' );

    // AJAX handler for translation status check
    add_action( 'wp_ajax_oait_translation_status', 'oait_ajax_translation_status' );

    // Enqueue admin assets
    add_action( 'admin_enqueue_scripts', 'oait_enqueue_admin_assets' );
}
add_action( 'plugins_loaded', 'oait_init' );

/**
 * Target languages for a translation run: the configured subset, or every
 * language the translator knows about when nothing is configured.
 *
 * @return string[]
 */
function oait_get_target_languages() {
    $languages = get_option( 'oait_enabled_languages', array() );

    if ( ! is_array( $languages ) || empty( $languages ) ) {
        $languages = array_keys( OAIT_Translator::LANGUAGES );
    }

    return array_values( array_intersect( $languages, array_keys( OAIT_Translator::LANGUAGES ) ) );
}

/**
 * Action Scheduler arguments for a single-language task.
 *
 * Cancelling relies on an exact argument match, so this shape is built in one
 * place and reused by both the enqueue and the unschedule path.
 *
 * @param int    $post_id   Source post ID.
 * @param string $lang_code WPML language code.
 * @return array
 */
function oait_async_args( $post_id, $lang_code ) {
    return array(
        'post_id'   => (int) $post_id,
        'languages' => array( $lang_code ),
    );
}

/**
 * Queue one translation task for one language and mark the language as queued.
 *
 * The state row is written before dispatching: a status poll that lands between
 * the enqueue and the worker picking the task up must not report the language
 * as idle, or the metabox would offer a second "Translate" for work that is
 * already scheduled.
 *
 * @param int    $post_id   Source post ID.
 * @param string $lang_code WPML language code.
 */
function oait_enqueue_language( $post_id, $lang_code ) {
    OAIT_Job_State::set( $post_id, $lang_code, array(
        'status'  => OAIT_Job_State::STATUS_QUEUED,
        'message' => '',
        'started' => time(),
        'run'     => '',
    ) );

    if ( function_exists( 'as_enqueue_async_action' ) ) {
        as_enqueue_async_action( OAIT_ASYNC_HOOK, oait_async_args( $post_id, $lang_code ), OAIT_ASYNC_GROUP );
    } else {
        // Action Scheduler is not bundled by this plugin — it is inherited from
        // third-party plugins that vendor it. Fall back to WP-Cron if none of
        // them is active.
        wp_schedule_single_event( time(), OAIT_ASYNC_HOOK, array( $post_id, array( $lang_code ) ) );
    }
}

/**
 * Drop any scheduled task for one language of one post.
 *
 * A task that is already executing cannot be killed from here; the worker
 * checks the state row after the API call returns and aborts before writing
 * anything to WPML.
 *
 * @param int    $post_id   Source post ID.
 * @param string $lang_code WPML language code.
 */
function oait_unschedule_language( $post_id, $lang_code ) {
    if ( function_exists( 'as_unschedule_all_actions' ) ) {
        as_unschedule_all_actions( OAIT_ASYNC_HOOK, oait_async_args( $post_id, $lang_code ), OAIT_ASYNC_GROUP );
    }

    wp_clear_scheduled_hook( OAIT_ASYNC_HOOK, array( $post_id, array( $lang_code ) ) );
}

/**
 * Drop the whole-post task queued by plugin versions before 1.1.0, which
 * translated every language in a single Action Scheduler run.
 *
 * @param int $post_id Source post ID.
 */
function oait_unschedule_legacy_task( $post_id ) {
    if ( function_exists( 'as_unschedule_all_actions' ) ) {
        as_unschedule_all_actions( OAIT_ASYNC_HOOK, array( 'post_id' => (int) $post_id ), OAIT_ASYNC_GROUP );
    }

    wp_clear_scheduled_hook( OAIT_ASYNC_HOOK, array( $post_id ) );
}

/**
 * Auto-translate when a post is published.
 */
function oait_on_post_publish( $new_status, $old_status, $post ) {
    if ( $new_status !== 'publish' || $post->post_type !== 'post' ) {
        return;
    }

    if ( ! get_option( 'oait_auto_translate', false ) ) {
        return;
    }

    // Only translate English posts
    $lang_details = apply_filters( 'wpml_post_language_details', null, $post->ID );
    if ( ! $lang_details || $lang_details['language_code'] !== 'en' ) {
        return;
    }

    // Prevent duplicate queueing
    if ( get_post_meta( $post->ID, '_ai_translations_queued', true ) ) {
        return;
    }

    // One task per language, same as the manual path: a whole-post task cannot
    // be cancelled or retried for a single language.
    foreach ( oait_get_target_languages() as $lang_code ) {
        oait_enqueue_language( $post->ID, $lang_code );
    }

    update_post_meta( $post->ID, '_ai_translations_queued', true );
}

/**
 * Handle async translation via Action Scheduler.
 *
 * @param int        $post_id   Source post ID.
 * @param array|null $languages Languages to translate; null means every enabled
 *                              language (tasks queued before plugin 1.1.0).
 */
function oait_handle_async_translation( $post_id, $languages = null ) {
    $request_timeout = OAIT_Translator::get_request_timeout();

    // Give PHP headroom on top of the HTTP timeout for prompt assembly and the
    // WPML write that follows it.
    @set_time_limit( $request_timeout + 90 );
    @ini_set( 'max_execution_time', (string) ( $request_timeout + 90 ) );

    if ( empty( $languages ) || ! is_array( $languages ) ) {
        $languages = oait_get_target_languages();
    }

    $translator = new OAIT_Translator();
    $wpml       = new OAIT_WPML_Integration();

    // Identifies this worker as the owner of the rows it sets to 'running'.
    $run_token = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'oait', true );

    // Safety net: if PHP dies (fatal, memory limit) before the loop finishes,
    // the language would stay 'running' until the stale sweep releases it an
    // hour or more later. Flip it to an error now so a retry is offered
    // immediately.
    // Only rows still owned by this run are touched, so a concurrent worker
    // handling another language is never clobbered.
    register_shutdown_function( function () use ( $post_id, $languages, $run_token ) {
        $error    = error_get_last();
        $is_fatal = $error && in_array(
            $error['type'],
            array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ),
            true
        );

        $stuck = array();

        foreach ( $languages as $lang_code ) {
            $released = OAIT_Job_State::set_if_running( $post_id, $lang_code, $run_token, array(
                'status'  => OAIT_Job_State::STATUS_ERROR,
                'message' => $is_fatal
                    ? 'PHP fatal — ' . $error['message']
                    : 'Process terminated unexpectedly.',
                'run'     => '',
            ) );

            if ( $released ) {
                $stuck[] = $lang_code;
            }
        }

        if ( ! empty( $stuck ) ) {
            error_log( sprintf(
                'OAIT: Shutdown cleanup for post %d, stuck langs: %s, fatal: %s',
                $post_id,
                implode( ',', $stuck ),
                $is_fatal ? 'yes' : 'no'
            ) );
        }
    } );

    foreach ( $languages as $lang_code ) {
        $state = OAIT_Job_State::get( $post_id, $lang_code );

        // Cancelled after the task was queued but before it ran.
        if ( OAIT_Job_State::STATUS_CANCELLED === $state['status'] ) {
            error_log( sprintf( 'OAIT: Skipping cancelled translation of post %d to %s.', $post_id, $lang_code ) );
            continue;
        }

        OAIT_Job_State::set( $post_id, $lang_code, array(
            'status'   => OAIT_Job_State::STATUS_RUNNING,
            'message'  => '',
            'attempts' => (int) $state['attempts'] + 1,
            'started'  => $state['started'] ? $state['started'] : time(),
            'run'      => $run_token,
        ) );

        $existing_post_id = $wpml->get_existing_translation_id( $post_id, $lang_code );

        $translated = $translator->translate( $post_id, $lang_code );

        if ( is_wp_error( $translated ) ) {
            error_log( sprintf(
                'OAIT: Failed to translate post %d to %s: %s',
                $post_id,
                $lang_code,
                $translated->get_error_message()
            ) );

            OAIT_Job_State::set_if_running( $post_id, $lang_code, $run_token, array(
                'status'  => OAIT_Job_State::STATUS_ERROR,
                'message' => $translated->get_error_message(),
                'run'     => '',
            ) );
            continue;
        }

        // A cancel issued while the API call was in flight must not leave a
        // half-applied translation behind, so the state is re-read here rather
        // than trusting the copy taken before the call.
        $current = OAIT_Job_State::get( $post_id, $lang_code );
        if ( OAIT_Job_State::STATUS_RUNNING !== $current['status'] || $current['run'] !== $run_token ) {
            error_log( sprintf(
                'OAIT: Discarding translation of post %d to %s — job was %s while the request was in flight.',
                $post_id,
                $lang_code,
                $current['status']
            ) );
            continue;
        }

        if ( $existing_post_id ) {
            $result_id = $wpml->update_translation( $existing_post_id, $translated, $lang_code );
        } else {
            $result_id = $wpml->create_translation( $post_id, $translated, $lang_code );
        }

        if ( is_wp_error( $result_id ) ) {
            error_log( sprintf(
                'OAIT: Failed to %s WPML translation for post %d to %s: %s',
                $existing_post_id ? 'update' : 'create',
                $post_id,
                $lang_code,
                $result_id->get_error_message()
            ) );

            OAIT_Job_State::set_if_running( $post_id, $lang_code, $run_token, array(
                'status'  => OAIT_Job_State::STATUS_ERROR,
                'message' => $result_id->get_error_message(),
                'run'     => '',
            ) );
            continue;
        }

        // Recorded unconditionally: the translated post exists now, so reporting
        // anything other than success would misrepresent the database even if a
        // cancel landed during the WPML write.
        OAIT_Job_State::set( $post_id, $lang_code, array(
            'status'  => OAIT_Job_State::STATUS_SUCCESS,
            'message' => '',
            'post_id' => (int) $result_id,
            'run'     => '',
        ) );

        error_log( sprintf( 'OAIT: Translated post %d to %s (post ID: %d).', $post_id, $lang_code, $result_id ) );
    }
}

/**
 * Read and validate the post_id + languages pair shared by the AJAX handlers.
 *
 * @param array $source $_POST or $_GET.
 * @return array{post_id:int,languages:string[]}
 */
function oait_read_request_languages( $source ) {
    $post_id = isset( $source['post_id'] ) ? absint( $source['post_id'] ) : 0;

    $languages = array();
    if ( isset( $source['languages'] ) && is_array( $source['languages'] ) ) {
        $languages = array_map( 'sanitize_text_field', wp_unslash( $source['languages'] ) );
        $languages = array_values( array_intersect( $languages, array_keys( OAIT_Translator::LANGUAGES ) ) );

        $enabled = get_option( 'oait_enabled_languages', array() );
        if ( is_array( $enabled ) && ! empty( $enabled ) ) {
            $languages = array_values( array_intersect( $languages, $enabled ) );
        }
    }

    return array(
        'post_id'   => $post_id,
        'languages' => $languages,
    );
}

/**
 * AJAX handler for manual translation.
 *
 * Re-queueing a language that is already queued or running is treated as a
 * forced restart: the previous task is unscheduled first so the post is not
 * translated twice in parallel.
 */
function oait_ajax_translate_post() {
    check_ajax_referer( 'oait_translate_nonce', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Insufficient permissions.' );
    }

    $request = oait_read_request_languages( $_POST );

    if ( ! $request['post_id'] ) {
        wp_send_json_error( 'Invalid post ID.' );
    }
    if ( empty( $request['languages'] ) ) {
        wp_send_json_error( 'No valid languages selected.' );
    }

    $post_id  = $request['post_id'];
    $restarted = array();

    foreach ( $request['languages'] as $lang_code ) {
        if ( OAIT_Job_State::is_active( OAIT_Job_State::get( $post_id, $lang_code ) ) ) {
            oait_unschedule_language( $post_id, $lang_code );
            $restarted[] = $lang_code;
        }

        oait_enqueue_language( $post_id, $lang_code );
    }

    // Plugin versions before 1.1.0 could have a whole-post task pending; it
    // would re-translate every language behind the user's back.
    oait_unschedule_legacy_task( $post_id );

    update_post_meta( $post_id, '_ai_translations_queued', true );

    if ( ! empty( $restarted ) ) {
        error_log( sprintf(
            'OAIT: Force-restarted translation of post %d for: %s',
            $post_id,
            implode( ',', $restarted )
        ) );
    }

    wp_send_json_success( array(
        'queued'    => $request['languages'],
        'restarted' => $restarted,
        'message'   => sprintf(
            'Queued %d language(s).%s',
            count( $request['languages'] ),
            $restarted ? ' Restarted: ' . implode( ', ', $restarted ) . '.' : ''
        ),
    ) );
}

/**
 * AJAX handler to stop queued/running translations.
 *
 * Without a language list every active language of the post is stopped.
 */
function oait_ajax_cancel_translation() {
    check_ajax_referer( 'oait_translate_nonce', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Insufficient permissions.' );
    }

    $request = oait_read_request_languages( $_POST );

    if ( ! $request['post_id'] ) {
        wp_send_json_error( 'Invalid post ID.' );
    }

    $post_id   = $request['post_id'];
    $languages = ! empty( $request['languages'] )
        ? $request['languages']
        : OAIT_Job_State::active_languages( $post_id );

    $cancelled = array();

    foreach ( $languages as $lang_code ) {
        if ( ! OAIT_Job_State::is_active( OAIT_Job_State::get( $post_id, $lang_code ) ) ) {
            continue;
        }

        oait_unschedule_language( $post_id, $lang_code );

        OAIT_Job_State::set( $post_id, $lang_code, array(
            'status'  => OAIT_Job_State::STATUS_CANCELLED,
            'message' => 'Cancelled.',
            'run'     => '',
        ) );

        $cancelled[] = $lang_code;
    }

    oait_unschedule_legacy_task( $post_id );
    delete_post_meta( $post_id, '_ai_translations_queued' );

    if ( ! empty( $cancelled ) ) {
        error_log( sprintf(
            'OAIT: Cancelled translation of post %d for: %s',
            $post_id,
            implode( ',', $cancelled )
        ) );
    }

    wp_send_json_success( array(
        'cancelled' => $cancelled,
        'message'   => $cancelled
            // A task already inside the API call finishes that call before it
            // notices the cancel, so the UI must not promise an instant stop.
            ? sprintf( 'Stopped %d language(s). A request already in flight may take up to a minute to wind down.', count( $cancelled ) )
            : 'Nothing was running.',
    ) );
}

/**
 * AJAX handler to check translation status.
 */
function oait_ajax_translation_status() {
    check_ajax_referer( 'oait_translate_nonce', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Insufficient permissions.' );
    }

    $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
    if ( ! $post_id ) {
        wp_send_json_error( 'Invalid post ID.' );
    }

    wp_send_json_success( oait_build_status_payload( $post_id ) );
}

/**
 * Build the per-language payload shared by the status endpoint and the initial
 * metabox render, so both surfaces always agree on what a language looks like.
 *
 * @param int $post_id Source post ID.
 * @return array
 */
function oait_build_status_payload( $post_id ) {
    $wpml    = new OAIT_WPML_Integration();
    $status  = $wpml->get_translation_status( $post_id );
    $enabled = get_option( 'oait_enabled_languages', array() );

    // Reads the state after migrating legacy meta and releasing stuck jobs, so
    // simply opening the post is enough to recover a hung translation.
    $states = OAIT_Job_State::all( $post_id );

    $now       = time();
    $languages = array();
    $active    = false;

    foreach ( OAIT_Translator::LANGUAGES as $code => $name ) {
        $state         = $states[ $code ];
        $is_active     = OAIT_Job_State::is_active( $state );
        $translated_id = isset( $status[ $code ] ) ? $status[ $code ] : null;

        if ( $is_active ) {
            $active = true;
        }

        $languages[ $code ] = array(
            'name'     => $name,
            'enabled'  => empty( $enabled ) || in_array( $code, $enabled, true ),
            'postId'   => $translated_id,
            'editLink' => $translated_id ? get_edit_post_link( $translated_id, 'raw' ) : null,
            'status'   => $state['status'],
            'active'   => $is_active,
            'message'  => $state['message'],
            'attempts' => (int) $state['attempts'],
            'elapsed'  => $is_active && $state['started'] ? max( 0, $now - (int) $state['started'] ) : 0,
        );
    }

    return array(
        'languages'    => $languages,
        'active'       => $active,
        // Retained under the old name for any cached admin.js still polling.
        'complete'     => ! $active,
        'staleTimeout' => OAIT_Job_State::stale_timeout(),
    );
}

/**
 * Enqueue admin CSS and JS on post edit screens.
 */
function oait_enqueue_admin_assets( $hook ) {
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php', 'edit.php' ), true ) ) {
        return;
    }

    wp_enqueue_style(
        'oait-admin',
        OAIT_PLUGIN_URL . 'assets/admin.css',
        array(),
        OAIT_VERSION
    );

    wp_enqueue_script(
        'oait-admin',
        OAIT_PLUGIN_URL . 'assets/admin.js',
        array( 'jquery' ),
        OAIT_VERSION,
        true
    );

    $post_id = 0;
    if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
        global $post;
        if ( $post ) {
            $post_id = $post->ID;
        }
    }

    wp_localize_script( 'oait-admin', 'oaitData', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'oait_translate_nonce' ),
        'postId'  => $post_id,
    ) );
}
