<?php
namespace AIOSEO\Plugin\Common\Newsroom;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Announces a feature on the screen it actually lives on.
 *
 * NOTE: Rendered through in_admin_header — our pages are a Vue mount point, so
 * markup placed inside is replaced on mount, and admin_notices is wiped on them.
 *
 * @since 5.0.0
 */
class ScreenCallout {
	/**
	 * User meta holding the item IDs this user has dismissed.
	 *
	 * @since 5.0.0
	 *
	 * @var string
	 */
	const DISMISSED_META = 'aioseo_newsroom_dismissed_callouts';

	/**
	 * How old an item can be and still announce itself.
	 *
	 * NOTE: Without this, someone installing today would meet years of callouts one
	 * screen at a time.
	 *
	 * @since 5.0.0
	 *
	 * @var int
	 */
	const MAX_AGE = 90 * DAY_IN_SECONDS;

	/**
	 * Class constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'maybeDismiss' ] );
		// Not admin_notices: Admin::hooks() calls remove_all_actions( 'admin_notices' )
		// on our own screens, which are the only ones this targets. in_admin_header
		// survives and renders in the same position.
		add_action( 'in_admin_header', [ $this, 'maybeRender' ] );
	}

	/**
	 * Record a dismissal and send the user back to the screen they were on.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function maybeDismiss() {
		if ( empty( $_GET['aioseo-newsroom-dismiss'] ) ) {
			return;
		}

		$id = (int) $_GET['aioseo-newsroom-dismiss'];
		if ( ! $id || ! aioseo()->access->isAdmin() ) {
			return;
		}

		check_admin_referer( 'aioseo-newsroom-dismiss-' . $id );

		$userId    = get_current_user_id();
		$dismissed = (array) get_user_meta( $userId, self::DISMISSED_META, true );
		$dismissed = array_values( array_unique( array_merge( array_map( 'intval', $dismissed ), [ $id ] ) ) );

		update_user_meta( $userId, self::DISMISSED_META, $dismissed );

		wp_safe_redirect( remove_query_arg( [ 'aioseo-newsroom-dismiss', '_wpnonce' ] ) );
		exit;
	}

	/**
	 * Render the newest undismissed callout for the current screen.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function maybeRender() {
		if ( ! aioseo()->access->isAdmin() ) {
			return;
		}

		/**
		 * Allows in-plugin feature callouts to be turned off.
		 *
		 * @since 5.0.0
		 *
		 * @param bool $show Whether to show callouts.
		 */
		if ( ! apply_filters( 'aioseo_show_newsroom_callouts', true ) ) {
			return;
		}

		$item = $this->getItem();
		if ( empty( $item ) ) {
			return;
		}

		$title = $item['callout']['title'] ?? $item['title'];
		$body  = $item['callout']['body'] ?? $item['excerpt'];

		$dismissUrl = wp_nonce_url(
			add_query_arg( 'aioseo-newsroom-dismiss', $item['id'] ),
			'aioseo-newsroom-dismiss-' . $item['id']
		);
		?>
		<style>
			.aioseo-nr-callout { display: flex; align-items: flex-start; gap: 12px; margin: 16px 20px 8px 2px;
				padding: 14px 16px; border: 1px solid #cfe0ff; border-left: 3px solid #005ae0; border-radius: 4px;
				background: #f0f6ff; }
			.aioseo-nr-callout__icon { flex: 0 0 auto; color: #005ae0; }
			.aioseo-nr-callout__main { flex: 1 1 auto; min-width: 0; }
			.aioseo-nr-callout__meta { display: flex; align-items: center; gap: 6px; margin-bottom: 5px; flex-wrap: wrap; }
			.aioseo-nr-callout__eyebrow { color: #004f9d; font-size: 10px; font-weight: 700; letter-spacing: .09em;
				text-transform: uppercase; }
			.aioseo-nr-callout__ver { padding: 1px 5px; border: 1px solid #cfe0ff; border-radius: 3px; color: #434960;
				font-size: 10px; font-variant-numeric: tabular-nums; }
			.aioseo-nr-callout__title { margin: 0 0 3px; color: #141b38; font-size: 14px; font-weight: 600; }
			.aioseo-nr-callout__body { margin: 0 0 9px; color: #434960; font-size: 12.5px; line-height: 1.5; }
			.aioseo-nr-callout__actions { display: flex; align-items: center; gap: 14px; }
			.aioseo-nr-callout__link { color: #005ae0; font-size: 12.5px; font-weight: 600; text-decoration: none; }
			.aioseo-nr-callout__dismiss { color: #8c8f9a; font-size: 12px; text-decoration: none; }
			/* in_admin_header puts this above #wpbody, where the app's fixed header covers the
			top of #wpcontent. Clear it here and drop the app's offset, which this now provides. */
			@media screen and (min-width: 601px) {
				.aioseo-nr-callout { margin-top: calc(var(--aioseo-header-height, 72px) + 16px); }
				.aioseo-nr-callout ~ #wpbody .aioseo-main > .aioseo-container { margin-top: 0; }
			}
		</style>
		<div class="aioseo-nr-callout">
			<span class="aioseo-nr-callout__icon" aria-hidden="true">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" focusable="false">
					<path d="M18 11v2h4v-2h-4zm-2 6.61c.96.71 2.21 1.65 3.2 2.39.4-.53.8-1.07
						1.2-1.6-.99-.74-2.24-1.68-3.2-2.4-.4.54-.8 1.08-1.2 1.61zM20.4 5.6c-.4-.53-.8-1.07-1.2-1.6-.99.74-2.24
						1.68-3.2 2.4.4.53.8 1.07 1.2 1.6.96-.72 2.21-1.65 3.2-2.4zM4 9c-1.1 0-2 .9-2 2v2c0 1.1.9 2 2
						2h1v4h2v-4h1l5 3V6L8 9H4zm11.5 3c0-1.33-.58-2.53-1.5-3.35v6.69c.92-.81 1.5-2.01 1.5-3.34z" />
				</svg>
			</span>
			<div class="aioseo-nr-callout__main">
				<div class="aioseo-nr-callout__meta">
					<span class="aioseo-nr-callout__eyebrow"><?php esc_html_e( 'New', 'all-in-one-seo-pack' ); ?></span>
					<?php if ( ! empty( $item['version'] ) ) : ?>
						<span class="aioseo-nr-callout__ver">v<?php echo esc_html( $item['version'] ); ?></span>
					<?php endif; ?>
				</div>

				<h3 class="aioseo-nr-callout__title"><?php echo esc_html( $title ); ?></h3>

				<?php if ( ! empty( $body ) ) : ?>
					<p class="aioseo-nr-callout__body"><?php echo esc_html( $body ); ?></p>
				<?php endif; ?>

				<div class="aioseo-nr-callout__actions">
					<a
						class="aioseo-nr-callout__link"
						target="_blank"
						rel="noopener noreferrer"
						href="<?php echo esc_url( aioseo()->helpers->utmUrl( $item['url'], 'newsroom-screen-callout', null, false ) ); ?>"
					><?php esc_html_e( 'Read the update', 'all-in-one-seo-pack' ); ?> &rarr;</a>

					<a class="aioseo-nr-callout__dismiss" href="<?php echo esc_url( $dismissUrl ); ?>">
						<?php esc_html_e( 'Dismiss', 'all-in-one-seo-pack' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get the item to announce on the current screen, if any.
	 *
	 * @since 5.0.0
	 *
	 * @return array The item, or empty.
	 */
	private function getItem() {
		$screen = $this->getScreenSlug();
		if ( '' === $screen ) {
			return [];
		}

		$dismissed = array_map( 'intval', (array) get_user_meta( get_current_user_id(), self::DISMISSED_META, true ) );
		$cutoff    = time() - self::MAX_AGE;

		foreach ( aioseo()->newsroom->getItemsForScreen( $screen ) as $item ) {
			if ( in_array( (int) $item['id'], $dismissed, true ) ) {
				continue;
			}

			if ( ! empty( $item['date'] ) && strtotime( $item['date'] ) < $cutoff ) {
				continue;
			}

			// One at a time: the feed is newest-first, so this is the most recent.
			return $item;
		}

		return [];
	}

	/**
	 * Get the current admin page slug.
	 *
	 * @since 5.0.0
	 *
	 * @return string The slug, or empty if this isn't one of our screens.
	 */
	private function getScreenSlug() {
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore HM.Security.NonceVerification.Recommended

		return 0 === strpos( $page, 'aioseo-' ) ? $page : '';
	}
}