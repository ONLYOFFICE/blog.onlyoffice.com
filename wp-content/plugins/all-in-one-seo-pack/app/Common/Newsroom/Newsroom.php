<?php
namespace AIOSEO\Plugin\Common\Newsroom;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads the newsroom feed that aioseo.com publishes to the CDN.
 *
 * NOTE: Every surface showing product news reads through here, so there is one
 * cache, one lock and one place a malformed document is rejected.
 *
 * @since 5.0.0
 */
class Newsroom {
	/**
	 * Where the feed is published.
	 *
	 * @since 5.0.0
	 *
	 * @var string
	 */
	private $url = 'https://plugin-cdn.aioseo.com/newsroom.json';

	/**
	 * The payload schema this understands.
	 *
	 * NOTE: A document declaring anything else is ignored rather than half-read.
	 *
	 * @since 5.0.0
	 *
	 * @var int
	 */
	const SCHEMA = 1;

	/**
	 * Which product's news this plugin shows.
	 *
	 * @since 5.0.0
	 *
	 * @var string
	 */
	const PRODUCT = 'aioseo';

	/**
	 * Cache key for the fetched items.
	 *
	 * @since 5.0.0
	 *
	 * @var string
	 */
	private $cacheKey = 'newsroom_feed';

	/**
	 * Lock key for the fetch.
	 *
	 * @since 5.0.0
	 *
	 * @var string
	 */
	private $lockKey = 'newsroom_feed_fetch_lock';

	/**
	 * The UpdateModal class instance.
	 *
	 * @since 5.0.0
	 *
	 * @var UpdateModal
	 */
	private $updateModal = null;

	/**
	 * The ScreenCallout class instance.
	 *
	 * @since 5.0.0
	 *
	 * @var ScreenCallout
	 */
	private $screenCallout = null;

	/**
	 * Class constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		$this->updateModal   = new UpdateModal();
		$this->screenCallout = new ScreenCallout();
	}

	/**
	 * Get the URL of this product's newsroom archive.
	 *
	 * NOTE: The product archive, not the combined one — the plugin's own releases are
	 * what someone clicking through from these surfaces is after.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $medium The UTM medium naming the surface.
	 * @return string         The tagged URL.
	 */
	public function getArchiveUrl( $medium ) {
		return aioseo()->helpers->utmUrl(
			AIOSEO_MARKETING_URL . 'newsroom/product/' . self::PRODUCT . '/',
			$medium,
			'view-all',
			false
		);
	}

	/**
	 * Format a feed date in the site's own date format.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $date An ISO 8601 date from the feed.
	 * @return string       The formatted date, or empty.
	 */
	public function formatDate( $date ) {
		$timestamp = strtotime( (string) $date );

		return $timestamp ? date_i18n( get_option( 'date_format' ), $timestamp ) : '';
	}

	/**
	 * Get every item in the feed, newest first.
	 *
	 * NOTE: Includes items cross-posted from other products.
	 *
	 * @since 5.0.0
	 *
	 * @return array The items, or an empty array if the feed is unavailable.
	 */
	public function getItems() {
		$items = aioseo()->core->networkCache->get( $this->cacheKey );
		if ( is_array( $items ) ) {
			return $items;
		}

		return $this->fetch();
	}

	/**
	 * Get items belonging to this plugin's own product.
	 *
	 * @since 5.0.0
	 *
	 * @return array The items.
	 */
	public function getOwnItems() {
		return array_values(
			array_filter(
				$this->getItems(),
				function ( $item ) {
					return self::PRODUCT === ( $item['product'] ?? '' );
				}
			)
		);
	}

	/**
	 * Get this plugin's items released after one version and up to another.
	 *
	 * NOTE: Cross-posted items are excluded — a version only means something inside
	 * one product's numbering, and 4.9.8 compares as newer than 1.3.0.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $from The version the site was previously running.
	 * @param  string $to   The version it is running now.
	 * @return array        The items, newest version first.
	 */
	public function getItemsBetweenVersions( $from, $to ) {
		$items = [];
		foreach ( $this->getOwnItems() as $item ) {
			$version = (string) ( $item['version'] ?? '' );

			// No version means nothing to place in a range.
			if ( '' === $version ) {
				continue;
			}

			if ( version_compare( $version, $from, '>' ) && version_compare( $version, $to, '<=' ) ) {
				$items[] = $item;
			}
		}

		usort(
			$items,
			function ( $a, $b ) {
				return version_compare( $b['version'], $a['version'] );
			}
		);

		return $items;
	}

	/**
	 * Get items that asked to be announced on a given admin screen.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $screen The admin page slug.
	 * @return array          The items.
	 */
	public function getItemsForScreen( $screen ) {
		if ( empty( $screen ) ) {
			return [];
		}

		return array_values(
			array_filter(
				$this->getItems(),
				function ( $item ) use ( $screen ) {
					return in_array( $screen, (array) ( $item['screens'] ?? [] ), true );
				}
			)
		);
	}

	/**
	 * Fetch and cache the feed.
	 *
	 * @since 5.0.0
	 *
	 * @return array The items.
	 */
	private function fetch() {
		// A cold cache on a busy site would otherwise fire one request per visitor.
		if ( null !== aioseo()->core->cache->get( $this->lockKey ) ) {
			return [];
		}

		aioseo()->core->cache->update( $this->lockKey, true, MINUTE_IN_SECONDS );

		$response = aioseo()->helpers->wpRemoteGetExternal( $this->url );

		if ( is_wp_error( $response ) ) {
			// Short retry window: a transport failure is usually transient.
			return $this->cacheAndReturn( [], 10 * MINUTE_IN_SECONDS );
		}

		// An HTTP error comes back as a successful response with an error body, so the
		// status has to be checked separately from is_wp_error().
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return $this->cacheAndReturn( [], HOUR_IN_SECONDS );
		}

		$body    = wp_remote_retrieve_body( $response );
		$payload = ! empty( $body ) ? json_decode( $body, true ) : null;

		if ( ! is_array( $payload ) || self::SCHEMA !== ( $payload['schema'] ?? null ) ) {
			return $this->cacheAndReturn( [], HOUR_IN_SECONDS );
		}

		$items = [];
		foreach ( (array) ( $payload['items'] ?? [] ) as $item ) {
			$clean = $this->sanitizeItem( $item );
			if ( ! empty( $clean ) ) {
				$items[] = $clean;
			}
		}

		return $this->cacheAndReturn( $items, DAY_IN_SECONDS );
	}

	/**
	 * Cache a result, release the lock and hand it back.
	 *
	 * @since 5.0.0
	 *
	 * @param  array $items      The items to cache.
	 * @param  int   $expiration How long to keep them.
	 * @return array             The items.
	 */
	private function cacheAndReturn( $items, $expiration ) {
		aioseo()->core->networkCache->update( $this->cacheKey, $items, $expiration );
		aioseo()->core->cache->delete( $this->lockKey );

		return $items;
	}

	/**
	 * Sanitise one item from the feed.
	 *
	 * NOTE: This arrives over the network, so nothing in it is trusted.
	 *
	 * @since 5.0.0
	 *
	 * @param  mixed $item The raw item.
	 * @return array       The sanitised item, or empty if unusable.
	 */
	private function sanitizeItem( $item ) {
		if ( ! is_array( $item ) ) {
			return [];
		}

		$title = isset( $item['title'] ) ? sanitize_text_field( (string) $item['title'] ) : '';
		$url   = isset( $item['url'] ) ? esc_url_raw( (string) $item['url'] ) : '';

		// Without a title and somewhere to send people, an item can't be rendered.
		if ( '' === $title || '' === $url ) {
			return [];
		}

		return [
			'id'      => isset( $item['id'] ) ? (int) $item['id'] : 0,
			'title'   => $title,
			'excerpt' => isset( $item['excerpt'] ) ? sanitize_text_field( (string) $item['excerpt'] ) : '',
			'url'     => $url,
			'date'    => isset( $item['date'] ) ? sanitize_text_field( (string) $item['date'] ) : '',
			'product' => isset( $item['product'] ) ? sanitize_key( $item['product'] ) : '',
			'label'   => isset( $item['label'] ) ? sanitize_text_field( (string) $item['label'] ) : '',
			'badge'   => isset( $item['badge'] ) ? sanitize_key( $item['badge'] ) : 'news',
			'version' => isset( $item['version'] ) ? preg_replace( '/[^0-9.]/', '', (string) $item['version'] ) : '',
			'screens' => array_values( array_filter( array_map( 'sanitize_key', (array) ( $item['screens'] ?? [] ) ) ) ),
			'image'   => isset( $item['image'] ) ? esc_url_raw( (string) $item['image'] ) : '',
			'callout' => $this->sanitizeCallout( $item['callout'] ?? [] )
		];
	}

	/**
	 * Sanitise an item's callout overrides.
	 *
	 * @since 5.0.0
	 *
	 * @param  mixed $callout The raw callout.
	 * @return array          The sanitised callout, or empty if absent.
	 */
	private function sanitizeCallout( $callout ) {
		if ( ! is_array( $callout ) ) {
			return [];
		}

		$clean = [];
		foreach ( [ 'title', 'body' ] as $key ) {
			if ( ! empty( $callout[ $key ] ) ) {
				$clean[ $key ] = sanitize_text_field( (string) $callout[ $key ] );
			}
		}

		return $clean;
	}
}