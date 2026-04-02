<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OETL_Meta_Box {

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
    }

    public function add_meta_box() {
        add_meta_box(
            'oetl_audio_status',
            'ElevenLabs TTS',
            array( $this, 'render_meta_box' ),
            'post',
            'side',
            'default'
        );
    }

    public function render_meta_box( $post ) {
        if ( $post->post_type !== 'post' ) {
            echo '<p>Not applicable.</p>';
            return;
        }

        if ( ! in_array( $post->post_status, array( 'publish', 'draft' ), true ) ) {
            echo '<p>Save the post as a draft first to generate audio.</p>';
            return;
        }

        $attachment_id = get_post_meta( $post->ID, '_oetl_audio_attachment_id', true );
        $in_progress   = get_post_meta( $post->ID, '_oetl_audio_in_progress', true );
        $error         = get_post_meta( $post->ID, '_oetl_audio_error', true );
        $generated_at  = get_post_meta( $post->ID, '_oetl_audio_generated_at', true );

        if ( $in_progress ) {
            // In progress
            ?>
            <div class="oetl-status">
                <span class="spinner is-active" style="float:none;margin:0 4px 0 0;"></span>
                <em>Generating audio...</em>
            </div>
            <?php
        } elseif ( $attachment_id ) {
            // Audio exists
            $audio_url = wp_get_attachment_url( $attachment_id );
            if ( $audio_url ) {
                ?>
                <div class="oetl-preview">
                    <audio controls preload="metadata" style="width:100%;margin:6px 0;">
                        <source src="<?php echo esc_url( $audio_url ); ?>" type="audio/mpeg">
                    </audio>
                    <?php if ( $generated_at ) : ?>
                        <p class="description" style="margin:4px 0;">Generated: <?php echo esc_html( $generated_at ); ?></p>
                    <?php endif; ?>
                </div>
                <div style="margin-top:8px;">
                    <button type="button" class="button oetl-generate-btn"
                            data-post-id="<?php echo esc_attr( $post->ID ); ?>">
                        Regenerate Audio
                    </button>
                    <span class="spinner oetl-spinner" style="float:none;margin:0 4px;"></span>
                </div>
                <?php
            }
        } else {
            // No audio
            if ( $error ) {
                ?>
                <p style="color:#d63638;font-size:12px;margin:4px 0;">
                    <strong>Error:</strong> <?php echo esc_html( $error ); ?>
                </p>
                <?php
            }
            ?>
            <div style="margin-top:4px;">
                <button type="button" class="button button-primary oetl-generate-btn"
                        data-post-id="<?php echo esc_attr( $post->ID ); ?>">
                    Generate Audio
                </button>
                <span class="spinner oetl-spinner" style="float:none;margin:0 4px;"></span>
            </div>
            <?php
        }

        echo '<span class="oetl-status-msg" style="display:none;margin-top:6px;font-size:12px;"></span>';
    }
}
