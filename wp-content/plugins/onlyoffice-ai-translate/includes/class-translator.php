<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OAIT_Translator {

    const API_ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    /**
     * Seconds to wait for the API before giving up.
     *
     * Must stay below Action Scheduler's failure period (300s by default in AS
     * 3.9.3), which marks any action still running past that point as failed.
     * The previous 300s timeout hit both limits at the same instant: the task
     * was declared failed by the scheduler at the exact moment its own request
     * timed out, so the run ended with no usable error to show or retry.
     */
    const DEFAULT_REQUEST_TIMEOUT = 180;

    /** Hard bounds for the configurable request timeout. */
    const MIN_REQUEST_TIMEOUT = 30;
    const MAX_REQUEST_TIMEOUT = 280;

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
        'hy'      => 'Հայերեն',
    );

    /**
     * Per-language product name localization rules.
     * Only languages where product names need localization are listed.
     */
    const PRODUCT_LOCALIZATION = array(
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

        // Written from a translator's review of two published Greek articles.
        // Gender agreement was by far the most frequent defect, so it leads the
        // list. The wrong → right pairs are that review's actual examples and are
        // deliberately kept verbatim: naming the noun's gender is what stops the
        // model repeating them, a bare \"check agreement\" rule did not.
        'el' => "Greek output is reviewed before publication and these ten rules are the defects the reviewers actually found, ordered by severity. Rule 1 produces unpublishable text; rule 2 is the most frequent.

### 1. Script integrity — never mix alphabets inside one word
Every Greek word must be written entirely in Greek letters. NEVER emit Cyrillic characters. Never blend Greek with Latin, or Greek with Cyrillic, inside a single word. Observed failures — do not repeat them:
  \"μακ rosi\" → \"μακροεντολές\" (\"macros\"; this one appeared four times in a single article)
  \"εγ dokumentων\" → \"εγγράφων\" (\"documents\")
  \"Мюнстер\" written in Cyrillic → \"Μύνστερ\" (\"Munster\")
If a proper name or technical term has to be transliterated, transliterate the whole word into Greek script and use the same spelling everywhere. If a term must stay in English (rule 6), leave it entirely in Latin script. Half-and-half is never correct.

### 2. Gender AND case agreement
The article, any adjective and the noun must agree in BOTH grammatical gender and case. Loanwords are normally neuter; nouns in -ος are usually masculine; nouns in -ι, -μα and -ο are neuter; nouns in -η and -ση are feminine. Do not repeat these:
  \"η πρόβλημα\" → \"το πρόβλημα\" (πρόβλημα is neuter)
  \"στην πάνελ\" → \"στο πάνελ\" (πάνελ is neuter)
  \"της σχολιασμού\" → \"του σχολιασμού\" (σχολιασμός is masculine)
  \"μια εκπληκτική αριθμό\" → \"έναν εκπληκτικό αριθμό\" (αριθμός is masculine)
  \"Αυτή τη καλοκαίρι\" → \"Αυτό το καλοκαίρι\" (καλοκαίρι is neuter)
  \"εκπαιδευτικά εκπτώσεις\" → \"εκπαιδευτικές εκπτώσεις\" (εκπτώσεις is feminine plural)
  \"ευαίσθητοι περιεχόμενο\" → \"ευαίσθητο περιεχόμενο\" (περιεχόμενο is neuter)
Case in particular: do not slip into the genitive when the sentence does not call for it. \"ενσωμάτωση\" stays nominative or accusative unless the structure genuinely requires the genitive — it was wrongly written \"ενσωμάτωσης\" three times in one article, once directly beside a correctly declined coordinate noun in the same phrase. Also neuter: ίδρυμα.

### 3. Verb tense and mood consistency
When the source has a sequence of verbs sharing a tense or mood (parallel actions, or a chain of subjunctives), keep every verb of that sequence in the same tense and mood — do not let one drift:
  \"σαρώνατε\" (past) inside a present-tense chain → \"σαρώνετε\"
  \"επαναφέρθηκε\" breaking a subjunctive chain → \"επαναφερθεί\"

### 4. Punctuation is not translated literally
Never carry an English semicolon over into Greek: \";\" is the Greek question mark, so it silently turns a statement into a question. This was most visible at the end of nearly every bullet in list-heavy sections, and also mid-sentence in running text. Use a full stop, the ano teleia \"·\" for list items, or restructure the sentence.

### 5. Idioms and figurative language
Work out the intended meaning first, then say it in natural Greek, restructuring the sentence completely if needed. Word-for-word renderings produced these:
  \"quietly run the working world\" → became \"are quietly possessed by\"
  \"punches above its weight\" → became \"exceeds its own expectations\"
  \"fits right into that vision\" → became \"fits into that sustainability\" (vision and sustainability are unrelated)
  \"no third-party cloud in the picture\" → became a roughly meaningless phrase

### 6. UI labels and named product features stay in English
Do not translate button, tab or menu names, or named product features, unless an official Greek localization of that exact product is confirmed. Keep the English label so instructions match what the reader sees on screen:
  the \"Marketplace\" tab stays \"Marketplace\", not \"Αγορά\"
  the \"Rooms\" feature stays \"Rooms\" — it was rendered \"Πολυμορφικό\" (\"polymorphic\"), losing the feature name entirely

### 7. Established terminology, and the right word sense
For technical, legal and product terms use the established Greek term, not a literal rendering:
  \"data sovereignty\" → \"κυριαρχία δεδομένων\", NOT \"αυτονομία δεδομένων\" (\"data autonomy\")
  \"Viewer\" (as in a diagram viewer) → a word meaning viewer/reader, NOT \"Περιοριστής\" (\"limiter\"), which says the opposite of what the feature does
  \"native working formats\" → \"format\" here is a file format; do not pick the sense that means \"educated\"
  \"The deadlines are real\" → \"real\", not \"realistic\" — the rhetorical point is that they exist, not that they are achievable
When an English word maps to several Greek words depending on context, decide which sense applies before choosing.

### 8. Consistency within one article
Translate a given term the same way every time it appears in the same piece. \"real-time mode\" was rendered correctly once and then reduced to just \"real\" a few lines later, changing the meaning. Keep the gender you assign to a loanword such as \"cloud\" consistent throughout the text as well.

### 9. Leave no English behind mid-sentence
Translate every word. English may remain only for proper nouns, the never-translate list, UI labels under rule 6, and established loanwords. \"edge cases\" must not come out as \"περιπτώσεις edge\".

### 10. Acronym plurals
Foreign acronyms and loanwords never take an English \"-s\" in the plural; the article alone marks number: \"τα PDF\", never \"τα PDFs\".",

        'ar' => "- Maintain right-to-left text direction awareness.
- Use Modern Standard Arabic for professional/technical content.",

        'hi' => "- Use Devanagari script consistently.
- Technical terms that are commonly used in English may be kept in Latin script where natural.",
    );

    /**
     * Configured API request timeout, clamped to a range that keeps the task
     * inside Action Scheduler's failure window.
     *
     * @return int Seconds.
     */
    public static function get_request_timeout() {
        return self::clamp_request_timeout( get_option( 'oait_request_timeout', self::DEFAULT_REQUEST_TIMEOUT ) );
    }

    /**
     * Bring a stored or submitted timeout into the supported range.
     *
     * Applied on read as well as on save, so a value written straight to the
     * options table by WP-CLI cannot push a task past Action Scheduler's
     * failure window. Empty or non-numeric input means "unset" and yields the
     * default rather than the 30 s floor.
     *
     * @param mixed $timeout Raw value.
     * @return int Seconds.
     */
    public static function clamp_request_timeout( $timeout ) {
        $timeout = (int) $timeout;

        if ( $timeout <= 0 ) {
            return self::DEFAULT_REQUEST_TIMEOUT;
        }

        return max( self::MIN_REQUEST_TIMEOUT, min( self::MAX_REQUEST_TIMEOUT, $timeout ) );
    }

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

        $system_prompt = $this->build_system_prompt( $language_name, $target_lang_code );
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
    private function build_system_prompt( $language_name, $lang_code ) {
        if ( 'zh-hans' === $lang_code ) {
            return $this->build_chinese_system_prompt( $language_name );
        }

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
     * Custom system prompt for Simplified Chinese (zh-hans). Self-contained:
     * already includes product localization mappings, locale-specific rules and
     * China-advertising-law constraints, so build_user_prompt() must NOT inject
     * PRODUCT_LOCALIZATION / LOCALE_RULES blocks for this language.
     */
    private function build_chinese_system_prompt( $language_name ) {
        return <<<PROMPT
You are a professional translator for ONLYOFFICE — a software company producing office productivity tools.
Translate blog post content from English to {$language_name}.

## Universal rules (apply to ALL locales):

### Never translate these — keep exactly as-is:
- Brand name: ONLYOFFICE (always all-caps, never translated)
- Third-party product names: Docker, Docker Compose, Linux, Windows, macOS, iOS, Android, Ubuntu, Debian, CentOS, RHEL, KylinOS, snap
- Technical terms: JWT, HTTPS, SSL, API, ARM, ARM64, AGPL
- Cloud/hosting platforms: Amazon S3, DigitalOcean, Cloudron, Alibaba Cloud, Vultr, Linode
- Integration connector names: Nextcloud, ownCloud, WordPress, Confluence, SharePoint, Jira, Moodle, Alfresco, HumHub, Mattermost, Odoo, Pipedrive, SuiteCRM and other third-party brands
- Database names: MySQL, PostgreSQL, MsSQL, Oracle, Redis
- Plugin names: PhotoEditor, Mendeley, Zotero
- URLs, email addresses, code blocks

### Ensure that ONLYOFFICE product names are correctly localized for the Chinese market as follows:
ONLYOFFICE Docs -> ONLYOFFICE 文档
ONLYOFFICE DocSpace -> ONLYOFFICE 协作空间
ONLYOFFICE Workspace -> ONLYOFFICE 工作区
Desktop Editors -> 桌面编辑器
Docs Enterprise → 文档企业版
DocSpace Enterprise → 协作空间企业版
Docs Developer → 文档开发者版
DocSpace Developer → 协作空间开发者版
Docs Home Server → 文档家用服务器
DocSpace Family Pack → 协作空间家用版
DocSpace STARTUP → 协作空间初创版
DocSpace BUSINESS → 协作空间专业版
DocSpace ENTERPRISE → 协作空间企业版

Support level terminology:
· BASIC → 初级
· PLUS → 中级
· PREMIUM → 高级

### HTML rules:
- Preserve ALL HTML tags, attributes (class, id, href, src, style, data-*, etc.) EXACTLY as they are.
- ONLY translate the visible text content between tags.
- Do NOT modify any tag names, attribute names, or attribute values.
- Add exactly one space between any Chinese character (汉字) and Latin letters (A–Z, a–z).

### Blog-specific rules:
- Keep the same professional blog tone
- Translate naturally for the target audience in China, not word-by-word
- Maintain technical accuracy while making the content easy to understand
- Avoid direct, overly literal translations — rephrase where necessary to match local language habits
- Do NOT invent, infer, or reconstruct content for empty fields
- Strictly avoid absolute or superlative terms (e.g., 最佳, 第一, 完美, etc.) and use neutral, factual wording instead to ensure compliance with China advertising law.
- Standardize the translation of "useful links" as "相关链接".
PROMPT;
    }

    /**
     * Build the user prompt with language-specific rules and content.
     */
    private function build_user_prompt( $lang_code, $language_name, $title, $content, $excerpt, $aioseo_title, $aioseo_desc ) {
        $prompt = "Translate the following blog post fields from English to {$language_name}.\n\n";

        // For zh-hans the localization and locale rules are already embedded in the
        // system prompt (build_chinese_system_prompt) — do not duplicate them here.
        if ( 'zh-hans' !== $lang_code ) {
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
            'timeout' => self::get_request_timeout(),
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

        // A response cut off at max_tokens still carries a well-formed title and
        // content marker, so parse_response() accepts it and the post is saved
        // with a silently truncated body. Reject it instead and let the operator
        // retry with a larger model or a shorter post.
        $finish_reason = isset( $data['choices'][0]['finish_reason'] ) ? $data['choices'][0]['finish_reason'] : '';
        if ( 'length' === $finish_reason ) {
            return new WP_Error(
                'api_truncated',
                'Translation was cut off at the model output limit — the post is too long for this model.'
            );
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
