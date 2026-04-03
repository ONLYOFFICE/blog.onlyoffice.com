<?php

namespace DemocracyPoll\Admin;

use function DemocracyPoll\plugin;

class Admin_Page_l10n implements Admin_Subpage_Interface {

	/** @var Admin_Page */
	private $admpage;

	public function __construct( Admin_Page $admin_page ){
		$this->admpage = $admin_page;
	}

	public function load(): void {
	}

	public function request_handler(): void {
		if( ! plugin()->super_access || ! Admin_Page::check_nonce() ){
			return;
		}

		if( isset( $_POST['dem_save_l10n'] ) || isset( $_POST['dem_reset_l10n'] ) ){
			$up = false;

			// обновляем произвольную локализацию
			if( isset( $_POST['dem_save_l10n'] ) ){
				$up = $this->update_l10n( stripslashes_deep( $_POST['l10n'] ) );
			}

			// сбрасываем произвольную локализацию
			if( isset( $_POST['dem_reset_l10n'] ) ){
				$up = $this->reset_l10n();
			}

			$up
				? plugin()->msg->add_ok( __( 'Updated', 'democracy-poll' ) )
				: plugin()->msg->add_notice( __( 'Nothing was updated', 'democracy-poll' ) );

		}
	}

	public function render(): void {
		if( ! plugin()->super_access ){
			return;
		}

		echo $this->admpage->subpages_menu();
		?>
		<div class="democr_options dempage_l10n">

			<?php Admin_Page_Design::polls_preview(); ?>

			<form method="POST" action="">
				<?php wp_nonce_field( 'dem_adminform', '_demnonce' ); ?>
				<table class="wp-list-table widefat fixed posts">
					<thead>
						<tr>
							<th><?= __( 'Original', 'democracy-poll' ) ?></th>
							<th><?= __( 'Your variant', 'democracy-poll' ) ?></th>
						</tr>
					</thead>
					<tbody id="the-list">
					<?php
					// get all translations from the files
					$strs = [];
					$files = [
						plugin()->dir . '/classes/DemPoll.php',
						plugin()->dir . '/classes/Poll_Widget.php',
					];
					foreach( $files as $file ){
						preg_match_all( '~_x\(\s*[\'](.*?)(?<!\\\\)[\']~', file_get_contents( $file ), $match );
						if( $match[1] ){
							/** @noinspection SlowArrayOperationsInLoopInspection */
							$strs = array_merge( $strs, $match[1] );
						}
					}
					$strs = array_unique( $strs );

					$i = 0;
					$_l10n = get_option( 'democracy_l10n' );
					self::remove_gettext_filter();
					foreach( $strs as $str ){
						$i++;
						$mo_str = _x( $str, 'front', 'democracy-poll' );

						$l10ed_str = ( ! empty( $_l10n[ $str ] ) && $_l10n[ $str ] !== $mo_str ) ? $_l10n[ $str ] : '';

						?>
						<tr class="<?= ( $i % 2 ? 'alternate' : '' ) ?>">
							<td><?= esc_html( $mo_str ) ?></td>
							<td>
								<input type="text" name="l10n[<?= esc_attr( $str ) ?>]" value="<?= esc_attr( $l10ed_str ) ?>"
								       style="width:100%;"  />
							</td>
						</tr>
						<?php
					}
					self::add_gettext_filter();
					?>
					<tbody>
				</table>

				<p>
					<input class="button-primary" type="submit" name="dem_save_l10n"
					       value="<?= esc_attr__( 'Save Text', 'democracy-poll' ) ?>">
					<input class="button" type="submit" name="dem_reset_l10n"
					       value="<?= esc_attr__( 'Reset Options', 'democracy-poll' ) ?>">
				</p>

			</form>

		</div>
		<?php
	}

	public function reset_l10n(): bool {
		$up = update_option( 'democracy_l10n', [] );
		self::handle_front_l10n( 'clear_cache' );

		return $up;
	}

	public function update_l10n( array $new_l10n ): bool {

		foreach( $new_l10n as $key => & $val ){
			$val = trim( $val );

			// delete if no difference from original translations_api
			if( __( $key, 'democracy-poll' ) === $val ){
				unset( $new_l10n[ $key ] );
			}
			// sanitize value: Thanks to //pluginvulnerabilities.com/?p=2967
			else{
				$val = \DemocracyPoll\Helpers\Kses::kses_html( $val );
			}
		}
		unset( $val );

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
			$l10n_opt = get_option( 'democracy_l10n' );
		}

		if( 'democracy-poll' === $domain && 'front' === $context && ! empty( $l10n_opt[ $text ] ) ){
			return $l10n_opt[ $text ];
		}

		return $text_translated;
	}

}
