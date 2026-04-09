<?php

namespace DemocracyPoll\Admin;

use function DemocracyPoll\plugin;
use function DemocracyPoll\options;

class Admin_Page_Other_Migrations implements Admin_Subpage_Interface {

	/** @var Admin_Page */
	private $admpage;

	public function __construct( Admin_Page $admin_page ) {
		$this->admpage = $admin_page;
	}

	public function load() {
	}

	public function request_handler() {

		$migration = get_option( 'democracy_migrated' );

		// handlers
		if( ! empty( $migration['wp-polls'] ) ){
			$more_action = $_GET['moreaction'] ?? '';

			if( in_array( $more_action, [ 'replace_shortcode', 'restore_shortcode_replace' ] ) ){
				$this->replace_shortcodes( $more_action );
			}

			// Deleting migration data
			if( $more_action === 'delete_wp_polls_info' ){
				delete_option( 'democracy_migrated' );

				plugin()->msg->add_ok( __( 'Data of migration deleted', 'democracy-poll' ) );

				return; // important!
			}
		}

		if( ( $_GET['from'] ?? '' ) === 'wp-polls' ){
			( new \DemocracyPoll\Utils\Migrator__WP_Polls() )->migrate();
		}

	}

	public function render() {

		echo $this->admpage->subpages_menu();

		$migration = get_option( 'democracy_migrated' );
		?>
		<div class="democr_options">
			<?php
			$wp_polls = $migration['wp-polls'] ?? '';
			if( $wp_polls ){
				$count_polls = count( wp_list_pluck( $wp_polls, 'new_poll_id' ) );

				$count_answe = 0;
				foreach( wp_list_pluck( $wp_polls, 'answers:old->new' ) as $val ){
					$count_answe += count( $val );
				}

				$count_logs = 0;
				foreach( wp_list_pluck( $wp_polls, 'logs_created' ) as $val ){
					$count_logs += count( $val );
				}

				?>
				<h3><?= __( 'Migration from WP Polls done', 'democracy-poll' ) ?></h3>
				<p><?= sprintf( __( 'Polls copied: %d. Answers copied: %d. Logs copied: %d', 'democracy-poll' ),
						$count_polls, $count_answe, $count_logs ) ?>
				</p>
				<p>
					<a class="button" href="<?= esc_url( add_query_arg( 'moreaction', 'replace_shortcode' ) ) ?>">
						<?= __( 'Replace WP Polls shortcodes in posts', 'democracy-poll' ) ?>
					</a>
					<=>
					<a class="button" href="<?= esc_url( add_query_arg( 'moreaction', 'restore_shortcode_replace' ) ) ?>">
						<?= __( 'Cancel the shortcode replace and reset changes', 'democracy-poll' ) ?>
					</a>
				</p>
				<br>
				<p>
					<a class="button button-small" style="opacity:.5;"
					   href="<?= esc_url( add_query_arg( 'moreaction', 'delete_wp_polls_info' ) ) ?>"
					   onclick="return confirm('<?= esc_attr__( 'Are you sure?', 'democracy-poll' ) ?>');">
						<?= __( 'Delete all data about WP Polls migration', 'democracy-poll' ) ?>
					</a>
				</p>
				<?php
			}
			?>
		</div>
		<?php
	}

	private function replace_shortcodes( string $more_action ){
		global $wpdb;

		$migration = get_option( 'democracy_migrated' );

		$count = 0;

		$poll_ids_old_new = wp_list_pluck( $migration['wp-polls'], 'new_poll_id' );

		foreach( $poll_ids_old_new as $old => $new ){
			$_new = '[democracy id="' . (int) $new . '"]';
			$_old = '[poll id="' . (int) $old . '"]';

			$rep_from = $rep_to = '';

			if( $more_action === 'replace_shortcode' ){
				$rep_from = $_old;
				$rep_to = $_new;
			}
			elseif( $more_action === 'restore_shortcode_replace' ){
				$rep_from = $_new;
				$rep_to = $_old;
			}

			if( $rep_from && $rep_to ){
				$count += $wpdb->query(
					"UPDATE $wpdb->posts SET post_content = REPLACE( post_content, '$rep_from', '$rep_to' )
						WHERE post_type NOT IN ('attachment','revision')"
				);
			}
		}

		plugin()->msg->add_ok( sprintf( __( 'Shortcodes replaced: %s', 'democracy-poll' ), $count ) );
	}

}
