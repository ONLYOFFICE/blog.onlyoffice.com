<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OETL_GraphQL {

    public function __construct() {
        add_action( 'graphql_register_types', array( $this, 'register_fields' ) );
    }

    public function register_fields() {
        register_graphql_field( 'Post', 'audioUrl', array(
            'type'        => 'String',
            'description' => 'URL to the audio version of this post, generated via ElevenLabs TTS.',
            'resolve'     => function ( $post ) {
                $attachment_id = get_post_meta( $post->ID, '_oetl_audio_attachment_id', true );
                if ( ! empty( $attachment_id ) ) {
                    $url = wp_get_attachment_url( $attachment_id );
                    return $url ?: '';
                }
                return '';
            },
        ) );
    }
}
