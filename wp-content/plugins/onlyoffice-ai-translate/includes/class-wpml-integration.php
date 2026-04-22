<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OAIT_WPML_Integration {

    /**
     * Create a translated copy of a post via WPML.
     *
     * @param int    $original_post_id The source post ID (English).
     * @param array  $translated_data  Translated fields array.
     * @param string $target_lang      WPML language code.
     * @return int|WP_Error The new post ID or error.
     */
    public function create_translation( $original_post_id, $translated_data, $target_lang ) {
        // Validate that the target language is active in WPML
        $active_languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
        if ( is_array( $active_languages ) && ! isset( $active_languages[ $target_lang ] ) ) {
            return new WP_Error(
                'invalid_language',
                sprintf( 'Language "%s" is not configured in WPML.', $target_lang )
            );
        }

        // Step 1: Create an independent translation via WPML (not a synchronized duplicate)
        $new_post_id = apply_filters( 'wpml_copy_post_to_language', $original_post_id, $target_lang, false );

        if ( ! $new_post_id || is_wp_error( $new_post_id ) ) {
            return new WP_Error(
                'wpml_copy_failed',
                sprintf( 'WPML failed to copy post %d to language %s.', $original_post_id, $target_lang )
            );
        }

        // Step 2: Update the duplicated post with translated content
        $update_result = wp_update_post( array(
            'ID'           => $new_post_id,
            'post_title'   => $translated_data['title'],
            'post_content' => $translated_data['content'],
            'post_excerpt' => $translated_data['excerpt'],
            'post_status'  => 'draft',
        ), true );

        if ( is_wp_error( $update_result ) ) {
            return $update_result;
        }

        // Step 3: Update AIOSEO meta fields
        if ( ! empty( $translated_data['aioseoTitle'] ) ) {
            update_post_meta( $new_post_id, '_aioseo_title', $translated_data['aioseoTitle'] );
        }
        if ( ! empty( $translated_data['aioseoDescription'] ) ) {
            update_post_meta( $new_post_id, '_aioseo_description', $translated_data['aioseoDescription'] );
        }

        // Step 4: Update AIOSEO custom table if it exists
        $this->update_aioseo_table( $new_post_id, $translated_data );

        // Step 5: Mark as AI-translated
        update_post_meta( $new_post_id, '_ai_translated', true );
        update_post_meta( $new_post_id, '_ai_translated_date', current_time( 'mysql' ) );
        update_post_meta( $new_post_id, '_ai_source_post_id', $original_post_id );

        // Step 6: Clear copied TTS audio meta (each translation generates its own audio)
        delete_post_meta( $new_post_id, '_oetl_audio_attachment_id' );
        delete_post_meta( $new_post_id, '_oetl_audio_in_progress' );
        delete_post_meta( $new_post_id, '_oetl_audio_error' );
        delete_post_meta( $new_post_id, '_oetl_audio_generated_at' );

        // Step 7: Copy featured image
        $thumbnail_id = get_post_meta( $original_post_id, '_thumbnail_id', true );
        if ( $thumbnail_id ) {
            update_post_meta( $new_post_id, '_thumbnail_id', $thumbnail_id );
            update_post_meta( $new_post_id, '_wpml_media_featured', 1 );
            update_post_meta( $new_post_id, '_wpml_media_duplicate', 1 );
        }

        /**
         * Fires after a new WPML translation has been created and filled.
         * Extension hook for companion plugins (e.g. AI Summarize) to attach
         * translated side content to the new post.
         *
         * @param int    $new_post_id      The translated post ID.
         * @param int    $original_post_id The source post ID.
         * @param string $target_lang      WPML language code.
         */
        do_action( 'oait_after_create_translation', $new_post_id, $original_post_id, $target_lang );

        return $new_post_id;
    }

    /**
     * Get the existing translation post ID for a given post and language.
     *
     * @param int    $post_id   The source post ID.
     * @param string $lang_code WPML language code.
     * @return int|null The translated post ID or null.
     */
    public function get_existing_translation_id( $post_id, $lang_code ) {
        $trid = apply_filters( 'wpml_element_trid', null, $post_id, 'post_post' );
        if ( ! $trid ) {
            return null;
        }

        $translations = apply_filters( 'wpml_get_element_translations', null, $trid, 'post_post' );
        if ( ! is_array( $translations ) ) {
            return null;
        }

        if ( isset( $translations[ $lang_code ] ) && ! empty( $translations[ $lang_code ]->element_id ) ) {
            return (int) $translations[ $lang_code ]->element_id;
        }

        return null;
    }

    /**
     * Update an existing translated post with new translated content.
     *
     * @param int    $translated_post_id The existing translated post ID.
     * @param array  $translated_data    Translated fields array.
     * @param string $target_lang        WPML language code (optional, forwarded to extension hooks).
     * @return int|WP_Error The post ID or error.
     */
    public function update_translation( $translated_post_id, $translated_data, $target_lang = '' ) {
        $update_result = wp_update_post( array(
            'ID'           => $translated_post_id,
            'post_title'   => $translated_data['title'],
            'post_content' => $translated_data['content'],
            'post_excerpt' => $translated_data['excerpt'],
        ), true );

        if ( is_wp_error( $update_result ) ) {
            return $update_result;
        }

        // Update AIOSEO meta fields
        if ( ! empty( $translated_data['aioseoTitle'] ) ) {
            update_post_meta( $translated_post_id, '_aioseo_title', $translated_data['aioseoTitle'] );
        }
        if ( ! empty( $translated_data['aioseoDescription'] ) ) {
            update_post_meta( $translated_post_id, '_aioseo_description', $translated_data['aioseoDescription'] );
        }

        // Update AIOSEO custom table if it exists
        $this->update_aioseo_table( $translated_post_id, $translated_data );

        // Update AI metadata
        update_post_meta( $translated_post_id, '_ai_translated', true );
        update_post_meta( $translated_post_id, '_ai_translated_date', current_time( 'mysql' ) );

        /**
         * Fires after an existing WPML translation has been re-filled with new content.
         * Extension hook for companion plugins (e.g. AI Summarize) to refresh
         * translated side content.
         *
         * @param int    $translated_post_id The translated post ID.
         * @param string $target_lang        WPML language code (if available).
         */
        do_action( 'oait_after_update_translation', $translated_post_id, $target_lang );

        return $translated_post_id;
    }

    /**
     * Check if a translation already exists for a given post and language.
     *
     * @param int    $post_id   The source post ID.
     * @param string $lang_code WPML language code.
     * @return bool
     */
    public function translation_exists( $post_id, $lang_code ) {
        $trid = apply_filters( 'wpml_element_trid', null, $post_id, 'post_post' );
        if ( ! $trid ) {
            return false;
        }

        $translations = apply_filters( 'wpml_get_element_translations', null, $trid, 'post_post' );
        if ( ! is_array( $translations ) ) {
            return false;
        }

        return isset( $translations[ $lang_code ] ) && ! empty( $translations[ $lang_code ]->element_id );
    }

    /**
     * Get translation status for a post across all languages.
     *
     * @param int $post_id The source post ID.
     * @return array Language code => translated post ID (or null).
     */
    public function get_translation_status( $post_id ) {
        $trid = apply_filters( 'wpml_element_trid', null, $post_id, 'post_post' );
        if ( ! $trid ) {
            return array();
        }

        $translations = apply_filters( 'wpml_get_element_translations', null, $trid, 'post_post' );
        if ( ! is_array( $translations ) ) {
            return array();
        }

        $status = array();
        foreach ( OAIT_Translator::LANGUAGES as $code => $name ) {
            $status[ $code ] = isset( $translations[ $code ] ) && ! empty( $translations[ $code ]->element_id )
                ? (int) $translations[ $code ]->element_id
                : null;
        }

        return $status;
    }

    /**
     * Update AIOSEO custom table for the translated post.
     *
     * @param int   $post_id         The translated post ID.
     * @param array $translated_data Translated fields.
     */
    private function update_aioseo_table( $post_id, $translated_data ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'aioseo_posts';

        // Check if table exists
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) {
            return;
        }

        $existing = $wpdb->get_row(
            $wpdb->prepare( "SELECT id FROM {$table_name} WHERE post_id = %d", $post_id )
        );

        $data = array(
            'post_id'          => $post_id,
            'title'            => $translated_data['aioseoTitle'] ?: null,
            'description'      => $translated_data['aioseoDescription'] ?: null,
            'updated'          => current_time( 'mysql' ),
        );

        if ( $existing ) {
            $wpdb->update( $table_name, $data, array( 'post_id' => $post_id ) );
        } else {
            $data['created'] = current_time( 'mysql' );
            $wpdb->insert( $table_name, $data );
        }
    }
}
