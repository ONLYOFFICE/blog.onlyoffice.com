<?php

namespace DemocracyPoll\Admin;

use DemocracyPoll\Poll_Utils;
use function DemocracyPoll\plugin;

class Admin_Page {

	use Admin_Page__Additional;

	/** @var string */
	public $subpage;

	/** @var Admin_Subpage_Interface */
	public $subpage_obj;

	/** @var int */
	public $edit_poll_id;

	public function __construct(){
		$this->edit_poll_id = (int) ( $_GET['edit_poll'] ?? 0 );

		$this->subpage = sanitize_key( $_GET['subpage'] ?? '' );
		if( ( ! $this->subpage && ! $this->edit_poll_id ) ){
			$this->subpage = 'polls_list';
		}

		if( $this->edit_poll_id ){
			$this->subpage = 'edit_poll'; // hack
		}
	}

	public function init(){
		if( ! plugin()->admin_access ){
			return;
		}

		add_action( 'admin_menu', [ $this, 'register_option_page' ] );

		// Сохранение настроек экрана
		add_filter( 'set-screen-option', function( $status, $option, $value ) {
			return in_array( $option, [ 'dem_polls_per_page', 'dem_logs_per_page' ] ) ? (int) $value : $status;
		}, 10, 3 );
	}

	public function register_option_page() {
		if( ! plugin()->admin_access ){
			return;
		}

		$title = __( 'Democracy Poll', 'democracy-poll' );
		$hook_name = add_options_page( $title, $title, 'edit_posts', basename( plugin()->dir ), [ $this, 'admin_page_output' ] );
		// notice: `edit_posts` (role more then subscriber) because capability tests inside the `admin_page.php` and `admin_page_load()`

		add_action( "load-$hook_name", [ $this, 'admin_page_load' ] );
	}

	public function admin_page_load(): void {

		// datepicker
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-style', plugin()->url . '/admin/css/jquery-ui.css', [], plugin()->ver );

		// democracy
		wp_enqueue_script( 'democracy-scripts', plugin()->url . '/js/admin.js', [ 'jquery' ], plugin()->ver, true );
		wp_enqueue_style( 'democracy-styles', plugin()->url . '/admin/css/admin.css', [], plugin()->ver );

		$this->run_upgrade();

		$this->global_handle_request();
		$this->set_subpage_obj();
		$this->subpage_obj->load();
		$this->subpage_obj->request_handler();
	}

	private function set_subpage_obj(){

		if( $this->edit_poll_id ){
			$this->subpage_obj = new Admin_Page_Edit_Poll( $this );
			$this->subpage_obj->set_poll_id( $this->edit_poll_id );
		}
		else {
			$subpage_class = [
				'polls_list'       => Admin_Page_Polls::class,
				'add_new'          => Admin_Page_Edit_Poll::class,
				'edit_poll'        => Admin_Page_Edit_Poll::class,
				'logs'             => Admin_Page_Logs::class,
				'general_settings' => Admin_Page_Settings::class,
				'design'           => Admin_Page_Design::class,
				'l10n'             => Admin_Page_l10n::class,
				'migration'        => Admin_Page_Other_Migrations::class,
			];

			$this->subpage_obj = new $subpage_class[ $this->subpage ]( $this );
		}
	}

	private function global_handle_request(){

		// simplify
		$_poll_id = 0;
		$set_poll_id__cb = static function( $name ) use ( & $_poll_id ) {
			if( empty( $_REQUEST[ $name ] ) ){
				return $_poll_id = 0;
			}

			if( ! Admin_Page::check_nonce() ){
				plugin()->msg->add_error( 'Bad Nonce' );
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

	public function admin_page_output() {
		?>
		<div class="wrap">
			<?php $this->subpage_obj->render(); ?>
		</div>
		<?php
	}

	private function run_upgrade(){
		// maybe force upgrade
		if( isset( $_POST['dem_forse_upgrade'] ) && plugin()->super_access ){

			update_option( 'democracy_version', '0.1' ); // hack
			( new \DemocracyPoll\Utils\Upgrader() )->upgrade();

			wp_safe_redirect( $_SERVER['REQUEST_URI'] );

			exit;
		}

		( new \DemocracyPoll\Utils\Upgrader() )->upgrade();
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

		$referer = self::back_link();
		$main_page = wp_make_link_relative( plugin()->admin_page_url );

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
			'general_settings' => plugin()->super_access ? (
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
					__( 'Texts changes', 'democracy-poll' )
				)
			) : '',
		] );

		$out = '<h2 class="nav-tab-wrapper" style="margin-bottom:1em;">' . implode( "\n", $buttons ) . '</h2>';

		if( plugin()->super_access
		    && in_array( $this->subpage, [ 'general_settings', 'design', 'l10n' ], true )
		){
			$out .= self::info_sidebar();
		}

		$out .= plugin()->msg->messages_html();

		return $out;
	}

	private static function back_link(): string {
		$request_uri = $_SERVER['REQUEST_URI'];

		$transient = 'democracy_referer';
		$main_page = wp_make_link_relative( plugin()->admin_page_url );
		$referer = isset( $_SERVER['HTTP_REFERER'] ) ? wp_make_link_relative( $_SERVER['HTTP_REFERER'] ) : '';

		// если обновляем
		if( $referer === $request_uri ){
			$referer = get_transient( $transient );
		}
		// если запрос пришел с любой страницы настроект democracy
		elseif( false !== strpos( $referer, $main_page ) ){
			$referer = false;
			set_transient( $transient, 'foo', 2 ); // удаляем. но не удалим, а обновим, так чтобы не работала
		}
		else{
			set_transient( $transient, $referer, HOUR_IN_SECONDS / 2 );
		}

		return $referer;
	}

	private static function info_sidebar() {
		ob_start();
		?>
		<style>
			.democr_options{ float: left; width: 80%; }

			.dem_info_wrap{ width: 17%; position: fixed; right: 0; padding: 2em 0; }

			@media screen and ( max-width: 1400px ){
				.democr_options{ float: none; width: 100%; }

				.dem_info_wrap{ display: none; }
			}
		</style>
		<div class="dem_info_wrap">
			<div class="infoblk">
				<?php
				echo str_replace(
					'<a',
					'<a target="_blank" href="https://wordpress.org/support/plugin/democracy-poll/reviews/#new-post"',
					__( 'If you like this plugin, please <a>leave your review</a>', 'democracy-poll' )
				);
				?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

}
