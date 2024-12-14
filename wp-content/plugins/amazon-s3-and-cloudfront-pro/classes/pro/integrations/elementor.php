<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Integrations;

use DeliciousBrains\WP_Offload_Media\Integrations\Integration;
use Elementor\Core\Files\CSS\Post;
use Elementor\Element_Base;
use Elementor\Plugin;

class Elementor extends Integration {
	/**
	 * Keep track update_metadata recursion level
	 *
	 * @var int
	 */
	private $recursion_level = 0;

	/**
	 * Is Elementor installed?
	 *
	 * @return bool
	 */
	public static function is_installed(): bool {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Init integration.
	 */
	public function init() {
		// Nothing to do.
	}

	/**
	 * @inheritDoc
	 */
	public function setup() {
		add_filter( 'elementor/editor/localize_settings', array( $this, 'localize_settings' ) );
		add_action( 'elementor/frontend/before_render', array( $this, 'frontend_before_render' ) );
		add_filter( 'update_post_metadata', array( $this, 'update_post_metadata' ), 10, 5 );
		add_action( 'wp_print_styles', array( $this, 'wp_print_styles' ) );

		if ( isset( $_REQUEST['action'] ) && 'elementor_ajax' === $_REQUEST['action'] ) {
			add_filter( 'widget_update_callback', array( $this, 'widget_update_callback' ), 10, 2 );
		}

		// Hooks to clear Elementor cache after OME bulk actions
		add_action( 'as3cf_copy_buckets_cancelled', array( $this, 'clear_elementor_css_cache' ) );
		add_action( 'as3cf_copy_buckets_completed', array( $this, 'clear_elementor_css_cache' ) );
		add_action( 'as3cf_download_and_remover_cancelled', array( $this, 'clear_elementor_css_cache' ) );
		add_action( 'as3cf_download_and_remover_completed', array( $this, 'clear_elementor_css_cache' ) );
		add_action( 'as3cf_move_objects_cancelled', array( $this, 'clear_elementor_css_cache' ) );
		add_action( 'as3cf_move_objects_completed', array( $this, 'clear_elementor_css_cache' ) );
		add_action( 'as3cf_move_private_objects_cancelled', array( $this, 'clear_elementor_css_cache' ) );
		add_action( 'as3cf_move_private_objects_completed', array( $this, 'clear_elementor_css_cache' ) );
		add_action( 'as3cf_move_public_objects_cancelled', array( $this, 'clear_elementor_css_cache' ) );
		add_action( 'as3cf_move_public_objects_completed', array( $this, 'clear_elementor_css_cache' ) );
		add_action( 'as3cf_uploader_cancelled', array( $this, 'clear_elementor_css_cache' ) );
		add_action( 'as3cf_uploader_completed', array( $this, 'clear_elementor_css_cache' ) );
		add_action( 'as3cf_elementor_analyze_and_repair_cancelled', array( $this, 'clear_elementor_css_cache' ) );
		add_action( 'as3cf_elementor_analyze_and_repair_completed', array( $this, 'clear_elementor_css_cache' ) );
	}

	/**
	 * Rewrite media library URLs from local to remote when settings are read from
	 * database.
	 *
	 * @param object $config
	 *
	 * @return object
	 *
	 * @handles elementor/editor/localize_settings
	 */
	public function localize_settings( $config ) {
		if ( ! is_array( $config ) || ! isset( $config['initial_document'] ) ) {
			return $config;
		}

		if ( ! is_array( $config['initial_document'] ) || ! isset( $config['initial_document']['elements'] ) ) {
			return $config;
		}

		$filtered = json_decode(
			$this->as3cf->filter_local->filter_post( json_encode( $config['initial_document']['elements'], JSON_UNESCAPED_SLASHES ) ),
			true
		);

		// Avoid replacing content if the filtering failed
		if ( false !== $filtered ) {
			$config['initial_document']['elements'] = $filtered;
		}

		return $config;
	}

	/**
	 * Replace local URLs in settings that Elementor renders in HTML for some attributes, i.e json structs for
	 * the section background slideshow
	 *
	 * @param Element_Base $element
	 *
	 * @handles elementor/frontend/before_render
	 */
	public function frontend_before_render( $element ) {
		$element->set_settings(
			json_decode(
				$this->as3cf->filter_local->filter_post( json_encode( $element->get_settings(), JSON_UNESCAPED_SLASHES ) ),
				true
			)
		);
	}

	/**
	 * Handle Elementor's call to update_metadata() for _elementor_data when saving
	 * a post or page. Rewrites remote URLs to local.
	 *
	 * @param bool   $check
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param mixed  $meta_value
	 * @param mixed  $prev_value
	 *
	 * @handles update_post_metadata
	 *
	 * @return bool
	 */
	public function update_post_metadata( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		if ( '_elementor_css' === $meta_key ) {
			$this->rewrite_css( $object_id, $meta_value );

			return $check;
		}

		if ( '_elementor_data' !== $meta_key ) {
			return $check;
		}

		// We're calling update_metadata recursively and need to make sure
		// we never nest deeper than one level.
		if ( 0 === $this->recursion_level ) {
			$this->recursion_level++;

			// We get here from an update_metadata() call that has already done some string sanitizing
			// including wp_unslash(), but the original json from Elementor still needs slashes
			// removed for our filters to work.
			// Note: wp_unslash can't be used because it also unescapes any embedded HTML.
			$json       = str_replace( '\/', '/', $meta_value );
			$json       = $this->as3cf->filter_provider->filter_post( $json );
			$meta_value = wp_slash( str_replace( '/', '\/', $json ) );
			update_metadata( 'post', $object_id, '_elementor_data', $meta_value, $prev_value );

			// Reset recursion level and let update_metadata we already handled saving the meta
			$this->recursion_level = 0;

			return true;
		}

		return $check;
	}

	/**
	 * Rewrites local URLs in generated CSS files
	 *
	 * @param int   $object_id
	 * @param array $meta_value
	 */
	private function rewrite_css( $object_id, $meta_value ) {
		if ( 'file' === $meta_value['status'] ) {
			$elementor_css = new Post( $object_id );
			$file          = Post::get_base_uploads_dir() . Post::DEFAULT_FILES_DIR . $elementor_css->get_file_name();

			if ( file_exists( $file ) ) {
				$old_content = file_get_contents( $file );
				if ( ! empty( $old_content ) ) {
					file_put_contents(
						$file,
						$this->as3cf->filter_local->filter_post( $old_content )
					);
				}
			}
		}
	}

	/**
	 * Some widgets, specifically any standard WordPress widgets, make ajax requests back to the server
	 * before the edited section gets rendered in the Elementor editor. When they do, Elementor picks up
	 * properties directly from the saved post meta.
	 *
	 * @param array $instance
	 * @param array $new_instance
	 *
	 * @handles widget_update_callback
	 *
	 * @return mixed
	 */
	public function widget_update_callback( $instance, $new_instance ) {
		return json_decode(
			$this->as3cf->filter_local->filter_post( json_encode( $instance, JSON_UNESCAPED_SLASHES ) ),
			true
		);
	}

	/**
	 * Clears the Elementor cache
	 *
	 * @handles Multiple as3cf_*_completed and as3cf_*_cancelled actions
	 */
	public function clear_elementor_css_cache() {
		if ( class_exists( '\Elementor\Plugin' ) ) {
			Plugin::instance()->files_manager->clear_cache();
		}
	}

	/**
	 * Rewrite URLs in Elementor frontend inline CSS before it's rendered/printed by WordPress
	 *
	 * @implements wp_print_styles
	 */
	public function wp_print_styles() {
		$wp_styles = wp_styles();
		if ( empty( $wp_styles->registered['elementor-frontend']->extra['after'] ) ) {
			return;
		}

		foreach ( $wp_styles->registered['elementor-frontend']->extra['after'] as &$extra_css ) {
			$filtered_css = $this->as3cf->filter_local->filter_post( $extra_css );
			if ( ! empty( $filtered_css ) ) {
				$extra_css = $filtered_css;
			}
		}
	}
}
