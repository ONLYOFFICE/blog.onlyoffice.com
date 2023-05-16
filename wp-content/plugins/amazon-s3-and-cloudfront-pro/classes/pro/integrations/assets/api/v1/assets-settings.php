<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Integrations\Assets\API\V1;

use DeliciousBrains\WP_Offload_Media\API\V1\Settings;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\Assets\Assets;

class Assets_Settings extends Settings {
	/** @var int */
	protected static $version = 1;

	/** @var string */
	protected static $name = 'assets-settings';

	/**
	 * Common response values for this API endpoint.
	 *
	 * @return array
	 */
	public function common_response(): array {
		/** @var Assets|null */
		$assets = $this->as3cf->get_integration_manager()->get_integration( 'assets' );

		return array(
			'assets_settings'         => $assets->obfuscate_sensitive_settings( $assets->get_all_settings() ),
			'assets_defined_settings' => array_keys( $assets->get_defined_settings() ),
		);
	}

	/**
	 * Handle saving settings submitted by user.
	 *
	 * @param array $new_settings
	 *
	 * @return array
	 */
	protected function save_settings( array $new_settings ): array {
		$changed_keys = array();

		do_action( 'as3cf_pre_save_assets_settings' );

		/** @var Assets $assets */
		$assets = $this->as3cf->get_integration_manager()->get_integration( 'assets' );

		$allowed      = $assets->get_allowed_settings_keys();
		$old_settings = $assets->get_all_settings( false );

		// Merge in defined settings as they take precedence and must overwrite anything supplied.
		// Only needed to allow for validation, during save defined settings are removed from array anyway.
		$new_settings = array_merge( $new_settings, $assets->get_defined_settings() );

		foreach ( $allowed as $key ) {
			// Whether defined or not, get rid of old database setting for key.
			$assets->remove_setting( $key );

			if ( ! isset( $new_settings[ $key ] ) ) {
				continue;
			}

			$value = $assets->sanitize_setting( $key, $new_settings[ $key ] );

			$assets->set_setting( $key, $value );

			if ( $this->setting_changed( $old_settings, $key, $value ) ) {
				$changed_keys[] = $key;
			}
		}

		// Great success ...
		$assets->save_settings();

		do_action( 'as3cf_post_save_assets_settings', true );

		return $changed_keys;
	}
}
