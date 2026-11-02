<?php
## TinyMCE button.

namespace DemocracyPoll\Admin;

use DemocracyPoll\Plugin;

class Tinymce_Button {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Registers callbacks with explicitly injected dependencies.
	 */
	public function register(): void {
		add_filter( 'mce_external_plugins', [ $this, 'add_tinymce_plugin' ] );
		add_filter( 'mce_buttons', [ self::class, 'tinymce_register_button' ] );
		add_filter( 'wp_mce_translation', [ self::class, 'tinymce_l10n' ] );
	}

	public static function tinymce_register_button( $buttons ) {
		array_push( $buttons, 'separator', 'demTiny' );

		return $buttons;
	}

	public function add_tinymce_plugin( $plugin_array ) {
		$plugin_array['demTiny'] = $this->plugin->url . '/assets/admin/tinymce.js';

		return $plugin_array;
	}

	public static function tinymce_l10n( $mce_l10n ): array {

		$l10n = array_map( 'esc_js', [
			'Insert Poll of Democracy' => __( 'Insert Poll of Democracy', 'democracy-poll' ),
			'Insert Poll ID'           => __( 'Insert Poll ID', 'democracy-poll' ),
		] );

		return $mce_l10n + $l10n;
	}

}
