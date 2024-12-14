<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Upgrades;

use Amazon_S3_And_CloudFront;
use DeliciousBrains\WP_Offload_Media\Upgrades\Network_Upgrade;

class Disable_Compatibility_Plugins extends Network_Upgrade {
	/**
	 * Network_Upgrade constructor.
	 *
	 * @param Amazon_S3_And_CloudFront $as3cf
	 * @param string                   $version
	 */
	public function __construct( $as3cf, $version ) {
		parent::__construct( $as3cf, $version );

		add_action( 'as3cf_init', array( $this, 'disable_obsolete_plugins' ), 5 );
		add_action( 'as3cf_pre_settings_render', array( $this, 'show_obsolete_notice' ) );
	}

	/**
	 * Perform upgrade logic.
	 */
	protected function do_upgrade() {
		$this->remove_existing_notice();
	}

	/**
	 * Remove existing compatibility notice.
	 */
	protected function remove_existing_notice() {
		$notice_id = 'as3cf-compat-addons';

		if ( $this->as3cf->notices->find_notice_by_id( $notice_id ) ) {
			$this->as3cf->notices->undismiss_notice_for_all( $notice_id );
			$this->as3cf->notices->remove_notice_by_id( $notice_id );
		}

		delete_site_option( 'as3cf_compat_addons_to_install' );
	}

	/**
	 * Show deactivation notice.
	 */
	protected function show_deactivation_notice() {
		$active_plugins = $this->get_active_plugins();

		if ( empty( $active_plugins ) ) {
			return;
		}

		$id      = 'disable-compat-plugins';
		$plugins = $this->render_plugins( $active_plugins );
		$args    = array(
			'type'              => 'notice-info',
			'custom_id'         => $id,
			'only_show_to_user' => false,
			'flash'             => false,
		);

		if ( $this->as3cf->notices->find_notice_by_id( $id ) ) {
			$this->as3cf->notices->undismiss_notice_for_all( $id );
			$this->as3cf->notices->remove_notice_by_id( $id );
		}

		$this->as3cf->notices->add_notice( $this->render_deactivation_notice( $plugins ), $args );
	}

	/**
	 * Render deactivation notice.
	 *
	 * @param string $plugins
	 *
	 * @return string
	 */
	protected function render_deactivation_notice( string $plugins ): string {
		$message   = __( "We've deactivated the following WP Offload Media addons:", 'amazon-s3-and-cloudfront' );
		$more_info = __( 'Integrations are now included in the core WP Offload Media plugin. The addon plugins listed above can safely be removed.', 'amazon-s3-and-cloudfront' );

		return $message . $plugins . $more_info;
	}

	/**
	 * Render plugins list.
	 *
	 * @param array $plugins
	 * @param bool  $uninstall
	 *
	 * @return string
	 */
	protected function render_plugins( array $plugins, bool $uninstall = false ): string {
		$html = '<ul style="list-style-type: disc; padding: 0 0 0 30px; margin: 5px 0;">';

		foreach ( $plugins as $plugin => $details ) {
			$html .= '<li style="margin: 0;">' . $details['name'];

			if ( $uninstall ) {
				$html .= ' (<a  href="' . wp_nonce_url( 'plugins.php?action=delete-selected&amp;checked[]=' . $plugin, 'bulk-plugins' ) . '">';
				$html .= _x( 'Remove', 'Remove plugin', 'amazon-s3-and-cloudfront' );
				$html .= '</a>)';
			}

			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Get compatibility plugins.
	 *
	 * @return array
	 */
	protected function get_plugins(): array {
		return array(
			'amazon-s3-and-cloudfront-acf-image-crop/amazon-s3-and-cloudfront-acf-image-crop.php'             => array(
				'name'        => __( 'ACF Image Crop', 'amazon-s3-and-cloudfront' ),
				'init_action' => 'aws_init',
				'init_func'   => 'as3cf_acf_image_crop_init',
			),
			'amazon-s3-and-cloudfront-edd/amazon-s3-and-cloudfront-edd.php'                                   => array(
				'name'        => __( 'Easy Digital Downloads', 'amazon-s3-and-cloudfront' ),
				'init_action' => 'aws_init',
				'init_func'   => 'as3cf_edd_init',
			),
			'amazon-s3-and-cloudfront-enable-media-replace/amazon-s3-and-cloudfront-enable-media-replace.php' => array(
				'name'        => __( 'Enable Media Replace', 'amazon-s3-and-cloudfront' ),
				'init_action' => 'aws_init',
				'init_func'   => 'as3cf_enable_media_replace_init',
			),
			'amazon-s3-and-cloudfront-meta-slider/amazon-s3-and-cloudfront-meta-slider.php'                   => array(
				'name'        => __( 'Meta Slider', 'amazon-s3-and-cloudfront' ),
				'init_action' => 'aws_init',
				'init_func'   => 'as3cf_meta_slider_init',
			),
			'amazon-s3-and-cloudfront-woocommerce/amazon-s3-and-cloudfront-woocommerce.php'                   => array(
				'name'        => __( 'WooCommerce', 'amazon-s3-and-cloudfront' ),
				'init_action' => 'aws_init',
				'init_func'   => 'as3cf_woocommerce_init',
			),
			'amazon-s3-and-cloudfront-wpml/amazon-s3-and-cloudfront-wpml.php'                                 => array(
				'name'        => __( 'WPML', 'amazon-s3-and-cloudfront' ),
				'init_action' => 'aws_init',
				'init_func'   => 'as3cf_wpml_init',
			),
			'amazon-s3-and-cloudfront-assets/amazon-s3-and-cloudfront-assets.php'                             => array(
				'name'        => __( 'Assets', 'amazon-s3-and-cloudfront' ),
				'init_action' => 'as3cf_pro_init',
				'init_func'   => 'as3cf_assets_init',
			),
			'amazon-s3-and-cloudfront-assets-pull/amazon-s3-and-cloudfront-assets-pull.php'                   => array(
				'name'        => __( 'Assets Pull', 'amazon-s3-and-cloudfront' ),
				'init_action' => 'as3cf_pro_init',
				'init_func'   => 'as3cf_assets_pull_init',
			),
		);
	}

	/**
	 * Get active plugins.
	 *
	 * @return array
	 */
	protected function get_active_plugins(): array {
		static $active_plugins;

		if ( is_null( $active_plugins ) ) {
			$active_plugins = array();

			foreach ( $this->get_plugins() as $plugin => $details ) {
				if ( is_plugin_active( $plugin ) ) {
					$active_plugins[ $plugin ] = $details;
				}
			}
		}

		return $active_plugins;
	}

	/**
	 * Get installed plugins.
	 *
	 * @return array
	 */
	protected function get_installed_plugins(): array {
		$plugins           = get_plugins();
		$installed_plugins = array();

		foreach ( $this->get_plugins() as $plugin => $details ) {
			if ( array_key_exists( $plugin, $plugins ) ) {
				$installed_plugins[ $plugin ] = $details;
			}
		}

		return $installed_plugins;
	}

	/**
	 * Show obsolete notice.
	 */
	public function show_obsolete_notice() {
		$id                = 'remove-compat-plugins';
		$installed_plugins = $this->get_installed_plugins();

		if ( empty( $installed_plugins ) ) {
			if ( $this->as3cf->notices->find_notice_by_id( $id ) ) {
				$this->as3cf->notices->remove_notice_by_id( $id );
			}

			return;
		}

		$plugins = $this->render_plugins( $installed_plugins, true );
		$args    = array(
			'type'                  => 'notice-info',
			'dismissible'           => false,
			'flash'                 => false,
			'only_show_to_user'     => false,
			'only_show_in_settings' => true,
			'custom_id'             => $id,
		);

		$this->as3cf->notices->add_notice( $this->render_obsolete_notice( $plugins ), $args );
	}

	/**
	 * Render obsolete notice.
	 *
	 * @param string $plugins
	 *
	 * @return string
	 */
	protected function render_obsolete_notice( string $plugins ): string {
		$message   = __( 'Please remove the following obsolete addons:', 'amazon-s3-and-cloudfront' );
		$more_info = __( 'Integrations are now included in the core WP Offload Media plugin. Once these addons are removed this message will go away.', 'amazon-s3-and-cloudfront' );

		return $message . $plugins . $more_info;
	}

	/**
	 * Disable obsolete plugins.
	 */
	public function disable_obsolete_plugins() {
		$this->show_deactivation_notice();

		$plugins = $this->get_active_plugins();

		if ( ! empty( $plugins ) ) {
			foreach ( $plugins as $details ) {
				$priority = has_action( $details['init_action'], $details['init_func'] );

				if ( $priority ) {
					remove_action( $details['init_action'], $details['init_func'], $priority );
				}
			}
			deactivate_plugins( array_keys( $plugins ), true );
		}
	}
}
