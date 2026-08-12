<?php
namespace DemocracyPoll\Admin;

/**
 * @var Admin_Page_l10n $this
 */

defined( 'ABSPATH' ) || exit;
?>

<?= Admin_Page::info_sidebar() ?>

<div class="demoptions dempage_l10n">

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
			$i = 0;
			$_l10n = $this::normalize_l10n_options( get_option( 'democracy_l10n' ) );
			$this::remove_gettext_filter();
			foreach( $this::get_front_texts() as $str ){
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
			$this::add_gettext_filter();
			?>
			</tbody>
		</table>

		<p>
			<input class="button-primary" type="submit" name="dem_save_l10n"
			       value="<?= esc_attr__( 'Save Text', 'democracy-poll' ) ?>">
			<input class="button" type="submit" name="dem_reset_l10n"
			       value="<?= esc_attr__( 'Reset Options', 'democracy-poll' ) ?>">
		</p>

	</form>

</div>
