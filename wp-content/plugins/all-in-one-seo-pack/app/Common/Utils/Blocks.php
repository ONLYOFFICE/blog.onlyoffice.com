<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block helpers.
 *
 * @since 4.1.1
 */
class Blocks {
	/**
	 * The block slugs.
	 *
	 * @since 4.9.0
	 *
	 * @var array
	 */
	private $blockSlugs = [];

	/**
	 * Frontend CSS asset paths collected during block registration (global.scss).
	 * Registered on `enqueue_block_assets` so they are available on both
	 * frontend and editor, since `style` handles are used in both contexts.
	 *
	 * @since 5.0.0
	 *
	 * @var array<string, true>
	 */
	protected $deferredFrontendCssAssets = [];

	/**
	 * Editor-only CSS asset paths collected during block registration
	 * (editor.scss and CSS chunks imported by the block's JS bundle).
	 * Registered on `enqueue_block_editor_assets` to skip registration on the frontend.
	 *
	 * @since 5.0.0
	 *
	 * @var array<string, true>
	 */
	protected $deferredEditorCssAssets = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.1
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initializes our blocks.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'registerBlockEditorAssets' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'registerDeferredEditorCssAssets' ] );
		add_action( 'enqueue_block_assets', [ $this, 'registerDeferredFrontendCssAssets' ] );
	}

	/**
	 * Returns the registered block name for a block slug.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $slug The block slug as passed to {@see registerBlock()}.
	 * @return string       The block name, e.g. `aioseo/product`.
	 */
	public function getBlockName( $slug ) {
		$blockName = str_replace( 'pro/', '', $slug );

		return strpos( $blockName, '/' ) ? $blockName : 'aioseo/' . $blockName;
	}

	/**
	 * Returns the class name the Block Editor generates for a block.
	 *
	 * Blocks that render through PHP have no saved markup for the editor to add this class to,
	 * so their render callbacks have to output it themselves to match the editor markup.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $slug The block slug as passed to {@see registerBlock()}.
	 * @return string       The generated class name, e.g. `wp-block-aioseo-product`.
	 */
	public function getBlockDefaultClassName( $slug ) {
		return 'wp-block-' . str_replace( '/', '-', $this->getBlockName( $slug ) );
	}

	/**
	 * Registers the block type with WordPress.
	 *
	 * @since 4.2.1
	 *
	 * @param  string               $slug The Block name.
	 * @param  array                $args Array of block type arguments with additional 'wp_min_version' arg.
	 * @return \WP_Block_Type|false       The registered block type on success, or false on failure.
	 */
	public function registerBlock( $slug = '', $args = [] ) {
		global $wp_version; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		$slugWithPrefix = $this->getBlockName( $slug );

		if ( ! $this->isBlockEditorActive() ) {
			return false;
		}

		// Check if the block requires a minimum WP version.
		if ( ! empty( $args['wp_min_version'] ) && version_compare( $wp_version, $args['wp_min_version'], '>' ) ) { // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			return false;
		}

		// Checking whether block is registered to ensure it isn't registered twice.
		if ( $this->isRegistered( $slugWithPrefix ) ) {
			return false;
		}

		// Store the block slugs so we can enqueue the global & editor assets later on.
		// We can't do this here because the built-in functions from WP will throw notices due to things running too soon.
		$this->blockSlugs[] = $slug;

		// Check if the block has global or editor assets. If so, we'll need to enqueue them later on.
		$editorScript    = "src/vue/standalone/blocks/{$slug}/editor-script.js";
		$hasEditorScript = aioseo()->core->assets->assetExists( $editorScript );

		$editorStyle     = "src/vue/standalone/blocks/{$slug}/editor.scss";
		$hasEditorStyle  = aioseo()->core->assets->assetExists( $editorStyle );

		$globalStyle     = "src/vue/standalone/blocks/{$slug}/global.scss";
		$hasGlobalStyle  = aioseo()->core->assets->assetExists( $globalStyle );

		// Defer CSS registration to a proper enqueue hook. We only compute handle names
		// here (pure string concatenation) and pass them to register_block_type().
		// WordPress looks up the handles later when the block is enqueued.
		// Frontend (global.scss) and editor-only (editor.scss + JS-chunk CSS) are
		// kept in separate buckets so we don't register editor-only assets on the frontend.
		if ( $hasGlobalStyle ) {
			$this->deferredFrontendCssAssets[ $globalStyle ] = true;
		}

		$editorStyleHandles = [];
		if ( $hasEditorStyle ) {
			$this->deferredEditorCssAssets[ $editorStyle ] = true;
			$editorStyleHandles[]                          = aioseo()->core->assets->cssHandle( $editorStyle );
		}

		// Collect CSS handles from the block JS bundle and its imported chunks so WordPress
		// includes them in the editor iframe via the editor_style_handles parameter.
		$mainJsx            = "src/vue/standalone/blocks/{$slug}/main.jsx";
		$editorStyleHandles = array_merge( $editorStyleHandles, $this->collectManifestCssHandles( $mainJsx, aioseo()->core->assets ) );
		$editorStyleHandles = array_unique( $editorStyleHandles );

		$styleDefault = empty( $args['style'] ) ? '' : $args['style'];

		$defaults = [
			'api_version'     => 3,
			'attributes'      => [],
			'editor_script'   => $hasEditorScript ? aioseo()->core->assets->jsHandle( $editorScript ) : '',
			'render_callback' => null,
			'style'           => $hasGlobalStyle ? aioseo()->core->assets->cssHandle( $globalStyle ) : $styleDefault,
			'supports'        => []
		];

		// The singular `editor_style` holds one handle, so an array of them leaks into
		// WP 6.0's _wp_get_iframed_editor_assets(), which flattens the list through
		// array_unique() and throws an "Array to string conversion" notice on every editor screen.
		// Versions without the plural property enqueue these handles directly instead,
		// {@see \AIOSEO\Plugin\Common\Utils\Blocks::registerDeferredEditorCssAssets()}.
		if ( $this->supportsAssetHandles() ) {
			// Assigning the property directly skips the array_values() that WP_Block_Type::__set()
			// applies, and the gaps array_unique() leaves would serialize as a JSON object.
			$defaults['editor_style_handles'] = array_values( $editorStyleHandles );
		}

		$args = wp_parse_args( $args, $defaults );

		return register_block_type( $slugWithPrefix, $args );
	}

	/**
	 * Registers Block Editor assets.
	 *
	 * @since 4.2.1
	 *
	 * @return void
	 */
	public function registerBlockEditorAssets() {
		$postSettingJsAsset = 'src/vue/standalone/post-settings/main.js';
		if (
			aioseo()->helpers->isScreenBase( 'widgets' ) ||
			aioseo()->helpers->isScreenBase( 'customize' )
		) {
			/**
			 * Make sure the post settings JS asset is registered before adding it as a dependency below.
			 * This is needed because this asset is not loaded on widgets and customizer screens,
			 * {@see \AIOSEO\Plugin\Common\Admin\PostSettings::enqueuePostSettingsAssets}.
			 *

			 */
			aioseo()->core->assets->load( $postSettingJsAsset, [], aioseo()->helpers->getVueData() );
		}

		aioseo()->core->assets->loadCss( 'src/vue/standalone/blocks/schema.js' );

		$dependencies = [
			'wp-rich-text',
			'wp-block-editor',
			'wp-blocks',
			'wp-components',
			'wp-element',
			'wp-i18n',
			'wp-data',
			'wp-url',
			'wp-polyfill',
			aioseo()->core->assets->jsHandle( $postSettingJsAsset )
		];

		aioseo()->core->assets->enqueueJs( 'src/vue/standalone/blocks/schema.js', $dependencies );

		foreach ( $this->blockSlugs as $slug ) {
			aioseo()->core->assets->enqueueJs( "src/vue/standalone/blocks/{$slug}/main.jsx", $dependencies );
		}
	}

	/**
	 * Traverses the Vite manifest for a JS asset and collects CSS handles
	 * (both direct and from imported chunks). CSS files are queued for deferred
	 * registration on the editor enqueue hook, since they originate from the
	 * editor's JS bundle and are not needed on the frontend.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $asset  The manifest entry key (e.g. "src/vue/standalone/blocks/faq/main.jsx").
	 * @param  Assets $assets The assets service instance.
	 * @return array          An array of CSS handles.
	 */
	protected function collectManifestCssHandles( $asset, $assets ) {
		$handles  = [];
		$manifest = $assets->getAssetManifestItem( $asset );

		if ( empty( $manifest ) ) {
			return $handles;
		}

		if ( ! empty( $manifest['css'] ) ) {
			foreach ( $manifest['css'] as $cssFile ) {
				$this->deferredEditorCssAssets[ $cssFile ] = true;
				$handles[]                                 = $assets->cssHandle( $cssFile );
			}
		}

		if ( ! empty( $manifest['imports'] ) ) {
			foreach ( $manifest['imports'] as $import ) {
				$handles = array_merge( $handles, $this->collectManifestCssHandles( $import, $assets ) );
			}
		}

		return $handles;
	}

	/**
	 * Registers frontend CSS assets (global.scss) collected during block registration.
	 * Hooked to `enqueue_block_assets` so the handles are available on both
	 * frontend and editor — WordPress needs them registered in both contexts
	 * because the `style` parameter on register_block_type() is used in both.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function registerDeferredFrontendCssAssets() {
		foreach ( array_keys( $this->deferredFrontendCssAssets ) as $cssFile ) {
			aioseo()->core->assets->registerCss( $cssFile );
		}
	}

	/**
	 * Registers editor-only CSS assets collected during block registration.
	 * Hooked to `enqueue_block_editor_assets` to avoid registering editor-only
	 * handles on the frontend.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function registerDeferredEditorCssAssets() {
		// Without the plural property the handles never reach the block type, so WordPress
		// has nothing to enqueue and we have to do it ourselves.
		$supportsAssetHandles = $this->supportsAssetHandles();

		foreach ( array_keys( $this->deferredEditorCssAssets ) as $cssFile ) {
			if ( $supportsAssetHandles ) {
				aioseo()->core->assets->registerCss( $cssFile );

				continue;
			}

			aioseo()->core->assets->enqueueCss( $cssFile );
		}
	}

	/**
	 * Checks whether WordPress supports the plural block asset handle properties (WP 6.1+).
	 *
	 * @since 5.0.0
	 *
	 * @return bool Whether the plural block asset handle properties are supported.
	 */
	protected function supportsAssetHandles() {
		return property_exists( 'WP_Block_Type', 'editor_style_handles' );
	}

	/**
	 * Check if a block is already registered.
	 *
	 * @since 4.2.1
	 *
	 * @param string $slug Name of block to check.
	 *
	 * @return bool
	 */
	public function isRegistered( $slug ) {
		if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
			return false;
		}

		return \WP_Block_Type_Registry::get_instance()->is_registered( $slug );
	}

	/**
	 * Helper function to determine if we're rendering the block inside Gutenberg.
	 *
	 * @since 4.1.1
	 *
	 * @return bool In gutenberg.
	 */
	public function isRenderingBlockInEditor() {
		// phpcs:disable HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
		if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) {
			return false;
		}

		$context = isset( $_REQUEST['context'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['context'] ) ) : '';
		// phpcs:enable HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended

		return 'edit' === $context;
	}

	/**
	 * Helper function to determine if we can register blocks.
	 *
	 * @since 4.1.1
	 *
	 * @return bool Can register block.
	 */
	public function isBlockEditorActive() {
		return function_exists( 'register_block_type' );
	}
}