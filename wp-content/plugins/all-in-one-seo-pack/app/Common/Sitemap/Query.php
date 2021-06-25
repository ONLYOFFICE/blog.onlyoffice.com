<?php
namespace AIOSEO\Plugin\Common\Sitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all complex queries for the sitemap.
 *
 * @since 4.0.0
 */
class Query {
	/**
	 * Returns all eligble sitemap entries for a given post type.
	 *
	 * @since 4.0.0
	 *
	 * @param  mixed $postTypes      The post type(s). Either a singular string or an array of strings.
	 * @param  array $additionalArgs Any additional arguments for the post query.
	 * @return array                 The post objects.
	 */
	public function posts( $postTypes, $additionalArgs = [] ) {
		$includedPostTypes = $postTypes;
		if ( is_array( $postTypes ) ) {
			$includedPostTypes = implode( "', '", $postTypes );
		}

		if (
			empty( $includedPostTypes ) ||
			( 'attachment' === $includedPostTypes && 'disabled' !== aioseo()->options->searchAppearance->dynamic->postTypes->attachment->redirectAttachmentUrls )
		) {
			return [];
		}

		// Set defaults.
		$fields  = '`p`.`ID`, `p`.`post_title`, `p`.`post_content`, `p`.`post_excerpt`, `p`.`post_type`, `p`.`post_password`, ';
		$fields .= '`p`.`post_parent`, `p`.`post_date_gmt`, `p`.`post_modified_gmt`, `ap`.`priority`, `ap`.`frequency`';
		$maxAge  = '';
		$orderBy = '`p`.`ID` ASC';

		// Override defaults if passed as additional arg.
		foreach ( $additionalArgs as $name => $value ) {
			// Attachments need to be fetched with all their fields because we need to get their post parent further down the line.
			$$name = esc_sql( $value );
			if ( 'root' === $name && $value && 'attachment' !== $includedPostTypes ) {
				$fields = '`p`.`ID`, `p`.`post_type`';
			}
		}

		$query = aioseo()->db
			->start( aioseo()->db->db->posts . ' as p', true )
			->select( $fields )
			->leftJoin( 'aioseo_posts as ap', '`ap`.`post_id` = `p`.`ID`' )
			->where( 'p.post_status', 'attachment' === $includedPostTypes ? 'inherit' : 'publish' )
			->whereRaw( "p.post_type IN ( '$includedPostTypes' )" );

		if ( ! is_array( $postTypes ) ) {
			if ( ! aioseo()->helpers->isPostTypeNoindexed( $includedPostTypes ) ) {
				$query->whereRaw( '( `ap`.`robots_noindex` IS NULL OR `ap`.`robots_default` = 1 OR `ap`.`robots_noindex` = 0 )' );
			} else {
				$query->whereRaw( '( `ap`.`robots_default` = 0 AND `ap`.`robots_noindex` = 0 )' );
			}
		} else {
			$robotsMetaSql = [];
			foreach ( $postTypes as $postType ) {
				if ( ! aioseo()->helpers->isPostTypeNoindexed( $postType ) ) {
					$robotsMetaSql[] = "( `p`.`post_type` = '$postType' AND ( `ap`.`robots_noindex` IS NULL OR `ap`.`robots_default` = 1 OR `ap`.`robots_noindex` = 0 ) )";
				} else {
					$robotsMetaSql[] = "( `p`.`post_type` = '$postType' AND ( `ap`.`robots_default` = 0 AND `ap`.`robots_noindex` = 0 ) )";
				}
			}
			$query->whereRaw( '( ' . implode( ' OR ', $robotsMetaSql ) . ' )' );
		}

		$excludedPosts = aioseo()->sitemap->helpers->excludedPosts();
		if ( $excludedPosts ) {
			$query->whereRaw( "( `p`.`ID` NOT IN ( $excludedPosts ) )" );
		}

		// Exclude posts assigned to excluded terms.
		$excludedTerms = aioseo()->sitemap->helpers->excludedTerms();
		if ( $excludedTerms ) {
			$termRelationshipsTable = aioseo()->db->db->prefix . 'term_relationships';
			$query->whereRaw("
				( `p`.`ID` NOT IN
					(
						SELECT `tr`.`object_id`
						FROM `$termRelationshipsTable` as tr
						WHERE `tr`.`term_taxonomy_id` IN ( $excludedTerms )
					)
				)" );
		}

		if ( $maxAge ) {
			$query->whereRaw( "( `p`.`post_date_gmt` >= '$maxAge' )" );
		}

		if ( aioseo()->sitemap->indexes && empty( $additionalArgs['root'] ) ) {
			$query->limit( aioseo()->sitemap->linksPerIndex, aioseo()->sitemap->offset );
		}

		$posts = $query->orderBy( $orderBy )
			->run()
			->result();

		// Convert ID from string to int.
		foreach ( $posts as $post ) {
			$post->ID = intval( $post->ID );
		}

		return $this->filterPosts( $posts );
	}

	/**
	 * Filters the queried posts.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $posts          The posts.
	 * @return array $remainingPosts The remaining posts.
	 */
	public function filterPosts( $posts ) {
		$remainingPosts          = [];
		$isWooCommerceActive     = aioseo()->helpers->isWooCommerceActive();
		$isWooCommerceNoindexing = $isWooCommerceActive && has_action( 'wp_head', 'wc_page_noindex' );
		$excludeHiddenProducts   = apply_filters( 'aioseo_sitemap_woocommerce_exclude_hidden_products', true );

		foreach ( $posts as $post ) {
			if ( 'product' !== $post->post_type && is_numeric( $post ) ) {
				$remainingPosts[] = $post;
				continue;
			}

			switch ( $post->post_type ) {
				case 'page':
					if ( ! $isWooCommerceNoindexing || ! aioseo()->helpers->isWooCommercePage( $post->ID ) ) {
						$remainingPosts[] = $post;
					}
					break;
				case 'product':
					if ( ! $isWooCommerceActive || ! $excludeHiddenProducts || ! $this->isHiddenProduct( $post ) ) {
						$remainingPosts[] = $post;
					}
					break;
				case 'attachment':
					if ( ! $this->isInvalidAttachment( $post ) ) {
						$remainingPosts[] = $post;
					}
					break;
				default:
					$remainingPosts[] = $post;
					break;
			}
		}

		return $remainingPosts;
	}

	/**
	 * Whether the WooCommerce product is hidden.
	 *
	 * @since 4.0.0
	 *
	 * @param  Object  $post The post.
	 * @return boolean       Whether the post is a hidden product.
	 */
	private function isHiddenProduct( $post ) {
		static $hiddenProductIds = null;
		if ( null === $hiddenProductIds ) {
			$hiddenProducts = aioseo()->db->start( 'term_relationships as tr' )
				->select( 'tr.object_id' )
				->join( 'term_taxonomy as tt', 'tr.term_taxonomy_id = tt.term_taxonomy_id' )
				->join( 'terms as t', 'tt.term_id = t.term_id' )
				->where( 't.name', 'exclude-from-catalog' )
				->run()
				->result();

			$hiddenProductIds = [];
			if ( ! empty( $hiddenProducts ) ) {
				foreach ( $hiddenProducts as $hiddenProduct ) {
					$hiddenProductIds[] = (int) $hiddenProduct->object_id;
				}
			}
		}

		$postId = $post;
		if ( ! is_numeric( $post ) ) {
			$postId = $post->ID;
		}

		return in_array( $postId, $hiddenProductIds, true );
	}

	/**
	 * Excludes attachments if their post parent isn't published or parent post type isn't registered anymore.
	 *
	 * @since 4.0.0
	 *
	 * @param  Object  $post The post.
	 * @return boolean       Whether the attachment is invalid.
	 */
	private function isInvalidAttachment( $post ) {
		if ( empty( $post->post_parent ) ) {
			return false;
		}

		$parent = get_post( $post->post_parent );
		if ( ! is_object( $parent ) ) {
			return false;
		}

		if (
			'publish' !== $parent->post_status ||
			! in_array( $parent->post_type, get_post_types(), true ) ||
			$parent->post_password
		) {
			return true;
		}
		return false;
	}

	/**
	 * Returns all eligble sitemap entries for a given taxonomy.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $taxonomy       The taxonomy.
	 * @param  array  $additionalArgs Any additional arguments for the term query.
	 * @return array                  The term objects.
	 */
	public function terms( $taxonomy, $additionalArgs = [] ) {
		// Set defaults.
		$fields  = 't.term_id';
		$offset  = aioseo()->sitemap->offset;

		// Override defaults if passed as additional arg.
		foreach ( $additionalArgs as $name => $value ) {
			$$name = esc_sql( $value );
			if ( 'root' === $name ) {
				$fields = 't.term_id';
			}
		}

		$termRelationshipsTable = aioseo()->db->db->prefix . 'term_relationships';
		$termTaxonomyTable      = aioseo()->db->db->prefix . 'term_taxonomy';
		$query = aioseo()->db
			->start( aioseo()->db->db->terms . ' as t', true )
			->select( $fields )
			->whereRaw( "
			( `t`.`term_id` IN
				(
					SELECT `tt`.`term_id`
					FROM `$termTaxonomyTable` as tt
					WHERE `tt`.`taxonomy` = '$taxonomy'
					AND `tt`.`count` > 0
				)
			)" );

		$excludedTerms = aioseo()->sitemap->helpers->excludedTerms();
		if ( $excludedTerms ) {
			$query->whereRaw("
				( `t`.`term_id` NOT IN
					(
						SELECT `tr`.`term_taxonomy_id`
						FROM `$termRelationshipsTable` as tr
						WHERE `tr`.`term_taxonomy_id` IN ( $excludedTerms )
					)
				)" );
		}

		if ( aioseo()->sitemap->indexes && empty( $additionalArgs['root'] ) ) {
			$query->limit( aioseo()->sitemap->linksPerIndex, $offset );
		}

		$terms = $query->orderBy( '`t`.`term_id` ASC' )
			->run()
			->result();

		foreach ( $terms as $term ) {
			// Convert ID from string to int.
			$term->term_id = intval( $term->term_id );
			// Add taxonomy name to object manually instead of querying it to prevent redundant join.
			$term->taxonomy = $taxonomy;
		}
		return $terms;
	}

	/**
	 * Wipes all data and forces the plugin to rescan the site for images.
	 *
	 * @since 4.0.13
	 *
	 * @return void
	 */
	public function resetImages() {
		aioseo()->db
			->update( 'aioseo_posts' )
			->set(
				[
					'images'          => null,
					'image_scan_date' => null
				]
			)
			->run();
	}
}