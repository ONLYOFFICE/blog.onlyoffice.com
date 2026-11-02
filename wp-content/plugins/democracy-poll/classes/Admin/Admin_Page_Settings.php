<?php

namespace DemocracyPoll\Admin;

use DemocracyPoll\Support\Messages;
use DemocracyPoll\Options;
use DemocracyPoll\Plugin;

class Admin_Page_Settings implements Admin_Subpage_Interface {

	private Plugin $plugin;
	private Admin_Page $admpage;
	private Messages $messages;
	private Options $options;

	public function __construct(
		Plugin $plugin,
		Admin_Page $admin_page,
		Messages $messages,
		Options $options
	){
		$this->plugin = $plugin;
		$this->admpage = $admin_page;
		$this->messages = $messages;
		$this->options = $options;
	}

	public function load(): void {
	}

	public function request_handler(): void {
		if( ! $this->plugin->super_access || ! Admin_Page::check_nonce() ){
			return;
		}

		$up = null;
		if( isset( $_POST['dem_save_main_options'] ) ){
			$up = $this->options->handle_update_options( 'main' );
		}
		if( isset( $_POST['dem_reset_main_options'] ) ){
			$up = $this->options->reset_options( 'main' );
		}

		if( $up !== null ){
			$up
				? $this->messages->add_ok( __( 'Updated', 'democracy-poll' ) )
				: $this->messages->add_notice( __( 'Nothing was updated', 'democracy-poll' ) );
		}

		// Handle the request to create an archive page.
		if( isset( $_GET['dem_create_archive_page'] ) ){
			$this->dem_create_archive_page();
		}
	}

	public function render(): void {
		echo $this->admpage->subpages_menu();

		if( ! $this->plugin->super_access ){
			return;
		}

		require __DIR__ . '/tpl/settings.php';
	}

	/**
	 * Creates the archive page.
	 * Saves the URL of the created page in the plugin option.
	 * Before creating, checks if such a page already exists.
	 *
	 * @return false|void
	 */
	protected function dem_create_archive_page() {
		global $wpdb;

		// try to find the archive page
		$page = $wpdb->get_row(
			"SELECT * FROM $wpdb->posts WHERE post_content LIKE '[democracy_archives]' AND post_status = 'publish' LIMIT 1"
		);

		if( $page ){
			$page_id = $page->ID;
		}
		// create a new page
		else{
			$page_id = wp_insert_post( [
				'post_title'   => __( 'Polls Archive', 'democracy-poll' ),
				'post_content' => '[democracy_archives]',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_name'    => 'democracy-archives',
			] );

			if( ! $page_id ){
				return false;
			}
		}

		// update option
		$this->options->update_single_option( 'archive_page_id', $page_id );

		wp_redirect( remove_query_arg( 'dem_create_archive_page' ) );
	}

}
