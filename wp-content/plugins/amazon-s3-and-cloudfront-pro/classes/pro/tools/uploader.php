<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Tools;

use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Background_Tool_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Uploader_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Tool;
use Exception;

class Uploader extends Background_Tool {

	/**
	 * @var string
	 */
	protected $tool_key = 'uploader';

	/**
	 * Initialize Uploader
	 */
	public function init() {
		parent::init();

		$this->error_setting_migration();
	}

	/**
	 * Migrate old upload errors to new setting key.
	 *
	 * TODO: Move this into next migration so it no longer fires
	 * TODO: every time the plugin serves a request for tool state!
	 */
	protected function error_setting_migration() {
		$errors = $this->as3cf->get_setting( 'bulk_upload_errors', false );

		if ( ! empty( $errors ) ) {
			$this->update_errors( $errors );
			$this->as3cf->remove_setting( 'bulk_upload_errors' );
			$this->as3cf->save_settings();
		}
	}

	/**
	 * Get info for tool, including current status.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_info() {
		$media_counts     = $this->as3cf->media_counts();
		$human_percentage = $this->percent_offloaded();

		$strings = array(
			'title_initial'          => __( 'Your media needs to be offloaded', 'amazon-s3-and-cloudfront' ),
			'title_partial_complete' => __( '<strong>%s%%</strong> of your media has been offloaded', 'amazon-s3-and-cloudfront' ),
			'title_complete'         => __( '<strong>100%</strong> of your media has been offloaded, congratulations!', 'amazon-s3-and-cloudfront' ),
		);

		switch ( $human_percentage ) {
			case 0: // Entire library needs uploading
				$progress_description = $strings['title_initial'];
				break;

			case 100: // Entire media library uploaded
				$progress_description = $strings['title_complete'];
				break;

			default: // Media library upload partially complete
				$progress_description = sprintf( $strings['title_partial_complete'], $human_percentage );
		}

		$args = array(
			'total_progress'       => $human_percentage,
			'total_processed'      => (int) $media_counts['offloaded'],
			'total_items'          => (int) $media_counts['total'],
			'progress_description' => $progress_description,
		);

		return array_merge( parent::get_info(), $args );
	}

	/**
	 * Calculate the percentage of media library items offloaded, to the nearest integer.
	 *
	 * @return int
	 */
	private function percent_offloaded() {
		static $human_percentage;

		if ( is_null( $human_percentage ) ) {
			$media_counts = $this->as3cf->media_counts();

			if ( empty( $media_counts['total'] ) || empty( $media_counts['offloaded'] ) ) {
				$human_percentage = 0;
			} else {
				$uploaded_percentage = (float) $media_counts['offloaded'] / $media_counts['total'];
				$human_percentage    = (int) floor( $uploaded_percentage * 100 );

				// Percentage of library needs uploading.
				if ( 0 === $human_percentage && $uploaded_percentage > 0 ) {
					$human_percentage = 1;
				}
			}
		}

		return $human_percentage;
	}

	/**
	 * Should the tool be rendered?
	 *
	 * @return bool
	 */
	public function should_render() {
		// Don't show tool if pro not set up.
		if ( ! $this->as3cf->is_pro_plugin_setup() ) {
			return false;
		}

		$media_counts = $this->as3cf->media_counts();

		// Don't show upload tool if media library empty.
		if ( 0 === $media_counts['total'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Message for error notice
	 *
	 * @param string|null $message Optional message to override the default for the tool.
	 *
	 * @return string
	 */
	protected function get_error_notice_message( $message = null ) {
		$title   = __( 'Offload Errors', 'amazon-s3-and-cloudfront' );
		$message = empty( $message ) ? __( 'Previous attempts at offloading your media library have resulted in errors.', 'amazon-s3-and-cloudfront' ) : $message;

		return sprintf( '<strong>%s</strong> &mdash; %s', $title, $message );
	}

	/**
	 * Handle start.
	 */
	public function handle_start() {
		$notice_id = $this->get_tool_key() . '_license_limit';
		$this->as3cf->notices->remove_notice_by_id( $notice_id );

		parent::handle_start();
	}

	/**
	 * Get the tool's name.
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Offload Media', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get title text.
	 *
	 * @return string
	 */
	public function get_title_text() {
		return __( 'Your media needs to be offloaded', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get title text for when tool is partially complete.
	 *
	 * @return string
	 */
	public function get_title_text_partial_complete() {
		return __( 'Offload Media', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get title text for when tool is complete.
	 *
	 * @return string
	 */
	public function get_title_text_complete() {
		return __( 'Offload Media', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get more info text.
	 *
	 * @return string
	 */
	public static function get_more_info_text() {
		return __( 'This tool goes through all your media items and offloads their files to the bucket.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get prompt text for when tool could be run in response to settings change.
	 * Defaults to more info text.
	 *
	 * @return string
	 */
	public static function get_prompt_text() {
		global $as3cf;

		$mesg = __( 'You\'ve enabled the "Offload Media" option. Do you want to copy all yet to be offloaded media files to the bucket?', 'amazon-s3-and-cloudfront' );
		$mesg .= ' ';
		$mesg .= $as3cf::settings_more_info_link(
			'copy-to-s3',
			'',
			'copy+to+s3'
		);

		return $mesg;
	}

	/**
	 * Get button text.
	 *
	 * @return string
	 */
	public function get_button_text() {
		return __( 'Offload Now', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get button text for when tool is partially complete.
	 *
	 * @return string
	 */
	public function get_button_text_partial_complete() {
		return __( 'Offload Remaining Now', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get queued status text.
	 *
	 * @return string
	 */
	public function get_queued_status(): string {
		return __( 'Offloading media items to bucket.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get short queued status text.
	 *
	 * @return string
	 */
	public function get_short_queued_status(): string {
		return _x( 'Offloading…', 'Short tool running message', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get background process class.
	 *
	 * @return Background_Tool_Process|null
	 */
	protected function get_background_process_class() {
		return new Uploader_Process( $this->as3cf, $this );
	}
}
