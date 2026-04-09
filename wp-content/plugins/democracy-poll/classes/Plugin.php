<?php

namespace DemocracyPoll;

use DemocracyPoll\Admin\Admin;
use DemocracyPoll\Helpers\Helpers;
use DemocracyPoll\Helpers\Messages;

class Plugin {

	/** Plugin version. Eg: 1.2 */
	public string $ver;

	/** URL to the plugin directory. Without trailing slash. */
	public string $url;

	/** Path to the plugin directory. Without trailing slash. */
	public string $dir;

	/** URL to the main plugin settings page. */
	public string $admin_page_url;

	/** Only access to add/edit poll and so on. */
	public bool $admin_access;

	/** Full access to change settings and so on. */
	public bool $super_access;

	/** Whether page caching is enabled */
	public bool $is_cachegear_on;

	public Plugin_Initor $initor;

	public Options $opt;

	public Admin $admin;

	public Messages $msg;

	public Poll_Ajax $poll_ajax;

	public function __construct( string $main_file ) {
		$this->ver = get_file_data( $main_file, [ 'ver' => 'Version' ] )['ver'];
		$this->dir = dirname( $main_file );
		$this->url = plugins_url( '', $main_file );

		$this->admin_page_url = admin_url( 'options-general.php?page=' . basename( $this->dir ) );

		$this->opt = new Options();
		$this->msg = new Messages();
		$this->initor = new Plugin_Initor();
	}

	public function set_access_caps(): void {
		$has_super_access = current_user_can( 'manage_options' );

		/**
		 * Allows to change the access to be able to change the plugin settings.
		 *
		 * @param bool $has_super_access  Default is true if the user has the 'manage_options' capability.
		 */
		$this->super_access = (bool) apply_filters( 'dem_super_access', $has_super_access );

		// access to add/edit poll and so on...
		$this->admin_access = $has_super_access;

		// open admin manage access for other roles
		if( ! $this->admin_access && $this->opt->access_roles ){
			foreach( wp_get_current_user()->roles as $role ){
				if( in_array( $role, $this->opt->access_roles, true ) ){
					$this->admin_access = true;
					break;
				}
			}
		}
	}

	public function set_is_cachegear_on(): void {
		if( $this->opt->force_cachegear ){
			$this->is_cachegear_on = true;
			return;
		}

		/**
		 * Allows to change the status of the page cache plugin.
		 *
		 * @param bool|null $status  If null, the plugin will check if the page cache plugin is active.
		 *                           If true, it means that the page cache plugin is active.
		 *                           If false, it means that the page cache plugin is NOT active.
		 */
		$status = apply_filters( 'dem_cachegear_status', null );
		if( null !== $status ){
			$this->is_cachegear_on = (bool) $status;
			return;
		}

		$this->is_cachegear_on = Helpers::is_page_cache_plugin_on();
	}

}

