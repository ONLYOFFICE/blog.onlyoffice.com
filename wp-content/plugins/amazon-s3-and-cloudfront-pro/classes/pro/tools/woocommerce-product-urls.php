<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Tools;

use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Background_Tool_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Woocommerce_Product_Urls_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Tool;

class Woocommerce_Product_Urls extends Background_Tool {

	/**
	 * @var string
	 */
	protected $tool_key = 'woocommerce_product_urls';

	/**
	 * Limit the item types that this tool handles.
	 *
	 * @var array
	 */
	protected $source_types = array(
		'media-library',
	);

	/**
	 * Message for error notice
	 *
	 * @param string|null $message Optional message to override the default for the tool.
	 *
	 * @return string
	 */
	protected function get_error_notice_message( $message = null ) {
		$title   = __( 'WooCommerce verify and update errors', 'amazon-s3-and-cloudfront' );
		$message = empty( $message ) ? __( 'Previous attempts at verifying and updating WooCommerce products have resulted in errors.', 'amazon-s3-and-cloudfront' ) : $message;

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

		if ( defined( 'AS3CF_SHOW_WOOCOMMERCE_TOOL' ) && AS3CF_SHOW_WOOCOMMERCE_TOOL ) {
			return true;
		}

		if ( defined( 'AS3CF_SHOW_WOOCOMMERCE_TOOL' ) && ! AS3CF_SHOW_WOOCOMMERCE_TOOL ) {
			return false;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
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
		return __( 'WooCommerce Analyze &amp; Repair', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get title text.
	 *
	 * @return string
	 */
	public function get_title_text() {
		return __( 'Verify and update WooCommerce downloadable products', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get more info text.
	 *
	 * @return string
	 */
	public static function get_more_info_text() {
		return __(
			'This tools goes through all your WooCommerce products with downloadable files. It marks all 
			downloadable files as private in the bucket. It also replaces shortcodes created by previous versions 
			of this plugin with proper URLs.',
			'amazon-s3-and-cloudfront'
		);
	}

	/**
	 * Get button text.
	 *
	 * @return string
	 */
	public function get_button_text() {
		return __( 'Verify and update', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get queued status text.
	 *
	 * @return string
	 */
	public function get_queued_status(): string {
		return __( 'Verify and update WooCommerce downloadable files', 'amazon-s3-and-cloudfront' );
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
		return new Woocommerce_Product_Urls_Process( $this->as3cf, $this );
	}
}
