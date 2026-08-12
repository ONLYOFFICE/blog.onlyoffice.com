<?php
namespace AIOSEO\Plugin\Common\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles bulk actions for TruSEO eligible post types.
 *
 * @since 5.0.0
 */
class BulkActions {
	/**
	 * Construct method.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		// Delay hook registration until admin_init to ensure translations are loaded.
		add_action( 'admin_init', [ $this, 'registerHooks' ] );
	}

	/**
	 * Register hooks for bulk actions.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function registerHooks() {
		// Register hooks for all TruSEO-eligible post types.
		$postTypes = aioseo()->helpers->getTruSeoEligiblePostTypes();
		foreach ( $postTypes as $postType ) {
			add_filter( 'bulk_actions-edit-' . $postType, [ $this, 'registerTruSeoResetBulkAction' ] );
			add_filter( 'handle_bulk_actions-edit-' . $postType, [ $this, 'handleTruSeoResetBulkAction' ], 10, 3 );
		}

		// Register admin notice hook.
		add_action( 'admin_notices', [ $this, 'showAdminNotice' ] );

		// Remove query arg from URL after notice is shown.
		add_filter( 'removable_query_args', [ $this, 'addRemovableQueryArgs' ] );
	}

	/**
	 * Register the TruSEO Reset bulk action.
	 *
	 * @since 5.0.0
	 *
	 * @param  array $bulkActions The existing bulk actions.
	 * @return array              The modified bulk actions.
	 */
	public function registerTruSeoResetBulkAction( $bulkActions ) {
		$bulkActions[ AIOSEO_PLUGIN_SHORT_NAME ]['aioseo_truseo_reset'] = __( 'Regenerate TruSEO score', 'all-in-one-seo-pack' );

		return $bulkActions;
	}

	/**
	 * Handle the TruSEO Reset bulk action.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $redirectTo The redirect URL.
	 * @param  string $doAction   The action being performed.
	 * @param  array  $postIds    The post IDs to process.
	 * @return string             The modified redirect URL.
	 */
	public function handleTruSeoResetBulkAction( $redirectTo, $doAction, $postIds ) {
		if ( 'aioseo_truseo_reset' !== $doAction ) {
			return $redirectTo;
		}

		// Only reset posts the current user is allowed to edit; skip the rest.
		$postIds = array_filter( array_map( 'intval', (array) $postIds ), function( $postId ) {
			return current_user_can( 'edit_post', $postId );
		} );

		// Bail before the query runs — an empty list would drop the WHERE clause and reset every row.
		if ( empty( $postIds ) ) {
			return $redirectTo;
		}

		// Reset TruSEO fields for selected posts.
		aioseo()->core->db->update( 'aioseo_posts' )
			->whereIn( 'post_id', $postIds )
			->set( [
				'truseo'              => null,
				'focus_keyword'       => null,
				'additional_keywords' => null,
				'keyphrases'          => null,
				'page_analysis'       => null,
				'seo_score'           => 0
			] )
			->run();

		// Add success notice.
		$redirectTo = add_query_arg( 'aioseo_truseo_reset', count( $postIds ), $redirectTo );

		return $redirectTo;
	}

	/**
	 * Display admin notice after bulk action completes.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function showAdminNotice() {
		if ( ! empty( $_REQUEST['aioseo_truseo_reset'] ) ) { // phpcs:ignore HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
			$count = intval( $_REQUEST['aioseo_truseo_reset'] ); // phpcs:ignore HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
			printf(
				'<div class="notice-truseo-reset notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* Translators: %s - The number of posts. */
						_n(
							'TruSEO data reset for %s post.',
							'TruSEO data reset for %s posts.',
							$count,
							'all-in-one-seo-pack'
						),
						$count
					)
				)
			);
		}
	}

	/**
	 * Add our custom query arg to the removable query args list.
	 *
	 * @since 5.0.0
	 *
	 * @param  array $args The existing removable query args.
	 * @return array       The modified removable query args.
	 */
	public function addRemovableQueryArgs( $args ) {
		$args[] = 'aioseo_truseo_reset';

		return $args;
	}
}