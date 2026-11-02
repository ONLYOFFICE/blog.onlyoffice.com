<?php
namespace AIOSEO\Plugin\Common\Newsroom;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shows what changed after the plugin is updated.
 *
 * NOTE: Rendered in the admin footer, not the Vue app — the first screen after an
 * update is usually not one of ours, and the Vue bundle only loads on those.
 *
 * @since 5.0.0
 */
class UpdateModal {
	/**
	 * User meta holding the newest plugin version this user has already been shown.
	 *
	 * NOTE: Per user, not per site — otherwise whoever loads an admin page first
	 * burns the announcement for everyone else.
	 *
	 * @since 5.0.0
	 *
	 * @var string
	 */
	const SEEN_META = 'aioseo_newsroom_seen_version';

	/**
	 * How many releases the modal shows before deferring to the newsroom.
	 *
	 * @since 5.0.0
	 *
	 * @var int
	 */
	const MAX_RELEASES = 5;

	/**
	 * Class constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		add_action( 'admin_footer', [ $this, 'maybeRender' ] );
	}

	/**
	 * Render the modal if this user has an update to catch up on.
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
		 * Allows the "what's new" modal to be turned off.
		 *
		 * @since 5.0.0
		 *
		 * @param bool $show Whether to show the modal.
		 */
		if ( ! apply_filters( 'aioseo_show_whats_new_modal', true ) ) {
			return;
		}

		$userId  = get_current_user_id();
		$current = aioseo()->version;
		$seen    = (string) get_user_meta( $userId, self::SEEN_META, true );

		// First sight of this user: record where they are and show nothing, so a fresh
		// install isn't shown a backlog it was never missing.
		if ( '' === $seen ) {
			update_user_meta( $userId, self::SEEN_META, $current );

			return;
		}

		if ( version_compare( $seen, $current, '>=' ) ) {
			return;
		}

		$items = aioseo()->newsroom->getItemsBetweenVersions( $seen, $current );

		// Nothing published for the skipped versions, so there's nothing worth
		// interrupting for — which is how a patch release stays silent with no flag.
		if ( empty( $items ) ) {
			update_user_meta( $userId, self::SEEN_META, $current );

			return;
		}

		// Recorded before rendering, so this shows once per version even if the page is
		// closed without dismissing.
		update_user_meta( $userId, self::SEEN_META, $current );

		$this->render( $items, $seen, $current );
	}

	/**
	 * Output the modal.
	 *
	 * @since 5.0.0
	 *
	 * @param  array  $items   The items to show.
	 * @param  string $from    The version the user was last shown.
	 * @param  string $to      The version now running.
	 * @return void
	 */
	private function render( $items, $from, $to ) {
		// Grouped by version so a release reads as one thing with several changes.
		$groups = [];
		foreach ( $items as $item ) {
			$groups[ $item['version'] ][] = $item;
		}

		$archiveUrl = aioseo()->newsroom->getArchiveUrl( 'newsroom-update-modal' );
		$releases   = count( $groups );

		// Coming from a much older version can span dozens of releases, which turns the
		// modal into a scroll. Show the most recent and point at the newsroom for the rest.
		$hidden = 0;
		if ( self::MAX_RELEASES < $releases ) {
			$hidden = $releases - self::MAX_RELEASES;
			$groups = array_slice( $groups, 0, self::MAX_RELEASES, true );
		}
		?>
		<style>
			.aioseo-nr-modal { position: fixed; inset: 0; z-index: 160000; display: flex; align-items: center;
				justify-content: center; padding: 24px; background: rgba(20, 27, 56, .45); }
			.aioseo-nr-modal__card { width: 100%; max-width: 540px; max-height: 84vh; display: flex; flex-direction: column;
				background: #fff; border-radius: 6px; box-shadow: 0 24px 60px rgba(20, 27, 56, .3); overflow: hidden; }
			.aioseo-nr-modal__head { position: relative; padding: 22px 24px 18px; border-bottom: 1px solid #e8e8eb;
				background: linear-gradient(180deg, #f0f6ff 0%, #fff 100%); }
			.aioseo-nr-modal__close { position: absolute; top: 12px; right: 14px; padding: 4px; border: 0; background: none;
				color: #8c8f9a; font-size: 16px; line-height: 1; cursor: pointer; }
			.aioseo-nr-modal__kicker { margin: 0 0 6px; color: #005ae0; font-size: 10px; font-weight: 700;
				letter-spacing: .12em; text-transform: uppercase; }
			.aioseo-nr-modal__title { margin: 0 0 6px; color: #141b38; font-size: 19px; font-weight: 600; line-height: 1.25; }
			.aioseo-nr-modal__sub { margin: 0; color: #434960; font-size: 13px; }
			.aioseo-nr-modal__body { flex: 1 1 auto; overflow-y: auto; padding: 4px 24px; }
			.aioseo-nr-modal__release { padding: 16px 0; border-bottom: 1px solid #e8e8eb; }
			.aioseo-nr-modal__release:last-child { border-bottom: 0; }
			.aioseo-nr-modal__rmeta { display: flex; align-items: center; gap: 7px; margin-bottom: 8px; flex-wrap: wrap; }
			.aioseo-nr-modal__badge { padding: 2px 6px; border-radius: 3px; background: #e5efff; color: #0046b0;
				font-size: 10px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; line-height: 1.4; }
			.aioseo-nr-modal__ver { padding: 1px 5px; border: 1px solid #e8e8eb; border-radius: 3px; color: #434960;
				font-size: 10px; font-variant-numeric: tabular-nums; }
			.aioseo-nr-modal__date { color: #8c8f9a; font-size: 11px; }
			.aioseo-nr-modal__list { margin: 0; padding-left: 18px; color: #434960; font-size: 13px; line-height: 1.6;
				list-style: disc outside; }
			/* The clamp used display:-webkit-box, which removes the list marker, so the
			clamp is dropped in favour of a divider between items. */
			.aioseo-nr-modal__list li { position: relative; margin: 0 0 10px; padding: 0 6px 10px;
				border-bottom: 1px solid #f0f0f1; list-style: disc outside;
				transition: background-color .15s ease; }
			.aioseo-nr-modal__list li:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: 0; }
			.aioseo-nr-modal__list li:hover { background-color: #f9f9fa; }
			.aioseo-nr-modal__list a { color: #141b38; font-weight: 600; text-decoration: none; }
			/* Stretched over the whole row, so anywhere on it is clickable while the
			link's accessible name stays the article title. */
			.aioseo-nr-modal__list a::after { content: ""; position: absolute; inset: 0; }
			.aioseo-nr-modal__list li:hover a { color: #005ae0; text-decoration: underline; }
			.aioseo-nr-modal__rest { display: block; padding: 10px 24px; border-top: 1px solid #e8e8eb;
				color: #005ae0; font-size: 12.5px; font-weight: 600; text-decoration: none; }
			.aioseo-nr-modal__rest:hover { text-decoration: underline; }
			.aioseo-nr-modal__foot { display: flex; align-items: center; justify-content: space-between; gap: 12px;
				padding: 16px 24px; border-top: 1px solid #e8e8eb; background: #f9f9fa; }
			.aioseo-nr-modal__note { margin: 0; color: #8c8f9a; font-size: 11px; }
			.aioseo-nr-modal__actions { display: flex; gap: 8px; }
			/* Values mirror components/common/base/Button.vue (.blue and .gray): the modal
			renders outside the Vue app, so the component itself isn't available here. */
			.aioseo-nr-modal__btn { display: inline-flex; align-items: center; padding: 7px 14px; border-radius: 3px;
				border: 1px solid #005ae0; background-color: #005ae0; color: #fff; font-size: 13px; font-weight: 600;
				text-decoration: none; cursor: pointer; transition: background-color .2s ease, border-color .2s ease; }
			.aioseo-nr-modal__btn:hover, .aioseo-nr-modal__btn:focus { border-color: #1a82ea;
				background-color: #1a82ea; color: #fff; }
			.aioseo-nr-modal__btn:active { border-color: #004f9d; background-color: #004f9d; }
			.aioseo-nr-modal__btn--ghost { border-color: #dcdde1; background-color: #f3f4f5; color: #141b38; }
			.aioseo-nr-modal__btn--ghost:hover, .aioseo-nr-modal__btn--ghost:focus { border-color: #dcdde1;
				background-color: #fff; color: #141b38; }
		</style>

		<div class="aioseo-nr-modal" role="dialog" aria-modal="true" aria-labelledby="aioseo-nr-modal-title">
			<div class="aioseo-nr-modal__card">
				<div class="aioseo-nr-modal__head">
					<button type="button" class="aioseo-nr-modal__close" data-aioseo-nr-close aria-label="<?php esc_attr_e( 'Close', 'all-in-one-seo-pack' ); ?>">&#10005;</button>
					<p class="aioseo-nr-modal__kicker">
						<?php
						printf(
							/* translators: 1 - the previous plugin version, 2 - the current plugin version. */
							esc_html__( 'Updated &middot; %1$s to %2$s', 'all-in-one-seo-pack' ),
							esc_html( $from ),
							esc_html( $to )
						);
						?>
					</p>
					<h2 class="aioseo-nr-modal__title" id="aioseo-nr-modal-title">
						<?php esc_html_e( "Here's what you missed", 'all-in-one-seo-pack' ); ?>
					</h2>
					<p class="aioseo-nr-modal__sub">
						<?php
						printf(
							/* translators: %s - the number of releases, always 1 or more. */
							esc_html( _n( '%s release landed since you last opened AIOSEO.', '%s releases landed since you last opened AIOSEO.', $releases, 'all-in-one-seo-pack' ) ),
							esc_html( number_format_i18n( $releases ) )
						);
						?>
					</p>
				</div>

				<div class="aioseo-nr-modal__body">
					<?php foreach ( $groups as $version => $groupItems ) : ?>
						<div class="aioseo-nr-modal__release">
							<div class="aioseo-nr-modal__rmeta">
								<?php if ( ! empty( $groupItems[0]['label'] ) ) : ?>
									<span class="aioseo-nr-modal__badge"><?php echo esc_html( $groupItems[0]['label'] ); ?></span>
								<?php endif; ?>
								<span class="aioseo-nr-modal__ver">v<?php echo esc_html( $version ); ?></span>
								<?php if ( ! empty( $groupItems[0]['date'] ) ) : ?>
									<span class="aioseo-nr-modal__date">
										<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $groupItems[0]['date'] ) ) ); ?>
									</span>
								<?php endif; ?>
							</div>

							<ul class="aioseo-nr-modal__list">
								<?php foreach ( $groupItems as $item ) : ?>
									<li>
										<a
											target="_blank"
											rel="noopener noreferrer"
											href="<?php echo esc_url( aioseo()->helpers->utmUrl( $item['url'], 'newsroom-update-modal', null, false ) ); ?>"
										><?php echo esc_html( $item['title'] ); ?></a>
										<?php if ( ! empty( $item['excerpt'] ) ) : ?>
											&mdash; <?php echo esc_html( $item['excerpt'] ); ?>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>
				</div>

				<?php if ( $hidden ) : ?>
					<a class="aioseo-nr-modal__rest" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( $archiveUrl ); ?>">
						<?php
						printf(
							/* translators: %s - the number of older releases not shown. */
							esc_html( _n( '%s earlier release is in the Newsroom', '%s earlier releases are in the Newsroom', $hidden, 'all-in-one-seo-pack' ) ),
							esc_html( number_format_i18n( $hidden ) )
						);
						?>
						&rarr;
					</a>
				<?php endif; ?>

				<div class="aioseo-nr-modal__foot">
					<p class="aioseo-nr-modal__note"><?php esc_html_e( 'Shown once per version', 'all-in-one-seo-pack' ); ?></p>
					<span class="aioseo-nr-modal__actions">
						<button type="button" class="aioseo-nr-modal__btn aioseo-nr-modal__btn--ghost" data-aioseo-nr-close>
							<?php esc_html_e( 'Dismiss', 'all-in-one-seo-pack' ); ?>
						</button>
						<a class="aioseo-nr-modal__btn" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( $archiveUrl ); ?>">
							<?php esc_html_e( 'View all updates', 'all-in-one-seo-pack' ); ?> &rarr;
						</a>
					</span>
				</div>
			</div>
		</div>

		<script>
			( function() {
				var modal = document.querySelector( '.aioseo-nr-modal' );
				if ( ! modal ) {
					return;
				}

				// Nothing to persist: the version was recorded server-side at render.
				var close = function() {
					modal.remove();
					document.removeEventListener( 'keydown', onKey );
				};

				var onKey = function( event ) {
					if ( 'Escape' === event.key ) {
						close();
					}
				};

				modal.querySelectorAll( '[data-aioseo-nr-close]' ).forEach( function( button ) {
					button.addEventListener( 'click', close );
				} );

				modal.addEventListener( 'click', function( event ) {
					if ( event.target === modal ) {
						close();
					}
				} );

				document.addEventListener( 'keydown', onKey );
			} )();
		</script>
		<?php
	}
}