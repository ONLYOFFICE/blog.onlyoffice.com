<?php
/**
 * Robots meta fields for the Quick Edit and Bulk Edit forms.
 *
 * Rendered after core's three column fieldsets, so it clears their floats and spans
 * the full width as its own section rather than sitting inside a column.
 *
 * @since 5.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
?>

<fieldset class="aioseo-inline-edit-robots">
	<span class="aioseo-inline-edit-robots__title">
		<?php
			// Translators: 1 - The short plugin name ("AIOSEO").
			echo esc_html( sprintf( __( '%1$s Robots Meta', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_SHORT_NAME ) );
		?>
	</span>

	<input
		type="hidden"
		name="<?php echo esc_attr( $prefix ); ?>-robots-submitted"
		value="1"
	/>

	<div class="aioseo-inline-edit-robots__controls">
		<?php if ( $isBulk ) : ?>
			<label class="aioseo-inline-edit-robots__default">
				<select name="<?php echo esc_attr( $prefix ); ?>-robots-default">
					<option value=""><?php esc_html_e( '— No Change —', 'all-in-one-seo-pack' ); ?></option>
					<option value="default"><?php esc_html_e( 'Use default settings', 'all-in-one-seo-pack' ); ?></option>
					<option value="custom"><?php esc_html_e( 'Use custom settings', 'all-in-one-seo-pack' ); ?></option>
				</select>
			</label>
		<?php else : ?>
			<label class="aioseo-inline-edit-robots__default">
				<input
					type="checkbox"
					name="<?php echo esc_attr( $prefix ); ?>-robots-default"
					value="1"
				/>
				<span><?php esc_html_e( 'Use default settings', 'all-in-one-seo-pack' ); ?></span>
			</label>
		<?php endif; ?>

		<div class="aioseo-inline-edit-robots__options">
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $prefix ); ?>-robots-noindex"
					value="1"
				/>
				<span><?php esc_html_e( 'No Index', 'all-in-one-seo-pack' ); ?></span>
			</label>

			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $prefix ); ?>-robots-nofollow"
					value="1"
				/>
				<span><?php esc_html_e( 'No Follow', 'all-in-one-seo-pack' ); ?></span>
			</label>
		</div>
	</div>
</fieldset>