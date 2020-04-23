<?php
$user_type_sort_order = 'asc';
$user_type_sort_order_display = 'desc';
$user_type_column_class = 'sortable';

$username_sort_order = 'asc';
$username_sort_order_display = 'desc';
$username_column_class = 'sortable';

$user_email_sort_order = 'asc';
$user_email_sort_order_display = 'desc';
$user_email_column_class = 'sortable';

$ipaddress_sort_order = 'asc';
$ipaddress_sort_order_display = 'desc';
$ipaddress_column_class = 'sortable';

$added_date_sort_order = 'asc';
$added_date_sort_order_display = 'desc';
$added_date_column_class = 'sortable';

switch( $params['order_by'] ) {
    case 'user_type': {
        $user_type_sort_order =  ( isset( $params['sort_order'] ) && ( 'asc' === $params['sort_order'] ) ) ? 'desc' : 'asc';
        $user_type_sort_order_display = ( isset( $params['sort_order'] ) && ( 'desc' === $params['sort_order'] ) ) ? 'desc' : 'asc';
        $user_type_column_class = 'sorted';
        break;
    }
    case 'username':{
        $username_sort_order = ( isset( $params['sort_order'] ) && ( 'asc' === $params['sort_order'] ) ) ? 'desc' : 'asc';
        $username_sort_order_display = ( isset( $params['sort_order'] ) && ( 'desc' === $params['sort_order'] ) ) ? 'desc' : 'asc';
        $username_column_class = 'sorted';
        break;
    }
    case 'user_email':{
        $user_email_sort_order = ( isset( $params['sort_order'] ) && ( 'asc' === $params['sort_order'] ) ) ? 'desc' : 'asc';
        $user_email_sort_order_display = ( isset( $params['sort_order'] ) && ( 'desc' === $params['sort_order'] ) ) ? 'desc' : 'asc';
        $user_email_column_class = 'sorted';
        break;
    }
    case 'ipaddress': {
        $ipaddress_sort_order =  ( isset( $params['sort_order'] ) && ( 'asc' === $params['sort_order'] ) ) ? 'desc' : 'asc';
        $ipaddress_sort_order_display = ( isset( $params['sort_order'] ) && ( 'desc' === $params['sort_order'] ) ) ? 'desc' : 'asc';
        $ipaddress_column_class = 'sorted';
        break;
    }
    case 'added_date': {
        $added_date_sort_order = ( isset( $params['sort_order'] ) && ( 'asc' === $params['sort_order'] ) ) ? 'desc' : 'asc';
        $added_date_sort_order_display = ( isset( $params['sort_order'] ) && ( 'desc' === $params['sort_order'] ) ) ? 'desc' : 'asc';
        $added_date_column_class = 'sorted';
        break;
    }
    default: {
        $user_type_sort_order = 'asc';
        $user_type_sort_order_display = 'desc';
        $user_type_column_class = 'sortable';

        $username_sort_order = 'asc';
        $username_sort_order_display = 'desc';
        $username_column_class = 'sortable';

        $user_email_sort_order = 'asc';
        $user_email_sort_order_display = 'desc';
        $user_email_column_class = 'sortable';

        $ipaddress_sort_order = 'asc';
        $ipaddress_sort_order_display = 'desc';
        $ipaddress_column_class = 'sortable';

        $added_date_sort_order = 'asc';
        $added_date_sort_order_display = 'desc';
        $added_date_column_class = 'sortable';

        break;
    }
}
$search_value = isset($_GET['q']) ? $_GET['q'] : '';
?>
<div id="yop-main-area" class="bootstrap-yop wrap">
    <input type="hidden" name="_token" value="<?php echo wp_create_nonce( 'yop-poll-get-vote-details' ); ?>">
    <div id="icon-options-general" class="icon32"></div>
    <h1>
        <i class="fa fa-bar-chart" aria-hidden="true"></i><?php _e( 'Poll results for', 'yop-poll' ); ?> <?php echo $poll->name; ?>
        <a href="<?php echo esc_url( add_query_arg(
            array(
                'page' => 'yop-polls',
                'action' => false,
                'poll_id' => false,
                '_token' => false,
                'order_by' => false,
                'sort_order' => false,
                'q' => false,
                'exportCustoms' => false,
            ) ) );?>" class="page-title-action">
            <?php _e( 'All Polls', 'yop-poll' );?>
        </a>
    </h1>
    <div class="container-fluid">
        <div class="row submenu" style="margin-top:30px; margin-bottom: 50px;">
            <div class="col-md-4">
                <a href="<?php echo esc_url( add_query_arg(
                    array(
                        'page' => 'yop-polls',
                        'action' => 'results',
                        'poll_id' => $poll->id,
                        '_token' => false,
                        'order_by' => false,
                        'sort_order' => false,
                        'q' => false,
                        'exportCustoms' => false
                    ) ) ); ?>" class="btn btn-link btn-block">
                    <?php _e( 'Results', 'yop-poll' ); ?>
                </a>
            </div>
            <div class="col-md-4">
                <a class="btn btn-link btn-block btn-underline">
                    <?php _e( 'View votes', 'yop-poll' );?>
                </a>
            </div>
            <div class="col-md-4"></div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form method="get" action="" id="searchForm">
                    <p class="search-box">
                        <label class="screen-reader-text" for="post-search-input">
                            <?php _e( 'Search Logs', 'yop-poll' ); ?>:
                        </label>
                        <input type="hidden" name="page" value="yop-polls">
                        <input type="hidden" name="action" value="view-votes">
                        <input type="hidden" name="poll_id" value="<?php echo $poll->id?>">
                        <input id="votes-search-input" name="q" value="<?php echo esc_html( $search_value ); ?>" type="search">
                        <input id="search-submit" class="button" value="<?php _e( 'Search Votes', 'yop-poll' );?>" type="submit">
                    </p>
                    <button class="export-logs-button button" id="doaction" type="button" name="export"><?php echo __( 'Export', 'yop-poll' ); ?></button>
                    <input type="hidden" name="doExport" id="doExport" value="">
                    <button class="add-votes-button button" type="button" name="add-votes" data-toggle="modal" data-target="#modal-add-votes-manually">
						<?php  _e( 'Add Votes', 'yop-poll' ); ?>
					</button>
					<div id="modal-add-votes-manually" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title">
									<?php _e( 'Add Votes Manually', 'yop-poll' );?>
								</h4>
							</div>
							<div class="modal-body section-main-add-votes-manually">
								<?php
								foreach ( $poll->elements as $poll_element ) {
									switch ( $poll_element->etype ) {
										case 'text-question': {
											?>
											<div class="question-section" data-id="<?php echo $poll_element->id;?>">
												<h4 class="text-center">
													<?php echo $poll_element->etext; ?>
												</h4>
												<?php
												foreach ( $poll_element->answers as $answer ) {
													?>
													<div class="row answer-section">
														<div class="col-md-12">
															<h5>
																<?php
																	echo $answer->stext;
																?>
															</h5>
														</div>
													</div>
													<div class="row">
														<div class="col-md-4">
															<div class="input-group">
																<input type="text" class="form-control answer-element" value="0" data-id="<?php echo $answer->id;?>">
																<span class="help-block">
																	<?php _e( 'Number of votes for this answer', 'yop-poll' );?>
																</span>
															</div>
														</div>
														<div class="col-md-8">&nbsp;</div>
													</div>
													<?php
												}
											?>
											</div>
											<?php
											break;
										}
									}
								}
								?>
							</div>
							<div class="modal-footer section-footer-add-votes-manually">
								<input type="hidden" name="_token-add-votes-manually" value="<?php echo wp_create_nonce( 'yop-poll-add-votes-manually' ); ?>">
								<button type="button" class="btn btn-default btn-cancel-add-votes-manually">
									<?php _e( 'Cancel', 'yop-poll' );?>
								</button>
								<button type="button" class="btn btn-primary btn-submit-add-votes-manually" data-poll-id="<?php echo $poll->id;?>">
									<?php _e( 'Add Votes', 'yop-poll' );?>
								</button>
								<span class="spinner hide"></span>
							</div>
							</div><!-- /.modal-content -->
						</div><!-- /.modal-dialog -->
					</div><!-- /.modal -->
                </form>
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <input type="hidden" name="_bulk_token" value="<?php echo wp_create_nonce( 'yop-poll-bulk-votes' ); ?>">
                        <label for="bulk-action-selector-top" class="screen-reader-text">
                            <?php _e( 'Select bulk action', 'yop-poll' );?>
                        </label>
                        <select name="action" class="logs-bulk-action-top">
                            <option value="-1" class="hide-if-no-js">
                                <?php _e( 'Bulk Actions', 'yop-poll' );?>
                            </option>
                            <option value="trash" class="hide-if-no-js">
                                <?php _e( 'Move to Trash', 'yop-poll' );?>
                            </option>
                        </select>
                        <input class="button votes-bulk-action" data-position="top" value="<?php _e( 'Apply', 'yop-poll' );?>" type="submit">
                    </div>
                    <h2 class="screen-reader-text">
                        <?php _e( 'Votes list navigation', 'yop-poll' );?>
                    </h2>
                    <div class="tablenav-pages">
						<span class="displaying-num">
							<?php echo sprintf( _n( '%s item', '%s items', count( $votes ), 'yop-poll' ), count( $votes ) );?>
						</span>
                        <?php
                        if ( 1 < $total_pages ) {
                            ?>
                            <span class="pagination-links">
							<?php echo $pagination['first_page'];?>
                                <?php echo $pagination['previous_page'];?>
								<span class="paging-input">
								<label for="current-page-selector" class="screen-reader-text">
									<? _e( 'Current Page', 'yop-poll' );?>
								</label>
								<input class="current-page"
                                       id="current-poll-page-selector"
                                       type="text"
                                       name="page_no"
                                       value="<?php echo sanitize_text_field( $params['page_no'] );?>"
                                       size="1"
                                       aria-describedby="table-paging">
								<span class="tablenav-paging-text"> of
									<span class="total-pages">
										<?php echo $total_pages;?>
									</span>
								</span>
							</span>
								<?php echo $pagination['next_page'];?>
                                <?php echo $pagination['last_page'];?>
						</span>
                            <?php
                        }
                        ?>
                    </div>
                    <br class="clear">
                </div>
                <table class="wp-list-table yop-table widefat striped pages ">
                    <thead>
                    <tr>
                        <td id="cb" class="manage-column column-cb check-column">
                            <label class="screen-reader-text" for="cb-select-all-1">
                                <?php _e( 'Select All', 'yop-poll' );?>
                            </label>
                            <input id="cb-select-all-1" type="checkbox">
                        </td>
                        <th scope="col" class="manage-column column-title column-primary <?php echo $user_type_column_class . ' ' . $user_type_sort_order_display;?>">
                            <a href="
								<?php echo esc_url(
                                add_query_arg(
                                    array(
                                        'action' => 'view-votes',
                                        'poll_id' => $poll->id,
                                        '_token' => false,
                                        'order_by' => 'user_type',
                                        'sort_order' => $user_type_sort_order,
                                        'q' => sanitize_text_field( $params['q'] ),
                                        'page_no' => sanitize_text_field( $params['page_no'] )
                                    )
                                )
                            );
                            ?>
								">
									<span>
										<?php _e( 'User Type', 'yop-poll' );?>
									</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" class="manage-column column-title column-primary <?php echo $username_column_class . ' ' . $username_sort_order_display;?>">
                            <a href="
									<?php echo esc_url(
                                add_query_arg(
                                    array(
                                        'action' => 'view-votes',
                                        'poll_id' => $poll->id,
                                        '_token' => false,
                                        'order_by' => 'username',
                                        'sort_order' => $username_sort_order,
                                        'q' => sanitize_text_field( $params['q'] ),
                                        'page_no' => sanitize_text_field( $params['page_no'] )
                                    )
                                )
                            );
                            ?>
									">
									<span>
										<?php _e( 'Username', 'yop-poll' );?>
									</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" class="manage-column column-title column-primary <?php echo $user_email_column_class . ' ' . $user_email_sort_order_display;?>">
                            <a href="
								<?php echo esc_url(
                                add_query_arg(
                                    array(
                                        'action' => 'view-votes',
                                        'poll_id' => $poll->id,
                                        '_token' => false,
                                        'order_by' => 'user_email',
                                        'sort_order' => $user_email_sort_order,
                                        'q' => sanitize_text_field( $params['q'] ),
                                        'page_no' => sanitize_text_field( $params['page_no'] )
                                    )
                                )
                            );
                            ?>
								">
									<span>
										<?php _e( 'Email', 'yop-poll' );?>
									</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" class="manage-column column-title column-primary <?php echo $ipaddress_column_class . ' ' . $ipaddress_sort_order_display;?>">
                            <a href="
								<?php echo esc_url(
                                add_query_arg(
                                    array(
                                        'action' => 'view-votes',
                                        'poll_id' => $poll->id,
                                        '_token' => false,
                                        'order_by' => 'ipaddress',
                                        'sort_order' => $ipaddress_sort_order,
                                        'q' => sanitize_text_field( $params['q'] ),
                                        'page_no' => sanitize_text_field( $params['page_no'] )
                                    )
                                )
                            );
                            ?>
								">
									<span>
										<?php _e( 'Ipaddress', 'yop-poll' );?>
									</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" class="manage-column column-title column-primary <?php echo $added_date_column_class . ' ' . $added_date_sort_order_display;?>">
                            <a href="
								<?php echo esc_url(
                                add_query_arg(
                                    array(
                                        'action' => 'view-votes',
                                        'poll_id' => $poll->id,
                                        '_token' => false,
                                        'order_by' => 'added_date',
                                        'sort_order' => $added_date_sort_order,
                                        'q' => sanitize_text_field( $params['q'] ),
                                        'page_no' => sanitize_text_field( $params['page_no'] )
                                    )
                                )
                            );
                            ?>
								">
									<span>
										<?php _e( 'Date', 'yop-poll' );?>
									</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                    </tr>
                    </thead>
                    <?php
                    foreach ( $votes as $vote ) {
                        ?>
                        <tr class="iedit author-self level-0 post-17 type-post status-publish format-standard hentry category-uncategorized">
                            <th scope="row" class="check-column">
                                <label class="screen-reader-text" for="cb-select-17">
                                    <?php _e( 'Select', 'yop-poll' );?>
                                </label>
                                <input name="votes[]" value="<?php echo esc_html( $vote['id'] );?>" type="checkbox">
                                <div class="locked-indicator"></div>
                            </th>
                            <td class="title column-title has-row-actions column-primary page-title" data-colname="User Type">
                                <?php echo esc_html( $vote['user_type'] );?>
                                <div class="row-actions">
									<span class="view">
										<a href="#" class="details-operation" data-vote-id="<?php echo $vote['id']; ?>" data-ajax-sent="no" title="<?php _e( 'View details for this record', 'yop-poll' );?>">
											<?php _e( 'View Details', 'yop-poll' );?> |
										</a>
									</span>
                                    <span class="trash">
										<a class="delete-vote" title="<?php _e( 'Move this log record to the Trash', 'yop-poll' );?>"
                                           href="#" data-id="<?php echo esc_html( $vote['id'] );?>"><?php _e( 'Trash', 'yop-poll' );?></a>
									</span>
                                </div>
                                <div id="vote-details-div-<?php echo $vote['id']; ?>" style="display: none; padding-top: 10px;">
                                </div>
                            </td>
                            <td class="title column-title has-row-actions column-primary page-title" data-colname="Username">
                                <?php
                                echo esc_html( $vote['username'] );
                                ?>
                            </td>
                            <td class="title column-title has-row-actions column-primary page-title" data-colname="Email">
                                <?php echo esc_html( $vote['user_email'] );?>
                            </td>
                            <td class="title column-title has-row-actions column-primary page-title" data-colname="Ipaddress">
                                <?php echo esc_html( $vote['ipaddress'] );?>
                            </td>
                            <td class="title column-title has-row-actions column-primary page-title" data-colname="Added Date">
                                <?php echo esc_html( date( $date_format . ' @ ' . $time_format, strtotime( $vote['added_date'] ) ) );?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    <thead>
                    <tr>
                        <td id="cb" class="manage-column column-cb check-column">
                            <label class="screen-reader-text" for="cb-select-all-1">
                                <?php _e( 'Select All', 'yop-poll' );?>
                            </label>
                            <input id="cb-select-all-1" type="checkbox">
                        </td>
                        <th scope="col" class="manage-column column-title column-primary <?php echo $user_type_column_class . ' ' . $user_type_sort_order_display;?>">
                            <a href="
								<?php echo esc_url(
                                add_query_arg(
                                    array(
                                        'action' => 'view-votes',
                                        'poll_id' => $poll->id,
                                        '_token' => false,
                                        'order_by' => 'user_type',
                                        'sort_order' => $user_type_sort_order,
                                        'q' => sanitize_text_field( $params['q'] ),
                                        'page_no' => sanitize_text_field( $params['page_no'] )
                                    )
                                )
                            );
                            ?>
								">
									<span>
										<?php _e( 'User Type', 'yop-poll' );?>
									</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" class="manage-column column-title column-primary <?php echo $username_column_class . ' ' . $username_sort_order_display;?>">
                            <a href="
									<?php echo esc_url(
                                add_query_arg(
                                    array(
                                        'action' => 'view-votes',
                                        'poll_id' => $poll->id,
                                        '_token' => false,
                                        'order_by' => 'username',
                                        'sort_order' => $username_sort_order,
                                        'q' => sanitize_text_field( $params['q'] ),
                                        'page_no' => sanitize_text_field( $params['page_no'] )
                                    )
                                )
                            );
                            ?>
									">
									<span>
										<?php _e( 'Username', 'yop-poll' );?>
									</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" class="manage-column column-title column-primary <?php echo $user_email_column_class . ' ' . $user_email_sort_order_display;?>">
                            <a href="
								<?php echo esc_url(
                                add_query_arg(
                                    array(
                                        'action' => 'view-votes',
                                        'poll_id' => $poll->id,
                                        '_token' => false,
                                        'order_by' => 'user_email',
                                        'sort_order' => $user_email_sort_order,
                                        'q' => sanitize_text_field( $params['q'] ),
                                        'page_no' => sanitize_text_field( $params['page_no'] )
                                    )
                                )
                            );
                            ?>
								">
									<span>
										<?php _e( 'Email', 'yop-poll' );?>
									</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" class="manage-column column-title column-primary <?php echo $ipaddress_column_class . ' ' . $ipaddress_sort_order_display;?>">
                            <a href="
								<?php echo esc_url(
                                add_query_arg(
                                    array(
                                        'action' => 'view-votes',
                                        'poll_id' => $poll->id,
                                        '_token' => false,
                                        'order_by' => 'ipaddress',
                                        'sort_order' => $ipaddress_sort_order,
                                        'q' => sanitize_text_field( $params['q'] ),
                                        'page_no' => sanitize_text_field( $params['page_no'] )
                                    )
                                )
                            );
                            ?>
								">
									<span>
										<?php _e( 'Ipaddress', 'yop-poll' );?>
									</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" class="manage-column column-title column-primary <?php echo $added_date_column_class . ' ' . $added_date_sort_order_display;?>">
                            <a href="
								<?php echo esc_url(
                                add_query_arg(
                                    array(
                                        'action' => 'view-votes',
                                        'poll_id' => $poll->id,
                                        '_token' => false,
                                        'order_by' => 'added_date',
                                        'sort_order' => $added_date_sort_order,
                                        'q' => sanitize_text_field( $params['q'] ),
                                        'page_no' => sanitize_text_field( $params['page_no'] )
                                    )
                                )
                            );
                            ?>
								">
									<span>
										<?php _e( 'Date', 'yop-poll' );?>
									</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                    </tr>
                    </thead>
                </table>
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <label for="bulk-action-selector-bottom" class="screen-reader-text">
                            <?php _e( 'Select bulk action', 'yop-poll' );?>
                        </label>
                        <select name="action" class="logs-bulk-action-bottom">
                            <option value="-1" class="hide-if-no-js">
                                <?php _e( 'Bulk Actions', 'yop-poll' );?>
                            </option>
                            <option value="trash" class="hide-if-no-js">
                                <?php _e( 'Move to Trash', 'yop-poll' );?>
                            </option>
                        </select>
                        <input class="button votes-bulk-action" data-position="bottom" value="<?php _e( 'Apply', 'yop-poll' );?>" type="submit">
                    </div>
                    <h2 class="screen-reader-text">
                        <?php _e( 'Pages list navigation', 'yop-poll' );?>
                    </h2>
                    <div class="tablenav-pages">
						<span class="displaying-num">
							<?php echo sprintf( _n( '%s item', '%s items', count( $votes ), 'yop-poll' ), count( $votes ) );?>
						</span>
                        <?php
                        if ( 1 < $total_pages ) {
                            ?>
                            <span class="pagination-links">
							<?php echo $pagination['first_page'];?>
                                <?php echo $pagination['previous_page'];?>
								<span class="paging-input">
								<label for="current-page-selector" class="screen-reader-text">
									<? _e( 'Current Page', 'yop-poll' );?>
								</label>
								<span class="tablenav-paging-text">
									<?php echo sanitize_text_field( $params['page_no'] );?> of
									<span class="total-pages">
										<?php echo $total_pages;?>
									</span>
								</span>
							</span>
								<?php echo $pagination['next_page'];?>
                                <?php echo $pagination['last_page'];?>
						</span>
                            <?php
                        }
                        ?>
                    </div>
                    <br class="clear">
                </div>
            </div>
        </div>
    </div>
</div>
