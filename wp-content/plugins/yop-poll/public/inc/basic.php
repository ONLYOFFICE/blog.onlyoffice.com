<?php
class YOP_Poll_Basic {
	public static function search_array($value, $key, $array) {
		foreach ( $array as $k => $val ) {
			if ( $val[$key] == $value ) {
				return $val;
			}
		}
		return null;
	}
	public static function generate_uid() {
		return md5( uniqid( rand(), true ) );
	}
	public static function is_allow_multiple_answers( $setting ) {
		$answers_type = '';
		if ( 'yes' === $setting ) {
			$answers_type = 'checkbox';
		} else {
			$answers_type = 'radio';
		}
		return $answers_type;
	}
	public static function is_answer_default( $setting) {
		$answer_selected = '';
		if ( 'yes' === $setting ) {
			$answer_selected = 'checked';
		} else {
			$answer_selected = '';
		}
		return $answer_selected;
	}
	public static function is_answer_link( $answer ) {
		$answer_text = '';
		$answer_url = '';
		if ( 'yes' === $answer->meta_data['makeLink'] ) {
			$answer_url = filter_var( $answer->meta_data['link'], FILTER_SANITIZE_URL );
			if ( ( '' !== $answer->meta_data['link'] ) && ( false === !filter_var( $answer_url, FILTER_VALIDATE_URL) ) ) {
				$answer_text = "<a href='{$answer_url}' target='_blank'>" . esc_html( $answer->stext ) . "</a>";
			} else {
				$answer_text = esc_html( $answer->stext );
			}
		} else {
			$answer_text = esc_html( $answer->stext );
		}
		$answer_text = str_replace( '[br]', '<br/>', $answer_text );
		return $answer_text;
	}
	public static function get_answers_count( $question ) {
		$answers_count = count( $question->answers );
		if ( 'yes' === $question->meta_data['allowOtherAnswers'] ) {
			$answers_count++;
		}
		return $answers_count;
	}
	public static function get_gdpr_html( $poll ) {
		$gdpr_html = '';
		if ( 'yes' === $poll->meta_data['options']['poll']['enableGdpr'] ) {
			if ( 'consent' === $poll->meta_data['options']['poll']['gdprSolution'] ) {
				$gdpr_html = '<div class="basic-gdpr">'
					. '<input type="checkbox" name="gdpr-consent" id="gdpr-consent" class="gdpr-consent" value="agree">'
					. '<label class="basic-gdpr-consent-text" for="gdpr-consent">' . $poll->meta_data['options']['poll']['gdprConsentText'] . '</label>'
					. '</div>';
			}
		}
		return $gdpr_html;
	}
	public static function has_captcha( $poll, $params ) {
		$use_captcha = array();
		$uid = self::generate_uid();
		if ( ( true === isset( $params['show_results'] ) ) && ( '1' === $params['show_results'] ) ) {
			$use_captcha[0] = '0';
			$use_captcha[1] = '';
			$use_captcha[2] = $uid;
		} else {
			switch ( $poll->meta_data['options']['poll']['useCaptcha'] ) {
				case 'yes': {
					$use_captcha[0] = '1';
					$use_captcha[1] = '<div id="yop-poll-captcha-' . $uid . '" class="basic-captcha"></div>';
					$use_captcha[2] = $uid;
					break;
				}
				case 'yes-recaptcha': {
					$use_captcha[0] = '2';
					$use_captcha[1] = '<div id="yop-poll-captcha-' . $uid . '" class="basic-captcha"></div>';
					$use_captcha[2] = $uid;
					break;
				}
				case 'yes-recaptcha-invisible': {
					$use_captcha[0] = '3';
					$use_captcha[1] = '<div id="yop-poll-captcha-' . $uid . '" class="basic-captcha"></div>';
					$use_captcha[2] = $uid;
					break;
				}
				default: {
					$use_captcha[0] = '0';
					$use_captcha[1] = '';
					$use_captcha[2] = $uid;
					break;
				}
			}
		}
		return $use_captcha;
	}
	public static function do_show_results_link( $poll ) {
		$poll_show_results_link = '';
		if ( 'yes' === $poll->meta_data['options']['poll']['showResultsLink'] ) {
			$poll_show_results_link = '<a href="#" class="button basic-results-button" style="'
											. 'background:' . esc_attr( $poll->meta_data['style']['buttons']['backgroundColor'] ) . ';'
											. ' border:' . esc_attr( $poll->meta_data['style']['buttons']['borderSize'] ) . 'px;'
											. ' border-style: solid;'
											. ' border-color:' . esc_attr( $poll->meta_data['style']['buttons']['borderColor'] ) . ';'
											. ' border-radius:' . esc_attr( $poll->meta_data['style']['buttons']['borderRadius'] ) . 'px;'
											. ' padding:' . esc_attr( $poll->meta_data['style']['buttons']['paddingTopBottom'] ) . 'px '
												. esc_attr( $poll->meta_data['style']['buttons']['paddingLeftRight'] ) . 'px;'
											. ' color:' . esc_attr( $poll->meta_data['style']['buttons']['textColor'] ) . ';'
											. ' font-size:' . esc_attr( $poll->meta_data['style']['buttons']['textSize'] ) . 'px;'
											. ' font-weight:' . esc_attr( $poll->meta_data['style']['buttons']['textWeight'] ) . ';'
										. '">'
											. $poll->meta_data['options']['poll']['resultsLabelText']
										. '</a>';
		}
		return $poll_show_results_link;
	}
	public static function do_show_back_to_vote_link( $poll ) {
		$poll_show_back_to_vote_link = '';
		if ( 'yes' === $poll->meta_data['options']['results']['backToVoteOption'] ) {
			$poll_show_back_to_vote_link = '<button class="basic-back-to-vote-button" style="'
											. 'background:' . esc_attr( $poll->meta_data['style']['buttons']['backgroundColor'] ) . ';'
											. ' border:' . esc_attr( $poll->meta_data['style']['buttons']['borderSize'] ) . 'px;'
											. ' border-style: solid;'
											. ' border-color:' . esc_attr( $poll->meta_data['style']['buttons']['borderColor'] ) . ';'
											. ' border-radius:' . esc_attr( $poll->meta_data['style']['buttons']['borderRadius'] ) . 'px;'
											. ' padding:' . esc_attr( $poll->meta_data['style']['buttons']['paddingTopBottom'] ) . 'px '
												. esc_attr( $poll->meta_data['style']['buttons']['paddingLeftRight'] ) . 'px;'
											. ' color:' . esc_attr( $poll->meta_data['style']['buttons']['textColor'] ) . ';'
											. ' font-size:' . esc_attr( $poll->meta_data['style']['buttons']['textSize'] ) . 'px;'
											. ' font-weight:' . esc_attr( $poll->meta_data['style']['buttons']['textWeight'] ) . ';'
											. ' display:none;'
										. '">'
											. $poll->meta_data['options']['results']['backToVoteCaption']
										. '</button>';
		}
		return $poll_show_back_to_vote_link;
	}
	public static function do_show_total_votes_and_answers( $poll, $params ) {
		$total_votes_and_answers = '';
		$messages = YOP_Poll_Settings::get_messages();
		$total_submits_text = '';
		$total_submited_answers_text = '';
		if ( 0 === (int)$poll->total_submits ) {
			$total_submits_text = $messages['results']['multiple-votes'];
		}
		if (  ( '' === $total_submits_text ) && ( 1 == (int)$poll->total_submits ) ) {
			$total_submits_text = $messages['results']['single-vote'];
		}
		if (  ( '' === $total_submits_text ) && ( (int)$poll->total_submits > 1 ) ) {
			$total_submits_text = $messages['results']['multiple-votes'];
		}
		if ( 0 === (int)$poll->total_submited_answers ) {
			$total_submited_answers_text = $messages['results']['multiple-answers'];
		}
		if ( ( '' === $total_submited_answers_text ) && ( 1 == (int)$poll->total_submited_answers ) ) {
			$total_submited_answers_text = $messages['results']['single-answer'];
		}
		if ( ( '' === $total_submited_answers_text ) && ( (int)$poll->total_submited_answers > 1 ) ) {
			$total_submited_answers_text = $messages['results']['multiple-answers'];
		}
		if (
			( 'yes' === $poll->meta_data['options']['poll']['showTotalVotes'] ) &&
			( 'yes' === $poll->meta_data['options']['poll']['showTotalAnswers'] )
			) {
				$total_votes_and_answers = '<div class="basic-stats text-center">'
												. '<span class="basic-stats-votes">'
													. '<span class="basic-stats-votes-number">'
														. $poll->total_submits
													. '</span>'
													. '<span class="basic-stats-votes-text">'
															. '&nbsp;' . $total_submits_text
													. '</span>'
												. '</span>'
												. '<span class="basic-stats-separator">'
													. '&nbsp;&middot;&nbsp;'
												. '</span>'
												. '<span class="basic-stats-answers">'
													. '<span class="basic-stats-answers-number">'
														. $poll->total_submited_answers
													. '</span>'
													. '<span class="basic-stats-answers-text">'
															. '&nbsp;' . $total_submited_answers_text
													. '</span>'
												. '</span>'
											. '</div>';
			} else if ( 'yes' === $poll->meta_data['options']['poll']['showTotalVotes'] ) {
				$total_votes_and_answers = '<div class="basic-stats text-center">'
												. '<span class="basic-stats-votes">'
													. '<span class="basic-stats-votes-number">'
														. $poll->total_submits
													. '</span>'
													. '<span class="basic-stats-votes-text">'
														. '&nbsp;' . $total_submits_text
													. '</span>'
												. '</span>'
											. '</div>';
			} else if ( 'yes' === $poll->meta_data['options']['poll']['showTotalAnswers'] ) {
				$total_votes_and_answers = '<div class="basic-stats text-center">'
												. '<span class="basic-stats-answers">'
													. '<span class="basic-stats-answers-number">'
														. $poll->total_submited_answers
													. '</span>'
													. '<span class="basic-stats-answers-text">'
														. '&nbsp;' . $total_submited_answers_text
													. '</span>'
												. '</span>'
											. '</div>';
			}
			return $total_votes_and_answers;
	}
	public static function do_anonymous_vote( $poll ) {
		$anonymous_vote_code = '';
		if ( 1 < count( $poll->meta_data['options']['access']['votePermissions'] ) ) {
			if (true === in_array( 'guest', $poll->meta_data['options']['access']['votePermissions'] ) ) {
				$messages = YOP_Poll_Settings::get_messages();
				$anonymous_vote_code .= '<div class="basic-anonymous">'
									. '<button type="button" class="btn btn-default">'
										. '<i class="fa fa-user-secret" aria-hidden="true"></i>'
										. $messages['buttons']['anonymous']
									. '</button>'
								. '</div>';
			}
		}
		return $anonymous_vote_code;
	}
	public static function do_wordpress_vote( $poll ) {
		$wordpress_vote_code = '';
		if ( 1 < count( $poll->meta_data['options']['access']['votePermissions'] ) ) {
			if (true === in_array( 'wordpress', $poll->meta_data['options']['access']['votePermissions'] ) ) {
				$messages = YOP_Poll_Settings::get_messages();
				$wordpress_vote_code .= '<div class="basic-wordpress">'
										. '<button type="button" class="btn btn-default">'
											. $messages['buttons']['wordpress']
										. '</button>'
									. '</div>';
			}
		}
		return $wordpress_vote_code;
	}
	public static function do_text_question( $element, $poll_meta_data, $params ) {
		$element_answers = '';
		switch ( $element->meta_data['answersDisplay'] ) {
			case 'vertical': {
				$element_class_type = 'basic-question-text-vertical';
				$element_answers = self::do_vertical_text( $element, $poll_meta_data, $params );
				break;
			}
			case 'horizontal': {
				$element_class_type = 'basic-question-text-horizontal';
				$element_answers = self::do_horizontal_text( $element, $poll_meta_data, $params );
				break;
			}
			case 'columns': {
				$element_class_type = 'basic-question-text-columns';
				$element_answers = self::do_columns_text( $element, $poll_meta_data, $params );
				break;
			}
			default: {
				$element_class_type = 'basic-question-text-vertical';
				$element_answers = self::do_vertical_text( $element, $poll_meta_data, $params );
				break;
			}
		}
		if ( ( 'yes' === $element->meta_data['allowOtherAnswers'] ) && ( 'yes' === $element->meta_data['displayOtherAnswersInResults'] ) ) {
			$display_other_answers_in_results = 'yes';
			$other_answers_results_color = isset( $element->meta_data['resultsColorForOtherAnswers'] ) ? $element->meta_data['resultsColorForOtherAnswers'] : '#000000';
			$other_answers = YOP_Poll_Other_Answers::get_for_element( $element->id );
			$other_answers_processed = array();
			$other_answers_as_string = '';
			if ( null !== $other_answers ) {
				foreach ( $other_answers as $other_answer ) {
					$other_answers_processed[] = array(
						'an' => $other_answer->answer,
						'vn' => $other_answer->total_submits
					);
				}
				if ( count( $other_answers_processed ) > 0 ) {
					$other_answers_as_string = json_encode( $other_answers_processed );
				}
			}
		} else {
			$display_other_answers_in_results = 'no';
			$other_answers_as_string = '';
			$other_answers_results_color = '';
		}
		$element_code = '<div class="basic-element basic-question ' . $element_class_type . '" '
							. 'data-id="' . esc_attr( $element->id ) . '"'
							. ' data-uid="' . esc_attr( self::generate_uid() ) . '"'
		                    . ' data-type="question"'
							. ' data-question-type="text"'
							. ' data-min="' . esc_attr( $element->meta_data['multipleAnswersMinim'] ) . '"'
							. ' data-max="' . esc_attr( $element->meta_data['multipleAnswersMaxim'] ) . '"'
		                    . ' data-display="' .esc_attr( $element->meta_data['answersDisplay'] ) . '"'
							. ' data-colnum="' . esc_attr( $element->meta_data['answersColumns']) . '"'
							. ' data-display-others="' . $display_other_answers_in_results . '"'
							. ' data-others-color="' . $other_answers_results_color . '"'
							. ' data-others=\'' . $other_answers_as_string . '\''
						. '>'
						. '<div class="basic-question-title">'
								. '<h5 style="'
									. 'color:' . esc_html( $poll_meta_data['style']['questions']['textColor'] ) . ';'
									. ' font-size:' . esc_html( $poll_meta_data['style']['questions']['textSize'] ) . 'px;'
									. ' font-weight:' . esc_html( $poll_meta_data['style']['questions']['textWeight'] ) . ';'
									. ' text-align:' . esc_html( $poll_meta_data['style']['questions']['textAlign'] ) . ';'
									. '">'
									. esc_html( $element->etext )
								. '</h5>'
						. '</div>'
						. $element_answers
					. '</div>'
					. '<div class="clearfix"></div>';
		return $element_code;
	}
	public static function do_vertical_text( $element, $poll_meta_data, $params ) {
		$answers_type = '';
		$answer_text = '';
		$answer_selected = '';
		$element_answers = '<ul class="basic-answers">';
		$answers_type = self::is_allow_multiple_answers( $element->meta_data['allowMultipleAnswers'] );
		foreach ( $element->answers as $answer ) {
			$answer_selected = self::is_answer_default( $answer->meta_data['makeDefault'] );
			$answer_text = self::is_answer_link( $answer );
			$element_answers .= '<li class="basic-answer" style="'
								. 'padding:' . esc_attr( $poll_meta_data['style']['answers']['paddingTopBottom'] ) . 'px '
									. esc_attr( $poll_meta_data['style']['answers']['paddingLeftRight'] ) . 'px;"'
								. ' data-id="' . esc_attr( $answer->id ) . '"'
								. ' data-type="' . $answer->stype . '"'
								. ' data-vn="' . $answer->total_submits . '"'
								. ' data-color="' . $answer->meta_data['resultsColor'] . '"'
								. ' data-make-link="' . ( isset( $answer->meta_data['makeLink'] ) ? $answer->meta_data['makeLink'] : '' ) . '"'
								. ' data-link="' . ( isset( $answer->meta_data['link'] ) ? $answer->meta_data['link'] : '' ) . '"'
								. '>'
								. '<div class="basic-answer-content basic-text-vertical">'
									. '<input type="' . $answers_type . '" id="answer[' . $answer->id . ']" name="answer[' . $element->id . ']" value="' . esc_attr( $answer->id ) . '"' . $answer_selected . '>'
									. '<label for="answer[' . $answer->id . ']" class="basic-answer-label">'
										. '<span class="basic-text" style="'
											. 'color: ' . esc_attr( $poll_meta_data['style']['answers']['textColor'] ) . '; '
											. 'font-size: ' . esc_attr( $poll_meta_data['style']['answers']['textSize'] ) . 'px; '
											. 'font-weight: ' . esc_attr( $poll_meta_data['style']['answers']['textWeight'] ) . ';'
										. '">'
											. $answer_text
										. '</span>'
									. '</label>'
								. '</div>'
							. '</li>';
		}
		if ( 'yes' === $element->meta_data['allowOtherAnswers'] ) {
			$element_answers .= '<li class="basic-answer basic-other-answer" style="'
									. 'padding:' . esc_attr( $poll_meta_data['style']['answers']['paddingTopBottom'] ) . 'px '
									. esc_attr( $poll_meta_data['style']['answers']['paddingLeftRight'] ) . 'px;"'
									. ' data-id="' . esc_attr( $answer->id ) . '"'
								. '>'
									. '<div class="basic-answer-content basic-text-vertical">'
										. '<input type="' . $answers_type . '" id="answer[' . $element->id . '][0]" name="answer[' . $element->id . ']" value="0"' . $answer_selected . '>'
										. '<label for="answer[' . $element->id . '][0]" class="basic-answer-label">'
											. '<span class="basic-text" style="'
												. 'color: ' . esc_attr( $poll_meta_data['style']['answers']['textColor'] ) . '; '
												. 'font-size: ' . esc_attr( $poll_meta_data['style']['answers']['textSize'] ) . 'px; '
												. 'font-weight: ' . esc_attr( $poll_meta_data['style']['answers']['textWeight'] ) . ';'
											. '">'
												. esc_html( $element->meta_data['otherAnswersLabel'] )
											. '</span>'
										. '</label>'
									. '</div>'
									. '<div class="col-md-6 col-sm-6 col-xs-12">'
										. '<input class="basic-input-text form-control" type="text" name="other[' . $element->id . ']" data-type="other-answer">'
									. '</div>'
							. '</li>';
		}
		$element_answers .= '</ul>';
		return $element_answers;
	}
	public static function get_class_for_columns( $answers_count ) {
		$class_def = '';
		switch ( $answers_count ) {
			case '2': {
				$class_def = 'col-xs-6 col-sm-6 col-md-6';
				break;
			}
			case '3': {
				$class_def = 'col-xs-6 col-sm-6 col-md-4';
				break;
			}
			case '4': {
				$class_def = 'col-xs-6 col-sm-6 col-md-3';
				break;
			}
			case '5': {
				$class_def = 'col-xs-6 col-sm-6 cols-5';
				break;
			}
			case '6': {
				$class_def = 'col-xs-6 col-sm-6 col-md-2';
				break;
			}
			case '7': {
				$class_def = 'col-xs-6 col-sm-6 cols-7';
				break;
			}
			case '8': {
				$class_def = 'col-xs-6 col-sm-6 cols-8';
				break;
			}
			case '9': {
				$class_def = 'col-xs-6 col-sm-6 cols-9';
				break;
			}
			case '10': {
				$class_def = 'col-xs-6 col-sm-6 cols-10';
				break;
			}
			case '11': {
				$class_def = 'col-xs-6 col-sm-6 cols-11';
				break;
			}
			case '12': {
				$class_def = 'col-xs-6 col-sm-6 col-md-1';
				break;
			}
		}
		return $class_def;
	}
	public static function do_horizontal_text( $element, $poll_meta_data, $params ) {
		$answers_type = '';
		$answer_text = '';
		$answer_selected = '';
		$answers_class = self::get_class_for_columns( self::get_answers_count( $element ) );
		$answers_type = self::is_allow_multiple_answers( $element->meta_data['allowMultipleAnswers'] );
		$element_answers = '<ul class="basic-answers basic-h-answers">';
		foreach ( $element->answers as $answer ) {
			$answer_selected = self::is_answer_default( $answer->meta_data['makeDefault'] );
			$answer_text = self::is_answer_link( $answer );
			$element_answers .= '<li class="basic-answer ' . $answers_class . '" style="'
									. 'padding:' . esc_attr( $poll_meta_data['style']['answers']['paddingTopBottom'] ) . 'px '
										. esc_attr( $poll_meta_data['style']['answers']['paddingLeftRight'] ) . 'px;"'
									. ' data-id="' . esc_attr( $answer->id ) . '"'
									. ' data-type="' . $answer->stype . '"'
									. ' data-vn="' . $answer->total_submits . '"'
									. ' data-color="' . $answer->meta_data['resultsColor'] . '"'
									. ' data-make-link="' . ( isset( $answer->meta_data['makeLink'] ) ? $answer->meta_data['makeLink'] : '' ) . '"'
									. ' data-link="' . ( isset( $answer->meta_data['link'] ) ? $answer->meta_data['link'] : '' ) . '"'
								. '>'
									. '<div class="basic-answer-content basic-text-horizontal">'
										. '<input type="' . $answers_type . '" id="answer[' . $answer->id . ']" name="answer[' . $element->id . ']" value="' . esc_attr( $answer->id ) . '"' . $answer_selected . '>'
										. '<label for="answer[' . $answer->id . ']" class="basic-answer-label">'
											. '<span class="basic-text" style="'
												. 'color: ' . esc_attr( $poll_meta_data['style']['answers']['textColor'] ) . '; '
												. 'font-size: ' . esc_attr( $poll_meta_data['style']['answers']['textSize'] ) . 'px; '
												. 'font-weight: ' . esc_attr( $poll_meta_data['style']['answers']['textWeight'] ) . ';'
											. '">'
												. $answer_text
											. '</span>'
										. '</label>'
									. '</div>'
								. '</li>';
		}
		if ( 'yes' === $element->meta_data['allowOtherAnswers'] ) {
			$element_answers .= '<li class="basic-answer basic-other-answer ' . $answers_class . '" style="'
									. 'padding:' . esc_attr( $poll_meta_data['style']['answers']['paddingTopBottom'] ) . 'px '
										. esc_attr( $poll_meta_data['style']['answers']['paddingLeftRight'] ) . 'px;"'
								. '>'
									. '<div class="basic-answer-content basic-text-horizontal">'
										. '<input type="' . $answers_type . '" id="answer[' . $element->id . '][0]" name="answer[' . $element->id . ']" value="0"' . $answer_selected . '>'
										. '<label for="answer[' . $element->id . '][0]" class="basic-answer-label">'
											. '<span class="basic-text" style="'
												. 'color: ' . esc_attr( $poll_meta_data['style']['answers']['textColor'] ) . '; '
												. 'font-size: ' . esc_attr( $poll_meta_data['style']['answers']['textSize'] ) . 'px; '
												. 'font-weight: ' . esc_attr( $poll_meta_data['style']['answers']['textWeight'] ) . ';'
											. '">'
												. esc_html( $element->meta_data['otherAnswersLabel'] )
											. '</span>'
										. '</label>'
									. '</div>'
									. '<div class="col-md-6 col-sm-6 col-xs-12">'
										. '<input class="basic-input-text form-control" type="text" name="other[' . $element->id . ']" data-type="other-answer">'
									. '</div>'
							. '</li>';
		}
		$element_answers .= '</ul>';
		return $element_answers;
	}
	public static function do_columns_text( $element, $poll_meta_data, $params ) {
		$answers_type = '';
		$answer_text = '';
		$answer_selected = '';
		$answers_class = self::get_class_for_columns( $element->meta_data['answersColumns'] );
		$answers_type = self::is_allow_multiple_answers( $element->meta_data['allowMultipleAnswers'] );
		$element_answers = '<ul class="basic-answers basic-h-answers basic-cols-display">';
		foreach ( $element->answers as $answer ) {
			$answer_selected = self::is_answer_default( $answer->meta_data['makeDefault'] );
			$answer_text = self::is_answer_link( $answer );
			$element_answers .= '<li class="basic-answer ' . $answers_class . '" style="'
									. 'padding:' . esc_attr( $poll_meta_data['style']['answers']['paddingTopBottom'] ) . 'px '
										. esc_attr( $poll_meta_data['style']['answers']['paddingLeftRight'] ) . 'px;"'
									. ' data-id="' . esc_attr( $answer->id ) . '"'
									. ' data-type="' . $answer->stype . '"'
									. ' data-vn="' . $answer->total_submits . '"'
									. ' data-color="' . $answer->meta_data['resultsColor'] . '"'
									. ' data-make-link="' . ( isset( $answer->meta_data['makeLink'] ) ? $answer->meta_data['makeLink'] : '' ) . '"'
									. ' data-link="' . ( isset( $answer->meta_data['link'] ) ? $answer->meta_data['link'] : '' ) . '"'
								. '>'
									. '<div class="basic-answer-content basic-text-horizontal">'
										. '<input type="' . $answers_type . '" id="answer[' . $answer->id . ']" name="answer[' . $element->id . ']" value="' . esc_attr( $answer->id ) . '"' . $answer_selected . '>'
										. '<label for="answer[' . $answer->id . ']" class="basic-answer-label">'
											. '<span class="basic-text" style="'
												. 'color: ' . esc_attr( $poll_meta_data['style']['answers']['textColor'] ) . '; '
												. 'font-size: ' . esc_attr( $poll_meta_data['style']['answers']['textSize'] ) . 'px; '
											. 'font-weight: ' . esc_attr( $poll_meta_data['style']['answers']['textWeight'] ) . ';'
											. '">'
												. $answer_text
											. '</span>'
										. '</label>'
									. '</div>'
								. '</li>';
		}
		if ( 'yes' === $element->meta_data['allowOtherAnswers'] ) {
			$element_answers .= '<li class="basic-answer basic-other-answer ' . $answers_class . '" style="'
									. 'padding:' . esc_attr( $poll_meta_data['style']['answers']['paddingTopBottom'] ) . 'px '
										. esc_attr( $poll_meta_data['style']['answers']['paddingLeftRight'] ) . 'px;"'
								. '>'
									. '<div class="basic-answer-content basic-text-horizontal">'
										. '<input type="' . $answers_type . '" id="answer[' . $element->id . '][0]" name="answer[' . $element->id . ']" value="0"' . $answer_selected . '>'
										. '<label for="answer[' . $element->id . '][0]" class="basic-answer-label">'
											. '<span class="basic-text" style="'
												. 'color: ' . esc_attr( $poll_meta_data['style']['answers']['textColor'] ) . '; '
												. 'font-size: ' . esc_attr( $poll_meta_data['style']['answers']['textSize'] ) . 'px; '
												. 'font-weight: ' . esc_attr( $poll_meta_data['style']['answers']['textWeight'] ) . ';'
											. '">'
												. esc_html( $element->meta_data['otherAnswersLabel'] )
											. '</span>'
										. '</label>'
									. '</div>'
									. '<div class="col-md-6 col-sm-6 col-xs-12">'
										. '<input class="basic-input-text form-control" type="text" name="other[' . $element->id . ']" data-type="other-answer">'
									. '</div>'
							. '</li>';
		}
		$element_answers .= '</ul>';
		return $element_answers;
	}
	public static function do_custom_field( $element, $poll_meta_data, $params ) {
		if ( ( true === isset( $params['show_results'] ) ) && ( '1' !== $params['show_results'] ) ) {
			$element_html = '';
			if ( true === isset( $element->meta_data['cType'] ) ) {
				switch( $element->meta_data['cType'] ){
					case 'textfield': {
						$element_html = '<input type="text" name="cfield[' . $element->id . ']" class="basic-input-text form-control" data-type="cfield">';
						break;
					}
					case 'textarea': {
						$element_html = '<textarea name="cfield[' . $element->id . ']" class="basic-input-text form-control" data-type="cfield"></textarea>';
						break;
					}
					default: {
						$element_html = '<input type="text" name="cfield[' . $element->id . ']" class="basic-input-text form-control" data-type="cfield">';
						break;
					}
				}
			} else {
				$element_html = '<input type="text" name="cfield[' . $element->id . ']" class="basic-input-text form-control" data-type="cfield">';
			}
			$poll_elements = '<div class="basic-element basic-custom-field"'
								. ' data-id="' .$element->id . '"'
								. ' data-type="custom-field"'
								. ' data-required="' .$element->meta_data['makeRequired'] . '"'
								. '>'
								. '<div class="basic-custom-field-title">'
									. '<label>'
										. esc_html( $element->etext )
									. '</label>'
								. '</div>'
								. '<div class="col-md-6 col-sm-12 col-xs-12">'
									. $element_html
								. '</div>'
							. '</div>'
							. '<div class="clearfix"></div>';
		} else {
			$poll_elements = '';
		}
		return $poll_elements;
	}
	public static function has_results_before_vote( $poll_meta_data ) {
		$show_results = false;
		if ( true === in_array( 'before-vote', $poll_meta_data['options']['results']['showResultsMoment'] ) ) {
			if ( true === in_array( 'custom-date', $poll_meta_data['options']['results']['showResultsMoment'] ) ) {
				$custom_date = new DateTime( $poll_meta_data['options']['results']['customDateResults'] );
				$today_date = new DateTime( 'now' );
				if ( $today_date >= $custom_date ) {
					$show_results = true;
				} else {
					$show_results = false;
				}
			} else {
				$show_results = true;
			}
		} else {
			$show_results = false;
		}
		if ( true === $show_results ) {
			if ( 1 === count( $poll_meta_data['options']['results']['showResultsTo'] ) ) {
				if ( 'registered' === $poll_meta_data['options']['results']['showResultsTo'][0] ) {
					if ( true === is_user_logged_in() ) {
						$show_results = true;
					} else {
						$show_results = false;
					}
				} else {
					$show_results = true;
				}
			} else {
				$show_results = true;
			}
		}
		return $show_results;
	}
	public static function should_display_results( $poll ) {
		$should_continue = true;
		$should_display_results = false;
		$current_user = wp_get_current_user();
		if ( ( 1 === count( $poll->meta_data['options']['results']['showResultsTo'] ) ) && ( true === in_array( 'registered', $poll->meta_data['options']['results']['showResultsTo'] ) ) ) {
			if ( 0 !== $current_user->ID ) {
				$should_continue = true;
			} else {
				$should_display_results = false;
				$should_continue = false;
			}
		}
		if ( ( true === $should_continue ) && ( true === in_array( 'never', $poll->meta_data['options']['results']['showResultsMoment'] ) ) ) {
			$should_display_results = false;
			$should_continue = false;
		}
		if ( ( true === $should_continue ) && ( true === in_array( 'before-vote', $poll->meta_data['options']['results']['showResultsMoment'] ) ) ) {
			$should_display_results = true;
			$should_continue = false;
		}
		if ( ( true === $should_continue ) && ( true === in_array( 'after-vote', $poll->meta_data['options']['results']['showResultsMoment'] ) ) ) {
			$should_display_results = true;
			$should_continue = false;
		}
		if ( ( true === $should_continue ) && ( true === in_array( 'after-end-date', $poll->meta_data['options']['results']['showResultsMoment'] ) ) ) {
			if ( true === YOP_Poll_Polls::has_ended_frontend( $poll ) ) {
				$should_display_results = true;
				$should_continue = false;
			} else {
				$should_display_results = false;;
				$should_continue = true;
			}
		}
		if ( ( true === $should_continue ) && ( true === in_array( 'custom-date', $poll->meta_data['options']['results']['showResultsMoment'] ) ) ) {
			$today = new DateTime( get_gmt_from_date( current_time( 'mysql' ) ), new DateTimeZone( 'UTC' ) );
			$custom_date = new DateTime( $poll->meta_data['options']['results']['customDateResults'] );
			if ( $today >= $custom_date ) {
				$should_display_results = true;
				$should_continue = false;
			} else {
				$should_display_results = false;;
				$should_continue = true;
			}
		}
		return $should_display_results;
	}
	public static function prepare_regular_view_for_display( $poll, $params ) {
		$poll_elements = '';
		foreach ( $poll->elements as $element ) {
			switch ( $element->etype ) {
				case 'text-question': {
					$poll_elements .= self::do_text_question( $element, $poll->meta_data, $params );
					break;
				}
				case 'custom-field': {
					$poll_elements .= self::do_custom_field( $element, $poll->meta_data, $params );
					break;
				}
			}
		}
		if ( false === isset( $poll->meta_data['style']['answers']['skin'] ) ) {
			$poll->meta_data['style']['answers']['skin'] = '';
		}
		if ( false === isset( $poll->meta_data['style']['answers']['colorScheme'] ) ) {
			$poll->meta_data['style']['answers']['colorScheme'] = '';
		}
		if ( true === isset( $params['show_results'] ) && ( '1' === $params['show_results'] ) ) {
			$show_results_only = 'true';
			$show_results_only_class = 'hide';
		} else {
			$show_results_only = 'false';
			$show_results_only_class = '';
		}
		if ( true === isset( $params['show_thank_you_message'] ) && ( '1' === $params['show_thank_you_message'] ) ) {
			$show_thank_you_message = 'true';
		} else {
			$show_thank_you_message = 'false';
		}
		if ( false === isset( $params['loaded_with'] ) ) {
			$params['loaded_with'] = '1';
		}
		if ( false === is_array( $poll->meta_data['options']['results']['showResultsTo'] ) ) {
			$poll->meta_data['options']['results']['showResultsTo'] = [];
		}
		$results_before_vote_data = ' data-show-results-to="' . implode( ',', $poll->meta_data['options']['results']['showResultsTo'] ) . '"'
			. ' data-show-results-moment="' . implode( ',', $poll->meta_data['options']['results']['showResultsMoment'] ) . '"'
			. ' data-show-results-only="' . $show_results_only . '"'
			. ' data-show-message="' . $show_thank_you_message . '"'
			. ' data-show-results-as="' . $poll->meta_data['options']['results']['displayResultsAs'] . '"'
			. ' data-sort-results-by="' . $poll->meta_data['options']['results']['sortResults'] . '"'
			. ' data-sort-results-rule="' . $poll->meta_data['options']['results']['sortResultsRule'] . '"';
		$use_captcha = self::has_captcha( $poll, $params );
		$text_for_message_section = '';
		$class_for_message_section = 'hide';
		if ( ( true === isset( $params['started'] ) && ( '0' === $params['started'] ) ) ) {
			$messages = YOP_Poll_Settings::get_messages();
			$text_for_message_section = $messages['voting']['poll-not-started'];
			$class_for_message_section = '';
		}
		if ( ( true === isset( $params['ended'] ) && ( '1' === $params['ended'] ) ) ) {
			$messages = YOP_Poll_Settings::get_messages();
			$text_for_message_section = $messages['voting']['poll-ended'];
			$class_for_message_section = '';
		}
		$poll_ready_for_output = '<div class="basic-yop-poll-container" style="'
									. 'background-color:' . esc_attr( $poll->meta_data['style']['poll']['backgroundColor'] ) . ';'
									. ' border:' . esc_attr( $poll->meta_data['style']['poll']['borderSize'] ) .'px;'
									. ' border-style:solid;'
									. ' border-color:' . esc_attr( $poll->meta_data['style']['poll']['borderColor'] ) .';'
									. ' border-radius:' . esc_attr( $poll->meta_data['style']['poll']['borderRadius'] ) .'px;'
									. ' padding:' . esc_attr( $poll->meta_data['style']['poll']['paddingTopBottom'] ) .'px '
									. esc_attr( $poll->meta_data['style']['poll']['paddingLeftRight'] ) .'px;"'
									. ' data-id="' . esc_attr( $poll->id ) . '"'
									. ' data-temp="' . esc_html( $poll->template_base ) .'"'
									. ' data-skin="' . esc_html( $poll->meta_data['style']['answers']['skin'] ) . '"'
									. ' data-cscheme="' . esc_html( $poll->meta_data['style']['answers']['colorScheme'] ) . '"'
									. ' data-cap="' . esc_attr( $use_captcha[0] ) . '"'
									. ' data-access="' . esc_attr( implode( ',', $poll->meta_data['options']['access']['votePermissions'] ) ) . '"'
									. ' data-tid="' . esc_attr( $params['tracking_id'] ) . '"'
									. ' data-uid="' . esc_attr( $use_captcha[2] ) . '"'
									. ' data-resdet="' . esc_attr( implode( ',', $poll->meta_data['options']['results']['resultsDetails'] ) ) . '"'
									. $results_before_vote_data
									. ' data-gdpr="' . $poll->meta_data['options']['poll']['enableGdpr'] . '"'
									. ' data-gdpr-sol="' . $poll->meta_data['options']['poll']['gdprSolution'] . '"'
									. ' data-css="' . esc_attr( $poll->meta_data['style']['custom']['css'] ) . '"'
									. ' data-counter="0"'
									. ' data-load-with="' . $params['loaded_with'] . '"'
									. '>'
									. '<div class="row">'
										. '<div class="col-md-12">'
											. '<div class="basic-inner">'
												. '<div class="basic-message ' . $class_for_message_section . '" style="'
													. 'border-left: ' . $poll->meta_data['style']['errors']['borderLeftSize'] . 'px solid '
													. $poll->meta_data['style']['errors']['borderLeftColorForSuccess'] . ';'
													. ' padding: ' . $poll->meta_data['style']['errors']['paddingTopBottom'] . 'px 10px;'
													. '"'
													. ' data-error="' . $poll->meta_data['style']['errors']['borderLeftColorForError'] . '"'
													. ' data-success="' . $poll->meta_data['style']['errors']['borderLeftColorForSuccess'] . '"'
													. '>'
													. '<p class="basic-message-text" style="'
														. 'color:' . esc_attr( $poll->meta_data['style']['errors']['textColor'] ) . ';'
														. ' font-size:' . esc_attr( $poll->meta_data['style']['errors']['textSize'] ) . 'px;'
														. ' font-weight:' . esc_attr( $poll->meta_data['style']['errors']['textWeight'] ) . ';'
														. '">'
														. $text_for_message_section
													. '</p>'
												. '</div>'
												. '<div class="basic-overlay hide">'
													. '<div class="basic-vote-options">'
														. self::do_anonymous_vote( $poll )
														. self::do_wordpress_vote( $poll )
													. '</div>'
													. '<div class="basic-preloader">'
														. '<div class="basic-windows8">'
															. '<div class="basic-wBall basic-wBall_1">'
																. '<div class="basic-wInnerBall"></div>'
															. '</div>'
															. '<div class="basic-wBall basic-wBall_2">'
																. '<div class="basic-wInnerBall"></div>'
															.'</div>'
															. '<div class="basic-wBall basic-wBall_3">'
																. '<div class="basic-wInnerBall"></div>'
															. '</div>'
															. '<div class="basic-wBall basic-wBall_4">'
																. '<div class="basic-wInnerBall"></div>'
															. '</div>'
															. '<div class="basic-wBall basic-wBall_5">'
																. '<div class="basic-wInnerBall"></div>'
															. '</div>'
														. '</div>'
													. '</div>'
												. '</div>'
												. '<form class="basic-form">'
													. '<input type="hidden" name="_token" value="' . wp_create_nonce( 'yop-poll-vote-' . $poll->id ) . '">'
													. '<div class="basic-elements">'
														. $poll_elements
													. '</div>'
													. self::get_gdpr_html( $poll )
													. $use_captcha[1]
													. self::do_show_total_votes_and_answers( $poll, $params )
													. '<div class="basic-vote">'
														. '<a href="#" class="button basic-vote-button" style="'
															. 'background:' . esc_attr( $poll->meta_data['style']['buttons']['backgroundColor'] ) . ';'
															. ' border:' . esc_attr( $poll->meta_data['style']['buttons']['borderSize'] ) . 'px;'
															. ' border-style: solid;'
															. ' border-color:' . esc_attr( $poll->meta_data['style']['buttons']['borderColor'] ) . ';'
															. ' border-radius:' . esc_attr( $poll->meta_data['style']['buttons']['borderRadius'] ) . 'px;'
															. ' padding:' . esc_attr( $poll->meta_data['style']['buttons']['paddingTopBottom'] ) . 'px '
																. esc_attr( $poll->meta_data['style']['buttons']['paddingLeftRight'] ) . 'px;'
															. ' color:' . esc_attr( $poll->meta_data['style']['buttons']['textColor'] ) . ';'
															. ' font-size:' . esc_attr( $poll->meta_data['style']['buttons']['textSize'] ) . 'px;'
															. ' font-weight:' . esc_attr( $poll->meta_data['style']['buttons']['textWeight'] ) . ';'
														. '">'
															. $poll->meta_data['options']['poll']['voteButtonLabel']
														. '</a>'
														. self::do_show_results_link( $poll )
														. self::do_show_back_to_vote_link( $poll )
													. '</div>'
												. '</form>'
											. '</div>'
										. '</div>'
									. '</div>'
								. '</div>';
		return $poll_ready_for_output;
	}
	public static function prepare_thankyou_view_for_display( $poll, $params ) {
		$messages = YOP_Poll_Settings::get_messages();
		$poll_ready_for_output = '<div class="basic-yop-poll-container" style="'
									. 'background-color:' . esc_attr( $poll->meta_data['style']['poll']['backgroundColor'] ) . ';'
									. ' border:' . esc_attr( $poll->meta_data['style']['poll']['borderSize'] ) .'px;'
									. ' border-style:solid;'
									. ' border-color:' . esc_attr( $poll->meta_data['style']['poll']['borderColor'] ) .';'
									. ' border-radius:' . esc_attr( $poll->meta_data['style']['poll']['borderRadius'] ) .'px;'
									. ' padding:' . esc_attr( $poll->meta_data['style']['poll']['paddingTopBottom'] ) .'px '
									. esc_attr( $poll->meta_data['style']['poll']['paddingLeftRight'] ) .'px;"'
									. ' data-id="' . esc_attr( $poll->id ) . '"'
									. ' data-temp="' . esc_html( $poll->template_base ) .'"'
									. ' data-skin="' . esc_html( $poll->meta_data['style']['answers']['skin'] ) . '"'
									. ' data-cscheme="' . esc_html( $poll->meta_data['style']['answers']['colorScheme'] ) . '"'
									. ' data-cap="0"'
									. ' data-access="' . esc_attr( implode( ',', $poll->meta_data['options']['access']['votePermissions'] ) ) . '"'
									. ' data-tid="' . esc_attr( $params['tracking_id'] ) . '"'
									. ' data-uid="0"'
									. ' data-resdet="' . esc_attr( implode( ',', $poll->meta_data['options']['results']['resultsDetails'] ) ) . '"'
									. ' data-gdpr="' . $poll->meta_data['options']['poll']['enableGdpr'] . '"'
									. ' data-gdpr-sol="' . $poll->meta_data['options']['poll']['gdprSolution'] . '"'
									. ' data-css="' . esc_attr( $poll->meta_data['style']['custom']['css'] ) . '"'
									. ' data-counter="0"'
									. '>'
									. '<div class="row">'
										. '<div class="col-md-12">'
											. '<div class="basic-inner">'
												. '<div class="basic-message" style="'
													. 'border-left: ' . $poll->meta_data['style']['errors']['borderLeftSize'] . 'px solid '
													. $poll->meta_data['style']['errors']['borderLeftColorForSuccess'] . ';'
													. ' padding: ' . $poll->meta_data['style']['errors']['paddingTopBottom'] . 'px 10px;"'
													. ' data-error="' . $poll->meta_data['style']['errors']['borderLeftColorForError'] . '"'
													. ' data-success="' . $poll->meta_data['style']['errors']['borderLeftColorForSuccess'] . '"'
													. '>'
													. '<p class="basic-message-text" style="'
														. 'color:' . esc_attr( $poll->meta_data['style']['errors']['textColor'] ) . ';'
														. ' font-size:' . esc_attr( $poll->meta_data['style']['errors']['textSize'] ) . 'px;'
														. ' font-weight:' . esc_attr( $poll->meta_data['style']['errors']['textWeight'] ) . ';'
														. '">'
														. $messages['voting']['already-voted-on-poll']
													. '</p>'
												. '</div>'
											. '</div>'
										. '</div>'
									. '</div>'
								. '</div>';
		return $poll_ready_for_output;
	}
	public static function create_poll_view( $poll, $params ) {
		$poll_ready_for_output = '';
		$should_continue = true;
		$current_user = wp_get_current_user();
		if ( ( true === $should_continue ) && ( false === YOP_Poll_Polls::has_started_frontend( $poll ) ) ) {
			//not started yet
			$params['started'] = '0';
			$poll = YOP_Poll_Polls::get_poll_for_voting( $poll->id );
			$poll_ready_for_output = self::prepare_regular_view_for_display( $poll, $params );
			$should_continue = false;
		}
		if ( ( true === $should_continue ) && ( true === YOP_Poll_Polls::has_ended_frontend( $poll ) ) ) {
			//poll has ended
			if ( true === self::should_display_results( $poll ) ) {
				$poll_for_display = YOP_Poll_Polls::get_poll_for_results( $poll->id );
				$params['show_results'] = '1';
				$poll_ready_for_output = self::prepare_regular_view_for_display( $poll_for_display, $params );
			} else {
				$params['ended'] = '1';
				$poll = YOP_Poll_Polls::get_poll_for_voting( $poll->id );
				$poll_ready_for_output = self::prepare_regular_view_for_display( $poll, $params );
			}
			$should_continue = false;
		}
		if ( true === $should_continue ) {
			if ( ( 1 === count( $poll->meta_data['options']['access']['votePermissions'] ) ) &&
				( true === in_array( 'wordpress', $poll->meta_data['options']['access']['votePermissions'] ) ) &&
				( 'yes' === $poll->meta_data['options']['access']['limitVotesPerUser'] ) && 
				( $poll->meta_data['options']['access']['votesPerUserAllowed'] > 0 ) 
			) {
				if ( 0 !== $current_user->ID ) {
					$accept_votes_from_user = YOP_Poll_Polls::accept_votes_from_user( $poll, $current_user->ID, 'wordpress' );
					if ( true === $accept_votes_from_user ) {
						//accepting votes from this user. showing regular poll
						$poll = YOP_Poll_Polls::get_poll_for_voting( $poll->id );
						$poll_ready_for_output = self::prepare_regular_view_for_display( $poll, $params );
					} else {
						//no longer accepting votes from this user. need to decide what to do
						if ( true === self::should_display_results( $poll ) ) {
							$poll_for_display = YOP_Poll_Polls::get_poll_for_results( $poll->id );
							$params['show_results'] = '1';
							$poll_ready_for_output = self::prepare_regular_view_for_display( $poll_for_display, $params );
						} else {
							$poll_ready_for_output = self::prepare_thankyou_view_for_display( $poll, $params );
						}
					}
				} else {
					$poll_for_display = YOP_Poll_Polls::get_poll_for_voting( $poll->id );
					$poll_ready_for_output = self::prepare_regular_view_for_display( $poll_for_display, $params );
				}
			} elseif ( ( true === in_array( 'by-cookie',  $poll->meta_data['options']['access']['blockVoters'] ) ) ||
						( true === in_array( 'by-ip',  $poll->meta_data['options']['access']['blockVoters'] ) ) ||
						( true === in_array( 'by-user-id',  $poll->meta_data['options']['access']['blockVoters'] ) )
		 	) {
				$voter_data['c-data'] = YOP_Poll_Votes::get_voter_cookie( $poll->id );
				$voter_data['ipaddress'] = YOP_Poll_Votes::get_voter_ip( $poll );
				$voter_data['user-id'] = ( $current_user->ID != 0 ) ? $current_user->ID : '';
				$accept_votes_from_anonymous = YOP_Poll_Polls::accept_votes_from_anonymous( $poll, $voter_data );
				if ( true === $accept_votes_from_anonymous ) {
					$poll_for_display = YOP_Poll_Polls::get_poll_for_voting( $poll->id );
					$poll_ready_for_output = self::prepare_regular_view_for_display( $poll_for_display, $params );
				} else {
					if ( true === self::should_display_results( $poll ) ) {
						$poll_for_display = YOP_Poll_Polls::get_poll_for_results( $poll->id );
						$params['show_results'] = '1';
						$poll_ready_for_output = self::prepare_regular_view_for_display( $poll_for_display, $params );
					} else {
						$poll_ready_for_output = self::prepare_thankyou_view_for_display( $poll, $params );
					}
				}
			} else {
				$poll_for_display = YOP_Poll_Polls::get_poll_for_voting( $poll->id );
				$poll_ready_for_output = self::prepare_regular_view_for_display( $poll_for_display, $params );
			}
		}
		return $poll_ready_for_output;
	}
	public static function create_poll_view_for_ajax( $poll, $params ) {
		if ( true === isset( $params['show_results'] ) && ( '1' === $params['show_results'] ) ) {
			$show_results_only = 'true';
		} else {
			$show_results_only = 'false';
		}
		$poll_ready_for_output = '<div class="basic-yop-poll-container"'
										. ' data-id="' . esc_attr( $poll->id ) . '"'
										. ' data-ajax="1"'
										. ' data-tid="' . esc_attr( $params['tracking_id'] ) . '"'
										. ' data-show-results-only="' . $show_results_only . '"'
										. '>'
										. '</div>';
		return $poll_ready_for_output;
	}
	public static function create_poll_view_for_results( $poll, $params ) {
		$poll_ready_for_output = '';
		if ( true === isset( $poll->id ) ) {
			$poll_for_display = YOP_Poll_Polls::get_poll_for_results( $poll->id );
			$poll_ready_for_output = self::prepare_regular_view_for_display( $poll_for_display, $params );
		}
		return $poll_ready_for_output;
	}
}