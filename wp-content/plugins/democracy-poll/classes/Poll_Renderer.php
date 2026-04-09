<?php

namespace DemocracyPoll;

use DemocracyPoll\Helpers\Helpers;
use DemocracyPoll\Helpers\Kses;
use DemPoll;

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

	private \DemPoll $poll;

	public function __construct( \DemPoll $poll ) {
		$this->poll = $poll;

		if(
			$poll->open
			&& ( ! $poll->show_results || options()->dont_show_results )
			&& ( ! is_admin() || wp_doing_ajax() )
		){
			$this->not_show_results = true;
		}
	}

	/**
	 * Gets the poll HTML.
	 *
	 * @param string $show_screen  Which screen to display: vote, voted, force_vote.
	 *
	 * @return string|false HTML.
	 */
	public function get_screen( string $show_screen = 'vote', string $before_title = '', string $after_title = '' ) {
		$poll = $this->poll; // simplify

		if( ! $poll->id ){
			return false;
		}

		$this->in_archive = ( (int) ( $GLOBALS['post']->ID ?? 0 ) === (int) options()->archive_page_id ) && is_singular();

		if( $poll->voting_blocked && $show_screen !== 'force_vote' ){
			$show_screen = 'voted';
		}

		$html = '';

		$js_opts = [
			'ajax_url'         => plugin()->poll_ajax->ajax_url,
			'pid'              => $poll->id,
			'max_answs'        => (int) ( $poll->multiple ?: 0 ),
			'answs_max_height' => options()->answs_max_height,
			'anim_speed'       => options()->anim_speed,
			'line_anim_speed'  => (int) options()->line_anim_speed,
		];

		$html .= '<div id="democracy-' . $poll->id . '" class="democracy" data-opts=\'' . json_encode( $js_opts ) . '\' >';
		$html .= $before_title ?: options()->before_title;
		$html .= Kses::kses_html( $poll->question );
		$html .= $after_title ?: options()->after_title;

		// changeable part
		$html .= $this->get_screen_basis( $show_screen );
		// / changeable part

		$html .= $poll->note ? '<div class="dem-poll-note">' . wpautop( $poll->note ) . '</div>' : '';

		if( Poll_Utils::cuser_can_edit_poll( $poll ) ){
			$html .= '<a class="dem-edit-link" href="' . Poll_Utils::edit_poll_url( $poll->id ) . '" title="' . __( 'Edit poll', 'democracy-poll' ) . '"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="1.5em" height="100%" viewBox="0 0 1000 1000" enable-background="new 0 0 1000 1000" xml:space="preserve"><path d="M617.8,203.4l175.8,175.8l-445,445L172.9,648.4L617.8,203.4z M927,161l-78.4-78.4c-30.3-30.3-79.5-30.3-109.9,0l-75.1,75.1 l175.8,175.8l87.6-87.6C950.5,222.4,950.5,184.5,927,161z M80.9,895.5c-3.2,14.4,9.8,27.3,24.2,23.8L301,871.8L125.3,696L80.9,895.5z"/></svg></a>';
		}

		// copyright
		if( options()->show_copyright && ( is_home() || is_front_page() ) ){
			$html .= '<a class="dem-copyright" href="http://wp-kama.ru/?p=67" target="_blank" rel="noopener" title="' . __( 'Download the Democracy Poll', 'democracy-poll' ) . '" onmouseenter="var $el = jQuery(this).find(\'span\'); $el.stop().animate({width:\'toggle\'},200); setTimeout(function(){ $el.stop().animate({width:\'toggle\'},200); }, 4000);"> © <span style="display:none;white-space:nowrap;">Kama</span></a>';
		}

		// loader
		if( options()->loader_fname ){
			static $loader; // оптимизация, чтобы один раз выводился код на странице
			if( ! $loader ){
				$loader = '<div class="dem-loader"><div>' . file_get_contents( plugin()->dir . '/styles/loaders/' . options()->loader_fname ) . '</div></div>';
				$html .= $loader;
			}
		}

		$html .= "</div><!--democracy-->";

		// for page cache
		// never use a poll caching mechanism in admin
		if( ! $this->in_archive && ! is_admin() && plugin()->is_cachegear_on ){
			$html .= $this->get_cache_screens();
		}

		if( ! options()->disable_js ){
			Poll_Utils::enqueue_js_once();
		}

		return Poll_Utils::get_minified_styles_once() . $html;
	}

	protected function get_cache_screens(): string {
		$poll = $this->poll; // simplify

		$saved_voted_for = $poll->voted_for;
		$poll->voted_for = '';
		$this->for_cache = true;

		$html = '';
		// voted screen
		if( ! $this->not_show_results ){
			$html .= self::minify_html( $this->get_screen_basis( 'voted' ) );
		}

		// vote screen
		if( $poll->open ){
			$html .= self::minify_html( $this->get_screen_basis( 'force_vote' ) );
		}

		$this->for_cache = false;
		$poll->voted_for = $saved_voted_for;

		$is_keep_logs = options()->keep_logs ? 1 : 0;
		return <<<HTML
			<!--noindex-->
			<div class="dem-cache-screens" style="display:none;" data-opt_logs="$is_keep_logs">$html</div>
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
		$class_suffix = $this->for_cache ? '-cache' : '';

		if( $this->not_show_results ){
			$show_screen = 'force_vote';
		}

		$screen = ( $show_screen === 'vote' || $show_screen === 'force_vote' ) ? 'vote' : 'voted';

		$html = '<div class="dem-screen' . $class_suffix . ' ' . $screen . '">';
		$html .= ( $screen === 'vote' )
			? $this->get_vote_screen()
			: $this->get_result_screen();
		$html .= '</div>';

		if( ! $this->for_cache ){
			$html .= '<noscript>Poll Options are limited because JavaScript is disabled in your browser.</noscript>';
		}

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

		$auto_vote_on_select = ( ! $poll->multiple && $poll->revote && options()->hide_vote_button );

		$lis_html = '';
		foreach( $poll->answers as $answer ){
			/** @var Poll_Answer $answer */
			/**
			 * Allows to modify the answer object before it will be processed for output.
			 *
			 * @param Poll_Answer $answer The answer object.
			 */
			$answer = apply_filters( 'dem_vote_screen_answer', $answer );

			$checked = '';
			if( in_array( $answer->aid, explode( ',', $poll->voted_for ), true ) ){
				$checked = ' checked="checked"';
			}

			$lis_html .= strtr( <<<'HTML'
				<li data-aid="{AID}">
					<label class="dem__{TYPE}_label">
						<input class="dem__{TYPE}" {AUTO_VOTE} type="{TYPE}" value="{AID}" name="answer_ids[]" {CHECKED} {DISABLED}><span class="dem__spot"></span> {ANSWER}
					</label>
				</li>
				HTML,
				[
					'{AID}'       => $answer->aid,
					'{TYPE}'      => $poll->multiple ? 'checkbox' : 'radio',
					'{AUTO_VOTE}' => $auto_vote_on_select ? 'data-dem-act="vote"' : '',
					'{CHECKED}'   => $checked,
					'{DISABLED}'  => $poll->voted_for ? 'disabled="disabled"' : '',
					'{ANSWER}'    => $answer->answer,
				]
			);
		}

		if( $poll->democratic && ! $poll->voting_blocked ){
			$lis_html .= '<li class="dem-add-answer"><a href="javascript:void(0);" rel="nofollow" data-dem-act="newAnswer" class="dem-link">' . _x( 'Add your answer', 'front', 'democracy-poll' ) . '</a></li>';
		}

		$bottom_html  = '<div class="dem-bottom">';
		$bottom_html .= '<input type="hidden" name="dem_act" value="vote">';
		$bottom_html .= '<input type="hidden" name="dem_pid" value="' . $poll->id . '">';

		$voted_btn = '<div class="dem-voted-button"><input class="dem-button ' . options()->btn_class . '" type="submit" value="' . _x( 'Already voted...', 'front', 'democracy-poll' ) . '" disabled="disabled"></div>';
		$vote_btn = '<div class="dem-vote-button"><input class="dem-button ' . options()->btn_class . '" type="submit" value="' . _x( 'Vote', 'front', 'democracy-poll' ) . '" data-dem-act="vote"></div>';

		if( $auto_vote_on_select ){
			$vote_btn = '';
		}

		$for_users_alert = $poll->blocked_by_not_logged ? '<div class="dem-only-users">' . self::registered_only_alert_text() . '</div>' : '';

		// add for cache
		if( $this->for_cache ){
			$bottom_html .= self::voted_notice_html();

			if( $for_users_alert ){
				$bottom_html .= str_replace(
					[ '<div', 'class="' ], [ '<div style="display:none;"', 'class="dem-notice ' ], $for_users_alert
				);
			}

			if( $poll->revote ){
				$bottom_html .= preg_replace( '/(<[^>]+)/', '$1 style="display:none;"', $this->revote_btn_html(), 1 );
			}
			else{
				$bottom_html .= substr_replace( $voted_btn, '<div style="display:none;"', 0, 4 );
			}
			$bottom_html .= $vote_btn;
		}
		// not for cache
		else{
			if( $for_users_alert ){
				$bottom_html .= $for_users_alert;
			}
			else{
				if( $poll->has_voted ){
					$bottom_html .= $poll->revote ? $this->revote_btn_html() : $voted_btn;
				}
				else{
					$bottom_html .= $vote_btn;
				}
			}
		}

		if( ! $this->not_show_results && ! options()->dont_show_results_link ){
			$bottom_html .= '<a href="javascript:void(0);" class="dem-link dem-results-link" data-dem-act="view" rel="nofollow">' . _x( 'Results', 'front', 'democracy-poll' ) . '</a>';
		}

		$bottom_html .= '</div>'; // dem-bottom

		$html = <<<HTML
		<form method="POST" action="#democracy-$poll->id">
			<ul class="dem-vote">
				$lis_html
			</ul>
			$bottom_html
		</form>
		HTML;

		/**
		 * Allows to modify the vote screen HTML before it is returned.
		 *
		 * @param string $html  The HTML of the vote screen.
		 * @param DemPoll $poll The current poll object.
		 */
		return apply_filters( 'dem_vote_screen', $html, $poll );
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

		// sort by votes
		$answers = Helpers::objects_array_sort( $poll->answers, [ 'votes' => 'desc' ] );

		$max = $total = 0;

		foreach( $answers as $answer ){
			/** @var Poll_Answer $answer */
			$total += $answer->votes;
			if( $max < $answer->votes ){
				$max = $answer->votes;
			}
		}

		$voted_class = 'dem-voted-this';
		$voted_txt = _x( 'This is Your vote.', 'front', 'democracy-poll' );

		$lis_html = '';
		foreach( $answers as $answer ){
			/**
			 * Allows to modify the answer object before it is processed for output.
			 *
			 * @param Poll_Answer $answer The answer object.
			 */
			$answer = apply_filters( 'dem_result_screen_answer', $answer );

			$is_voted_this = ( $poll->has_voted && in_array( (string) $answer->aid, explode( ',', $poll->voted_for ), true ) );
			$is_winner     = ( $max === $answer->votes );
			$novoted_class = $answer->votes ? '' : ' dem-novoted';
			$li_class      = trim( ( $is_winner ? 'dem-winner' : '' ) . ( $is_voted_this ? " $voted_class" : '' ) . $novoted_class );
			$li_class_attr = $li_class ? " class=\"$li_class\"" : '';

			$mark = $answer->added_by
				? '<sup class="dem-star" title="' . _x( 'The answer was added by a visitor', 'front', 'democracy-poll' ) . '">*</sup>'
				: '';

			$percent = ( $answer->votes > 0 ) ? round( $answer->votes / $total * 100 ) : 0;

			$percent_txt = sprintf(
				_x( '%s - %s%% of all votes', 'front', 'democracy-poll' ),
				self::pluralize( $answer->votes, _x( 'vote,votes,votes', 'front', 'democracy-poll' ) ),
				$percent
			);

			$title = trim( ( $is_voted_this ? $voted_txt : '' ) . ' ' . $percent_txt );
			$title_attr = 'title="' . esc_attr( $title ) . '"';

			$votes_txt = $answer->votes . ' ' . '<span class="votxt">' . self::pluralize( $answer->votes, _x( 'vote,votes,votes', 'front', 'democracy-poll' ), false ) . '</span>';
			$label_perc_txt = ' <span class="dem-label-percent-txt">' . $percent . '%, ' . $votes_txt . '</span>';

			$graph_percent = ( ! options()->graph_from_total && $percent ) ? round( $answer->votes / $max * 100 ) : $percent;
			$graph_percent = $graph_percent ? "$graph_percent%" : '1px';
			$width_attr    = options()->line_anim_speed
				? 'data-width="' . $graph_percent . '"'
				: 'style="width:' . $graph_percent . '"';
			$percent_html  = $percent ? "<span class=\"dem-votes-txt-percent\">{$percent}%</span>" : '';

			$answer_text = $answer->answer . $mark;

			$lis_html .= <<<HTML
			<li $li_class_attr $title_attr data-aid="$answer->aid">
				<div class="dem-label">$answer_text $label_perc_txt</div>
				<div class="dem-graph">
					<div class="dem-fill" $width_attr></div>
					<div class="dem-votes-txt">
						<span class="dem-votes-txt-votes">$votes_txt</span>
						$percent_html
					</div>
					<div class="dem-percent-txt">$percent_txt</div>
				</div>
			</li>
			HTML;
		}

		$ul_html = <<<HTML
		<ul class="dem-answers" data-voted-class="$voted_class" data-voted-txt="$voted_txt">
			$lis_html
		</ul>
		HTML;

		$total_votes_txt = sprintf( _x( 'Total Votes: %s', 'front', 'democracy-poll' ), $total );
		$begin_title     = esc_attr( _x( 'Begin', 'front', 'democracy-poll' ) );
		$end_title       = esc_attr( _x( 'End', 'front', 'democracy-poll' ) );
		$begin_date_txt  = date_i18n( get_option( 'date_format' ), $poll->added );
		$end_date_txt    = date_i18n( get_option( 'date_format' ), $poll->end );
		$end_date = $poll->end
			? ' - <span class="dem-end-date" title="' . $end_title . '">' . $end_date_txt . '</span>'
			: '';
		$voters_txt = sprintf( _x( 'Voters: %s', 'front', 'democracy-poll' ), $poll->users_voted );
		$voters_div = $poll->multiple
			? '<div class="dem-users-voted">' . $voters_txt . '</div>'
			: '';
		$added_by_div = ( $answer->added_by ?? 0 )
			? '<div class="dem-added-by-user"><span class="dem-star">*</span>' . _x( ' - added by visitor', 'front', 'democracy-poll' ) . '</div>'
			: '';
		$closed_div   = ! $poll->open
			? '<div>' . _x( 'Voting is closed', 'front', 'democracy-poll' ) . '</div>'
			: '';
		$archive_link = ( ! $this->in_archive && options()->archive_page_id )
			? '<a class="dem-archive-link dem-link" href="' . get_permalink( options()->archive_page_id ) . '" rel="nofollow">' . _x( 'Polls Archive', 'front', 'democracy-poll' ) . '</a>'
			: '';

		$controls_html = $this->result_screen_controls_html();

		$bottom_html = <<<HTML
		<div class="dem-bottom">
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
		</div>
		HTML;

		$html = "$ul_html\n$bottom_html";

		/**
		 * Allows to modify result screen HTML before it is returned.
		 *
		 * @param string  $html The HTML of the result screen.
		 * @param DemPoll $poll The current poll object.
		 */
		return apply_filters( 'dem_result_screen', $html, $this->poll );
	}

	private function result_screen_controls_html(): string {
		$poll = $this->poll; // simplify

		$html = '';

		if( $poll->open ){
			// note for unregistered users
			$for_users_alert = $poll->blocked_by_not_logged
				? '<div class="dem-only-users">' . self::registered_only_alert_text() . '</div>'
				: '';

			// back to voting
			$vote_btn = sprintf( '<button type="button" class="dem-button dem-vote-link %s" data-dem-act="vote_screen">%s</button>',
				options()->btn_class,
				_x( 'Vote', 'front', 'democracy-poll' )
			);

			// for cache
			if( $this->for_cache ){
				$html .= self::voted_notice_html();

				if( $for_users_alert ){
					$html .= str_replace( [ '<div', 'class="' ], [
						'<div style="display:none;"',
						'class="dem-notice ',
					], $for_users_alert );
				}

				if( $poll->revote ){
					$html .= $this->revote_btn_html();
				}
				else{
					$html .= $vote_btn;
				}
			}
			// not for cache
			else{
				if( $for_users_alert ){
					$html .= $for_users_alert;
				}
				else{
					if( $poll->has_voted ){
						if( $poll->revote ){
							$html .= $this->revote_btn_html();
						}
					}
					else{
						$html .= $vote_btn;
					}
				}
			}
		}

		return $html;
	}

	protected function revote_btn_html(): string {
		return strtr( <<<'HTML'
			<span class="dem-revote-button-wrap">
			<form action="#democracy-{POLL_ID}" method="POST">
				<input type="hidden" name="dem_act" value="delVoted">
				<input type="hidden" name="dem_pid" value="{POLL_ID}">
				<input type="submit" value="{REVOTE}" class="dem-revote-link dem-revote-button dem-button {BTN_CLASS}" data-dem-act="delVoted" data-confirm-text="{CONFIRM}">
			</form>
			</span>
			HTML,
			[
				'{POLL_ID}'  => $this->poll->id,
				'{REVOTE}'   => _x( 'Revote', 'front', 'democracy-poll' ),
				'{BTN_CLASS}'=> options()->btn_class,
				'{CONFIRM}'  => _x( 'Are you sure you want cancel the votes?', 'front', 'democracy-poll' ),
			]
		);
	}

	/**
	 * Note: you have already voted
	 */
	public static function voted_notice_html( $msg = '' ): string {
		$js = <<<'JS'
			let el = this.parentElement; el.animate([{ opacity:1 }, { opacity:0 }], { duration:300 }).onfinish = () => { el.style.display = 'none' }
			JS;

		if( ! $msg ){
			return strtr( <<<'HTML'
				<div class="dem-notice dem-youarevote" style="display:none;">
					<div class="dem-notice-close" onclick="{JS}">&times;</div>
					{MESSAGE}
				</div>
				HTML,
				[ '{JS}' => esc_attr( $js ), '{MESSAGE}' => _x( 'You or your IP had already vote.', 'front', 'democracy-poll' ) ]
			);
		}

		return strtr( <<<'HTML'
				<div class="dem-notice">
					<div class="dem-notice-close" onclick="{JS}">&times;</div>
					{MESSAGE}
				</div>
				HTML,
			[ '{JS}' => esc_attr( $js ), '{MESSAGE}' => $msg ]
		);
	}

	protected static function registered_only_alert_text(): string {
		$text = _x( 'Only registered users can vote. <a>Login</a> to vote.', 'front', 'democracy-poll' );
		$login_url = wp_login_url( $_SERVER['REQUEST_URI'] );

		return str_replace( '<a', sprintf( '<a href="%s" rel="nofollow"', esc_url( $login_url ) ), $text );
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
	protected static function pluralize( $number, $titles, $add_num = true ): string {
		$titles = explode( ',', $titles );

		if( 2 === count( $titles ) ){
			$titles[2] = $titles[1];
		}

		$cases = [ 2, 0, 1, 1, 1, 2 ];

		return ( $add_num ? "$number " : '' ) . $titles[ ( $number % 100 > 4 && $number % 100 < 20 ) ? 2 : $cases[ min( $number % 10, 5 ) ] ];
	}

	protected static function minify_html( string $html ): string {
		$html = preg_replace( '~\s+~u', ' ', $html ); // remove extra spaces
		$html = preg_replace( "~[\n\r\t]~u", '', $html ); // remove new lines and tabs

		return $html;
	}

}
