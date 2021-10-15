<?php

/**
 * Get poll object
 *
 * @param  integer $poll_id ID of poll.
 *
 * @return object Poll object
 */
function democracy_get_poll( $poll_id ){
	return DemPoll::get_poll( $poll_id );
}

/**
 * Get poll attached to current post.
 *
 * @param  integer $post_id ID or object of post, attached poll of which you want to get.
 *
 * @return string  Poll HTML code.
 */
function get_post_poll_id( $post_id = 0 ){
	$post_id = ( is_numeric($post_id) && $post_id ) ? (int) $post_id : get_post( $post_id )->ID;

	return $poll_id = (int) get_post_meta( $post_id, Democracy_Poll::$pollid_meta_key, 1 );
}

/**
 * Display specified democracy poll.
 *
 * @see get_democracy_poll()
 */
function democracy_poll( $id = 0, $before_title = '', $after_title = '', $from_post = 0 ){
	echo get_democracy_poll( $id, $before_title, $after_title, $from_post );
}

/**
 * Get specified democracy poll.
 *
 * @param  integer  $poll_id       Poll ID. If 0 Random Active poll will be returned.
 * @param  string   $before_title  HTML/text before poll title.
 * @param  string   $after_title   HTML/text after poll title.
 * @param  integer  $from_post     Post ID from which the poll was called - to which the poll must be attached.
 *
 * @return string   Poll HTML code.
 */
function get_democracy_poll( $poll_id = 0, $before_title = '', $after_title = '', $from_post = 0 ){

	$poll = new DemPoll( $poll_id );

	if( ! $poll )
		return 'Poll not found';

	// обновим ID записи с которой вызван опрос, если такого ID нет в данных
	$from_post = is_object($from_post) ? $from_post->ID : intval($from_post);
	if( $from_post && ( ! $poll->in_posts || ! preg_match('~(?:^|,)'. $from_post .'(?:,|$)~', $poll->in_posts) ) ){
		global $wpdb;

		$new_in_posts = $poll->in_posts ? "$poll->in_posts,$from_post" : $from_post;
		$new_in_posts = trim( $new_in_posts, ','); // на всякий...
		$wpdb->update( $wpdb->democracy_q, array('in_posts'=>$new_in_posts), array('id'=>$poll_id) );
	}

	$show_screen = dem__query_poll_screen_choose( $poll );

	return $poll->get_screen( $show_screen, $before_title, $after_title );
}

/**
 * Gets poll results screen.
 *
 * @param  integer  $poll_id       Poll ID
 * @param  string   $before_title  HTML/text before poll title.
 * @param  string   $after_title   HTML/text after poll title.
 *
 * @return string   Poll HTML code.
 */
function get_democracy_poll_results( $poll_id = 0, $before_title = '', $after_title = '' ){
	if( ! $poll = new DemPoll( $poll_id ) ) return '';

	if( $poll->open && ! $poll->show_results ) return __('Poll results hidden for now...','democracy-poll');

	return $poll->get_screen( 'voted', $before_title, $after_title );
}

/**
 * Show archives.
 *
 * @see get_democracy_archives()
 *
 * @param array $args See {@see get_democracy_archives()}.
 *
 * @return string HTML
 */
function democracy_archives( $args = [] ){
	echo get_democracy_archives( $args );
}

/**
 * Retrieves list of polls for archive page.
 *
 * @param array $args {
 *     Array of arguments.
 *
 *     @type string $before_title
 *     @type string $after_title
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

	$dem_paged = isset( $_GET['dem_paged'] ) ? (int) $_GET['dem_paged'] : 1;

	$defaults = [
		'before_title' => '',
		'after_title'  => '',
		'active'       => null,    // 1 (active), 0 (not active) or null (param not set).
		'open'         => null,    // 1 (opened), 0 (closed) or null (param not set) polls.
		'screen'       => 'voted',
		'per_page'     => 10,
		'add_from_posts' => true,    // add From posts: html block
		// internal
		'paged'        => $dem_paged,       // pagination page when 'limit' parameter is set
		'wrap'         => '<div class="dem-archives">%s</div>',
		'return'       => 'html',
	];

	// backward compatibility
	if( func_num_args() > 1 ){
		$args = [
			'active'       => ( $hide_active = func_get_arg( 0 ) ) ? 0 : null,
			'before_title' => func_get_arg( 1 ),
			'after_title'  => func_get_arg( 2 ),
		];
	}

	$args = wp_parse_args( $args, $defaults );

	$html = get_dem_polls( $args );
	$found_rows = get_dem_polls( 'get_found_rows' );

	// pagination
	if( $found_rows ){

		$pagination = paginate_links( [
			'base'    => esc_url( remove_query_arg( 'dem_paged', $_SERVER['REQUEST_URI'] ) ) . '%_%',
			'format'  => '?dem_paged=%#%',
			'current' => max( 1, $dem_paged ),
			'total'   => ceil( $found_rows / (int) $args['per_page'] ),
		] );

		$html .= '<div class="dem-paging">'. $pagination .'</div>';
	}

	return $html;
}

/**
 * Gets polls by parametrs.
 *
 * @param array $args {
 *     Array of arguments.
 *
 *     @type string       $wrap            HTML block wrap tag.
 *     @type string       $before_title    For single poll title.
 *     @type string       $after_title     For single poll title.
 *     @type string       $screen          vote | voted.
 *     @type bool         $active          1 (active), 0 (not active) or null (param not set).
 *     @type bool         $open            1 (opened), 0 (closed) or null (param not set) polls.
 *     @type string       $add_from_posts  Add From posts: html block.
 *     @type string       $return          HTML, objects.
 *     @type int          $paged           Pagination page when 'limit' parameter is set.
 *     @type int          $per_page        Limit. 0 or -1 - no limit.
 *     @type string|array $orderby         [ 'open' => 'ASC' ] | 'open' | rand.
 * }
 *
 * @return array|string
 */
function get_dem_polls( $args = [] ){
	global $wpdb;

	static $all_found_rows;
	if( 'get_found_rows' === $args  )
		return $all_found_rows;

	$rg = (object) wp_parse_args( $args, [
		'wrap'           => '<div class="dem-polls">%s</div>',
		'before_title'   => '',
		'after_title'    => '',
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
	if( isset( $rg->active ) )
		$WHERE['active'] = $wpdb->prepare( 'WHERE active = %d', (int) $rg->active );
	if( isset( $rg->open ) )
		$WHERE['open']   = $wpdb->prepare( 'WHERE open = %d', (int) $rg->open );

	// ORDER_BY
	$esc_orderby__fn = function( $val ){
		return preg_replace( '/[^a-z0-9 _\-]/i', '', $val );
	};

	$ORDER_BY = [];
	if( ! $rg->orderby ){

		if( null === $rg->active )
			$ORDER_BY['active'] = 'active DESC';
		if( null === $rg->open )
			$ORDER_BY['open'] = 'open DESC';

		$ORDER_BY['id'] = 'id DESC';
	}
	else {

		if( is_array( $rg->orderby ) ){
			$ORDER_BY['array'] = $esc_orderby__fn( implode( ' ', $rg->orderby ) );
		}
		elseif( is_string($rg->orderby) ){

			if( 'rand' === $rg->orderby )
				$ORDER_BY['rand'] = 'rand()';
			else
				$ORDER_BY['string'] = $esc_orderby__fn( $rg->orderby ) .' ASC';
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

	$clauses = (object) apply_filters( 'get_dem_polls_sql_clauses', [
		'where'   => implode(' AND ', $WHERE ),
		'orderby' => 'ORDER BY '. implode(', ', $ORDER_BY ),
		'limit'   => $LIMIT,
	] );

	$sql = "SELECT id FROM $wpdb->democracy_q $clauses->where $clauses->orderby $clauses->limit";

	$poll_ids = $wpdb->get_col( $sql );

	$all_found_rows = $SET_FOUND_ROWS
		? $wpdb->get_var( "SELECT count(*) FROM $wpdb->democracy_q $clauses->where" )
		: null;

	// OUT
	$out = array();

	foreach( $poll_ids as $poll_id ){

		$DemPoll = new DemPoll( $poll_id );
		$poll = $DemPoll->poll;

		if( $rg->return === 'objects' ){
			$out[] = $DemPoll;
			continue;
		}

		// if return html is set
		$screen = isset( $_REQUEST['dem_act'] ) ? dem__query_poll_screen_choose( $DemPoll ) : $rg->screen;

		$elm_html = $DemPoll->get_screen( $screen, $rg->before_title, $rg->after_title );

		// in posts
		if( $rg->add_from_posts && ( $posts = democr()->get_in_posts_posts( $poll ) ) ){

			$links = array();
			foreach( $posts as $post ){
				$links[] = '<a href="' . get_permalink( $post ) . '">' . esc_html( $post->post_title ) . '</a>';
			}

			$elm_html .= '
			<div class="dem-moreinfo">
				<b>'. __('From posts:','democracy-poll') .'</b>
				<ul>
					<li>'. implode("</li>\n<li>", $links) .'</li>
				</ul>
			</div>';
		}

		$out[] = '<div class="dem-elem-wrap">'. $elm_html .'</div>';
	}

	if( $rg->return === 'objects' )
		return $out;
	else
		return sprintf( $rg->wrap, implode( "\n", $out ) );
}

/**
 * Какой экран показать, на основе переданных запросов: 'voted' или 'vote'.
 *
 * @param $poll
 *
 * @return mixed|string|void
 */
function dem__query_poll_screen_choose( $poll ){

	if( $poll->open && ! $poll->show_results )
		return 'vote'; // view results is closed in options

	$screen = (
		isset( $_REQUEST['dem_act'], $_REQUEST['dem_pid'] ) &&
		$_REQUEST['dem_act'] === 'view' &&
		(int) $_REQUEST['dem_pid'] === (int) $poll->id
	)
		? 'voted' : 'vote';

	return apply_filters( 'dem_poll_screen_choose', $screen, $poll );
}

