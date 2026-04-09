<?php

namespace DemocracyPoll;

use DemocracyPoll\Helpers\Kses;
use DemocracyPoll\Utils\Activator;
use DemocracyPoll\Admin\Admin;
use DemocracyPoll\Admin\Admin_Page_l10n;

class Plugin_Initor {

	public function __construct() {
	}

	public function basic_init(): void {
		options()->set_opt();

		Activator::set_db_tables();
		if( is_multisite() ){
			add_action( 'switch_blog', [ Activator::class, 'set_db_tables' ] );
		}

		plugin()->set_access_caps();
		Kses::setup_allowed_tags();
		$this->load_textdomain();
	}

	public function plugin_init(): void {
		$this->basic_init();

		plugin()->set_is_cachegear_on();

		$this->admin_init();

		$this->init_shortcodes();
		$this->init_ajax();

		// For front-end localization and custom translation
		Admin_Page_l10n::add_gettext_filter();

		$this->init_menu_in_toolbar();
		$this->init_hide_form_indexing();
		$this->init_wp_widget();
	}

	private function admin_init(): void {
		if( is_admin() && ! wp_doing_ajax() ){
			plugin()->admin = new Admin();
			plugin()->admin->init();
		}
	}

	private function init_shortcodes(): void {
		( new Shortcodes() )->init();
	}

	private function init_ajax(): void {
		plugin()->poll_ajax = new Poll_Ajax();
		plugin()->poll_ajax->init();
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'democracy-poll', false, basename( plugin()->dir ) . '/languages/build/' );
	}

	private function init_wp_widget(): void {
		if( options()->use_widget ){
			add_action( 'widgets_init', static function() {
				register_widget( Poll_Widget::class );
			} );
		}
	}

	private function init_menu_in_toolbar(): void {
		if( plugin()->admin_access && options()->toolbar_menu ){
			add_action( 'admin_bar_menu', [ plugin()->initor, 'add_toolbar_node' ], 99 );
		}
	}

	/**
	 * Hide duplicate content. For 5+ versions it's no need.
	 */
	private function init_hide_form_indexing(): void {
		// Hide duplicate content. For 5+ versions it's no need
		if(
			isset( $_GET['dem_act'] )
			|| isset( $_GET['dem_action'] )
			|| isset( $_GET['dem_pid'] )
			|| isset( $_GET['show_addanswerfield'] )
			|| isset( $_GET['dem_add_user_answer'] )
		){
			add_action( 'wp', static function() {
				status_header( 404 );
			} );

			add_action( 'wp_head', static function() {
				echo "\n<!--democracy-poll-->\n" . '<meta name="robots" content="noindex,nofollow">' . "\n";
			} );
		}
	}

	/**
	 * @param \WP_Admin_Bar $toolbar
	 */
	public function add_toolbar_node( $toolbar ): void {
		$toolbar->add_node( [
			'id'    => 'dem_settings',
			'title' => 'Democracy',
			'href'  => plugin()->admin_page_url,
		] );

		$list = [
			''                 => __( 'Polls List', 'democracy-poll' ),
			'add_new'          => __( 'Add Poll', 'democracy-poll' ),
			'logs'             => __( 'Logs', 'democracy-poll' ),
			'general_settings' => __( 'Settings', 'democracy-poll' ),
			'design'           => __( 'Theme Settings', 'democracy-poll' ),
			'l10n'             => __( 'Texts changes', 'democracy-poll' ),
		];

		if( ! plugin()->super_access ){
			unset( $list['general_settings'], $list['design'], $list['l10n'] );
		}

		foreach( $list as $subpage => $title ){
			$toolbar->add_node( [
				'parent' => 'dem_settings',
				'id'     => $subpage ?: 'polls_list',
				'title'  => $title,
				'href'   => add_query_arg( [ 'subpage' => $subpage ], plugin()->admin_page_url ),
			] );
		}
	}

}
