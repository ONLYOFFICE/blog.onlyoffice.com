<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OAIS_Frontend {

    public function __construct() {
        add_filter( 'the_content', array( $this, 'render_summary' ), 6 );
    }

    public function render_summary( $content ) {
        // Skip feeds, admin screens, REST responses where $content is previewed mid-edit.
        if ( is_feed() || is_admin() ) {
            return $content;
        }

        $post_id = get_the_ID();
        if ( ! $post_id ) {
            $post = get_post();
            $post_id = $post ? $post->ID : 0;
        }
        if ( ! $post_id ) {
            return $content;
        }

        $post_types = get_option( 'oais_post_types', array( 'post' ) );
        if ( ! is_array( $post_types ) || ! in_array( get_post_type( $post_id ), $post_types, true ) ) {
            return $content;
        }

        $summary = (string) get_post_meta( $post_id, OAIS_META_SUMMARY, true );

        $lines = preg_split( "/\r\n|\n|\r/", $summary );
        $lines = array_map( array( $this, 'normalize_whitespace' ), $lines );
        $lines = array_values( array_filter( $lines, 'strlen' ) );
        if ( empty( $lines ) ) {
            return $content;
        }

        $title = (string) get_option( 'oais_section_title', 'Summary' );

        $html  = '<div class="leafio-aspc-section template-clean">';
        $html .= '<h2 class="summary-header">' . esc_html( $title ) . '</h2>';
        $html .= '<ul>';
        foreach ( $lines as $line ) {
            $html .= '<li>' . esc_html( $line ) . '</li>';
        }
        $html .= '</ul></div>';

        $position = get_option( 'oais_display_position', 'top' );
        return $position === 'bottom' ? $content . $html : $html . $content;
    }

    /**
     * Trim a line of all whitespace, including non-breaking spaces (U+00A0)
     * that can sneak in from copy-paste.
     */
    private function normalize_whitespace( $line ) {
        $line = preg_replace( '/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', (string) $line );
        return $line === null ? '' : $line;
    }
}
