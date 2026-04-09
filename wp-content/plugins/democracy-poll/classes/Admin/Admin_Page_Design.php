<?php

namespace DemocracyPoll\Admin;

use function DemocracyPoll\plugin;
use function DemocracyPoll\options;

class Admin_Page_Design implements Admin_Subpage_Interface {

	/** @var Admin_Page */
	private $admpage;

	public function __construct( Admin_Page $admin_page ){
		$this->admpage = $admin_page;
	}

	public function load(){
		// Iris Color Picker
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );

		// CodeMirror
		if( function_exists( 'wp_enqueue_code_editor' ) ){
			add_action( 'admin_enqueue_scripts', function() {
				// подключаем редактор кода для HTML.
				$settings = wp_enqueue_code_editor( [ 'type' => 'text/css' ] );

				// инициализация
				wp_add_inline_script( 'code-editor', sprintf(
					'jQuery( function(){  wp.codeEditor.initialize( jQuery("textarea[name=additional_css]"), %s );  } );', wp_json_encode( $settings )
				) );
			}, 99 );
		}
	}

	public function request_handler(  ){
		if( ! plugin()->super_access || ! Admin_Page::check_nonce() ){
			return;
		}

		$up = null;
		if( isset( $_POST['dem_save_design_options'] ) ){
			$up = options()->update_options( 'design' );
		}
		if( isset( $_POST['dem_reset_design_options'] ) ){
			$up = options()->reset_options( 'design' );
		}

		if( $up !== null ){
			$up
				? plugin()->msg->add_ok( __( 'Updated', 'democracy-poll' ) )
				: plugin()->msg->add_notice( __( 'Nothing was updated', 'democracy-poll' ) );
		}

		// hack to immediately apply the option change
		if( $up ){
			options()->toolbar_menu
				? add_action( 'admin_bar_menu', [ plugin()->initor, 'add_toolbar_node', ], 99 )
				: remove_action( 'admin_bar_menu', [ plugin()->initor, 'add_toolbar_node' ], 99 );
		}
	}

	public function render() {
		if( ! plugin()->super_access ){
			return;
		}

		$demcss = get_option( 'democracy_css' );
		$additional = $demcss['additional_css'];
		if( ! $demcss['base_css'] && $additional ){
			$demcss['base_css'] = $additional; // если не используется тема
		}

		echo $this->admpage->subpages_menu();
		?>
		<div class="democr_options dempage_design">
			<?php self::polls_preview(); ?>

			<form action="" method="post">
				<?php wp_nonce_field( 'dem_adminform', '_demnonce' ); ?>

				<ul class="group">
					<li class="title"><?= esc_html__( 'Choose Theme', 'democracy-poll' ); ?></li>
					<li class="block selectable_els">
						<label>
							<input type="radio" name="dem[css_file_name]"
							       value="" <?php checked( options()->css_file_name, '' ) ?> />
							<span class="radio_content"><?= esc_html__( 'No theme', 'democracy-poll' ) ?></span>
						</label>
						<?php
						foreach( $this->_get_styles_files() as $file ){
							$filename = basename( $file );
							?>
							<label>
								<input type="radio" name="dem[css_file_name]"
								       value="<?= $filename ?>" <?php checked( options()->css_file_name, $filename ) ?> />
								<span class="radio_content"><?= $filename ?></span>
							</label>
							<?php
						}
						?>
					</li>
				</ul>

				<!-- Other settings -->
				<ul class="group">
					<li class="title"><?= esc_html__( 'Other settings', 'democracy-poll' ); ?></li>
					<li class="block">
						<input type="number" min="-1" style="width:90px;" name="dem[answs_max_height]"
						       value="<?= esc_attr( options()->answs_max_height ) ?>">
						<?= esc_html__( 'Max height of the poll in px. When poll has very many answers, it\'s better to collapse it. Set \'-1\', in order to disable this option. Default 500.', 'democracy-poll' ) ?>
					</li>
					<li class="block">
						<input type="number" min="0" style="width:90px;" name="dem[anim_speed]"
						       value="<?= esc_attr( options()->anim_speed ) ?>">
						<?= esc_html__( 'Animation speed in milliseconds.', 'democracy-poll' ) ?>
					</li>

				</ul>

				<!--Progress line-->
				<ul class="group">
					<li class="title"><?= esc_html__( 'Progress line', 'democracy-poll' ); ?></li>
					<li class="block">

						<?= esc_html__( 'How to fill (paint) the progress of each answer?', 'democracy-poll' ) ?><br>
						<label style="margin-left:1em;">
							<input type="radio" name="dem[graph_from_total]"
							       value="0" <?php checked( options()->graph_from_total, 0 ) ?> />
							<?= esc_html__( 'winner - 100%, others as % of the winner', 'democracy-poll' ) ?>
						</label>
						<br>
						<label style="margin-left:1em;">
							<input type="radio" name="dem[graph_from_total]"
							       value="1" <?php checked( options()->graph_from_total, 1 ) ?> />
							<?= esc_html__( 'as percent of all votes', 'democracy-poll' ) ?>
						</label>

						<br><br>

						<label>
							<input type="text" class="iris_color" name="dem[line_fill]"
							       value="<?= options()->line_fill ?>"/>
							<?= esc_html__( 'Line Color', 'democracy-poll' ) ?>
						</label>
						<br>

						<label>
							<input type="text" class="iris_color" name="dem[line_fill_voted]"
							       value="<?= options()->line_fill_voted ?>">
							<?= esc_html__( 'Line color (for voted user)', 'democracy-poll' ) ?>
						</label>
						<br>

						<label>
							<input type="text" class="iris_color" name="dem[line_bg]"
							       value="<?= options()->line_bg ?>"/>
							<?= esc_html__( 'Background color', 'democracy-poll' ) ?>
						</label>
						<br><br>

						<label>
							<input type="number" style="width:90px" name="dem[line_height]"
							       value="<?= options()->line_height ?>"/> px
							<?= esc_html__( 'Line height', 'democracy-poll' ) ?>
						</label>
						<br><br>

						<label>
							<input type="number" style="width:90px" name="dem[line_anim_speed]"
							       value="<?= (int) options()->line_anim_speed ?>"/>
							<?= esc_html__( 'Progress line animation effect speed (default 1500). Set 0 to disable animation.', 'democracy-poll' ) ?>
						</label>

					</li>
				</ul>

				<!-- checkbox, radio -->
				<ul class="group">
					<li class="title">checkbox, radio</li>
					<li class="block check_radio_wrap selectable_els">
						<div style="float:left;">
							<label style="padding:0em 3em 1em;">
								<input type="radio" value=""
								       name="dem[checkradio_fname]" <?php checked( options()->checkradio_fname, '' ) ?>>
								<span class="radio_content">
								<div style="padding:1.25em;"></div>
								<?= esc_html__( 'No (default)', 'democracy-poll' ); ?>
							</span>
							</label>
						</div>
						<?php
						$data = [];
						foreach( glob( plugin()->dir . '/styles/checkbox-radio/*' ) as $file ){
							if( is_dir( $file ) ){
								continue;
							}
							$data[ basename( $file ) ] = $file;
						}
						foreach( $data as $fname => $file ){
							$styles = file_get_contents( $file );

							// поправим стили
							$unique = 'unique' . rand( 1, 9999 ) . '_';
							$styles = str_replace( '.dem__radio_label', ".{$unique}dem__radio_label", $styles );
							$styles = str_replace( '.dem__checkbox_label', ".{$unique}dem__checkbox_label", $styles );
							$styles = str_replace( '.dem__radio', ".{$unique}dem__radio", $styles );
							$styles = str_replace( '.dem__checkbox', ".{$unique}dem__checkbox", $styles );
							$styles = str_replace( ':disabled', ':disabled__', $styles ); // отменим действие :disabled

							?>
							<div style="float:left;">
								<style><?= $styles ?></style>
								<label style="padding:0 3em 1em;">
									<input type="radio" value="<?= $fname ?>" name="dem[checkradio_fname]" <?= checked( options()->checkradio_fname, $fname, 0 ) ?>>
									<span class="radio_content">
										<div style="padding:.5em;">
											<label class="<?= $unique ?>dem__radio_label">
												<input disabled class="<?= $unique ?>dem__radio demdummy" type="radio" /><span class="dem__spot"></span>
											</label>
											<label class="<?= $unique ?>dem__radio_label">
												<input disabled class="<?= $unique ?>dem__radio demdummy" checked type="radio" /><span class="dem__spot"></span>
											</label>
											<label class="<?= $unique ?>dem__checkbox_label">
												<input disabled class="<?= $unique ?>dem__checkbox demdummy" type="checkbox" /><span class="dem__spot"></span>
											</label>
											<label class="<?= $unique ?>dem__checkbox_label demdummy">
												<input disabled class="<?= $unique ?>dem__checkbox" checked type="checkbox" /><span class="dem__spot"></span>
											</label>
										</div>

										<?= $fname ?>
									<span>
								</label>

							</div>
							<?php
						}
						?>
					</li>
				</ul>


				<!--Button-->
				<ul class="group">
					<li class="title"><?= esc_html__( 'Button', 'democracy-poll' ); ?></li>
					<li class="block buttons">

						<div class="btn_select_wrap selectable_els">
							<label>
								<input type="radio" value=""
								       name="dem[css_button]" <?php checked( options()->css_button, '' ) ?> />
								<span class="radio_content">
									<input type="button" value="<?= esc_attr__( 'No (default)', 'democracy-poll' ); ?>"/>
								</span>
							</label>

							<?php
							$data = [];
							$i = 0;
							foreach( glob( plugin()->dir . '/styles/buttons/*' ) as $file ){
								if( is_dir( $file ) ){
									continue;
								}

								$fname = basename( $file );
								$button_class = 'dem-button' . ++$i;
								$css = "/*reset*/\n.$button_class{position: relative; display:inline-block; text-decoration: none; user-select: none; outline: none; line-height: 1; border:0;}\n";
								$css .= str_replace( 'dem-button', $button_class, file_get_contents( $file ) ); // стили кнопки

								if( options()->css_button ){
									$bbg = options()->btn_bg_color;
									$bcolor = options()->btn_color;
									$bbcolor = options()->btn_border_color;
									// hover
									$bh_bg = options()->btn_hov_bg;
									$bh_color = options()->btn_hov_color;
									$bh_bcolor = options()->btn_hov_border_color;

									if( $bbg ){
										$css .= "\n.$button_class{ background-color:$bbg !important; }\n";
									}
									if( $bcolor ){
										$css .= ".$button_class{ color:$bcolor !important; }\n";
									}
									if( $bbcolor ){
										$css .= ".$button_class{ border-color:$bbcolor !important; }\n";
									}
									if( $bh_bg ){
										$css .= "\n.$button_class:hover{ background-color:$bh_bg !important; }\n";
									}
									if( $bh_color ){
										$css .= ".$button_class:hover{ color:$bh_color !important; }\n";
									}
									if( $bh_bcolor ){
										$css .= ".$button_class:hover{ border-color:$bh_bcolor !important; }\n";
									}
								}
								?>
								<style><?= $css ?></style>

								<label>
									<input type="radio" value="<?= esc_attr( $fname ) ?>"
									       name="dem[css_button]" <?php checked( options()->css_button, $fname ) ?> />
									<span class="radio_content">
										<input type="button" value="<?= esc_attr( $fname ) ?>"
										       class="<?= $button_class ?>">
									</span>
								</label>
								<?php
							}
							?>
						</div>
						<div class="clearfix"></div>
						<br>

						<p style="float:left; margin-right:3em;">
							<?= esc_html__( 'Button colors', 'democracy-poll' ) ?><br>

							<input type="text" class="iris_color" name="dem[btn_bg_color]"
							       value="<?= options()->btn_bg_color ?>">
							<?= esc_html__( 'Bg color', 'democracy-poll' ) ?><br>

							<input type="text" class="iris_color" name="dem[btn_color]"
							       value="<?= options()->btn_color ?>">
							<?= esc_html__( 'Text Color', 'democracy-poll' ) ?><br>

							<input type="text" class="iris_color" name="dem[btn_border_color]"
							       value="<?= options()->btn_border_color ?>">
							<?= esc_html__( 'Border Color', 'democracy-poll' ) ?>
						</p>
						<p style="float:left; margin-right:3em;">
							<?= esc_html__( 'Hover button colors', 'democracy-poll' ) ?><br>

							<input type="text" class="iris_color" name="dem[btn_hov_bg]"
							       value="<?= options()->btn_hov_bg ?>">
							<?= esc_html__( 'Bg color', 'democracy-poll' ) ?><br>

							<input type="text" class="iris_color" name="dem[btn_hov_color]"
							       value="<?= options()->btn_hov_color ?>">
							<?= esc_html__( 'Text Color', 'democracy-poll' ) ?><br>

							<input type="text" class="iris_color" name="dem[btn_hov_border_color]"
							       value="<?= options()->btn_hov_border_color ?>">
							<?= esc_html__( 'Border Color', 'democracy-poll' ) ?>
						</p>
						<div class="clearfix"></div>
						<em>
							<?= esc_html__( 'The colors correctly affects NOT for all buttons. You can change styles completely in "additional styles" field bellow.', 'democracy-poll' ) ?>
						</em>

						<!--<hr>-->
						<label style="margin-top:3em;">
							<input type="text" name="dem[btn_class]" value="<?= options()->btn_class ?>">
							<em><?= esc_html__( 'An additional css class for all buttons in the poll. When the template has a special class for buttons, for example <code>btn btn-info</code>', 'democracy-poll' ) ?></em>
						</label>
					</li>

				</ul>


				<!-- AJAX loader -->
				<ul class="group">
					<li class="title"><?= esc_html__( 'AJAX loader', 'democracy-poll' ); ?></li>
					<li class="block loaders" style="text-align:center;">

						<div class="selectable_els">
							<label class="lo_item" style="display: block; height:30px;">
								<input type="radio" value=""
								       name="dem[loader_fname]" <?php checked( options()->loader_fname, '' ) ?>>
								<span class="radio_content"><?= esc_html__( 'No (dots...)', 'democracy-poll' ); ?></span>
							</label>
							<br>
							<?php
							$data = [];
							foreach( glob( plugin()->dir . '/styles/loaders/*' ) as $file ){
								if( is_dir( $file ) ){
									continue;
								}
								$fname = basename( $file );
								$ex = preg_replace( '~.*\.~', '', $fname );
								$data[ $ex ][ $fname ] = $file;
							}
							foreach( $data as $ex => $val ){
								echo '<div class="clearfix"></div>' . "<h2 style='text-align:center;'>$ex</h2>"; //'';

								// поправим стили
								if( options()->loader_fill ){
									preg_match_all( '~\.dem-loader\s+\.(?:fill|stroke|css-fill)[^\{]*\{.*?\}~s', $demcss['base_css'], $match );
									echo "<style>" . str_replace( '.dem-loader', '.loader', implode( "\n", $match[0] ) ) . "</style>";
								}

								foreach( $val as $fname => $file ){
									?>
									<label class="lo_item <?= $ex ?>">
										<input type="radio" value="<?= $fname ?>"
										       name="dem[loader_fname]" <?php checked( options()->loader_fname, $fname ) ?>>
										<span class="radio_content">
											<div class="loader"><?= file_get_contents( $file ) ?></div>
											<?php //echo $ex
											?>
										</span>
									</label>
									<?php
								}
							}
							?>

						</div>

						<em>
							<?= esc_html__( 'AJAX Loader. If choose "NO", loader replaces by dots "..." which appends to a link/button text. SVG images animation don\'t work in IE 11 or lower, other browsers are supported at  90% (according to caniuse.com statistics).', 'democracy-poll' ) ?>
						</em>

						<input class="iris_color fill" name="dem[loader_fill]" type="text"
						       value="<?= options()->loader_fill ?>">

					</li>

				</ul>

				<!-- Custom styles -->
				<ul class="group">
					<li class="title"><?= esc_html__( 'Custom/Additional CSS styles', 'democracy-poll' ) ?></li>

					<li class="block" style="width:98%;">
						<p>
							<i><?php
								echo esc_html__( 'In this field you can add some additional css properties or completely replace current css theme. Write here css and it will be added at the bottom of current Democracy css. To complete replace styles, check "No theme" and describe all styles.', 'democracy-poll' );
								echo esc_html__( 'This field cleaned manually, if you reset options of this page or change/set another theme, the field will not be touched.', 'democracy-poll' );
								?></i>
						</p>
						<textarea name="additional_css" style="width:100%; min-height:50px; height:<?= $additional ? '300px' : '50px' ?>;"><?= esc_textarea( $additional ) ?></textarea>
					</li>
				</ul>

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

				<ul class="group">
					<li class="title"><?= esc_html__( 'All CSS styles that uses now', 'democracy-poll' ) ?></li>
					<li class="block">
						<script>
							function select_kdfgu( that ){
								var sel = ( !! document.getSelection) ? document.getSelection() : ( !! window.getSelection) ? window.getSelection() : document.selection.createRange().text;
								if( sel == '' ) that.select();
							}
						</script>
						<em style="__opacity: 0.8;">
							<?= esc_html__( 'It\'s all collected css styles: theme, button, options. You can copy this styles to the "Custom/Additional CSS styles:" field, disable theme and change copied styles by itself.', 'democracy-poll' ) ?>
						</em>
						<textarea onmouseup="select_kdfgu(this);" onfocus="this.style.height = '700px';"
						          onblur="this.style.height = '100px';" readonly="true"
						          style="width:100%;min-height:100px;"><?php
							echo $demcss['base_css'] . "\n\n\n/* custom styles ------------------------------ */\n" . $demcss['additional_css'];
							?></textarea>

						<p><?= esc_html__( 'Minified version (uses to include it in HTML)', 'democracy-poll' ); ?></p>

						<textarea onmouseup="select_kdfgu(this);" readonly="true"
						          style="width:100%; min-height:10em;"><?= $demcss['minify'] ?></textarea>
					</li>
				</ul>

			</form>

		</div>
		<?php
	}

	/**
	 * Получает существующие полные css файлы из каталога плагина.
	 *
	 * @return array Возвращает массив имен (путей) к файлам.
	 */
	protected function _get_styles_files(): array {
		$arr = [];
		foreach( glob( plugin()->dir . '/styles/*.css' ) as $file ){
			if( preg_match( '~\.min~', basename( $file ) ) ){
				continue;
			}
			$arr[] = $file;
		}

		return $arr;
	}

	public static function polls_preview(): void {
		?>
		<ul class="group">
			<li class="block polls-preview">
				<?php
				$poll = new \DemPoll( \DemPoll::get_db_data( 'rand' ) );
				$render = $poll->renderer;

				if( $poll->id ){
					$answers = wp_list_pluck( $poll->answers, 'aid' );
					$poll->voted_for = (string) ( $answers ? $answers[ array_rand( $answers ) ] : '' );

					$rm_disabled = static function( $val ) {
						return str_replace( 'disabled="disabled"', '', $val );
					};

					$html = <<<HTML
						<div class="poll"><p class="tit">{RESULTS_TXT}</p>{VOTED_SCREEN}</div>
						<div class="poll"><p class="tit">{VOTE_TXT}</p>{FORCE_VOTE_SCREEN}</div>
						<div class="poll show-loader"><p class="tit">{AJAX_TXT}</p>{VOTE_SCREEN}</div>
						HTML;

					echo strtr( $html, [
						'{RESULTS_TXT}'       => __( 'Results view:', 'democracy-poll' ),
						'{VOTE_TXT}'          => __( 'Vote view:', 'democracy-poll' ),
						'{AJAX_TXT}'          => __( 'AJAX loader view:', 'democracy-poll' ),
						'{VOTED_SCREEN}'      => $rm_disabled( $render->get_screen( 'voted' ) ),
						'{FORCE_VOTE_SCREEN}' => $rm_disabled( $render->get_screen( 'force_vote' ) ),
						'{VOTE_SCREEN}'       => $rm_disabled( $render->get_screen( 'vote' ) ),
					] );
				}
				else{
					echo 'no data or no active polls...';
				}

				if( ( $_GET['subpage'] ?? '' ) === 'design' ){
					echo '<input type="text" class="iris_color preview-bg">';
				}
				?>
			</li>
		</ul>
		<?php
	}

}
