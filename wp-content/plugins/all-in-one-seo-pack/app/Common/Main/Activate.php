<?php
namespace AIOSEO\Plugin\Common\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class Activate {
	/**
	 * Construct method.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		register_activation_hook( AIOSEO_FILE, [ $this, 'activate' ] );
		register_deactivation_hook( AIOSEO_FILE, [ $this, 'deactivate' ] );

		// If Pro just deactivated the lite version, we need to manually run the activation hook, because it doesn't run here.
		$proDeactivatedLite = (bool) aioseo()->transients->get( 'pro_just_deactivated_lite' );
		if ( $proDeactivatedLite ) {
			aioseo()->transients->delete( 'pro_just_deactivated_lite', true );
			$this->activate( false );
		}
	}

	/**
	 * Runs on activation.
	 *
	 * @since 4.0.17
	 *
	 * @param  bool $networkWide Whether or not this is a network wide activation.
	 * @return void
	 */
	public function activate( $networkWide ) {
		aioseo()->access->addCapabilities();

		// Make sure our tables exist.
		aioseo()->updates->addInitialCustomTablesForV4();

		// Set the activation timestamps.
		$time = time();
		aioseo()->internalOptions->internal->activated = $time;

		if ( ! aioseo()->internalOptions->internal->firstActivated ) {
			aioseo()->internalOptions->internal->firstActivated = $time;
		}

		aioseo()->transients->clearCache();
	}

	/**
	 * Runs on deactivation.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function deactivate() {
		aioseo()->access->removeCapabilities();
		\AIOSEO\Plugin\Common\Sitemap\Rewrite::removeRewriteRules( [], true );
	}
}