<?php

namespace DemocracyPoll\Admin;

use DemocracyPoll\Support\Kses;
use DemocracyPoll\Support\Messages;
use DemocracyPoll\Plugin;

class Admin_Page_l10n implements Admin_Subpage_Interface {

	private const OLD_VOTES_PERCENT_TEXT = '%s - %s%% of all votes';
	private const VOTES_PERCENT_TEXT = '{votes} - {percent}% of all votes';

	private Admin_Page $admpage;
	private Messages $messages;
	private Plugin $plugin;

	public function __construct( Admin_Page $admin_page, Messages $messages, Plugin $plugin ){
		$this->admpage = $admin_page;
		$this->messages = $messages;
		$this->plugin = $plugin;
	}

	public function load(): void {
	}

	public function request_handler(): void {
		if( ! $this->plugin->super_access || ! Admin_Page::check_nonce() ){
			return;
		}

		if( isset( $_POST['dem_save_l10n'] ) || isset( $_POST['dem_reset_l10n'] ) ){
			$up = false;

			// Update custom localization.
			if( isset( $_POST['dem_save_l10n'] ) ){
				$up = $this->update_l10n( stripslashes_deep( $_POST['l10n'] ) );
			}

			// Reset custom localization.
			if( isset( $_POST['dem_reset_l10n'] ) ){
				$up = $this->reset_l10n();
			}

			$up
				? $this->messages->add_ok( __( 'Updated', 'democracy-poll' ) )
				: $this->messages->add_notice( __( 'Nothing was updated', 'democracy-poll' ) );

		}
	}

	public function render(): void {
		if( ! $this->plugin->super_access ){
			return;
		}

		echo $this->admpage->subpages_menu();

		require __DIR__ . '/tpl/l10n.php';
	}

	public function reset_l10n(): bool {
		$up = update_option( 'democracy_l10n', [] );
		self::handle_front_l10n( 'clear_cache' );

		return $up;
	}

	/**
	 * Gets front-end strings that can be overridden on this settings page.
	 *
	 * @return string[]
	 */
	public static function get_front_texts(): array {
		$source = file_get_contents( dirname( __DIR__ ) . '/Poll_Renderer.php' );
		if( false === $source ){
			return [];
		}

		preg_match_all( '~_x\(\s*[\'](.*?)(?<!\\\\)[\']~', $source, $matches );

		return array_values( array_unique( $matches[1] ) );
	}

	public function update_l10n( array $new_l10n ): bool {
		self::remove_gettext_filter();

		foreach( $new_l10n as $key => & $val ){
			$val = trim( $val );

			// Delete values that do not differ from the original contextual translation.
			if( _x( $key, 'front', 'democracy-poll' ) === $val ){
				unset( $new_l10n[ $key ] );
			}
			// sanitize value: Thanks to http://pluginvulnerabilities.com/?p=2967
			else{
				$val = Kses::kses_html( $val );
			}
		}
		unset( $val );
		self::add_gettext_filter();

		$up = (bool) update_option( 'democracy_l10n', $new_l10n );

		self::handle_front_l10n( 'clear_cache' );

		return $up;
	}

	/**
	 * For front part localization and custom translation setup.
	 */
	public static function add_gettext_filter(): void {
		add_filter( 'gettext_with_context', [ __CLASS__, 'handle_front_l10n' ], 10, 4 );
	}

	public static function remove_gettext_filter(): void {
		remove_filter( 'gettext_with_context', [ __CLASS__, 'handle_front_l10n' ], 10 );
	}

	public static function handle_front_l10n( $text_translated, $text = '', $context = '', $domain = '' ) {
		static $l10n_opt;
		if( $l10n_opt === null || 'clear_cache' === $text_translated ){
			$l10n_opt = self::normalize_l10n_options( get_option( 'democracy_l10n' ) );
		}

		if( 'democracy-poll' === $domain && 'front' === $context && ! empty( $l10n_opt[ $text ] ) ){
			return $l10n_opt[ $text ];
		}

		return $text_translated;
	}

	public static function normalize_l10n_options( $l10n_opt ): array {
		$l10n_opt = is_array( $l10n_opt ) ? $l10n_opt : [];

		if( empty( $l10n_opt[ self::VOTES_PERCENT_TEXT ] ) && ! empty( $l10n_opt[ self::OLD_VOTES_PERCENT_TEXT ] ) ){
			$l10n_opt[ self::VOTES_PERCENT_TEXT ] = sprintf(
				$l10n_opt[ self::OLD_VOTES_PERCENT_TEXT ],
				'{votes}',
				'{percent}'
			);
		}

		unset( $l10n_opt[ self::OLD_VOTES_PERCENT_TEXT ] );

		return $l10n_opt;
	}

}
