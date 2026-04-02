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
            'oetl_voice_id',
            'Default Voice ID',
            array( $this, 'render_voice_id_field' ),
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

    public function render_voice_id_field() {
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

    public static function get_api_key() {
        if ( defined( 'OETL_API_KEY' ) ) {
            return OETL_API_KEY;
        }
        return get_option( 'oetl_api_key', '' );
    }
}
