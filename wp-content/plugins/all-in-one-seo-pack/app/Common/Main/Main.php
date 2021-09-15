<?php
namespace AIOSEO\Plugin\Common\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class Main {
	/**
	 * Construct method.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->media = new Media();

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueueFrontEndAssets' ] );
		add_action( 'admin_footer', [ $this, 'adminFooter' ] );
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function enqueueAssets() {
		// Scripts.
		$standalone = [
			'app',
			'notifications'
		];

		foreach ( $standalone as $key ) {
			aioseo()->helpers->enqueueScript(
				'aioseo-' . $key,
				'js/' . $key . '.js'
			);
		}

		aioseo()->helpers->enqueueScript(
			'aioseo-vendors',
			'js/chunk-vendors.js'
		);
		aioseo()->helpers->enqueueScript(
			'aioseo-common',
			'js/chunk-common.js'
		);

		wp_localize_script(
			'aioseo-app',
			'aioseoTranslations',
			[
				'translations' => aioseo()->helpers->getJedLocaleData( 'all-in-one-seo-pack' )
			]
		);

		wp_localize_script(
			'aioseo-notifications',
			'aioseoNotifications',
			[
				'newNotifications' => count( Models\Notification::getNewNotifications() )
			]
		);

		// Styles.
		$rtl = is_rtl() ? '.rtl' : '';
		aioseo()->helpers->enqueueStyle(
			'aioseo-common',
			"css/chunk-common$rtl.css"
		);
		aioseo()->helpers->enqueueStyle(
			'aioseo-vendors',
			"css/chunk-vendors$rtl.css"
		);

		foreach ( $standalone as $key ) {
			aioseo()->helpers->enqueueStyle(
				"aioseo-$key-style",
				"css/$key$rtl.css"
			);
		}
	}

	/**
	 * Enqueue styles on the front-end.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function enqueueFrontEndAssets() {
		$canManageSeo = apply_filters( 'aioseo_manage_seo', 'aioseo_manage_seo' );
		if (
			! is_user_logged_in() ||
			! ( current_user_can( $canManageSeo ) || aioseo()->access->canManage() )
		) {
			return;
		}

		// Styles.
		aioseo()->helpers->enqueueStyle(
			'aioseo-admin-bar',
			'css/aioseo-admin-bar.css',
			false
		);
	}

	/**
	 * Enqueue the footer file to let vue attach.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function adminFooter() {
		echo '<div id="aioseo-admin"></div>';
	}
}