<?php

namespace DemocracyPoll\System;

use DemocracyPoll\Options;
use DemocracyPoll\Plugin;
use DemocracyPoll\Poll_Ajax;
use DemocracyPoll\Poll_Widget;
use DemocracyPoll\Shortcodes;
use DemocracyPoll\Support\Kses;
use DemocracyPoll\Admin\Admin;
use DemocracyPoll\Admin\Admin_Page_Logs;
use DemocracyPoll\Admin\Admin_Page_l10n;
use function DemocracyPoll\container;

class Plugin_Initor {

	private Plugin $plugin;
	private Options $options;
	private Poll_Ajax $poll_ajax;
	private Shortcodes $shortcodes;

	public function __construct(
		Plugin $plugin,        /** @see Plugin::__construct() */
		Options $options,
		Poll_Ajax $poll_ajax,  /** @see Poll_Ajax::__construct() */
		Shortcodes $shortcodes /** @see Shortcodes::__construct() */
	) {
		$this->plugin = $plugin;
		$this->options = $options;
		$this->poll_ajax = $poll_ajax;
		$this->shortcodes = $shortcodes;
	}

	public function basic_init(): void {
		$this->options->set_opt();

		Activator::set_db_tables();
		if( is_multisite() ){
			add_action( 'switch_blog', [ Activator::class, 'set_db_tables' ] );
		}

		$this->plugin->set_access_caps();
		Kses::setup_allowed_tags();
		$this->load_textdomain();
	}

	public function init_plugin(): void {
		$this->basic_init();

		$this->plugin->set_is_cachegear_on();

		$this->admin_init();

		$this->init_shortcodes();
		$this->init_ajax();

		// For front-end localization and custom translation
		Admin_Page_l10n::add_gettext_filter();

		$this->init_menu_in_toolbar();
		$this->init_hide_form_indexing();
		$this->init_wp_widget();

		if( get_option( Upgrader::VER_OPT_NAME ) !== $this->plugin->ver ){
			container()->get( Upgrader::class )->upgrade(); /** @see Upgrader::__construct */
		}
	}

	private function admin_init(): void {
		if( is_admin() && ! wp_doing_ajax() ){
			container()->get( Admin::class )->init(); /** @see Admin_Page::__construct() lazy construct */
		}
	}

	private function init_shortcodes(): void {
		$this->shortcodes->init();
	}

	private function init_ajax(): void {
		$this->poll_ajax->init();

		if( wp_doing_ajax() ){
			Admin_Page_Logs::init_ajax();
		}
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'democracy-poll', false, basename( $this->plugin->dir ) . '/languages/build/' );
	}

	private function init_wp_widget(): void {
		if( $this->options->use_widget ){
			add_action( 'widgets_init', static function() {
				register_widget( Poll_Widget::class );
			} );
		}
	}

	private function init_menu_in_toolbar(): void {
		if( $this->plugin->admin_access && $this->options->toolbar_menu ){
			add_action( 'admin_bar_menu', [ $this, 'add_toolbar_node' ], 99 );
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
			'href'  => $this->plugin->admin_page_url,
		] );

		$list = [
			''                 => __( 'Polls List', 'democracy-poll' ),
			'add_new'          => __( 'Add Poll', 'democracy-poll' ),
			'logs'             => __( 'Logs', 'democracy-poll' ),
			'general_settings' => __( 'Settings', 'democracy-poll' ),
			'design'           => __( 'Theme Settings', 'democracy-poll' ),
			'l10n'             => __( 'Text changes', 'democracy-poll' ),
		];

		if( ! $this->plugin->super_access ){
			unset( $list['general_settings'], $list['design'], $list['l10n'] );
		}

		foreach( $list as $subpage => $title ){
			$toolbar->add_node( [
				'parent' => 'dem_settings',
				'id'     => $subpage ?: 'polls_list',
				'title'  => $title,
				'href'   => add_query_arg( [ 'subpage' => $subpage ], $this->plugin->admin_page_url ),
			] );
		}
	}

}
