<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains all context related helper methods.
 * This includes methods to check the context of the current request, but also get WP objects.
 *
 * @since 4.1.4
 */
trait WpContext {
	/**
	 * Get the home page object.
	 *
	 * @since 4.1.1
	 *
	 * @return WP_Post|null The home page.
	 */
	public function getHomePage() {
		$homePageId = $this->getHomePageId();

		return $homePageId ? get_post( $homePageId ) : null;
	}

	/**
	 * Get the ID of the home page.
	 *
	 * @since 4.0.0
	 *
	 * @return integer|null The home page ID.
	 */
	public function getHomePageId() {
		$pageShowOnFront = ( 'page' === get_option( 'show_on_front' ) );
		$pageOnFrontId   = get_option( 'page_on_front' );

		return $pageShowOnFront && $pageOnFrontId ? (int) $pageOnFrontId : null;
	}

	/**
	 * Returns the blog page.
	 *
	 * @since 4.0.0
	 *
	 * @return WP_Post|null The blog page.
	 */
	public function getBlogPage() {
		$blogPageId = $this->getBlogPageId();

		return $blogPageId ? get_post( $blogPageId ) : null;
	}

	/**
	 * Gets the current blog page id if it's configured.
	 *
	 * @since 4.1.1
	 *
	 * @return int|null
	 */
	public function getBlogPageId() {
		$pageShowOnFront = ( 'page' === get_option( 'show_on_front' ) );
		$blogPageId      = (int) get_option( 'page_for_posts' );

		return $pageShowOnFront && $blogPageId ? $blogPageId : null;
	}

	/**
	 * Checks whether the current page is a taxonomy term archive.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether the current page is a taxonomy term archive.
	 */
	public function isTaxTerm() {
		$object = get_queried_object();
		return $object instanceof \WP_Term;
	}

	/**
	 * Checks whether the current page is a static one.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether the current page is a static one.
	 */
	public function isStaticPage() {
		return $this->isStaticHomePage() || $this->isStaticPostsPage() || $this->isWooCommerceShopPage();
	}

	/**
	 * Checks whether the current page is the static homepage.
	 *
	 * @since 4.0.0
	 *
	 * @param  mixed   $post Pass in an optional post to check if its the static home page.
	 * @return boolean       Whether the current page is the static homepage.
	 */
	public function isStaticHomePage( $post = null ) {
		static $isHomePage = null;
		if ( null !== $isHomePage ) {
			return $isHomePage;
		}

		$post = aioseo()->helpers->getPost( $post );
		return ( 'page' === get_option( 'show_on_front' ) && ! empty( $post->ID ) && (int) get_option( 'page_on_front' ) === $post->ID );
	}

	/**
	 * Checks whether the current page is the static posts page.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether the current page is the static posts page.
	 */
	public function isStaticPostsPage() {
		return is_home() && ( 0 !== (int) get_option( 'page_for_posts' ) );
	}

	/**
	 * Checks whether current page supports meta.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether the current page supports meta.
	 */
	public function supportsMeta() {
		return ! is_date() && ! is_author() && ! is_search() && ! is_404();
	}

	/**
	 * Returns the network ID.
	 *
	 * @since 4.0.0
	 *
	 * @return int The integer of the blog/site id.
	 */
	public function getNetworkId() {
		if ( is_multisite() ) {
			return get_network()->site_id;
		}
		return get_current_blog_id();
	}

	/**
	 * Returns the current post object.
	 *
	 * @since 4.0.0
	 *
	 * @param  int     $postId The post ID.
	 * @return WP_Post         The post object.
	 */
	public function getPost( $postId = false ) {
		static $showOnFront  = null;
		static $pageOnFront  = null;
		static $pageForPosts = null;

		$postId = is_a( $postId, 'WP_Post' ) ? $postId->ID : $postId;

		if ( aioseo()->helpers->isWooCommerceShopPage( $postId ) ) {
			return get_post( wc_get_page_id( 'shop' ) );
		}

		if ( is_front_page() || is_home() ) {
			$showOnFront = $showOnFront ? $showOnFront : 'page' === get_option( 'show_on_front' );
			if ( $showOnFront ) {
				if ( is_front_page() ) {
					$pageOnFront = $pageOnFront ? $pageOnFront : (int) get_option( 'page_on_front' );
					return get_post( $pageOnFront );
				} elseif ( is_home() ) {
					$pageForPosts = $pageForPosts ? $pageForPosts : (int) get_option( 'page_for_posts' );
					return get_post( $pageForPosts );
				}
			}

			return get_post();
		}

		if (
			$this->isScreenBase( 'post' ) ||
			$postId ||
			is_singular()
		) {
			return get_post( $postId );
		}
	}

	/**
	 * Returns the page content.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post|int $post The post.
	 * @return string            The content.
	 */
	public function getContent( $post = null ) {
		$post = ( $post && is_object( $post ) ) ? $post : $post = $this->getPost( $post );

		static $content = [];
		if ( isset( $content[ $post->ID ] ) ) {
			return $content[ $post->ID ];
		}

		if ( empty( $post->post_content ) ) {
			return $post->post_content;
		}

		$postContent = $post->post_content;
		if (
			! in_array( 'runShortcodesInDescription', aioseo()->internalOptions->deprecatedOptions, true ) ||
			aioseo()->options->deprecated->searchAppearance->advanced->runShortcodesInDescription
		) {
			$postContent = $this->doShortcodes( $postContent );
		}

		$postContent          = wp_trim_words( $postContent, 55, apply_filters( 'excerpt_more', ' ' . '[&hellip;]' ) );
		$postContent          = str_replace( ']]>', ']]&gt;', $postContent );
		$postContent          = preg_replace( '#(<figure.*\/figure>|<img.*\/>)#', '', $postContent );
		$content[ $post->ID ] = trim( wp_strip_all_tags( strip_shortcodes( $postContent ) ) );
		return $content[ $post->ID ];
	}

	/**
	 * Returns custom fields as a string.
	 *
	 * @since 4.0.6
	 *
	 * @param  WP_Post|int $post The post.
	 * @param  array       $keys The post meta_keys to check for values.
	 * @return string            The custom field content.
	 */
	public function getCustomFieldsContent( $post = null, $keys = [] ) {
		$post = ( $post && is_object( $post ) ) ? $post : $this->getPost( $post );

		$customFieldContent = '';

		$acfFields     = $this->getAcfContent( $post );
		$acfFieldsKeys = [];

		if ( ! empty( $acfFields ) ) {
			foreach ( $acfFields as $acfField => $acfValue ) {
				if ( in_array( $acfField, $keys, true ) ) {
					$customFieldContent .= "{$acfValue} ";
					$acfFieldsKeys[]     = $acfField;
				}
			}
		}

		foreach ( $keys as $key ) {
			if ( in_array( $key, $acfFieldsKeys, true ) ) {
				continue;
			}

			$value = get_post_meta( $post->ID, $key, true );

			if ( $value ) {
				$customFieldContent .= "{$value} ";
			}
		}

		return $customFieldContent;
	}

	/**
	 * Returns if the page is a special type (WooCommerce pages, Privacy page).
	 *
	 * @since 4.0.0
	 *
	 * @param  int     $postId The post ID.
	 * @return boolean         If the page is special or not.
	 */
	public function isSpecialPage( $postId = false ) {
		if (
			(int) get_option( 'page_for_posts' ) === (int) $postId ||
			(int) get_option( 'wp_page_for_privacy_policy' ) === (int) $postId ||
			$this->isBuddyPressPage( $postId ) ||
			$this->isWooCommercePage( $postId )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the page number of the current page.
	 *
	 * @since 4.0.0
	 *
	 * @return int The page number.
	 */
	public function getPageNumber() {
		$page  = get_query_var( 'page' );
		$paged = get_query_var( 'paged' );
		return ! empty( $page )
			? $page
			: (
				! empty( $paged )
					? $paged
					: 1
			);
	}

	/**
	 * Check if the post passed in is a valid post, not a revision or autosave.
	 *
	 * @since 4.0.5
	 *
	 * @param  WP_Post $post The Post object to check.
	 * @return bool          True if valid, false if not.
	 */
	public function isValidPost( $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( ! is_object( $post ) ) {
			$post = get_post( $post );
		}

		// In order to prevent recursion, we are skipping scheduled-action posts.
		if (
			empty( $post ) ||
			'scheduled-action' === $post->post_type ||
			'revision' === $post->post_type ||
			'publish' !== $post->post_status
		) {
			return false;
		}

		return true;
	}

	/**
	 * Checks whether the given URL is a valid attachment.
	 *
	 * @since 4.0.13
	 *
	 * @param  string  $url The URL.
	 * @return boolean      Whether the URL is a valid attachment.
	 */
	public function isValidAttachment( $url ) {
		$uploadDirUrl = aioseo()->helpers->escapeRegex( $this->getWpContentUrl() );
		return preg_match( "/$uploadDirUrl.*/", $url );
	}

	/**
	 * Tries to convert an attachment URL into a post ID.
	 *
	 * This our own optimized version of attachment_url_to_postid().
	 *
	 * @since 4.0.13
	 *
	 * @param  string       $url The attachment URL.
	 * @return int|boolean       The attachment ID or false if no attachment could be found.
	 */
	public function attachmentUrlToPostId( $url ) {
		$cacheName = "aioseo_attachment_url_to_post_id_$url";

		$cachedId = wp_cache_get( $cacheName, 'aioseo' );
		if ( $cachedId ) {
			return 'none' !== $cachedId && is_numeric( $cachedId ) ? (int) $cachedId : false;
		}

		$path          = $url;
		$uploadDirInfo = wp_get_upload_dir();

		$siteUrl   = wp_parse_url( $uploadDirInfo['url'] );
		$imagePath = wp_parse_url( $path );

		// Force the protocols to match if needed.
		if ( isset( $imagePath['scheme'] ) && ( $imagePath['scheme'] !== $siteUrl['scheme'] ) ) {
			$path = str_replace( $imagePath['scheme'], $siteUrl['scheme'], $path );
		}

		if ( ! $this->isValidAttachment( $path ) ) {
			wp_cache_set( $cacheName, 'none', 'aioseo', DAY_IN_SECONDS );
			return false;
		}

		if ( 0 === strpos( $path, $uploadDirInfo['baseurl'] . '/' ) ) {
			$path = substr( $path, strlen( $uploadDirInfo['baseurl'] . '/' ) );
		}

		$results = aioseo()->db->start( 'postmeta' )
			->select( 'post_id' )
			->where( 'meta_key', '_wp_attached_file' )
			->where( 'meta_value', $path )
			->limit( 1 )
			->run()
			->result();

		if ( empty( $results[0]->post_id ) ) {
			wp_cache_set( $cacheName, 'none', 'aioseo', DAY_IN_SECONDS );
			return false;
		}

		wp_cache_set( $cacheName, $results[0]->post_id, 'aioseo', DAY_IN_SECONDS );
		return $results[0]->post_id;
	}

	/**
	 * Returns true if the request is a non-legacy REST API request.
	 * This function was copied from WooCommerce and improved.
	 *
	 * @since 4.1.2
	 *
	 * @return bool True if this is a REST API request.
	 */
	public function isRestApiRequest() {
		global $wp_rewrite;

		if ( empty( $wp_rewrite ) ) {
			return false;
		}

		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$restUrl = wp_parse_url( get_rest_url() );
		$restUrl = $restUrl['path'] . ( ! empty( $restUrl['query'] ) ? '?' . $restUrl['query'] : '' );

		$isRestApiRequest = ( 0 === strpos( $_SERVER['REQUEST_URI'], $restUrl ) );

		return apply_filters( 'aioseo_is_rest_api_request', $isRestApiRequest );
	}

	/**
	 * Checks whether the current request is an AJAX, CRON or REST request.
	 *
	 * @since 4.1.3
	 *
	 * @return bool Wether the request is an AJAX, CRON or REST request.
	 */
	public function isAjaxCronRest() {
		return wp_doing_ajax() || wp_doing_cron() || $this->isRestApiRequest();
	}

	/**
	 * Checks whether we're on the given screen.
	 *
	 * @since 4.0.7
	 *
	 * @param  string  $screenName The screen name.
	 * @return boolean             Whether we're on the given screen.
	 */
	public function isScreenBase( $screenName ) {
		$screen = $this->getCurrentScreen();
		if ( ! $screen || ! isset( $screen->base ) ) {
			return false;
		}
		return $screen->base === $screenName;
	}

	/**
	 * Returns if current screen is of a post type
	 *
	 * @since 4.0.17
	 *
	 * @param string $postType Post type slug
	 *
	 * @return bool
	 */
	public function isScreenPostType( $postType ) {
		$screen = $this->getCurrentScreen();
		if ( ! $screen || ! isset( $screen->post_type ) ) {
			return false;
		}
		return $screen->post_type === $postType;
	}

	/**
	 * Gets current admin screen
	 *
	 * @since 4.0.17
	 *
	 * @return false|\WP_Screen|null
	 */
	public function getCurrentScreen() {
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		return get_current_screen();
	}
}