<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OAIT_Bulk_Actions {

    public function __construct() {
        // Meta box with translation status
        add_action( 'add_meta_boxes', array( $this, 'add_translation_meta_box' ) );
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
     * Render translation status meta box with language checkboxes.
     */
    public function render_translation_meta_box( $post ) {
        if ( $post->post_type !== 'post' ) {
            echo '<p>Not applicable.</p>';
            return;
        }

        // Determine the post language
        $lang_details = apply_filters( 'wpml_post_language_details', null, $post->ID );
        if ( $lang_details && ! empty( $lang_details['language_code'] ) ) {
            $post_lang = $lang_details['language_code'];
        } else {
            // For new/auto-draft posts, use the current admin language
            $post_lang = apply_filters( 'wpml_current_language', 'en' );
        }

        // Only show for English posts
        if ( $post_lang !== 'en' ) {
            echo '<p>Only English source posts can be translated.</p>';
            return;
        }

        // Check if post is saved as draft/published first
        if ( ! in_array( $post->post_status, array( 'publish', 'draft' ), true ) ) {
            echo '<p>Save the post as a draft first to enable AI translation.</p>';
            return;
        }

        $wpml        = new OAIT_WPML_Integration();
        $status      = $wpml->get_translation_status( $post->ID );
        $enabled     = get_option( 'oait_enabled_languages', array() );
        $in_progress = get_post_meta( $post->ID, '_ai_translation_in_progress', true );
        if ( ! is_array( $in_progress ) ) {
            $in_progress = array();
        }

        // Select all checkbox
        echo '<label style="display:block;margin:4px 0 8px;font-weight:600;"><input type="checkbox" id="oait_metabox_select_all" /> Select all</label>';

        echo '<ul style="margin:0;" class="oait-language-list">';
        foreach ( OAIT_Translator::LANGUAGES as $code => $name ) {
            $is_enabled    = empty( $enabled ) || in_array( $code, $enabled, true );
            if ( ! $is_enabled ) {
                continue; // Skip disabled languages
            }

            $translated_id = isset( $status[ $code ] ) ? $status[ $code ] : null;
            $is_in_progress = in_array( $code, $in_progress, true );
            $label = esc_html( $name ) . ' (' . esc_html( $code ) . ')';

            if ( $translated_id ) {
                // Already translated: checkmark + edit link
                $edit_link = get_edit_post_link( $translated_id );
                $link_label = '<a href="' . esc_url( $edit_link ) . '">' . $label . '</a>';
                printf(
                    '<li style="padding:2px 0;"><span style="color:#00a32a;">&#10004;</span> %s</li>',
                    $link_label
                );
            } elseif ( $is_in_progress ) {
                // In progress: spinner
                printf(
                    '<li style="padding:2px 0;"><span class="spinner is-active" style="float:none;margin:0 4px 0 0;"></span> %s <em style="color:#999;">translating...</em></li>',
                    $label
                );
            } else {
                // Not translated: checkbox
                printf(
                    '<li style="padding:2px 0;"><label><input type="checkbox" class="oait-lang-checkbox" value="%s"> %s</label></li>',
                    esc_attr( $code ),
                    $label
                );
            }
        }
        echo '</ul>';

        // Translate button
        ?>
        <div style="margin-top:10px;">
            <button type="button" class="button button-primary oait-translate-btn"
                    data-post-id="<?php echo esc_attr( $post->ID ); ?>">
                Translate Selected
            </button>
            <span class="spinner oait-spinner" style="float:none;margin:0 4px;"></span>
        </div>
        <span class="oait-translate-status" style="display:none;margin-top:6px;"></span>
        <?php

        // Show errors from results
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
