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
     *
     * The markup produced here must stay in sync with renderMetaBox() in
     * assets/admin.js, which replaces it on every status poll.
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

        $payload = oait_build_status_payload( $post->ID );

        echo '<div class="oait-body">';
        echo self::render_body( $payload, $post->ID ); // phpcs:ignore WordPress.Security.EscapeOutput -- markup assembled with escaping below.
        echo '</div>';
    }

    /**
     * Build the metabox body from a status payload.
     *
     * @param array $payload Output of oait_build_status_payload().
     * @param int   $post_id Source post ID.
     * @return string
     */
    public static function render_body( array $payload, $post_id ) {
        $languages   = $payload['languages'];
        $has_actions = false;

        foreach ( $languages as $lang ) {
            // Every language that is not already translated can be selected —
            // including queued/running ones, which re-queue as a forced restart.
            // Before 1.1.0 an in-progress language rendered as a bare spinner,
            // so a job that never reported back left the metabox with nothing
            // to click and the post permanently "translating".
            if ( $lang['enabled'] && ! $lang['postId'] ) {
                $has_actions = true;
                break;
            }
        }

        $html = '';

        if ( $has_actions ) {
            $html .= '<label style="display:block;margin:4px 0 8px;font-weight:600;">'
                   . '<input type="checkbox" id="oait_metabox_select_all" /> Select all</label>';
        }

        $html .= '<ul style="margin:0;" class="oait-language-list">';

        foreach ( $languages as $code => $lang ) {
            if ( ! $lang['enabled'] ) {
                continue;
            }

            $label = esc_html( $lang['name'] ) . ' (' . esc_html( $code ) . ')';

            if ( $lang['postId'] ) {
                $link = $lang['editLink']
                    ? '<a href="' . esc_url( $lang['editLink'] ) . '">' . $label . '</a>'
                    : $label;

                $html .= '<li style="padding:2px 0;">'
                       . '<span style="color:#00a32a;">&#10004;</span> ' . $link
                       . '</li>';
                continue;
            }

            $checkbox = '<label><input type="checkbox" class="oait-lang-checkbox" value="'
                      . esc_attr( $code ) . '"> ' . $label . '</label>';

            if ( $lang['active'] ) {
                $note = 'queued' === $lang['status'] ? 'queued' : 'translating';
                if ( $lang['elapsed'] ) {
                    $note .= ' ' . self::format_elapsed( (int) $lang['elapsed'] );
                }

                $html .= '<li style="padding:2px 0;">'
                       . '<span class="spinner is-active" style="float:none;margin:0 4px 0 0;"></span> '
                       . $checkbox
                       . ' <em style="color:#999;font-size:11px;">(' . esc_html( $note ) . ')</em>'
                       . '</li>';
                continue;
            }

            if ( in_array( $lang['status'], array( 'error', 'cancelled' ), true ) && $lang['message'] ) {
                $colour = 'cancelled' === $lang['status'] ? '#996800' : '#d63638';

                $html .= '<li style="padding:2px 0;">'
                       . $checkbox
                       . ' <em style="color:' . esc_attr( $colour ) . ';font-size:11px;">('
                       . esc_html( $lang['message'] ) . ')</em>'
                       . '</li>';
                continue;
            }

            $html .= '<li style="padding:2px 0;">' . $checkbox . '</li>';
        }

        $html .= '</ul>';

        if ( $has_actions ) {
            $html .= '<div style="margin-top:10px;">'
                   . '<button type="button" class="button button-primary oait-translate-btn" data-post-id="'
                   . esc_attr( $post_id ) . '">Translate Selected</button> ';

            if ( $payload['active'] ) {
                $html .= '<button type="button" class="button oait-cancel-btn" data-post-id="'
                       . esc_attr( $post_id ) . '">Stop</button> ';
            }

            $html .= '<span class="spinner oait-spinner" style="float:none;margin:0 4px;"></span>'
                   . '</div>';
        }

        if ( $payload['active'] ) {
            $html .= '<p style="margin:8px 0 0;color:#999;font-size:11px;">'
                   . 'A language stuck for more than '
                   . esc_html( (string) round( $payload['staleTimeout'] / MINUTE_IN_SECONDS ) )
                   . ' min is released automatically and can be re-run.</p>';
        }

        $html .= '<span class="oait-translate-status" style="display:none;margin-top:6px;"></span>';

        return $html;
    }

    /**
     * Format an elapsed duration as "1m 05s" / "42s".
     *
     * @param int $seconds Elapsed seconds.
     * @return string
     */
    public static function format_elapsed( $seconds ) {
        if ( $seconds < MINUTE_IN_SECONDS ) {
            return $seconds . 's';
        }

        return sprintf( '%dm %02ds', intdiv( $seconds, MINUTE_IN_SECONDS ), $seconds % MINUTE_IN_SECONDS );
    }
}
