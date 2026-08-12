<?php
/**
 * @noinspection PhpUnnecessaryLocalVariableInspection
 * @noinspection OneTimeUseVariablesInspection
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */

namespace DemocracyPoll\Admin;

use DemocracyPoll\Options;
use DemocracyPoll\Plugin;

class Admin {

	private Options $options;
	private Plugin $plugin;
	private Admin_Page $admin_page;
	private Tinymce_Button $tinymce_button;

	public function __construct(
		Plugin $plugin,
		Options $options,
		/** @see Admin_Page::__construct() */
		Admin_Page $admin_page,
		Tinymce_Button $tinymce_button
	) {
		$this->plugin = $plugin;
		$this->options = $options;
		$this->admin_page = $admin_page;
		$this->tinymce_button = $tinymce_button;
	}

	public function init(): void {
		$this->admin_page->init();

		add_filter( 'plugin_action_links', [ $this, '_plugin_action_setting_page_link' ], 10, 2 );

		// TinyMCE button WP 2.5+
		if( $this->options->tinymce_button ){
			$this->tinymce_button->register();
		}

		if( ! $this->options->post_metabox_off ){
			Post_Metabox::init();
		}
	}

	public function _plugin_action_setting_page_link( $actions, $plugin_file ) {
		if( false === strpos( $plugin_file, basename( $this->plugin->dir ) ) ){
			return $actions;
		}

		$settings_link = sprintf( '<a href="%s">%s</a>', $this->plugin->admin_page_url, __( 'Settings', 'democracy-poll' ) );
		array_unshift( $actions, $settings_link );

		return $actions;
	}

}
