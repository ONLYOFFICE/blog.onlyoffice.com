<?php
class YOP_Poll_Polls {
	private static $errors_present = false,
			$error_text,
			$text_size_allowed = array( 'small', 'medium', 'large' ),
			$text_weight_allowed = array( 'normal', 'bold' ),
			$text_align_allowed = array( 'left', 'center', 'right' ),
			$yes_no_allowed = array( 'yes', 'no' ),
			$captcha_allowed = array( 'yes', 'yes-recaptcha', 'yes-recaptcha-invisible', 'no' ),
			$answers_display_allowed = array( 'vertical', 'horizontal', 'columns' ),
			$answers_sort_allowed = array( 'as-defined' ),
			$date_values_allowed = array( 'now', 'custom', 'never', 'custom-date' ),
			$reset_stats_allowed = array( 'hours', 'days' ),
			$show_results_allowed = array( 'before-vote', 'after-vote', 'after-end-date', 'never', 'custom-date' ),
			$show_results_to_allowed = array( 'guest', 'registered' ),
			$sort_results_allowed = array( 'as-defined', 'alphabetical', 'number-of-votes' ),
			$sort_results_rule_allowed = array( 'asc', 'desc' ),
			$display_results_as_allowed = array( 'bar', 'pie' ),
			$vote_permissions_allowed = array( 'guest', 'wordpress', 'facebook', 'google' ),
			$block_voters_allowed = array( 'no-block', 'by-cookie', 'by-ip', 'by-user-id' ),
			$block_voters_period_allowed = array( 'minutes', 'hours', 'days' ),
			$sort_order_allowed = array( 'asc', 'desc' ),
			$order_by_allowed = array( 'id', 'name', 'status', 'total_submits', 'author', 'sdate', 'edate' ),
			$ends_soon_interval = 10,
			$polls_per_page = 10;
	public static function get_text_sizes() {
		return self::$text_size_allowed;
	}
	public static function get_owner( $poll_id ) {
		$query = $GLOBALS['wpdb']->prepare(
			"SELECT * FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `id` = %s", $poll_id
		);
		$poll = $GLOBALS['wpdb']->get_row( $query, OBJECT );
		if( null !== $poll ){
			return $poll->author;
		} else {
			return false;
		}
	}
	public static function paginate( $params ) {
		$return_data = array();
		$total_pages = 0;
		$total_polls = 0;
		$current_user = wp_get_current_user();
		if ( current_user_can( 'yop_poll_results_others' ) ) {
			$query = "SELECT COUNT(*) FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `status` != 'deleted'";
			if ( isset( $params['q'] ) && ( '' !== $params['q'] ) ) {
				$params['q'] = "'" . '%' . esc_sql( $GLOBALS['wpdb']->esc_like( $params['q'] ) ) . '%' . "'";
				$query .= " AND `name` LIKE {$params['q']}";
			}
		} else if ( current_user_can( 'yop_poll_results_own' ) ) {
			$query = "SELECT COUNT(*) FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE
						`author` = '" . $current_user->ID . "'
						AND `status` !='deleted'";
			if ( isset( $params['q'] ) && ( '' !== $params['q'] ) ) {
				$params['q'] = "'" . '%' . esc_sql( $GLOBALS['wpdb']->esc_like( $params['q'] ) ) . '%' . "'";
				$query .= " AND `name` LIKE {$params['q']}";
			}
		}
		if ( '' !== $query ) {
			$total_polls = $GLOBALS['wpdb']->get_var( $query );
		}
        self::$polls_per_page = $params['perpage'];
		if ( 0 < $total_polls ) {
			if ( $total_polls <= self::$polls_per_page ) {
				$data['pagination'] = '';
				$page = 1;
				$total_pages = 1;
			} else {
				$total_pages = intval( ceil( $total_polls / self::$polls_per_page ) );
			}
		} else {
			$data['pagination'] = '';
		}
		if ( 1 < $total_pages ){
			$pagination['first_page'] = '<span class="tablenav-pages-navspan" aria-hidden="true">
							«
						  </span>';
			$pagination['previous_page'] = '<span class="screen-reader-text">
								' . __( 'Previous page', 'yop-poll' ) . '
							</span>
							<span class="tablenav-pages-navspan" aria-hidden="true">
								‹
							</span>';
			$pagination['next_page'] = '<span class="screen-reader-text">' . __( 'Next page', 'yop-poll' ) . '
							</span>
							<span aria-hidden="true">›</span>';
			$pagination['last_page'] = '<span class="tablenav-pages-navspan" aria-hidden="true">
							»
							</span>';
			if ( 1 === intval( $params['page_no'] ) ) {
				//we're on the first page.
				$links['next_page'] = esc_url(
						add_query_arg(
							array(
								'action' => false,
								'poll_id' => false,
								'_token' => false,
								'order_by' => $params['order_by'],
								'sort_order' => $params['sort_order'],
								'q' => htmlentities( $params['q'] ),
								'page_no' => $params['page_no']+1
							)
						)
					);
				$pagination['next_page'] = "<a
										class=\"next-page\"
										href=\"{$links['next_page']}\">{$pagination['next_page']}</a>";
				if ( 2 < intval( $total_pages ) ) {
					$links['last_page'] = esc_url(
							add_query_arg(
								array(
									'action' => false,
									'poll_id' => false,
									'_token' => false,
									'order_by' => $params['order_by'],
									'sort_order' => $params['sort_order'],
									'q' => htmlentities( $params['q'] ),
									'page_no' => intval( $total_pages )
								)
							)
						);
					$pagination['last_page'] = "<a
												class=\"last-page\"
												href=\"{$links['last_page']}\">{$pagination['last_page']}</a>";
				}
			} else if ( intval( $params['page_no'] ) === intval( $total_pages ) ) {
				//we're on the last page
				$links['previous_page'] = esc_url(
						add_query_arg(
							array(
								'action' => false,
								'poll_id' => false,
								'_token' => false,
								'order_by' => $params['order_by'],
								'sort_order' => $params['sort_order'],
								'q' => htmlentities( $params['q'] ),
								'page_no' => $params['page_no']-1
							)
						)
					);
				$pagination['previous_page'] = "<a
											class=\"prev-page\"
											href=\"{$links['previous_page']}\">{$pagination['previous_page']}</a>";
				if ( 2 < intval( $total_pages ) ) {
					$links['first_page'] = esc_url(
							add_query_arg(
								array(
									'action' => false,
									'poll_id' => false,
									'_token' => false,
									'order_by' => $params['order_by'],
									'sort_order' => $params['sort_order'],
									'q' => htmlentities( $params['q'] ),
									'page_no' => 1
								)
							)
						);
					$pagination['first_page'] = "<a
												class=\"first-page\"
												href=\"{$links['first_page']}\">{$pagination['first_page']}</a>";
				}
			} else {
				//we're on an intermediary page
				$links['previous_page'] = esc_url(
						add_query_arg(
							array(
								'action' => false,
								'poll_id' => false,
								'_token' => false,
								'order_by' => $params['order_by'],
								'sort_order' => $params['sort_order'],
								'q' => htmlentities( $params['q'] ),
								'page_no' => $params['page_no']-1
							)
						)
					);
				$links['next_page'] = esc_url(
						add_query_arg(
							array(
								'action' => false,
								'poll_id' => false,
								'_token' => false,
								'order_by' => $params['order_by'],
								'sort_order' => $params['sort_order'],
								'q' => htmlentities( $params['q'] ),
								'page_no' => $params['page_no']+1
							)
						)
					);
				$pagination['previous_page'] = "<a
											class=\"prev-page\"
											href=\"{$links['previous_page']}\">{$pagination['previous_page']}</a>";
				$pagination['next_page'] = "<a
											class=\"prev-page\"
											href=\"{$links['next_page']}\">{$pagination['next_page']}</a>";
				if ( 2 < intval( $params['page_no'] ) ) {
					$links['first_page'] = esc_url(
							add_query_arg(
								array(
									'action' => false,
									'poll_id' => false,
									'_token' => false,
									'order_by' => $params['order_by'],
									'sort_order' => $params['sort_order'],
									'q' => htmlentities( $params['q'] ),
									'page_no' => 1
								)
							)
						);
					$pagination['first_page'] = "<a
												class=\"first-page\"
												href=\"{$links['first_page']}\">{$pagination['first_page']}</a>";
				}
				if ( ( intval( $params['page_no'] + 2 ) ) <= $total_pages ) {
					$links['last_page'] = esc_url(
							add_query_arg(
								array(
									'action' => false,
									'poll_id' => false,
									'_token' => false,
									'order_by' => $params['order_by'],
									'sort_order' => $params['sort_order'],
									'q' => htmlentities( $params['q'] ),
									'page_no' => intval( $total_pages )
								)
							)
						);
					$pagination['last_page'] = "<a
												class=\"last-page\"
												href=\"{$links['last_page']}\">{$pagination['last_page']}</a>";
				}
			}
		} else {
			$pagination['first_page'] = '';
			$pagination['previous_page'] = '';
			$pagination['next_page'] = '';
			$pagination['last_page'] = '';
		}
		return array(
			'total_polls' => $total_polls,
			'total_pages' => $total_pages,
			'pagination' => $pagination
		);
	}
	public static function get_polls( $params ) {
		$query = '';
		$polls = '';
		$statistics = array(
			'published' => 0,
			'draft' => 0,
			'archived' => 0,
			'ending-soon' => 0,
			'ended' => 0
		);
		$current_user = wp_get_current_user();
		if ( 0 >= intval( $params['page_no'] ) ) {
			$params['page_no'] = 1;
		}
		$pagination = self::paginate( $params );
		if ( !in_array( $params['sort_order'], self::$sort_order_allowed ) ) {
			$params['sort_order'] = SORT_ASC;
		} elseif ( 'desc' === $params['sort_order'] ) {
			$params['sort_order'] = SORT_DESC;
		} else{
			$params['sort_order'] = SORT_ASC;
		}
		if ( !in_array( $params['order_by'], self::$order_by_allowed ) ) {
			$params['order_by'] = 'id';
		}
		if ( $params['page_no'] > $pagination['total_pages']) {
			$params['page_no'] = 1;
		}
		$limit = self::$polls_per_page * ( $params['page_no'] - 1 );
		$limit_query = " LIMIT {$limit}, ". self::$polls_per_page;
		if ( current_user_can( 'yop_poll_results_others' ) ) {
			$query = "SELECT * FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `status` != 'deleted'";
			if ( isset( $params['q'] ) && ( '' !== $params['q'] ) ) {
				$params['q'] = "'" . '%' . esc_sql( $GLOBALS['wpdb']->esc_like( $params['q'] ) ) . '%' . "'";
				$query .= " AND `name` LIKE {$params['q']} ORDER BY `id` DESC";
			} else {
				$query .= " ORDER BY `id` DESC";
			}
		} else if ( current_user_can( 'yop_poll_results_own' ) ) {
			$query = "SELECT * FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `author` = '" . $current_user->ID . "'
					AND `status` != 'deleted'";
			if ( isset( $params['q'] ) && ( '' !== $params['q'] ) ) {
				$params['q'] = "'" . '%' . esc_sql( $GLOBALS['wpdb']->esc_like( $params['q'] ) ) . '%' . "'";
				$query .= " AND `name` LIKE {$params['q']} ORDER BY `id` DESC";
			} else {
				$query .= " ORDER BY `id` DESC";
			}
		}
		if ( '' !== $query ) {
			$query .= $limit_query;
			$polls = $GLOBALS['wpdb']->get_results( $query, ARRAY_A );
		}
		foreach( $polls as &$poll ) {
			$statistics[$poll['status']]++;
			if ( true === self::is_ended( $poll, false ) ) {
				$poll['status'] = 'ended';
				$statistics['ended']++;
			} else if ( true === self::ends_soon( $poll ) ) {
				$statistics['ending-soon']++;
				$poll['status'] = 'ending soon';
			}
			$poll_author = get_user_by( 'id', $poll['author'] );
			if ( false !== $poll_author ) {
				$poll['author'] = $poll_author->display_name;
			} else {
				$poll['author'] = '';
			}
			$poll_meta_data = unserialize( $poll['meta_data'] );
			if ( 'now' === $poll_meta_data['options']['poll']['startDateOption'] ) {
				$poll['sdate'] = $poll['added_date'];
			} else {
				$poll['sdate'] = $poll_meta_data['options']['poll']['startDateCustom'];
			}
			if ( 'never' === $poll_meta_data['options']['poll']['endDateOption'] ) {
				$poll['edate'] = '2100-12-31 23:59:59';
			} else {
				$poll['edate'] = $poll_meta_data['options']['poll']['endDateCustom'];
			}
		}
		foreach ( $polls as $key => $row ) {
			$order_by['id'][$key] = $row['id'];
			$order_by['name'][$key] = $row['name'];
			$order_by['status'][$key] = $row['status'];
			$order_by['votes'][$key] = $row['total_submits'];
			$order_by['author'][$key] = $row['author'];
			$order_by['sdate'][$key] = strtotime( $row['sdate'] );
			$order_by['edate'][$key] = strtotime( $row['edate'] );
		}
		if ( 0 < count( $polls ) ) {
			array_multisort( $order_by[$params['order_by']], $params['sort_order'], $polls );
		}
		return array(
			'polls' => $polls,
			'statistics' => $statistics,
			'total_polls' => $pagination['total_polls'],
			'total_pages' => $pagination['total_pages'],
			'pagination' => $pagination['pagination']
		);
	}
	public static function get_names() {
		$polls = false;
		$current_user = wp_get_current_user();
		if ( current_user_can( 'yop_poll_results_others' ) ) {
			$query = "SELECT `id`, `name` FROM {$GLOBALS['wpdb']->yop_poll_polls} ORDER BY `name`";
		} else if ( current_user_can( 'yop_poll_results_own' ) ) {
			$query = "SELECT `id`, `name` FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `author` = '" . $current_user->ID . "' ORDER BY `name`";
		}
		if ( '' !== $query ) {
			$polls = $GLOBALS['wpdb']->get_results( $query, OBJECT );
		}
		return $polls;
	}
	public static function add( stdClass $poll ) {
		$elements_result = array();
		$poll_id = '';
		$current_user = wp_get_current_user();
		self::validate_data( $poll );
		if ( false === self::$errors_present ) {
			self::check_for_name( $poll->name );
			if ( false === self::$errors_present ) {
				$poll_meta_data = self::create_meta_data( $poll );
				$data = array(
					'name' => $poll->name,
					'template' => $poll->design->template,
					'template_base' => $poll->design->templateBase,
					'skin_base' => $poll->design->skinBase,
					'author' => $current_user->ID,
					'stype' => 'poll',
					'status' => $poll->status,
					'meta_data' => serialize( $poll_meta_data ),
					'total_submits' => 0,
					'total_submited_answers' => 0,
					'added_date' => isset( $poll->added_date ) ? $poll->added_date : current_time( 'mysql' ),
					'modified_date' => isset( $poll->modified_date ) ? $poll->modified_date : current_time( 'mysql' )
				);
				if ( isset( $poll->ID ) && is_numeric($poll->ID ) ) {
					$data['id'] = $poll->ID;
				}
				if ( isset( $poll->poll_author ) && is_numeric( $poll->poll_author ) ) {
					$data['author'] = $poll->poll_author;
				}
				if ( isset( $poll->total_submits ) && is_numeric( $poll->total_submits ) ) {
					$data['total_submits'] = $poll->total_submits;
				}
				if ( isset( $poll->total_submited_answers ) && is_int( $poll->total_submited_answers ) ) {
					$data['total_submited_answers'] = $poll->total_submited_answers;
				}
				if ( false !== $GLOBALS['wpdb']->insert( $GLOBALS['wpdb']->yop_poll_polls, $data ) ) {
					$poll_id = $GLOBALS['wpdb']->insert_id;
					if ( isset( $poll->ID ) && is_numeric( $poll->ID ) ) {
						$elements_result = YOP_Poll_Elements::add( $poll_id, $poll->elements, true );
					} else {
						$elements_result = YOP_Poll_Elements::add( $poll_id, $poll->elements );
					}
					if ( false === $elements_result['errors_present'] ) {
						if ( 'yes' === $poll->options->poll->autoGeneratePollPage ) {
							$page_id = wp_insert_post( array(
								'post_title' => $poll->name,
								'post_content' => "[yop_poll id='{$poll_id}']",
								'post_status' => 'publish',
								'post_type' => 'page',
								'comment_status' => 'open',
								'ping_status' => 'open',
								'post_category' => array( 1 )
							) );
							if ( 0 !== $page_id ) {
								$poll_meta_data['options']['poll']['pageId'] = $page_id;
								$poll_meta_data['options']['poll']['pageLink'] = get_permalink( $page_id );
								$data = array(
									'meta_data' => serialize( $poll_meta_data )
								);
								if ( false !== $GLOBALS['wpdb']->update( $GLOBALS['wpdb']->yop_poll_polls, $data, array( 'id' => $poll_id ) ) ) {
									self::$errors_present = false;
								} else {
									self::$errors_present = true;
									self::$error_text = __( 'Error adding page', 'yop-poll' );
								}
							}
						}
						self::$errors_present = false;
						self::$error_text = $elements_result['error_text'];
					}
				} else {
					self::$errors_present = true;
					self::$error_text = __( 'Error adding poll', 'yop-poll' );
				}
			} else {
				self::$errors_present = true;
				self::$error_text = __( 'A poll with this name already exists', 'yop-poll' );
			}
		}
		return array(
			'success' => !self::$errors_present,
			'error' => self::$error_text,
			'poll_id' => $poll_id
		);
	}
	public static function check_for_name( $poll_name ) {
		$sql_query = $GLOBALS['wpdb']->prepare( "SELECT COUNT(*) FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `status`!= 'deleted' AND `name` = %s", $poll_name );
		$polls_count = $GLOBALS['wpdb']->get_var( $sql_query );
		if ( 0 === intval( $polls_count ) ) {
			self::$errors_present = false;
		} else {
			self::$errors_present = true;
		}
	}
	public static function update( stdClass $poll ) {
		$poll_id = $poll->id;
		if ( intval( $poll_id ) > 0 ) {
			$elements_result = array();
			self::validate_data( $poll );
			if ( false === self::$errors_present ) {
				$poll_meta_data = self::create_meta_data( $poll );
				$db_poll_meta = unserialize( $GLOBALS['wpdb']->get_var( $GLOBALS['wpdb']->prepare( "SELECT `meta_data` FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `id` = %d", $poll->id ) ) );
				if ( 'yes' === $poll->options->poll->autoGeneratePollPage ) {
                    $has_page = true;
                    if ( isset( $db_poll_meta['options']['poll']['pageId'] ) && ( $db_poll_meta['options']['poll']['pageId'] > 0 ) ){
                        $poll_page_count = $GLOBALS['wpdb']->get_var( $GLOBALS['wpdb']->prepare( "SELECT COUNT(*) FROM {$GLOBALS['wpdb']->posts} WHERE `ID` = %d AND `post_status` = 'publish'", $db_poll_meta['options']['poll']['pageId'] ) );
                        if ( 0 === (int)$poll_page_count ) {
                            $has_page = false;
                        }
                    } else {
                        $has_page = false;
                    }
                    if ( false === $has_page ) {
                        $page_id = wp_insert_post( array(
                            'post_title' => $poll->name,
                            'post_content' => "[yop_poll id='{$poll_id}']",
                            'post_status' => 'publish',
                            'post_type' => 'page',
                            'comment_status' => 'open',
                            'ping_status' => 'open',
                            'post_category' => array( 1 )
                        ) );
                    } else {
                        wp_update_post( array(
                            'ID' => $db_poll_meta['options']['poll']['pageId'],
                            'post_title' => $poll->name
                        ) );
                        $page_id = $db_poll_meta['options']['poll']['pageId'];
                    }

					if ( 0 !== $page_id ) {
						$poll_meta_data['options']['poll']['pageId'] = $page_id;
						$poll_meta_data['options']['poll']['pageLink'] = get_permalink( $page_id );

					}
				} else {
					if ( '' !== $poll->options->poll->pageId ) {
						wp_delete_post( $poll->options->poll->pageId );
						$poll_meta_data['options']['poll']['pageId'] = '';
						$poll_meta_data['options']['poll']['pageLink'] = '';
					}
				}
				$data = array(
					'name' => $poll->name,
					'template' => $poll->design->template,
					'template_base' => $poll->design->templateBase,
					'skin_base' => $poll->design->skinBase,
					'stype' => 'poll',
					'status' => $poll->status,
					'meta_data' => serialize( $poll_meta_data ),
					'modified_date' => current_time( 'mysql' )
				);
				if ( false !== $GLOBALS['wpdb']->update( $GLOBALS['wpdb']->yop_poll_polls, $data, array( 'id' => $poll_id ) ) ) {
					$elements_result = YOP_Poll_Elements::update( $poll_id, $poll->elements );
					if ( false === $elements_result['errors_present'] ) {
						self::$errors_present = false;
						self::$error_text = $elements_result['error_text'];
					}
				} else {
					self::$errors_present = true;
					self::$error_text = __( 'Error adding poll', 'yop-poll' );
				}
				if ( true === isset( $poll->elementsRemoved ) ) {
					$elements_removed = explode( ',', $poll->elementsRemoved );
					foreach ( $elements_removed as $element_removed ) {
						YOP_Poll_Elements::delete( $poll_id, $element_removed );
						YOP_Poll_SubElements::delete_all_for_element( $poll_id, $element_removed );
					}
				}
			}
		} else {
			self::$errors_present = true;
			self::$error_text = __( 'Error updating poll', 'yop-poll' );
		}
		return array(
			'success' => !self::$errors_present,
			'error' => self::$error_text,
			'new-elements' => $elements_result['new_elements'],
			'new-subelements' => $elements_result['new_subelements'],
		);
	}
	public static function delete( $poll_id ) {
		$meta_data = self::get_meta_data( $poll_id );
		if ( '' !== $meta_data['options']['poll']['pageId'] ) {
			wp_delete_post( $meta_data['options']['poll']['pageId'] );
		}
		$data = array( 'status' => 'deleted' );
		$delete_poll_result = $GLOBALS['wpdb']->update(
			$GLOBALS['wpdb']->yop_poll_polls,
			$data,
			array(
				'id' => $poll_id
			)
		);
		if ( false !== $delete_poll_result ) {
			$delete_elements_result = YOP_Poll_Elements::delete_all_for_poll( $poll_id );
			if ( false === $delete_elements_result['errors_present'] ) {
				$delete_subelements_result = YOP_Poll_SubElements::delete_all_for_poll( $poll_id );
				if ( false === $delete_subelements_result['errors_present'] ) {
					self::$errors_present = false;
				} else {
					self::$errors_present = true;
					self::$error_text = $delete_subelements_result['error_text'];
				}
			} else {
				self::$errors_present = true;
				self::$error_text = $delete_poll_result['error_text'];
			}
		} else {
			self::$errors_present = true;
			self::$error_text = __( 'Error deleting poll', 'yop-poll' );
		}
		return array(
			'success' => !self::$errors_present,
			'error' => self::$error_text
		);
	}
	public static function clone_poll( $poll_id ) {
		$current_user = wp_get_current_user();
		$poll_query = $GLOBALS['wpdb']->prepare(
			"SELECT * from {$GLOBALS['wpdb']->yop_poll_polls} WHERE `id`=%s", $poll_id
		);
		$cloned_poll = $GLOBALS['wpdb']->get_row( $poll_query, OBJECT );
		$data = array(
			'name' => $cloned_poll->name . ' ' . __( 'clone', 'yop-poll' ),
			'template' => $cloned_poll->template,
			'template_base' => $cloned_poll->template_base,
			'skin_base' => $cloned_poll->skin_base,
			'author' => $current_user->ID,
			'stype' => 'poll',
			'status' => $cloned_poll->status,
			'meta_data' => $cloned_poll->meta_data,
			'total_submits' => 0,
			'added_date' => current_time( 'mysql' ),
			'modified_date' => current_time( 'mysql' )
		);
		if ( false !== $GLOBALS['wpdb']->insert( $GLOBALS['wpdb']->yop_poll_polls, $data ) ) {
			$new_poll_id = $GLOBALS['wpdb']->insert_id;
			$elements_result = YOP_Poll_Elements::clone_all( $poll_id, $new_poll_id );
			if ( false === $elements_result['errors_present'] ) {
				self::$errors_present = false;
				self::$error_text = $elements_result['error_text'];
			}
			$new_poll_meta_data = unserialize( $cloned_poll->meta_data );
			if ( 'yes' === $new_poll_meta_data['options']['poll']['autoGeneratePollPage'] ) {
				$page_id = wp_insert_post( array(
					'post_title' => $cloned_poll->name . ' ' . __( 'clone', 'yop-poll' ),
					'post_content' => "[yop_poll id='{$new_poll_id}']",
					'post_status' => 'publish',
					'post_type' => 'page',
					'comment_status' => 'open',
					'ping_status' => 'open',
					'post_category' => array( 1 )
				) );
				if ( 0 !== $page_id ) {
					$new_poll_meta_data['options']['poll']['pageId'] = $page_id;
					$ne_poll_meta_data['options']['poll']['pageLink'] = get_permalink( $page_id );
					$data = array(
						'meta_data' => serialize( $new_poll_meta_data )
					);
					if ( false !== $GLOBALS['wpdb']->update( $GLOBALS['wpdb']->yop_poll_polls, $data, array( 'id' => $new_poll_id ) ) ) {
						self::$errors_present = false;
					} else {
						self::$errors_present = true;
						self::$error_text = __( 'Error adding page', 'yop-poll' );
					}
				}
			}
		} else {
			self::$errors_present = true;
			self::$error_text = __( 'Error cloning poll', 'yop-poll' );
		}
		return array(
			'success' => !self::$errors_present,
			'error' => self::$error_text
		);
	}
	public static function reset_poll( $poll_id ) {
		$data = array(
			'total_submits' => '0',
			'total_submited_answers' => '0',
			'modified_date' => current_time( 'mysql' )
		);
		if ( false !== $GLOBALS['wpdb']->update( $GLOBALS['wpdb']->yop_poll_polls, $data, array( 'id' => $poll_id ) ) ) {
			YOP_Poll_SubElements::delete_others_for_poll( $poll_id );
			YOP_Poll_SubElements::reset_submits_for_poll( $poll_id );
			YOP_Poll_Votes::delete_votes_for_poll( $poll_id );
		} else {
			self::$errors_present = true;
			self::$error_text = __( 'Error resetting votes', 'yop-poll' );
		}
		return array(
			'success' => !self::$errors_present,
			'error' => self::$error_text
		);
	}
	public static function create_meta_data( stdClass $poll ) {
		return array(
			'style' => array(
				'poll' => array(
					'backgroundColor' => $poll->design->style->poll->backgroundColor,
					'borderSize' => $poll->design->style->poll->borderSize,
					'borderColor' => $poll->design->style->poll->borderColor,
					'borderRadius' => $poll->design->style->poll->borderRadius,
					'paddingLeftRight' => $poll->design->style->poll->paddingLeftRight,
					'paddingTopBottom' => $poll->design->style->poll->paddingTopBottom
				),
				'questions' => array(
					'textColor' => $poll->design->style->questions->textColor,
					'textSize' => $poll->design->style->questions->textSize,
					'textWeight' => $poll->design->style->questions->textWeight,
					'textAlign' => $poll->design->style->questions->textAlign
				),
				'answers' => array(
					'paddingLeftRight' => $poll->design->style->answers->paddingLeftRight,
					'paddingTopBottom' => $poll->design->style->answers->paddingTopBottom,
					'textColor' => $poll->design->style->answers->textColor,
					'textSize' => $poll->design->style->answers->textSize,
					'textWeight' => $poll->design->style->answers->textWeight,
					'skin' => $poll->design->style->answers->skin,
					'colorScheme' => $poll->design->style->answers->colorScheme
				),
				'buttons' => array(
					'backgroundColor' => $poll->design->style->buttons->backgroundColor,
					'borderSize' => $poll->design->style->buttons->borderSize,
					'borderColor' => $poll->design->style->buttons->borderColor,
					'borderRadius' => $poll->design->style->buttons->borderRadius,
					'paddingLeftRight' => $poll->design->style->buttons->paddingLeftRight,
					'paddingTopBottom' => $poll->design->style->buttons->paddingTopBottom,
					'textColor' => $poll->design->style->buttons->textColor,
					'textSize' => $poll->design->style->buttons->textSize,
					'textWeight' => $poll->design->style->buttons->textWeight
				),
				'captcha' => array(),
				'errors' => array(
					'borderLeftColorForSuccess' => $poll->design->style->errors->borderLeftColorForSuccess,
					'borderLeftColorForError' => $poll->design->style->errors->borderLeftColorForError,
					'borderLeftSize' => $poll->design->style->errors->borderLeftSize,
					'paddingTopBottom' => $poll->design->style->errors->paddingTopBottom,
					'textColor' => $poll->design->style->errors->textColor,
					'textSize' => $poll->design->style->errors->textSize,
					'textWeight' => $poll->design->style->errors->textWeight
				),
				'custom' => array(
					'css' => $poll->design->style->custom->css
				)
			),
			'options' => array(
				'poll' => array(
					'voteButtonLabel' => $poll->options->poll->voteButtonLabel,
					'showResultsLink' => $poll->options->poll->showResultsLink,
					'resultsLabelText' => $poll->options->poll->resultsLabelText,
					'showTotalVotes' => $poll->options->poll->showTotalVotes,
					'showTotalAnswers' => $poll->options->poll->showTotalAnswers,
					'startDateOption' => $poll->options->poll->startDateOption,
					'startDateCustom' => $poll->options->poll->startDateCustom,
					'endDateOption' => $poll->options->poll->endDateOption,
					'endDateCustom' => $poll->options->poll->endDateCustom,
					'redirectAfterVote' => $poll->options->poll->redirectAfterVote,
					'redirectUrl' => $poll->options->poll->redirectUrl,
					'resetPollStatsAutomatically' => $poll->options->poll->resetPollStatsAutomatically,
					'resetPollStatsOn' => $poll->options->poll->resetPollStatsOn,
					'resetPollStatsEvery' => $poll->options->poll->resetPollStatsEvery,
					'resetPollStatsEveryPeriod' => $poll->options->poll->resetPollStatsEveryPeriod,
					'autoGeneratePollPage' => $poll->options->poll->autoGeneratePollPage,
					'pageId' => ( 'yes' === $poll->options->poll->autoGeneratePollPage ) ? $poll->options->poll->pageId : '',
					'pageLink' => ( 'yes' === $poll->options->poll->autoGeneratePollPage ) ? $poll->options->poll->pageLink : '',
					'useCaptcha' => $poll->options->poll->useCaptcha,
					'sendEmailNotifications' => $poll->options->poll->sendEmailNotifications,
					'emailNotificationsFromName' => $poll->options->poll->emailNotificationsFromName,
					'emailNotificationsFromEmail' => $poll->options->poll->emailNotificationsFromEmail,
                    'emailNotificationsRecipients' => $poll->options->poll->emailNotificationsRecipients,
					'emailNotificationsSubject' => $poll->options->poll->emailNotificationsSubject,
					'emailNotificationsMessage' => $poll->options->poll->emailNotificationsMessage,
					'enableGdpr' => $poll->options->poll->enableGdpr,
					'gdprSolution' => $poll->options->poll->gdprSolution,
					'gdprConsentText' => $poll->options->poll->gdprConsentText,
					'loadWithAjax' => $poll->options->poll->loadWithAjax
				),
				'results' => array(
					'showResultsMoment' => $poll->options->results->showResultsMoment,
					'customDateResults' => $poll->options->results->customDateResults,
					'showResultsTo' => $poll->options->results->showResultsTo,
					'resultsDetails' => $poll->options->results->resultsDetails,
					'backToVoteOption' => $poll->options->results->backToVoteOption,
					'backToVoteCaption' => $poll->options->results->backToVoteCaption,
					'sortResults' => $poll->options->results->sortResults,
					'sortResultsRule' => $poll->options->results->sortResultsRule,
					'displayResultsAs' => $poll->options->results->displayResultsAs
				),
				'access' => array(
					'votePermissions' => $poll->options->access->votePermissions,
					/*'allowWordpressVotes' => $poll->options->access->allowWordpressVotes,*/
					'blockVoters' => $poll->options->access->blockVoters,
					'blockLengthType' => $poll->options->access->blockLengthType,
					'blockForValue' => $poll->options->access->blockForValue,
					'blockForPeriod' => $poll->options->access->blockForPeriod,
					'limitVotesPerUser' => $poll->options->access->limitVotesPerUser,
					'votesPerUserAllowed' => $poll->options->access->votesPerUserAllowed
				)
			)
		);
	}
	public static function validate_data( stdClass $poll ) {
		if ( false === is_object( $poll ) ) {
			self::$errors_present = true;
			self::$error_text = __( 'Invalid data', 'yop-poll' );
		} else {
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->name ) ||
				( '' === trim( $poll->name ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Poll Name" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->template ) ||
				( '' === trim( $poll->design->template ) ) ||
				( 0 === intval( $poll->design->template ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Template" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->poll->backgroundColor ) ||
				( '' === trim( $poll->design->style->poll->backgroundColor ) ) ||
				( !ctype_alnum( str_replace( '#', '', $poll->design->style->poll->backgroundColor ) ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Poll Background Color" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->poll->borderSize ) ||
				( '' === trim( $poll->design->style->poll->borderSize ) ) ||
				( !ctype_digit( (string) $poll->design->style->poll->borderSize ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Poll Border Thickness" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->poll->borderColor ) ||
				( '' === trim( $poll->design->style->poll->borderColor ) ) ||
				( !ctype_alnum( str_replace( '#', '', $poll->design->style->poll->borderColor ) ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Poll Border Color" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->poll->borderRadius ) ||
				( '' === trim( $poll->design->style->poll->borderRadius ) ) ||
				( !ctype_digit( (string) $poll->design->style->poll->borderRadius ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Poll Border Radius" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->poll->paddingLeftRight ) ||
				( '' === trim( $poll->design->style->poll->paddingLeftRight ) ) ||
				( !ctype_digit( (string) $poll->design->style->poll->paddingLeftRight ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Poll Padding Left/Right" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->poll->paddingTopBottom ) ||
				( '' === trim( $poll->design->style->poll->paddingTopBottom ) ) ||
				( !ctype_digit( (string) $poll->design->style->poll->paddingTopBottom ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Poll Padding Top/Bottom" is invalid', 'yop-poll' );
			}
			/* QUESTIONS STYLE CHECK */
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->questions->textColor ) ||
				( '' === trim( $poll->design->style->questions->textColor ) ) ||
				( !ctype_alnum( str_replace( '#', '', $poll->design->style->questions->textColor ) ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Question Text Color" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->questions->textSize ) ||
				( '' === trim( $poll->design->style->questions->textSize ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Question Text Size" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->questions->textWeight ) ||
				( '' === trim( $poll->design->style->questions->textWeight ) ) ||
				( !in_array( $poll->design->style->questions->textWeight, self::$text_weight_allowed ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Question Text Weight" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->questions->textAlign ) ||
				( '' === trim( $poll->design->style->questions->textAlign ) ) ||
				( !in_array( $poll->design->style->questions->textAlign, self::$text_align_allowed ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Question Text Align" is invalid', 'yop-poll' );
			}
			/* ANSWERS STYLE CHECK */
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->answers->paddingLeftRight ) ||
				( '' === trim( $poll->design->style->answers->paddingLeftRight ) ) ||
				( !ctype_digit( (string) $poll->design->style->answers->paddingLeftRight ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Answers Padding Left/Right" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->answers->paddingTopBottom ) ||
				( '' === trim( $poll->design->style->answers->paddingTopBottom ) ) ||
				( !ctype_digit( (string) $poll->design->style->answers->paddingTopBottom ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Answers Padding Top/Bottom" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->answers->textColor ) ||
				( '' === trim( $poll->design->style->answers->textColor ) ) ||
				( !ctype_alnum( str_replace( '#', '', $poll->design->style->answers->textColor ) ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Answers Text Color" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->answers->textSize ) ||
				( '' === trim( $poll->design->style->answers->textSize ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Answers Text Size" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->answers->textWeight ) ||
				( '' === trim( $poll->design->style->answers->textWeight ) ) ||
				( !in_array( $poll->design->style->answers->textWeight, self::$text_weight_allowed ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Answers Text Weight" is invalid', 'yop-poll' );
			}
			/* BUTTONS STYLE CHECK */
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->buttons->backgroundColor ) ||
				( '' === trim( $poll->design->style->buttons->backgroundColor ) ) ||
				( !ctype_alnum( str_replace( '#', '', $poll->design->style->buttons->backgroundColor ) ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Buttons Background Color" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->buttons->borderSize ) ||
				( '' === trim( $poll->design->style->buttons->borderSize ) ) ||
				( !ctype_digit( (string) $poll->design->style->buttons->borderSize ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Buttons Border Thickness" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->buttons->borderColor ) ||
				( '' === trim( $poll->design->style->buttons->borderColor ) ) ||
				( !ctype_alnum( str_replace( '#', '', $poll->design->style->buttons->borderColor ) ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Buttons Border Color" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->buttons->borderRadius ) ||
				( '' === trim( $poll->design->style->buttons->borderRadius ) ) ||
				( !ctype_digit( (string) $poll->design->style->buttons->borderRadius ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Buttons Border Radius" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->buttons->paddingLeftRight ) ||
				( '' === trim( $poll->design->style->buttons->paddingLeftRight ) ) ||
				( !ctype_digit( (string) $poll->design->style->buttons->paddingLeftRight ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Buttons Padding Left/Right" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->buttons->paddingTopBottom ) ||
				( '' === trim( $poll->design->style->buttons->paddingTopBottom ) ) ||
				( !ctype_digit( (string) $poll->design->style->buttons->paddingTopBottom ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Buttons Padding Top/Bottom" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->buttons->textColor ) ||
				( '' === trim( $poll->design->style->buttons->textColor ) ) ||
				( !ctype_alnum( str_replace( '#', '', $poll->design->style->buttons->textColor ) ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Buttons Text Color" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->buttons->textSize ) ||
				( '' === trim( $poll->design->style->buttons->textSize ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Buttons Text Size" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->buttons->textWeight ) ||
				( '' === trim( $poll->design->style->buttons->textWeight ) ) ||
				( !in_array( $poll->design->style->answers->textWeight, self::$text_weight_allowed ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Buttons Text Weight" is invalid', 'yop-poll' );
			}
			/* ERRORS STYLE CHECK */
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->errors->borderLeftColorForSuccess ) ||
				( '' === trim( $poll->design->style->errors->borderLeftColorForSuccess ) ) ||
				( !ctype_alnum( str_replace( '#', '', $poll->design->style->errors->borderLeftColorForSuccess ) ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Messages Border Color For Success" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->errors->borderLeftColorForError ) ||
				( '' === trim( $poll->design->style->errors->borderLeftColorForError ) ) ||
				( !ctype_alnum( str_replace( '#', '', $poll->design->style->errors->borderLeftColorForError ) ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Messages Border Color For Error" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->errors->borderLeftSize ) ||
				( '' === trim( $poll->design->style->errors->borderLeftSize ) ) ||
				( !ctype_digit( (string) $poll->design->style->errors->borderLeftSize ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Messages Border Left Thickness" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->errors->paddingTopBottom ) ||
				( '' === trim( $poll->design->style->errors->paddingTopBottom ) ) ||
				( !ctype_digit( (string) $poll->design->style->errors->paddingTopBottom ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Messages Padding Top/Bottom" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->errors->textColor ) ||
				( '' === trim( $poll->design->style->errors->textColor ) ) ||
				( !ctype_alnum( str_replace( '#', '', $poll->design->style->errors->textColor ) ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Buttons Text Color" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->errors->textSize ) ||
				( '' === trim( $poll->design->style->errors->textSize ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Messages Text Size" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->design->style->errors->textWeight ) ||
				( '' === trim( $poll->design->style->errors->textWeight ) ) ||
				( !in_array( $poll->design->style->answers->textWeight, self::$text_weight_allowed ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Messages Text Weight" is invalid', 'yop-poll' );
			}
			/* POLL ELEMENTS CHECK */
			if (
			 	( false === self::$errors_present ) &&
				( 0 === count( $poll->elements ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'No elements present', 'yop-poll' );
			}
			if ( false === self::$errors_present ) {
				foreach( $poll->elements as $element ) {
					switch ( $element->type ) {
						case 'text-question': {
							if (
								( false === self::$errors_present ) &&
								( !isset( $element->text ) ||
								( '' === trim( $element->text ) ) )
							) {
								self::$errors_present = true;
								self::$error_text = __( 'Data for "Question" is invalid', 'yop-poll' );
							}
							if (
								( false === self::$errors_present ) &&
								( 0 == count( $element->answers ) )
							){
								self::$errors_present = true;
								self::$error_text = __( 'At least one answer per question is required', 'yop-poll' );
							}
							if (
								( false === self::$errors_present )
							){
								foreach( $element->answers as $answer ) {
									if (
										( false === self::$errors_present ) &&
										( !isset( $answer->text ) ||
										( '' === trim( $answer->text ) ) )
									){
										self::$errors_present = true;
										self::$error_text = __( 'Answer text is invalid', 'yop-poll' );
									}
									if (
										( false === self::$errors_present ) &&
										!in_array( $answer->options->makeDefault, self::$yes_no_allowed )
									) {
										self::$errors_present = true;
										self::$error_text = __( 'Data for default answer is invalid', 'yop-poll' );
									}
									if (
										( false === self::$errors_present ) &&
										!in_array( $answer->options->makeLink, self::$yes_no_allowed )
									) {
										self::$errors_present = true;
										self::$error_text = __( ' Data for "Answer Link" is invalid', 'yop-poll' );
									}
									if (
										( false === self::$errors_present ) &&
										( 'yes' === $answer->options->makeLink ) &&
										( ( !isset( $answer->options->link ) ) ||
										( '' === trim( $answer->options->link ) ) ||
										!filter_var( $answer->options->link, FILTER_VALIDATE_URL ) )
									) {
										self::$errors_present = true;
										self::$error_text = __( 'Data for "Answer link" is invalid', 'yop-poll' );
									}
								}
							}
							if (
								( false === self::$errors_present ) &&
								( !in_array( $element->options->allowOtherAnswers, self::$yes_no_allowed ) )
							){
								self::$errors_present = true;
								self::$error_text = __( 'Data for "Allow other options" is invalid', 'yop-poll' );
							}
							if (
								( false === self::$errors_present ) &&
								( !isset( $element->options->otherAnswersLabel ) ||
								( '' === trim( $element->options->otherAnswersLabel ) ) )
							){
								self::$errors_present = true;
								self::$error_text = __( 'Data for "Label for Other Answers" is invalid', 'yop-poll' );
							}
							if (
								( false === self::$errors_present ) &&
								( !in_array( $element->options->addOtherAnswers, self::$yes_no_allowed ) )
							){
								self::$errors_present = true;
								self::$error_text = __( 'Data for "Add other answers in answer list" is invalid', 'yop-poll' );
							}
							if (
								( false === self::$errors_present ) &&
								( !in_array( $element->options->displayOtherAnswersInResults, self::$yes_no_allowed ) )
							){
								self::$errors_present = true;
								self::$error_text = __( 'Data for "Display other answers in results list" is invalid', 'yop-poll' );
							}
							if (
								( false === self::$errors_present ) &&
								( !in_array( $element->options->allowMultipleAnswers, self::$yes_no_allowed ) )
							){
								self::$errors_present = true;
								self::$error_text = __( 'Data for "Allow multiple answers " is invalid', 'yop-poll' );
							}
							if (
								( false === self::$errors_present ) &&
								( !isset( $element->options->multipleAnswersMinim ) ||
								( '' === trim( $element->options->multipleAnswersMinim ) ) )
							){
								self::$errors_present = true;
								self::$error_text = __( 'Data for "Minimum answers required" is invalid', 'yop-poll' );
							}
							if (
								( false === self::$errors_present ) &&
								( !isset( $element->options->multipleAnswersMaxim ) ||
								( '' === trim( $element->options->multipleAnswersMaxim ) ) )
							){
								self::$errors_present = true;
								self::$error_text = __( 'Data for "Maximum answers required" is invalid', 'yop-poll' );
							}
							if (
								( false === self::$errors_present ) &&
								( intval( $element->options->multipleAnswersMinim ) > intval( $element->options->multipleAnswersMaxim ) )
							){
								self::$errors_present = true;
								self::$error_text = __( 'Data for "Minimum answers required" is invalid', 'yop-poll' );
							}
							if (
								( false === self::$errors_present ) &&
								( !in_array( $element->options->answersDisplay, self::$answers_display_allowed ) )
							){
								self::$errors_present = true;
								self::$error_text = __( 'Data for "Display answers" is invalid', 'yop-poll' );
							}
							if (
								( false === self::$errors_present ) &&
								( 'columns' === $element->options->answersDisplay ) &&
								( !isset( $element->options->answersColumns ) ||
								( '' === trim( $element->options->answersColumns ) ) ||
								( 0 === intval( $element->options->answersColumns ) ) )
							){
								self::$errors_present = true;
								self::$error_text = __( 'Data for "Maximum answers required" is invalid', 'yop-poll' );
							}
							if (
								( false === self::$errors_present ) &&
								( !in_array( $element->options->answersSort, self::$answers_sort_allowed ) )
							){
								self::$errors_present = true;
								self::$error_text = __( 'Data for "Sort Answers" is invalid', 'yop-poll' );
							}
							break;
						}
						case 'custom-field': {
							if (
								( false === self::$errors_present ) &&
								( !isset( $element->text ) ||
								( '' === trim( $element->text ) ) )
							) {
								self::$errors_present = true;
								self::$error_text = __( 'Data for "Custom Field" is invalid', 'yop-poll' );
							}
							if (
								( false === self::$errors_present ) &&
								( !in_array( $element->options->makeRequired, self::$yes_no_allowed ) )
							){
								self::$errors_present = true;
								self::$error_text = __( 'Data for "Make Required" is invalid', 'yop-poll' );
							}
							break;
						}
						default: {
							break;
						}
					}
				}
			}
			/* POLL OPTIONS->POLL CHECK */
			if (
				( false === self::$errors_present ) &&
				( !isset( $poll->options->poll->voteButtonLabel ) ||
				( '' === trim( $poll->options->poll->voteButtonLabel ) ) )
			 ) {
				 self::$errors_present = true;
				 self::$error_text = __( 'Data for "Vote Button Label" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->poll->showResultsLink, self::$yes_no_allowed ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Show [Results] link" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( 'yes' === $poll->options->poll->showResultsLink ) &&
				( !isset( $poll->options->poll->resultsLabelText ) ||
				( '' === trim( $poll->options->poll->resultsLabelText ) ) )
			 ) {
				 self::$errors_present = true;
				 self::$error_text = __( 'Data for "[Results] Link Label" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->poll->showTotalVotes, self::$yes_no_allowed ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Show Total Votes" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->poll->showTotalAnswers, self::$yes_no_allowed ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Show Total Answers" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->poll->startDateOption, self::$date_values_allowed ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Start Date" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( 'custom' === $poll->options->poll->startDateOption ) &&
				( !isset( $poll->options->poll->startDateCustom ) ||
				( '' === trim( $poll->options->poll->startDateCustom ) ) )
			 ) {
				 self::$errors_present = true;
				 self::$error_text = __( 'Data for "Start Date" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->poll->endDateOption, self::$date_values_allowed ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "End Date" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( 'custom' === $poll->options->poll->endDateOption ) &&
				( !isset( $poll->options->poll->endDateCustom ) ||
				( '' === trim( $poll->options->poll->endDateCustom ) ) )
			 ) {
				 self::$errors_present = true;
				 self::$error_text = __( 'Data for "End Date" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->poll->redirectAfterVote, self::$yes_no_allowed ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Redirect after vote" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( 'yes' === $poll->options->poll->redirectAfterVote ) &&
				( !isset( $poll->options->poll->redirectUrl ) ||
				( '' === trim( $poll->options->poll->redirectUrl ) ) )
			 ) {
				 self::$errors_present = true;
				 self::$error_text = __( 'Data for "Redirect Url" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->poll->resetPollStatsAutomatically, self::$yes_no_allowed ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Reset Poll Stats automatically" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->poll->resetPollStatsEveryPeriod, self::$reset_stats_allowed ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Reset Every" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->poll->autoGeneratePollPage, self::$yes_no_allowed ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Auto Generate Poll Page" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->poll->useCaptcha, self::$captcha_allowed ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Use Captcha" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->poll->sendEmailNotifications, self::$yes_no_allowed ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Send Email notifications" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( 'yes' === $poll->options->poll->sendEmailNotifications ) &&
				( !isset( $poll->options->poll->emailNotificationsFromName ) ||
				( '' === trim( $poll->options->poll->emailNotificationsFromName ) ) )
			 ) {
				 self::$errors_present = true;
				 self::$error_text = __( 'Data for "From Name" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( 'yes' === $poll->options->poll->sendEmailNotifications ) &&
				( !isset( $poll->options->poll->emailNotificationsFromEmail ) ||
				( '' === trim( $poll->options->poll->emailNotificationsFromEmail ) ) )
			 ) {
				 self::$errors_present = true;
				 self::$error_text = __( 'Data for "From Email" is invalid', 'yop-poll' );
			}
            if (
                ( false === self::$errors_present ) &&
                ( 'yes' === $poll->options->poll->sendEmailNotifications ) &&
                ( !isset( $poll->options->poll->emailNotificationsRecipients ) ||
                    ( '' === trim( $poll->options->poll->emailNotificationsRecipients ) ) )
            ) {
                self::$errors_present = true;
                self::$error_text = __( 'Data for "Recipients" is invalid', 'yop-poll' );
            }
			if (
				( false === self::$errors_present ) &&
				( 'yes' === $poll->options->poll->sendEmailNotifications ) &&
				( !isset( $poll->options->poll->emailNotificationsSubject ) ||
				( '' === trim( $poll->options->poll->emailNotificationsSubject ) ) )
			 ) {
				 self::$errors_present = true;
				 self::$error_text = __( 'Data for "Subject" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( 'yes' === $poll->options->poll->sendEmailNotifications ) &&
				( !isset( $poll->options->poll->emailNotificationsMessage ) ||
				( '' === trim( $poll->options->poll->emailNotificationsMessage ) ) )
			 ) {
				 self::$errors_present = true;
				 self::$error_text = __( 'Data for "Message" is invalid', 'yop-poll' );
			}
			/* POLL OPTIONS->RESULTS CHECK */
			if (
				( false === self::$errors_present ) &&
				( 0 < count( $poll->options->results->showResultsMoment ) ) &&
				( 0 === count( array_intersect( $poll->options->results->showResultsMoment, self::$show_results_allowed ) ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Show results" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( in_array( 'custom-date', $poll->options->results->showResultsMoment ) ) &&
				( !isset( $poll->options->results->customDateResults ) ||
				( '' === trim( $poll->options->results->customDateResults ) ) )
			 ) {
				 self::$errors_present = true;
				 self::$error_text = __( 'Data for "Show Results" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( 0 < count( $poll->options->results->showResultsMoment ) ) && !in_array('never', $poll->options->results->showResultsMoment ) &&
				( ( 0 === count( $poll->options->results->showResultsTo )) ||
				( 0 === count( array_intersect( $poll->options->results->showResultsTo, self::$show_results_to_allowed ) ) ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Show results to" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->results->backToVoteOption, self::$yes_no_allowed ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Display [Back to vote] link" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( 'yes' === $poll->options->results->backToVoteOption ) &&
				( !isset( $poll->options->results->backToVoteCaption ) ||
				( '' === trim( $poll->options->results->backToVoteCaption ) ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "[Back to vote] caption" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->results->sortResults, self::$sort_results_allowed ) )
			){
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Sort Results" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( 'number-of-votes' === $poll->options->results->sortResults ) &&
				( !in_array( $poll->options->results->sortResultsRule, self::$sort_results_rule_allowed ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Sort rule" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->results->displayResultsAs, self::$display_results_as_allowed ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Display Results As"', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( ( 0 === count( $poll->options->access->votePermissions ) ) ||
				( 0 === count( array_intersect( $poll->options->access->votePermissions, self::$vote_permissions_allowed ) ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Vote Permissions" is invalid', 'yop-poll' );
			}
			/*
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->access->allowWordpressVotes, self::$yes_no_allowed ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Wordpress" is invalid', 'yop-poll' );
			}
			*/
			if (
				( false === self::$errors_present ) &&
				( count( $poll->options->access->blockVoters ) > 0 ) &&
				( 0 === count( array_intersect($poll->options->access->blockVoters, self::$block_voters_allowed) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Block Voters" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( count( $poll->options->access->blockVoters ) > 0 ) &&
				( !in_array( 'no-block', $poll->options->access->blockVoters ) ) &&
				( 'limited-time' === $poll->options->access->blockLengthType ) &&
				( ( !isset( $poll->options->access->blockForValue ) ||
				( 0 === intval( $poll->options->access->blockForValue ) ) ) ||
				( !in_array( $poll->options->access->blockForPeriod, self::$block_voters_period_allowed ) ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Block Period" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( !in_array( $poll->options->access->limitVotesPerUser, self::$yes_no_allowed ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Limit Number Of Votes per User" is invalid', 'yop-poll' );
			}
			if (
				( false === self::$errors_present ) &&
				( 'yes' === $poll->options->access->limitVotesPerUser ) &&
				( 0 === intval( $poll->options->access->votesPerUserAllowed ) )
			) {
				self::$errors_present = true;
				self::$error_text = __( 'Data for "Votes per user" is invalid', 'yop-poll' );
			}
		}
	}
    public static function is_ended( $poll, $is_serialized ) {
        $today = date( 'Y-m-d H:i:s', strtotime( current_time( 'mysql' ) ) );
        if ( false === $is_serialized ) {
            $poll_meta_data = unserialize( $poll['meta_data'] );
        } else {
            $poll_meta_data = $poll->meta_data;
        }
        if ( 'custom' === $poll_meta_data['options']['poll']['endDateOption'] ) {
            $end_date = date( 'Y-m-d H:i:s', strtotime( $poll_meta_data['options']['poll']['endDateCustom'] ) );
            if ( false !== $end_date ) {
                if ( $today > $end_date ) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public static function ends_soon( $poll ) {
        $ends_soon_date = date( 'Y-m-d H:i:s', strtotime( current_time( 'mysql' ) . ' + ' . self::$ends_soon_interval. ' days' ) );
		$poll_meta_data = unserialize( $poll['meta_data'] );
		if ( 'custom' === $poll_meta_data['options']['poll']['endDateOption'] ) {
            $end_date = date( 'Y-m-d H:i:s', strtotime( $poll_meta_data['options']['poll']['endDateCustom'] ) );
            if ( false !== $end_date ) {
                if ( $ends_soon_date >= $end_date ) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
		} else {
		    return false;
        }
	}
	public static function get_poll_for_admin(
			$poll_id,
			$elements_order_by = 'sorder',
			$elements_sort_rule = 'ASC',
			$sub_elements_order_by = 'sorder',
			$sub_elements_sort_rule = 'ASC'
		) {
		$query = $GLOBALS['wpdb']->prepare(
			"SELECT * FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `id` = %s AND `status` != 'deleted'", $poll_id
		);
		$poll = $GLOBALS['wpdb']->get_row( $query, OBJECT );
		if( null !== $poll ){
			$poll_meta_data = unserialize( $poll->meta_data );
			$poll->meta_data = array(
				'style' => self::convert_meta_data_for_style( $poll_meta_data['style'] ),
				'options' => $poll_meta_data['options']
			);
			$poll_elements = YOP_Poll_Elements::get( $poll_id, $elements_order_by, $elements_sort_rule );
			$poll_sub_elements = YOP_Poll_SubElements::get( $poll_id, $sub_elements_order_by, $sub_elements_sort_rule );
			foreach ( $poll_elements as $poll_element ) {
				foreach ( $poll_sub_elements as $poll_sub_element ) {
					if ( $poll_element->id === $poll_sub_element->element_id ) {
						$poll_element->answers[] = $poll_sub_element;
					}
				}
			}
			$poll->elements = $poll_elements;
			return $poll;
		} else {
			return false;
		}
	}
	public static function get_poll_for_voting( $poll_id ) {
		if ( true === isset( $poll_id ) ) {
			$query = $GLOBALS['wpdb']->prepare(
				"SELECT * FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `id` = %s AND `status` !='deleted'", $poll_id
			);
			$poll = $GLOBALS['wpdb']->get_row( $query, OBJECT );
			if ( null !== $poll ){
				$poll_meta_data = unserialize( $poll->meta_data );
				$poll->meta_data = array(
					'style' => self::convert_meta_data_for_style( $poll_meta_data['style'] ),
					'options' => $poll_meta_data['options']
				);
				$poll_elements = YOP_Poll_Elements::get_all_for_poll( $poll_id );
				foreach( $poll_elements as $poll_element ) {
					if ( true === isset( $poll_element->meta_data['answersSort'] ) ) {
						switch( $poll_element->meta_data['answersSort'] ) {
							case 'as-defined': {
								$sub_elements_order_by = 'sorder';
								$sub_elements_order_rule = 'ASC';
								break;
							}
							case 'alphabetically-asc': {
								$sub_elements_order_by = 'stext';
								$sub_elements_order_rule = 'ASC';
								break;
							}
							case 'alphabetically-desc': {
								$sub_elements_order_by = 'stext';
								$sub_elements_order_rule = 'DESC';
								break;
							}
							case 'random': {
								$sub_elements_order_by = 'random';
								$sub_elements_order_rule = 'ASC';
								break;
							}
						}
					} else {
						$sub_elements_order_by = 'sorder';
						$sub_elements_order_rule = 'ASC';
					}
					$poll_element->answers = YOP_Poll_SubElements::get_all_for_element( $poll_element->id, $sub_elements_order_by, $sub_elements_order_rule );
				}
				$poll->elements = $poll_elements;
				return $poll;
			}
		} else {
			return false;
		}
	}
	public static function get_poll_for_results( $poll_id ) {
		$elements_order_by = 'sorder';
		$elements_sort_rule = 'ASC';
		$sub_elements_order_by = 'sorder';
		$sub_elements_sort_rule = 'ASC';
		$query = $GLOBALS['wpdb']->prepare(
			"SELECT * FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `id` = %s AND `status` !='deleted'", $poll_id
		);
		$poll = $GLOBALS['wpdb']->get_row( $query, OBJECT );
		if( null !== $poll ){
			$poll->meta_data = unserialize( $poll->meta_data );
			switch ( $poll->meta_data['options']['results']['sortResults'] ) {
				case 'as-defined': {
					$sub_elements_order_by = 'sorder';
					break;
				}
				case 'alphabetical': {
					$sub_elements_order_by = 'stext';
					break;
				}
				case 'number-of-votes':{
					$sub_elements_order_by = 'total_submits';
					break;
				}
				default: {
					$sub_elements_order_by = 'sorder';
					break;
				}
			}
			if ( 'sorder' === $sub_elements_order_by ) {
				$sub_elements_sort_rule = 'ASC';
			} else {
				switch ( $poll->meta_data['options']['results']['sortResultsRule'] ) {
					case 'asc': {
						$sub_elements_sort_rule = 'ASC';
						break;
					}
					case 'desc': {
						$sub_elements_sort_rule = 'DESC';
						break;
					}
					default: {
						$sub_elements_sort_rule = 'ASC';
						break;
					}
				}
			}
			$poll_elements = YOP_Poll_Elements::get( $poll_id, $elements_order_by, $elements_sort_rule );
			$poll_sub_elements = YOP_Poll_SubElements::get( $poll_id, $sub_elements_order_by, $sub_elements_sort_rule );
			foreach ( $poll_elements as $poll_element ) {
				foreach ( $poll_sub_elements as $poll_sub_element ) {
					if ( $poll_element->id === $poll_sub_element->element_id ) {
						$poll_element->answers[] = $poll_sub_element;
					}
				}
				if ( 
					( true === isset( $poll_element->meta_data['displayOtherAnswersInResults'] ) ) && 
					( 'yes' === $poll_element->meta_data['displayOtherAnswersInResults'] ) &&
					( 'no' === $poll_element->meta_data['addOtherAnswers'] )
					) {
					$element_other_answers = YOP_Poll_Other_Answers::get_for_element( $poll_element->id );
					if ( count( $element_other_answers ) > 0 ) {
						$i = 1;
						foreach( $element_other_answers as $other_answer ) {
							$poll_element->answers[] = ( object ) array(
								'id' => 0,
								'poll_id' => $poll_id,
								'element_id' => $poll_element->id,
								'stext' => $other_answer->answer,
								'author' => '',
								'textExtra' => '',
								'stype' => 'text',
								'status' => 'active',
								'sorder' => count( $poll_element->answers ) + $i,
								'meta_data' => array(
									'makeDefault' => '',
									'makeLink' => '',
									'link' => '',
									'resultsColor' => isset( $poll_element->meta_data['resultsColorForOtherAnswers'] ) ? $poll_element->meta_data['resultsColorForOtherAnswers'] : '#000000'
								),
								'total_submits' => $other_answer->total_submits
							);
							$i++;
						}
					}
					$poll_element->answers = YOP_POLL_Elements::order_subelements( $poll_element->answers, $poll->meta_data['options']['results']['sortResults'], $poll->meta_data['options']['results']['sortResultsRule'] );
				}
			}
			$poll->elements = $poll_elements;
			return $poll;
		} else {
			return false;
		}
	}
	public static function get_current_active() {
		$query = "SELECT max(`id`) FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `status` = 'published'";
		$poll_id = $GLOBALS['wpdb']->get_var( $query );
		return $poll_id;
	}
	public static function get_latest() {
		$query = "SELECT max(`id`) FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `status` = 'published'";
		$poll_id = $GLOBALS['wpdb']->get_var( $query );
		return $poll_id;
	}
	public static function get_random() {
		$query = "SELECT `id` FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `status` = 'published' ORDER BY rand() LIMIT 1";
		$poll_id = $GLOBALS['wpdb']->get_var( $query );
		return $poll_id;
	}
	public static function get_meta_data( $poll_id ) {
		$query = $GLOBALS['wpdb']->prepare(
			"SELECT `meta_data` FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `id` = %s", $poll_id
		);
		$poll = $GLOBALS['wpdb']->get_col( $query );
		if ( 1 === count( $poll ) ) {
			return unserialize( $poll[0] );
		} else {
			return false;
		}
	}
	public static function get_info( $poll_id ) {
		$query = $GLOBALS['wpdb']->prepare(
			"SELECT * FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `id` = %s AND `status` != 'deleted'", $poll_id
		);
		$poll = $GLOBALS['wpdb']->get_row( $query );
		if ( ( true === isset( $poll->id ) ) && ( $poll->id > 0 ) ) {
			return $poll;
		} else {
			return false;
		}
	}
	public static function convert_meta_data_for_style( $meta_data_for_style ) {
		/*BEGIN POLL*/
		if ( false === isset( $meta_data_for_style['poll']['paddingLeftRight'] ) ) {
			$meta_data_for_style['poll']['paddingLeftRight'] = $meta_data_for_style['poll']['padding'];
		}
		if ( false === isset( $meta_data_for_style['poll']['paddingTopBottom'] ) ) {
			$meta_data_for_style['poll']['paddingTopBottom'] = $meta_data_for_style['poll']['padding'];
		}
		/*END POLL*/
		/*BEGIN QUESTIONS*/
		if ( true === in_array( $meta_data_for_style['questions']['textSize'], array( 'small', 'medium', 'large' ) ) ) {
			switch( $meta_data_for_style['questions']['textSize'] ) {
				case 'small': {
					$meta_data_for_style['questions']['textSize'] = '14';
					break;
				}
				case 'small': {
					$meta_data_for_style['questions']['textSize'] = '16';
					break;
				}
				case 'small': {
					$meta_data_for_style['questions']['textSize'] = '18';
					break;
				}
			}
		}
		if ( false === isset( $meta_data_for_style['questions']['textWeight'] ) ) {
			$meta_data_for_style['questions']['textWeight'] = 'normal';
		}
		if ( false === isset( $meta_data_for_style['questions']['textAlign'] ) ) {
			$meta_data_for_style['questions']['textAlign'] = 'left';
		}
		/*END QUESTIONS*/
		/*BEGIN ANSWERS*/
		if ( false === isset( $meta_data_for_style['answers']['paddingLeftRight'] ) ) {
			$meta_data_for_style['answers']['paddingLeftRight'] = $meta_data_for_style['answers']['padding'];
		}
		if ( false === isset( $meta_data_for_style['answers']['paddingTopBottom'] ) ) {
			$meta_data_for_style['answers']['paddingTopBottom'] = $meta_data_for_style['answers']['padding'];
		}
		if ( true === in_array( $meta_data_for_style['answers']['textSize'], array( 'small', 'medium', 'large' ) ) ) {
			switch( $meta_data_for_style['answers']['textSize'] ) {
				case 'small': {
					$meta_data_for_style['answers']['textSize'] = '14';
					break;
				}
				case 'small': {
					$meta_data_for_style['answers']['textSize'] = '16';
					break;
				}
				case 'small': {
					$meta_data_for_style['answers']['textSize'] = '18';
					break;
				}
			}
		}
		if ( false === isset( $meta_data_for_style['answers']['textWeight'] ) ) {
			$meta_data_for_style['answers']['textWeight'] = 'normal';
		}
		/*END ANSWERS*/
		/*BEGIN BUTTONS*/
		if ( false === isset( $meta_data_for_style['buttons']['paddingLeftRight'] ) ) {
			$meta_data_for_style['buttons']['paddingLeftRight'] = '10';
		}
		if ( false === isset( $meta_data_for_style['buttons']['paddingTopBottom'] ) ) {
			$meta_data_for_style['buttons']['paddingTopBottom'] = '5';
		}
		if ( true === in_array( $meta_data_for_style['buttons']['textSize'], array( 'small', 'medium', 'large' ) ) ) {
			switch( $meta_data_for_style['buttons']['textSize'] ) {
				case 'small': {
					$meta_data_for_style['buttons']['textSize'] = '14';
					break;
				}
				case 'small': {
					$meta_data_for_style['buttons']['textSize'] = '16';
					break;
				}
				case 'small': {
					$meta_data_for_style['buttons']['textSize'] = '18';
					break;
				}
			}
		}
		if ( false === isset( $meta_data_for_style['buttons']['textWeight'] ) ) {
			$meta_data_for_style['buttons']['textWeight'] = 'normal';
		}
		/*END BUTTONS*/
		/*BEGIN ERRORS*/
		if ( false === isset( $meta_data_for_style['errors']['borderLeftColorForSuccess'] ) ) {
			$meta_data_for_style['errors']['borderLeftColorForSuccess'] = '#008000';
		}
		if ( false === isset( $meta_data_for_style['errors']['borderLeftColorForError'] ) ) {
			$meta_data_for_style['errors']['borderLeftColorForError'] = '#ff0000';
		}
		if ( false === isset( $meta_data_for_style['errors']['borderLeftSize'] ) ) {
			$meta_data_for_style['errors']['borderLeftSize'] = '10';
		}
		if ( false === isset( $meta_data_for_style['errors']['paddingTopBottom'] ) ) {
			$meta_data_for_style['errors']['paddingTopBottom'] = $meta_data_for_style['errors']['padding'];
		}
		if ( true === in_array( $meta_data_for_style['errors']['textSize'], array( 'small', 'medium', 'large' ) ) ) {
			switch( $meta_data_for_style['errors']['textSize'] ) {
				case 'small': {
					$meta_data_for_style['errors']['textSize'] = '14';
					break;
				}
				case 'small': {
					$meta_data_for_style['errors']['textSize'] = '16';
					break;
				}
				case 'small': {
					$meta_data_for_style['errors']['textSize'] = '18';
					break;
				}
			}
		}
		if ( false === isset( $meta_data_for_style['errors']['textWeight'] ) ) {
			$meta_data_for_style['errors']['textWeight'] = 'normal';
		}
		/*END ERRORS*/
		/*BEGIN CUSTOM*/
		if ( false === isset( $meta_data_for_style['custom']['css'] ) ) {
			$meta_data_for_style['custom']['css'] = '';
		}
		/*END CUSTOM*/
		return $meta_data_for_style;
	}
	public static function has_ended_frontend( $poll ) {
        $today = date( 'Y-m-d H:i:s', strtotime( current_time( 'mysql' ) ) );
		if ( 'custom' === $poll->meta_data['options']['poll']['endDateOption'] ) {
            $end_date = date( 'Y-m-d H:i:s', strtotime( $poll->meta_data['options']['poll']['endDateCustom'] ) );
            if ( $end_date ) {
                if ( $today > $end_date ) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
		} else {
			return false;
		}
	}
	public static function has_started_frontend( $poll ) {
        $today = date( 'Y-m-d H:i:s', strtotime( current_time( 'mysql' ) ) );
		if ( 'custom' === $poll->meta_data['options']['poll']['startDateOption'] ) {
            $end_date = date( 'Y-m-d H:i:s', strtotime( $poll->meta_data['options']['poll']['startDateCustom'] ) );
            if ( $end_date ) {
                if ( $today < $end_date ) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return false;
            }
		} else {
			return true;
		}
	}
	public static function get_results_for_frontend(
		$poll,
		$before_or_after_vote,
		$sort_params = array( 'order_by' => 'sorder', 'sort_order' => 'asc' ) ) {
		switch ( $sort_params['order_by'] ) {
			case 'as-defined' :{
				$sort_params['order_by'] = 'sorder';
				break;
			}
			case 'alphabetical': {
				$sort_params['order_by'] = 'stext';
				break;
			}
			case 'number-of-votes': {
				$sort_params['order_by'] = 'votes';
				break;
			}
			default: {
				$sort_params['order_by'] = 'sorder';
				break;
			}
		}
		switch ( $sort_params['sort_order'] ) {
			case 'asc': {
				$sort_params['sort_order'] = SORT_ASC;
				break;
			}
			case 'desc': {
				$sort_params['sort_order'] = SORT_DESC;
				break;
			}
			default: {
				$sort_params['sort_order'] = SORT_ASC;
				break;
			}
		}
		$results = array();
		$max = 0;
		$i = 0;
		foreach ( $poll->elements[0]->answers as $answer ) {
			if (
				( 'before-vote' === $before_or_after_vote ) ||
				( '0' !== $answer->author ) ||
				( ( '0' === $answer->author ) && ( 'yes' === $poll->elements[0]->meta_data['displayOtherAnswersInResults'] ) )
			) {
				$results[$i]['id'] = $answer->id;
				$results[$i]['stext'] = $answer->stext;
				$results[$i]['sorder'] = $answer->sorder;
				$results[$i]['votes'] = $answer->total_submits;
				if ( 0 < intval( $poll->total_submits ) ) {
					if ( 0 ===  ( 100 * $answer->total_submits % $poll->total_submits ) ) {
						$results[$i]['percentage'] = number_format( $answer->total_submits / $poll->total_submits * 100, 0 );
					} else {
						$results[$i]['percentage'] = number_format( $answer->total_submits / $poll->total_submits * 100, 2 );
					}
					if ( $answer->total_submits >= $max ) {
						$max = $answer->total_submits;
					}
				} else {
					$results[$i]['percentage'] = 0;
				}
				$i++;
			}
		}
		foreach ( $results as &$result ) {
			if ( $result['votes'] === $max ) {
				$result['winner'] = 'yes';
			} else {
				$result['winner'] = 'no';
			}
		}
		foreach ( $results as $key => $row ) {
			$order_by['sorder'][$key] = $row['sorder'];
			$order_by['stext'][$key] = $row['stext'];
			$order_by['votes'][$key] = $row['votes'];
			$order_by['percentage'][$key] = $row['percentage'];
		}
		if ( 0 < count( $results) ) {
			array_multisort( $order_by[$sort_params['order_by']], $sort_params['sort_order'], $results );
		}
		return $results;
	}
	public static function add_vote( $poll_id, $total_submited_answers ) {
		$query = $GLOBALS['wpdb']->prepare(
			"UPDATE {$GLOBALS['wpdb']->yop_poll_polls} SET `total_submits` = `total_submits` + 1, "
			. "`total_submited_answers` = `total_submited_answers` + {$total_submited_answers} WHERE `id` = %s", $poll_id
		);
		$GLOBALS['wpdb']->query( $query );
	}
	public static function is_show_results_before_vote( $poll ) {
		$show = false;
		if ( true === in_array( 'before-vote', $poll->meta_data['options']['results']['showResultsMoment'] ) ) {
			if (
				( 1 === count( $poll->meta_data['options']['results']['showResultsTo'] ) ) &&
				( true === in_array( 'registered', $poll->meta_data['options']['results']['showResultsTo'] ) )
				) {
				if ( true === is_user_logged_in() ) {
					$show = true;
				} else {
					$show = false;
				}
			} else {
				$show = true;
			}
		} else {
			$show = false;
		}
		return $show;
	}
	public static function is_show_results_after_vote( $poll ) {
		$show = false;
		if ( true === in_array( 'after-vote', $poll->meta_data['options']['results']['showResultsMoment'] ) ) {
			if (
				( 1 === count( $poll->meta_data['options']['results']['showResultsTo'] ) ) &&
				( true === in_array( 'registered', $poll->meta_data['options']['results']['showResultsTo'] ) )
				) {
				if ( true === is_user_logged_in() ) {
					$show = true;
				} else {
					$show = false;
				}
			} else {
				$show = true;
			}
		} else {
			$show = false;
		}
		return $show;
	}
	public static function generate_results_after_vote( $poll ) {
		$poll_results = array();
		foreach ( $poll->elements as $element ) {
			if ( true === in_array( $element->etype, array( 'text-question', 'media-question' ) ) ) {
				$element_results = array();
				$element_results['id'] = $element->id;
				$element_results['text'] = $element->etext;
				$element_results['type'] = $element->etype;
				$element_results['answers'] = array();
				foreach ( $element->answers as $subelement ) {
					$element_results['answers'][] = array(
						'id' => $subelement->id,
						'text' => $subelement->stext,
						'type' => $subelement->stype,
						'makeLink' => isset( $subelement->meta_data['makeLink'] ) ? $subelement->meta_data['makeLink'] : '',
						'link' => isset( $subelement->meta_data['link'] ) ? $subelement->meta_data['link'] : '',
						'color' => $subelement->meta_data['resultsColor'],
						'votes' => $subelement->total_submits
					);
				}
				$poll_results[] = $element_results;
			}
		}
		return $poll_results;
	}
	public static function get_polls_for_cron() {
		$returned_polls = array();
		$query = "SELECT `id`, `meta_data` FROM {$GLOBALS['wpdb']->yop_poll_polls} WHERE `status` != 'deleted'";
		$polls = $GLOBALS['wpdb']->get_results( $query, ARRAY_A );
		foreach ( $polls as $poll ) {
			$poll_meta_data = unserialize( $poll['meta_data'] );
			array_push( $returned_polls, array(
				'id' => $poll['id'],
				'resetPollStatsAutomatically' => $poll_meta_data['options']['poll']['resetPollStatsAutomatically'],
				'resetPollStatsOn' => $poll_meta_data['options']['poll']['resetPollStatsOn'],
				'resetPollStatsEvery' => $poll_meta_data['options']['poll']['resetPollStatsEvery'],
				'resetPollStatsEveryPeriod' => $poll_meta_data['options']['poll']['resetPollStatsEveryPeriod']
			));
		}
		return $returned_polls;
	}
	public static function reset_stats_for_poll( $poll_id ) {
		$query = $GLOBALS['wpdb']->prepare(
			"UPDATE {$GLOBALS['wpdb']->yop_poll_polls} SET `total_submits` = '0', `total_submited_answers` = '0' WHERE `id` = %s", $poll_id
		);
		$GLOBALS['wpdb']->query( $query );
		YOP_POLL_SubElements::reset_submits_for_poll( $poll_id );
		YOP_Poll_Votes::delete_votes_for_poll( $poll_id );
	}
	public static function update_meta_data( $poll_id, $meta_flag_owner, $meta_flag, $meta_value ) {
		$meta_data = self::get_meta_data( $poll_id );
		$meta_data['options'][$meta_flag_owner][$meta_flag] = $meta_value;
		$query = $GLOBALS['wpdb']->prepare(
			"UPDATE {$GLOBALS['wpdb']->yop_poll_polls} SET `meta_data` = %s, `total_submited_answers` = '0' WHERE `id` = %s",
				serialize( $meta_data ),
				$poll_id
		);
		$GLOBALS['wpdb']->query( $query );
	}
	public static function accept_votes_from_user( $poll, $user, $user_type ) {
		$search_field = '';
		$total_votes_for_user = '';
		$accept_votes_from_user = true;
		switch( $user_type ) {
			case 'wordpress': {
				$search_field = "user_id";
				break;
			}
			case 'facebook': {
				$search_field = 'user_email';
				break;
			}
			case 'google': {
				$search_field = 'user_email';
				break;
			}
			default: {
				$search_field = 'user_email';
				break;
			}
		}
		if ( ( 'yes' === $poll->meta_data['options']['access']['limitVotesPerUser'] ) && ( $poll->meta_data['options']['access']['votesPerUserAllowed'] > 0 ) ) {
			$query = $GLOBALS['wpdb']->prepare(
				"SELECT COUNT(*) FROM {$GLOBALS['wpdb']->yop_poll_votes} WHERE `" . $search_field . "` = %s AND `poll_id` = %d AND `status` = 'active'", $user, $poll->id
			);
			$total_votes_for_user = $GLOBALS['wpdb']->get_var( $query );
			if ( $total_votes_for_user >= $poll->meta_data['options']['access']['votesPerUserAllowed'] ) {
				$accept_votes_from_user = false;
			} else {
				$accept_votes_from_user = true;
			}
		}
		return $accept_votes_from_user;
	}
	public static function accept_votes_from_anonymous( $poll, $voter_data ) {
		$accept_votes_from_anonymous = true;
		$should_continue = true;
		$previous_vote = null;
		if ( false === isset( $poll->meta_data['options']['access']['blockLengthType'] ) ) {
			$poll->meta_data['options']['access']['blockLengthType'] = 'limited-time';
		}
		if ( true === in_array( 'by-cookie', $poll->meta_data['options']['access']['blockVoters'] ) ) {
			if ( '' !== $voter_data['c-data'] ) {
				$previous_vote = YOP_Poll_Votes::get_vote( $poll->id, 'voter_id', $voter_data['c-data'] );
			}
		}
		if ( null !== $previous_vote ) {
			if ( 'forever' === $poll->meta_data['options']['access']['blockLengthType'] ) {
				$accept_votes_from_anonymous = false;
				$should_continue = false;
			} else {
				switch ( $poll->meta_data['options']['access']['blockForPeriod'] ) {
					case 'minutes': {
						$block_for_period = 'PT' . $poll->meta_data['options']['access']['blockForValue'] . 'M';
						break;
					}
					case 'hours': {
						$block_for_period = 'PT' . $poll->meta_data['options']['access']['blockForValue'] . 'H';
						break;
					}
					case 'days': {
						$block_for_period = 'P' . $poll->meta_data['options']['access']['blockForValue'] . 'D';
						break;
					}
				}
				$current_vote_date = new DateTime( get_gmt_from_date( current_time( 'mysql' ) ), new DateTimeZone( 'UTC' ) );
				$previous_vote_date = new DateTime( get_gmt_from_date( $previous_vote->added_date ), new DateTimeZone( 'UTC' ) );
				$previous_vote_date->add( new DateInterval( $block_for_period ) );
				if ( $current_vote_date < $previous_vote_date ) {
					$accept_votes_from_anonymous = false;
					$should_continue = false;
				}
			}
		}
		if ( true === $should_continue ) {
			$previous_vote = null;
			if ( true === in_array( 'by-ip', $poll->meta_data['options']['access']['blockVoters'] ) ) {
				if ( '' !== $voter_data['ipaddress'] ) {
					$previous_vote = YOP_Poll_Votes::get_vote( $poll->id, 'ipaddress', $voter_data['ipaddress'] );
				}
			}
			if ( null !== $previous_vote ) {
				if ( 'forever' === $poll->meta_data['options']['access']['blockLengthType'] ) {
					$accept_votes_from_anonymous = false;
					$should_continue = false;
				} else {
					switch ( $poll->meta_data['options']['access']['blockForPeriod'] ) {
						case 'minutes': {
							$block_for_period = 'PT' . $poll->meta_data['options']['access']['blockForValue'] . 'M';
							break;
						}
						case 'hours': {
							$block_for_period = 'PT' . $poll->meta_data['options']['access']['blockForValue'] . 'H';
							break;
						}
						case 'days': {
							$block_for_period = 'P' . $poll->meta_data['options']['access']['blockForValue'] . 'D';
							break;
						}
					}
					$current_vote_date = new DateTime( get_gmt_from_date( current_time( 'mysql' ) ), new DateTimeZone( 'UTC' ) );
					$previous_vote_date = new DateTime( get_gmt_from_date( $previous_vote->added_date ), new DateTimeZone( 'UTC' ) );
					$previous_vote_date->add( new DateInterval( $block_for_period ) );
					if ( $current_vote_date < $previous_vote_date ) {
						$accept_votes_from_anonymous = false;
						$should_continue = false;
					}
				}
			}
		}
		if ( true === $should_continue ) {
			$previous_vote = null;
			if ( true === in_array( 'by-user-id', $poll->meta_data['options']['access']['blockVoters'] ) ) {
				if ( '' !== $voter_data['user-id'] ) {
					$previous_vote = YOP_Poll_Votes::get_vote( $poll->id, 'user_id', $voter_data['user-id'] );
				}
			}
			if ( null !== $previous_vote ) {
				if ( 'forever' === $poll->meta_data['options']['access']['blockLengthType'] ) {
					$accept_votes_from_anonymous = false;
					$should_continue = false;
				} else {
					switch ( $poll->meta_data['options']['access']['blockForPeriod'] ) {
						case 'minutes': {
							$block_for_period = 'PT' . $poll->meta_data['options']['access']['blockForValue'] . 'M';
							break;
						}
						case 'hours': {
							$block_for_period = 'PT' . $poll->meta_data['options']['access']['blockForValue'] . 'H';
							break;
						}
						case 'days': {
							$block_for_period = 'P' . $poll->meta_data['options']['access']['blockForValue'] . 'D';
							break;
						}
					}
					$current_vote_date = new DateTime( get_gmt_from_date( current_time( 'mysql' ) ), new DateTimeZone( 'UTC' ) );
					$previous_vote_date = new DateTime( get_gmt_from_date( $previous_vote->added_date ), new DateTimeZone( 'UTC' ) );
					$previous_vote_date->add( new DateInterval( $block_for_period ) );
					if ( $current_vote_date < $previous_vote_date ) {
						$accept_votes_from_anonymous = false;
					}
				}
			}
		}
		return $accept_votes_from_anonymous;
	}
	public static function add_votes_manually( $poll_id, $elements_data ) {
		$total_votes__to_be_added = 0;
		$elements_votes = [];
		$i = 0;
		$votes = [];
		$result = array();
		if ( $poll_id > 0 ) {
			foreach ( $elements_data as $element_data ) {
				if ( intval( $element_data->id ) > 0 ) {
					$elements_votes[$i] = 0;
					foreach ( $element_data->answers as $answer_data ) {
						if ( intval( $answer_data->id ) > 0 ) {
							$elements_votes[$i] += intval( $answer_data->votes);
						}
					}
					$i++;
				}
			}
			if ( 1 === count( array_unique( $elements_votes ) ) ) {
				$total_votes_to_be_added = $elements_votes[0];
				for ( $i = 0; $i < $total_votes_to_be_added; $i++ ) {
					$votes[$i] = new stdClass();
					$votes[$i]->pollId = $poll_id;
					$votes[$i]->trackingId = '';
					$votes[$i]->data = array();
					$j = 0;
					foreach( $elements_data as $element_data ) {
						if ( intval( $element_data->id ) > 0 ) {
							$votes[$i]->data[$j] = new stdClass();
							$votes[$i]->data[$j]->type = 'question';
							$votes[$i]->data[$j]->id = $element_data->id;
							foreach ( $element_data->answers as $answer_data ) {
								if ( ( intval( $answer_data->id ) > 0 ) && ( intval( $answer_data->votes ) > 0 ) ) {
									$votes[$i]->data[$j]->data = [];
									$votes[$i]->data[$j]->data[0] = new stdClass();
									$votes[$i]->data[$j]->data[0]->id = $answer_data->id;
									$votes[$i]->data[$j]->data[0]->data = 1;
									$answer_data->votes--;
									break;
								}
							}
							$j++;
						}
					}
					$votes[$i]->user = new stdClass();
					$votes[$i]->user->type = 'manually';
					$votes[$i]->user->c_data = '';
					$votes[$i]->user->f_data = '';
					YOP_Poll_Votes::add_manually( $votes[$i] );
				}
				$result['success'] = true;
			} else {
				$result['success'] = false;
				$result['error'] = __( 'Number of votes for each question should be the same', 'yop-poll' );
			}
		} else {
			$result['success'] = false;
			$result['error'] = __( 'Invalid Poll', 'yop-poll' );
		}
		return $result;
	}
	public static function has_other_answers( $poll ) {
		if ( true === isset( $poll->elements ) ) {
			foreach( $poll->elements as $element ) {
				if ( 
					( true === in_array( $element->etype, array( 'text-question', 'media-question' ) ) ) &&
					( 'yes' === $element->meta_data['allowOtherAnswers'] )
					) {
						return true;
				}
			}
		} else {
			return false;
		}
	}
}
