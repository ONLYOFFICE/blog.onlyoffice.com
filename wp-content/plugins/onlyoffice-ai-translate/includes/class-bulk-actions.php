<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OAIT_Bulk_Actions {

    public function __construct() {
        // Bulk action on posts list
        add_filter( 'bulk_actions-edit-post', array( $this, 'register_bulk_action' ) );
        add_filter( 'handle_bulk_actions-edit-post', array( $this, 'handle_bulk_action' ), 10, 3 );
        add_action( 'admin_notices', array( $this, 'bulk_action_notice' ) );

        // Button in Classic Editor publish box
        add_action( 'post_submitbox_misc_actions', array( $this, 'render_editor_button' ) );

        // Meta box with translation status
        add_action( 'add_meta_boxes', array( $this, 'add_translation_meta_box' ) );
    }

    /**
     * Add "Translate with AI" to bulk actions dropdown.
     */
    public function register_bulk_action( $actions ) {
        $actions['oait_translate'] = 'Translate with AI';
        return $actions;
    }

    /**
     * Handle the bulk action.
     */
    public function handle_bulk_action( $redirect_to, $action, $post_ids ) {
        if ( $action !== 'oait_translate' ) {
            return $redirect_to;
        }

        $queued = 0;

        foreach ( $post_ids as $post_id ) {
            // Only translate English posts
            $lang_details = apply_filters( 'wpml_post_language_details', null, $post_id );
            if ( ! $lang_details || $lang_details['language_code'] !== 'en' ) {
                continue;
            }

            if ( function_exists( 'as_enqueue_async_action' ) ) {
                as_enqueue_async_action( 'oait_translate_post_async', array( 'post_id' => $post_id ), 'oait' );
            } else {
                wp_schedule_single_event( time() + $queued, 'oait_translate_post_async', array( $post_id ) );
            }

            update_post_meta( $post_id, '_ai_translations_queued', true );
            $queued++;
        }

        return add_query_arg( 'oait_translated', $queued, $redirect_to );
    }

    /**
     * Show admin notice after bulk action.
     */
    public function bulk_action_notice() {
        if ( empty( $_REQUEST['oait_translated'] ) ) {
            return;
        }

        $count = absint( $_REQUEST['oait_translated'] );
        printf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            esc_html( sprintf(
                '%d post(s) queued for AI translation.',
                $count
            ) )
        );
    }

    /**
     * Render "Translate with AI" button in the Classic Editor publish box.
     */
    public function render_editor_button( $post ) {
        if ( $post->post_type !== 'post' || ! in_array( $post->post_status, array( 'publish', 'draft' ), true ) ) {
            return;
        }

        // Only show for English posts
        $lang_details = apply_filters( 'wpml_post_language_details', null, $post->ID );
        if ( ! $lang_details || $lang_details['language_code'] !== 'en' ) {
            return;
        }

        $is_queued = get_post_meta( $post->ID, '_ai_translations_queued', true );
        ?>
        <div class="misc-pub-section oait-translate-section">
            <span class="dashicons dashicons-translation" style="color:#2271b1;"></span>
            <strong>AI Translation</strong>
            <button type="button" class="button button-small oait-translate-btn"
                    data-post-id="<?php echo esc_attr( $post->ID ); ?>"
                    style="float:right;">
                <?php echo $is_queued ? 'Re-translate' : 'Translate with AI'; ?>
            </button>
            <span class="oait-translate-status" style="display:none;"></span>
            <span class="spinner oait-spinner" style="float:none;margin:0 4px;"></span>
            <div class="clear"></div>
        </div>
        <?php
    }

    /**
     * Add meta box showing translation status.
     */
    public function add_translation_meta_box() {
        add_meta_box(
            'oait_translation_status',
            'AI Translation Status',
            array( $this, 'render_translation_meta_box' ),
            'post',
            'side',
            'default'
        );
    }

    /**
     * Render translation status meta box.
     */
    public function render_translation_meta_box( $post ) {
        if ( $post->post_type !== 'post' ) {
            echo '<p>Not applicable.</p>';
            return;
        }

        // Only show for English posts
        $lang_details = apply_filters( 'wpml_post_language_details', null, $post->ID );
        if ( ! $lang_details || $lang_details['language_code'] !== 'en' ) {
            echo '<p>Only English source posts can be translated.</p>';
            return;
        }

        $wpml   = new OAIT_WPML_Integration();
        $status = $wpml->get_translation_status( $post->ID );
        $enabled = get_option( 'oait_enabled_languages', array() );

        if ( empty( $status ) ) {
            echo '<p>No translation data available.</p>';
            return;
        }

        echo '<ul style="margin:0;">';
        foreach ( OAIT_Translator::LANGUAGES as $code => $name ) {
            $is_enabled    = empty( $enabled ) || in_array( $code, $enabled, true );
            $translated_id = isset( $status[ $code ] ) ? $status[ $code ] : null;
            $icon          = $translated_id ? '&#10004;' : '&#10060;';
            $color         = $translated_id ? '#00a32a' : '#999';
            $style         = $is_enabled ? '' : 'opacity:0.5;';

            $label = esc_html( $name ) . ' (' . esc_html( $code ) . ')';
            if ( $translated_id ) {
                $edit_link = get_edit_post_link( $translated_id );
                $label = '<a href="' . esc_url( $edit_link ) . '">' . $label . '</a>';
            }

            printf(
                '<li style="padding:2px 0;%s"><span style="color:%s;">%s</span> %s</li>',
                $style,
                $color,
                $icon,
                $label
            );
        }
        echo '</ul>';

        $results = get_post_meta( $post->ID, '_ai_translation_results', true );
        if ( $results && is_array( $results ) ) {
            $errors = array_filter( $results, function ( $r ) {
                return strpos( $r, 'error' ) === 0;
            } );
            if ( ! empty( $errors ) ) {
                echo '<hr/><p style="color:#d63638;"><strong>Errors:</strong></p><ul style="margin:0;">';
                foreach ( $errors as $lang => $msg ) {
                    printf( '<li><code>%s</code>: %s</li>', esc_html( $lang ), esc_html( $msg ) );
                }
                echo '</ul>';
            }
        }
    }
}
