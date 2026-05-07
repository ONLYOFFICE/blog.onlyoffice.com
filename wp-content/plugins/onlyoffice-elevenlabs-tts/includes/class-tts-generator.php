<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OETL_TTS_Generator {

    const API_BASE = 'https://api.elevenlabs.io/v1/text-to-speech/';
    const CHUNK_LIMIT = 4500;

    /**
     * Generate audio for a post and store it in the media library.
     *
     * @param int $post_id The post ID.
     * @return array|WP_Error On success: ['attachment_id' => int, 'duration' => float].
     *                        On failure: WP_Error.
     */
    public function generate( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post ) {
            return new WP_Error( 'invalid_post', 'Post not found.' );
        }

        $api_key = OETL_Admin_Settings::get_api_key();
        if ( empty( $api_key ) ) {
            return new WP_Error( 'no_api_key', 'ElevenLabs API key is not configured.' );
        }

        $voice_id = $this->get_voice_id( $post_id );
        if ( empty( $voice_id ) ) {
            return new WP_Error( 'no_voice_id', 'ElevenLabs Voice ID is not configured.' );
        }

        // Clean and prepare text
        $text = $this->prepare_text( $post->post_content );
        if ( empty( $text ) ) {
            return new WP_Error( 'empty_content', 'Post has no text content to convert.' );
        }

        // Pre-flight: verify we can write temp files before spending API tokens
        $temp_dir = get_temp_dir();
        if ( ! is_dir( $temp_dir ) ) {
            wp_mkdir_p( $temp_dir );
        }
        $upload_dir = wp_upload_dir();
        if ( ! is_writable( $temp_dir ) && ! is_writable( $upload_dir['path'] ) ) {
            return new WP_Error( 'write_error', 'Neither temp directory nor uploads directory is writable. Cannot save audio.' );
        }

        // Chunk text for API limits
        $chunks = $this->chunk_text( $text );
        $total  = count( $chunks );

        update_post_meta( $post_id, '_oetl_audio_progress_total', $total );
        wp_cache_delete( $post_id, 'post_meta' );

        // Generate audio for each chunk
        $audio_parts = array();
        foreach ( $chunks as $i => $chunk ) {
            update_post_meta( $post_id, '_oetl_audio_progress_current', $i + 1 );
            wp_cache_delete( $post_id, 'post_meta' );

            $audio = $this->call_api( $api_key, $voice_id, $chunk );
            if ( is_wp_error( $audio ) ) {
                return new WP_Error(
                    $audio->get_error_code(),
                    sprintf( 'Chunk %d/%d failed: %s', $i + 1, $total, $audio->get_error_message() )
                );
            }
            $audio_parts[] = $audio;
        }

        // Concatenate MP3 chunks
        $mp3_data = implode( '', $audio_parts );

        // Save to media library
        return $this->save_audio( $post_id, $post->post_name, $mp3_data );
    }

    /**
     * Re-process an already-stored audio attachment: rebuild its Xing header
     * via ffmpeg and refresh `_oetl_audio_duration`. Used to retroactively fix
     * files that were saved before the ffmpeg remux was added to the pipeline.
     *
     * @param int $post_id The post whose attached audio should be repaired.
     * @return array{ok: bool, message: string, duration?: float}
     */
    public function repair_attachment( $post_id ) {
        $attachment_id = get_post_meta( $post_id, '_oetl_audio_attachment_id', true );
        if ( empty( $attachment_id ) ) {
            return array( 'ok' => false, 'message' => 'No audio attached to this post.' );
        }

        $file_path = get_attached_file( $attachment_id );
        if ( ! $file_path || ! file_exists( $file_path ) ) {
            return array( 'ok' => false, 'message' => "Attachment file missing on disk: {$file_path}" );
        }
        if ( ! is_writable( $file_path ) ) {
            return array( 'ok' => false, 'message' => "Attachment file not writable: {$file_path}" );
        }

        $remuxed = $this->fix_mp3_xing( $file_path );
        if ( ! $remuxed ) {
            return array( 'ok' => false, 'message' => 'ffmpeg remux failed (see error_log).' );
        }

        $duration = $this->get_mp3_duration( $file_path );
        if ( $duration > 0 ) {
            update_post_meta( $post_id, '_oetl_audio_duration', $duration );
        }

        // Notify WordPress that the underlying file changed. WP Offload Media
        // (and similar offload plugins) listen to `wp_update_attachment_metadata`
        // and re-upload the file to S3 under the same object key — so the
        // bucket version gets overwritten, no duplicate files are created.
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $meta = wp_generate_attachment_metadata( $attachment_id, $file_path );
        if ( ! empty( $meta ) ) {
            wp_update_attachment_metadata( $attachment_id, $meta );
        }

        return array( 'ok' => true, 'message' => 'Repaired.', 'duration' => $duration );
    }

    /**
     * Remux a concatenated MP3 file through ffmpeg so it gets a single, accurate
     * Xing/Info header with a TOC. ElevenLabs returns one Xing-headed MP3 per
     * chunk; concatenating them leaves the first chunk's TOC describing only its
     * own frames, which breaks seeking (browsers land in the middle of the file
     * when the user drags the slider to the end). `-c copy -write_xing 1`
     * remuxes without re-encoding and rebuilds the header.
     *
     * Returns true on success, false if ffmpeg is unavailable or fails. On
     * failure the original file is left untouched.
     */
    private function fix_mp3_xing( $file_path ) {
        $ffmpeg = $this->get_ffmpeg_path();
        if ( ! $ffmpeg ) {
            return false;
        }

        $output_path = $file_path . '.fixed.mp3';
        $cmd = sprintf(
            '%s -y -i %s -c copy -map 0:a -write_xing 1 -f mp3 %s 2>&1',
            escapeshellcmd( $ffmpeg ),
            escapeshellarg( $file_path ),
            escapeshellarg( $output_path )
        );

        $output = array();
        $code   = 0;
        @exec( $cmd, $output, $code );

        if ( $code !== 0 || ! file_exists( $output_path ) || filesize( $output_path ) === 0 ) {
            @unlink( $output_path );
            error_log( sprintf( 'OETL: ffmpeg remux failed (code %d): %s', $code, implode( "\n", $output ) ) );
            return false;
        }

        // Replace the original file with the fixed one
        if ( ! @unlink( $file_path ) ) {
            @unlink( $output_path );
            return false;
        }
        if ( ! @rename( $output_path, $file_path ) ) {
            return false;
        }

        return true;
    }

    /**
     * Get duration in seconds from an MP3 file via getID3 (bundled with WP).
     * Returns 0.0 if it can't be determined.
     */
    private function get_mp3_duration( $file_path ) {
        if ( ! class_exists( 'getID3' ) ) {
            require_once ABSPATH . WPINC . '/ID3/getid3.php';
        }
        $getid3 = new getID3();
        $info   = $getid3->analyze( $file_path );
        if ( isset( $info['playtime_seconds'] ) && is_numeric( $info['playtime_seconds'] ) ) {
            return (float) $info['playtime_seconds'];
        }
        return 0.0;
    }

    /**
     * Resolve the ffmpeg binary. Honors the `oetl_ffmpeg_path` option, falls
     * back to PATH lookup. Returns null if unavailable.
     */
    private function get_ffmpeg_path() {
        $configured = get_option( 'oetl_ffmpeg_path', '' );
        if ( ! empty( $configured ) && file_exists( $configured ) ) {
            return $configured;
        }

        // PATH lookup
        $output = array();
        $code   = 0;
        @exec( 'ffmpeg -version 2>&1', $output, $code );
        if ( $code === 0 ) {
            return 'ffmpeg';
        }

        return null;
    }

    /**
     * Strip HTML, shortcodes, and clean text for TTS.
     */
    private function prepare_text( $content ) {
        // Remove shortcodes
        $text = strip_shortcodes( $content );

        // Remove script and style tags with their content
        $text = preg_replace( '/<(script|style)[^>]*>.*?<\/\1>/si', '', $text );

        // Remove image alt text artifacts and captions
        $text = preg_replace( '/\[caption[^\]]*\].*?\[\/caption\]/si', '', $text );

        // Remove iframe/embed/object tags
        $text = preg_replace( '/<(iframe|embed|object|video|audio)[^>]*>.*?<\/\1>/si', '', $text );
        $text = preg_replace( '/<(iframe|embed|object|video|audio)[^>]*\/>/si', '', $text );

        // Strip all HTML tags
        $text = wp_strip_all_tags( $text );

        // Remove URLs
        $text = preg_replace( '/https?:\/\/\S+/i', '', $text );

        // Decode HTML entities
        $text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

        // Normalize whitespace
        $text = preg_replace( '/[\t ]+/', ' ', $text );
        $text = preg_replace( '/\n{3,}/', "\n\n", $text );

        return trim( $text );
    }

    /**
     * Split text into chunks at sentence boundaries.
     */
    private function chunk_text( $text ) {
        if ( mb_strlen( $text ) <= self::CHUNK_LIMIT ) {
            return array( $text );
        }

        $chunks = array();
        $remaining = $text;

        while ( mb_strlen( $remaining ) > self::CHUNK_LIMIT ) {
            $piece = mb_substr( $remaining, 0, self::CHUNK_LIMIT );

            // Find last sentence boundary
            $last_period = $this->find_last_sentence_break( $piece );

            if ( $last_period !== false && $last_period > self::CHUNK_LIMIT * 0.3 ) {
                $chunks[] = trim( mb_substr( $remaining, 0, $last_period + 1 ) );
                $remaining = trim( mb_substr( $remaining, $last_period + 1 ) );
            } else {
                // Fallback: split at last space
                $last_space = mb_strrpos( $piece, ' ' );
                if ( $last_space !== false ) {
                    $chunks[] = trim( mb_substr( $remaining, 0, $last_space ) );
                    $remaining = trim( mb_substr( $remaining, $last_space ) );
                } else {
                    $chunks[] = trim( $piece );
                    $remaining = trim( mb_substr( $remaining, self::CHUNK_LIMIT ) );
                }
            }
        }

        if ( ! empty( $remaining ) ) {
            $chunks[] = $remaining;
        }

        return $chunks;
    }

    /**
     * Find the last sentence-ending position in a string.
     */
    private function find_last_sentence_break( $text ) {
        $best = false;

        // Look for sentence-ending patterns: ". ", "! ", "? ", ".\n", etc.
        $patterns = array( '. ', ".\n", '! ', "!\n", '? ', "?\n" );
        foreach ( $patterns as $pattern ) {
            $pos = mb_strrpos( $text, $pattern );
            if ( $pos !== false && ( $best === false || $pos > $best ) ) {
                $best = $pos;
            }
        }

        return $best;
    }

    /**
     * Call ElevenLabs TTS API.
     *
     * @param string $api_key  The API key.
     * @param string $voice_id The voice ID.
     * @param string $text     The text to convert.
     * @return string|WP_Error Raw MP3 data or error.
     */
    private function call_api( $api_key, $voice_id, $text ) {
        $model_id = get_option( 'oetl_model_id', 'eleven_multilingual_v2' );

        $body = wp_json_encode( array(
            'text'           => $text,
            'model_id'       => $model_id,
            'output_format'  => 'mp3_44100_128',
            'voice_settings' => array(
                'stability'        => 0.5,
                'similarity_boost' => 0.75,
            ),
        ) );

        $response = wp_remote_post( self::API_BASE . $voice_id, array(
            'timeout' => 120,
            'headers' => array(
                'xi-api-key'   => $api_key,
                'Content-Type' => 'application/json',
                'Accept'       => 'audio/mpeg',
            ),
            'body' => $body,
        ) );

        if ( is_wp_error( $response ) ) {
            $msg = $response->get_error_message();
            if ( strpos( $msg, 'timed out' ) !== false || strpos( $msg, 'cURL error 28' ) !== false ) {
                return new WP_Error( 'api_timeout', 'ElevenLabs request timed out. The text chunk may be too long.' );
            }
            return new WP_Error( 'api_request_failed', $msg );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body_raw = wp_remote_retrieve_body( $response );

        if ( $code !== 200 ) {
            $error_data = json_decode( $body_raw, true );
            $error_msg = isset( $error_data['detail']['message'] )
                ? $error_data['detail']['message']
                : ( isset( $error_data['detail'] ) && is_string( $error_data['detail'] )
                    ? $error_data['detail']
                    : "HTTP {$code}" );
            return new WP_Error( 'api_error', "ElevenLabs API error: {$error_msg}" );
        }

        if ( empty( $body_raw ) ) {
            return new WP_Error( 'api_empty_response', 'Empty response from ElevenLabs API.' );
        }

        return $body_raw;
    }

    /**
     * Save audio data to WordPress media library.
     *
     * @param int    $post_id   The post ID.
     * @param string $slug      The post slug for filename.
     * @param string $mp3_data  Raw MP3 binary data.
     * @return array|WP_Error On success: ['attachment_id' => int, 'duration' => float].
     */
    private function save_audio( $post_id, $slug, $mp3_data ) {
        // Delete old audio attachment if exists
        $old_attachment_id = get_post_meta( $post_id, '_oetl_audio_attachment_id', true );
        if ( $old_attachment_id ) {
            wp_delete_attachment( $old_attachment_id, true );
        }

        // Ensure temp directory exists
        $temp_dir = get_temp_dir();
        if ( ! is_dir( $temp_dir ) ) {
            wp_mkdir_p( $temp_dir );
        }

        // Write to temp file
        $filename = sanitize_file_name( $slug . '-audio.mp3' );
        $tmp_file = wp_tempnam( $filename );

        // Fallback: if temp file was not created, use uploads directory
        if ( ! $tmp_file || ! file_exists( $tmp_file ) ) {
            $upload_dir = wp_upload_dir();
            $tmp_file   = trailingslashit( $upload_dir['path'] ) . wp_unique_filename( $upload_dir['path'], $slug . '-audio.tmp' );
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_touch
            @touch( $tmp_file );
            if ( ! file_exists( $tmp_file ) ) {
                return new WP_Error( 'temp_file_error', 'Could not create temporary file in temp or uploads directory.' );
            }
        }

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        $bytes_written = file_put_contents( $tmp_file, $mp3_data );
        if ( false === $bytes_written || 0 === $bytes_written ) {
            @unlink( $tmp_file );
            return new WP_Error( 'temp_write_error', 'Could not write audio data to temporary file.' );
        }

        if ( ! is_readable( $tmp_file ) ) {
            @unlink( $tmp_file );
            return new WP_Error( 'temp_read_error', sprintf( 'Temporary file is not readable: %s', $tmp_file ) );
        }

        // Remux through ffmpeg to rebuild the Xing/TOC header on the
        // concatenated stream — without this, browsers seek incorrectly.
        $this->fix_mp3_xing( $tmp_file );

        // Read the (now-correct) duration from the file headers
        $duration = $this->get_mp3_duration( $tmp_file );

        // Prepare file array for media_handle_sideload
        $file_array = array(
            'name'     => $filename,
            'tmp_name' => $tmp_file,
        );

        // Sideload into media library
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_handle_sideload( $file_array, $post_id, sprintf( 'Audio for: %s', get_the_title( $post_id ) ) );

        // Clean up temp file if sideload failed
        if ( is_wp_error( $attachment_id ) ) {
            @unlink( $tmp_file );
            return $attachment_id;
        }

        return array(
            'attachment_id' => $attachment_id,
            'duration'      => $duration,
        );
    }

    /**
     * Get the voice ID for a post, considering WPML language.
     */
    private function get_voice_id( $post_id ) {
        // 1. Per-post override
        $override = get_post_meta( $post_id, '_oetl_voice_id', true );
        if ( ! empty( $override ) ) {
            return $override;
        }

        // 2. Language-specific voice via WPML
        if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
            $lang_details = apply_filters( 'wpml_post_language_details', null, $post_id );
            if ( is_array( $lang_details ) && ! empty( $lang_details['language_code'] ) ) {
                $voice = OETL_Admin_Settings::get_voice_id_for_language( $lang_details['language_code'] );
                if ( ! empty( $voice ) ) {
                    return $voice;
                }
            }
        }

        // 3. Fallback to English (default)
        return OETL_Admin_Settings::get_voice_id_for_language( 'en' );
    }
}
