<?php
namespace DemocracyPoll\Admin;

/**
 * @var Admin_Page_Design $this
 */

defined( 'ABSPATH' ) || exit;

$opt = $this->options;
$plugin = $this->plugin;

$demcss = get_option( 'democracy_css' );
$additional = $demcss['additional_css'];
if( ! $demcss['base_css'] && $additional ){
	$demcss['base_css'] = $additional; // Use additional CSS when no theme is selected.
}

?>
<?= Admin_Page::info_sidebar() ?>

<div class="demoptions dempage-design">
	<?php $this::polls_preview( true ); ?>

	<form action="" method="post">
		<?php wp_nonce_field( 'dem_adminform', '_demnonce' ); ?>

		<!-- Choose theme -->
		<section class="demoptions__group">
			<div class="title"><?= esc_html__( 'Choose Theme', 'democracy-poll' ) ?></div>
			<div class="demoptions__block selectable_els">
				<label>
					<input type="radio" name="dem[css_file_name]"
					       value="" <?php checked( $opt->css_file_name, '' ) ?> />
					<span class="radio_content"><?= esc_html__( 'No theme', 'democracy-poll' ) ?></span>
				</label>
				<?php
				foreach( $this->_get_styles_files() as $file ){
					$filename = basename( $file );
					?>
					<label>
						<input type="radio" name="dem[css_file_name]"
						       value="<?= $filename ?>" <?php checked( $opt->css_file_name, $filename ) ?> />
						<span class="radio_content"><?= $filename ?></span>
					</label>
					<?php
				}
				?>
			</div>
		</section>

		<!-- checkbox, radio -->
		<section class="demoptions__group">
			<div class="title">checkbox, radio</div>
			<div class="demoptions__block check_radio_wrap selectable_els" style="display:flex; align-items:center; gap:3rem;">
				<div>
					<label>
						<input type="radio" value=""
						       name="dem[checkradio_fname]" <?php checked( $opt->checkradio_fname, '' ) ?>>
						<span class="radio_content">
								<div style="padding:.5rem;"></div>
								<?= esc_html__( 'No (default)', 'democracy-poll' ); ?>
							</span>
					</label>
				</div>
				<?php
				$data = [];
				foreach( glob( "$plugin->dir/assets/styles/checkbox-radio/*" ) as $file ){
					if( is_dir( $file ) ){
						continue;
					}
					$data[ basename( $file ) ] = $file;
				}
				foreach( $data as $fname => $file ){
					$styles = file_get_contents( $file );

					// Adjust styles.
					$unique = 'unique' . rand( 1, 9999 ) . '_';
					$styles = str_replace( '.dem__radio_label', ".{$unique}dem__radio_label", $styles );
					$styles = str_replace( '.dem__checkbox_label', ".{$unique}dem__checkbox_label", $styles );
					$styles = str_replace( '.dem__radio', ".{$unique}dem__radio", $styles );
					$styles = str_replace( '.dem__checkbox', ".{$unique}dem__checkbox", $styles );
					$styles = str_replace( ':disabled', ':disabled__', $styles ); // Disable the :disabled selector.
					?>
					<div>
						<style><?= $styles ?></style>
						<label>
							<input type="radio" value="<?= $fname ?>" name="dem[checkradio_fname]" <?= checked( $opt->checkradio_fname, $fname, 0 ) ?>>
							<span class="radio_content">
								<div style="display:flex; gap:.5em;">
									<div class="<?= $unique ?>dem__radio_label">
										<input disabled class="<?= $unique ?>dem__radio demdummy" type="radio" /><span class="dem__spot"></span>
									</div>
									<div class="<?= $unique ?>dem__radio_label">
										<input disabled class="<?= $unique ?>dem__radio demdummy" checked type="radio" /><span class="dem__spot"></span>
									</div>
									<div class="<?= $unique ?>dem__checkbox_label">
										<input disabled class="<?= $unique ?>dem__checkbox demdummy" type="checkbox" /><span class="dem__spot"></span>
									</div>
									<div class="<?= $unique ?>dem__checkbox_label demdummy">
										<input disabled class="<?= $unique ?>dem__checkbox" checked type="checkbox" /><span class="dem__spot"></span>
									</div>
								</div>

								<?= $fname ?>
							</span>
						</label>
					</div>
					<?php
				}
				?>
			</div>
		</section>

		<!-- Other settings -->
		<section class="demoptions__group">
			<div class="title"><?= esc_html__( 'Other settings', 'democracy-poll' ) ?></div>
			<div class="demoptions__block">
				<input type="text" style="width:7em;" name="dem[answs_max_height]" value="<?= esc_attr( $opt->answs_max_height ) ?>">
				<?= esc_html__( 'Max poll height (in em, rem, px, ...). Leave empty to disable.', 'democracy-poll' ) ?>
			</div>
			<div class="demoptions__block">
				<input type="number" min="0" style="width:7em;" name="dem[anim_speed]" value="<?= esc_attr( $opt->anim_speed ) ?>">
				<?= esc_html__( 'Animation speed (in milliseconds).', 'democracy-poll' ) ?>
			</div>
		</section>

		<!--Progress line-->
		<section class="demoptions__group">
			<div class="title"><?= esc_html__( 'Progress line', 'democracy-poll' ) ?></div>
			<div class="demoptions__block">
				<p><?= esc_html__( 'How to fill (paint) the progress of each answer:', 'democracy-poll' ) ?></p>
				<label style="margin-left:1em;">
					<input type="radio" name="dem[graph_from_total]"
					       value="0" <?php checked( $opt->graph_from_total, 0 ) ?> />
					<?= esc_html__( 'winner - 100%, others as % of the winner', 'democracy-poll' ) ?>
				</label>
				<br>
				<label style="margin-left:1em;">
					<input type="radio" name="dem[graph_from_total]"
					       value="1" <?php checked( $opt->graph_from_total, 1 ) ?> />
					<?= esc_html__( 'as percent of all votes', 'democracy-poll' ) ?>
				</label>

				<br><br>

				<?php
				$this::color_picker_html( [
					'title' => __( 'Line Color', 'democracy-poll' ),
					'name'  => 'dem[line_fill]',
					'value' => $opt->line_fill
				] );

				$this::color_picker_html( [
					'title' => __( 'Line color (for voted user)', 'democracy-poll' ),
					'name'  => 'dem[line_fill_voted]',
					'value' => $opt->line_fill_voted
				] );

				$this::color_picker_html( [
					'title' => __( 'Background color', 'democracy-poll' ),
					'name'  => 'dem[line_bg]',
					'value' => $opt->line_bg
				] );
				?>

				<br>

				<label>
					<input type="text" style="width:90px" name="dem[line_height]" value="<?= $opt->line_height ?>"/>
					<?= esc_html__( 'Line height (in px if unit not set)', 'democracy-poll' ) ?>
				</label>
				<br><br>

				<label>
					<input type="number" style="width:90px" name="dem[line_anim_speed]"
					       value="<?= (int) $opt->line_anim_speed ?>"/>
					<?= esc_html__( 'Progress line animation effect speed (default 1500). Set 0 to disable animation.', 'democracy-poll' ) ?>
				</label>
			</div>
		</section>


		<!--Button-->
		<section class="demoptions__group">
			<div class="title">Button</div>
			<div class="demoptions__block buttons">
				<div class="btn_select_wrap selectable_els">
					<label>
						<input type="radio" value="" name="dem[css_button]" <?php checked( $opt->css_button, '' ) ?> />
						<span class="radio_content">
							<input type="button" value="<?= esc_attr__( 'No (default)', 'democracy-poll' ); ?>"/>
						</span>
					</label>

					<?php
					$i = 0;
					foreach( glob( "$plugin->dir/assets/styles/buttons/*" ) as $file ){
						if( is_dir( $file ) ){
							continue;
						}

						$fname = basename( $file );
						$button_class = 'dem-button' . ++$i;
						$css = ".$button_class{ position: relative; display:inline-block; text-decoration:none; user-select: none; outline:none; line-height:1; border:0; }\n"; // reset
						$css .= str_replace( '.dem-button', ".$button_class", file_get_contents( $file ) );

						if( $opt->css_button ){
							$bbg     = $opt->btn_bg_color;
							$bcolor  = $opt->btn_color;
							$bbcolor = $opt->btn_border_color;
							// hover
							$bh_bg     = $opt->btn_hov_bg;
							$bh_color  = $opt->btn_hov_color;
							$bh_bcolor = $opt->btn_hov_border_color;

							$button_vars = array_filter( [
								$bbg       ? "--dem-button-bg: $bbg"                       : '',
								$bcolor    ? "--dem-button-color: $bcolor"                 : '',
								$bbcolor   ? "--dem-button-border-color: $bbcolor"         : '',
								$bh_bg     ? "--dem-button-hover-bg: $bh_bg"               : '',
								$bh_color  ? "--dem-button-hover-color: $bh_color"         : '',
								$bh_bcolor ? "--dem-button-hover-border-color: $bh_bcolor" : '',
							] );

							if( $button_vars ){
								$css .= "\n.$button_class{ " . implode( "; ", $button_vars ) . "; }\n";
							}
						}
						?>
						<style><?= $css ?></style>

						<label>
							<input type="radio" value="<?= esc_attr( $fname ) ?>"
							       name="dem[css_button]" <?php checked( $opt->css_button, $fname ) ?> />
							<span class="radio_content">
								<input type="button" value="<?= esc_attr( $fname ) ?>" class="<?= $button_class ?>">
							</span>
						</label>
						<?php
					}
					?>
					<br><br>
					<em>
						<?= esc_html__( 'These colors may not affect all button styles. You can fully customize button styles in the "Additional CSS styles" field below.' , 'democracy-poll' ) ?>
					</em>
				</div>

				<div style="display:flex; gap:1rem;">
					<div style="flex-basis: 33%">
						<p><?= esc_html__( 'Button colors', 'democracy-poll' ) ?></p>
						<?php
						$this::color_picker_html( [
							'title' => __( 'Bg color', 'democracy-poll' ),
							'name'  => 'dem[btn_bg_color]',
							'value' => $opt->btn_bg_color
						] );

						$this::color_picker_html( [
							'title' => __( 'Text Color', 'democracy-poll' ),
							'name'  => 'dem[btn_color]',
							'value' => $opt->btn_color
						] );

						$this::color_picker_html( [
							'title' => __( 'Border Color', 'democracy-poll' ),
							'name'  => 'dem[btn_border_color]',
							'value' => $opt->btn_border_color
						] );
						?>
					</div>
					<div style="flex-basis: 33%">
						<p><?= esc_html__( 'Hover button colors', 'democracy-poll' ) ?></p>
						<?php
						$this::color_picker_html( [
							'title' => __( 'Bg color', 'democracy-poll' ),
							'name'  => 'dem[btn_hov_bg]',
							'value' => $opt->btn_hov_bg
						] );

						$this::color_picker_html( [
							'title' => __( 'Text Color', 'democracy-poll' ),
							'name'  => 'dem[btn_hov_color]',
							'value' => $opt->btn_hov_color
						] );

						$this::color_picker_html( [
							'title' => __( 'Border Color', 'democracy-poll' ),
							'name'  => 'dem[btn_hov_border_color]',
							'value' => $opt->btn_hov_border_color
						] );
						?>
					</div>
					<div style="flex-basis: 33%">
						<!--<hr>-->
						<label style="margin-top:3em;">
							<input type="text" name="dem[btn_class]" value="<?= $opt->btn_class ?>">
							<em><?= esc_html__( 'An additional css class for all buttons in the poll. When the template has a special class for buttons, for example:', 'democracy-poll' ) ?> <code>btn btn-info</code></em>
						</label>
					</div>

				</div><!--flex-->

			</div>

		</section>


		<!-- AJAX loader -->
		<section class="demoptions__group">
			<div class="title">Loader</div>
			<div class="demoptions__block loaders" style="text-align:center;">
				<div class="selectable_els">
					<label class="lo_item" style="display: block; height:30px;">
						<input type="radio" value="" name="dem[loader_fname]" <?php checked( $opt->loader_fname, '' ) ?>>
						<span class="radio_content"><?= esc_html__( 'No (dots...)', 'democracy-poll' ); ?></span>
					</label>
					<br>
					<?php
					$data = [];
					foreach( glob( "$plugin->dir/assets/styles/loaders/*" ) as $file ){
						if( is_dir( $file ) ){
							continue;
						}
						$fname = basename( $file );
						$ex = preg_replace( '~.*\.~', '', $fname );
						$data[ $ex ][ $fname ] = $file;
					}
					foreach( $data as $ex => $val ){
						echo '<div class="clearfix"></div>' . "<h2 style='text-align:center;'>$ex</h2>"; //'';

						// Adjust styles.
						if( $opt->loader_fill ){
							$loader_css = ".loader{ --dem-loader-color: $opt->loader_fill; }";
							echo "<style>$loader_css</style>";
						}

						foreach( $val as $fname => $file ){
							?>
							<label class="lo_item <?= $ex ?>">
								<input type="radio" value="<?= $fname ?>"
								       name="dem[loader_fname]" <?php checked( $opt->loader_fname, $fname ) ?>>
								<span class="radio_content">
									<div class="loader"><?= file_get_contents( $file ) ?></div>
								</span>
							</label>
							<?php
						}
					}
					?>

				</div>

				<em>
					<?= esc_html__( 'AJAX Loader. If you choose "NO", loader is replaced by dots "..." which are appended to a link/button text. SVG images animations don\'t work in IE 11 or lower, other browsers are supported at 90% (according to caniuse.com statistics).', 'democracy-poll' ) ?>
				</em>

				<input class="iris_color fill" name="dem[loader_fill]" type="text" value="<?= $opt->loader_fill ?>">

			</div>

		</section>

		<!-- Custom styles -->
		<section class="demoptions__group">
			<div class="title"><?= esc_html__( 'Custom/Additional CSS styles', 'democracy-poll' ) ?></div>
			<div class="demoptions__block" style="width:98%;">
				<p>
					<i><?php
						echo esc_html__( 'In this field you can add some additional css properties or completely replace current css theme. Write here css and it will be added at the bottom of current Democracy css. To completely replace styles, check "No theme" and describe all styles.', 'democracy-poll' );
						echo esc_html__( 'This field must be cleared manually, if you reset options of this page or change/set another theme, the field will not be touched.', 'democracy-poll' );
						?></i>
				</p>
				<textarea name="additional_css" style="width:100%; min-height:50px; height:<?= $additional ? '300px' : '50px' ?>;"><?= esc_textarea( $additional ) ?></textarea>
			</div>
		</section>

		<!-- Connected styles -->
		<p style="margin:2em 0; margin-top:5em; position:fixed; bottom:0; z-index:99;">
			<input type="submit" name="dem_save_design_options" class="button-primary"
		       value="<?= esc_attr__( 'Save All Changes', 'democracy-poll' ) ?>"
			>

			<input type="submit" name="dem_reset_design_options" class="button"
		       value="<?= esc_attr__( 'Reset Options', 'democracy-poll' ) ?>"
		       onclick="return confirm('<?= esc_attr__( 'are you sure?', 'democracy-poll' ) ?>');"
		       style="margin-left:4em;"
			>
		</p>

		<section class="demoptions__group">
			<div class="title"><?= esc_html__( 'All CSS styles currently in use', 'democracy-poll' ) ?></div>
			<div class="demoptions__block">
				<script>
					function select_kdfgu( that ){
						var sel = ( !! document.getSelection) ? document.getSelection() : ( !! window.getSelection) ? window.getSelection() : document.selection.createRange().text;
						if( sel == '' ) that.select();
					}
				</script>
				<em>
					<?= esc_html__( 'These are all collected CSS styles: theme, button, options. You can copy these styles to the "Custom/Additional CSS styles:" field, disable theme and edit them manually.', 'democracy-poll' ) ?>
				</em>
				<textarea onmouseup="select_kdfgu(this);" onfocus="this.style.height = '700px';"
				          onblur="this.style.height = '100px';" readonly="true"
				          style="width:100%;min-height:100px;"><?php
					echo $demcss['base_css'] . "\n\n\n/* custom styles ------------------------------ */\n" . $demcss['additional_css'];
					?></textarea>

				<p>
					<?= esc_html__( 'Minified version (used to include it in HTML)', 'democracy-poll' ) ?>
					<button type="button" onclick="const el = this.parentElement.nextElementSibling; el.hidden = ! el.hidden">show</button>
				</p>
				<pre hidden style="width:auto; white-space:pre-wrap; overflow-wrap:anywhere; word-break:break-word; padding: 1em; background:rgba(0 0 0 / .1)"><?= $demcss['minify'] ?></pre>
			</div>
		</section>

	</form>

</div>
