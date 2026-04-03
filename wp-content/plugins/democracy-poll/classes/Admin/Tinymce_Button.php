<?php
## tinymce кнопка

namespace DemocracyPoll\Admin;

use function DemocracyPoll\plugin;

class Tinymce_Button {

	public static function init() {
		add_filter( 'mce_external_plugins', [ __CLASS__, 'tinymce_plugin' ] );
		add_filter( 'mce_buttons', [ __CLASS__, 'tinymce_register_button' ] );
		add_filter( 'wp_mce_translation', [ __CLASS__, 'tinymce_l10n' ] );
	}

	public static function tinymce_register_button( $buttons ) {
		array_push( $buttons, 'separator', 'demTiny' );

		return $buttons;
	}

	public static function tinymce_plugin( $plugin_array ) {
		$plugin_array['demTiny'] = plugin()->url . '/js/tinymce.js';

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
