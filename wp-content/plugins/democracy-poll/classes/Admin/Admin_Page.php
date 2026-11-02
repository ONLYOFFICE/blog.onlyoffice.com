<?php

namespace DemocracyPoll\Admin;

use DemocracyPoll\Support\Messages;
use DemocracyPoll\Plugin;
use DemocracyPoll\Poll_Utils;
use DemocracyPoll\System\Upgrader;
use function DemocracyPoll\container;

class Admin_Page {

	use Admin_Page__Additional;

	public const ASSETS_ID = 'democracy-admin';

	public string $subpage;

	public Admin_Subpage_Interface $subpage_obj;

	public int $edit_poll_id;

	private Plugin $plugin;
	private Messages $messages;

	public function __construct(
		Plugin $plugin,
		Messages $messages
	) {
		$this->plugin = $plugin;
		$this->messages = $messages;

		$this->edit_poll_id = (int) ( $_GET['edit_poll'] ?? 0 );

		$this->subpage = sanitize_key( $_GET['subpage'] ?? '' );
		if( ( ! $this->subpage && ! $this->edit_poll_id ) ){
			$this->subpage = 'polls_list';
		}

		if( $this->edit_poll_id ){
			$this->subpage = 'edit_poll'; // hack
		}
	}

	public function init(): void {
		if( ! $this->plugin->admin_access ){
			return;
		}

		add_action( 'admin_menu', [ $this, 'register_option_page' ] );

		// Save screen options.
		add_filter( 'set-screen-option', static function( $status, $option, $value ) {
			return in_array( $option, [ 'dem_polls_per_page', 'dem_logs_per_page' ] ) ? (int) $value : $status;
		}, 10, 3 );
	}

	public function register_option_page(): void {
		if( ! $this->plugin->admin_access ){
			return;
		}

		$title = 'Democracy Poll';
		$hook_name = add_options_page( $title, $title, 'edit_posts', basename( $this->plugin->dir ), [ $this, 'admin_page_output' ] );
		// notice: `edit_posts` (role more then subscriber) because capability tests inside the `admin_page.php` and `admin_page_load()`

		add_action( "load-$hook_name", [ $this, 'admin_page_load' ] );
	}

	public function admin_page_load(): void {
		wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_enqueue_script( self::ASSETS_ID, "{$this->plugin->url}/assets/admin/admin.js", [ 'jquery' ], $this->plugin->ver, true );
		wp_enqueue_style( self::ASSETS_ID, "{$this->plugin->url}/assets/admin/admin.css", [], $this->plugin->ver );

		$this->handle_force_upgrade_request();
		$this->global_handle_request();
		$this->set_subpage_obj();
		$this->subpage_obj->load();
		$this->subpage_obj->request_handler();
	}

	private function handle_force_upgrade_request(): void {
		if( isset( $_POST['dem_forse_upgrade'] ) && $this->plugin->super_access ){
			container()->get( Upgrader::class )->upgrade_force(); /** @see Upgrader::__construct */
			wp_safe_redirect( $_SERVER['REQUEST_URI'] );
			exit;
		}
	}

	private function set_subpage_obj(): void {
		if( $this->edit_poll_id ){
			$this->subpage_obj = container()->get( Admin_Page_Edit_Poll::class );
			$this->subpage_obj->set_poll_id( $this->edit_poll_id );
			return;
		}

		$subpage_class = [
			'polls_list'       => Admin_Page_Polls::class,            /** @see Admin_Page_Polls::__construct() */
			'add_new'          => Admin_Page_Edit_Poll::class,        /** @see Admin_Page_Edit_Poll::__construct() */
			'edit_poll'        => Admin_Page_Edit_Poll::class,        /** @see Admin_Page_Edit_Poll::__construct() */
			'logs'             => Admin_Page_Logs::class,             /** @see Admin_Page_Logs::__construct() */
			'general_settings' => Admin_Page_Settings::class,         /** @see Admin_Page_Settings::__construct() */
			'design'           => Admin_Page_Design::class,           /** @see Admin_Page_Design::__construct() */
			'l10n'             => Admin_Page_l10n::class,             /** @see Admin_Page_l10n::__construct() */
			'migration'        => Admin_Page_Other_Migrations::class, /** @see Admin_Page_Other_Migrations::__construct() */
		][ $this->subpage ];

		$this->subpage_obj = container()->get( $subpage_class );
	}

	private function global_handle_request(): void {
		// simplify
		$_poll_id = 0;
		$set_poll_id__cb = function( $name ) use ( & $_poll_id ) {
			if( empty( $_REQUEST[ $name ] ) ){
				return $_poll_id = 0;
			}

			if( ! Admin_Page::check_nonce() ){
				$this->messages->add_error( 'Bad Nonce' );
				return 0;
			}

			$_poll_id = (int) $_REQUEST[ $name ];

			return Poll_Utils::cuser_can_edit_poll( $_poll_id ) ? $_poll_id : 0;
		};

		if( $set_poll_id__cb( 'delete_poll' ) ){
			Admin_Page_Edit_Poll::delete_poll( $_poll_id );
		}

		if( $set_poll_id__cb( 'dmc_activate_poll' ) ){
			Admin_Page_Edit_Poll::activate_poll( $_poll_id );
		}
		if( $set_poll_id__cb( 'dmc_deactivate_poll' ) ){
			Admin_Page_Edit_Poll::deactivate_poll( $_poll_id );
		}

		if( $set_poll_id__cb( 'dmc_open_poll' ) ){
			Admin_Page_Edit_Poll::open_poll( $_poll_id );
		}
		if( $set_poll_id__cb( 'dmc_close_poll' ) ){
			Admin_Page_Edit_Poll::close_poll( $_poll_id );
		}
	}

	public function admin_page_output(): void {
		?>
		<div class="wrap">
			<?php $this->subpage_obj->render(); ?>
		</div>
		<?php
	}

	public static function check_nonce(): bool {
		return ( isset( $_REQUEST['_demnonce'] ) && wp_verify_nonce( $_REQUEST['_demnonce'], 'dem_adminform' ) );
	}

	public static function add_nonce( $url ): string {
		return add_query_arg( [ '_demnonce' => wp_create_nonce( 'dem_adminform' ) ], $url );
	}

}

trait Admin_Page__Additional {

	/**
	 * Displays full admin menu. Links: from subpages to home page and smart referer.
	 * Outputs error and success messages.
	 */
	public function subpages_menu(): string {

		$referer = $this->back_link();
		$main_page = wp_make_link_relative( $this->plugin->admin_page_url );

		$current_class = function( $page ) {
			return $this->subpage === $page ? ' nav-tab-active' : '';
		};

		$buttons = array_filter( [
			'back' => $referer
				? sprintf( '<a class="nav-tab" href="%s" style="margin-right:20px;">← %s</a>', $referer, __( 'Back', 'democracy-poll' ) )
				: '',
			'list' => sprintf( '<a class="nav-tab %s" href="%s">%s</a>',
				$current_class( 'polls_list' ),
				$main_page,
				__( 'Polls List', 'democracy-poll' )
			),
			'add_new' => sprintf( '<a class="nav-tab %s" href="%s">%s</a>',
				$current_class( 'add_new' ),
				add_query_arg( [ 'subpage' => 'add_new' ], $main_page ), __( 'Add new poll', 'democracy-poll' )
			),
			'logs' => sprintf( '<a style="margin-right:1em;" class="nav-tab %s" href="%s">%s</a>',
				$current_class( 'logs' ),
				add_query_arg( [ 'subpage' => 'logs' ], $main_page ), __( 'Logs', 'democracy-poll' )
			),
			'general_settings' => $this->plugin->super_access ? (
				sprintf( '<a class="nav-tab %s" href="%s">%s</a>',
					$current_class( 'general_settings' ),
					add_query_arg( [ 'subpage' => 'general_settings' ], $main_page ),
					__( 'Settings', 'democracy-poll' )
				) .
				sprintf( '<a class="nav-tab %s" href="%s">%s</a>',
					$current_class( 'design' ),
					add_query_arg( [ 'subpage' => 'design' ], $main_page ),
					__( 'Theme Settings', 'democracy-poll' )
				) .
				sprintf( '<a class="nav-tab %s" href="%s">%s</a>',
					$current_class( 'l10n' ),
					add_query_arg( [ 'subpage' => 'l10n' ], $main_page ),
					__( 'Text changes', 'democracy-poll' )
				)
			) : '',
		] );

		$out = '<h2 class="nav-tab-wrapper" style="margin-bottom:1em;">' . implode( "\n", $buttons ) . '</h2>';

		$out .= $this->messages->messages_html();

		return $out;
	}

	private function back_link(): string {
		$request_uri = $_SERVER['REQUEST_URI'];

		$transient = 'democracy_referer';
		$main_page = wp_make_link_relative( $this->plugin->admin_page_url );
		$referer = isset( $_SERVER['HTTP_REFERER'] ) ? wp_make_link_relative( $_SERVER['HTTP_REFERER'] ) : '';

		// Handle updates from the current page.
		if( $referer === $request_uri ){
			$referer = get_transient( $transient );
		}
		// Handle requests from any Democracy settings page.
		elseif( false !== strpos( $referer, $main_page ) ){
			$referer = false;
			set_transient( $transient, 'foo', 2 ); // Invalidate by replacing the transient instead of deleting it.
		}
		else{
			set_transient( $transient, $referer, HOUR_IN_SECONDS / 2 );
		}

		return $referer;
	}

	public static function info_sidebar() {
		ob_start();
		?>
		<div class="dem-info-wrap">
			<section class="dem-info-section">
				<?php
				echo str_replace(
					'<a',
					'<a target="_blank" href="https://wordpress.org/support/plugin/democracy-poll/reviews/#new-post"',
					__( 'If you like this plugin, please <a>leave your review</a>', 'democracy-poll' )
				);
				?>
			</section>
		</div>
		<?php

		return ob_get_clean();
	}

}
