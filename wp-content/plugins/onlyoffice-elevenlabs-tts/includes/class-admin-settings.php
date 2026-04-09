<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OETL_Admin_Settings {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_settings_page() {
        add_options_page(
            'ElevenLabs TTS Settings',
            'ElevenLabs TTS',
            'manage_options',
            'oetl-settings',
            array( $this, 'render_settings_page' )
        );
    }

    public function register_settings() {
        register_setting( 'oetl_settings', 'oetl_api_key', array(
            'type'              => 'string',
            'sanitize_callback' => array( $this, 'sanitize_api_key' ),
        ) );

        register_setting( 'oetl_settings', 'oetl_voice_id', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ) );

        register_setting( 'oetl_settings', 'oetl_voice_ids', array(
            'type'              => 'array',
            'sanitize_callback' => array( $this, 'sanitize_voice_ids' ),
        ) );

        register_setting( 'oetl_settings', 'oetl_model_id', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'eleven_multilingual_v2',
        ) );

        register_setting( 'oetl_settings', 'oetl_auto_generate', array(
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
        ) );

        // API Section
        add_settings_section( 'oetl_api_section', 'API Settings', null, 'oetl-settings' );

        add_settings_field(
            'oetl_api_key',
            'ElevenLabs API Key',
            array( $this, 'render_api_key_field' ),
            'oetl-settings',
            'oetl_api_section'
        );

        add_settings_field(
            'oetl_voice_ids',
            'Voice Configuration',
            array( $this, 'render_voice_ids_field' ),
            'oetl-settings',
            'oetl_api_section'
        );

        add_settings_field(
            'oetl_model_id',
            'Model',
            array( $this, 'render_model_field' ),
            'oetl-settings',
            'oetl_api_section'
        );

        // Automation Section
        add_settings_section( 'oetl_auto_section', 'Automation', null, 'oetl-settings' );

        add_settings_field(
            'oetl_auto_generate',
            'Auto-generate on publish',
            array( $this, 'render_auto_generate_field' ),
            'oetl-settings',
            'oetl_auto_section'
        );
    }

    public function render_api_key_field() {
        $value = self::get_api_key();
        $masked = $value ? str_repeat( '*', max( 0, strlen( $value ) - 8 ) ) . substr( $value, -8 ) : '';
        ?>
        <input type="password" name="oetl_api_key" value="<?php echo esc_attr( $value ); ?>"
               class="regular-text" autocomplete="off" />
        <?php if ( $masked ) : ?>
            <p class="description">Current key: <code><?php echo esc_html( $masked ); ?></code></p>
        <?php endif; ?>
        <?php
    }

    public function render_voice_ids_field() {
        if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
            $this->render_wpml_voice_fields();
        } else {
            $this->render_single_voice_field();
        }
    }

    private function render_wpml_voice_fields() {
        $languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
        if ( empty( $languages ) || ! is_array( $languages ) ) {
            $this->render_single_voice_field();
            return;
        }

        $voice_ids = get_option( 'oetl_voice_ids', array() );
        if ( ! is_array( $voice_ids ) ) {
            $voice_ids = array();
        }

        // Auto-populate from legacy option on first use
        if ( empty( $voice_ids ) ) {
            $legacy = get_option( 'oetl_voice_id', '' );
            if ( ! empty( $legacy ) && isset( $languages['en'] ) ) {
                $voice_ids['en'] = $legacy;
            }
        }

        // Ensure English is listed first
        $sorted = array();
        if ( isset( $languages['en'] ) ) {
            $sorted['en'] = $languages['en'];
        }
        foreach ( $languages as $code => $lang ) {
            if ( $code !== 'en' ) {
                $sorted[ $code ] = $lang;
            }
        }

        echo '<table class="oetl-voice-ids-table" style="border-spacing:0 6px;">';
        foreach ( $sorted as $code => $lang ) {
            $name = ! empty( $lang['native_name'] ) ? $lang['native_name'] : $code;
            $is_default = ( $code === 'en' );
            $value = isset( $voice_ids[ $code ] ) ? $voice_ids[ $code ] : '';
            ?>
            <tr>
                <td style="padding:2px 8px 2px 0;white-space:nowrap;">
                    <label for="oetl_voice_ids_<?php echo esc_attr( $code ); ?>">
                        <strong><?php echo esc_html( $name ); ?></strong>
                        <span style="color:#666;">(<?php echo esc_html( $code ); ?>)</span>
                        <?php if ( $is_default ) : ?>
                            <span style="color:#00a32a;font-weight:600;"> — Default</span>
                        <?php endif; ?>
                    </label>
                </td>
                <td style="padding:2px 0;">
                    <input type="text"
                           id="oetl_voice_ids_<?php echo esc_attr( $code ); ?>"
                           name="oetl_voice_ids[<?php echo esc_attr( $code ); ?>]"
                           value="<?php echo esc_attr( $value ); ?>"
                           class="regular-text"
                           placeholder="Voice ID for <?php echo esc_attr( $name ); ?>" />
                </td>
            </tr>
            <?php
        }
        echo '</table>';
        echo '<p class="description">Set a Voice ID for each language. Find voice IDs in your <a href="https://elevenlabs.io/app/voice-library" target="_blank">ElevenLabs dashboard</a>. English is used as the fallback for languages without a configured voice.</p>';
    }

    private function render_single_voice_field() {
        $value = get_option( 'oetl_voice_id', '' );
        ?>
        <input type="text" name="oetl_voice_id" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <p class="description">The ElevenLabs Voice ID to use. Find voice IDs in your <a href="https://elevenlabs.io/app/voice-library" target="_blank">ElevenLabs dashboard</a>.</p>
        <?php
    }

    public function render_model_field() {
        $value = get_option( 'oetl_model_id', 'eleven_multilingual_v2' );
        ?>
        <input type="text" name="oetl_model_id" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <p class="description">Default: <code>eleven_multilingual_v2</code> (supports 29 languages). Other options: <code>eleven_turbo_v2_5</code>, <code>eleven_turbo_v2</code></p>
        <?php
    }

    public function render_auto_generate_field() {
        $value = get_option( 'oetl_auto_generate', false );
        ?>
        <label>
            <input type="checkbox" name="oetl_auto_generate" value="1" <?php checked( $value ); ?> />
            Automatically generate audio when a post is published
        </label>
        <p class="description">When enabled, audio will be generated automatically for new posts upon publish. <strong>Note:</strong> This uses ElevenLabs API credits.</p>
        <?php
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>ONLYOFFICE ElevenLabs TTS Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'oetl_settings' );
                do_settings_sections( 'oetl-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function sanitize_api_key( $value ) {
        $value = sanitize_text_field( $value );
        if ( empty( $value ) ) {
            return get_option( 'oetl_api_key', '' );
        }
        return $value;
    }

    public function sanitize_voice_ids( $value ) {
        if ( ! is_array( $value ) ) {
            return array();
        }
        $clean = array();
        foreach ( $value as $lang => $voice_id ) {
            $voice_id = sanitize_text_field( $voice_id );
            if ( ! empty( $voice_id ) ) {
                $clean[ sanitize_key( $lang ) ] = $voice_id;
            }
        }
        return $clean;
    }

    public static function get_api_key() {
        if ( defined( 'OETL_API_KEY' ) ) {
            return OETL_API_KEY;
        }
        return get_option( 'oetl_api_key', '' );
    }

    /**
     * Get the voice ID configured for a specific language code.
     * Falls back to the default voice ID if not set.
     */
    public static function get_voice_id_for_language( $lang_code ) {
        $voice_ids = get_option( 'oetl_voice_ids', array() );
        if ( ! is_array( $voice_ids ) ) {
            $voice_ids = array();
        }
        if ( ! empty( $voice_ids[ $lang_code ] ) ) {
            return $voice_ids[ $lang_code ];
        }
        // Fallback to English (default)
        if ( ! empty( $voice_ids['en'] ) ) {
            return $voice_ids['en'];
        }
        // Legacy fallback
        return get_option( 'oetl_voice_id', '' );
    }

    /**
     * Get all configured voices as lang_code => voice_id pairs.
     * Only returns languages that have a voice ID set.
     */
    public static function get_all_configured_voices() {
        $voice_ids = get_option( 'oetl_voice_ids', array() );
        if ( ! is_array( $voice_ids ) ) {
            $voice_ids = array();
        }

        return array_filter( $voice_ids, function ( $v ) {
            return ! empty( $v );
        } );
    }
}
