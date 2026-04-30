<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OETL_Meta_Box {

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_voice_override' ), 10, 2 );
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

        // Voice override dropdown
        wp_nonce_field( 'oetl_voice_meta', 'oetl_voice_meta_nonce' );
        $current_override = get_post_meta( $post->ID, '_oetl_voice_id', true );
        $configured_voices = OETL_Admin_Settings::get_all_configured_voices();

        // Get WPML language names for labels
        $language_names = array();
        if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
            $languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
            if ( is_array( $languages ) ) {
                foreach ( $languages as $code => $lang ) {
                    $language_names[ $code ] = ! empty( $lang['native_name'] ) ? $lang['native_name'] : $code;
                }
            }
        }

        ?>
        <div class="oetl-voice-override">
            <label for="oetl_voice_id_override"><strong>Voice</strong></label>
            <select name="oetl_voice_id_override" id="oetl_voice_id_override" style="width:100%;margin-top:4px;">
                <option value="" <?php selected( $current_override, '' ); ?>>English (Default)</option>
                <?php foreach ( $configured_voices as $lang_code => $voice_id ) :
                    if ( $lang_code === 'en' ) {
                        continue; // Already shown as the default option above
                    }
                    if ( isset( $language_names[ $lang_code ] ) ) {
                        $label = $language_names[ $lang_code ];
                    } else {
                        $label = strtoupper( $lang_code );
                    }
                ?>
                    <option value="<?php echo esc_attr( $voice_id ); ?>" <?php selected( $current_override, $voice_id ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php

        // Dynamic content area (rebuilt by JS after generation)
        echo '<div class="oetl-dynamic-content">';

        // Bypass Redis Object Cache so a reload during generation reflects the
        // latest progress written by the AJAX-generate process.
        wp_cache_delete( $post->ID, 'post_meta' );

        $attachment_id    = get_post_meta( $post->ID, '_oetl_audio_attachment_id', true );
        $in_progress      = get_post_meta( $post->ID, '_oetl_audio_in_progress', true );
        $error            = get_post_meta( $post->ID, '_oetl_audio_error', true );
        $generated_at     = get_post_meta( $post->ID, '_oetl_audio_generated_at', true );
        $progress_current = get_post_meta( $post->ID, '_oetl_audio_progress_current', true );
        $progress_total   = get_post_meta( $post->ID, '_oetl_audio_progress_total', true );

        if ( $in_progress ) {
            $progress_label = 'Generating audio...';
            if ( $progress_current !== '' && $progress_total !== '' ) {
                $progress_label = sprintf( 'Generating audio... (%d/%d)', (int) $progress_current, (int) $progress_total );
            }
            ?>
            <div class="oetl-status">
                <span class="spinner is-active" style="float:none;margin:0 4px 0 0;"></span>
                <em><?php echo esc_html( $progress_label ); ?></em>
            </div>
            <?php
        } elseif ( $attachment_id ) {
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
        echo '</div>'; // .oetl-dynamic-content
    }

    public function save_voice_override( $post_id, $post ) {
        if ( ! isset( $_POST['oetl_voice_meta_nonce'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( $_POST['oetl_voice_meta_nonce'], 'oetl_voice_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $voice_id = isset( $_POST['oetl_voice_id_override'] ) ? sanitize_text_field( $_POST['oetl_voice_id_override'] ) : '';

        if ( empty( $voice_id ) ) {
            delete_post_meta( $post_id, '_oetl_voice_id' );
        } else {
            update_post_meta( $post_id, '_oetl_voice_id', $voice_id );
        }
    }
}
