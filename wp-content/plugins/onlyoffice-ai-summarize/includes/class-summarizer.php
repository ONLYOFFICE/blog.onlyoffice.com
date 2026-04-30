<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OAIS_Summarizer {

    const API_ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    const LANGUAGES = array(
        'en'       => 'English',
        'fr'       => 'French',
        'de'       => 'German',
        'es'       => 'Spanish',
        'it'       => 'Italian',
        'ja'       => 'Japanese',
        'ko'       => 'Korean',
        'zh-hans'  => 'Chinese (Simplified)',
        'pt-br'    => 'Portuguese (Brazil)',
        'pt-pt'    => 'Portuguese (Portugal)',
        'tr'       => 'Turkish',
        'ru'       => 'Russian',
        'cs'       => 'Czech',
        'pl'       => 'Polish',
        'nl'       => 'Dutch',
    );

    /**
     * Generate a bullet-point summary for a post.
     *
     * @param int $post_id
     * @return string|WP_Error Plain text: one bullet per line (no markers).
     */
    public function generate( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post ) {
            return new WP_Error( 'invalid_post', 'Post not found.' );
        }

        $title   = trim( (string) $post->post_title );
        $content = $this->prepare_content( (string) $post->post_content );

        if ( $content === '' ) {
            return new WP_Error( 'empty_content', 'Post content is empty.' );
        }

        // Hard cap to keep token usage predictable.
        if ( mb_strlen( $content ) > 15000 ) {
            $content = mb_substr( $content, 0, 15000 ) . '…';
        }

        $max_words = (int) get_option( 'oais_max_words', 80 );
        if ( $max_words < 20 )  { $max_words = 20;  }
        if ( $max_words > 500 ) { $max_words = 500; }

        $system_prompt = "You are a technical writer for ONLYOFFICE — a software company producing office productivity tools (DocSpace, Docs, Desktop Editors, Workspace, Document Builder).

Your task: given a blog post H1 title (which poses an explicit or implicit question) and the post content, write a Summary that directly answers that question.

Structure:
1. An intro paragraph of 2–3 sentences, 40–{$max_words} words total, that answers the title's question in prose.
2. OPTIONALLY, a bulleted list of 3–5 bullets. Each bullet is ONE sentence of 5–10 words. Add bullets only if they genuinely help the reader scan key facts; otherwise return only the paragraph.

Rules:
- Do NOT invent facts that are not in the content.
- Never translate brand names: ONLYOFFICE (all-caps), DocSpace, Docs, Desktop Editors, Workspace, Document Builder, and third-party names (WordPress, Linux, Windows, macOS, etc.).

Output format:
- Paragraph lines have NO prefix.
- Bullet lines start with '- ' (dash + space). Do NOT use '*', '•', '·', or numbering.
- Separate the paragraph from the bullet list with exactly one empty line.
- If you choose not to add bullets, return only the paragraph — no trailing empty line, no stray markers.
- Do NOT add a heading, preface, or trailing commentary.";

        $user_prompt = "H1 Title: {$title}\n\nPost content:\n{$content}";

        $response = $this->call_api( $system_prompt, $user_prompt, 800 );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return $this->normalize_lines( $response );
    }

    /**
     * Translate already-prepared summary text (bullets separated by newlines)
     * to the target language, preserving line structure.
     *
     * @param string $text
     * @param string $target_lang_code WPML language code (fr, de, ...).
     * @return string|WP_Error
     */
    public function translate_text( $text, $target_lang_code ) {
        $text = trim( (string) $text );
        if ( $text === '' ) {
            return new WP_Error( 'empty_text', 'Summary text is empty.' );
        }

        $language_name = isset( self::LANGUAGES[ $target_lang_code ] )
            ? self::LANGUAGES[ $target_lang_code ]
            : $target_lang_code;

        $system_prompt = "You are a professional translator for ONLYOFFICE — a software company producing office productivity tools.
Translate the following summary from English to {$language_name}.

Input format:
- Each input line is one of two types:
  (a) a paragraph line — NO prefix;
  (b) a bullet line — starts with '- ' (dash + space).
- Empty lines are separators between the paragraph and the bullet list.

Rules:
- Preserve the line structure EXACTLY: input lines count must equal output lines count, in the same order, with empty lines kept in place.
- For every bullet line, keep the leading '- ' prefix UNCHANGED in the translation; translate only the text after it.
- For paragraph lines, translate the text without adding any prefix.
- Do NOT add, remove, merge or split lines. Do NOT add '*', '•', or numbering.
- Never translate brand names: ONLYOFFICE (all-caps), DocSpace, Docs, Desktop Editors, Workspace, Document Builder.
- Never translate third-party product or technology names: WordPress, Linux, Windows, macOS, Android, iOS, Docker, MySQL, PostgreSQL, etc.
- Translate naturally for the target audience — avoid literal word-by-word translation.
- Return ONLY the translated lines, nothing else.";

        $user_prompt = $text;

        $response = $this->call_api( $system_prompt, $user_prompt, 1200 );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return $this->normalize_lines( $response );
    }

    /**
     * Call OpenAI chat/completions endpoint.
     *
     * @param string $system_prompt
     * @param string $user_prompt
     * @param int    $max_tokens
     * @return string|WP_Error Raw assistant message content.
     */
    private function call_api( $system_prompt, $user_prompt, $max_tokens = 1000 ) {
        $api_key = OAIS_Admin_Settings::get_api_key();
        if ( empty( $api_key ) ) {
            return new WP_Error( 'no_api_key', 'OpenAI API key is not configured. Set it in Settings → AI Summarize.' );
        }

        $model = get_option( 'oais_model', 'gpt-4o-mini' );

        $body = wp_json_encode( array(
            'model'       => $model,
            'max_tokens'  => (int) $max_tokens,
            'temperature' => 0.3,
            'messages'    => array(
                array( 'role' => 'system', 'content' => $system_prompt ),
                array( 'role' => 'user',   'content' => $user_prompt ),
            ),
        ) );

        $response = wp_remote_post( self::API_ENDPOINT, array(
            'timeout' => 120,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ),
            'body'    => $body,
        ) );

        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'api_request_failed', $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        if ( $code !== 200 ) {
            $error_data = json_decode( $body, true );
            $error_msg  = isset( $error_data['error']['message'] ) ? $error_data['error']['message'] : "HTTP {$code}";
            return new WP_Error( 'api_error', "OpenAI API error: {$error_msg}" );
        }

        $data = json_decode( $body, true );
        if ( ! isset( $data['choices'][0]['message']['content'] ) ) {
            return new WP_Error( 'api_empty_response', 'Empty response from OpenAI API.' );
        }

        return (string) $data['choices'][0]['message']['content'];
    }

    /**
     * Clean post content before sending to the model:
     *   - expand and then strip shortcodes (so a plain-text fallback remains when one exists)
     *   - drop <script>, <style> and every HTML tag with its attributes
     *   - drop HTML comments including Gutenberg block markers
     *   - decode HTML entities (&amp;, &nbsp;, &mdash;, …) into real characters
     *   - collapse runs of whitespace into single spaces
     *
     * @param string $raw
     * @return string
     */
    private function prepare_content( $raw ) {
        $text = (string) $raw;
        $text = strip_shortcodes( $text );
        $text = wp_strip_all_tags( $text, true );
        $text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        $text = preg_replace( '/\s+/u', ' ', $text );
        return trim( (string) $text );
    }

    /**
     * Normalize model output into the storage format:
     *   - Paragraph lines stay unprefixed.
     *   - Bullet lines keep a single unified "- " prefix (alternative markers
     *     like "*", "•", "·", "▪", "‣", "◦" or "1." / "1)" are rewritten to "- ").
     *   - Strip blank lines at the top/bottom; keep AT MOST one blank line
     *     between paragraph and bullet list (collapses runs of blanks).
     *
     * @param string $text
     * @return string
     */
    private function normalize_lines( $text ) {
        $lines = preg_split( "/\r\n|\n|\r/", (string) $text );
        $clean = array();
        $prev_blank = true; // treat start of buffer as "after a blank" so leading blanks are dropped
        foreach ( $lines as $line ) {
            $line = preg_replace( '/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', (string) $line );
            if ( $line === '' ) {
                if ( ! $prev_blank ) {
                    $clean[]    = '';
                    $prev_blank = true;
                }
                continue;
            }
            if ( preg_match( '/^(?:[-*•·‣▪◦]|\d+[\.\)])\s+(.*)$/u', $line, $m ) ) {
                $body = trim( $m[1] );
                if ( $body !== '' ) {
                    $clean[]    = '- ' . $body;
                    $prev_blank = false;
                }
            } else {
                $clean[]    = $line;
                $prev_blank = false;
            }
        }
        // Drop a trailing blank line if it slipped through.
        while ( ! empty( $clean ) && end( $clean ) === '' ) {
            array_pop( $clean );
        }
        return implode( "\n", $clean );
    }
}
