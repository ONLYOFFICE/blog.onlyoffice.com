<?php

namespace DeliciousBrains\WP_Offload_Media\Pro;

use Amazon_S3_And_CloudFront;
use AS3CF_Utils;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Background_Tool_Process;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade;

abstract class Background_Tool extends Tool {

	/**
	 * @var string
	 */
	protected $type = 'background-tool';

	/**
	 * @var string
	 */
	protected $view = 'background-tool';

	/**
	 * @var Background_Tool_Process
	 */
	protected $background_process;

	/**
	 * @var null|array
	 */
	private $batch = null;

	/**
	 * Limit the item types that this tool handles. Leave empty to handle all registered item types
	 *
	 * @var array
	 */
	protected $source_types = array();

	/**
	 * Initialize the tool.
	 */
	public function init() {
		parent::init();

		$this->background_process = $this->get_background_process_class();

		// During an upgrade, cancel all background processes.
		if ( Upgrade::is_locked() && ( $this->is_processing() || $this->is_queued() ) ) {
			$this->handle_cancel();
		}
	}

	/**
	 * Get info for tool, including current status.
	 *
	 * @return array
	 */
	public function get_info() {
		$info = $this->get_default_info();

		$this->maybe_add_loopback_request_notice( $info );

		return array_merge( parent::get_info(), $info );
	}

	/**
	 * Get a list of key names for tools that are related to the current tool.
	 *
	 * @return array
	 */
	public function get_related_tools() {
		return array();
	}

	/**
	 * Get default info for tool, including current status.
	 *
	 * @return array
	 */
	public function get_default_info(): array {
		return array(
			'name'                     => $this->get_name(),
			'title'                    => $this->get_title_text(),
			'title_partial_complete'   => $this->get_title_text_partial_complete(),
			'title_complete'           => $this->get_title_text_complete(),
			'more_info'                => $this->get_more_info_text(),
			'related_tools'            => $this->get_related_tools(),
			'prompt'                   => $this->get_prompt_text(),
			'doc_url'                  => $this->get_doc_url(),
			'doc_desc'                 => $this->get_doc_desc(),
			'status_description'       => $this->get_status_description(),
			'short_status_description' => $this->get_short_status_description(),
			'busy_description'         => $this->get_busy_description(),
			'locked_notification'      => $this->get_locked_notification(),
			'button'                   => $this->get_button_text(),
			'button_partial_complete'  => $this->get_button_text_partial_complete(),
			'button_complete'          => $this->get_button_text_complete(),
			'is_queued'                => $this->is_queued(),
			'is_paused'                => $this->is_paused(),
			'is_cancelled'             => $this->is_cancelled(),
			'is_upgrading'             => Upgrade::is_locked(),
			'progress'                 => $this->get_progress(),
			'queue'                    => $this->get_queue_counts(),
		);
	}

	/**
	 * If it looks like this tool is stuck, check loopback site health report and potentially add notice.
	 *
	 * @param array $status
	 */
	private function maybe_add_loopback_request_notice( $status ) {
		$site_health_path = trailingslashit( ABSPATH ) . 'wp-admin/includes/class-wp-site-health.php';

		if (
			! empty( $status['is_queued'] ) &&
			empty( $status['is_processing'] ) &&
			empty( $status['is_paused'] ) &&
			empty( $status['is_cancelled'] ) &&
			file_exists( $site_health_path ) &&
			(
				false === get_site_transient( $this->prefix . '_loopback_test' ) ||
				get_site_transient( $this->prefix . '_loopback_test' ) === $this->tool_key
			)
		) {
			set_site_transient( $this->prefix . '_loopback_test', $this->tool_key, 30 );

			/** @noinspection PhpIncludeInspection */
			require_once $site_health_path;
			$site_health = new \WP_Site_Health();

			$loopback = $site_health->get_test_loopback_requests();

			if (
				! empty( $loopback['status'] ) &&
				'good' !== $loopback['status'] &&
				! empty( $loopback['label'] ) &&
				! empty( $loopback['description'] ) ) {
				$args = array(
					'type'              => 'error',
					'class'             => 'tool-error',
					'dismissible'       => false,
					'flash'             => false,
					'only_show_to_user' => false,
					'only_show_on_tab'  => $this->tab,
					'custom_id'         => $this->errors_key_prefix . 'loopback_test',
					'user_capabilities' => array( 'as3cfpro', 'is_plugin_setup' ),
				);

				$site_health_link = get_dashboard_url( get_current_user_id(), 'site-health.php' );

				$doc_url  = $this->as3cf->dbrains_url( '/wp-offload-media/doc/background-processes-not-completing/', array(
					'utm_campaign' => 'support+docs',
				) );
				$doc_link = AS3CF_Utils::dbrains_link( $doc_url, __( 'Background Processes doc', 'amazon-s3-and-cloudfront' ) );

				$message = sprintf( __( 'The background process is stuck. Please ensure that the <strong>loopback request</strong> test is passing in <a href="%1$s">Site Health</a>.<br><br>For troubleshooting tips please see our %2$s.', 'amazon-s3-and-cloudfront' ), $site_health_link, $doc_link );

				$this->as3cf->notices->add_notice( $this->get_error_notice_message( $message ), $args );
			} else {
				$this->as3cf->notices->remove_notice_by_id( $this->errors_key_prefix . 'loopback_test' );
			}
		} elseif (
			false === get_site_transient( $this->prefix . '_loopback_test' ) ||
			get_site_transient( $this->prefix . '_loopback_test' ) === $this->tool_key
		) {
			// No other tool is stuck, clear out admin notice if set.
			$this->as3cf->notices->remove_notice_by_id( $this->errors_key_prefix . 'loopback_test' );
		}
	}

	/**
	 * Get more info text.
	 *
	 * @return string
	 */
	public static function get_more_info_text() {
		return '';
	}

	/**
	 * Get prompt text for when tool could be run in response to settings change.
	 * Defaults to more info text.
	 *
	 * @return string
	 */
	public static function get_prompt_text() {
		return static::get_more_info_text();
	}

	/**
	 * Returns doc URL for use in UI help buttons etc.
	 *
	 * @return string
	 *
	 * Note: Should be overridden if a dedicated help doc is available for the tool.
	 */
	public function get_doc_url() {
		return '';
	}

	/**
	 * Returns doc description for use in UI help buttons etc.
	 *
	 * @return string
	 *
	 * Note: Should be overridden if a dedicated doc description is required for the tool.
	 *       However, the UI will use a reasonable default in none supplied here.
	 */
	public function get_doc_desc() {
		return '';
	}

	/**
	 * Get status description.
	 *
	 * @return string
	 */
	public function get_status_description(): string {
		if ( $this->is_processing() && ( $this->is_cancelled() || $this->is_paused() ) ) {
			return __( 'Completing current batch.', 'amazon-s3-and-cloudfront' );
		}

		if ( $this->is_paused() ) {
			return __( 'Paused', 'amazon-s3-and-cloudfront' );
		}

		if ( $this->is_queued() ) {
			return $this->get_queued_status();
		}

		return '';
	}

	/**
	 * Get short status description.
	 *
	 * @return string
	 */
	public function get_short_status_description(): string {
		if ( $this->is_processing() && $this->is_cancelled() ) {
			return _x( 'Stopping…', 'Short tool stopping message', 'amazon-s3-and-cloudfront' );
		}

		if ( $this->is_processing() && $this->is_paused() ) {
			return _x( 'Pausing…', 'Short tool pausing message', 'amazon-s3-and-cloudfront' );
		}

		if ( $this->is_paused() ) {
			return _x( 'Paused', 'Short tool paused message', 'amazon-s3-and-cloudfront' );
		}

		if ( $this->is_queued() ) {
			return $this->get_short_queued_status();
		}

		return $this->get_status_description();
	}

	/**
	 * Get busy description.
	 *
	 * @return string
	 */
	public function get_busy_description() {
		return _x( 'Processing…', 'Short tool running message', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get description for locked notification.
	 *
	 * @return string
	 */
	public function get_locked_notification() {
		return sprintf(
			__(
				'<strong>Settings Locked</strong> &mdash; You can\'t change any of your settings until the "<a href="#/tools">%s</a>" tool has completed.',
				'amazon-s3-and-cloudfront'
			),
			$this->get_name()
		);
	}

	/**
	 * Handle start.
	 *
	 * Note: Dynamically called by `DeliciousBrains\WP_Offload_Media\Pro\Tools_Manager::perform_action`.
	 */
	public function handle_start() {
		if ( $this->is_queued() ) {
			return;
		}

		$this->clear_errors();
		$this->as3cf->notices->dismiss_notice( $this->errors_key );

		$session = $this->create_session();

		$this->background_process->push_to_queue( $session )->save()->dispatch();
		do_action( $this->prefix . '_' . $this->tool_key . '_started' );
	}

	/**
	 * Handle cancel.
	 *
	 * Note: Dynamically called by `DeliciousBrains\WP_Offload_Media\Pro\Tools_Manager::perform_action`.
	 */
	public function handle_cancel() {
		if ( ! $this->is_queued() ) {
			return;
		}

		$this->background_process->cancel();

		// Force this process to think there is no current batch
		// as this process will not be processing one anyway.
		// This ensures subsequent in-process checks don't return a mixed view.
		$this->batch = null;
	}

	/**
	 * Handle pause resume.
	 *
	 * Note: Dynamically called by `DeliciousBrains\WP_Offload_Media\Pro\Tools_Manager::perform_action`.
	 */
	public function handle_pause_resume() {
		if ( ! $this->is_queued() || $this->is_cancelled() ) {
			return;
		}

		if ( $this->is_paused() ) {
			$this->background_process->resume();
		} else {
			$this->background_process->pause();
		}
	}

	/**
	 * Create session.
	 *
	 * @return array
	 */
	protected function create_session() {
		/** @var Amazon_S3_And_CloudFront $as3cf */
		global $as3cf;

		$source_types      = array();
		$tool_source_types = empty( $this->source_types ) ? array_keys( $as3cf->get_source_type_classes() ) : $this->source_types;
		foreach ( $tool_source_types as $source_type ) {
			$source_types[ $source_type ] = 0;
		}

		$session = array(
			'total_items'     => 0,
			'processed_items' => 0,
			'blogs_processed' => false,
			'blogs'           => array(),
		);

		foreach ( AS3CF_Utils::get_all_blog_table_prefixes() as $blog_id => $prefix ) {
			$session['blogs'][ $blog_id ] = array(
				'prefix'         => $prefix,
				'processed'      => $source_types,
				'total_items'    => null,
				'last_source_id' => array(),
			);
		}

		return $session;
	}

	/**
	 * Get progress.
	 *
	 * @return int
	 */
	public function get_progress() {
		$batch = $this->get_batch();

		if ( empty( $batch ) ) {
			return 0;
		}

		$data = $batch->data;
		$data = array_shift( $data );

		if ( empty( $data['total_items'] ) || ! isset( $data['processed_items'] ) ) {
			return 0;
		}

		return absint( $data['processed_items'] / $data['total_items'] * 100 );
	}

	/**
	 * Is queued?
	 *
	 * @return bool
	 */
	public function is_queued(): bool {
		$batch = $this->get_batch();

		if ( empty( $batch ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get total and processed counts for queue.
	 *
	 * @return array
	 */
	public function get_queue_counts() {
		$counts = array(
			'total'     => 0,
			'processed' => 0,
		);

		$batch = $this->get_batch();

		if ( empty( $batch ) ) {
			return $counts;
		}

		$data = $batch->data;
		$data = array_shift( $data );

		if ( ! isset( $data['total_items'] ) || ! isset( $data['processed_items'] ) ) {
			return $counts;
		}

		$counts['total']     = $data['total_items'];
		$counts['processed'] = $data['processed_items'];

		return $counts;
	}

	/**
	 * Is the tool paused?
	 *
	 * @return bool
	 */
	public function is_paused() {
		return $this->background_process->is_paused();
	}

	/**
	 * Has the tool been cancelled?
	 *
	 * @return bool
	 */
	public function is_cancelled() {
		return $this->background_process->is_cancelled();
	}

	/**
	 * Is the background process currently running?
	 *
	 * @return bool
	 */
	public function is_processing() {
		return $this->background_process->is_process_running();
	}

	/**
	 * Get background process batch.
	 *
	 * @return array
	 */
	protected function get_batch() {
		if ( is_null( $this->batch ) ) {
			$batch = $this->background_process->get_batches( 1 );

			if ( empty( $batch ) ) {
				$this->batch = array();
			} else {
				$this->batch = array_shift( $batch );
			}
		}

		return $this->batch;
	}

	/**
	 * Is the tool currently active, e.g. starting, working, paused or cleaning up?
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return $this->is_queued() || $this->is_processing() || $this->is_paused() || $this->is_cancelled();
	}

	/**
	 * Get the tool's name. Defaults to its title text.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->get_title_text();
	}

	/**
	 * Get title text.
	 *
	 * @return string
	 */
	abstract public function get_title_text();

	/**
	 * Get title text for when tool is partially complete.
	 *
	 * @return string
	 */
	public function get_title_text_partial_complete() {
		return $this->get_title_text();
	}

	/**
	 * Get title text for when tool is complete.
	 *
	 * @return string
	 */
	public function get_title_text_complete() {
		return $this->get_title_text();
	}

	/**
	 * Get button text.
	 *
	 * @return string
	 */
	abstract public function get_button_text();

	/**
	 * Get button text for when tool is partially complete.
	 *
	 * @return string
	 */
	public function get_button_text_partial_complete() {
		return $this->get_button_text();
	}

	/**
	 * Get button text for when tool is complete.
	 *
	 * @return string
	 */
	public function get_button_text_complete() {
		return $this->get_button_text();
	}

	/**
	 * Get queued status text.
	 *
	 * @return string
	 */
	abstract public function get_queued_status(): string;

	/**
	 * Get a shortened queued status message.
	 *
	 * @return string
	 */
	public function get_short_queued_status(): string {
		return _x( 'Processing…', 'Short tool running message', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get background process class.
	 *
	 * @return Background_Tool_Process|null
	 */
	abstract protected function get_background_process_class();

}
