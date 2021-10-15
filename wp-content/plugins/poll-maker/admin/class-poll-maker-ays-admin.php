<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ays-pro.com/
 * @since      1.0.0
 *
 * @package    Poll_Maker_Ays
 * @subpackage Poll_Maker_Ays/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Poll_Maker_Ays
 * @subpackage Poll_Maker_Ays/admin
 * @author     Poll Maker Team <info@ays-pro.com>
 */
class Poll_Maker_Ays_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	private $polls_obj;
	private $cats_obj;
	private $results_obj;
    private $settings_obj;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		add_filter('set-screen-option', array(__CLASS__, 'set_screen'), 10, 3);
		$per_page_array = array(
            'polls_per_page',
            'cats_per_page',
            'poll_results_per_page',
        );
        foreach($per_page_array as $option_name){
            add_filter('set_screen_option_'.$option_name, array(__CLASS__, 'set_screen'), 10, 3);
        }
	}

	/**
	 * Register the styles for the admin menu area.
	 *
	 * @since    1.5.5
	 */
	public function admin_menu_styles() {
		echo "
        <style>
            #adminmenu a.toplevel_page_poll-maker-ays div.wp-menu-image img {
                width: 28px;
                padding-top: 2px;
            }

            #adminmenu li.toplevel_page_poll-maker-ays ul.wp-submenu.wp-submenu-wrap li:last-child a {
                color: #68A615;
            }

            .apm-badge {
                position: relative;
                top: -1px;
                right: -3px;
            }

            .apm-badge.badge-danger {
                color: #fff;
                background-color: #ca4a1f;
            }

            .apm-badge.badge {
                display: inline-block;
                vertical-align: top;
                margin: 1px 0 0 2px;
                padding: 0 5px;
                min-width: 7px;
                height: 17px;
                border-radius: 11px;
                font-size: 9px;
                line-height: 17px;
                text-align: center;
                z-index: 26;
            }

            .wp-first-item .apm-badge {
                display: none;
            }

            .apm-badge.badge.apm-no-results {
                display: none;
            }
        </style>
		";
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook_suffix ) {
		wp_enqueue_style($this->plugin_name . '-admin', plugin_dir_url(__FILE__) . 'css/admin.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . '-sweetalert-css', plugin_dir_url(__FILE__) .  'css/poll-maker-sweetalert2.min.css', array(), $this->version, 'all');
		if (false === strpos($hook_suffix, $this->plugin_name)) {
			return;
		}
		// wp_enqueue_style('wp-color-picker');
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Poll_Maker_Ays_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Poll_Maker_Ays_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		// You need styling for the datepicker. For simplicity I've linked to the jQuery UI CSS on a CDN.
        wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css' );
        wp_enqueue_style( 'jquery-ui' );

		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style('ays_poll_animate.css', plugin_dir_url(__FILE__) . 'css/animate.min.css', array(), $this->version, 'all');
		wp_enqueue_style( $this->plugin_name . '-font-awesome', plugin_dir_url(__FILE__) . 'css/poll-maker-font-awesome-all.css', array(), $this->version, 'all');
		wp_enqueue_style('ays_poll_bootstrap', plugin_dir_url(__FILE__) . 'css/bootstrap.min.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name.'-jquery-datetimepicker', plugin_dir_url(__FILE__) . 'css/jquery-ui-timepicker-addon.css', array(), $this->version, 'all');
		wp_enqueue_style('ays-poll-select2', plugin_dir_url(__FILE__) . 'css/select2.min.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/poll-maker-ays-admin.css', array(), $this->version, 'all');

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook_suffix ) {
		global $wp_version;
		if (false !== strpos($hook_suffix, "plugins.php")){
			wp_enqueue_script('sweetalert-js-poll', plugin_dir_url(__FILE__) . 'js/sweetalert2.all.min.js', array('jquery'), $this->version, true);

			wp_enqueue_script($this->plugin_name . '-admin', plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery'), $this->version, true);
			wp_localize_script($this->plugin_name . '-admin', 'apm_admin_ajax_obj', array('ajaxUrl' => admin_url('admin-ajax.php')));
		}

		$version1 = $wp_version;
        $operator = '>=';
        $version2 = '5.5';
        $versionCompare = $this->versionCompare($version1, $operator, $version2);
        if ($versionCompare) {	
            wp_enqueue_script( $this->plugin_name.'-wp-load-scripts', plugin_dir_url(__FILE__) . 'js/ays-wp-load-scripts.js', array(), $this->version, true);
        }	
		
		if (false === strpos($hook_suffix, $this->plugin_name)) {
			return;
		}

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Poll_Maker_Ays_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Poll_Maker_Ays_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_media();
		wp_enqueue_script( $this->plugin_name.'-wp-color-picker-alpha', plugin_dir_url(__FILE__) . 'js/wp-color-picker-alpha.min.js', array('wp-color-picker'), $this->version, true);
		wp_enqueue_script('ays_poll_popper', plugin_dir_url(__FILE__) . 'js/popper.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script('ays_poll_bootstrap', plugin_dir_url(__FILE__) . 'js/bootstrap.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script('ays_poll_select2', plugin_dir_url(__FILE__) . 'js/select2.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script('sweetalert-js-poll', plugin_dir_url(__FILE__) . 'js/sweetalert2.all.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script( $this->plugin_name."-jquery.datetimepicker.js", plugin_dir_url( __FILE__ ) . 'js/jquery-ui-timepicker-addon.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script('ays-poll-admin-js', plugin_dir_url(__FILE__) . 'js/poll-maker-ays-admin.js', array('jquery', 'wp-color-picker'),  $this->version, true);		
		wp_enqueue_script( $this->plugin_name . '-charts-google', plugin_dir_url(__FILE__) . 'js/google-chart.js', array('jquery'), $this->version, false);
		wp_localize_script('ays-poll-admin-js', 'pollLangObj', array(
            'add' 				=> __( 'Add', $this->plugin_name),
            'proVersionText'    => __( 'This property aviable only in pro version', $this->plugin_name),
            'answersMinCount'   => __( 'Sorry minimum count of answers should be 2', $this->plugin_name),
            'copied'        	=> __( 'Copied!', $this->plugin_name),
            'clickForCopy'  	=> __( 'Click for copy.', $this->plugin_name)
        ) );

		$color_picker_strings = array(
			'clear'            => __( 'Clear', $this->plugin_name ),
			'clearAriaLabel'   => __( 'Clear color', $this->plugin_name ),
			'defaultString'    => __( 'Default', $this->plugin_name ),
			'defaultAriaLabel' => __( 'Select default color', $this->plugin_name ),
			'pick'             => __( 'Select Color', $this->plugin_name ),
			'defaultLabel'     => __( 'Color value', $this->plugin_name ),
		);
		wp_localize_script( $this->plugin_name.'-wp-color-picker-alpha', 'wpColorPickerL10n', $color_picker_strings );
	}

	public function codemirror_enqueue_scripts($hook) {
        if(strpos($hook, $this->plugin_name) !== false){
            if(function_exists('wp_enqueue_code_editor')){
                $cm_settings['codeEditor'] = wp_enqueue_code_editor(array(
                    'type' => 'text/css',
                    'codemirror' => array(
                        'inputStyle' => 'contenteditable',
                        'theme' => 'cobalt',
                    )
                ));

                wp_enqueue_script('wp-theme-plugin-editor');
                wp_localize_script('wp-theme-plugin-editor', 'cm_settings', $cm_settings);

                wp_enqueue_style('wp-codemirror');
            }
        }
	}
	
	public function versionCompare($version1, $operator, $version2) {
   
        $_fv = intval ( trim ( str_replace ( '.', '', $version1 ) ) );
        $_sv = intval ( trim ( str_replace ( '.', '', $version2 ) ) );
       
        if (strlen ( $_fv ) > strlen ( $_sv )) {
            $_sv = str_pad ( $_sv, strlen ( $_fv ), 0 );
        }
       
        if (strlen ( $_fv ) < strlen ( $_sv )) {
            $_fv = str_pad ( $_fv, strlen ( $_sv ), 0 );
        }
       
        return version_compare ( ( string ) $_fv, ( string ) $_sv, $operator );
    }

	public function add_plugin_admin_menu() {

		/*
		 * Check unread results
		 *
		 */
		global $wpdb;
		$sql            = "SELECT COUNT(unread) FROM {$wpdb->prefix}ayspoll_reports WHERE unread=1";
		$unread_results = $wpdb->get_var($sql);
		$show           = $unread_results > 0 ? '' : "apm-no-results";
		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 */

		$hook_poll = add_menu_page('Poll Maker', "Poll Maker <span class=\"apm-badge badge badge-danger $show\">$unread_results</span>", 'manage_options', $this->plugin_name, array(
			$this,
			'display_plugin_polls_page'
		), POLL_MAKER_AYS_ADMIN_URL . '/images/icons/icon-128x128.png', '6.33');

		add_action("load-$hook_poll", array($this, 'screen_option_polls'));
	}


	public function add_plugin_polls_submenu() {
		$capability = $this->poll_maker_capabilities();

		$hook_polls = add_submenu_page(
			$this->plugin_name,
			__('Polls', $this->plugin_name),
			__('Polls', $this->plugin_name),
			$capability,
			$this->plugin_name,
			array($this, 'display_plugin_polls_page')
		);
		add_action("load-$hook_polls", array($this, 'screen_option_polls'));
	}

	public function add_plugin_categories_submenu() {
		$capability = $this->poll_maker_capabilities();

		$hook_cats = add_submenu_page(
			$this->plugin_name,
			__('Categories', $this->plugin_name),
			__('Categories', $this->plugin_name),
			$capability,
			$this->plugin_name . '-cats',
			array($this, 'display_plugin_cats_page')
		);
		add_action("load-$hook_cats", array($this, 'screen_option_cats'));
	}

	public function add_plugin_results_submenu() {
		/*
		 * Check unread results
		 *
		 */
		global $wpdb;
		$sql            = "SELECT COUNT(unread) FROM {$wpdb->prefix}ayspoll_reports WHERE unread=1";
		$unread_results = $wpdb->get_var($sql);
		$show           = $unread_results > 0 ? '' : "apm-no-results";

		$capability = $this->poll_maker_capabilities();

		$hook_results = add_submenu_page(
			$this->plugin_name,
			__('Results', $this->plugin_name),
			__('Results', $this->plugin_name) . " <span class=\"apm-badge badge badge-danger $show\">$unread_results</span>",
			$capability,
			$this->plugin_name . '-results',
			array($this, 'display_plugin_results_page')
		);
		add_action("load-$hook_results", array($this, 'screen_option_results'));
	}

	public function add_plugin_formfields_submenu() {

		$hook_formfields = add_submenu_page(
			$this->plugin_name,
			__('Custom Fields', $this->plugin_name),
			__('Custom Fields', $this->plugin_name),
			'manage_options',
			$this->plugin_name . '-formfields',
			array($this, 'display_plugin_formfields_page')
		);
	}

	public function add_plugin_general_settings_submenu() {
		$hook_settings = add_submenu_page($this->plugin_name,
			__('General Settings', $this->plugin_name),
			__('General Settings', $this->plugin_name),
			'manage_options',
			$this->plugin_name . '-settings',
			array($this, 'display_plugin_settings_page')
		);
		add_action("load-$hook_settings", array($this, 'screen_option_settings'));
	}

	public function add_plugin_how_to_use_submenu() {
		$hook_pro_features = add_submenu_page(
			$this->plugin_name,
			__('How to use', $this->plugin_name),
			__('How to use', $this->plugin_name),
			'manage_options',
			$this->plugin_name . '-how-to-use',
			array($this, 'display_plugin_how_to_use_page')
		);
	}

	public function add_plugin_pro_features_submenu() {
		$hook_pro_features = add_submenu_page(
			$this->plugin_name,
			__('PRO features', $this->plugin_name),
			__('PRO features', $this->plugin_name),
			'manage_options',
			$this->plugin_name . '-pro-features',
			array($this, 'display_plugin_pro_features_page')
		);
	}

	public function add_plugin_featured_plugins_submenu(){
        add_submenu_page( $this->plugin_name,
            __('Our products', $this->plugin_name),
            __('Our products', $this->plugin_name),
            'manage_options',
            $this->plugin_name . '-featured-plugins',
            array($this, 'display_plugin_featured_plugins_page') 
        );
    }

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */

	public function add_action_links( $links ) {
		/*
		 *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
		 */
		$settings_link = array(
			'<a href="' . admin_url('admin.php?page=' . $this->plugin_name) . '">' . __('Settings', $this->plugin_name) . '</a>',
			'<a href="https://poll-plugin.com/wordpress-poll-plugin-free-demo/" target="_blank">' . __('Demo', $this->plugin_name) . '</a>',
			'<a href="https://ays-pro.com/index.php/wordpress/poll-maker/" target="_blank" style="color:red;font-weight: bold;">' . __('Buy Now', $this->plugin_name) . '</a>',
		);

		return array_merge($settings_link, $links);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */

	public function display_plugin_polls_page() {
		$action = (isset($_GET['action'])) ? sanitize_text_field($_GET['action']) : '';

		switch ( $action ) {
			case 'add':
				include_once 'partials/poll-maker-ays-polls-actions.php';
				break;
			case 'edit':
				include_once 'partials/poll-maker-ays-polls-actions.php';
				break;
			default:
				include_once 'partials/poll-maker-ays-admin-display.php';
		}
	}

	public function display_plugin_cats_page() {
		$action = (isset($_GET['action'])) ? sanitize_text_field($_GET['action']) : '';

		switch ( $action ) {
			case 'add':
				include_once 'partials/categories/actions/poll-maker-ays-categories-actions.php';
				break;
			case 'edit':
				include_once 'partials/categories/actions/poll-maker-ays-categories-actions.php';
				break;
			default:
				include_once 'partials/categories/poll-maker-ays-categories-display.php';
		}
	}

	public function display_plugin_results_page() {
		include_once 'partials/results/poll-maker-ays-results-display.php';
	}

	public function display_plugin_formfields_page() {
		include_once 'partials/features/poll-maker-formfields_page-display.php';
	}

	public function display_plugin_pro_features_page() {
		include_once 'partials/features/poll-maker-pro-features-display.php';
	}

	public function display_plugin_how_to_use_page() {
		include_once 'partials/features/poll-maker-how-to-use-display.php';
	}

	public function display_plugin_featured_plugins_page(){
        include_once('partials/features/poll-maker-featured-display.php');
    }

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	public function screen_option_polls() {
		$option = 'per_page';
		$args   = array(
			'label'   => __('Polls', $this->plugin_name),
			'default' => 20,
			'option'  => 'polls_per_page',
		);

		add_screen_option($option, $args);
		$this->polls_obj = new Polls_List_Table($this->plugin_name);
        $this->settings_obj = new Poll_Maker_Settings_Actions($this->plugin_name);

    }

	public function screen_option_cats() {
		$option = 'per_page';
		$args   = array(
			'label'   => __('Categories', $this->plugin_name),
			'default' => 20,
			'option'  => 'cats_per_page',
		);

		add_screen_option($option, $args);
		$this->cats_obj = new Pma_Categories_List_Table($this->plugin_name);
	}

	public function screen_option_results() {
		$option = 'per_page';
		$args   = array(
			'label'   => __('Results', $this->plugin_name),
			'default' => 50,
			'option'  => 'poll_results_per_page',
		);

		add_screen_option($option, $args);
		$this->results_obj = new Pma_Results_List_Table($this->plugin_name);
		$this->answer_results_obj = new Poll_Answer_Results($this->plugin_name);
	}

	public function register_poll_ays_widget() {
		global $wpdb;
		$poll_table = esc_sql($wpdb->prefix . "ayspoll_polls");
		$sql = "SELECT COUNT(*) FROM ".$poll_table;

		$c = $wpdb->get_var($sql);
		if ($c == 0) {
			return;
		} else {
			register_widget('Poll_Maker_Widget');
		}
	}

	public function poll_maker_el_widgets_registered() {
        wp_enqueue_style($this->plugin_name . '-admin', plugin_dir_url(__FILE__) . 'css/admin.css', array(), $this->version, 'all');
        // We check if the Elementor plugin has been installed / activated.
        if ( defined( 'ELEMENTOR_PATH' ) && class_exists( 'Elementor\Widget_Base' ) ) {
            // get our own widgets up and running:
            // copied from widgets-manager.php
            if ( class_exists( 'Elementor\Plugin' ) ) {
                if ( is_callable( 'Elementor\Plugin', 'instance' ) ) {
                    $elementor = Elementor\Plugin::instance();
                    if ( isset( $elementor->widgets_manager ) ) {
                        if ( method_exists( $elementor->widgets_manager, 'register_widget_type' ) ) {
                            $widget_file   = 'plugins/elementor/poll_maker_elementor.php';
                            $template_file = locate_template( $widget_file );
                            if ( !$template_file || !is_readable( $template_file ) ) {
                                $template_file = POLL_MAKER_AYS_DIR.'pb_templates/poll_maker_elementor.php';
                            }
                            if ( $template_file && is_readable( $template_file ) ) {
                                require_once $template_file;
                                Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Elementor\Widget_Poll_Maker_Elementor() );
                            }
                        }
                    }
                }
            }
        }
    }

	public function apm_deactivate_plugin_option() {
		$request_value  = esc_sql( sanitize_text_field( $_REQUEST['upgrade_plugin'] ) );
		$upgrade_option = get_option('ays_poll_maker_upgrade_plugin');
		if ($upgrade_option === '') {
			add_option('ays_poll_maker_upgrade_plugin', $request_value);
		} else {
			update_option('ays_poll_maker_upgrade_plugin', $request_value);
		}
		ob_end_clean();
		$ob_get_clean = ob_get_clean();
		echo json_encode(array('option' => get_option('ays_poll_maker_upgrade_plugin')));
		wp_die();
	}

    public function screen_option_settings() {
		$this->polls_obj = new Polls_List_Table($this->plugin_name);
        $this->settings_obj = new Poll_Maker_Settings_Actions($this->plugin_name);
    }

    public function display_plugin_settings_page() {
        include_once('partials/settings/poll-maker-settings.php');
    }

    public function ays_get_mailchimp_lists( $username, $api_key ) {
        if (!empty($api_key) && strpos($api_key, '-') !== false) {
            $api_postfix = explode("-", $api_key)[1];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL            => "https://" . $api_postfix . ".api.mailchimp.com/3.0/lists",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "GET",
                CURLOPT_USERPWD        => "$username:$api_key",
                CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
	   //   CURLOPT_POSTFIELDS => "undefined=",
                CURLOPT_HTTPHEADER     => array(
                    "Content-Type: application/json",
                    "cache-control: no-cache"
                ),
            ));

            $response = curl_exec($curl);
            $err      = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error";
            } else {
                return json_decode($response, true);
            }
        }

        return array();
    }

    public static function validateDate($date, $format = 'Y-m-d H:i:s'){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public static function ays_restriction_string($type, $x, $length){
        $output = "";
        switch($type){
            case "char":                
                if(strlen($x)<=$length){
                    $output = $x;
                } else {
                    $output = substr($x,0,$length) . '...';
                }
                break;
            case "word":
                $res = explode(" ", $x);
                if(count($res)<=$length){
                    $output = implode(" ",$res);
                } else {
                    $res = array_slice($res,0,$length);
                    $output = implode(" ",$res) . '...';
                }
            break;
        }
        return $output;
    }

    public static function get_listtables_title_length( $listtable_name ) {
        global $wpdb;

        $settings_table = $wpdb->prefix . "ayspoll_settings";
        $sql = "SELECT meta_value FROM ".$settings_table." WHERE meta_key = 'options'";
        $result = $wpdb->get_var($sql);
        $options = ($result == "") ? array() : json_decode($result, true);

        $listtable_title_length = 5;
        if(! empty($options) ){
            switch ( $listtable_name ) {
                case 'polls':
                    $listtable_title_length = (isset($options['poll_title_length']) && intval($options['poll_title_length']) != 0) ? absint(intval($options['poll_title_length'])) : 5;
                    break;      
                default:
                    $listtable_title_length = 5;
                    break;
            }
            return $listtable_title_length;
        }
        return $listtable_title_length;
    }

    protected function poll_maker_capabilities(){
        global $wpdb;
		$sql    = "SELECT meta_value FROM {$wpdb->prefix}ayspoll_settings WHERE `meta_key` = 'user_roles'";
		$result = $wpdb->get_var($sql);
		
        $capability = 'manage_options';
        if($result !== null){
            $ays_user_roles = json_decode($result, true);
            if(is_user_logged_in()){
                $current_user = wp_get_current_user();
                $current_user_roles = $current_user->roles;
                $ishmar = 0;
                foreach($current_user_roles as $r){
                    if(in_array($r, $ays_user_roles)){
                        $ishmar++;
                    }
                }
                if($ishmar > 0){
                    $capability = "read";
                }
            }
        }
        return $capability;
	}
	
	public function poll_maker_admin_footer($a){
        if(isset($_REQUEST['page'])){
            if(false !== strpos($_REQUEST['page'], $this->plugin_name)){
                ?>
                <p style="font-size:13px;text-align:center;font-style:italic;">
                    <span style="margin-left:0px;margin-right:10px;" class="ays_heart_beat"><i class="ays_fa ays_poll_fa_heart_o animated"></i></span>
                    <span><?php echo __( "If you love our plugin, please do big favor and rate us on", $this->plugin_name); ?></span> 
                    <a target="_blank" href='https://wordpress.org/support/plugin/poll-maker/reviews/'>WordPress.org</a>
                    <span class="ays_heart_beat"><i class="ays_fa ays_poll_fa_heart_o animated"></i></span>
                </p>
            <?php
            }
        }
    }

	// Sales baner function 
    public function ays_poll_sale_baner(){
		if(isset($_POST['ays_poll_sale_btn'])){
				update_option('ays_poll_sale_notification_'.$_POST['ays_poll_sale_btn'], 1); 
				update_option('ays_poll_sale_date_'.$_POST['ays_poll_sale_btn'], current_time( 'mysql' ));
		}
		
		$one_day = 60*60*24; 
		$poll_sales = array(
			'plugin_sale'     => array(
									'status' => 'inactive',
									'time'   => ($one_day * 5),
								),
			'mega_bundle'     => array(
									'status' => 'inactive',
									'time'   => ($one_day * 5),
								),
			'business_bundle' => array(
									'status' => 'active',
									'time'   => ($one_day * 7),
								),
		);
		
		if (isset($_GET['page']) && strpos($_GET['page'], POLL_MAKER_AYS_NAME) !== false) {
			foreach($poll_sales as $sale => $status){
				$ays_poll_sale_date = '';
				if(isset($status['status']) && $status['status'] == 'active'){
					$ays_poll_sale_date = get_option('ays_poll_sale_date_'.$sale);
					$current_date = current_time( 'mysql' );
					$date_diff = strtotime($current_date) -  intval(strtotime($ays_poll_sale_date)) ;
					$val = isset($status['time']) ? intval($status['time']) : $one_day*5;
					$days_diff = $date_diff / $val;
					if(intval($days_diff) > 0 ){
						update_option('ays_poll_sale_notification_'.$sale, 0); 
					}
					$ays_poll_flag = intval(get_option('ays_poll_sale_notification_'.$sale));
					if($ays_poll_flag == 0){
						$ays_poll_sale_message = 'ays_poll_sale_message_'.$sale;
						$this->$ays_poll_sale_message();
					}
				}
			}
		}
	}

	// Poll plugin sale  
	// public function ays_poll_sale_message($flag){
    //     if($flag == 0){
        	
	// 		<div id="ays-poll-dicount-month-main" class="notice notice-success is-dismissible ays_poll_dicount_info" >
	// 			<div id="ays_poll_dicount_banner" class="ays_poll_dicount_month" style="display: flex;align-items: center;justify-content: space-between;">
	// 				<div style="display: flex; align-items: center;">
	// 					<div>
	// 						<a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" class="ays-poll-sale-banner-link"><img src="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/sale.png'; " style="width: 60px;"></a>
	// 					</div>
	// 					<div style="font-size:14px; padding: 12px;">
	// 						<strong>
	// 							<?php echo __( "Limited Time <span style='color:red;'>30%</span> SALE on <a href='https://ays-pro.com/wordpress/poll-maker' target='_blank'>Poll Maker</a> premium plans!",POLL_MAKER_AYS_NAME);   
	// 						</strong>
	// 						<br>
	// 						<strong style="font-size: 12px;">								
	// 								<?php echo __( "Welcome to summer! Start it right with all the powerful tools you need for creating an advanced interactive website. Hurry! Ends on June 30. <a href='https://ays-pro.com/wordpress/poll-maker' target='_blank'>Check it out!</a>",POLL_MAKER_AYS_NAME); 					
	// 						</strong>							
	// 						<form action="" method="POST">
	// 							<button class="btn btn-link ays-button" name="ays_poll_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0">Dismiss ad</button>
	// 						</form>
	// 					</div>
	// 				</div>
	// 				<a href="https://ays-pro.com/wordpress/poll-maker" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank" style="height: 32px; display: flex; align-items: center; font-weight: 500; " ><?php echo __('Buy Now !',POLL_MAKER_AYS_NAME); </a>
				
	// 			</div>
				
	// 		</div>		
	// 		<?php
	// 	}
	// }

	// Mega bundle sale
	public function ays_poll_sale_message_mega_bundle(){
		?>
		<div class="notice notice-success is-dismissible ays_poll_dicount_info" >
			<div id="ays_poll_dicount_banner" class="ays_poll_dicount_month" style="display: flex;align-items: center;justify-content: space-between;">
				<div style="display: flex; align-items: center;">
					<div>
						<a href="https://ays-pro.com/mega-bundle" target="_blank" class="ays-poll-sale-banner-link"><img src="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/mega_bundle_logo_box.png';?> " style="width: 60px;"></a>
					</div>
					<div style="font-size:14px; padding:12px; width: 100%;">
						<strong>
							<?php echo __( "Limited Time <span style='color:red;'>50%</span> SALE on 3 Powerful Plugins (Quiz, Survey, Poll)!",POLL_MAKER_AYS_NAME);?>  
						</strong>
						<br>
						<strong style="font-size: 12px;">								
								<?php echo __( "Mega bundle offer for you! It consists of 3 different powerful plugins, each one allowing you to make your WordPress experience the best that could be.",POLL_MAKER_AYS_NAME);?>							
								<br>
								<?php echo __( "Hurry up! Ends on October 15. <a href='https://ays-pro.com/mega-bundle' target='_blank'>Check it out!</a>",POLL_MAKER_AYS_NAME);?>							
						</strong>							
						<form action="" method="POST">
							<button class="btn btn-link ays-button" name="ays_poll_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0" value='mega_bundle'>Dismiss ad</button>
						</form>
					</div>
				</div>
				<a href="https://ays-pro.com/mega-bundle" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank" style="height: 32px; display: flex; align-items: center; font-weight: 500; " ><?php echo __('Buy Now !',POLL_MAKER_AYS_NAME);?> </a>				
			</div>
		</div>	
		<?php
	}

	// Business bundle sale
	public function ays_poll_sale_message_business_bundle(){
		?>
		<div class="notice notice-success is-dismissible ays_poll_dicount_info" >
			<div id="ays_poll_dicount_banner" class="ays_poll_dicount_month" style="display: flex;align-items: center;justify-content: space-between;">
				<div style="display: flex; align-items: center;">
					<div>
						<a href="https://ays-pro.com/business-bundle" target="_blank" class="ays-poll-sale-banner-link"><img src="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/business_bundle_logo.png';?> " style="width: 60px;"></a>
					</div>
					<div style="font-size:14px; padding:12px; width: 100%;">
						<strong>
							<?php echo __( "Limited Time <span style='color:red;'>50%</span> SALE on 13 Powerful Plugins!",POLL_MAKER_AYS_NAME);?>  
						</strong>
						<br>
						<strong style="font-size: 12px;">								
								<?php echo __( "Business bundle offer for you! It consists of 13 different powerful plugins, each one allowing you to make your WordPress experience the best that could be.",POLL_MAKER_AYS_NAME);?>							
								<br>
								<?php echo __( "Hurry up! Ends on October 15. <a href='https://ays-pro.com/business-bundle' target='_blank'>Check it out!</a>",POLL_MAKER_AYS_NAME);?>							
						</strong>							
						<form action="" method="POST">
							<button class="btn btn-link ays-button" name="ays_poll_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0" value='business_bundle'>Dismiss ad</button>
						</form>
					</div>
				</div>
				<a href="https://ays-pro.com/business-bundle" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank" style="height: 32px; display: flex; align-items: center; font-weight: 500; " ><?php echo __('Buy Now !',POLL_MAKER_AYS_NAME);?> </a>				
			</div>
		</div>	
		<?php
	}
}