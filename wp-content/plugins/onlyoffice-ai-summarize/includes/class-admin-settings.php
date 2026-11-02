<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OAIS_Admin_Settings {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_settings_page() {
        add_options_page(
            'AI Summarize Settings',
            'AI Summarize',
            'manage_options',
            'oais-settings',
            array( $this, 'render_settings_page' )
        );
    }

    public function register_settings() {
        register_setting( 'oais_settings', 'oais_api_key', array(
            'type'              => 'string',
            'sanitize_callback' => array( $this, 'sanitize_api_key' ),
        ) );

        register_setting( 'oais_settings', 'oais_model', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'gpt-4o-mini',
        ) );

        register_setting( 'oais_settings', 'oais_max_words', array(
            'type'              => 'integer',
            'sanitize_callback' => array( $this, 'sanitize_max_words' ),
            'default'           => 80,
        ) );

        register_setting( 'oais_settings', 'oais_section_title', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'Summary',
        ) );

        register_setting( 'oais_settings', 'oais_post_types', array(
            'type'              => 'array',
            'sanitize_callback' => array( $this, 'sanitize_post_types' ),
            'default'           => array( 'post' ),
        ) );

        register_setting( 'oais_settings', 'oais_display_position', array(
            'type'              => 'string',
            'sanitize_callback' => array( $this, 'sanitize_position' ),
            'default'           => 'top',
        ) );

        add_settings_section( 'oais_api_section', 'API Settings', null, 'oais-settings' );

        add_settings_field(
            'oais_api_key',
            'OpenAI API Key',
            array( $this, 'render_api_key_field' ),
            'oais-settings',
            'oais_api_section'
        );

        add_settings_field(
            'oais_model',
            'AI Model',
            array( $this, 'render_model_field' ),
            'oais-settings',
            'oais_api_section'
        );

        add_settings_section( 'oais_output_section', 'Summary Output', null, 'oais-settings' );

        add_settings_field(
            'oais_max_words',
            'Maximum words',
            array( $this, 'render_max_words_field' ),
            'oais-settings',
            'oais_output_section'
        );

        add_settings_field(
            'oais_section_title',
            'Section title',
            array( $this, 'render_section_title_field' ),
            'oais-settings',
            'oais_output_section'
        );

        add_settings_field(
            'oais_post_types',
            'Post types',
            array( $this, 'render_post_types_field' ),
            'oais-settings',
            'oais_output_section'
        );

        add_settings_field(
            'oais_display_position',
            'Display position',
            array( $this, 'render_position_field' ),
            'oais-settings',
            'oais_output_section'
        );
    }

    public function render_api_key_field() {
        $value  = self::get_api_key();
        $masked = $value ? str_repeat( '*', max( 0, strlen( $value ) - 8 ) ) . substr( $value, -8 ) : '';
        ?>
        <input type="password" name="oais_api_key" value="<?php echo esc_attr( $value ); ?>"
               class="regular-text" autocomplete="off" />
        <?php if ( $masked ) : ?>
            <p class="description">Current key: <code><?php echo esc_html( $masked ); ?></code></p>
        <?php endif; ?>
        <?php
    }

    public function render_model_field() {
        $value = get_option( 'oais_model', 'gpt-4o-mini' );
        ?>
        <input type="text" name="oais_model" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <p class="description">Default: <code>gpt-4o-mini</code>. Other options: <code>gpt-4o</code>, <code>gpt-4.1-mini</code>, <code>gpt-4.1-nano</code></p>
        <?php
    }

    public function render_max_words_field() {
        $value = (int) get_option( 'oais_max_words', 80 );
        ?>
        <input type="number" min="20" max="500" step="10" name="oais_max_words"
               value="<?php echo esc_attr( $value ); ?>" class="small-text" />
        <p class="description">Word budget for the intro paragraph (20&ndash;500). Bullets, when present, follow a separate 5&ndash;10 words-per-bullet rule.</p>
        <?php
    }

    public function render_section_title_field() {
        $value = get_option( 'oais_section_title', 'Summary' );
        ?>
        <input type="text" name="oais_section_title" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <p class="description">Heading text rendered inside the summary block. On the Next.js frontend, this text is replaced by a localized equivalent.</p>
        <?php
    }

    public function render_post_types_field() {
        $enabled = get_option( 'oais_post_types', array( 'post' ) );
        if ( ! is_array( $enabled ) ) {
            $enabled = array( 'post' );
        }
        $public = get_post_types( array( 'public' => true ), 'objects' );
        unset( $public['attachment'] );

        foreach ( $public as $pt ) {
            printf(
                '<label style="display:inline-block;min-width:180px;margin-bottom:6px;">' .
                '<input type="checkbox" name="oais_post_types[]" value="%1$s" %2$s /> %3$s (<code>%1$s</code>)</label><br/>',
                esc_attr( $pt->name ),
                in_array( $pt->name, $enabled, true ) ? 'checked' : '',
                esc_html( $pt->labels->singular_name )
            );
        }
    }

    public function render_position_field() {
        $value = get_option( 'oais_display_position', 'top' );
        ?>
        <label><input type="radio" name="oais_display_position" value="top"    <?php checked( $value, 'top' ); ?> /> Above content</label><br/>
        <label><input type="radio" name="oais_display_position" value="bottom" <?php checked( $value, 'bottom' ); ?> /> Below content</label>
        <?php
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>ONLYOFFICE AI Summarize</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'oais_settings' );
                do_settings_sections( 'oais-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function sanitize_api_key( $value ) {
        $value = sanitize_text_field( $value );
        if ( empty( $value ) ) {
            return get_option( 'oais_api_key', '' );
        }
        return $value;
    }

    public function sanitize_max_words( $value ) {
        $value = (int) $value;
        if ( $value < 20 )  { $value = 20;  }
        if ( $value > 500 ) { $value = 500; }
        return $value;
    }

    public function sanitize_post_types( $value ) {
        if ( ! is_array( $value ) ) {
            return array( 'post' );
        }
        $public = array_keys( get_post_types( array( 'public' => true ) ) );
        return array_values( array_intersect( $value, $public ) );
    }

    public function sanitize_position( $value ) {
        return in_array( $value, array( 'top', 'bottom' ), true ) ? $value : 'top';
    }

    public static function get_api_key() {
        return (string) get_option( 'oais_api_key', '' );
    }
}
