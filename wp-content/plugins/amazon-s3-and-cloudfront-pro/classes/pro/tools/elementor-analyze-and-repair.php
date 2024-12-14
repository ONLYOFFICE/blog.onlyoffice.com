<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Tools;

use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Background_Tool_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Elementor_Analyze_And_Repair_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Tool;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\Elementor;

class Elementor_Analyze_And_Repair extends Background_Tool {

	/**
	 * @var string
	 */
	protected $tool_key = 'elementor_analyze_and_repair';

	/**
	 * @var bool
	 */
	protected static $requires_bucket_access = false;

	/**
	 * Limit the item types that this tool handles.
	 *
	 * @var array
	 */
	protected $source_types = array(
		'media-library',
	);

	/**
	 * Initialize tool
	 */
	public function init() {
		parent::init();

		if ( ! $this->as3cf->is_pro_plugin_setup( true ) ) {
			return;
		}

		$this->maybe_show_notice();
	}

	/**
	 * Show a notice about this tool to the user under certain conditions.
	 *
	 * @return void
	 */
	private function maybe_show_notice() {
		if ( wp_doing_ajax() || ! Elementor::is_installed() ) {
			return;
		}

		$notice_id           = 'elementor-analyze-and-repair';
		$tool_completed_id   = $this->get_tool_key() . '_completed';
		$tool_not_needed_key = $this->prefix . '_' . $this->get_tool_key() . '_not_needed';
		$tool_not_needed     = get_site_option( $tool_not_needed_key ) !== false;

		// If the tool has already run or we previously deemed it's not needed just ensure the
		// notice isn't there, removing it if needed.
		$tool_completed_notice = $this->as3cf->notices->find_notice_by_id( $tool_completed_id );
		if ( ! empty( $tool_completed_notice ) || $tool_not_needed ) {
			$this->as3cf->notices->remove_notice_by_id( $notice_id );
			if ( false === $tool_not_needed ) {
				update_site_option( $tool_not_needed_key, time() );
			}

			return;
		}

		// Exit out if the notice already exists
		$notice = $this->as3cf->notices->find_notice_by_id( $notice_id );
		if ( ! empty( $notice ) ) {
			return;
		}

		// count posts with elementor and offloaded media files
		$media_counts    = $this->as3cf->media_counts();
		$process_object  = $this->get_background_process_class();
		$elementor_items = $process_object->get_elementor_items_count();

		// Either OME or Elementor have never been used, there can't be an overlap of items.
		if ( 0 === $media_counts['offloaded'] || 0 === $elementor_items ) {
			update_site_option( $tool_not_needed_key, time() );

			return;
		}

		$this->as3cf->notices->add_notice(
			sprintf(
				__(
					'There is content created with Elementor
						that may need to be checked for broken media links. Go to the WP Offload Media settings page and run the
						Elementor Analyze and repair tool. <a href="%s">Get started&nbsp;&raquo;</a>',
					'amazon-s3-and-cloudfront'
				),
				$this->as3cf->get_plugin_page_url()
			),
			array(
				'custom_id'         => $notice_id,
				'type'              => 'notice-info',
				'flash'             => false,
				'only_show_to_user' => false,
			)
		);
	}

	/**
	 * Message for error notice
	 *
	 * @param string|null $message Optional message to override the default for the tool.
	 *
	 * @return string
	 */
	protected function get_error_notice_message( $message = null ) {
		$title   = __( 'Elementor Analyze and Repair errors', 'amazon-s3-and-cloudfront' );
		$message = empty( $message ) ? __( 'Previous attempts at analyzing and repairing Elementor content resulted in errors', 'amazon-s3-and-cloudfront' ) : $message;

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

		if ( defined( 'AS3CF_SHOW_ELEMENTOR_TOOL' ) && AS3CF_SHOW_ELEMENTOR_TOOL ) {
			return true;
		}

		if ( defined( 'AS3CF_SHOW_ELEMENTOR_TOOL' ) && ! AS3CF_SHOW_ELEMENTOR_TOOL ) {
			return false;
		}

		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return false;
		}

		return (bool) $this->count_offloaded_media_files();
	}

	/**
	 * Get the tool's name.
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Elementor Analyze &amp; Repair', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get title text.
	 *
	 * @return string
	 */
	public function get_title_text() {
		return __( 'Analyze and repair content created with Elementor', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get more info text.
	 *
	 * @return string
	 */
	public static function get_more_info_text() {
		return __(
			'This tools goes through all content created with Elementor. It will check all media URLs found and if
			needed repair any broken URLs to offloaded media files.',
			'amazon-s3-and-cloudfront'
		);
	}

	/**
	 * Get button text.
	 *
	 * @return string
	 */
	public function get_button_text() {
		return __( 'Analyze and repair', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get queued status text.
	 *
	 * @return string
	 */
	public function get_queued_status(): string {
		return __( 'Analyze and repair URLs in Elementor content', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get short queued status text.
	 *
	 * @return string
	 */
	public function get_short_queued_status(): string {
		return _x( 'Repairing…', 'Short tool running message', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get background process class.
	 *
	 * @return Background_Tool_Process|null
	 */
	protected function get_background_process_class() {
		return new Elementor_Analyze_And_Repair_Process( $this->as3cf, $this );
	}
}
