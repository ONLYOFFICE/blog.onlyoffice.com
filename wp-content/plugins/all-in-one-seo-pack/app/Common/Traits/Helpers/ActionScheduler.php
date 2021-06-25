<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains Action Scheduler specific helper methods.
 *
 * @since 4.0.13
 */
trait ActionScheduler {

	/**
	 * Schedules a single action at a specific time in the future.
	 *
	 * @since 4.0.13
	 *
	 * @param  string  $actionName The action name.
	 * @param  int     $time       The time to add to the current time.
	 * @return boolean             Whether the action was scheduled.
	 */
	public function scheduleSingleAction( $actionName, $time ) {
		try {
			if ( ! $this->isScheduledAction( $actionName ) ) {
				as_schedule_single_action( time() + $time, $actionName, [], 'aioseo' );
				return true;
			}
		} catch ( \RuntimeException $e ) {
			return false;
		}
	}

	/**
	 * Checks if a given action is already scheduled.
	 *
	 * @since 4.0.13
	 *
	 * @param  string  $actionName The action name.
	 * @return boolean             Whether the action is already scheduled.
	 */
	public function isScheduledAction( $actionName ) {
		$actions = as_get_scheduled_actions( [
			'hook'     => $actionName,
			'status'   => \ActionScheduler_Store::STATUS_PENDING,
			'per_page' => 1
		]);
		return ! empty( $actions );
	}
}