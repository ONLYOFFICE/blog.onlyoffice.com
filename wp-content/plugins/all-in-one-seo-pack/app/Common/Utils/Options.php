<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;
use AIOSEO\Plugin\Common\Traits;

/**
 * Class that holds all options for AIOSEO.
 *
 * @since 4.0.0
 */
class Options {
	use Traits\Options;

	/**
	 * Whether the options need to be updated.
	 *
	 * @since 4.1.1
	 *
	 * @var bool
	 */
	protected $needsUpdate = false;

	/**
	 * All the default options.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $defaults = [
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		'internal'         => [
			'searchAppearanceDynamicBackup' => [
				'postTypes'  => [ 'type' => 'array', 'default' => [] ],
				'taxonomies' => [ 'type' => 'array', 'default' => [] ]
			],
			'socialFacebookDynamicBackup'   => [
				'postTypes'  => [ 'type' => 'array', 'default' => [] ],
				'taxonomies' => [ 'type' => 'array', 'default' => [] ]
			],
		],
		'webmasterTools'   => [
			'google'                    => [ 'type' => 'string' ],
			'bing'                      => [ 'type' => 'string' ],
			'yandex'                    => [ 'type' => 'string' ],
			'baidu'                     => [ 'type' => 'string' ],
			'pinterest'                 => [ 'type' => 'string' ],
			'alexa'                     => [ 'type' => 'string' ],
			'norton'                    => [ 'type' => 'string' ],
			'miscellaneousVerification' => [ 'type' => 'html' ]
		],
		'breadcrumbs'      => [
			'enable'                => [ 'type' => 'boolean', 'default' => true ],
			'separator'             => [ 'type' => 'string', 'default' => '&raquo;' ],
			'homepageLink'          => [ 'type' => 'boolean', 'default' => true ],
			'homepageLabel'         => [ 'type' => 'string', 'default' => 'Home' ],
			'breadcrumbPrefix'      => [ 'type' => 'string' ],
			'archiveFormat'         => [ 'type' => 'string', 'default' => 'Archives for #breadcrumb_archive_post_type_name' ],
			'searchResultFormat'    => [ 'type' => 'string', 'default' => 'Search for \'#breadcrumb_search_string\'' ],
			'errorFormat404'        => [ 'type' => 'string', 'default' => '404 Error: page not found' ],
			'showCurrentItem'       => [ 'type' => 'boolean', 'default' => true ],
			'linkCurrentItem'       => [ 'type' => 'boolean', 'default' => false ],
			'categoryFullHierarchy' => [ 'type' => 'boolean', 'default' => false ],
			'showBlogHome'          => [ 'type' => 'boolean', 'default' => false ]
		],
		'rssContent'       => [
			'before' => [ 'type' => 'html' ],
			'after'  => [
				'type'    => 'html',
				'default' => <<<TEMPLATE
&lt;p&gt;The post #post_link first appeared on #site_link.&lt;/p&gt;
TEMPLATE
			]
		],
		'advanced'         => [
			'truSeo'          => [ 'type' => 'boolean', 'default' => true ],
			'seoAnalysis'     => [ 'type' => 'boolean', 'default' => true ],
			'dashboardWidget' => [ 'type' => 'boolean', 'default' => true ],
			'announcements'   => [ 'type' => 'boolean', 'default' => true ],
			'postTypes'       => [
				'all'      => [ 'type' => 'boolean', 'default' => true ],
				'included' => [ 'type' => 'array', 'default' => [ 'post', 'page', 'product' ] ],
			],
			'taxonomies'      => [
				'all'      => [ 'type' => 'boolean', 'default' => true ],
				'included' => [ 'type' => 'array', 'default' => [ 'category', 'post_tag', 'product_cat', 'product_tag' ] ],
			],
			'uninstall'       => [ 'type' => 'boolean', 'default' => false ]
		],
		'sitemap'          => [
			'general' => [
				'enable'           => [ 'type' => 'boolean', 'default' => true ],
				'filename'         => [ 'type' => 'string', 'default' => 'sitemap' ],
				'indexes'          => [ 'type' => 'boolean', 'default' => true ],
				'linksPerIndex'    => [ 'type' => 'number', 'default' => 1000 ],
				// @TODO: [V4+] Convert this to the dynamic options like in search appearance so we can have backups when plugins are deactivated.
				'postTypes'        => [
					'all'      => [ 'type' => 'boolean', 'default' => true ],
					'included' => [ 'type' => 'array', 'default' => [ 'post', 'page', 'attachment', 'product' ] ],
				],
				// @TODO: [V4+] Convert this to the dynamic options like in search appearance so we can have backups when plugins are deactivated.
				'taxonomies'       => [
					'all'      => [ 'type' => 'boolean', 'default' => true ],
					'included' => [ 'type' => 'array', 'default' => [ 'category', 'post_tag', 'product_cat', 'product_tag' ] ],
				],
				'author'           => [ 'type' => 'boolean', 'default' => false ],
				'date'             => [ 'type' => 'boolean', 'default' => false ],
				'additionalPages'  => [
					'enable' => [ 'type' => 'boolean', 'default' => false ],
					'pages'  => [ 'type' => 'array', 'default' => [] ]
				],
				'advancedSettings' => [
					'enable'        => [ 'type' => 'boolean', 'default' => false ],
					'excludeImages' => [ 'type' => 'boolean', 'default' => false ],
					'excludePosts'  => [ 'type' => 'array', 'default' => [] ],
					'excludeTerms'  => [ 'type' => 'array', 'default' => [] ],
					'priority'      => [
						'homePage'   => [
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						],
						'postTypes'  => [
							'grouped'   => [ 'type' => 'boolean', 'default' => true ],
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						],
						'taxonomies' => [
							'grouped'   => [ 'type' => 'boolean', 'default' => true ],
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						],
						'archive'    => [
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						],
						'author'     => [
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						]
					]
				]
			],
			'rss'     => [
				'enable'        => [ 'type' => 'boolean', 'default' => true ],
				'linksPerIndex' => [ 'type' => 'number', 'default' => 50 ],
				// @TODO: [V4+] Convert this to the dynamic options like in search appearance so we can have backups when plugins are deactivated.
				'postTypes'     => [
					'all'      => [ 'type' => 'boolean', 'default' => true ],
					'included' => [ 'type' => 'array', 'default' => [ 'post', 'page', 'product' ] ],
				]
			],
			'dynamic' => [
				'priority' => [
					'postTypes'  => [],
					'taxonomies' => []
				]
			]
		],
		'social'           => [
			'profiles'           => [
				'sameUsername' => [
					'enable'   => [ 'type' => 'boolean', 'default' => false ],
					'username' => [ 'type' => 'string' ],
					'included' => [ 'type' => 'array', 'default' => [ 'facebookPageUrl', 'twitterUrl', 'pinterestUrl', 'instagramUrl', 'youtubeUrl', 'linkedinUrl' ] ]
				],
				'urls'         => [
					'facebookPageUrl' => [ 'type' => 'string' ],
					'twitterUrl'      => [ 'type' => 'string' ],
					'instagramUrl'    => [ 'type' => 'string' ],
					'pinterestUrl'    => [ 'type' => 'string' ],
					'youtubeUrl'      => [ 'type' => 'string' ],
					'linkedinUrl'     => [ 'type' => 'string' ],
					'tumblrUrl'       => [ 'type' => 'string' ],
					'yelpPageUrl'     => [ 'type' => 'string' ],
					'soundCloudUrl'   => [ 'type' => 'string' ],
					'wikipediaUrl'    => [ 'type' => 'string' ],
					'myspaceUrl'      => [ 'type' => 'string' ],
					'googlePlacesUrl' => [ 'type' => 'string' ]
				]
			],
			'siteSocialProfiles' => [ 'type' => 'array' ],
			'facebook'           => [
				'general'  => [
					'enable'                  => [ 'type' => 'boolean', 'default' => true ],
					'defaultImageSourcePosts' => [ 'type' => 'string', 'default' => 'default' ],
					'customFieldImagePosts'   => [ 'type' => 'string' ],
					'defaultImagePosts'       => [ 'type' => 'string', 'default' => '' ],
					'defaultImagePostsWidth'  => [ 'type' => 'number', 'default' => '' ],
					'defaultImagePostsHeight' => [ 'type' => 'number', 'default' => '' ],
					'showAuthor'              => [ 'type' => 'boolean', 'default' => true ],
					'siteName'                => [ 'type' => 'string', 'localized' => true, 'default' => '#site_title #separator_sa #tagline' ],
					'dynamic'                 => [
						'postTypes'  => [],
						'taxonomies' => [],
						'archives'   => []
					]
				],
				'homePage' => [
					'image'       => [ 'type' => 'string', 'default' => '' ],
					'title'       => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'description' => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'imageWidth'  => [ 'type' => 'number', 'default' => '' ],
					'imageHeight' => [ 'type' => 'number', 'default' => '' ],
					'objectType'  => [ 'type' => 'string', 'default' => 'website' ]
				],
				'advanced' => [
					'enable'              => [ 'type' => 'boolean', 'default' => false ],
					'adminId'             => [ 'type' => 'string', 'default' => '' ],
					'appId'               => [ 'type' => 'string', 'default' => '' ],
					'authorUrl'           => [ 'type' => 'string', 'default' => '' ],
					'generateArticleTags' => [ 'type' => 'boolean', 'default' => false ],
					'useKeywordsInTags'   => [ 'type' => 'boolean', 'default' => true ],
					'useCategoriesInTags' => [ 'type' => 'boolean', 'default' => true ],
					'usePostTagsInTags'   => [ 'type' => 'boolean', 'default' => true ]
				]
			],
			'twitter'            => [
				'general'  => [
					'enable'                  => [ 'type' => 'boolean', 'default' => true ],
					'defaultCardType'         => [ 'type' => 'string', 'default' => 'summary' ],
					'defaultImageSourcePosts' => [ 'type' => 'string', 'default' => 'default' ],
					'customFieldImagePosts'   => [ 'type' => 'string' ],
					'defaultImagePosts'       => [ 'type' => 'string', 'default' => '' ],
					'showAuthor'              => [ 'type' => 'boolean', 'default' => true ],
					'additionalData'          => [ 'type' => 'boolean', 'default' => false ]
				],
				'homePage' => [
					'image'       => [ 'type' => 'string', 'default' => '' ],
					'title'       => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'description' => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'cardType'    => [ 'type' => 'string', 'default' => 'summary' ]
				],
			]
		],
		'searchAppearance' => [
			'global'   => [
				'separator'       => [ 'type' => 'string', 'default' => '&#45;' ],
				'siteTitle'       => [ 'type' => 'string', 'localized' => true, 'default' => '#site_title #separator_sa #tagline' ],
				'metaDescription' => [ 'type' => 'string', 'localized' => true, 'default' => '#tagline' ],
				'keywords'        => [ 'type' => 'string', 'localized' => true ],
				'schema'          => [
					'siteRepresents'    => [ 'type' => 'string', 'default' => 'organization' ],
					'person'            => [ 'type' => 'string' ],
					'organizationName'  => [ 'type' => 'string' ],
					'organizationLogo'  => [ 'type' => 'string' ],
					'personName'        => [ 'type' => 'string' ],
					'personLogo'        => [ 'type' => 'string' ],
					'phone'             => [ 'type' => 'string' ],
					'contactType'       => [ 'type' => 'string' ],
					'contactTypeManual' => [ 'type' => 'string' ]
				]
			],
			'advanced' => [
				'globalRobotsMeta'             => [
					'default'           => [ 'type' => 'boolean', 'default' => true ],
					'noindex'           => [ 'type' => 'boolean', 'default' => false ],
					'nofollow'          => [ 'type' => 'boolean', 'default' => false ],
					'noindexPaginated'  => [ 'type' => 'boolean', 'default' => true ],
					'nofollowPaginated' => [ 'type' => 'boolean', 'default' => true ],
					'noindexFeed'       => [ 'type' => 'boolean', 'default' => true ],
					'noarchive'         => [ 'type' => 'boolean', 'default' => false ],
					'noimageindex'      => [ 'type' => 'boolean', 'default' => false ],
					'notranslate'       => [ 'type' => 'boolean', 'default' => false ],
					'nosnippet'         => [ 'type' => 'boolean', 'default' => false ],
					'noodp'             => [ 'type' => 'boolean', 'default' => false ],
					'maxSnippet'        => [ 'type' => 'number', 'default' => -1 ],
					'maxVideoPreview'   => [ 'type' => 'number', 'default' => -1 ],
					'maxImagePreview'   => [ 'type' => 'string', 'default' => 'large' ]
				],
				'sitelinks'                    => [ 'type' => 'boolean', 'default' => true ],
				'noIndexEmptyCat'              => [ 'type' => 'boolean', 'default' => true ],
				'removeStopWords'              => [ 'type' => 'boolean', 'default' => false ],
				'noPaginationForCanonical'     => [ 'type' => 'boolean', 'default' => true ],
				'useKeywords'                  => [ 'type' => 'boolean', 'default' => false ],
				'keywordsLooking'              => [ 'type' => 'boolean', 'default' => true ],
				'useCategoriesForMetaKeywords' => [ 'type' => 'boolean', 'default' => false ],
				'useTagsForMetaKeywords'       => [ 'type' => 'boolean', 'default' => false ],
				'dynamicallyGenerateKeywords'  => [ 'type' => 'boolean', 'default' => false ],
				'pagedFormat'                  => [ 'type' => 'string', 'default' => '- Page #page_number' ]
			],
			'archives' => [
				'author' => [
					'show'            => [ 'type' => 'boolean', 'default' => true ],
					'title'           => [ 'type' => 'string', 'localized' => true, 'default' => '#author_name #separator_sa #site_title' ],
					'metaDescription' => [ 'type' => 'string', 'localized' => true, 'default' => '#author_bio' ],
					'advanced'        => [
						'robotsMeta'                => [
							'default'         => [ 'type' => 'boolean', 'default' => true ],
							'noindex'         => [ 'type' => 'boolean', 'default' => false ],
							'nofollow'        => [ 'type' => 'boolean', 'default' => false ],
							'noarchive'       => [ 'type' => 'boolean', 'default' => false ],
							'noimageindex'    => [ 'type' => 'boolean', 'default' => false ],
							'notranslate'     => [ 'type' => 'boolean', 'default' => false ],
							'nosnippet'       => [ 'type' => 'boolean', 'default' => false ],
							'noodp'           => [ 'type' => 'boolean', 'default' => false ],
							'maxSnippet'      => [ 'type' => 'number', 'default' => -1 ],
							'maxVideoPreview' => [ 'type' => 'number', 'default' => -1 ],
							'maxImagePreview' => [ 'type' => 'string', 'default' => 'large' ]
						],
						'showDateInGooglePreview'   => [ 'type' => 'boolean', 'default' => true ],
						'showPostThumbnailInSearch' => [ 'type' => 'boolean', 'default' => true ],
						'showMetaBox'               => [ 'type' => 'boolean', 'default' => true ],
						'keywords'                  => [ 'type' => 'string', 'localized' => true ]
					]
				],
				'date'   => [
					'show'            => [ 'type' => 'boolean', 'default' => true ],
					'title'           => [ 'type' => 'string', 'localized' => true, 'default' => '#archive_date #separator_sa #site_title' ],
					'metaDescription' => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'advanced'        => [
						'robotsMeta'                => [
							'default'         => [ 'type' => 'boolean', 'default' => true ],
							'noindex'         => [ 'type' => 'boolean', 'default' => false ],
							'nofollow'        => [ 'type' => 'boolean', 'default' => false ],
							'noarchive'       => [ 'type' => 'boolean', 'default' => false ],
							'noimageindex'    => [ 'type' => 'boolean', 'default' => false ],
							'notranslate'     => [ 'type' => 'boolean', 'default' => false ],
							'nosnippet'       => [ 'type' => 'boolean', 'default' => false ],
							'noodp'           => [ 'type' => 'boolean', 'default' => false ],
							'maxSnippet'      => [ 'type' => 'number', 'default' => -1 ],
							'maxVideoPreview' => [ 'type' => 'number', 'default' => -1 ],
							'maxImagePreview' => [ 'type' => 'string', 'default' => 'large' ]
						],
						'showDateInGooglePreview'   => [ 'type' => 'boolean', 'default' => true ],
						'showPostThumbnailInSearch' => [ 'type' => 'boolean', 'default' => true ],
						'showMetaBox'               => [ 'type' => 'boolean', 'default' => true ],
						'keywords'                  => [ 'type' => 'string', 'localized' => true ]
					]
				],
				'search' => [
					'show'            => [ 'type' => 'boolean', 'default' => false ],
					'title'           => [ 'type' => 'string', 'localized' => true, 'default' => '#search_term #separator_sa #site_title' ],
					'metaDescription' => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'advanced'        => [
						'robotsMeta'                => [
							'default'         => [ 'type' => 'boolean', 'default' => false ],
							'noindex'         => [ 'type' => 'boolean', 'default' => true ],
							'nofollow'        => [ 'type' => 'boolean', 'default' => false ],
							'noarchive'       => [ 'type' => 'boolean', 'default' => false ],
							'noimageindex'    => [ 'type' => 'boolean', 'default' => false ],
							'notranslate'     => [ 'type' => 'boolean', 'default' => false ],
							'nosnippet'       => [ 'type' => 'boolean', 'default' => false ],
							'noodp'           => [ 'type' => 'boolean', 'default' => false ],
							'maxSnippet'      => [ 'type' => 'number', 'default' => -1 ],
							'maxVideoPreview' => [ 'type' => 'number', 'default' => -1 ],
							'maxImagePreview' => [ 'type' => 'string', 'default' => 'large' ]
						],
						'showDateInGooglePreview'   => [ 'type' => 'boolean', 'default' => true ],
						'showPostThumbnailInSearch' => [ 'type' => 'boolean', 'default' => true ],
						'showMetaBox'               => [ 'type' => 'boolean', 'default' => true ],
						'keywords'                  => [ 'type' => 'string', 'localized' => true ]
					]
				]
			],
			'dynamic'  => [
				'postTypes'  => [],
				'taxonomies' => [],
				'archives'   => []
			]
		],
		'tools'            => [
			'robots'       => [
				'enable'         => [ 'type' => 'boolean', 'default' => false ],
				'rules'          => [ 'type' => 'array', 'default' => [] ],
				'robotsDetected' => [ 'type' => 'boolean', 'default' => true ],
			],
			'importExport' => [
				'backup' => [
					'lastTime' => [ 'type' => 'string' ],
					'data'     => [ 'type' => 'string' ],
				]
			]
		],
		'deprecated'       => [
			'webmasterTools'   => [
				'googleAnalytics' => [
					'id'                        => [ 'type' => 'string' ],
					'advanced'                  => [ 'type' => 'boolean', 'default' => false ],
					'trackingDomain'            => [ 'type' => 'string' ],
					'multipleDomains'           => [ 'type' => 'boolean', 'default' => false ],
					'additionalDomains'         => [ 'type' => 'html' ],
					'anonymizeIp'               => [ 'type' => 'boolean', 'default' => false ],
					'displayAdvertiserTracking' => [ 'type' => 'boolean', 'default' => false ],
					'excludeUsers'              => [ 'type' => 'array', 'default' => [] ],
					'trackOutboundLinks'        => [ 'type' => 'boolean', 'default' => false ],
					'enhancedLinkAttribution'   => [ 'type' => 'boolean', 'default' => false ],
					'enhancedEcommerce'         => [ 'type' => 'boolean', 'default' => false ]
				]
			],
			'searchAppearance' => [
				'global'   => [
					'descriptionFormat' => [ 'type' => 'string' ],
					'schema'            => [
						'enableSchemaMarkup' => [ 'type' => 'boolean', 'default' => true ]
					]
				],
				'advanced' => [
					'autogenerateDescriptions'               => [ 'type' => 'boolean', 'default' => true ],
					'runShortcodesInDescription'             => [ 'type' => 'boolean', 'default' => true ],
					'useContentForAutogeneratedDescriptions' => [ 'type' => 'boolean', 'default' => false ],
					'excludePosts'                           => [ 'type' => 'array', 'default' => [] ],
					'excludeTerms'                           => [ 'type' => 'array', 'default' => [] ],
				]
			],
			'sitemap'          => [
				'general' => [
					'advancedSettings' => [
						'dynamic' => [ 'type' => 'boolean', 'default' => true ]
					]
				]
			],
			'tools'            => [
				'blocker' => [
					'blockBots'    => [ 'type' => 'boolean' ],
					'blockReferer' => [ 'type' => 'boolean' ],
					'track'        => [ 'type' => 'boolean' ],
					'custom'       => [
						'enable'  => [ 'type' => 'boolean' ],
						'bots'    => [ 'type' => 'html', 'default' => '' ],
						'referer' => [ 'type' => 'html', 'default' => '' ]
					]
				]
			]
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];

	/**
	 * The Construct method.
	 *
	 * @since 4.0.0
	 *
	 * @param string $optionsName An array of options.
	 */
	public function __construct( $optionsName = 'aioseo_options' ) {
		$this->optionsName = is_network_admin() ? $optionsName . '_network' : $optionsName;

		$this->init();
	}

	/**
	 * Initializes the options.
	 *
	 * @since 4.0.0
	 *
	 * @param  boolean $resetKeys Whether or not to reset keys after init.
	 * @return void
	 */
	protected function init( $resetKeys = false ) {
		if ( $resetKeys ) {
			$originalGroupKey  = $this->groupKey;
			$originalSubGroups = $this->subGroups;
		}

		$this->addDynamicDefaults();
		$this->translateDefaults();

		$options = $this->getDbOptions();

		$this->options = apply_filters( 'aioseo_get_options', $options );

		// Get the localized options.
		$dbOptionsLocalized = get_option( $this->optionsName . '_localized' );
		if ( empty( $dbOptionsLocalized ) ) {
			$dbOptionsLocalized = [];
		}
		$this->localized = $dbOptionsLocalized;

		if ( $resetKeys ) {
			$this->groupKey  = $originalGroupKey;
			$this->subGroups = $originalSubGroups;
		}

		add_action( 'wp_loaded', [ $this, 'maybeFlushRewriteRules' ] );
	}

	/**
	 * Get the DB options.
	 *
	 * @since 4.0.12
	 *
	 * @return array An array of options.
	 */
	public function getDbOptions() {
		// Options from the DB.
		$dbOptions = json_decode( get_option( $this->optionsName ), true );
		if ( empty( $dbOptions ) ) {
			$dbOptions = [];
		}

		// Refactor options.
		$this->defaultsMerged = array_replace_recursive( $this->defaults, $this->defaultsMerged );

		return array_replace_recursive(
			$this->defaultsMerged,
			$this->addValueToValuesArray( $this->defaultsMerged, $dbOptions )
		);
	}

	/**
	 * Adds some defaults that are dynamically generated.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addDynamicDefaults() {
		$this->addDynamicPostTypeDefaults();
		$this->addDynamicTaxonomyDefaults();
		$this->addDynamicArchiveDefaults();

		$this->defaults['searchAppearance']['global']['schema']['organizationName']['default'] = aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) );
		$this->defaults['deprecated']['tools']['blocker']['custom']['bots']['default']         = implode( "\n", aioseo()->badBotBlocker->getBotList() );
		$this->defaults['deprecated']['tools']['blocker']['custom']['referer']['default']      = implode( "\n", aioseo()->badBotBlocker->getRefererList() );

		if ( $this->needsUpdate ) {
			$this->update( $this->getDbOptions() );
		}
	}

	/**
	 * Adds the dynamic defaults for the public post types.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	protected function addDynamicPostTypeDefaults() {
		$postTypes = aioseo()->helpers->getPublicPostTypes();
		foreach ( $postTypes as $postType ) {
			if ( 'type' === $postType['name'] ) {
				$postType['name'] = '_aioseo_type';
			}

			$defaultTitle       = '#post_title #separator_sa #site_title';
			$defaultDescription = $postType['hasExcerpt'] ? '#post_excerpt' : '#post_content';
			$defaultSchemaType  = 'WebPage';
			$defaultWebPageType = 'WebPage';
			$defaultArticleType = 'BlogPosting';

			switch ( $postType['name'] ) {
				case 'post':
					$defaultSchemaType = 'Article';
					break;
				case 'attachment':
					$defaultDescription = '#attachment_caption';
					$defaultSchemaType  = 'ItemPage';
					$defaultWebPageType = 'ItemPage';
					break;
				case 'product':
					$defaultSchemaType  = 'WebPage';
					$defaultWebPageType = 'ItemPage';
					break;
				case 'news':
					$defaultArticleType = 'NewsArticle';
					break;
				default:
					break;
			}

			$defaultOptions = array_replace_recursive(
				$this->getDefaultSearchAppearanceOptions(),
				[
					'title'           => [
						'type'      => 'string',
						'localized' => true,
						'default'   => $defaultTitle
					],
					'metaDescription' => [
						'type'      => 'string',
						'localized' => true,
						'default'   => $defaultDescription
					],
					'schemaType'      => [
						'type'    => 'string',
						'default' => $defaultSchemaType
					],
					'webPageType'     => [
						'type'    => 'string',
						'default' => $defaultWebPageType
					],
					'articleType'     => [
						'type'    => 'string',
						'default' => $defaultArticleType
					],
					'customFields'    => [ 'type' => 'html' ],
					'advanced'        => [
						'bulkEditing' => [
							'type'    => 'string',
							'default' => 'enabled'
						]
					]
				]
			);

			if ( 'attachment' === $postType['name'] ) {
				$defaultOptions['redirectAttachmentUrls'] = [
					'type'    => 'string',
					'default' => 'attachment'
				];
			}

			$this->setDynamicSearchApperanceOptions( 'postTypes', $postType['name'], $defaultOptions );
			$this->setDynamicSocialOptions( 'postTypes', $postType['name'] );
			$this->setDynamicSitemapOptions( 'postTypes', $postType['name'] );
		}
	}

	/**
	 * Adds the dynamic defaults for the public taxonomies.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	protected function addDynamicTaxonomyDefaults() {
		$taxonomies = aioseo()->helpers->getPublicTaxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			if ( 'type' === $taxonomy['name'] ) {
				$taxonomy['name'] = '_aioseo_type';
			}

			$defaultOptions = array_replace_recursive(
				$this->getDefaultSearchAppearanceOptions(),
				[
					'title'           => [
						'type'      => 'string',
						'localized' => true,
						'default'   => '#taxonomy_title #separator_sa #site_title'
					],
					'metaDescription' => [
						'type'      => 'string',
						'localized' => true,
						'default'   => '#taxonomy_description'
					],
				]
			);

			$this->setDynamicSearchApperanceOptions( 'taxonomies', $taxonomy['name'], $defaultOptions );
			$this->setDynamicSocialOptions( 'taxonomies', $taxonomy['name'] );
			$this->setDynamicSitemapOptions( 'taxonomies', $taxonomy['name'] );
		}

	}

	/**
	 * Adds the dynamic defaults for the archive pages.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	protected function addDynamicArchiveDefaults() {
		$postTypes = aioseo()->helpers->getPublicPostTypes( false, true );
		foreach ( $postTypes as $postType ) {
			if ( 'type' === $postType['name'] ) {
				$postType['name'] = '_aioseo_type';
			}

			$defaultOptions = array_replace_recursive(
				$this->getDefaultSearchAppearanceOptions(),
				[
					'title'           => [
						'type'      => 'string',
						'localized' => true,
						'default'   => '#archive_title #separator_sa #site_title'
					],
					'metaDescription' => [
						'type'      => 'string',
						'localized' => true,
						'default'   => ''
					],
					'customFields'    => [ 'type' => 'html' ],
					'advanced'        => [
						'keywords' => [
							'type'      => 'string',
							'localized' => true
						]
					]
				]
			);

			$this->setDynamicSearchApperanceOptions( 'archives', $postType['name'], $defaultOptions );
			$this->setDynamicSocialOptions( 'archives', $postType['name'] );
		}
	}

	/**
	 * Returns the search appearance options for dynamic objects.
	 *
	 * @since 4.1.1
	 *
	 * @return array The default options.
	 */
	protected function getDefaultSearchAppearanceOptions() {
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		return [
			'show'     => [ 'type' => 'boolean', 'default' => true ],
			'advanced' => [
				'robotsMeta'                => [
					'default'         => [ 'type' => 'boolean', 'default' => true ],
					'noindex'         => [ 'type' => 'boolean', 'default' => false ],
					'nofollow'        => [ 'type' => 'boolean', 'default' => false ],
					'noarchive'       => [ 'type' => 'boolean', 'default' => false ],
					'noimageindex'    => [ 'type' => 'boolean', 'default' => false ],
					'notranslate'     => [ 'type' => 'boolean', 'default' => false ],
					'nosnippet'       => [ 'type' => 'boolean', 'default' => false ],
					'noodp'           => [ 'type' => 'boolean', 'default' => false ],
					'maxSnippet'      => [ 'type' => 'number', 'default' => -1 ],
					'maxVideoPreview' => [ 'type' => 'number', 'default' => -1 ],
					'maxImagePreview' => [ 'type' => 'string', 'default' => 'large' ]
				],
				'showDateInGooglePreview'   => [ 'type' => 'boolean', 'default' => true ],
				'showPostThumbnailInSearch' => [ 'type' => 'boolean', 'default' => true ],
				'showMetaBox'               => [ 'type' => 'boolean', 'default' => true ]
			]
		];
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	}

	/**
	 * Sets the dynamic search appearance settings for a given post type or taxonomy.
	 *
	 * @since 4.1.1
	 *
	 * @param  string $objectType     Whether the object belongs to the dynamic "postTypes" or "taxonomies".
	 * @param  string $objectName     The object name.
	 * @param  array  $defaultOptions The default options for the object.
	 * @return void
	 */
	protected function setDynamicSearchApperanceOptions( $objectType, $objectName, $defaultOptions ) {
		// Check if dynamic backup needs to be reset.
		if ( ! empty( $this->options['internal']['searchAppearanceDynamicBackup'][ $objectType ]['value'][ $objectName ] ) ) {
			$this->needsUpdate = true;

			$defaultOptions = array_replace_recursive( $defaultOptions, $this->options['internal']['searchAppearanceDynamicBackup'][ $objectType ]['value'][ $objectName ] );
			unset( $this->options['internal']['searchAppearanceDynamicBackup'][ $objectType ]['value'][ $objectName ] );
		}

		$this->defaults['searchAppearance']['dynamic'][ $objectType ][ $objectName ] = $defaultOptions;
	}

	/**
	 * Sets the dynamic social settings for a given post type or taxonomy.
	 *
	 * @since 4.1.1
	 *
	 * @param  string $objectType Whether the object belongs to the dynamic "postTypes" or "taxonomies".
	 * @param  string $objectName The object name.
	 * @return void
	 */
	protected function setDynamicSocialOptions( $objectType, $objectName ) {
		$defaultOptions = [
			'objectType' => [
				'type'    => 'string',
				'default' => 'article'
			]
		];

		// Check if dynamic backup needs to be reset.
		if ( ! empty( $this->options['internal']['socialFacebookDynamicBackup'][ $objectType ]['value'][ $objectName ] ) ) {
			$this->needsUpdate = true;

			$defaultOptions = array_replace_recursive( $defaultOptions, $this->options['internal']['socialFacebookDynamicBackup'][ $objectType ]['value'][ $objectName ] );
			unset( $this->options['internal']['socialFacebookDynamicBackup'][ $objectType ]['value'][ $objectName ] );
		}

		$this->defaults['social']['facebook']['general']['dynamic'][ $objectType ][ $objectName ] = $defaultOptions;
	}

	/**
	 * Sets the dynamic sitemap settings for a given post type or taxonomy.
	 *
	 * @since 4.1.1
	 *
	 * @param  string $objectType Whether the object belongs to the dynamic "postTypes" or "taxonomies".
	 * @param  string $objectName The object name.
	 * @return void
	 */
	protected function setDynamicSitemapOptions( $objectType, $objectName ) {
		$this->defaults['sitemap']['dynamic']['priority'][ $objectType ][ $objectName ] = [
			'priority'  => [
				'type'    => 'string',
				'default' => '{"label":"default","value":"default"}'
			],
			'frequency' => [
				'type'    => 'string',
				'default' => '{"label":"default","value":"default"}'
			]
		];
	}

	/**
	 * For our defaults array, some options need to be translated, so we do that here.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function translateDefaults() {
		$default = sprintf( '{"label":"%1$s","value":"default"}', __( 'default', 'all-in-one-seo-pack' ) );
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['homePage']['priority']['default']    = $default;
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['homePage']['frequency']['default']   = $default;
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['postTypes']['priority']['default']   = $default;
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['postTypes']['frequency']['default']  = $default;
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['taxonomies']['priority']['default']  = $default;
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['taxonomies']['frequency']['default'] = $default;

		$this->defaults['breadcrumbs']['homepageLabel']['default']      = __( 'Home', 'all-in-one-seo-pack' );
		$this->defaults['breadcrumbs']['archiveFormat']['default']      = sprintf( '%1$s #breadcrumb_archive_post_type_name', __( 'Archives for', 'all-in-one-seo-pack' ) );
		$this->defaults['breadcrumbs']['searchResultFormat']['default'] = sprintf( '%1$s \'#breadcrumb_search_string\'', __( 'Search for', 'all-in-one-seo-pack' ) );
		$this->defaults['breadcrumbs']['errorFormat404']['default']     = __( '404 Error: page not found', 'all-in-one-seo-pack' );

		$this->defaults['searchAppearance']['global']['schema']['organizationName']['default'] = aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) );
		$this->defaults['searchAppearance']['global']['schema']['organizationLogo']['default'] = aioseo()->helpers->getSiteLogoUrl() ? aioseo()->helpers->getSiteLogoUrl() : '';
	}

	/**
	 * Sanitizes, then saves the options to the database.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $options An array of options to sanitize, then save.
	 * @return void
	 */
	public function sanitizeAndSave( $options ) {
		$sitemapOptions           = ! empty( $options['sitemap'] ) && ! empty( $options['sitemap']['general'] ) ? $options['sitemap']['general'] : null;
		$oldSitemapOptions        = aioseo()->options->sitemap->general->all();
		$deprecatedSitemapOptions = ! empty( $options['deprecated']['sitemap']['general'] )
				? $options['deprecated']['sitemap']['general']
				: null;
		$oldDeprecatedSitemapOptions = aioseo()->options->deprecated->sitemap->general->all();
		$oldPhoneOption              = aioseo()->options->searchAppearance->global->schema->phone;
		$phoneNumberOptions          = isset( $options['searchAppearance']['global']['schema']['phone'] )
				? $options['searchAppearance']['global']['schema']['phone']
				: null;

		$options = $this->maybeRemoveUnfilteredHtmlFields( $options );

		$this->init();

		if ( ! is_array( $options ) ) {
			return;
		}

		// Get the settings from the options being passed in.
		$dynamicPostTypeSettings     = $this->options['searchAppearance']['dynamic']['postTypes'];
		$dynamicPostTypeSettingsOG   = $this->options['social']['facebook']['general']['dynamic']['postTypes'];
		$dynamicTaxonomiesSettings   = $this->options['searchAppearance']['dynamic']['taxonomies'];
		$dynamicTaxonomiesSettingsOG = $this->options['social']['facebook']['general']['dynamic']['taxonomies'];
		$dynamicArchiveSettings      = $this->options['searchAppearance']['dynamic']['archives'];

		// Refactor options.
		$this->options = array_replace_recursive(
			$this->options,
			$this->addValueToValuesArray( $this->options, $options, [], true )
		);

		// Post Types.
		$postTypes = aioseo()->helpers->getPublicPostTypes();
		foreach ( $dynamicPostTypeSettings as $postTypeName => $postTypeData ) {
			foreach ( $postTypes as $postType ) {
				if ( 'type' === $postType['name'] ) {
					$postType['name'] = '_aioseo_type';
				}

				// If the option has disappeared, we want to save the settings manually.
				if ( $postTypeName === $postType['name'] ) {
					continue 2;
				}
			}

			$this->options['internal']['searchAppearanceDynamicBackup']['postTypes']['value'][ $postTypeName ] = $postTypeData;
		}
		foreach ( $dynamicPostTypeSettingsOG as $postTypeName => $postTypeData ) {
			foreach ( $postTypes as $postType ) {
				if ( 'type' === $postType['name'] ) {
					$postType['name'] = '_aioseo_type';
				}

				// If the option has disappeared, we want to save the settings manually.
				if ( $postTypeName === $postType['name'] ) {
					continue 2;
				}
			}

			$this->options['internal']['socialFacebookDynamicBackup']['postTypes']['value'][ $postTypeName ] = $postTypeData;
		}

		// Taxonomies.
		$taxonomies = aioseo()->helpers->getPublicTaxonomies();
		foreach ( $dynamicTaxonomiesSettings as $taxonomyName => $taxonomyData ) {
			foreach ( $taxonomies as $taxonomy ) {
				if ( 'type' === $taxonomy['name'] ) {
					$taxonomy['name'] = '_aioseo_type';
				}

				// If the option has disappeared, we want to save the settings manually.
				if ( $taxonomyName === $taxonomy['name'] ) {
					continue 2;
				}
			}

			$this->options['internal']['searchAppearanceDynamicBackup']['taxonomies']['value'][ $taxonomyName ] = $taxonomyData;
		}
		foreach ( $dynamicTaxonomiesSettingsOG as $taxonomyName => $taxonomyData ) {
			foreach ( $taxonomies as $taxonomy ) {
				// If the option has disappeared, we want to save the settings manually.
				if ( $taxonomyName === $taxonomy['name'] ) {
					continue 2;
				}
			}

			$this->options['internal']['socialFacebookDynamicBackup']['taxonomies']['value'][ $taxonomyName ] = $taxonomyData;
		}

		// Archives.
		$postTypes = aioseo()->helpers->getPublicPostTypes( false, true );
		foreach ( $dynamicArchiveSettings as $archiveName => $archiveData ) {
			foreach ( $postTypes as $postType ) {
				if ( 'type' === $postType['name'] ) {
					$postType['name'] = '_aioseo_type';
				}

				// If the option has disappeared, we want to save the settings manually.
				if ( $archiveName === $postType['name'] ) {
					continue 2;
				}
			}

			$this->options['internal']['searchAppearanceDynamicBackup']['archives']['value'][ $postTypeName ] = $postTypeData;
		}

		// The above works for most options, but there are a few that need to be forcibly updated.
		if ( $sitemapOptions ) {
			$this->options['sitemap']['general']['postTypes']['included']['value']            = $this->sanitizeField( $options['sitemap']['general']['postTypes']['included'], 'array' );
			$this->options['sitemap']['general']['taxonomies']['included']['value']           = $this->sanitizeField( $options['sitemap']['general']['taxonomies']['included'], 'array' );
			$this->options['sitemap']['general']['additionalPages']['pages']['value']         = $this->sanitizeField( $options['sitemap']['general']['additionalPages']['pages'], 'array' );
			$this->options['sitemap']['general']['advancedSettings']['excludePosts']['value'] = $this->sanitizeField( $options['sitemap']['general']['advancedSettings']['excludePosts'], 'array' );
			$this->options['sitemap']['general']['advancedSettings']['excludeTerms']['value'] = $this->sanitizeField( $options['sitemap']['general']['advancedSettings']['excludeTerms'], 'array' );
			$this->options['sitemap']['rss']['postTypes']['included']['value']                = $this->sanitizeField( $options['sitemap']['rss']['postTypes']['included'], 'array' );
		}

		// Advanced options.
		if ( ! empty( $options['advanced'] ) ) {
			if ( isset( $options['advanced']['postTypes']['included'] ) ) {
				$this->options['advanced']['postTypes']['included']['value'] = $this->sanitizeField( $options['advanced']['postTypes']['included'], 'array' );
			}

			if ( isset( $options['advanced']['taxonomies']['included'] ) ) {
				$this->options['advanced']['taxonomies']['included']['value'] = $this->sanitizeField( $options['advanced']['taxonomies']['included'], 'array' );
			}
		}

		// Tools.
		if ( ! empty( $options['tools'] ) ) {
			if ( isset( $options['tools']['robots']['rules'] ) ) {
				$this->options['tools']['robots']['rules']['value'] = $this->sanitizeField( $options['tools']['robots']['rules'], 'array' );
			}
		}

		// Deprecated options.
		if ( ! empty( $options['deprecated'] ) ) {

			if ( isset( $options['deprecated']['webmasterTools']['googleAnalytics']['excludeUsers'] ) ) {
				$this->options['deprecated']['webmasterTools']['googleAnalytics']['excludeUsers']['value'] = $this->sanitizeField( $options['deprecated']['webmasterTools']['googleAnalytics']['excludeUsers'], 'array' ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			}
			if ( isset( $options['deprecated']['searchAppearance']['advanced']['excludePosts'] ) ) {
				$this->options['deprecated']['searchAppearance']['advanced']['excludePosts']['value'] = $this->sanitizeField( $options['deprecated']['searchAppearance']['advanced']['excludePosts'], 'array' ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			}

			if ( isset( $options['deprecated']['searchAppearance']['advanced']['excludeTerms'] ) ) {
				$this->options['deprecated']['searchAppearance']['advanced']['excludeTerms']['value'] = $this->sanitizeField( $options['deprecated']['searchAppearance']['advanced']['excludeTerms'], 'array' ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			}
		}

		// Social networks.
		if ( isset( $options['social']['profiles']['sameUsername']['included'] ) ) {
			$this->options['social']['profiles']['sameUsername']['included']['value'] = $this->sanitizeField( $options['social']['profiles']['sameUsername']['included'], 'array' );
		}

		// Update localized options.
		update_option( $this->optionsName . '_localized', $this->localized );

		// Update values.
		$this->update();

		// If phone settings have changed, let's see if we need to dump the phone number notice.
		if (
			$phoneNumberOptions &&
			$phoneNumberOptions !== $oldPhoneOption
		) {
			$notification = Models\Notification::getNotificationByName( 'v3-migration-schema-number' );
			if ( $notification->exists() ) {
				Models\Notification::deleteNotificationByName( 'v3-migration-schema-number' );
			}
		}

		// If sitemap settings were changed, static files need to be regenerated.
		if (
			! empty( $deprecatedSitemapOptions ) &&
			! empty( $sitemapOptions )
		) {
			if (
				(
					aioseo()->helpers->arraysDifferent( $oldSitemapOptions, $sitemapOptions ) ||
					aioseo()->helpers->arraysDifferent( $oldDeprecatedSitemapOptions, $deprecatedSitemapOptions )
				) &&
				$sitemapOptions['advancedSettings']['enable'] &&
				! $deprecatedSitemapOptions['advancedSettings']['dynamic']
			) {
				aioseo()->sitemap->scheduleRegeneration();
			}
		}
	}

	/**
	 * If the user does not have access to unfiltered HTML, we need to remove them from saving.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $options An array of options.
	 * @return array          An array of options.
	 */
	private function maybeRemoveUnfilteredHtmlFields( $options ) {
		if ( ! current_user_can( 'unfiltered_html' ) ) {
			if (
				! empty( $options['webmasterTools'] ) &&
				isset( $options['webmasterTools']['miscellaneousVerification'] )
			) {
				unset( $options['webmasterTools']['miscellaneousVerification'] );
			}

			if (
				! empty( $options['rssContent'] ) &&
				isset( $options['rssContent']['before'] )
			) {
				unset( $options['rssContent']['before'] );
			}

			if (
				! empty( $options['rssContent'] ) &&
				isset( $options['rssContent']['after'] )
			) {
				unset( $options['rssContent']['after'] );
			}
		}

		return $options;
	}

	/**
	 * Set a redirection URL after saving options or a slug ( 'reload' )
	 *
	 * @since 4.0.17
	 *
	 * @param  string $urlOrSlug
	 * @return void
	 */
	public function setRedirection( $urlOrSlug ) {
		$this->screenRedirection = $urlOrSlug;
	}

	/**
	 * Get the redirection URL after saving options
	 *
	 * @since 4.0.17
	 *
	 * @return boolean|string The screen redirection URL.
	 */
	public function getRedirection() {
		return $this->screenRedirection ? $this->screenRedirection : false;
	}


	/**
	 * Indicate we need to flush rewrite rules on next load.
	 *
	 * @since 4.0.17
	 *
	 * @return void
	 */
	public function flushRewriteRules() {
		update_option( 'aioseo_flush_rewrite_rules_flag', true );
	}

	/**
	 * Flush rewrite rules if needed.
	 *
	 * @since 4.0.17
	 *
	 * @return void
	 */
	public function maybeFlushRewriteRules() {
		if ( get_option( 'aioseo_flush_rewrite_rules_flag' ) ) {
			flush_rewrite_rules();
			delete_option( 'aioseo_flush_rewrite_rules_flag' );
		}
	}
}