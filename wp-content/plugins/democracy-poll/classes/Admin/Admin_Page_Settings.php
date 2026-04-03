<?php

namespace DemocracyPoll\Admin;

use function DemocracyPoll\plugin;
use function DemocracyPoll\options;

class Admin_Page_Settings implements Admin_Subpage_Interface {

	/** @var Admin_Page */
	private $admpage;

	public function __construct( Admin_Page $admin_page ){
		$this->admpage = $admin_page;
	}

	public function load(){
	}

	public function request_handler(){
		if( ! plugin()->super_access || ! Admin_Page::check_nonce() ){
			return;
		}

		$up = null;
		if( isset( $_POST['dem_save_main_options'] ) ){
			$up = options()->update_options( 'main' );
		}
		if( isset( $_POST['dem_reset_main_options'] ) ){
			$up = options()->reset_options( 'main' );
		}

		if( $up !== null ){
			$up
				? plugin()->msg->add_ok( __( 'Updated', 'democracy-poll' ) )
				: plugin()->msg->add_notice( __( 'Nothing was updated', 'democracy-poll' ) );
		}

		// запрос на создание страницы архива
		if( isset( $_GET['dem_create_archive_page'] ) ){
			$this->dem_create_archive_page();
		}
	}

	public function render(): void {
		echo $this->admpage->subpages_menu();

		if( ! plugin()->super_access ){
			return;
		}

		?>
		<div class="democr_options dempage_settings">
			<form action="" method="POST">
				<?php wp_nonce_field( 'dem_adminform', '_demnonce' ); ?>

				<ul style="margin:1em;">
					<li class="block">
						<label>
							<input type="checkbox" value="1"
							       name="dem[keep_logs]" <?php checked( options()->keep_logs, 1 ) ?> />
							<?= esc_html__( 'Log data & take visitor IP into consideration? (recommended)', 'democracy-poll' ) ?>
						</label>
						<em><?= esc_html__( 'Saves data into Data Base. Forbids to vote several times from a single IP or to same WordPress user. If a user is logged in, then his voting is checked by WP account. If a user is not logged in, then checks the IP address. The negative side of IP checks is that a site may be visited from an enterprise network (with a common IP), so all users from this network are allowed to vote only once. If this option is disabled the voting is checked by Cookies only. Default enabled.', 'democracy-poll' ) ?></em>
					</li>

					<li class="block">
						<label>
							<input type="number" step="1" min="0" value="<?= (float) options()->cookie_days ?>"
							       name="dem[cookie_days]" />
							<?= esc_html__( 'How many days to keep Cookies alive?', 'democracy-poll' ) ?>
						</label>
						<em>
							<?= esc_html__( 'How many days the user\'s browser remembers the votes. Default: 365. Note: works together with IP log.', 'democracy-poll' ) ?>
							<br>
							<?= esc_html__( 'To set hours use float number - 0.04 = 1 hour.', 'democracy-poll' ) ?>
						</em>
					</li>

					<li class="block">
						<label><?= esc_html__( 'HTML tags to wrap the poll title.', 'democracy-poll' ) ?></label><br>
						<input type="text" size="35" value="<?= esc_attr( options()->before_title ) ?>"
						       name="dem[before_title]"/>
						<i><?= esc_html__( 'poll\'s question', 'democracy-poll' ) ?></i>
						<input type="text" size="15" value="<?= esc_attr( options()->after_title ) ?>"
						       name="dem[after_title]"/>
						<em><?= wp_kses_post( __( 'Example: <code>&lt;h2&gt;</code> и <code>&lt;/h2&gt;</code>. Default: <code>&lt;strong class=&quot;dem-poll-title&quot;&gt;</code> & <code>&lt;/strong&gt;</code>.', 'democracy-poll' ) ) ?></em>
					</li>

					<li class="block">
						<label>
							<input type="text" size="10" name="dem[archive_page_id]" value="<?= (int) options()->archive_page_id ?>" />
							<?= esc_html__( 'Polls archive page ID.', 'democracy-poll' ) ?>
						</label>
						<?php
						if( options()->archive_page_id ){
							echo sprintf( '<a href="%s">%s</a>',
								get_permalink( options()->archive_page_id ),
								__( 'Go to archive page', 'democracy-poll' )
							);
						}
						else{
							echo sprintf( '<a class="button" href="%s">%s</a>',
								esc_url( Admin_Page::add_nonce( add_query_arg( [ 'dem_create_archive_page' => 1 ] ) ) ),
								__( 'Create/find archive page', 'democracy-poll' )
							);
						}
						?>
						<em><?= wp_kses_post( __( 'Specify the poll archive link to be in the poll legend. Example: <code>25</code>', 'democracy-poll' ) ) ?></em>
					</li>

					<h3><?= esc_html__( 'Global Polls options', 'democracy-poll' ) ?></h3>

					<li class="block">
						<select name="dem[order_answers]">
							<?= \DemocracyPoll\Helpers\Helpers::answers_order_select_options( options()->order_answers ) ?>
						</select>
						<?= esc_html__( 'How to sort the answers during voting, if they don\'t have order? (default option)', 'democracy-poll' ) ?>
						<br>
						<em><?= esc_html__( 'This is the default value. Option can be changed for each poll separately.', 'democracy-poll' ) ?></em>
					</li>

					<li class="block">
						<label>
							<input type="checkbox" value="1"
							       name="dem[only_for_users]" <?php checked( options()->only_for_users, 1 ) ?> />
							<?= esc_html__( 'Only registered users allowed to vote (global option)', 'democracy-poll' ) ?>
						</label>
						<em><?= esc_html__( 'This option  is available for each poll separately, but if you heed you can turn ON the option for all polls at once, just tick.', 'democracy-poll' ) ?></em>
					</li>

					<li class="block">
						<label>
							<input type="checkbox" value="1"
							       name="dem[democracy_off]" <?php checked( options()->democracy_off, 1 ) ?> />
							<?= esc_html__( 'Prohibit users to add new answers (global Democracy option).', 'democracy-poll' ) ?>
						</label>
						<em><?= esc_html__( 'This option  is available for each poll separately, but if you heed you can turn OFF the option for all polls at once, just tick.', 'democracy-poll' ) ?></em>
					</li>

					<li class="block">
						<label>
							<input type="checkbox" value="1"
							       name="dem[revote_off]" <?php checked( options()->revote_off, 1 ) ?> />
							<?= esc_html__( 'Remove the Revote possibility (global option).', 'democracy-poll' ) ?>
						</label>
						<em><?= esc_html__( 'This option  is available for each poll separately, but if you heed you can turn OFF the option for all polls at once, just tick.', 'democracy-poll' ) ?></em>
					</li>

					<li class="block">
						<label>
							<input type="checkbox" value="1"
							       name="dem[dont_show_results]" <?php checked( options()->dont_show_results, 1 ) ?> />
							<?= esc_html__( 'Don\'t show poll results (global option).', 'democracy-poll' ) ?>
						</label>
						<em><?= esc_html__( 'If checked, user can\'t see poll results if voting is open.', 'democracy-poll' ) ?></em>
					</li>

					<li class="block">
						<label>
							<input type="checkbox" value="1"
							       name="dem[dont_show_results_link]" <?php checked( options()->dont_show_results_link, 1 ) ?> />
							<?= esc_html__( 'Don\'t show poll results link (global option).', 'democracy-poll' ) ?>
						</label>
						<em><?= esc_html__( 'Users can see results after vote.', 'democracy-poll' ) ?></em>
					</li>

					<li class="block">
						<label>
							<input type="checkbox" value="1"
							       name="dem[hide_vote_button]" <?php checked( options()->hide_vote_button, 1 ) ?> />
							<?= esc_html__( 'Hide vote button.', 'democracy-poll' ) ?>
						</label>
						<em><?= esc_html__( 'Hide vote button if it is NOT multiple poll with revote option. User will vote by clicking on answer itself.', 'democracy-poll' ) ?></em>
					</li>

					<li class="block">
						<label>
							<input type="checkbox" value="1"
							       name="dem[post_metabox_off]" <?php checked( options()->post_metabox_off, 1 ) ?> />
							<?= esc_html__( 'Disable post metabox.', 'democracy-poll' ) ?>
						</label>
						<em><?= esc_html__( 'Check this to disable polls metabox functionality for posts where you can attached poll to a post...', 'democracy-poll' ) ?></em>
					</li>

					<h3><?= esc_html__( 'Others', 'democracy-poll' ) ?></h3>
					<li class="block">
						<label>
							<input type="checkbox" value="1"
							       name="dem[force_cachegear]" <?php checked( options()->force_cachegear, 1 ) ?> />
							<?php
							[ $cache_status, $cache_style ] = plugin()->is_cachegear_on
								? [ __( 'ON', 'democracy-poll' ), 'color:#05A800' ]
								: [ __( 'OFF', 'democracy-poll' ), 'color:#FF1427' ];

							echo sprintf(
								__( 'Force enable gear to working with cache plugins. The condition: %s', 'democracy-poll' ),
								"<span style='$cache_style'>$cache_status</span>"
							);
							?>
						</label>
						<em><?= esc_html__( 'Democracy has smart mechanism for working with page cache plugins like "WP Total Cache". It is ON automatically if such plugin is enabled on your site. But if you use unusual page caching plugin you can force enable this option.', 'democracy-poll' ) ?></em>
					</li>

					<li class="block">
						<label>
							<input type="checkbox" value="1" name="dem[inline_js_css]" <?php checked( options()->inline_js_css, 1 ) ?> />
							<?= esc_html__( 'Inline script code directly in the HTML', 'democracy-poll' ) ?>
						</label>
						<em><?= esc_html__( 'Enable to add plugin scripts directly into HTML instead of loading separate .js files. This reduces server requests and speeds up page loading.', 'democracy-poll' ) ?></em>
					</li>

					<li class="block">
						<label>
							<input type="checkbox" value="1" name="dem[toolbar_menu]" <?php checked( options()->toolbar_menu, 1 ) ?> />
							<?= esc_html__( 'Add plugin menu on the toolbar?', 'democracy-poll' ) ?>
						</label>
						<em><?= esc_html__( 'Uncheck to remove the plugin menu from the toolbar.', 'democracy-poll' ) ?></em>
					</li>

					<li class="block">
						<label>
							<input type="checkbox" value="1" name="dem[tinymce_button]" <?php checked( options()->tinymce_button, 1 ) ?> />
							<?= esc_html__( 'Add fast Poll insert button to WordPress visual editor (TinyMCE)?', 'democracy-poll' ) ?>
						</label>
						<em><?= esc_html__( 'Uncheck to disable button in visual editor.', 'democracy-poll' ) ?></em>
					</li>

					<li class="block">
						<label>
							<input type="checkbox" value="1" name="dem[soft_ip_detect]" <?php checked( options()->soft_ip_detect, 1 ) ?> />
							<?= esc_html__( 'Check if you see something like "no_IP__123" in IP column on logs page. (not recommended)', 'democracy-poll' ) ?>
							<?= esc_html__( 'Or if IP detection is wrong. (for cloudflare)', 'democracy-poll' ) ?>
						</label>
						<em><?= esc_html__( 'Useful when your server does not work correctly with server variable REMOTE_ADDR. NOTE: this option give possibility to cheat voice.', 'democracy-poll' ) ?></em>
					</li>

					<?php
					$select_options = '';
					foreach( array_reverse( get_editable_roles() ) as $role => $details ){
						if( $role === 'administrator' ){
							continue;
						}
						if( $role === 'subscriber' ){
							continue;
						}

						$select_options .= sprintf( '<option value="%s" %s>%s</option>',
							esc_attr( $role ),
							in_array( $role, (array) options()->access_roles ) ? ' selected="selected"' : '',
							translate_user_role( $details['name'] )
						);
					}
					?>
					<li class="block">
						<select multiple name="dem[access_roles][]"><?= $select_options ?></select>
						<?= esc_html__( 'Role names, except \'administrator\' which will have access to manage plugin.', 'democracy-poll' ) ?>
					</li>
				</ul>

				<?php if( get_option( 'poll_allowtovote' ) /* WP Polls plugin */ ){ ?>
					<h3><?= esc_html__( 'Migration', 'democracy-poll' ) ?></h3>
					<ul style="margin:1em;">
						<li class="block">
							<a class="button button-small"
							   href="<?= esc_url( add_query_arg( [ 'subpage' => 'migration', 'from' => 'wp-polls' ] ) ) ?>"
							>
								<?= esc_html__( 'Migrate from WP Polls plugin', 'democracy-poll' ) ?>
							</a>
							<em><?= esc_html__( 'All polls, answers and logs of WP Polls will be added to Democracy Poll', 'democracy-poll' ) ?></em>
						</li>
					</ul>
				<?php } ?>

				<br>
				<p>
					<input type="submit" name="dem_save_main_options" class="button-primary"
					       value="<?= esc_attr__( 'Save Options', 'democracy-poll' ) ?>">
					<input type="submit" name="dem_reset_main_options" class="button"
					       value="<?= esc_attr__( 'Reset Options', 'democracy-poll' ) ?>">
				</p>

				<br><br>

				<h3><?= esc_html__( 'Others', 'democracy-poll' ) ?></h3>

				<ul style="margin:1em;">

					<li class="block">
						<label>
							<input type="checkbox" value="1"
							       name="dem[disable_js]" <?php checked( options()->disable_js, 1 ) ?> />
							<?= esc_html__( 'Don\'t connect JS files. (Debug)', 'democracy-poll' ) ?>
						</label>
						<em><?= esc_html__( 'If checked, the plugin\'s .js file will NOT be connected to front end. Enable this option to test the plugin\'s work without JavaScript.', 'democracy-poll' ) ?></em>
					</li>

					<li class="block">
						<label>
							<input type="checkbox" value="1"
							       name="dem[show_copyright]" <?php checked( options()->show_copyright, 1 ) ?> />
							<?= esc_html__( 'Show copyright', 'democracy-poll' ) ?>
						</label>
						<em><?= esc_html__( 'Link to plugin page is shown on front page only as a &copy; icon. It helps visitors to learn about the plugin and install it for themselves. Please don\'t disable this option without urgent needs. Thanks!', 'democracy-poll' ) ?></em>
					</li>

					<li class="block">
						<label>
							<input type="checkbox" value="1"
							       name="dem[use_widget]" <?php checked( options()->use_widget, 1 ) ?> />
							<?= esc_html__( 'Widget', 'democracy-poll' ) ?>
						</label>
						<em><?= esc_html__( 'Check to activate the widget.', 'democracy-poll' ) ?></em>
					</li>

					<li class="block">
						<label>
							<!--<input type="checkbox" value="1" name="dem_forse_upgrade">-->
							<input name="dem_forse_upgrade" type="submit" class="button"
							       value="<?= esc_attr__( 'Force plugin versions update (debug)', 'democracy-poll' ) ?>"/>
						</label>
					</li>

				</ul>

			</form>
		</div>
		<?php
	}

	/**
	 * Creates the archive page.
	 * Saves the URL of the created page in the plugin option.
	 * Before creating, checks if such a page already exists.
	 *
	 * @return false|void
	 */
	protected function dem_create_archive_page() {
		global $wpdb;

		// try to find the archive page
		$page = $wpdb->get_row(
			"SELECT * FROM $wpdb->posts WHERE post_content LIKE '[democracy_archives]' AND post_status = 'publish' LIMIT 1"
		);

		if( $page ){
			$page_id = $page->ID;
		}
		// create a new page
		else{
			$page_id = wp_insert_post( [
				'post_title'   => __( 'Polls Archive', 'democracy-poll' ),
				'post_content' => '[democracy_archives]',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_name'    => 'democracy-archives',
			] );

			if( ! $page_id ){
				return false;
			}
		}

		// update option
		options()->update_single_option( 'archive_page_id', $page_id );

		wp_redirect( remove_query_arg( 'dem_create_archive_page' ) );
	}

}
