<?php
namespace DemocracyPoll\Admin;

use DemocracyPoll\Support\Helpers;

/**
 * @var Admin_Page_Settings $this
 */

defined( 'ABSPATH' ) || exit;

$opt = $this->options;
?>

<?= Admin_Page::info_sidebar() ?>

<div class="demoptions dempage_settings">
	<form action="" method="POST">
		<?php wp_nonce_field( 'dem_adminform', '_demnonce' ); ?>

		<section class="demoptions__optlist" style="margin:1em;">
			<div class="demoptions__block">
				<label>
					<input type="checkbox" value="1"
					       name="dem[allow_same_ip_votes]" <?php checked( $opt->allow_same_ip_votes, 1 ) ?> />
					<?= esc_html__( 'Allow multiple votes from the same IP address.', 'democracy-poll' ) ?>
				</label>
				<em><?= esc_html__( 'When enabled, guests sharing an IP address are distinguished by a browser fingerprint. Each browser can still vote only once. Logged-in users are always identified by their WordPress account.', 'democracy-poll' ) ?></em>
			</div>

			<div class="demoptions__block">
				<label>
					<input type="number" step="1" min="0" value="<?= (float) $opt->cookie_days ?>"
					       name="dem[cookie_days]" />
					<?= esc_html__( 'How many days to keep Cookies alive?', 'democracy-poll' ) ?>
				</label>
				<em>
					<?= esc_html__( 'How many days the browser and server remember votes. Default: 365.', 'democracy-poll' ) ?>
					<br>
					<?= esc_html__( 'To set hours use float number - 0.04 = 1 hour.', 'democracy-poll' ) ?>
				</em>
			</div>

			<div class="demoptions__block">
				<label><?= esc_html__( 'Poll title HTML template.', 'democracy-poll' ) ?></label><br>
				<input type="text" size="70" value="<?= esc_attr( $opt->title_markup ) ?>" name="dem[title_markup]"/>
				<em><?= wp_kses_post( __( 'Use <code>{question}</code> where the poll question should appear. Default: <code>&lt;strong class=&quot;dem-poll-title&quot;&gt;{question}&lt;/strong&gt;</code>.', 'democracy-poll' ) ) ?></em>
			</div>

			<div class="demoptions__block">
				<label>
					<input type="text" size="10" name="dem[archive_page_id]" value="<?= (int) $opt->archive_page_id ?>" />
					<?= esc_html__( 'Polls archive page ID.', 'democracy-poll' ) ?>
					<?php
					if( $opt->archive_page_id ){
						echo sprintf( '<a href="%s">%s</a>',
							get_permalink( $opt->archive_page_id ),
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
				</label>
			</div>

			<h3><?= esc_html__( 'Global Polls options', 'democracy-poll' ) ?></h3>

			<div class="demoptions__block">
				<select name="dem[order_answers]">
					<?= Helpers::answers_order_select_options( $opt->order_answers ) ?>
				</select>
				<?= esc_html__( 'Answer order on vote screen (global option)', 'democracy-poll' ) ?>
				<br>
				<em><?= esc_html__( 'Option can be changed for each poll separately.', 'democracy-poll' ) ?></em>
			</div>

			<div class="demoptions__block">
				<select name="dem[order_answers_voted]">
					<?= Helpers::answers_order_select_options( $opt->order_answers_voted ) ?>
				</select>
				<?= esc_html__( 'Answer order on results screen (global option)', 'democracy-poll' ) ?>
			</div>

			<div class="demoptions__block">
				<label>
					<input type="checkbox" value="1"
					       name="dem[only_for_users]" <?php checked( $opt->only_for_users, 1 ) ?> />
					<?= esc_html__( 'Only registered users allowed to vote (global option)', 'democracy-poll' ) ?>
				</label>
				<em><?= esc_html__( 'Can be changed for each poll separately.', 'democracy-poll' ) ?></em>
			</div>

			<div class="demoptions__block">
				<label>
					<input type="checkbox" value="1"
					       name="dem[democracy_off]" <?php checked( $opt->democracy_off, 1 ) ?> />
					<?= esc_html__( 'Prohibit users to add new answers (global option).', 'democracy-poll' ) ?>
				</label>
				<em><?= esc_html__( 'Can be changed for each poll separately.', 'democracy-poll' ) ?></em>
			</div>

			<div class="demoptions__block">
				<label>
					<input type="checkbox" value="1"
					       name="dem[revote_off]" <?php checked( $opt->revote_off, 1 ) ?> />
					<?= esc_html__( 'Remove the Revote possibility (global option).', 'democracy-poll' ) ?>
				</label>
				<em><?= esc_html__( 'Can be changed for each poll separately.', 'democracy-poll' ) ?></em>
			</div>

			<div class="demoptions__block">
				<label>
					<input type="checkbox" value="1"
					       name="dem[dont_show_results]" <?php checked( $opt->dont_show_results, 1 ) ?> />
					<?= esc_html__( 'Don\'t show poll results (global option).', 'democracy-poll' ) ?>
				</label>
				<em><?= esc_html__( 'If checked, user can\'t see poll results if voting is open.', 'democracy-poll' ) ?></em>
			</div>

			<div class="demoptions__block">
				<label>
					<input type="checkbox" value="1"
					       name="dem[dont_show_results_link]" <?php checked( $opt->dont_show_results_link, 1 ) ?> />
					<?= esc_html__( 'Don\'t show poll results link (global option).', 'democracy-poll' ) ?>
				</label>
				<em><?= esc_html__( 'Users can see results after vote.', 'democracy-poll' ) ?></em>
			</div>

			<div class="demoptions__block">
				<label>
					<input type="checkbox" value="1"
					       name="dem[hide_vote_button]" <?php checked( $opt->hide_vote_button, 1 ) ?> />
					<?= esc_html__( 'Hide vote button.', 'democracy-poll' ) ?>
				</label>
				<em><?= esc_html__( 'Users can vote with one click on an answer. Works only for non-multiple polls with the revote option enabled.', 'democracy-poll' ) ?></em>
			</div>

			<div class="demoptions__block">
				<label>
					<input type="checkbox" value="1"
					       name="dem[post_metabox_off]" <?php checked( $opt->post_metabox_off, 1 ) ?> />
					<?= esc_html__( 'Disable post metabox.', 'democracy-poll' ) ?>
				</label>
				<em><?= esc_html__( 'Check this to disable polls metabox functionality for posts where you can attach a poll to a post.', 'democracy-poll' ) ?></em>
			</div>

			<h3><?= esc_html__( 'Others', 'democracy-poll' ) ?></h3>
			<div class="demoptions__block">
				<label>
					<input type="checkbox" value="1"
					       name="dem[force_cachegear]" <?php checked( $opt->force_cachegear, 1 ) ?> />
					<?php
					[ $cache_status, $cache_style ] = $this->plugin->is_cachegear_on
						? [ __( 'ON', 'democracy-poll' ), 'color:#05A800' ]
						: [ __( 'OFF', 'democracy-poll' ), 'color:#FF1427' ];

					echo sprintf(
						__( 'Force-enable compatibility mode for page cache plugins. Current status: %s', 'democracy-poll' ),
						"<span style='$cache_style'>$cache_status</span>"
					);
					?>
				</label>
				<em><?= esc_html__( 'Democracy Poll has a built-in mechanism for working with page cache plugins such as W3 Total Cache. It is enabled automatically when a supported plugin is active. If you use a different page cache plugin, you can force-enable this option.', 'democracy-poll' ) ?></em>
			</div>

			<div class="demoptions__block">
				<label>
					<input type="checkbox" value="1" name="dem[toolbar_menu]" <?php checked( $opt->toolbar_menu, 1 ) ?> />
					<?= esc_html__( 'Add plugin menu on the toolbar?', 'democracy-poll' ) ?>
				</label>
				<em><?= esc_html__( 'Uncheck to remove the plugin menu from the toolbar.', 'democracy-poll' ) ?></em>
			</div>

			<div class="demoptions__block">
				<label>
					<input type="checkbox" value="1" name="dem[tinymce_button]" <?php checked( $opt->tinymce_button, 1 ) ?> />
					<?= esc_html__( 'Add fast Poll insert button to WordPress visual editor (TinyMCE)?', 'democracy-poll' ) ?>
				</label>
				<em><?= esc_html__( 'Uncheck to disable button in visual editor.', 'democracy-poll' ) ?></em>
			</div>

			<div class="demoptions__block">
				<label>
					<input type="checkbox" value="1" name="dem[use_widget]" <?php checked( $opt->use_widget, 1 ) ?> />
					<?= esc_html__( 'Widget', 'democracy-poll' ) ?>
				</label>
				<em><?= esc_html__( 'Check to activate the widget.', 'democracy-poll' ) ?></em>
			</div>

			<div class="demoptions__block">
				<label>
					<input type="checkbox" value="1" name="dem[soft_ip_detect]" <?php checked( $opt->soft_ip_detect, 1 ) ?> />
					<?= esc_html__( 'Check if you see something like "no_IP__123" in IP column on logs page. (not recommended)', 'democracy-poll' ) ?>
					<?= esc_html__( 'Or if IP detection is wrong. (for cloudflare)', 'democracy-poll' ) ?>
				</label>
				<em><?= esc_html__( 'Use this if your server does not detect the visitor IP correctly from REMOTE_ADDR. Note: this option may make vote cheating easier.', 'democracy-poll' ) ?></em>
			</div>

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
					in_array( $role, (array) $opt->access_roles ) ? ' selected="selected"' : '',
					translate_user_role( $details['name'] )
				);
			}
			?>
			<div class="demoptions__block">
				<select multiple name="dem[access_roles][]"><?= $select_options ?></select>
				<?= esc_html__( 'Role names, except \'administrator\' which will have access to manage plugin.', 'democracy-poll' ) ?>
			</div>
		</section>

		<?php if( get_option( 'poll_allowtovote' ) /* WP Polls plugin */ ){ ?>
			<h3><?= esc_html__( 'Migration', 'democracy-poll' ) ?></h3>
			<section style="margin:1em;">
				<div class="demoptions__block">
					<a class="button button-small"
					   href="<?= esc_url( add_query_arg( [ 'subpage' => 'migration', 'from' => 'wp-polls' ] ) ) ?>"
					>
						<?= esc_html__( 'Migrate from WP Polls plugin', 'democracy-poll' ) ?>
					</a>
					<em><?= esc_html__( 'All polls, answers and logs of WP Polls will be added to Democracy Poll', 'democracy-poll' ) ?></em>
				</div>
			</section>
		<?php } ?>

		<br>
		<p>
			<input type="submit" name="dem_save_main_options" class="button-primary"
			       value="<?= esc_attr__( 'Save Options', 'democracy-poll' ) ?>">
			<input type="submit" name="dem_reset_main_options" class="button"
			       value="<?= esc_attr__( 'Reset Options', 'democracy-poll' ) ?>">
		</p>

		<?php if( WP_DEBUG ){ ?>
			<br><br>
			<h3><?= esc_html__( 'Others', 'democracy-poll' ) ?></h3>
			<section style="margin:1em;">
				<div class="demoptions__block">
					<label>
						<!--<input type="checkbox" value="1" name="dem_forse_upgrade">-->
						<input name="dem_forse_upgrade" type="submit" class="button"
						       value="<?= esc_attr__( 'Force plugin versions update (debug)', 'democracy-poll' ) ?>"/>
					</label>
				</div>
			</section>
		<?php } ?>

	</form>
</div>
