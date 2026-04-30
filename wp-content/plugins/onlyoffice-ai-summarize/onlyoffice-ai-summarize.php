<?php
/**
 * Plugin Name: ONLYOFFICE AI Summarize
 * Description: Generates an AI-powered Summary block for blog posts that answers the H1 title's explicit or implicit question. Integrates with ONLYOFFICE AI Translate to auto-translate the summary when a post translation is created.
 * Version: 1.0.0
 * Author: ONLYOFFICE
 * Text Domain: onlyoffice-ai-summarize
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'OAIS_VERSION', '1.0.0' );
define( 'OAIS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OAIS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

define( 'OAIS_META_SUMMARY',      '_oais_summary' );
define( 'OAIS_META_GENERATED_AT', '_oais_generated_at' );

require_once OAIS_PLUGIN_DIR . 'includes/class-admin-settings.php';
require_once OAIS_PLUGIN_DIR . 'includes/class-summarizer.php';
require_once OAIS_PLUGIN_DIR . 'includes/class-meta-box.php';
require_once OAIS_PLUGIN_DIR . 'includes/class-frontend.php';
require_once OAIS_PLUGIN_DIR . 'includes/class-translator-integration.php';

function oais_init() {
    new OAIS_Admin_Settings();
    new OAIS_Meta_Box();
    new OAIS_Frontend();
    new OAIS_Translator_Integration();
}
add_action( 'plugins_loaded', 'oais_init' );

function oais_enqueue_admin_assets( $hook ) {
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
        return;
    }
    $screen = get_current_screen();
    if ( ! $screen ) {
        return;
    }
    $post_types = get_option( 'oais_post_types', array( 'post' ) );
    if ( ! is_array( $post_types ) || ! in_array( $screen->post_type, $post_types, true ) ) {
        return;
    }

    wp_enqueue_style(
        'oais-admin',
        OAIS_PLUGIN_URL . 'assets/admin.css',
        array(),
        OAIS_VERSION
    );

    wp_enqueue_script(
        'oais-admin',
        OAIS_PLUGIN_URL . 'assets/admin.js',
        array( 'jquery' ),
        OAIS_VERSION,
        true
    );

    wp_localize_script( 'oais-admin', 'oaisData', array(
        'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'oais_generate_nonce' ),
        'maxWords' => (int) get_option( 'oais_max_words', 120 ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'oais_enqueue_admin_assets' );
