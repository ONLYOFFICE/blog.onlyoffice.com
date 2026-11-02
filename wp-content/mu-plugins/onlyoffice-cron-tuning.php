<?php
/**
 * Plugin Name: ONLYOFFICE Cron Tuning
 * Description: Action Scheduler adjustments for the external cron (blog-wp-cron CronJob in Kubernetes).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Raise the Action Scheduler queue runner time budget.
 *
 * Default is 30 seconds, after which the runner tries to re-spawn itself via
 * an HTTP loopback request. Pods cannot reach the site through the public
 * ALB, so the re-spawn always fails and one cron tick used to process only
 * ~1 long task (an AI translation holds the request up to ~5 minutes).
 * With a 300s budget a single tick from the CronJob drains several tasks
 * back-to-back. The CronJob side is sized for this: curl timeout 20 min,
 * concurrencyPolicy: Forbid.
 */
add_filter( 'action_scheduler_queue_runner_time_limit', function () {
    return 300;
} );
