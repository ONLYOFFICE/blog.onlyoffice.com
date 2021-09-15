<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

use AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains all WP related helper methods.
 *
 * @since 4.1.4
 */
trait Wp {
	/**
	 * Whether or not we have a local connection.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	private static $connection = false;

	/**
	 * Helper method to enqueue scripts.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $script The script to enqueue.
	 * @param  string $url    The URL of the script.
	 * @param  bool   $vue    Whether or not this is a vue script.
	 * @return void
	 */
	public function enqueueScript( $script, $url, $vue = true ) {
		if ( ! wp_script_is( $script, 'enqueued' ) ) {
			wp_enqueue_script(
				$script,
				$this->getScriptUrl( $url, $vue ),
				[],
				aioseo()->version,
				true
			);
		}
	}

	/**
	 * Helper method to enqueue stylesheets.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $style The stylesheet to enqueue.
	 * @param  string $url   The URL of the stylesheet.
	 * @param  bool   $vue    Whether or not this is a vue stylesheet.
	 * @return void
	 */
	public function enqueueStyle( $style, $url, $vue = true ) {
		if ( ! wp_style_is( $style, 'enqueued' ) && $this->shouldEnqueue( $url ) ) {
			wp_enqueue_style(
				$style,
				$this->getScriptUrl( $url, $vue ),
				[],
				aioseo()->version
			);
		}
	}

	/** Whether or not we should enqueue a file.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $url The url to check against.
	 * @return bool        Whether or not we should enqueue.
	 */
	private function shouldEnqueue( $url ) {
		$version  = strtoupper( aioseo()->versionPath );
		$host     = defined( 'AIOSEO_DEV_' . $version ) ? constant( 'AIOSEO_DEV_' . $version ) : false;

		if ( ! $host ) {
			return true;
		}

		if ( false !== strpos( $url, 'chunk-common.css' ) ) {
			// return false;
		}

		return true;
	}

	/**
	 * Retrieve the proper URL for this script or style.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $url The url.
	 * @param  bool   $vue Whether or not this is a vue script.
	 * @return string      The modified url.
	 */
	public function getScriptUrl( $url, $vue = true ) {
		$version  = strtoupper( aioseo()->versionPath );
		$host     = $vue && defined( 'AIOSEO_DEV_' . $version ) ? constant( 'AIOSEO_DEV_' . $version ) : false;
		$localUrl = $url;
		$url      = plugins_url( 'dist/' . aioseo()->versionPath . '/assets/' . $url, AIOSEO_FILE );

		if ( ! $host ) {
			return $url;
		}

		if ( $host && ! self::$connection ) {
			$splitHost        = explode( ':', str_replace( '/', '', str_replace( 'http://', '', str_replace( 'https://', '', $host ) ) ) );
			self::$connection = @fsockopen( $splitHost[0], $splitHost[1] ); // phpcs:ignore WordPress
		}

		if ( ! self::$connection ) {
			return $url;
		}

		return $host . $localUrl;
	}

	/**
	 * Returns user roles in the current WP install.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of user roles.
	 */
	public function getUserRoles() {
		global $wp_roles;
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
		}
		$roleNames = $wp_roles->get_names();
		asort( $roleNames );

		return $roleNames;
	}

	/**
	 * Returns the custom roles in the current WP install.
	 *
	 * @since 4.1.3
	 *
	 * @return array An array of custom roles.
	 */
	public function getCustomRoles() {
		$allRoles = $this->getUserRoles();

		$toSkip = array_merge(
			// Default WordPress roles.
			[ 'superadmin', 'administrator', 'editor', 'author', 'contributor' ],
			// Default AIOSEO roles.
			[ 'aioseo_manager', 'aioseo_editor' ],
			// Filterable roles.
			apply_filters( 'aioseo_access_control_excluded_roles', array_merge( [
				'subscriber'
			], aioseo()->helpers->isWooCommerceActive() ? [ 'customer' ] : [] ) )
		);

		// Remove empty entries.
		$toSkip = array_filter( $toSkip );

		$customRoles = [];
		foreach ( $allRoles as $roleName => $role ) {
			// Skip specific roles.
			if ( in_array( $roleName, $toSkip, true ) ) {
				continue;
			}

			$customRoles[ $roleName ] = $role;
		}

		return $customRoles;
	}

	/**
	 * Returns an array of plugins with the active status.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of plugins with active status.
	 */
	public function getPluginData() {
		$pluginUpgrader   = new Utils\PluginUpgraderSilentAjax();
		$installedPlugins = array_keys( get_plugins() );

		$plugins = [];
		foreach ( $pluginUpgrader->pluginSlugs as $key => $slug ) {
			$plugins[ $key ] = [
				'basename'    => $slug,
				'installed'   => in_array( $slug, $installedPlugins, true ),
				'activated'   => is_plugin_active( $slug ),
				'adminUrl'    => admin_url( $pluginUpgrader->pluginAdminUrls[ $key ] ),
				'canInstall'  => aioseo()->addons->canInstall(),
				'canActivate' => aioseo()->addons->canActivate(),
				'wpLink'      => ! empty( $pluginUpgrader->wpPluginLinks[ $key ] ) ? $pluginUpgrader->wpPluginLinks[ $key ] : null
			];
		}

		return $plugins;
	}

	/**
	 * Retrieve a list of public post types with slugs/icons.
	 *
	 * @since 4.0.0
	 *
	 * @param  boolean $namesOnly       Whether only the names should be returned.
	 * @param  boolean $hasArchivesOnly Whether or not to only include post types which have archives.
	 * @param  boolean $rewriteType     Whether or not to rewrite the type slugs.
	 * @return array                    An array of public post types.
	 */
	public function getPublicPostTypes( $namesOnly = false, $hasArchivesOnly = false, $rewriteType = false ) {
		$postTypes   = [];
		$postObjects = get_post_types( [ 'public' => true ], 'objects' );
		$woocommerce = class_exists( 'woocommerce' );
		foreach ( $postObjects as $postObject ) {
			if ( empty( $postObject->label ) ) {
				continue;
			}

			// We don't want to include archives for the WooCommerce shop page.
			if (
				$hasArchivesOnly &&
				(
					! $postObject->has_archive ||
					( 'product' === $postObject->name && $woocommerce )
				)
			) {
				continue;
			}

			if ( $namesOnly ) {
				$postTypes[] = $postObject->name;
				continue;
			}

			if ( 'attachment' === $postObject->name ) {
				$postObject->label = __( 'Attachments', 'all-in-one-seo-pack' );
			}

			if ( 'product' === $postObject->name && $woocommerce ) {
				$postObject->menu_icon = 'dashicons-products';
			}

			$name = $postObject->name;
			if ( 'type' === $postObject->name && $rewriteType ) {
				$name = '_aioseo_type';
			}

			$postTypes[] = [
				'name'         => $name,
				'label'        => ucwords( $postObject->label ),
				'singular'     => ucwords( $postObject->labels->singular_name ),
				'icon'         => $postObject->menu_icon,
				'hasExcerpt'   => post_type_supports( $postObject->name, 'excerpt' ),
				'hasArchive'   => $postObject->has_archive,
				'hierarchical' => $postObject->hierarchical,
				'taxonomies'   => get_object_taxonomies( $name ),
				'slug'         => isset( $postObject->rewrite['slug'] ) ? $postObject->rewrite['slug'] : $name
			];
		}

		return $postTypes;
	}

	/**
	 * Retrieve a list of public taxonomies with slugs/icons.
	 *
	 * @since 4.0.0
	 *
	 * @param  boolean $namesOnly   Whether only the names should be returned.
	 * @param  boolean $rewriteType Whether or not to rewrite the type slugs.
	 * @return array                An array of public taxonomies.
	 */
	public function getPublicTaxonomies( $namesOnly = false, $rewriteType = false ) {
		$taxonomies = [];
		if ( count( $taxonomies ) ) {
			return $taxonomies;
		}

		$taxObjects = get_taxonomies( [ 'public' => true ], 'objects' );
		foreach ( $taxObjects as $taxObject ) {
			if ( empty( $taxObject->label ) ) {
				continue;
			}

			if ( in_array( $taxObject->name, [
				'product_shipping_class',
				'post_format'
			], true ) ) {
				continue;
			}

			// We need to exclude product attributes from this list as well.
			if (
				'pa_' === substr( $taxObject->name, 0, 3 ) &&
				'manage_product_terms' === $taxObject->cap->manage_terms &&
				! apply_filters( 'aioseo_woocommerce_product_attributes', false )
			) {
				continue;
			}

			if ( $namesOnly ) {
				$taxonomies[] = $taxObject->name;
				continue;
			}

			$name = $taxObject->name;
			if ( 'type' === $taxObject->name && $rewriteType ) {
				$name = '_aioseo_type';
			}

			$taxonomies[] = [
				'name'         => $name,
				'label'        => ucwords( $taxObject->label ),
				'singular'     => ucwords( $taxObject->labels->singular_name ),
				'icon'         => strpos( $taxObject->label, 'categor' ) !== false ? 'dashicons-category' : 'dashicons-tag',
				'hierarchical' => $taxObject->hierarchical,
				'slug'         => isset( $taxObject->rewrite['slug'] ) ? $taxObject->rewrite['slug'] : ''
			];
		}

		return $taxonomies;
	}

	/**
	 * Retrieve a list of users that match passed in roles.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of user data.
	 */
	public function getSiteUsers( $roles ) {
		static $users = [];

		if ( ! empty( $users ) ) {
			return $users;
		}

		$rolesWhere = [];
		foreach ( $roles as $role ) {
			$rolesWhere[] = '(um.meta_key = \'' . aioseo()->db->db->prefix . 'capabilities\' AND um.meta_value LIKE \'%\"' . $role . '\"%\')';
		}
		$dbUsers = aioseo()->db->start( 'users as u' )
			->select( 'u.ID, u.display_name, u.user_nicename, u.user_email' )
			->join( 'usermeta as um', 'u.ID = um.user_id' )
			->whereRaw( '(' . implode( ' OR ', $rolesWhere ) . ')' )
			->orderBy( 'u.user_nicename' )
			->run()
			->result();

		foreach ( $dbUsers as $dbUser ) {
			$users[] = [
				'id'          => intval( $dbUser->ID ),
				'displayName' => $dbUser->display_name,
				'niceName'    => $dbUser->user_nicename,
				'email'       => $dbUser->user_email,
				'gravatar'    => get_avatar_url( $dbUser->user_email )
			];
		}

		return $users;
	}

	/**
	 * Returns the ID of the site logo if it exists.
	 *
	 * @since 4.0.0
	 *
	 * @return int
	 */
	public function getSiteLogoId() {
		if ( ! get_theme_support( 'custom-logo' ) ) {
			return false;
		}
		return get_theme_mod( 'custom_logo' );
	}

	/**
	 * Returns the URL of the site logo if it exists.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public function getSiteLogoUrl() {
		$id = $this->getSiteLogoId();
		if ( ! $id ) {
			return false;
		}

		$image = wp_get_attachment_image_src( $id, 'full' );
		if ( empty( $image ) ) {
			return false;
		}
		return $image[0];
	}

	/**
	 * Returns the filesystem object if we have access to it.
	 *
	 * @since 4.0.0
	 *
	 * @param  array         $args The connection args.
	 * @return WP_Filesystem       The filesystem object.
	 */
	public function wpfs( $args = [] ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		WP_Filesystem( $args );

		global $wp_filesystem;
		if ( is_object( $wp_filesystem ) ) {
			return $wp_filesystem;
		}
		return false;
	}

	/**
	 * Returns noindexed post types.
	 *
	 * @since 4.0.0
	 *
	 * @return array A list of noindexed post types.
	 */
	public function getNoindexedPostTypes() {
		return $this->getNoindexedObjects( 'postTypes' );
	}

	/**
	 * Checks whether a given post type is noindexed.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $postType The post type.
	 * @return bool              Whether the post type is noindexed.
	 */
	public function isPostTypeNoindexed( $postType ) {
		$noindexedPostTypes = $this->getNoindexedPostTypes();
		return in_array( $postType, $noindexedPostTypes, true );
	}

	/**
	 * Returns noindexed taxonomies.
	 *
	 * @since 4.0.0
	 *
	 * @return array A list of noindexed taxonomies.
	 */
	public function getNoindexedTaxonomies() {
		return $this->getNoindexedObjects( 'taxonomies' );
	}

	/**
	 * Checks whether a given post type is noindexed.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $taxonomy The taxonomy.
	 * @return bool              Whether the taxonomy is noindexed.
	 */
	public function isTaxonomyNoindexed( $taxonomy ) {
		$noindexedTaxonomies = $this->getNoindexedTaxonomies();
		return in_array( $taxonomy, $noindexedTaxonomies, true );
	}

	/**
	 * Returns noindexed object types of a given parent type.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $type The parent object type ("postTypes" or "taxonomies").
	 * @return array        A list of noindexed objects types.
	 */
	private function getNoindexedObjects( $type ) {
		$noindexed = [];
		foreach ( aioseo()->dynamicOptions->searchAppearance->$type->all() as $name => $object ) {
			if (
				! $object['show'] ||
				( $object['advanced']['robotsMeta'] && ! $object['advanced']['robotsMeta']['default'] && $object['advanced']['robotsMeta']['noindex'] )
			) {
				$noindexed[] = $name;
			}
		}
		return $noindexed;
	}

	/**
	 * Returns all categories for a post.
	 *
	 * @since 4.1.4
	 *
	 * @param  int   $postId The post ID.
	 * @return array $names  The category names.
	 */
	public function getAllCategories( $postId = 0 ) {
		$names      = [];
		$categories = get_the_category( $postId );
		if ( $categories && count( $categories ) ) {
			foreach ( $categories as $category ) {
				$names[] = aioseo()->helpers->internationalize( $category->cat_name );
			}
		}
		return $names;
	}

	/**
	 * Returns all tags for a post.
	 *
	 * @since 4.1.4
	 *
	 * @param  int   $postId The post ID.
	 * @return array $names  The tag names.
	 */
	public function getAllTags( $postId = 0 ) {
		$names = [];

		$tags = get_the_tags( $postId );
		if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
			foreach ( $tags as $tag ) {
				if ( ! empty( $tag->name ) ) {
					$names[] = aioseo()->helpers->internationalize( $tag->name );
				}
			}
		}
		return $names;
	}

	/**
	 * Loads the translations for a given domain.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	public function loadTextDomain( $domain ) {
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Unload the domain in case WordPress has enqueued the translations for the site language instead of profile language.
		// Reloading the text domain will otherwise not override the existing loaded translations.
		unload_textdomain( $domain );

		$mofile = $domain . '-' . get_user_locale() . '.mo';
		load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile );
	}
}