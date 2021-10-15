<?php
/**
 * Enqueue front end and editor JavaScript
 */
function ays_poll_gutenberg_scripts() {
    $blockPath = 'poll-maker-block.js';

    wp_enqueue_script("jquery-effects-core");
    wp_enqueue_script('ays_block_select2js', POLL_MAKER_AYS_ADMIN_URL . '/js/select2.min.js', array('jquery'), '4.0.6', true);
    wp_enqueue_script(POLL_MAKER_AYS_NAME . '-autosize', POLL_MAKER_AYS_PUBLIC_URL . '/js/poll-maker-autosize.js', array( 'jquery' ), POLL_MAKER_AYS_VERSION, false );
    wp_enqueue_script(POLL_MAKER_AYS_NAME, POLL_MAKER_AYS_PUBLIC_URL . '/js/poll-maker-ays-public.js', array('jquery'), POLL_MAKER_AYS_VERSION, false);
    wp_localize_script(POLL_MAKER_AYS_NAME . '-ajax-public', 'poll_maker_ajax_public', array('ajax_url' => admin_url('admin-ajax.php')));

    // Enqueue the bundled block JS file
    wp_enqueue_script(
        'poll-maker-block-js',
        POLL_MAKER_AYS_BASE_URL . "poll/" . $blockPath,
        array('jquery', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor'),
        POLL_MAKER_AYS_VERSION, true
    );
    wp_localize_script('ays-poll-gutenberg-block-js', 'ays_poll_block_ajax', array('aysDoShortCode' => admin_url('admin-ajax.php')));

    // wp_enqueue_style('ays_poll_font_awesome', 'https://use.fontawesome.com/releases/v5.6.3/css/all.css', array(), '5.6.3', 'all');
    wp_enqueue_style( POLL_MAKER_AYS_NAME . '-font-awesome', POLL_MAKER_AYS_ADMIN_URL . '/css/poll-maker-font-awesome-all.css', array(), POLL_MAKER_AYS_VERSION, 'all');
    // wp_enqueue_style('ays-block-font-awesome', POLL_MAKER_AYS_PUBLIC_URL . '/css/font_awesome_all.css', array(), '5.6.3', 'all');
    // wp_enqueue_style('ays_poll_fa_v4_shims', POLL_MAKER_AYS_ADMIN_URL . '/css/font_awesome_v4-shims.css', array(), '5.6.3', 'all');

    wp_enqueue_style('ays-block-animate', POLL_MAKER_AYS_ADMIN_URL . '/css/animate.min.css', array(), '2.0.6', 'all');
    wp_enqueue_style('ays-block-select2', POLL_MAKER_AYS_ADMIN_URL . '/css/select2.min.css', array(), '4.0.6', 'all');
    wp_enqueue_style(POLL_MAKER_AYS_NAME, POLL_MAKER_AYS_PUBLIC_URL . '/css/poll-maker-ays-public.css', array(), POLL_MAKER_AYS_VERSION, 'all');

    // Enqueue the bundled block CSS file
    wp_enqueue_style(
        'poll-maker-block-css',
        POLL_MAKER_AYS_BASE_URL . "poll/poll-maker-block.css",
        array(),
        POLL_MAKER_AYS_VERSION, 'all'
    );
}

function ays_poll_gutenberg_block_register() {

    global $wpdb;
    $block_name = 'poll';
    $block_namespace = 'poll-maker/' . $block_name;
    $poll_table = esc_sql($wpdb->prefix . "ayspoll_polls");
    $sql = "SELECT * FROM ".$poll_table;
    $results = $wpdb->get_results($sql, "ARRAY_A");

    register_block_type(
        $block_namespace,
        array(
            'render_callback' => 'pollmaker_render_callback',
            'editor_script' => 'poll-maker-block-js', // The block script slug
            'style' => 'poll-maker-block-css',
            'attributes' => array(
                'idner' => $results,
                'metaFieldValue' => array(
                    'type' => 'integer',
                ),
                'shortcode' => array(
                    'type' => 'string',
                ),
                'className' => array(
                    'type'  => 'string',                
                ),
                'openPopupId' => array(
                    'type'  => 'string',
                ),
            ),
        )
    );
}

function pollmaker_render_callback($attributes) {
    $ays_html = "<p style='text-align:center;'>" . __('Please select poll') . "</p>";
    if (isset($attributes["shortcode"]) && $attributes["shortcode"] != '') {
        $ays_html = do_shortcode($attributes["shortcode"]);
    }
    return $ays_html;
}

if (function_exists("register_block_type")) {
// Hook scripts function into block editor hook
    add_action('enqueue_block_editor_assets', 'ays_poll_gutenberg_scripts');
    add_action('init', 'ays_poll_gutenberg_block_register');
}