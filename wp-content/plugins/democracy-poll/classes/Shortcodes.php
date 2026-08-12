<?php

namespace DemocracyPoll;

class Shortcodes {

	private Options $options;

	public function __construct( Options $options ){
		$this->options = $options;
	}

	public function init(): void {
		add_shortcode( 'democracy', [ $this, 'democracy_shortcode' ] );
		add_shortcode( 'democracy_archives', [ $this, 'democracy_archives_shortcode' ] );
	}

	public function democracy_archives_shortcode( $args ): string {
		$args = shortcode_atts( [
			// 'before_title' => '', // deprecated since 6.4.1
			// 'after_title'  => '', // deprecated since 6.4.1
			'title_markup'   => '',
			'active'         => null,    // 1 (active), 0 (not active) or null (param not set).
			'open'           => null,    // 1 (opened), 0 (closed) or null (param not set) polls.
			'screen'         => 'voted',
			'per_page'       => 10,
			'add_from_posts' => true,    // add From posts: html block
			'orderby'        => '',      // string|array - [ 'open' => 'ASC' ] | 'open' | rand
		], $args );

		return '<div class="dem-archives-shortcode">' . get_democracy_archives( $args ) . '</div>';
	}

	public function democracy_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'id' => '', // number or 'current', 'last'
			// 'before_title' => '', // IMP! can't be added - security reason
			// 'after_title'  => '', // IMP! can't be added - security reason
		], $atts, 'democracy' );

		// Determine which post the poll belongs to when the shortcode is used outside the content.
		$post_id = ( is_singular() && is_main_query() ) ? $GLOBALS['post']->ID : 0;
		$poll = self::normalize_poll_id_attr( $atts['id'] );

		if( $poll === 'current' ){
			$poll = Admin\Post_Metabox::get_post_poll_id( $post_id ) ?: 'rand';
		}

		if( $poll === 'last' || $poll === 'rand' ){
			$poll = Poll_Storage::get_db_data( $poll );
		}

		return '<div class="dem-poll-shortcode">' . get_democracy_poll( [
			'poll'         => $poll,
			'title_markup' => $this->options->title_markup,
			'from_post'    => $post_id,
		] ) . '</div>';
	}

	private static function normalize_poll_id_attr( $poll_id ): string {
		return sanitize_key( html_entity_decode( (string) $poll_id, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	}

}
