<?php

namespace DemocracyPoll;

class Shortcodes {

	public function __construct(){
	}

	public function init(): void {
		add_shortcode( 'democracy', [ $this, 'democracy_shortcode' ] );
		add_shortcode( 'democracy_archives', [ $this, 'democracy_archives_shortcode' ] );
	}

	public function democracy_archives_shortcode( $args ): string {

		$args = shortcode_atts( [
			'before_title'   => '',
			'after_title'    => '',
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
			// 'before_title'  => '', // IMP! can't be added - security reason
			// 'after_title'   => '', // IMP! can't be added - security reason
		], $atts, 'democracy' );

		// для опредления к какой записи относиться опрос. проверка, если шорткод вызван не из контента...
		$post_id = ( is_singular() && is_main_query() ) ? $GLOBALS['post']->ID : 0;

		if( $atts['id'] === 'current' ){
			$atts['id'] = \DemocracyPoll\Admin\Post_Metabox::get_post_poll_id( $post_id );
		}

		return '<div class="dem-poll-shortcode">' . get_democracy_poll( $atts['id'], '', '', $post_id ) . '</div>';
	}

}
