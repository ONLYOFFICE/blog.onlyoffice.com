<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Integrates with the ONLYOFFICE AI Translate plugin.
 *
 * AI Translate fires two custom actions after it creates or updates a WPML
 * translation of a post:
 *   - do_action( 'oait_after_create_translation', $new_post_id, $original_post_id, $target_lang )
 *   - do_action( 'oait_after_update_translation', $translated_post_id, $target_lang )
 *
 * When the original post has an enabled AI Summary, we translate that summary
 * to the target language and save it on the translated post so the block
 * renders without a second round of manual work.
 */
class OAIS_Translator_Integration {

    public function __construct() {
        add_action( 'oait_after_create_translation', array( $this, 'on_translation_created' ), 10, 3 );
        add_action( 'oait_after_update_translation', array( $this, 'on_translation_updated' ), 10, 2 );
    }

    /**
     * Copy + translate summary from the original post to the newly created translation.
     *
     * @param int    $new_post_id      Newly created translated post.
     * @param int    $original_post_id Source post.
     * @param string $target_lang      WPML language code.
     */
    public function on_translation_created( $new_post_id, $original_post_id, $target_lang ) {
        $this->translate_for_post( $new_post_id, $original_post_id, $target_lang );
    }

    /**
     * On re-translation of an existing translated post, re-derive the summary
     * from the original (in case the writer updated the original's summary).
     *
     * @param int    $translated_post_id
     * @param string $target_lang
     */
    public function on_translation_updated( $translated_post_id, $target_lang ) {
        $original_post_id = (int) get_post_meta( $translated_post_id, '_ai_source_post_id', true );
        if ( ! $original_post_id ) {
            return;
        }
        $this->translate_for_post( $translated_post_id, $original_post_id, $target_lang );
    }

    private function translate_for_post( $translated_post_id, $original_post_id, $target_lang ) {
        $summary = (string) get_post_meta( $original_post_id, OAIS_META_SUMMARY, true );
        if ( trim( $summary ) === '' ) {
            return;
        }

        $summarizer = new OAIS_Summarizer();
        $translated = $summarizer->translate_text( $summary, $target_lang );

        if ( is_wp_error( $translated ) ) {
            error_log( sprintf(
                'OAIS: failed to translate summary (post %d → %s): %s',
                $original_post_id,
                $target_lang,
                $translated->get_error_message()
            ) );
            return;
        }

        update_post_meta( $translated_post_id, OAIS_META_SUMMARY, $translated );
        update_post_meta( $translated_post_id, OAIS_META_GENERATED_AT, current_time( 'mysql' ) );
    }
}
