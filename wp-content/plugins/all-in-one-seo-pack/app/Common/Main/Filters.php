<?php
namespace AIOSEO\Plugin\Common\Main;

use AIOSEO\Plugin\Common\Models as Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
abstract class Filters {
	/**
	 * The plugin we are checking.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $plugin;

	/**
	 * Construct method.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_filter( 'plugin_row_meta', [ $this, 'pluginRowMeta' ], 10, 2 );
		add_filter( 'plugin_action_links_' . AIOSEO_PLUGIN_BASENAME, [ $this, 'pluginActionLinks' ], 10, 2 );

		// Genesis theme compatibility.
		add_filter( 'genesis_detect_seo_plugins', [ $this, 'genesisTheme' ] );

		// WeGlot compatibility.
		if ( preg_match( '#(/default\.xsl)$#i', $_SERVER['REQUEST_URI'] ) ) {
			add_filter( 'weglot_active_translation_before_treat_page', '__return_false' );
		}

		// GoDaddy CDN compatibility.
		add_filter( 'wpaas_cdn_file_ext', [ $this, 'goDaddySitemapXml' ] );

		// Duplicate Post integration.
		add_action( 'dp_duplicate_post', [ $this, 'duplicatePostIntegration' ], 10, 3 );
		add_action( 'dp_duplicate_page', [ $this, 'duplicatePostIntegration' ], 10, 3 );

		// Classic Editor emoji
		add_action( 'init', [ $this, 'removeEmojiScript' ] );
	}

	/**
	 * Duplicates the model when duplicate post is triggered.
	 *
	 * @since 4.1.1
	 *
	 * @param  integer $newPostId    The new post ID.
	 * @param  WP_Post $originalPost The original post object.
	 * @param  string  $status       The status of the post.
	 * @return void
	 */
	public function duplicatePostIntegration( $newPostId, $originalPost, $status ) {
		$originalAioseoPost = Models\Post::getPost( $originalPost->ID );
		if ( ! $originalAioseoPost->exists() ) {
			return;
		}

		$newPost = Models\Post::getPost( $newPostId );
		if ( $newPost->exists() ) {
			return;
		}

		$columns = $originalAioseoPost->getColumns();
		foreach ( $columns as $column => $value ) {
			// Skip the ID column.
			if ( 'id' === $column ) {
				continue;
			}

			if ( 'post_id' === $column ) {
				$newPost->$column = $newPostId;
				continue;
			}

			$newPost->$column = $originalAioseoPost->$column;
		}
		$newPost->save();
	}

	/**
	 * Disable SEO inside the Genesis theme if it's running.
	 *
	 * @since 4.0.3
	 *
	 * @param  array $array An array of checks.
	 * @return array        An array with our function added.
	 */
	public function genesisTheme( $array ) {
		if ( empty( $array ) || ! isset( $array['functions'] ) ) {
			return $array;
		}

		$array['functions'][] = 'aioseo';

		return $array;
	}

	/**
	 * Remove XML from the GoDaddy CDN so our urls remain intact.
	 *
	 * @since 4.0.5
	 *
	 * @param  array $extensions The original extensions list.
	 * @return array             The extensions list without xml.
	 */
	public function goDaddySitemapXml( $extensions ) {
		$key = array_search( 'xml', $extensions, true );
		unset( $extensions[ $key ] );
		return $extensions;
	}

	/**
	 * Action links for the plugins page.
	 *
	 * @since 4.0.0
	 *
	 * @return array The array of actions.
	 */
	abstract public function pluginRowMeta( $actions, $pluginFile );

	/**
	 * Action links for the plugins page.
	 *
	 * @since 4.0.0
	 *
	 * @return array The array of actions.
	 */
	abstract public function pluginActionLinks( $actions, $pluginFile );

	/**
	 * Parse the action links.
	 *
	 * @since 4.0.0
	 *
	 * @param  array  $actions
	 * @param  string $pluginFile
	 * @param
	 * @return array
	 */
	protected function parseActionLinks( $actions, $pluginFile, $actionLinks = [], $position = 'after' ) {
		if ( empty( $this->plugin ) ) {
			$this->plugin = AIOSEO_PLUGIN_BASENAME;
		}

		if ( $this->plugin === $pluginFile && ! empty( $actionLinks ) ) {
			foreach ( $actionLinks as $key => $value ) {
				$link = [
					$key => '<a href="' . $value['url'] . '">' . $value['label'] . '</a>'
				];

				$actions = 'after' === $position ? array_merge( $actions, $link ) : array_merge( $link, $actions );
			}
		}
		return $actions;
	}

	/**
	 * Prevents the Classic Editor from enqueuing a script that breaks emoji in our metabox.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function removeEmojiScript() {
		if ( apply_filters( 'aioseo_classic_editor_disable_emoji_script', false ) ) {
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		}
	}
}