<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains shortcode specific helper methods.
 *
 * @since 4.1.2
 */
trait Shortcodes {
	/**
	 * Shortcodes known to conflict with AIOSEO.
	 *
	 * @since 4.1.2
	 *
	 * @var array
	 */
	private $conflictingShortcodes = [
		'WooCommerce Login'                => 'woocommerce_my_account',
		'WooCommerce Checkout'             => 'woocommerce_checkout',
		'WooCommerce Order Tracking'       => 'woocommerce_order_tracking',
		'WooCommerce Cart'                 => 'woocommerce_cart',
		'WooCommerce Registration'         => 'wwp_registration_form',
		'WISDM Group Registration'         => 'wdm_group_users',
		'WISDM Quiz Reporting'             => 'wdm_quiz_statistics_details',
		'WISDM Course Review'              => 'rrf_course_review',
		'Simple Membership Login'          => 'swpm_login_form',
		'Simple Membership Mini Login'     => 'swpm_mini_login',
		'Simple Membership Payment Button' => 'swpm_payment_button',
		'Simple Membership Thank You Page' => 'swpm_thank_you_page_registration',
		'Simple Membership Registration'   => 'swpm_registration_form',
		'Simple Membership Profile'        => 'swpm_profile_form',
		'Simple Membership Reset'          => 'swpm_reset_form',
		'Simple Membership Update Level'   => 'swpm_update_level_to',
		'Simple Membership Member Info'    => 'swpm_show_member_info',
		'Revslider'                        => 'rev_slider'
	];

	/**
	 * Returns the content with shortcodes replaced.
	 *
	 * @since 4.0.5
	 *
	 * @param  string $content      The post content.
	 * @param  array  $tagsToRemove Shortcode tags that should be removed before parsing the content.
	 * @param  bool   $override     Whether shortcodes should be parsed regardless of the context. Needed for ActionScheduler actions.
	 * @return string $content      The post content with shortcodes replaced.
	 */
	public function doShortcodes( $content, $tagsToRemove = [], $override = false ) {
		if (
			! $override &&
			(
				( is_admin() && ! wp_doing_ajax() && ! wp_doing_cron() ) ||
				apply_filters( 'aioseo_disable_shortcode_parsing', false )
			)
		) {
			return $content;
		}

		global $shortcode_tags;
		$conflictingShortcodes = apply_filters( 'aioseo_conflicting_shortcodes', $this->conflictingShortcodes );

		foreach ( $conflictingShortcodes as $shortcode ) {
			$shortcodeTag = str_replace( [ '[', ']' ], '', $shortcode );
			if ( array_key_exists( $shortcodeTag, $shortcode_tags ) ) {
				$tagsToRemove[ $shortcodeTag ] = $shortcode_tags[ $shortcodeTag ];
			}
		}

		// Remove all conflicting shortcodes before parsing the content.
		foreach ( $tagsToRemove as $shortcodeTag => $shortcodeCallback ) {
			remove_shortcode( $shortcodeTag );
		}

		$content = do_shortcode( $content );

		// Add back shortcodes as remove_shortcode() disables them site-wide.
		foreach ( $tagsToRemove as $shortcodeTag => $shortcodeCallback ) {
			add_shortcode( $shortcodeTag, $shortcodeCallback );
		}

		return $content;
	}

	/**
	 * Returns the content with only the allowed shortcodes and wildcards replaced.
	 * This function should be used in Action Scheduler action callbacks only as it runs shortcodes everywhere, regardless of the context.
	 *
	 * @since 4.1.2
	 *
	 * @param  string $content     The content.
	 * @param  array  $allowedTags The allowed shortcode tags.
	 * @param  array  $wildcards   The wildcards.
	 * @return string              The content with shortcodes replaced.
	 */
	public function doAllowedShortcodes( $content, $allowedTags = [], $wildcards = [] ) {
		// Extract list of shortcodes from the post content.
		$tags = $this->getShortcodeTags( $content );
		if ( ! count( $tags ) ) {
			return $content;
		}

		// Include shortcodes that match wildcards with allowed shortcodes.
		foreach ( $tags as $tag ) {
			foreach ( $wildcards as $wildcard ) {
				if ( preg_match( "/$wildcard/", $tag ) ) {
					$allowedTags[] = $tag;
				}
			}
		}

		$tagsToRemove = array_diff( $tags, $allowedTags );
		return $this->doShortcodes( $content, $tagsToRemove, true );
	}

	/**
	 * Extracts the shortcode tags from the content.
	 *
	 * @since 4.1.2
	 *
	 * @param  string $content The content.
	 * @return array  $tags    The shortcode tags.
	 */
	private function getShortcodeTags( $content ) {
		$tags    = [];
		$pattern = '\\[(\\[?)([^\s]*)(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*+(?:\\[(?!\\/\\2\\])[^\\[]*+)*+)\\[\\/\\2\\])?)(\\]?)';
		if ( preg_match_all( "#$pattern#s", $content, $matches ) && array_key_exists( 2, $matches ) ) {
			$tags = array_unique( $matches[2] );
		}

		if ( ! count( $tags ) ) {
			return $tags;
		}

		// Extract nested shortcodes.
		foreach ( $matches[5] as $innerContent ) {
			$tags = array_merge( $tags, $this->getShortcodeTags( $innerContent ) );
		}

		return $tags;
	}
}