<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OAIS_Frontend {

    public function __construct() {
        add_filter( 'the_content', array( $this, 'render_summary' ), 6 );
    }

    public function render_summary( $content ) {
        if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }

        $post_id = get_the_ID();
        if ( ! $post_id ) {
            return $content;
        }

        $post_types = get_option( 'oais_post_types', array( 'post' ) );
        if ( ! is_array( $post_types ) || ! in_array( get_post_type( $post_id ), $post_types, true ) ) {
            return $content;
        }

        if ( ! get_post_meta( $post_id, OAIS_META_ENABLED, true ) ) {
            return $content;
        }

        $summary = (string) get_post_meta( $post_id, OAIS_META_SUMMARY, true );
        $summary = trim( $summary );
        if ( $summary === '' ) {
            return $content;
        }

        $lines = preg_split( "/\r\n|\n|\r/", $summary );
        $lines = array_values( array_filter( array_map( 'trim', $lines ), 'strlen' ) );
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
}
