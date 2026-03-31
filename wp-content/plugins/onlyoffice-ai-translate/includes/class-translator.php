<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OAIT_Translator {

    const API_ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    const LANGUAGES = array(
        'fr'      => 'Français',
        'de'      => 'Deutsch',
        'es'      => 'Español',
        'pt-br'   => 'Português',
        'it'      => 'Italiano',
        'cs'      => 'Čeština',
        'ja'      => '日本語',
        'zh-hans' => '中文',
        'el'      => 'Ελληνικά',
        'hi'      => 'हिन्दी',
        'ar'      => 'عربي',
        'sr'      => 'Srpski',
        'hy'      => 'Հայերেն',
    );

    /**
     * Per-language product name localization rules.
     * Only languages where product names need localization are listed.
     */
    const PRODUCT_LOCALIZATION = array(
        'zh-hans' => "Ensure that ONLYOFFICE product names are correctly localized for the Chinese market:
- ONLYOFFICE Docs → ONLYOFFICE 文档
- ONLYOFFICE DocSpace → ONLYOFFICE 协作空间
- Enterprise (edition) → 企业版
- Developer (edition) → 开发者版
- Community (edition) → 社区版
- ONLYOFFICE Desktop Editors → 桌面编辑器
- Document Server → 文档服务器
- Document Editor → 文档编辑器
- Spreadsheet Editor → 电子表格编辑器
- Presentation Editor → 演示文稿编辑器
- Keep ONLYOFFICE, DocSpace, Workspace, Desktop Editors in Latin script inline with Chinese text.",

        'ja' => "Ensure that ONLYOFFICE product names are correctly localized for the Japanese market:
- Enterprise (edition) → エンタープライズ版
- Developer (edition) → デベロッパー版
- Community (edition) → コミュニティ版
- ONLYOFFICE Desktop Editors → ONLYOFFICEデスクトップエディター
- Document Editor → ドキュメントエディター
- Spreadsheet Editor → スプレッドシートエディター
- Presentation Editor → プレゼンテーションエディター
- PDF Editor → PDFエディター
- Keep ONLYOFFICE, DocSpace, Workspace, Desktop Editors in Latin script.
- Use Japanese symbols where needed: \":\" → \"：\", \"()\" → \"（）\".",

        'es' => "Product name localization for Spanish:
- Document Server → Servidor de documentos
- Edition names (Enterprise, Developer, Community) stay in English.
- The word \"plugin\" should be translated as \"plugin\". When it comes to the Plugins tab, the correct translation is \"Pestaña Extensiones\".",

        'de' => "Product name localization for German:
- Editor names use compound nouns: Document Editor → Dokumenteneditor, Spreadsheet Editor → Tabellenkalkulationseditor, Presentation Editor → Präsentationseditor.
- Edition names stay in English: Community Edition, Developer Edition, Enterprise Edition — do NOT translate.",

        'fr' => "Product name localization for French:
- Editor names: Document Editor → éditeur de documents, Spreadsheet Editor → éditeur de classeurs, Presentation Editor → éditeur de présentations, PDF Editor → éditeur de PDF.
- Edition types: Community Edition → Édition Communauté, Developer Edition → Édition Développeur, Enterprise Edition → Édition Enterprise.
- \"Edition\" → \"Édition\" (with accent).",

        'pt-br' => "Product name localization for Brazilian Portuguese:
- Editor names: Document Editor → editor de documentos, Spreadsheet Editor → editor de planilhas, Presentation Editor → editor de apresentações, PDF Editor → editor de PDF.
- Edition names stay in English: Community Edition, Developer Edition, Enterprise Edition.",

        'it' => "Product name localization for Italian:
- Edition names stay in English: Community Edition, Developer Edition, Enterprise Edition.",
    );

    /**
     * Per-language style and grammar rules.
     */
    const LOCALE_RULES = array(
        'fr' => "- Use \"plug-ins\" (hyphenated), not \"plugins\".
- Titles use sentence case (capitalize only first word and proper nouns).
- Use infinitive verbs in titles: \"Adding\" → \"Ajouter\", \"Installing\" → \"Installer\", \"Configuring\" → \"Configurer\".
- Use French elision: l'éditeur, d'ONLYOFFICE, etc.
- Tab names: \"X tab\" → \"Onglet [TranslatedName]\" (e.g. \"File tab\" → \"Onglet Fichier\").",

        'de' => "- All nouns must be capitalized (standard German grammar).
- Use a neutral, professional tone. Avoid slang and colloquial expressions.
- Titles use nominalized verbs: \"Adding\" → \"Hinzufügen\", \"Installing\" → \"Installation von\", \"Configuring\" → \"Konfigurieren\".
- Tab names: \"X tab\" → \"Registerkarte [TranslatedName]\" (e.g. \"File tab\" → \"Registerkarte Datei\").",

        'es' => "- Titles use sentence case.
- Tab names: \"X tab\" → \"Pestaña [TranslatedName]\" (e.g. \"File tab\" → \"Pestaña de archivo\").",

        'ja' => "- Use Japanese symbols where needed: \":\" → \"：\", \"()\" → \"（）\".
- Tab names: \"X tab\" → \"[TranslatedName]タブ\" (e.g. \"File tab\" → \"ファイルタブ\").
- Titles use natural Japanese phrasing with nominalized verbs ending in 〜方 or 〜する方法 (e.g. \"Adding\" → \"追加する方法\").",

        'pt-br' => "- Use Brazilian Portuguese spelling and vocabulary (e.g. \"você\" not \"tu\", \"arquivo\" not \"ficheiro\").
- Titles use sentence case (capitalize only first word and proper nouns).
- Tab names: \"X tab\" → \"Guia [TranslatedName]\" (e.g. \"File tab\" → \"Guia Arquivo\").
- Use infinitive or nominalized verbs in titles: \"Adding\" → \"Adicionando\" or \"Como adicionar\".",

        'it' => "- Titles use sentence case.
- Tab names: \"X tab\" → \"Scheda [TranslatedName]\" (e.g. \"File tab\" → \"Scheda File\").",

        'zh-hans' => "- Keep ONLYOFFICE, DocSpace, Workspace, Desktop Editors in Latin script inline with Chinese text.
- Use terminology commonly understood in the IT and office software industry, same as WPS Office and Microsoft Office where applicable.",

        'ar' => "- Maintain right-to-left text direction awareness.
- Use Modern Standard Arabic for professional/technical content.",

        'hi' => "- Use Devanagari script consistently.
- Technical terms that are commonly used in English may be kept in Latin script where natural.",
    );

    /**
     * Translate a post to the target language.
     *
     * @param int    $post_id         The source post ID.
     * @param string $target_lang_code WPML language code.
     * @return array|WP_Error Translated fields or error.
     */
    public function translate( $post_id, $target_lang_code ) {
        $post = get_post( $post_id );
        if ( ! $post ) {
            return new WP_Error( 'invalid_post', 'Post not found.' );
        }

        $language_name = isset( self::LANGUAGES[ $target_lang_code ] )
            ? self::LANGUAGES[ $target_lang_code ]
            : $target_lang_code;

        $title        = $post->post_title;
        $content      = $post->post_content;
        $excerpt      = $post->post_excerpt;
        $aioseo_title = get_post_meta( $post_id, '_aioseo_title', true ) ?: '';
        $aioseo_desc  = get_post_meta( $post_id, '_aioseo_description', true ) ?: '';

        $system_prompt = $this->build_system_prompt( $language_name );
        $user_prompt   = $this->build_user_prompt( $target_lang_code, $language_name, $title, $content, $excerpt, $aioseo_title, $aioseo_desc );

        $response = $this->call_api( $system_prompt, $user_prompt );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $parsed = $this->parse_response( $response );
        if ( is_wp_error( $parsed ) ) {
            return $parsed;
        }

        return $parsed;
    }

    /**
     * Build the system prompt with universal translation rules.
     */
    private function build_system_prompt( $language_name ) {
        return "You are a professional translator for ONLYOFFICE — a software company producing office productivity tools.
Translate blog post content from English to {$language_name}.

## Universal rules (apply to ALL locales):

### Never translate these — keep exactly as-is:
- Brand name: ONLYOFFICE (always all-caps, never translated)
- Product names: DocSpace, Docs, Desktop Editors, Workspace, Document Builder
- Third-party product names: Docker, Docker Compose, Linux, Windows, macOS, iOS, Android, Ubuntu, Debian, CentOS, RHEL, KylinOS, snap
- Technical terms: JWT, HTTPS, SSL, API, ARM, ARM64, AGPL
- Cloud/hosting platforms: Amazon S3, DigitalOcean, Cloudron, Alibaba Cloud, Vultr, Linode
- Integration connector names: Nextcloud, ownCloud, WordPress, Confluence, SharePoint, Jira, Moodle, Alfresco, HumHub, Mattermost, Odoo, Pipedrive, SuiteCRM and other third-party brands
- Database names: MySQL, PostgreSQL, MsSQL, Oracle, Redis
- Plugin names: PhotoEditor, Mendeley, Zotero
- URLs, email addresses, code blocks

### HTML rules:
- Preserve ALL HTML tags, attributes (class, id, href, src, style, data-*, etc.) EXACTLY as they are.
- ONLY translate the visible text content between tags.
- Do NOT modify any tag names, attribute names, or attribute values.

### Blog-specific rules:
- Keep the same professional blog tone
- Translate naturally for the target audience, not word-by-word
- Maintain technical accuracy while making the content easy to understand
- Avoid direct, overly literal translations — rephrase where necessary to match local language habits
- Do NOT invent, infer, or reconstruct content for empty fields";
    }

    /**
     * Build the user prompt with language-specific rules and content.
     */
    private function build_user_prompt( $lang_code, $language_name, $title, $content, $excerpt, $aioseo_title, $aioseo_desc ) {
        $prompt = "Translate the following blog post fields from English to {$language_name}.\n\n";

        // Add product localization rules if available
        if ( isset( self::PRODUCT_LOCALIZATION[ $lang_code ] ) ) {
            $prompt .= "## Product name localization for {$language_name}:\n";
            $prompt .= self::PRODUCT_LOCALIZATION[ $lang_code ] . "\n\n";
        }

        // Add locale-specific style rules if available
        if ( isset( self::LOCALE_RULES[ $lang_code ] ) ) {
            $prompt .= "## Locale-specific rules for {$language_name}:\n";
            $prompt .= self::LOCALE_RULES[ $lang_code ] . "\n\n";
        }

        // Field instructions
        $prompt .= "## Output format:\n";
        $prompt .= "- Each field is separated by ---FIELD:fieldname--- markers\n";
        $prompt .= "- Return the translation with the SAME markers, preserving the exact field structure\n";
        $prompt .= "- Return ONLY the translated fields with markers, no explanations or extra text\n";
        $prompt .= "- If a field is empty, return the marker with empty content\n\n";

        // Fields to translate
        $prompt .= "---FIELD:title---\n{$title}\n";
        $prompt .= "---FIELD:content---\n{$content}\n";
        $prompt .= "---FIELD:excerpt---\n{$excerpt}\n";
        $prompt .= "---FIELD:aioseoTitle---\n{$aioseo_title}\n";
        $prompt .= "---FIELD:aioseoDescription---\n{$aioseo_desc}";

        return $prompt;
    }

    /**
     * Call the OpenAI API.
     *
     * @param string $system_prompt The system message.
     * @param string $user_prompt   The user message with content to translate.
     * @return string|WP_Error The response text or error.
     */
    private function call_api( $system_prompt, $user_prompt ) {
        $api_key = OAIT_Admin_Settings::get_api_key();
        if ( empty( $api_key ) ) {
            return new WP_Error( 'no_api_key', 'OpenAI API key is not configured.' );
        }

        $model = get_option( 'oait_model', 'gpt-4o-mini' );

        $body = wp_json_encode( array(
            'model'      => $model,
            'max_tokens' => 16000,
            'messages'   => array(
                array(
                    'role'    => 'system',
                    'content' => $system_prompt,
                ),
                array(
                    'role'    => 'user',
                    'content' => $user_prompt,
                ),
            ),
        ) );

        $response = wp_remote_post( self::API_ENDPOINT, array(
            'timeout' => 300,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ),
            'body' => $body,
        ) );

        if ( is_wp_error( $response ) ) {
            $msg = $response->get_error_message();
            if ( strpos( $msg, 'timed out' ) !== false || strpos( $msg, 'cURL error 28' ) !== false ) {
                return new WP_Error( 'api_timeout', 'Translation request timed out. The post may be too long for this language.' );
            }
            return new WP_Error( 'api_request_failed', $msg );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        if ( $code !== 200 ) {
            $error_data = json_decode( $body, true );
            $error_msg  = isset( $error_data['error']['message'] )
                ? $error_data['error']['message']
                : "HTTP {$code}";
            return new WP_Error( 'api_error', "OpenAI API error: {$error_msg}" );
        }

        $data = json_decode( $body, true );
        if ( ! isset( $data['choices'][0]['message']['content'] ) ) {
            return new WP_Error( 'api_empty_response', 'Empty response from OpenAI API.' );
        }

        return $data['choices'][0]['message']['content'];
    }

    /**
     * Parse the API response into structured fields.
     *
     * @param string $response_text The raw response text.
     * @return array|WP_Error Parsed fields or error.
     */
    public function parse_response( $response_text ) {
        $fields = array();

        if ( preg_match_all( '/---FIELD:(\w+)---\s*([\s\S]*?)(?=---FIELD:|\z)/', $response_text, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                $fields[ $match[1] ] = trim( $match[2] );
            }
        }

        if ( empty( $fields['title'] ) || empty( $fields['content'] ) ) {
            return new WP_Error(
                'parse_error',
                'Failed to parse translation response. Missing required fields (title, content).'
            );
        }

        return array(
            'title'             => $fields['title'],
            'content'           => $fields['content'],
            'excerpt'           => isset( $fields['excerpt'] ) ? $fields['excerpt'] : '',
            'aioseoTitle'       => isset( $fields['aioseoTitle'] ) ? $fields['aioseoTitle'] : '',
            'aioseoDescription' => isset( $fields['aioseoDescription'] ) ? $fields['aioseoDescription'] : '',
        );
    }
}
