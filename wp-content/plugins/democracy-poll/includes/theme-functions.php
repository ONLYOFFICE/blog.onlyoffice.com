<?php

use DemocracyPoll\Admin\Post_Metabox;
use DemocracyPoll\Poll;
use DemocracyPoll\Poll_Renderer;
use DemocracyPoll\Support\Helpers;
use function DemocracyPoll\container;

/**
 * @param Poll|int $poll_id  Poll ID to get. OR poll object from DB.
 */
function democracy_get_poll( $poll_id ): Poll {
	return new Poll( $poll_id );
}

/**
 * Gets a poll attached to the current post.
 *
 * @param int $post_id  ID or object of post, attached poll of which you want to get.
 *
 * @return int|string Poll ID or 'last' or 'rand'.
 */
function get_post_poll_id( $post_id = 0 ) {
	if( ! $post_id ){
		$post_id = get_post()->ID;
	}

	return Post_Metabox::get_post_poll_id( (int) $post_id );
}

/**
 * Display specified democracy poll.
 *
 *  Legacy signature is also supported:
 *
 *      democracy_poll( $poll_id, $before_title, $after_title, $from_post )
 *
 * @see get_democracy_poll()
 */
function democracy_poll( $args = [] ) {
	echo get_democracy_poll( ...func_get_args() );
}

/**
 * Get specified democracy poll.
 *
 * Legacy signature is also supported:
 *
 *     get_democracy_poll( $poll_id, $before_title, $after_title, $from_post )
 *
 * @since 6.4.1 All parameters replaced with `$args` array.
 *
 * @param array $args {
 *     Poll rendering arguments.
 *
 *     @type object|int $poll         Poll ID or Object. If 0 Random Active poll is returned. Default 0.
 *     @type object|int $from_post    Post ID from which the poll was called - to which the poll must be attached. Default 0.
 *     @type string     $title_markup Poll title markup containing the {question} placeholder. Default ''.
 * }
 *
 * @return string   Poll HTML code.
 */
function get_democracy_poll( $args = [] ) {
	// backward compatibility since v6.4.1
	if( ! is_array( $args ) ){
		$func_args = func_get_args();
		$poll         = $func_args[0] ?? 0;
		$before_title = $func_args[1] ?? '';
		$after_title  = $func_args[2] ?? '';
		$from_post    = $func_args[3] ?? 0;

		$title_markup = $before_title ? "$before_title{question}$after_title" : '';
	}
	else{
		$args = wp_parse_args( $args, [
			'poll'         => 0,
			'from_post'    => 0,
			'title_markup' => '',
		] );

		$poll         = $args['poll'];
		$title_markup = $args['title_markup'];
		$from_post    = $args['from_post'];
	}

	$poll = new Poll( $poll );
	if( ! $poll->id ){
		return 'Poll not found';
	}

	// Add the ID of the post where the poll was displayed if it is not stored yet.
	$from_post = is_object( $from_post ) ? $from_post->ID : (int) $from_post;
	if( $from_post && ( ! $poll->in_posts || ! preg_match( '~(?:^|,)' . $from_post . '(?:,|$)~', $poll->in_posts ) ) ){
		global $wpdb;

		$new_in_posts = $poll->in_posts ? "$poll->in_posts,$from_post" : $from_post;
		$new_in_posts = trim( $new_in_posts, ',' ); // Just in case.

		$wpdb->update( $wpdb->democracy_q, [ 'in_posts' => $new_in_posts ], [ 'id' => $poll->id ] );
	}

	return container()->make( Poll_Renderer::class, [ 'poll' => $poll ] )
			->render_poll( 'vote', $title_markup );
}

/**
 * Gets poll results screen.
 *
 * Legacy signature is also supported:
 *
 *     get_democracy_poll_results( $poll_id = 0, $before_title = '', $after_title = '' )
 *
 * @param array $args {
 *     Poll results rendering arguments.
 *
 *     @type object|int $poll         Poll ID or Object. Default 0.
 *     @type string     $title_markup Poll title markup containing the {question} placeholder. Default ''.
 * }
 *
 * @since 6.4.1 All parameters replaced with `$args` array.
 *
 * @return string Poll HTML code.
 */
function get_democracy_poll_results( $args = [] ) {
	// backward compatibility since v6.4.1
	if( ! is_array( $args ) ){
		$func_args   = func_get_args();
		$poll         = $func_args[0] ?? 0;
		$before_title = $func_args[1] ?? '';
		$after_title  = $func_args[2] ?? '';

		$title_markup = $before_title ? "$before_title{question}$after_title" : '';
	}
	else{
		$args = wp_parse_args( $args, [
			'poll'         => 0,
			'title_markup' => '',
		] );

		$poll         = $args['poll'];
		$title_markup = $args['title_markup'];
	}

	$poll = new Poll( $poll );
	if( ! $poll->id ){
		return '';
	}

	if( $poll->open && ! $poll->show_results ){
		return __( 'Poll results hidden for now...', 'democracy-poll' );
	}

	return container()->make( Poll_Renderer::class, [ 'poll' => $poll ] )
			->render_poll( 'voted', $title_markup );
}

/**
 * Display archives HTML.
 *
 * @param array $args  See {@see get_democracy_archives()}.
 */
function democracy_archives( $args = [] ): void {
	echo get_democracy_archives( ...func_get_args() );
}

/**
 * Retrieves list of polls for archive page.
 *
 * @param array $args {
 *     Array of arguments.
 *
 *     @type string $title_markup
 *     @type bool   $active
 *     @type bool   $open
 *     @type string $screen
 *     @type int    $per_page
 *     @type bool   $add_from_posts
 *     @type int    $paged
 *     @type string $wrap
 *     @type string $return
 * }
 *
 * @return string HTML
 */
function get_democracy_archives( $args = [] ){
	// backward compatibility
	if( func_num_args() > 1 ){
		$func_args = func_get_args();
		$args = [
			'active'       => $func_args[0] ? 0 : null, // $hide_active
			'title_markup' => $func_args[1] . '{question}' . ( $func_args[2] ?? '' ),
		];
	}

	// backward compatibility since v6.4.1
	if( ! empty( $args['before_title'] ) ){
		$args['title_markup'] = $args['before_title'] . '{question}' . $args['after_title'];
	}

	$dem_paged = max( 1, (int) ( $_GET['dem_paged'] ?? 0 ) );

	$defaults = [
		'title_markup'   => '',
		'active'         => null,    // 1 (active), 0 (not active) or null (param not set).
		'open'           => null,    // 1 (opened), 0 (closed) or null (param not set) polls.
		'screen'         => 'voted',
		'per_page'       => 10,
		'add_from_posts' => true,    // add From posts: html block
		// internal
		'paged'          => $dem_paged,       // pagination page when 'limit' parameter is set
		'wrap'           => '<div class="dem-archives">%s</div>',
		'return'         => 'html',
	];

	$args = wp_parse_args( $args, $defaults );

	$html = (string) get_dem_polls( $args );
	$found_rows = get_dem_polls( 'get_found_rows' );

	// pagination
	if( $found_rows ){
		$pagination = paginate_links( [
			'base'    => esc_url( remove_query_arg( 'dem_paged', $_SERVER['REQUEST_URI'] ) ) . '%_%',
			'format'  => '?dem_paged=%#%',
			'current' => $dem_paged,
			'total'   => ceil( $found_rows / (int) $args['per_page'] ),
		] );

		$html .= '<div class="dem-paging">' . $pagination . '</div>';
	}

	return $html;
}

/**
 * Gets polls by parameters.
 *
 * @param array|string $args {
 *     Array of arguments. Or 'get_found_rows' to get found rows count.
 *
 *     @type string       $wrap            HTML block wrap tag.
 *     @type string       $title_markup    For single poll title. Must contain {question} placeholder.
 *     @type string       $screen          vote | voted.
 *     @type bool         $active          1 (active), 0 (not active) or null (param not set).
 *     @type bool         $open            1 (opened), 0 (closed) or null (param not set) polls.
 *     @type string       $add_from_posts  Add From posts: HTML block.
 *     @type string       $return          HTML, objects.
 *     @type int          $paged           Pagination page when 'limit' parameter is set.
 *     @type int          $per_page        Limit. 0 or -1 - no limit.
 *     @type string|array $orderby         [ 'open' => 'ASC' ] | 'open' | rand.
 * }
 *
 * @return array|string|int Generated HTML string. OR Array or objects. OR int when 'get_found_rows' is passed.
 */
function get_dem_polls( $args = [] ) {
	global $wpdb;

	static $all_found_rows;
	if( 'get_found_rows' === $args ){
		return $all_found_rows;
	}

	// legacy support
	if( ! empty( $args['before_title'] ) ){
		$args['title_markup'] = $args['before_title'] . '{question}' . $args['after_title'];
	}

	$rg = (object) wp_parse_args( $args, [
		'wrap'           => '<div class="dem-polls">%s</div>',
		'title_markup'   => '',
		'screen'         => 'vote',
		'active'         => null,
		'open'           => null,
		'add_from_posts' => false,
		'return'         => 'html',
		'paged'          => 1,
		'per_page'       => 0,
		'orderby'        => [],
	] );

	// WHERE
	$WHERE = [];
	if( isset( $rg->active ) ){
		$WHERE['active'] = $wpdb->prepare( 'WHERE active = %d', (int) $rg->active );
	}
	if( isset( $rg->open ) ){
		$WHERE['open'] = $wpdb->prepare( 'WHERE open = %d', (int) $rg->open );
	}

	// ORDER_BY
	$esc_orderby__fn = static function( $val ) {
		return preg_replace( '/[^a-z0-9 _\-]/i', '', $val );
	};

	$ORDER_BY = [];
	if( ! $rg->orderby ){
		if( null === $rg->active ){
			$ORDER_BY['active'] = 'active DESC';
		}
		if( null === $rg->open ){
			$ORDER_BY['open'] = 'open DESC';
		}

		$ORDER_BY['id'] = 'id DESC';
	}
	elseif( is_array( $rg->orderby ) ){
		$ORDER_BY['array'] = $esc_orderby__fn( implode( ' ', $rg->orderby ) );
	}
	elseif( is_string( $rg->orderby ) ){

		if( 'rand' === $rg->orderby ){
			$ORDER_BY['rand'] = 'rand()';
		}
		else{
			$ORDER_BY['string'] = $esc_orderby__fn( $rg->orderby ) . ' ASC';
		}
	}

	// LIMIT
	$LIMIT = '';
	$SET_FOUND_ROWS = false;
	if( $rg->per_page > 0 ){
		$SET_FOUND_ROWS = true;
		$offset = $rg->paged > 1 ? ( (int) $rg->paged - 1 ) * $rg->per_page : 0;
		$LIMIT = $wpdb->prepare( 'LIMIT %d, %d', $offset, $rg->per_page );
	}

	/**
	 * Allows modifying the SQL clauses for getting polls.
	 * This filter can be used to add custom WHERE conditions, ORDER BY clauses, or LIMIT.
	 *
	 * @param array $clauses  Array of SQL clauses.
	 */
	$clauses = (object) apply_filters( 'get_dem_polls_sql_clauses', [
		'where'   => implode( ' AND ', $WHERE ),
		'orderby' => 'ORDER BY ' . implode( ', ', $ORDER_BY ),
		'limit'   => $LIMIT,
	] );

	$sql = "SELECT id FROM $wpdb->democracy_q $clauses->where $clauses->orderby $clauses->limit";

	$poll_ids = $wpdb->get_col( $sql );

	$all_found_rows = (int) $SET_FOUND_ROWS
		? $wpdb->get_var( "SELECT count(*) FROM $wpdb->democracy_q $clauses->where" )
		: 0;

	// poll objects
	$polls = [];
	if( $rg->return === 'objects' ){
		foreach( $poll_ids as $poll_id ){
			$polls[] = new Poll( $poll_id );
		}

		return $polls;
	}

	// HTML
	$out = [];
	foreach( $poll_ids as $poll_id ){
		$poll = new Poll( $poll_id );

		$elm_html = container()->make( Poll_Renderer::class, [ 'poll' => $poll ] )
			->render_poll( $rg->screen, $rg->title_markup );

		// in posts
		if(
			$rg->add_from_posts &&
			( $posts = Helpers::get_posts_with_poll( $poll ) )
		){
			$links = [];
			foreach( $posts as $post ){
				$links[] = sprintf( '<a href="%s">%s</a>', get_permalink( $post ), esc_html( $post->post_title ) );
			}

			$elm_html .= '
			<div class="dem-moreinfo">
				<b>' . __( 'From posts:', 'democracy-poll' ) . '</b>
				<ul>
					<li>' . implode( "</li>\n<li>", $links ) . '</li>
				</ul>
			</div>';
		}

		$out[] = '<div class="dem-elem-wrap">' . $elm_html . '</div>';
	}

	return sprintf( $rg->wrap, implode( "\n", $out ) );
}
