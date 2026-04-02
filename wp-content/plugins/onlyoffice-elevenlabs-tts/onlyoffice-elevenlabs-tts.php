<?php
/**
 * Plugin Name: ONLYOFFICE ElevenLabs TTS
 * Description: Generate audio versions of blog posts using ElevenLabs Text-to-Speech API. Exposes audioUrl via WPGraphQL.
 * Version: 1.0.0
 * Author: ONLYOFFICE
 * Text Domain: onlyoffice-elevenlabs-tts
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'OETL_VERSION', '1.0.0' );
define( 'OETL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OETL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check if WPGraphQL is active.
 */
function oetl_check_dependencies() {
    if ( ! class_exists( 'WPGraphQL' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__( 'ONLYOFFICE ElevenLabs TTS requires WPGraphQL to be installed and activated.', 'onlyoffice-elevenlabs-tts' );
            echo '</p></div>';
        } );
        return false;
    }
    return true;
}

/**
 * Initialize the plugin.
 */
function oetl_init() {
    if ( ! oetl_check_dependencies() ) {
        return;
    }

    require_once OETL_PLUGIN_DIR . 'includes/class-admin-settings.php';
    require_once OETL_PLUGIN_DIR . 'includes/class-tts-generator.php';
    require_once OETL_PLUGIN_DIR . 'includes/class-meta-box.php';
    require_once OETL_PLUGIN_DIR . 'includes/class-graphql.php';

    new OETL_Admin_Settings();
    new OETL_Meta_Box();
    new OETL_GraphQL();

    // Action Scheduler handler for async generation
    add_action( 'oetl_generate_audio_async', 'oetl_handle_async_generation', 10, 1 );

    // AJAX handlers
    add_action( 'wp_ajax_oetl_generate_audio', 'oetl_ajax_generate_audio' );
    add_action( 'wp_ajax_oetl_audio_status', 'oetl_ajax_audio_status' );

    // Auto-generate on publish
    add_action( 'transition_post_status', 'oetl_on_post_publish', 10, 3 );

    // Enqueue admin assets
    add_action( 'admin_enqueue_scripts', 'oetl_enqueue_admin_assets' );
}
add_action( 'plugins_loaded', 'oetl_init' );

/**
 * Auto-generate audio when a post is published.
 */
function oetl_on_post_publish( $new_status, $old_status, $post ) {
    if ( $new_status !== 'publish' || $post->post_type !== 'post' ) {
        return;
    }

    if ( ! get_option( 'oetl_auto_generate', false ) ) {
        return;
    }

    // Skip if audio already exists
    $attachment_id = get_post_meta( $post->ID, '_oetl_audio_attachment_id', true );
    if ( ! empty( $attachment_id ) ) {
        return;
    }

    if ( function_exists( 'as_enqueue_async_action' ) ) {
        as_enqueue_async_action( 'oetl_generate_audio_async', array( 'post_id' => $post->ID ), 'oetl' );
    } else {
        wp_schedule_single_event( time(), 'oetl_generate_audio_async', array( $post->ID ) );
    }

    update_post_meta( $post->ID, '_oetl_audio_in_progress', true );
}

/**
 * Handle async audio generation.
 */
function oetl_handle_async_generation( $post_id ) {
    $generator = new OETL_TTS_Generator();
    $result = $generator->generate( $post_id );

    if ( is_wp_error( $result ) ) {
        update_post_meta( $post_id, '_oetl_audio_error', $result->get_error_message() );
        delete_post_meta( $post_id, '_oetl_audio_in_progress' );
        error_log( sprintf( 'OETL: Failed to generate audio for post %d: %s', $post_id, $result->get_error_message() ) );
        return;
    }

    update_post_meta( $post_id, '_oetl_audio_attachment_id', $result );
    update_post_meta( $post_id, '_oetl_audio_generated_at', current_time( 'mysql' ) );
    delete_post_meta( $post_id, '_oetl_audio_in_progress' );
    delete_post_meta( $post_id, '_oetl_audio_error' );

    error_log( sprintf( 'OETL: Audio generated for post %d, attachment ID: %d', $post_id, $result ) );
}

/**
 * AJAX handler for manual audio generation.
 */
function oetl_ajax_generate_audio() {
    check_ajax_referer( 'oetl_audio_nonce', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Insufficient permissions.' );
    }

    $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    if ( ! $post_id ) {
        wp_send_json_error( 'Invalid post ID.' );
    }

    // Clear previous error
    delete_post_meta( $post_id, '_oetl_audio_error' );
    update_post_meta( $post_id, '_oetl_audio_in_progress', true );

    if ( function_exists( 'as_enqueue_async_action' ) ) {
        as_enqueue_async_action( 'oetl_generate_audio_async', array( 'post_id' => $post_id ), 'oetl' );
    } else {
        wp_schedule_single_event( time(), 'oetl_generate_audio_async', array( $post_id ) );
    }

    wp_send_json_success( 'Audio generation queued.' );
}

/**
 * AJAX handler to check audio generation status.
 */
function oetl_ajax_audio_status() {
    check_ajax_referer( 'oetl_audio_nonce', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Insufficient permissions.' );
    }

    $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
    if ( ! $post_id ) {
        wp_send_json_error( 'Invalid post ID.' );
    }

    $attachment_id = get_post_meta( $post_id, '_oetl_audio_attachment_id', true );
    $in_progress   = get_post_meta( $post_id, '_oetl_audio_in_progress', true );
    $error         = get_post_meta( $post_id, '_oetl_audio_error', true );
    $generated_at  = get_post_meta( $post_id, '_oetl_audio_generated_at', true );

    $audio_url = '';
    if ( $attachment_id ) {
        $audio_url = wp_get_attachment_url( $attachment_id );
    }

    wp_send_json_success( array(
        'audioUrl'    => $audio_url ?: '',
        'inProgress'  => (bool) $in_progress,
        'error'       => $error ?: '',
        'generatedAt' => $generated_at ?: '',
        'hasAudio'    => ! empty( $attachment_id ) && ! empty( $audio_url ),
    ) );
}

/**
 * Enqueue admin CSS and JS on post edit screens.
 */
function oetl_enqueue_admin_assets( $hook ) {
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
        return;
    }

    wp_enqueue_style(
        'oetl-admin',
        OETL_PLUGIN_URL . 'assets/admin.css',
        array(),
        OETL_VERSION
    );

    wp_enqueue_script(
        'oetl-admin',
        OETL_PLUGIN_URL . 'assets/admin.js',
        array( 'jquery' ),
        OETL_VERSION,
        true
    );

    $post_id = 0;
    global $post;
    if ( $post ) {
        $post_id = $post->ID;
    }

    wp_localize_script( 'oetl-admin', 'oetlData', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'oetl_audio_nonce' ),
        'postId'  => $post_id,
    ) );
}
