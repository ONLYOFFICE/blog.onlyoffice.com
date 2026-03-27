<?php
/**
 * Plugin Name: ONLYOFFICE AI Translate
 * Description: Automatically translates posts to all WPML languages using Claude API (Anthropic).
 * Version: 1.0.0
 * Author: ONLYOFFICE
 * Text Domain: onlyoffice-ai-translate
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'OAIT_VERSION', '1.0.0' );
define( 'OAIT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OAIT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

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
    add_action( 'oait_translate_post_async', 'oait_handle_async_translation', 10, 1 );

    // AJAX handler for manual translation
    add_action( 'wp_ajax_oait_translate_post', 'oait_ajax_translate_post' );

    // Enqueue admin assets
    add_action( 'admin_enqueue_scripts', 'oait_enqueue_admin_assets' );
}
add_action( 'plugins_loaded', 'oait_init' );

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

    if ( function_exists( 'as_enqueue_async_action' ) ) {
        as_enqueue_async_action( 'oait_translate_post_async', array( 'post_id' => $post->ID ), 'oait' );
    } else {
        wp_schedule_single_event( time(), 'oait_translate_post_async', array( $post->ID ) );
    }

    update_post_meta( $post->ID, '_ai_translations_queued', true );
}

/**
 * Handle async translation via Action Scheduler.
 */
function oait_handle_async_translation( $post_id ) {
    $translator = new OAIT_Translator();
    $wpml       = new OAIT_WPML_Integration();
    $languages  = get_option( 'oait_enabled_languages', array() );

    if ( empty( $languages ) ) {
        $languages = array_keys( OAIT_Translator::LANGUAGES );
    }

    $results = array();

    foreach ( $languages as $lang_code ) {
        if ( $wpml->translation_exists( $post_id, $lang_code ) ) {
            $results[ $lang_code ] = 'skipped';
            continue;
        }

        $translated = $translator->translate( $post_id, $lang_code );

        if ( is_wp_error( $translated ) ) {
            error_log( sprintf(
                'OAIT: Failed to translate post %d to %s: %s',
                $post_id,
                $lang_code,
                $translated->get_error_message()
            ) );
            $results[ $lang_code ] = 'error: ' . $translated->get_error_message();
            continue;
        }

        $new_post_id = $wpml->create_translation( $post_id, $translated, $lang_code );

        if ( is_wp_error( $new_post_id ) ) {
            error_log( sprintf(
                'OAIT: Failed to create WPML translation for post %d to %s: %s',
                $post_id,
                $lang_code,
                $new_post_id->get_error_message()
            ) );
            $results[ $lang_code ] = 'error: ' . $new_post_id->get_error_message();
            continue;
        }

        $results[ $lang_code ] = 'success (post ID: ' . $new_post_id . ')';
    }

    update_post_meta( $post_id, '_ai_translation_results', $results );
    error_log( sprintf( 'OAIT: Translation complete for post %d: %s', $post_id, wp_json_encode( $results ) ) );
}

/**
 * AJAX handler for manual translation.
 */
function oait_ajax_translate_post() {
    check_ajax_referer( 'oait_translate_nonce', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Insufficient permissions.' );
    }

    $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    if ( ! $post_id ) {
        wp_send_json_error( 'Invalid post ID.' );
    }

    // Queue async translation
    if ( function_exists( 'as_enqueue_async_action' ) ) {
        as_enqueue_async_action( 'oait_translate_post_async', array( 'post_id' => $post_id ), 'oait' );
    } else {
        wp_schedule_single_event( time(), 'oait_translate_post_async', array( $post_id ) );
    }

    delete_post_meta( $post_id, '_ai_translations_queued' );
    update_post_meta( $post_id, '_ai_translations_queued', true );

    wp_send_json_success( 'Translation queued successfully.' );
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

    wp_localize_script( 'oait-admin', 'oaitData', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'oait_translate_nonce' ),
    ) );
}
