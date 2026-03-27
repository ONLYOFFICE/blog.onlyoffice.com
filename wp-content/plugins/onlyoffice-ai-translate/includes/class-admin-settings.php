<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OAIT_Admin_Settings {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_settings_page() {
        add_options_page(
            'AI Translate Settings',
            'AI Translate',
            'manage_options',
            'oait-settings',
            array( $this, 'render_settings_page' )
        );
    }

    public function register_settings() {
        register_setting( 'oait_settings', 'oait_api_key', array(
            'type'              => 'string',
            'sanitize_callback' => array( $this, 'sanitize_api_key' ),
        ) );

        register_setting( 'oait_settings', 'oait_enabled_languages', array(
            'type'              => 'array',
            'sanitize_callback' => array( $this, 'sanitize_languages' ),
        ) );

        register_setting( 'oait_settings', 'oait_auto_translate', array(
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
        ) );

        register_setting( 'oait_settings', 'oait_model', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'gpt-4o-mini',
        ) );

        // API Section
        add_settings_section( 'oait_api_section', 'API Settings', null, 'oait-settings' );

        add_settings_field(
            'oait_api_key',
            'OpenAI API Key',
            array( $this, 'render_api_key_field' ),
            'oait-settings',
            'oait_api_section'
        );

        add_settings_field(
            'oait_model',
            'Claude Model',
            array( $this, 'render_model_field' ),
            'oait-settings',
            'oait_api_section'
        );

        // Languages Section
        add_settings_section( 'oait_languages_section', 'Target Languages', null, 'oait-settings' );

        add_settings_field(
            'oait_enabled_languages',
            'Languages to translate',
            array( $this, 'render_languages_field' ),
            'oait-settings',
            'oait_languages_section'
        );

        // Auto-translate Section
        add_settings_section( 'oait_auto_section', 'Automation', null, 'oait-settings' );

        add_settings_field(
            'oait_auto_translate',
            'Auto-translate on publish',
            array( $this, 'render_auto_translate_field' ),
            'oait-settings',
            'oait_auto_section'
        );
    }

    public function render_api_key_field() {
        $value = $this->get_api_key();
        $masked = $value ? str_repeat( '*', max( 0, strlen( $value ) - 8 ) ) . substr( $value, -8 ) : '';
        ?>
        <input type="password" name="oait_api_key" value="<?php echo esc_attr( $value ); ?>"
               class="regular-text" autocomplete="off" />
        <?php if ( $masked ) : ?>
            <p class="description">Current key: <code><?php echo esc_html( $masked ); ?></code></p>
        <?php endif; ?>
        <?php
    }

    public function render_model_field() {
        $value = get_option( 'oait_model', 'gpt-4o-mini' );
        ?>
        <input type="text" name="oait_model" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <p class="description">Default: <code>gpt-4o-mini</code>. Other options: <code>gpt-4o</code>, <code>gpt-4.1-mini</code>, <code>gpt-4.1-nano</code></p>
        <?php
    }

    public function render_languages_field() {
        $enabled = get_option( 'oait_enabled_languages', array() );
        if ( ! is_array( $enabled ) ) {
            $enabled = array();
        }

        foreach ( OAIT_Translator::LANGUAGES as $code => $name ) {
            $checked = in_array( $code, $enabled, true ) ? 'checked' : '';
            printf(
                '<label style="display:inline-block;min-width:180px;margin-bottom:6px;">' .
                '<input type="checkbox" name="oait_enabled_languages[]" value="%s" %s /> %s (%s)' .
                '</label><br/>',
                esc_attr( $code ),
                $checked,
                esc_html( $name ),
                esc_html( $code )
            );
        }
    }

    public function render_auto_translate_field() {
        $value = get_option( 'oait_auto_translate', false );
        ?>
        <label>
            <input type="checkbox" name="oait_auto_translate" value="1" <?php checked( $value ); ?> />
            Automatically translate posts when published
        </label>
        <p class="description">When enabled, new English posts will be queued for translation to all selected languages upon publish.</p>
        <?php
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>ONLYOFFICE AI Translate Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'oait_settings' );
                do_settings_sections( 'oait-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Sanitize API key before saving.
     */
    public function sanitize_api_key( $value ) {
        $value = sanitize_text_field( $value );
        if ( empty( $value ) ) {
            return get_option( 'oait_api_key', '' );
        }
        return $value;
    }

    public function sanitize_languages( $value ) {
        if ( ! is_array( $value ) ) {
            return array();
        }
        $valid_codes = array_keys( OAIT_Translator::LANGUAGES );
        return array_values( array_intersect( $value, $valid_codes ) );
    }

    /**
     * Get API key.
     */
    public static function get_api_key() {
        return get_option( 'oait_api_key', '' );
    }
}
