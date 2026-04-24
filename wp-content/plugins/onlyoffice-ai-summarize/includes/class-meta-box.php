<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OAIS_Meta_Box {

    const NONCE_ACTION = 'oais_save_meta';
    const NONCE_NAME   = 'oais_meta_nonce';

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
        add_action( 'save_post',      array( $this, 'save_meta' ), 10, 2 );

        add_action( 'wp_ajax_oais_generate_summary', array( $this, 'ajax_generate_summary' ) );
    }

    public function register_meta_box() {
        $post_types = get_option( 'oais_post_types', array( 'post' ) );
        if ( ! is_array( $post_types ) || empty( $post_types ) ) {
            return;
        }
        foreach ( $post_types as $pt ) {
            add_meta_box(
                'oais_summary_box',
                'AI Summary',
                array( $this, 'render_meta_box' ),
                $pt,
                'normal',
                'high'
            );
        }
    }

    public function render_meta_box( $post ) {
        wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

        $summary   = get_post_meta( $post->ID, OAIS_META_SUMMARY, true );
        $generated = get_post_meta( $post->ID, OAIS_META_GENERATED_AT, true );
        $max_words = (int) get_option( 'oais_max_words', 80 );
        $placeholder = "Intro paragraph in 2–3 sentences that answers the post's headline.\n\n- First key point in 5–10 words\n- Second key point in 5–10 words\n- Third key point in 5–10 words";
        ?>
        <div class="oais-meta-box">
            <p>
                <label for="oais_summary_field"><strong>Summary text</strong></label>
            </p>
            <p class="description" style="margin:0 0 6px;">
                Paragraph lines &mdash; no prefix. Bullet lines &mdash; start with <code>- </code> (dash + space). Bullets are optional. Leave empty to hide the block.
            </p>

            <textarea id="oais_summary_field"
                      name="oais_summary"
                      rows="10"
                      class="large-text code"
                      placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php echo esc_textarea( $summary ); ?></textarea>

            <p class="oais-actions">
                <button type="button"
                        id="oais-generate-btn"
                        class="button button-primary"
                        data-post-id="<?php echo esc_attr( $post->ID ); ?>">
                    Generate Summary
                </button>
                <span class="spinner oais-spinner" style="float:none;margin:0 4px;"></span>
                <span class="description">
                    Max <?php echo esc_html( $max_words ); ?> words in the paragraph. Bullets optional (5&ndash;10 words each).
                </span>
            </p>

            <p class="oais-status" id="oais-status" style="display:none;"></p>

            <?php if ( $generated ) : ?>
                <p class="description">
                    Last generated: <code><?php echo esc_html( $generated ); ?></code>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    public function save_meta( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
        if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( $_POST[ self::NONCE_NAME ], self::NONCE_ACTION ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $post_types = get_option( 'oais_post_types', array( 'post' ) );
        if ( ! is_array( $post_types ) || ! in_array( $post->post_type, $post_types, true ) ) {
            return;
        }

        $summary = isset( $_POST['oais_summary'] ) ? (string) wp_unslash( $_POST['oais_summary'] ) : '';
        $summary = $this->sanitize_summary( $summary );
        if ( $summary === '' ) {
            delete_post_meta( $post_id, OAIS_META_SUMMARY );
        } else {
            update_post_meta( $post_id, OAIS_META_SUMMARY, $summary );
        }
    }

    public function ajax_generate_summary() {
        check_ajax_referer( 'oais_generate_nonce', 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
        if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied.' ), 403 );
        }

        $post = get_post( $post_id );
        if ( ! $post ) {
            wp_send_json_error( array( 'message' => 'Post not found.' ), 404 );
        }

        $summarizer = new OAIS_Summarizer();
        $result     = $summarizer->generate( $post_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 );
        }

        update_post_meta( $post_id, OAIS_META_GENERATED_AT, current_time( 'mysql' ) );

        wp_send_json_success( array( 'summary' => $result ) );
    }

    /**
     * Keep plain-text bullets only — strip any HTML the writer may paste in.
     */
    private function sanitize_summary( $text ) {
        $lines = preg_split( "/\r\n|\n|\r/", $text );
        $clean = array();
        foreach ( $lines as $line ) {
            $line = wp_strip_all_tags( $line );
            $line = trim( $line );
            if ( $line !== '' ) {
                $clean[] = $line;
            }
        }
        return implode( "\n", $clean );
    }
}
