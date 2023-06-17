<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Tools;

use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Background_Tool_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Downloader_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Tool;

class Downloader extends Background_Tool {

	/**
	 * @var string
	 */
	protected $tool_key = 'downloader';

	/**
	 * @var bool
	 */
	protected static $deactivate_prompt_rendered = false;

	/**
	 * Initialize Downloader
	 */
	public function init() {
		parent::init();

		if ( ! $this->as3cf->is_pro_plugin_setup( true ) ) {
			return;
		}

		$this->maybe_render_deactivate_prompt();
	}

	/**
	 * Maybe render deactivate plugin prompt.
	 */
	private function maybe_render_deactivate_prompt() {
		if ( self::$deactivate_prompt_rendered ) {
			return;
		}

		if ( ! $this->as3cf->get_setting( 'remove-local-file' ) ) {
			return;
		}

		add_action( 'load-plugins.php', array( $this, 'deactivate_plugin_assets' ) );
		add_action( 'admin_footer', array( $this, 'deactivate_plugin_modal' ) );

		self::$deactivate_prompt_rendered = true;
	}

	/**
	 * Register the modal for plugin deactivation
	 *
	 * TODO: Could be replaced with Modal.svelte using REST-API for modal button's actions etc.
	 */
	public function deactivate_plugin_assets() {
		$this->as3cf->enqueue_script(
			'as3cf-pro-downloader-deactivate-plugin-script',
			'assets/js/pro/tools/downloader-deactivate-plugin',
			array( 'as3cf-modal', 'wp-api-request' )
		);

		wp_localize_script( 'as3cf-pro-downloader-deactivate-plugin-script', 'as3cfpro_downloader', array(
			'plugin_url'  => $this->as3cf->get_plugin_page_url( array( 'hash' => '/tools' ) ),
			'plugin_slug' => $this->as3cf->get_plugin_row_slug(),
		) );

		wp_enqueue_style( 'as3cf-modal' );
	}

	/**
	 * Load the view for the plugin deactivation modal
	 */
	public function deactivate_plugin_modal() {
		global $pagenow;
		if ( 'plugins.php' !== $pagenow ) {
			return;
		}

		$this->as3cf->render_view( 'deactivate-plugin-prompt' );
	}

	/**
	 * Message for error notice
	 *
	 * @param string|null $message Optional message to override the default for the tool.
	 *
	 * @return string
	 */
	protected function get_error_notice_message( $message = null ) {
		$title   = __( 'Download Errors', 'amazon-s3-and-cloudfront' );
		$message = empty( $message ) ? __( 'Previous attempts at downloading your media library from the bucket have resulted in errors.', 'amazon-s3-and-cloudfront' ) : $message;

		return sprintf( '<strong>%s</strong> &mdash; %s', $title, $message );
	}

	/**
	 * Should render.
	 *
	 * @return bool
	 */
	public function should_render() {
		if ( ! $this->as3cf->is_pro_plugin_setup() ) {
			return false;
		}

		return (bool) $this->count_offloaded_media_files();
	}

	/**
	 * Get title text.
	 *
	 * @return string
	 */
	public function get_title_text() {
		return __( 'Download all files from bucket to server', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get more info text.
	 *
	 * @return string
	 */
	public static function get_more_info_text() {
		return __( 'If you\'ve ever had the "Remove Local Media" option on, some media files are likely missing on your server. You can use this tool to download any missing files back to your server.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get prompt text for when tool could be run in response to settings change.
	 *
	 * @return string
	 */
	public static function get_prompt_text() {
		global $as3cf;

		$mesg = __( 'You\'ve disabled the "Remove Local Media" option. Do you want to download all media files previously offloaded and removed from the server? This tool will not remove the media files from the bucket, and only downloads files that are missing from the server.', 'amazon-s3-and-cloudfront' );
		$mesg .= ' ';
		$mesg .= $as3cf::settings_more_info_link(
			'remove-local-file',
			'',
			'remove+local+file'
		);

		return $mesg;
	}

	/**
	 * Get button text.
	 *
	 * @return string
	 */
	public function get_button_text() {
		return __( 'Download Files', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get queued status text.
	 *
	 * @return string
	 */
	public function get_queued_status(): string {
		return __( 'Downloading media from bucket', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get short queued status text.
	 *
	 * @return string
	 */
	public function get_short_queued_status(): string {
		return _x( 'Downloading…', 'Short tool running message', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get background process class.
	 *
	 * @return Background_Tool_Process|null
	 */
	protected function get_background_process_class() {
		return new Downloader_Process( $this->as3cf, $this );
	}
}
