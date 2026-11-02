<?php

namespace DemocracyPoll;

use DemocracyPoll\Support\Kses;

/**
 * Handles rendering the poll HTML for display on the front-end.
 */
class Poll_Renderer {

	/** Flag that indicates whether the poll is displayed on an archive page */
	private bool $in_archive = false;

	/** Flag that helps determine if additional data is needed for the caching mechanism */
	private bool $for_cache = false;

	/** Flag to not show results in the poll */
	public bool $not_show_results = false;

	private Poll $poll;
	private Plugin $plugin;
	private Options $options;

	public function __construct( Poll $poll, Plugin $plugin, Options $options ) {
		$this->poll = $poll;
		$this->plugin = $plugin;
		$this->options = $options;

		if(
			$poll->open
			&& ( ! $poll->show_results || $this->options->dont_show_results )
			&& ( ! is_admin() || wp_doing_ajax() )
		){
			$this->not_show_results = true;
		}
	}

	/**
	 * Gets the poll HTML.
	 *
	 * @since 6.4.1 `$before_title`, `$after_title` parameters replaced with `$title_markup`
	 *               Old Signature `render_poll( $show_screen, $before_title, $after_title )`.
	 *
	 * @param string $show_screen  Which screen to display: vote, voted, force_vote.
	 * @param string $title_markup Poll title markup containing the {question} placeholder.
	 *
	 * @return string HTML. Empty string if poll is not found.
	 */
	public function render_poll( string $show_screen = 'vote', string $title_markup = '' ): string {
		$opt = $this->options; // simplify
		$poll = $this->poll; // simplify
		if( ! $this->poll->id ){
			return '';
		}

		$once_data = $this->get_poll_assets_once();
		$styles               = $once_data['styles'] ?? '';
		$loader_html          = $once_data['loader_html'] ?? '';
		$notice_template_html = $once_data['notice_template_html'] ?? '';

		$this->in_archive = ( (int) ( $GLOBALS['post']->ID ?? 0 ) === (int) $opt->archive_page_id ) && is_singular();

		// Legacy signature: `render_poll( $show_screen, $before_title, $after_title )`.
		$is_legacy_call = func_num_args() > 2 || ( $title_markup && ! str_contains( $title_markup, '{question}' ) );
		if( $is_legacy_call ){
			[ $def_before_title, $def_after_title ] = explode( '{question}', $opt->title_markup, 2 ) + [ '', '' ];
			$before_title = $title_markup ?: $def_before_title;
			$after_title  = ( func_get_args()[2] ?? '' ) ?: $def_after_title;
			$title_markup = "$before_title{question}$after_title";
		}

		$title_html = str_replace( '{question}', Kses::kses_html( $poll->question ), $title_markup ?: $opt->title_markup );

		// ! before get_cache_screens() because of filters
		$poll_body_html = $this->get_poll_body( $show_screen );

		// for page cache // not use caching mechanism in admin
		$cache_screens = ( ! $this->in_archive && ! is_admin() && $this->plugin->is_cachegear_on )
			? $this->get_cache_screens()
			: '';

		$opts_json = esc_attr( wp_json_encode( [
			'pid'                 => (int) $poll->id,
			'max_answs'           => (int) ( $poll->multiple ?: 0 ),
			'answs_max_height'    => is_numeric( $opt->answs_max_height ) ? "{$opt->answs_max_height}px" : $opt->answs_max_height,
			'allow_same_ip_votes' => (int) $opt->allow_same_ip_votes,
		] ) );

		return <<<HTML
			$styles
			$loader_html
			$notice_template_html
			<div id="democracy-$poll->id" class="democracy democracy_js" data-opts='$opts_json' >
				$title_html
				$poll_body_html
			</div><!--democracy-->
			$cache_screens
			HTML;
	}

	protected function get_poll_body( string $show_screen ): string {
		$poll = $this->poll; // simplify

		if( $poll->user_state->voting_blocked && $show_screen !== 'force_vote' ){
			$show_screen = 'voted';
		}

		// changeable part
		$screen_html = $this->get_screen_basis( $show_screen );

		$note_html = $poll->note ? sprintf( '<div class="dem-poll-note">%s</div>', wpautop( Kses::kses_html( $poll->note ) ) ) : '';

		$edit_link_html = Poll_Utils::cuser_can_edit_poll( $poll )
			? strtr( '<a class="dem-edit-link" href="{HREF}" title="{TITLE}">{SVG}</a>', [
				'{HREF}'  => esc_url( Poll_Utils::edit_poll_url( $poll->id ) ),
				'{TITLE}' => esc_attr__( 'Edit poll', 'democracy-poll' ),
				'{SVG}'   => '<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="1.5em" viewBox="0 0 1000 1000"><path d="m617.8 203.4 175.8 175.8-445 445-175.7-175.8zM927 161l-78.4-78.4c-30.3-30.3-79.5-30.3-109.9 0l-75.1 75.1 175.8 175.8 87.6-87.6c23.5-23.5 23.5-61.4 0-84.9M80.9 895.5c-3.2 14.4 9.8 27.3 24.2 23.8L301 871.8 125.3 696z"/></svg>',
			] )
			: '';

		return
			$screen_html .
			$note_html .
			$edit_link_html;
	}

	/**
	 * @return array{
	 *     notice_template_html:string,
	 *     loader_html:string,
	 *     styles:string,
	 * }
	 */
	protected function get_poll_assets_once(): array {
		static $once = 0;
		if( $once++ ){
			return [];
		}

		Poll_Utils::enqueue_js();

		return [
			'notice_template_html' => $this->notice_template_html(),
			'loader_html'          => $this->get_loader_html(),
			'styles'               => Poll_Utils::get_minified_styles(),
		];
	}

	protected function get_cache_screens(): string {
		$poll = $this->poll; // simplify
		$ustate = $poll->user_state;

		$saved_voted_for = $ustate->voted_for;
		$saved_has_voted = $ustate->has_voted;
		$ustate->voted_for = '';
		$ustate->has_voted = false;
		$this->for_cache = true;

		$html = '';
		// voted screen
		if( ! $this->not_show_results ){
			$html .= $this->get_screen_basis( 'voted' );
		}

		// vote screen
		if( $poll->open ){
			$html .= $this->get_screen_basis( 'force_vote' );
		}

		$this->for_cache = false;
		$ustate->voted_for = $saved_voted_for;
		$ustate->has_voted = $saved_has_voted;

		return <<<HTML
			<!--noindex-->
			<div class="dem-cache-screens dem_cache_screens_js" style="display:none;">$html</div>
			<!--/noindex-->
			HTML;
	}

	/**
	 * Gets the core HTML of the poll (the dynamic part).
	 *
	 * @param string $show_screen One of: vote, voted, force_vote.
	 *
	 * @return string HTML
	 */
	protected function get_screen_basis( string $show_screen = 'vote' ): string {
		if( $this->not_show_results ){
			$show_screen = 'force_vote';
		}

		$screen = ( $show_screen === 'vote' || $show_screen === 'force_vote' )
			? 'vote'
			: 'voted';

		$class = $this->for_cache
			? "dem-screen-cache $screen dem_screen_cache_js"
			: "dem-screen $screen dem_screen_js";

		$html = "<div class=\"$class\">";
		$html .= ( $screen === 'vote' )
			? $this->get_vote_screen()
			: $this->get_result_screen();
		$html .= '</div>';

		return $html;
	}

	/**
	 * Gets the voting form HTML
	 */
	public function get_vote_screen(): string {
		$poll = $this->poll; // simplify
		if( ! $poll->id ){
			return '';
		}

		$auto_vote_on_click = ( ! $poll->multiple && $poll->revote && $this->options->hide_vote_button );
		$auto_vote_attr = $auto_vote_on_click ? 'data-is_auto_vote="1"' : '';
		$body_html = $this->vote_body( $auto_vote_on_click );
		$bottom_html = $this->vote_bottom( $auto_vote_on_click );

		$html = <<<HTML
		<div class="dem-vote-wrap dem_vote_wrap_js" $auto_vote_attr>
			$body_html
			$bottom_html
		</div>
		HTML;

		/**
		 * Allows modifying the vote screen HTML before it is returned.
		 *
		 * @param string $html  The HTML of the vote screen.
		 * @param Poll   $poll  The current poll object.
		 */
		return apply_filters( 'dem_vote_screen', $html, $poll );
	}

	private function vote_body( bool $auto_vote_on_click ): string {
		$poll = $this->poll;

		$lis_html = '';
		foreach( $poll->answers as $answer ){
			/**
			 * Allows modifying the answer object before it will be processed for output.
			 *
			 * @param Poll_Answer $answer The answer object.
			 */
			$answer = apply_filters( 'dem_vote_screen_answer', $answer );
			/** @var Poll_Answer $answer */

			$checked = '';
			if( in_array( (string) $answer->aid, explode( ',', $poll->user_state->voted_for ), true ) ){
				$checked = ' checked="checked"';
			}

			$lis_html .= strtr( <<<'HTML'
				<li class="dem-answer-item dem_answer_item_js" data-aid="{AID}">
					<label class="dem__{TYPE}_label">
						<input class="dem__{TYPE}" {INPUT_NAME} {AUTO_VOTE} type="{TYPE}" value="{AID}" {CHECKED} {DISABLED}><span class="dem__spot"></span> {ANSWER}
					</label>
				</li>
				HTML,
				[
					'{AID}'        => $answer->aid,
					'{INPUT_NAME}' => $poll->multiple ? '' : "name=\"dem_group_p$poll->id\"", // radio should have grouping name
					'{TYPE}'       => $poll->multiple ? 'checkbox' : 'radio',
					'{AUTO_VOTE}'  => $auto_vote_on_click ? 'data-dem-act="vote"' : '',
					'{CHECKED}'    => $checked,
					'{DISABLED}'   => $poll->user_state->voted_for ? 'disabled="disabled"' : '',
					'{ANSWER}'     => $answer->answer,
				]
			);
		}

		if( $poll->democratic && ! $poll->user_state->voting_blocked ){
			$lis_html .= strtr( <<<'HTML'
				<li class="dem-add-answer dem_add_answer_item_js">
					<a class="dem-link dem_link_js dem-add-answer-link dem_add_answer_link_js" data-dem-act="newAnswer" href="#" rel="nofollow">{ANCHOR}</a>
				</li>
				HTML,
				[ '{ANCHOR}' => _x( 'Add your answer', 'front', 'democracy-poll' ) ]
			);
		}

		return <<<HTML
		<ul class="dem-vote dem_answers_list_js">
			$lis_html
		</ul>
		HTML;
	}

	private function vote_bottom( bool $auto_vote_on_click ): string {
		$poll = $this->poll;

		$voted_btn = strtr( <<<HTML
			<div class="dem-voted-button dem_voted_button_js">
				<input class="dem-button {CLASS}" type="button" value="{ANCHOR}" disabled="disabled">
			</div>
			HTML,
			[
				'{CLASS}' => $this->options->btn_class,
				'{ANCHOR}' => esc_attr_x( 'Already voted...', 'front', 'democracy-poll' ),
			]
		);

		$vote_btn = strtr( <<<HTML
			<div class="dem-vote-button dem_vote_button_js" {ATTRS}>
				<input class="dem-button {CLASS}" type="button" value="{ANCHOR}" data-dem-act="vote">
			</div>
			HTML,
			[
				'{CLASS}' => $this->options->btn_class,
				'{ANCHOR}' => esc_attr_x( 'Vote', 'front', 'democracy-poll' ),
				'{ATTRS}' => $auto_vote_on_click ? 'style="display:none;"' : '',
			]
		);

		$html = '';
		$login_required_alert = $this->registered_only_alert_html();

		// add for cache
		if( $this->for_cache ){
			$html .= $poll->revote
				? preg_replace( '/(<[^>]+)/', '$1 style="display:none;"', $this->revote_btn_html(), 1 )
				: substr_replace( $voted_btn, '<div style="display:none;"', 0, 4 );

			$html .= $vote_btn;
		}
		// not for cache
		elseif( $login_required_alert ){
			$html .= $login_required_alert;
		}
		else{
			$html .= $poll->user_state->has_voted
				? ( $poll->revote ? $this->revote_btn_html() : $voted_btn )
				: $vote_btn;
		}

		if( ! $this->not_show_results && ! $this->options->dont_show_results_link ){
			$html .= '<a href="#" class="dem-link dem_link_js dem-results-link" data-dem-act="viewResults" rel="nofollow">' . _x( 'Results', 'front', 'democracy-poll' ) . '</a>';
		}

		return <<<HTML
		<div class="dem-bottom dem-vote-bottom">
			$html
		</div><!--/dem-bottom-->
		HTML;
	}

	/**
	 * Gets the voting results HTML code.
	 *
	 * @return string HTML
	 */
	public function get_result_screen(): string {
		$poll = $this->poll; // simplify

		if( ! $poll->id ){
			return '';
		}

		$answers = $this->get_result_screen_answers();

		$total_votes = 0;
		$max_votes = 0;
		$has_added_by = false;
		foreach( $answers as $answer ){
			$total_votes += $answer->votes;
			$has_added_by = $has_added_by || $answer->added_by;
			$max_votes = max( $max_votes, $answer->votes );
		}

		$answers_html = $this->results_body( $answers, $max_votes, $total_votes );
		$bottom_html  = $this->results_bottom( $total_votes, $has_added_by );

		$html = "$answers_html\n$bottom_html";

		/**
		 * Allows modifying result screen HTML before it is returned.
		 *
		 * @param string $html  The HTML of the result screen.
		 * @param Poll   $poll  The current poll object.
		 */
		return apply_filters( 'dem_result_screen', $html, $this->poll );
	}

	/**
	 * @param Poll_Answer[] $answers
	 */
	private function results_body( array $answers, int $max_votes, int $total_votes ): string {
		$poll = $this->poll;

		$voted_txt = _x( 'This is your vote.', 'front', 'democracy-poll' );
		$voted_aids = Poll_Utils::parse_voted_str( $poll->user_state->voted_for );

		$list_html = '';
		foreach( $answers as $answer ){
			/**
			 * Allows modifying the answer object before it is processed for output.
			 *
			 * @param Poll_Answer $answer The answer object.
			 */
			$answer = apply_filters( 'dem_result_screen_answer', $answer );

			$is_voted_this = ( $poll->user_state->has_voted && in_array( $answer->aid, $voted_aids, true ) );

			// class
			$is_winner     = ( $max_votes === $answer->votes );
			$novoted_class = $answer->votes ? '' : ' dem-novoted';
			$li_class      = trim( ( $is_winner ? 'dem-winner' : '' ) . ( $is_voted_this ? " dem-voted-this" : '' ) . $novoted_class );
			$li_class_attr = $li_class ? " class=\"$li_class\"" : '';

			// percent
			$percent = ( $answer->votes > 0 ) ? round( $answer->votes / $total_votes * 100 ) : 0;
			$percent_txt = strtr(
				_x( '{votes} - {percent}% of all votes', 'front', 'democracy-poll' ),
				[
					'{votes}'   => self::pluralize( $answer->votes, _x( 'vote,votes,votes', 'front', 'democracy-poll' ) ),
					'{percent}' => $percent,
				]
			);

			// title
			$title = trim( ( $is_voted_this ? $voted_txt : '' ) . ' ' . $percent_txt );
			$title_attr = 'title="' . esc_attr( $title ) . '"';

			// label
			$votes_txt = strtr( '{VOTES_NUM} <span class="votxt">{VOTES_WORD}</span>', [
				'{VOTES_NUM}'  => $answer->votes,
				'{VOTES_WORD}' => self::pluralize( $answer->votes, _x( 'vote,votes,votes', 'front', 'democracy-poll' ), false ),
			] );
			$label_perc_txt = "$percent%, $votes_txt";

			$graph_percent = ( ! $this->options->graph_from_total && $percent ) ? round( $answer->votes / $max_votes * 100 ) : $percent;
			$graph_percent = $graph_percent ? "$graph_percent%" : '1px';
			$width_attr    = $this->options->line_anim_speed
				? 'data-width="' . esc_attr( $graph_percent ) . '"'
				: 'style="width:' . esc_attr( $graph_percent ) . '"';

			$percent_html  = $percent ? "<span class=\"dem-votes-txt-percent\">$percent%</span>" : '';

			$mark = $answer->added_by
				? '<sup class="dem-star" title="' . esc_attr_x( 'The answer was added by a visitor', 'front', 'democracy-poll' ) . '">*</sup>'
				: '';

			$list_html .= <<<HTML
			<li $li_class_attr $title_attr data-aid="$answer->aid">
				<div class="dem-label">
					$answer->answer $mark
					<span class="dem-label-percent-txt">$label_perc_txt</span>
				</div>
				<div class="dem-graph">
					<div class="dem-fill dem_fill_js" $width_attr></div>
					<div class="dem-votes-txt">
						<span class="dem-votes-txt-votes">$votes_txt</span>
						$percent_html
					</div>
					<div class="dem-percent-txt">$percent_txt</div>
				</div>
			</li>
			HTML;
		}

		$esc_voted_txt = esc_attr( $voted_txt );

		return <<<HTML
		<ul class="dem-answers dem_answers_list_js" data-voted_txt="$esc_voted_txt">
			$list_html
		</ul>
		HTML;
	}

	private function results_bottom( int $total_votes, bool $has_added_by ): string {
		$poll = $this->poll; // simplify

		$total_votes_txt = sprintf( _x( 'Total Votes: %s', 'front', 'democracy-poll' ), $total_votes );
		$begin_title     = esc_attr( _x( 'Begin', 'front', 'democracy-poll' ) );
		$end_title       = _x( 'End', 'front', 'democracy-poll' );
		$begin_date_txt  = date_i18n( get_option( 'date_format' ), $poll->added );
		$end_date_txt    = date_i18n( get_option( 'date_format' ), $poll->end );
		$end_date = $poll->end
			? ' - <span class="dem-end-date" title="' . esc_attr( $end_title ) . '">' . $end_date_txt . '</span>'
			: '';
		$voters_txt = sprintf( _x( 'Voters: %s', 'front', 'democracy-poll' ), $poll->users_voted );
		$voters_div = $poll->multiple
			? '<div class="dem-users-voted">' . esc_html( $voters_txt ) . '</div>'
			: '';
		$added_by_div = $has_added_by
			? '<div class="dem-added-by-user"><span class="dem-star">*</span>' . esc_html_x( ' - added by visitor', 'front', 'democracy-poll' ) . '</div>'
			: '';
		$closed_div   = ! $poll->open
			? '<div>' . _x( 'Voting is closed', 'front', 'democracy-poll' ) . '</div>'
			: '';
		$archive_link = ( ! $this->in_archive && $this->options->archive_page_id )
			? '<a class="dem-archive-link dem-link" href="' . get_permalink( $this->options->archive_page_id ) . '" rel="nofollow">' . esc_html_x( 'Polls Archive', 'front', 'democracy-poll' ) . '</a>'
			: '';

		$controls_html = $this->result_screen_controls_html();

		return <<<HTML
		<div class="dem-bottom dem-results-bottom">
			<div class="dem-poll-info">
				<div class="dem-total-votes">$total_votes_txt</div>
				$voters_div
				<div class="dem-date" title="$begin_title">
					<span class="dem-begin-date">$begin_date_txt</span>$end_date
				</div>
				$added_by_div
				$closed_div
				$archive_link
			</div>
			$controls_html
		</div><!--/dem-bottom-->
		HTML;
	}

	/**
	 * @return Poll_Answer[]
	 */
	private function get_result_screen_answers(): array {
		$answers = $this->poll->answers;

		$order = $this->options->order_answers_voted ?: 'by_winner';

		if( $order === 'by_winner' || $order == 1 ){
			$answers = wp_list_sort( $answers, [ 'votes' => 'desc' ] );
		}
		elseif( $order === 'alphabet' ){
			$answers = wp_list_sort( $answers, [ 'answer' => 'asc' ] );
		}
		elseif( $order === 'mix' ){
			shuffle( $answers );
		}
		elseif( $order === 'by_id' ){
			$answers = wp_list_sort( $answers, [ 'aid' => 'asc' ] );
		}

		/**
		 * Allows the result screen answers to be changed before rendering.
		 *
		 * @param Poll_Answer[] $answers  The answers to render.
		 * @param Poll          $poll     The current poll object.
		 */
		return apply_filters( 'dem_result_screen_answers', $answers, $this->poll );
	}

	private function result_screen_controls_html(): string {
		$poll = $this->poll; // simplify

		$html = '';

		if( $poll->open ){
			// back to voting
			$vote_btn = sprintf( '<button type="button" class="dem-button dem-vote-link dem_vote_link_js %s" data-dem-act="voteScreen">%s</button>',
				$this->options->btn_class,
				_x( 'Vote', 'front', 'democracy-poll' )
			);

			$login_required_alert = $this->registered_only_alert_html();

			// for cache
			if( $this->for_cache ){
				$html .= $poll->revote
					? $this->revote_btn_html()
					: $vote_btn;
			}
			// not for cache
			elseif( $login_required_alert ){
				$html .= $login_required_alert;
			}
			elseif( $poll->user_state->has_voted ){
				if( $poll->revote ){
					$html .= $this->revote_btn_html();
				}
			}
			else{
				$html .= $vote_btn;
			}
		}

		return $html;
	}

	protected function revote_btn_html(): string {
		return strtr( <<<'HTML'
			<span class="dem-revote-button-wrap dem_revote_button_wrap_js">
				<input class="dem-button dem-revote-button dem-revote-link {BTN_CLASS} dem_revote_button_js" type="button" value="{REVOTE}" data-dem-act="delVoted" data-confirm_text="{CONFIRM}">
			</span>
			HTML,
			[
				'{REVOTE}'    => esc_attr_x( 'Revote', 'front', 'democracy-poll' ),
				'{BTN_CLASS}' => $this->options->btn_class,
				'{CONFIRM}'   => esc_attr_x( 'Are you sure you want to cancel the votes?', 'front', 'democracy-poll' ),
			]
		);
	}

	/**
	 * Note for unregistered users.
	 */
	protected function registered_only_alert_html(): string {
		$text = $this->registered_only_notice_html();
		if( ! $text ){
			return '';
		}

		return <<<HTML
		<div class="dem-notice-inline">$text</div>
		HTML;
	}

	public function registered_only_notice_html(): string {
		if( ! $this->poll->user_state->blocked_by_not_logged ){
			return '';
		}

		$text = _x( 'Only registered users can vote. <a>Log in</a> to vote.', 'front', 'democracy-poll' );
		$login_url = wp_login_url( $_SERVER['REQUEST_URI'] );

		return str_replace( '<a', sprintf( '<a href="%s" rel="nofollow"', esc_url( $login_url ) ), $text );
	}

	/**
	 * Note: need to be in this file because of the "front" parser.
	 */
	public static function already_voted_notice_message(): string {
		return _x( 'You or your IP have already voted.', 'front', 'democracy-poll' );
	}

	protected function notice_template_html(): string {
		$close_label = esc_attr_x( 'Close', 'front', 'democracy-poll' );

		return <<<HTML
		<template class="dem_notice_template_js">
			<div class="dem-notice dem_notice_js">
				<button type="button" class="dem-notice-close dem_notice_close_js" aria-label="$close_label">&times;</button>
				<div class="dem-notice-message dem_notice_message_js"></div>
			</div>
		</template>
		HTML;
	}

	protected function get_loader_html(): string {
		$loader_fname = $this->options->loader_fname;
		if( ! $loader_fname ){
			return '';
		}

		return sprintf( '<div class="dem-loader dem_loader_js"><div>%s</div></div>',
			file_get_contents( $this->plugin->dir . "/assets/styles/loaders/" . basename( $loader_fname ) )
		);
	}

	/**
	 * Pluralizes the title based on the number.
	 *
	 * @param int    $number   The number to pluralize.
	 * @param string $titles   Comma-separated titles for singular, plural, and other cases.
	 * @param bool   $add_num  Whether to add the number before the title.
	 *
	 * @return string The pluralized title.
	 */
	protected static function pluralize( $number, string $joined_titles, $add_num = true ): string {
		$titles = explode( ',', $joined_titles );

		if( 2 === count( $titles ) ){
			$titles[2] = $titles[1];
		}

		$cases = [ 2, 0, 1, 1, 1, 2 ];
		$index = ( $number % 100 > 4 && $number % 100 < 20 ) ? 2 : $cases[ min( $number % 10, 5 ) ];

		return ( $add_num ? "$number " : '' ) . $titles[ $index ];
	}

	/**
	 * Legacy backcompat
	 */
	public function get_screen( string $show_screen = 'vote', string $before_title = '', string $after_title = '' ): string {
		return $this->render_poll( $show_screen, $before_title, $after_title );
	}

}
