<?php
class YOP_Poll_Admin {
	private $templates;
	private static $date_format, $time_format, $old_version = null;
	public function __construct() {
		self::$date_format = get_option( 'date_format' );
		self::$time_format = get_option( 'time_format' );
		self::$old_version = get_option( 'yop_poll_old_version' );
		if ( true === is_admin() ) {
            add_filter( 'admin_title', array( &$this, 'change_page_title' ) );
			add_filter( 'clean_url', array( &$this, 'clean_recaptcha_url' ) );
            add_action( 'admin_menu', array( &$this, 'build_admin_menu' ) );
            add_action( 'plugins_loaded', array( &$this, 'verify_update' ) );
			add_action( 'plugins_loaded', array( $this, 'load_translations') );
            add_action( 'admin_enqueue_scripts', array( &$this, 'load_dependencies' ), 1000 );
            add_action( 'wp_ajax_create_yop_poll', array( &$this, 'create_poll' ) );
            add_action( 'wp_ajax_update_yop_poll', array( &$this, 'update_poll' ) );
            add_action( 'wp_ajax_delete_single_yop_poll', array( &$this, 'delete_single_poll' ) );
            add_action( 'wp_ajax_delete_bulk_yop_poll', array( &$this, 'delete_bulk_poll' ) );
            add_action( 'wp_ajax_clone_single_yop_poll', array( &$this, 'clone_single_poll' ) );
            add_action( 'wp_ajax_clone_bulk_yop_poll', array( &$this, 'clone_bulk_poll' ) );
            add_action( 'wp_ajax_reset_bulk_yop_poll', array( &$this, 'reset_bulk_poll' ) );
            add_action( 'wp_ajax_create_yop_poll_ban', array( &$this, 'create_ban' ) );
            add_action( 'wp_ajax_delete_yop_poll_ban', array( &$this, 'delete_single_ban' ) );
            add_action( 'wp_ajax_update_yop_poll_ban', array( &$this, 'update_ban' ) );
            add_action( 'wp_ajax_delete_bulk_yop_poll_ban', array( &$this, 'delete_bulk_ban' ) );
            add_action( 'wp_ajax_delete_yop_poll_log', array( &$this, 'delete_single_log' ) );
            add_action( 'wp_ajax_get_yop_poll_log_details', array( &$this, 'get_log_details' ) );
            add_action( 'wp_ajax_delete_bulk_yop_poll_log', array( &$this, 'delete_bulk_log' ) );
            add_action( 'wp_ajax_yop_poll_is_user_logged_in', array( &$this, 'is_user_logged_in' ) );
            add_action( 'wp_ajax_yop_poll_record_vote', array( &$this, 'record_vote' ) );
			add_action( 'wp_ajax_yop_poll_record_wordpress_vote', array( &$this, 'record_wordpress_vote' ) );
			add_action( 'wp_ajax_yop_poll_get_poll_for_frontend', array( &$this, 'create_poll_for_frontend' ) );
            add_action( 'wp_ajax_get_yop_poll_votes_customs', array( &$this, 'get_yop_poll_votes_customs' ) );
            add_action( 'wp_ajax_yop-poll-get-vote-details', array( &$this, 'get_vote_details' ) );
            add_action( 'wp_ajax_yop_poll_delete_vote', array( &$this, 'delete_single_vote' ) );
            add_action( 'wp_ajax_yop_poll_delete_votes_bulk', array( &$this, 'delete_bulk_votes' ) );
			add_action( 'wp_ajax_yop_poll_save_settings', array( &$this, 'save_settings' ) );
			add_action( 'wp_ajax_yop_poll-add-votes-manually', array( &$this, 'add_votes_manually' ) );
			add_action( 'wp_ajax_yop_poll_stop_showing_guide', array( &$this, 'stop_showing_guide' ) );
			add_action( 'wp_ajax_yop_poll_send_guide', array( &$this, 'send_guide' ) );
			if ( self::$old_version ) {
				if ( false !== strpos( self::$old_version, '4.' ) ) {
					add_action( 'wp_ajax_yop_ajax_migrate', array( 'ClassYopPollImporter4x', 'yop_ajax_import' ) );
				} elseif ( false !== strpos( self::$old_version, '5.' ) ) {
					add_action( 'wp_ajax_yop_ajax_migrate', array( 'ClassYopPollImporter5x', 'yop_ajax_import' ) );
				}
			}
			add_action( 'wp_ajax_nopriv_yop_poll_is_user_logged_in', array( &$this, 'is_user_logged_in' ) );
			add_action( 'wp_ajax_nopriv_yop_poll_record_vote', array( &$this, 'record_vote' ) );
			add_action( 'wp_ajax_nopriv_yop_poll_record_wordpress_vote', array( &$this, 'record_wordpress_vote' ) );
			add_action( 'wp_ajax_nopriv_yop_poll_get_poll_for_frontend', array( &$this, 'create_poll_for_frontend' ) );
		}
		Yop_Poll_DbSchema::initialize_tables_names();
	}
	public function set_admin_footer() {
		return 'Please rate YOP Poll <a href="https://wordpress.org/support/plugin/yop-poll/reviews/?filter=5#new-post" target="_blank">★★★★★</a> on <a href="https://wordpress.org/support/plugin/yop-poll/reviews/?filter=5#new-post" target="_blank">WordPress.org</a> to help us spread the word. Thank you from the YOP team!';
	}
	public function clean_recaptcha_url( $url ) {
		if ( false !== strstr( $url, "recaptcha/api.js" ) ) {
			$url = str_replace( "&#038;", "&", $url );
		}
		return $url;
	}
	public function verify_update() {
        $installed_version = get_option( 'yop_poll_version' );
        if ( $installed_version ) {
            if ( true === version_compare( $installed_version, '6.0.0', '<' ) ) {
                $maintenance = new YOP_POLL_Maintenance();
                $maintenance->activate( false );
            }
            if ( true === version_compare( $installed_version, '6.0.4', '<' ) ) {
                $maintenance  = new YOP_POLL_Maintenance();
                $maintenance->update_to_version_6_0_4();
			}
			if ( true === version_compare( $installed_version, '6.0.5', '<' ) ) {
				$maintenance  = new YOP_POLL_Maintenance();
				$maintenance->update_to_version_6_0_5();
			}
			if ( true === version_compare( $installed_version, '6.0.6', '<' ) ) {
				$maintenance  = new YOP_POLL_Maintenance();
				$maintenance->update_to_version_6_0_6();
			}
			if ( true === version_compare( $installed_version, '6.0.7', '<' ) ) {
				$maintenance  = new YOP_POLL_Maintenance();
				$maintenance->update_to_version_6_0_7();
			}
			if ( true === version_compare( $installed_version, '6.0.8', '<' ) ) {
				$maintenance  = new YOP_POLL_Maintenance();
				$maintenance->update_to_version_6_0_8();
			}
			if ( true === version_compare( $installed_version, '6.0.9', '<' ) ) {
				$maintenance  = new YOP_POLL_Maintenance();
				$maintenance->update_to_version_6_0_9();
			}
			if ( true === version_compare( $installed_version, '6.1.0', '<' ) ) {
				$maintenance  = new YOP_POLL_Maintenance();
				$maintenance->update_to_version_6_1_0();
			}
			if ( true === version_compare( $installed_version, '6.1.1', '<' ) ) {
				$maintenance  = new YOP_POLL_Maintenance();
				$maintenance->update_to_version_6_1_1();
			}
			if ( true === version_compare( $installed_version, '6.1.2', '<' ) ) {
				$maintenance  = new YOP_POLL_Maintenance();
				$maintenance->update_to_version_6_1_2();
			}
			if ( true === version_compare( $installed_version, '6.1.4', '<' ) ) {
				$maintenance  = new YOP_POLL_Maintenance();
				$maintenance->update_to_version_6_1_4();
			}
        }
	}
    public function load_translations() {
        load_plugin_textdomain( 'yop-poll', FALSE, YOP_POLL_PATH . '/languages/' );
    }
	public function is_user_logged_in() {
		if ( true === is_user_logged_in() ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
		die();
	}
	public function build_admin_menu() {
		if( function_exists( 'add_menu_page' ) ) {
			$page = add_menu_page(
				__( 'Yop Poll', 'yop-poll' ),
				__( 'Yop Poll', 'yop-poll' ),
				'yop_poll_results_own',
				'yop-polls',
				array(
					$this,
					'manage_polls'
				),
				YOP_POLL_URL . "admin/assets/images/yop-poll-admin-menu-icon16.png",
				'26.6'
			);
			if ( function_exists( 'add_submenu_page' ) ) {
				$subpage = add_submenu_page(
					'yop-polls',
					__( 'All Polls', 'yop-poll' ),
					__( 'All Polls', 'yop-poll' ),
					'yop_poll_results_own',
					'yop-polls',
					array(
						$this,
						'manage_polls'
					)
				);
                if ( $subpage ) {
                    $votesObj = YOP_Poll_Votes::get_instance();
                    add_action( 'load-' . $subpage, array( $votesObj, 'send_votes_to_download' ) );
                }
				$subpage = add_submenu_page(
					'yop-polls',
					__( 'Add New', 'yop-poll' ),
					__( 'Add New', 'yop-poll' ),
					'yop_poll_add',
					'yop-poll-add-poll',
					array(
						$this,
						'add_new_poll'
					)
				);
				$subpage = add_submenu_page(
					'yop-polls',
					__( 'Bans', 'yop-poll' ),
					__( 'Bans', 'yop-poll' ),
					'yop_poll_results_own',
					'yop-poll-bans',
					array(
						$this,
						'manage_bans'
					)
				);
				$subpage_logs = add_submenu_page(
					'yop-polls',
					__( 'Logs', 'yop-poll' ),
					__( 'Logs', 'yop-poll' ),
					'yop_poll_results_own',
					'yop-poll-logs',
					array(
						$this,
						'manage_logs'
					)
				);
                if ( $subpage_logs ) {
                    $logsObj = YOP_Poll_Logs::get_instance();
                    add_action( 'load-' . $subpage_logs, array( $logsObj, 'send_logs_to_download' ) );
                }
                $subpage = add_submenu_page(
                    'yop-polls',
                    __( 'Settings', 'yop-poll' ),
                    __( 'Settings', 'yop-poll' ),
                    'yop_poll_results_own',
                    'yop-poll-settings',
                    array(
                        $this,
                        'manage_settings'
                    )
                );
				if ( self::$old_version ) {
					$subpage = add_submenu_page(
						'yop-polls',
						__( 'Migrate old records', 'yop-poll' ),
						__( 'Migrate old records', 'yop-poll' ),
						'yop_poll_results_own',
						'yop-poll-migrate',
						array(
							$this,
							'migrate_old_tables'
						)
					);
				}
				$subpage = add_submenu_page(
                    'yop-polls',
                    __( 'Upgrade to Pro', 'yop-poll' ),
                    __( 'Upgrade to Pro', 'yop-poll' ),
                    'yop_poll_results_own',
                    'yop-poll-upgrade-to-pro',
                    array(
                        $this,
                        'show_upgrade_to_pro'
                    )
                );
			}
		}
	}
	public function load_dependencies() {
	    $yop_poll_pages = [
	        'yop-polls',
            'yop-poll-add-poll',
            'yop-poll-bans',
            'yop-poll-logs',
            'yop-poll-settings',
			'yop-poll-migrate',
			'yop-poll-upgrade-to-pro'
        ];
	    if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], $yop_poll_pages ) ) {
            $this->load_styles();
			$this->load_scripts();
			add_filter( 'admin_footer_text', array( $this, 'set_admin_footer' ) );
        }
	}
	public function load_scripts() {
        $plugin_settings = YOP_Poll_Settings::get_all_settings();
        if (false !== $plugin_settings) {
            $plugin_settings_decoded = unserialize($plugin_settings);
        }
        //include jquery by default
        wp_enqueue_script('jquery');
        wp_enqueue_script('tiny_mce');
        wp_enqueue_script('jquery-ui-core', array('jquery'));
        wp_enqueue_script('jquery-ui-datepicker', array('jquery'));
        wp_enqueue_script('jquery-ui-sortable', array('jquery-ui-core'));
        wp_enqueue_script('jquery-ui-draggable', array('jquery-ui-core'));
		wp_enqueue_script('jquery-ui-droppable', array('jquery-ui-core'));
		if ( TRUE === YOP_POLL_TEST_MODE ) {
			$plugin_admin_js_file = 'admin-' . YOP_POLL_VERSION . '.js';

		} else {
			$plugin_admin_js_file = 'admin-' . YOP_POLL_VERSION . '.min.js';
		}
		wp_enqueue_script( 'yop', YOP_POLL_URL . 'admin/assets/js/' . $plugin_admin_js_file , array( 'jquery',
			'jquery-ui-sortable',
			'jquery-ui-dialog',
			'jquery-ui-datepicker' )
		);
        /* add reCaptcha if enabled */
        if (
            (true === isset($plugin_settings_decoded['integrations']['reCaptcha']['enabled'])) &&
            ('yes' === $plugin_settings_decoded['integrations']['reCaptcha']['enabled']) &&
            (true === isset($plugin_settings_decoded['integrations']['reCaptcha']['site-key'])) &&
            ('' !== $plugin_settings_decoded['integrations']['reCaptcha']['site-key']) &&
            (true === isset($plugin_settings_decoded['integrations']['reCaptcha']['secret-key'])) &&
            ('' !== $plugin_settings_decoded['integrations']['reCaptcha']['secret-key'])
        ) {
            $args = array(
                'render' => 'explicit',
                'onload' => 'YOPPollOnLoadRecaptcha'
            );
            wp_register_script('yop-reCaptcha', add_query_arg($args, 'https://www.google.com/recaptcha/api.js'), '', null);
            wp_enqueue_script('yop-reCaptcha');
        }
        /* done adding reCaptcha */
        if (true === isset( $plugin_settings_decoded['messages']['captcha']['accessibility-description'] ) ) {
            $captcha_accessibility_description = str_replace('[STRONG]', '<strong>', esc_html( $plugin_settings_decoded['messages']['captcha']['accessibility-description'] ) );
            $captcha_accessibility_description = str_replace('[/STRONG]', '</strong>', $captcha_accessibility_description );
        } else {
            $captcha_accessibility_description = '';
        }
        if ( true === isset( $plugin_settings_decoded['messages']['captcha']['explanation'] ) ) {
            $captcha_explanation = str_replace('[STRONG]', '<strong>', esc_html( $plugin_settings_decoded['messages']['captcha']['explanation'] ) );
            $captcha_explanation = str_replace('[/STRONG]', '</strong>', $captcha_explanation );
        } else {
            $captcha_explanation = '';
        }
		wp_localize_script( 'yop', 'objectL10n', array(
			'yopPollParams' => array(
                'appUrl' => YOP_POLL_URL,
                'dateFormat' => self::$date_format,
                'timeFormat' => self::$time_format,
                'timeNow' => time(),
                'votingEnded' => isset( $plugin_settings_decoded['messages']['voting']['poll-ended'] ) ? esc_html__( $plugin_settings_decoded['messages']['voting']['poll-ended'] ) : '',
                'votingNotStarted' => isset( $plugin_settings_decoded['messages']['voting']['poll-not-started'] ) ? esc_html__( $plugin_settings_decoded['messages']['voting']['poll-not-started'] ) : '',
                'newCustomFieldText' => esc_html__( 'New Custom Field', 'yop-poll' ),
                'deleteTitle'  => esc_html__( 'Warning', 'yop-poll' ),
                'deletePollMessage' => esc_html__( 'Are you sure you want to delete this poll?', 'yop-poll' ),
                'deleteBulkPollsSingleMessage' => esc_html__( 'Are you sure you want to delete this poll?', 'yop-poll' ),
                'deleteBulkPollsMultiMessage' => esc_html__( 'Are you sure you want to delete these polls?', 'yop-poll' ),
                'clonePollMessage' => esc_html__( 'Are you sure you want to clone this poll?', 'yop-poll' ),
                'cloneBulkPollsSingleMessage' => esc_html__( 'Are you sure you want to clone this poll?', 'yop-poll' ),
                'cloneBulkPollsMultiMessage' => esc_html__( 'Are you sure you want to clone these polls?', 'yop-poll' ),
                'resetPollMessage' => esc_html__( 'Are you sure you want to reset votes for this poll?', 'yop-poll' ),
                'resetBulkPollsSingleMessage' => esc_html__( 'Are you sure you want to reset votes for this poll?', 'yop-poll' ),
                'resetBulkPollsMultiMessage' => esc_html__( 'Are you sure you want to reset votes for these polls?', 'yop-poll' ),
                'noBulkActionSelected' => esc_html__( 'No bulk action selected', 'yop-poll' ),
                'noPollsSelectedForBulk' => esc_html__( 'No polls selected', 'yop-poll' ),
                'noBansSelectedForBulk' => esc_html__( 'No bans selected', 'yop-poll' ),
                'noLogsSelectedForBulk' => esc_html__( 'No logs selected', 'yop-poll' ),
                'noVotesSelectedForBulk' => esc_html__( 'No votes selected', 'yop-poll' ),
                'deleteBulkBansSingleMessage' => esc_html__( 'Are you sure you want to delete this ban?', 'yop-poll' ),
                'deleteBulkBansMultiMessage' => esc_html__( 'Are you sure you want to delete these bans?', 'yop-poll' ),
                'deleteBulkLogsSingleMessage' => esc_html__( 'Are you sure you want to delete this log?', 'yop-poll' ),
                'deleteBulkLogsMultiMessage' => esc_html__( 'Are you sure you want to delete these logs?', 'yop-poll' ),
                'deleteBulkVotesSingleMessage' => esc_html__( 'Are you sure you want to delete this vote?', 'yop-poll' ),
                'deleteBulkVotessMultiMessage' => esc_html__( 'Are you sure you want to delete these votes?', 'yop-poll' ),
                'deleteAnswerMessage' => esc_html__( 'Are you sure you want to delete this answer?', 'yop-poll' ),
                'deleteAnswerNotAllowedMessage' => esc_html__( 'Answer can\'t be deleted. At least one answer is required!', 'yop-poll' ),
                'deleteCustomFieldMessage' => esc_html__( 'Are you sure you want to delete this custom field?', 'yop-poll' ),
                'deleteCancelLabel' => esc_html__( 'Cancel', 'yop-poll' ),
                'deleteOkLabel' => esc_html__( 'Ok', 'yop-poll' ),
				'noTemplateSelectedLabel' => esc_html__( 'Before generating the preview a template is required', 'yop-poll' ),
				'noSkinSelectedLabel' => esc_html__( 'Before generating the preview a skin is required', 'yop-poll' ),
                'noNumberOfColumnsDefined' => esc_html__( 'Number of columns is missing', 'yop-poll' ),
                'numberOfColumnsTooBig' => esc_html__( 'Too many columns. Max 12 allowed', 'yop-poll' ),
                'selectHelperText' => esc_html__( 'Click to select', 'yop-poll' ),
                'publishDateImmediately' => esc_html__( 'Publish immediately', 'yop-poll' ),
                'publishDateSchedule' => esc_html__( 'Schedule for', 'yop-poll' ),
                'copyToClipboardSuccess' => esc_html__( 'Code Copied To Clipboard', 'yop-poll' ),
                'copyToClipboardError' => array(
                    'press' => esc_html__( 'Press', 'yop-poll' ),
                    'copy' => esc_html__( ' to copy', 'yop-poll' ),
                    'noSupport' => esc_html__( 'No Support', 'yop-poll' )
				),
				'elementAdded' => esc_html__( 'Element added', 'yop-poll' ),
                'captchaParams' => array(
                    'imgPath' => YOP_POLL_URL . 'public/assets/img/',
                    'url' => YOP_POLL_URL . 'app.php',
                    'accessibilityAlt' => isset( $plugin_settings_decoded['messages']['captcha']['accessibility-alt'] ) ? esc_html( $plugin_settings_decoded['messages']['captcha']['accessibility-alt'] ) : '',
                    'accessibilityTitle' => isset( $plugin_settings_decoded['messages']['captcha']['accessibility-alt'] ) ? esc_html( $plugin_settings_decoded['messages']['captcha']['accessibility-title'] ) : '',
                    'accessibilityDescription' => $captcha_accessibility_description,
                    'explanation' => $captcha_explanation,
                    'refreshAlt' => isset( $plugin_settings_decoded['messages']['captcha']['refresh-alt'] ) ? esc_html( $plugin_settings_decoded['messages']['captcha']['refresh-alt'] ) : '',
                    'refreshTitle' => isset( $plugin_settings_decoded['messages']['captcha']['refresh-title'] ) ? esc_html( $plugin_settings_decoded['messages']['captcha']['refresh-title'] ) : ''
                ),
                'previewParams' => array(
                    'pollPreviewTitle' => esc_html__( 'Poll Preview', 'yop-poll' ),
                    'choosePreviewText' => esc_html__( 'Show preview for', 'yop-poll' ),
                    'votingText' => esc_html__( 'Voting', 'yop-poll' ),
                    'resultsText' => esc_html__( 'Results', 'yop-poll' ),
                    'numberOfVotesSingular' => isset( $plugin_settings_decoded['messages']['results']['single-vote'] ) ? esc_html( $plugin_settings_decoded['messages']['results']['single-vote'] ) : '',
                    'numberOfVotesPlural' => isset( $plugin_settings_decoded['messages']['results']['multiple-votes'] ) ? esc_html__( $plugin_settings_decoded['messages']['results']['multiple-votes'] ) : '',
                    'numberOfAnswerSingular' => isset( $plugin_settings_decoded['messages']['results']['single-answer'] ) ? esc_html__( $plugin_settings_decoded['messages']['results']['single-answer'] ) : '',
                    'numberOfAnswersPlural' => isset( $plugin_settings_decoded['messages']['results']['multiple-answers'] ) ? esc_html__( $plugin_settings_decoded['messages']['results']['multiple-answers'] ) : '',
                    'annonymousVoteText' => isset( $plugin_settings_decoded['messages']['buttons']['anonymous'] ) ? esc_html__( $plugin_settings_decoded['messages']['buttons']['anonymous'] ) : '',
                    'wordpressVoteText' => isset( $plugin_settings_decoded['messages']['buttons']['wordpress'] ) ? esc_html__( $plugin_settings_decoded['messages']['buttons']['wordpress'] ) : '',
                    'facebookVoteText' => isset( $plugin_settings_decoded['messages']['buttons']['facebook'] ) ? esc_html__( $plugin_settings_decoded['messages']['buttons']['facebook'] ) : '',
                    'googleVoteText' => isset( $plugin_settings_decoded['messages']['buttons']['google'] ) ? esc_html__( $plugin_settings_decoded['messages']['buttons']['google'] ) :''
                ),
                'saveParams' => array(
					'noTemplateSelected' => esc_html__( 'Template is missing', 'yop-poll' ),
					'noSkinSelected' => esc_html__( 'Skin is missing', 'yop-poll' ),
                    'generalErrorMessage' => esc_html__( ' is missing', 'yop-poll' ),
                    'noPollName' => esc_html__( 'Poll name is missing', 'yop-poll' ),
                    'noQuestion' => esc_html__( 'Question Text is missing', 'yop-poll' ),
                    'noAnswerText' => esc_html__( 'Answer Text is missing', 'yop-poll' ),
                    'noAnswerLink' => esc_html__( 'Answer Link is missing', 'yop-poll' ),
                    'noAnswerEmbed' => esc_html__( 'Answer Embed is missing', 'yop-poll' ),
                    'noOtherLabel' => esc_html__( 'Label for Other is missing', 'yop-poll' ),
                    'noMinAnswers' => esc_html__( 'Minimum answers is missing', 'yop-poll' ),
                    'noMaxAnswers' => esc_html__( 'Maximum answers is missing', 'yop-poll' ),
                    'noCustomFieldName' => esc_html__( 'Custom Field Name is missing', 'yop-poll' ),
                    'noStartDate' => esc_html__( 'Poll Start Date is missing', 'yop-poll' ),
                    'noEndDate' => esc_html__( 'Poll End Date is missing', 'yop-poll' ),
                    'noCustomDate' => esc_html__( 'Custom Date for displaying results is missing', 'yop-poll' ),
                    'noShowResultsMoment' => esc_html__( 'Show Results Time is missing', 'yop-poll' ),
                    'noShowResultsTo' => esc_html__( 'Show Results To is missing', 'yop-poll' ),
                    'noVoteAsWordpress' => esc_html__( 'Vote As Wordpress User is missing', 'yop-poll' )
                ),
                'saveBanParams' => array(
                    'noBanFor' => esc_html__( 'Ban For is missing', 'yop-poll' ),
                    'noBanValue' => esc_html__( 'Ban Value is missing', 'yop-poll' )
                ),
                'deleteBanMessage' => esc_html__( 'Are you sure you want to delete this ban?', 'yop-poll' ),
                'deleteLogMessage' => esc_html__( 'Are you sure you want to delete this log?', 'yop-poll' ),
                'viewLogDetailsQuestionText' => esc_html__( 'Question', 'yop-poll' ),
                'viewLogDetailsAnswerText' => esc_html__( 'Answer', 'yop-poll' ),
                'showLogDetailsLinkText' => esc_html__( 'View Details', 'yop-poll' ),
                'hideLogDetailsLinkText' => esc_html__( 'Hide Details', 'yop-poll' ),
                'numberOfVotesText'      => esc_html__( 'Number of Votes', 'yop-poll' ),
                'resultsParams'=> array(
                    'singleVote' => esc_html__( 'vote', 'yop-poll' ),
                    'multipleVotes' => esc_html__( 'votes', 'yop-poll' )
                ),
                'importOld' => array(
                    'gdprEnabledContinue' => esc_html__( 'Got It. Continue with the migration', 'yop-poll' ),
                    'gdprEnabledStop' => esc_html__( 'Hold On. I want to change settings', 'yop-poll' ),
                    'gdprEnabledGeneral' => esc_html__( 'Please review your settings before continue', 'yop-poll' ),
                    'gdprEnabledChoice' => esc_html__( 'Your selection', 'yop-poll' ),
                    'gdprEnabledMigrateAsIs' => esc_html__( 'This setting will migrate all data from previous version without any anonymization', 'yop-poll' ),
                    'gdprEnabledAnonymizeIp' => esc_html__( 'This setting will migrate all data from previous version but ips will be anonymized', 'yop-poll' ),
                    'gdprEnabledNoStore' => esc_html__( 'This setting will migrate everything except ip addresses. ', 'yop-poll' ),
                    'response' => esc_html__( 'Response:', 'yop-poll' ),
                    'allDone' => esc_html__( 'All done.', 'yop-poll' ),
                    'importStarted' => esc_html__( 'Migration started', 'yop-poll' ),
				)
			)
		) );
	}
	public function load_styles() {
		wp_enqueue_style( 'yop-admin', YOP_POLL_URL . 'admin/assets/css/admin-' . YOP_POLL_VERSION . '.css' );
		wp_enqueue_style( 'yop-public', YOP_POLL_URL . 'public/assets/css/yop-poll-public-' . YOP_POLL_VERSION . '.css' );
	}
	public function change_page_title( $title ) {
		$_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
		$_action = isset( $_GET['action'] ) ? $_GET['action'] : '';
		switch ( $_page ) {
			case 'yop-polls':{
				switch ( $_action ) {
					case 'edit': {
						$title = __( 'Edit Poll', 'yop-poll' );
						break;
					}
					case 'view-results': {
						$title = __( 'View Poll Results', 'yop-poll' );
						break;
					}
					default: {
						$title = __( 'All Polls', 'yop-poll' );
						break;
					}
				}
				break;
			}
			case 'yop-poll-logs': {
				switch ( $_action ) {
					default: {
						$title = __( 'View Logs', 'yop-poll' );
						break;
					}
				}
				break;
			}
			case 'yop-poll-bans': {
				switch ( $_action ) {
					case 'add': {
						$title = __( 'Add Ban', 'yop-poll' );
						break;
					}
					case 'edit': {
						$title = __( 'Edit Ban', 'yop-poll' );
						break;
					}
					default: {
						$title = __( 'All Bans', 'yop-poll' );
						break;
					}
				}
				break;
			}
		}
		return $title;
	}
	public function manage_polls() {
		$_action = isset( $_GET['action'] ) ? $_GET['action'] : '';
		switch ( $_action) {
			case 'edit': {
				$this->show_edit_poll( $_GET['poll_id'] );
				break;
			}
			case 'delete': {
				$this->delete_poll( $_GET['poll_id'] );
				break;
			}
			case 'view-results': {
				$this->display_results( $_GET['poll_id'] );
				break;
			}
            case 'results': {
                $this->build_results( $_GET['poll_id'] );
                break;
            }
            case 'view-votes': {
                $this->display_votes( $_GET['poll_id'] );
                break;
            }
			default: {
				$this->show_polls();
				break;
			}
		}
	}
	public function show_polls() {
		if ( current_user_can( 'yop_poll_results_own' ) ) {
			$params['q'] = isset( $_GET['q']) ? $_GET['q'] : '';
			$params['order_by'] = isset( $_GET['order_by'] ) ? $_GET['order_by'] : '';
			$params['sort_order'] = isset( $_GET['sort_order'] ) ? $_GET['sort_order'] : 'desc';
			$params['page_no'] = isset( $_GET['page_no'] ) ? $_GET['page_no'] : '1';
			$params['perpage'] = isset( $_GET['perpage'] ) && is_numeric( $_GET['perpage'] ) && $_GET['perpage'] > 0 ? $_GET['perpage'] : 10;
			$polls = YOP_Poll_Polls::get_polls( $params );
			$show_guide = YOP_Poll_Settings::get_show_guide();
			$template = YOP_POLL_PATH . 'admin/views/polls/view.php';
			echo YOP_Poll_View::render(
				$template,
				array(
					'polls' => $polls['polls'],
					'statistics' => $polls['statistics'],
					'params' => $params,
					'total_polls' => $polls['total_polls'],
					'total_pages' => $polls['total_pages'],
					'pagination' => $polls['pagination'],
					'date_format' => self::$date_format,
					'time_format' => self::$time_format,
					'show_guide' => $show_guide
				)
			);
		}
		return true;
	}
	public function add_new_poll() {
		if ( current_user_can( 'yop_poll_add' ) ) {
			$template = YOP_POLL_PATH . 'admin/views/polls/add/main.php';
			$templates = YOP_Poll_Templates::get_templates();
			$skins = YOP_Poll_Skins::get_skins();
			echo YOP_Poll_View::render( $template, array(
				'templates' => $templates,
				'skins' => $skins,
				'email_settings' => YOP_Poll_Settings::get_email_settings(),
				'integrations' => YOP_Poll_Settings::get_integrations(),
				'date_format' => self::$date_format
			) );
		}
	}
	public function show_edit_poll( $poll_id ) {
		if ( 0 < intval( $poll_id ) ) {
			$current_user = wp_get_current_user();
			$poll_owner = YOP_Poll_Polls::get_owner( $poll_id );
			if (
				( ( $poll_owner === $current_user->ID ) && ( current_user_can( 'yop_poll_edit_own' ) ) ) ||
				( ( $poll_owner !== $current_user->ID ) && ( current_user_can( 'yop_poll_edit_others' ) ) )
			) {
				$poll = YOP_Poll_Polls::get_poll_for_admin( $poll_id );
				if ( false !== $poll ) {
					$template = YOP_POLL_PATH . 'admin/views/polls/edit/main.php';
					$templates = YOP_Poll_Templates::get_templates();
					$skins = YOP_Poll_Skins::get_skins();
					echo YOP_Poll_View::render( $template, array(
						'poll' => $poll,
						'templates' => $templates,
						'skins' => $skins,
						'integrations' => YOP_Poll_Settings::get_integrations(),
						'date_format' => self::$date_format ) );
				} else {
					echo __( 'You don\'t have sufficient permissions to access this page', 'yop-poll');
				}
			}
		}
	}
	public function create_poll() {
		if ( current_user_can( 'yop_poll_add' ) && check_ajax_referer( 'yop-poll-add-poll', '_token', false ) ) {
			$result = YOP_Poll_Polls::add( json_decode( wp_unslash( $_POST['poll'] ) ) );
			if ( true === $result['success'] ) {
				wp_send_json_success( 
					array(
						'success' => true,
						'message' => __( 'Poll successfully added', 'yop-poll' ),
						'pollId' => $result['poll_id']
					)
				);
			} else {
				wp_send_json_error( $result['error'] );
			}
		} else {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
		}
	}
	public function update_poll() {
		$current_user = wp_get_current_user();
		$poll = json_decode( wp_unslash( $_POST['poll'] ) );
		$poll_owner = YOP_Poll_Polls::get_owner( $poll->id );
		if ( check_ajax_referer( 'yop-poll-edit-poll', '_token', false ) ) {
			if (
				( ( $poll_owner === $current_user->ID ) && ( current_user_can( 'yop_poll_edit_own' ) ) ) ||
				( ( $poll_owner !== $current_user->ID ) && ( current_user_can( 'yop_poll_edit_others' ) ) )
			) {
				$result = YOP_Poll_Polls::update( $poll );
				if ( true === $result['success'] ) {
					wp_send_json_success( 
						array(
							'success' => true,
							'message' => __( 'Poll successfully updated', 'yop-poll' ),
							'newElements' => $result['new-elements'],
							'newSubElements' => $result['new-subelements'],
						)
					);
				} else {
					wp_send_json_error( $result['error'] );
				}
			} else {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
			}
		} else {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
		}
	}
	public function delete_single_poll() {
		if ( check_ajax_referer( 'yop-poll-view-polls', '_token', false ) || check_ajax_referer( 'yop-poll-edit-poll', '_token', false ) ) {
			if ( isset( $_POST['poll_id'] ) && ( 0 < intval( $_POST['poll_id'] ) ) ) {
				$poll_id = sanitize_text_field( $_POST['poll_id'] );
				$current_user = wp_get_current_user();
				$poll_owner = YOP_Poll_Polls::get_owner( $poll_id );
				if (
					( ( $poll_owner === $current_user->ID ) && ( current_user_can( 'yop_poll_delete_own' ) ) ) ||
					( ( $poll_owner !== $current_user->ID ) && ( current_user_can( 'yop_poll_delete_others' ) ) )
				) {
					$result = YOP_Poll_Polls::delete( $poll_id );
					if ( true === $result['success'] ) {
						YOP_Poll_Bans::delete_all_for_poll( $poll_id );
						wp_send_json_success( __( 'Poll successfully deleted', 'yop-poll' ) );
					} else {
						wp_send_json_error( $result['error'] );
					}
				} else {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
				}
			} else {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
			}
		} else {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
		}
	}
	public function delete_bulk_poll() {
		if ( check_ajax_referer( 'yop-poll-bulk-polls', '_token', false ) ) {
			$current_user = wp_get_current_user();
			$polls = json_decode( wp_unslash( $_POST['polls'] ) );
			$success = 0;
			foreach ( $polls as $poll ) {
				$poll_owner = YOP_Poll_Polls::get_owner( $poll );
				if (
					( ( $poll_owner === $current_user->ID ) && ( current_user_can( 'yop_poll_delete_own' ) ) ) ||
					( ( $poll_owner !== $current_user->ID ) && ( current_user_can( 'yop_poll_delete_others' ) ) )
				) {
					$result = YOP_Poll_Polls::delete( $poll );
					if ( true === $result['success'] ) {
						$success++;
					} else {
						$success--;
					}
				} else {
					$success--;
				}
			}
			if ( $success === intval( count( $polls ) ) ) {
				wp_send_json_success( _n(
					'Poll successfully deleted',
					'Polls successfully deleted',
					count( $polls ),
					'yop-poll' )
				);
			} else {
				wp_send_json_error( _(
					'Error deleting poll',
					'Error deleting polls',
					count( $polls ),
					'yop-poll' )
				);
			}
		} else {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
		}
	}
	public function clone_single_poll() {
		if ( check_ajax_referer( 'yop-poll-view-polls', '_token', false ) ) {
			if ( isset( $_POST['poll_id'] ) && ( 0 < intval( $_POST['poll_id'] ) ) ) {
				if ( current_user_can( 'yop_poll_add' ) ) {
					$result = YOP_Poll_Polls::clone_poll( $_POST['poll_id'] );
					if ( true === $result['success'] ) {
						wp_send_json_success( __( 'Poll successfully cloned', 'yop-poll' ) );
					} else {
						wp_send_json_error( $result['error'] );
					}
				} else {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
				}
			} else {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
			}
		} else {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
		}
	}
	public function clone_bulk_poll() {
		if ( check_ajax_referer( 'yop-poll-bulk-polls', '_token', false ) ) {
			$polls = json_decode( wp_unslash( $_POST['polls'] ) );
			$success = 0;
			foreach ( $polls as $poll ) {
				if ( current_user_can( 'yop_poll_add' ) ) {
					$result = YOP_Poll_Polls::clone_poll( $poll );
					if ( true === $result['success'] ) {
						$success++;
					} else {
						$success--;
					}
				} else {
					$success--;
				}
			}
			if ( $success === intval( count( $polls ) ) ) {
				wp_send_json_success( _n(
					'Poll successfully cloned',
					'Polls successfully cloned',
					count( $polls ),
					'yop-poll' )
				);
			} else {
				wp_send_json_error( _(
					'Error cloning poll',
					'Error cloning polls',
					count( $polls ),
					'yop-poll' )
				);
			}
		} else {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
		}
	}
	public function reset_bulk_poll() {
		if ( check_ajax_referer( 'yop-poll-bulk-polls', '_token', false ) ) {
			$polls = json_decode( wp_unslash( $_POST['polls'] ) );
			$success = 0;
			foreach ( $polls as $poll ) {
				if ( current_user_can( 'yop_poll_add' ) ) {
					$result = YOP_Poll_Polls::reset_poll( $poll );
					if ( true === $result['success'] ) {
						$success++;
					} else {
						$success--;
					}
				} else {
					$success--;
				}
			}
			if ( $success === intval( count( $polls ) ) ) {
				wp_send_json_success( __( 'Votes successfully reset', 'yop-poll' ) );
			} else {
				wp_send_json_error( __( 'Error resetting votes', 'yop-poll' ) );
			}
		} else {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
		}
	}
	public function display_results( $poll_id ) {
		if ( current_user_can( 'yop_poll_results_own' ) ) {
			$template = YOP_POLL_PATH . 'admin/views/results/view.php';
			$poll = YOP_Poll_Polls::get_poll_for_admin( $poll_id );
			echo YOP_Poll_View::render(
				$template,
				array(
					'poll' => $poll
				)
			);
		}
	}
	public function build_results( $poll_id ) {
        if ( current_user_can( 'yop_poll_results_own' ) ) {
            $params['q'] = isset( $_GET['q']) ? $_GET['q'] : '';
            $params['order_by'] = isset( $_GET['order_by'] ) ? $_GET['order_by'] : '';
            $params['sort_order'] = isset( $_GET['sort_order'] ) ? $_GET['sort_order'] : 'asc';
            $params['page_no'] = isset( $_GET['page_no'] ) ? $_GET['page_no'] : '1';
            $template = YOP_POLL_PATH . 'admin/views/results/view.php';
            $poll = YOP_Poll_Polls::get_poll_for_admin( $poll_id );
            if ( $poll ) {
                $voters = YOP_Poll_Votes::get_poll_voters_sorted( $poll_id );
                $limit = 10;
                $page = 1;
                $offset = 0;
                $cf_string = '';
                $cf_hidden = '';
                $cf_total_pages = 0;
                $customs_count = 0;
                $total_votes_per_question = [];
                $total_voters_per_question = [];
                $votes_count = $GLOBALS['wpdb']->get_var( $GLOBALS['wpdb']->prepare( "SELECT COUNT(*) FROM `{$GLOBALS['wpdb']->yop_poll_votes}` WHERE `poll_id` = %d AND `status` = 'active'", array( $poll_id ) ) );
                $total_pages = ceil( $votes_count/$limit );
                $query  = "SELECT * FROM `{$GLOBALS['wpdb']->yop_poll_votes}` WHERE `poll_id` = %d AND `status` = 'active' limit $offset, $limit";
                $votes = $GLOBALS['wpdb']->get_results( $GLOBALS['wpdb']->prepare( $query, array( $poll_id ) ) );

                $all_votes_query = "SELECT * FROM `{$GLOBALS['wpdb']->yop_poll_votes}` WHERE `poll_id` = %d AND `status` = 'active'";
                $all_votes = $GLOBALS['wpdb']->get_results( $GLOBALS['wpdb']->prepare( $all_votes_query, array( $poll_id ) ) );

                $other_answers = [];
                foreach ( $all_votes as $av ) {
                    $vote_data = unserialize( $av->vote_data );
					$user_type = $av->user_type;
                    foreach ( $vote_data['elements'] as $ave ) {
                        $question_aswers = [];
                        if ( 'question' === $ave['type'] ) {
                            foreach ( $ave['data'] as $answers ) {
                                if ( 0 == $answers['id'] ) {
                                    $question_aswers[] = $answers['data'];
                                }
                            }
                            if( isset( $total_votes_per_question[$ave['id']] ) ) {
                                $total_votes_per_question[$ave['id']]++;
                            } else {
                                $total_votes_per_question[$ave['id']] = 1;
                            }
                            if( isset( $total_voters_per_question[$ave['id']][$user_type] ) ) {
                                $total_voters_per_question[$ave['id']][$user_type]++;
                            } else {
                                $total_voters_per_question[$ave['id']][$user_type] = 1;
                            }
                            $other_answers[] = [ 'question_id' => $ave['id'], 'other_answers' => $question_aswers ];
                        }
                    }
                }
                $other_answers = YOP_Poll_Helper::group_other_answers( $other_answers );
                if( count( $votes ) > 0 ) {
                    $cf_hidden .= '<input type="hidden" name="cf_total_pages" id="cf-total-pages" value="' . $total_pages . '">';
                    $cf_hidden .= '<input type="hidden" name="cf_page" id="cf-page" value="' . $page . '">';
                    foreach ( $votes as $vote ) {
                        $vote_data = unserialize( $vote->vote_data );
                        $custom_fields = [];
                        foreach ( $vote_data['elements'] as $vde ) {
                            if ( 'custom-field' === $vde['type'] ) {
                                $custom_fields[] = [ 'id' => $vde['id'], 'data' => isset( $vde['data'][0] ) ? $vde['data'][0] : '' ];
                                $customs_count++;
                            }
                        }
                        if ( count( $custom_fields ) > 0 ) {
                            $cf_total_pages = ceil( count( $custom_fields )/$limit );
                            $cf_string .= '<tr>';
                            foreach ( $custom_fields as $cf ) {
                                $cf_string .= '<td>' . $cf['data'] . '</td>';
                            }
                            $cf_string .= '</tr>';
                        } else {
                            $cf_total_pages = 0;
                        }
                    }
                }
                echo YOP_Poll_View::render(
                    $template,
                    array(
                        'params' => $params,
                        'poll' => $poll,
                        'total_votes' => $votes_count,
                        'total_pages' => $total_pages,
                        'voters' => $voters,
                        'cf_string' => $cf_string,
                        'cf_hidden' => $cf_hidden,
                        'cf_total_pages' => $cf_total_pages,
                        'other_answers' => $other_answers,
                        'total_votes_per_question' => $total_votes_per_question,
                        'total_voters_per_question' => $total_voters_per_question
                    )
                );
            } else {
                $error = __( 'Invalid poll', 'yop-poll' );
                $template = YOP_POLL_PATH . 'admin/views/general/error.php';
                echo YOP_Poll_View::render(
                    $template,
                    array(
                        'error' => $error
                    )
                );
            }
        }
    }
    public function display_votes( $poll_id ) {
        if ( current_user_can( 'yop_poll_results_own' ) ) {
            $params['q'] = isset( $_GET['q']) ? $_GET['q'] : '';
            $params['order_by'] = isset( $_GET['order_by'] ) ? $_GET['order_by'] : '';
            $params['sort_order'] = isset( $_GET['sort_order'] ) ? $_GET['sort_order'] : 'asc';
            $params['page_no'] = isset( $_GET['page_no'] ) ? $_GET['page_no'] : '1';
            $params['page'] = isset( $_GET['page'] ) ? $_GET['page'] : 'yop-poll';
            $params['poll_id'] = isset( $_GET['poll_id'] ) ? $_GET['poll_id'] : '';
            $params['action'] = isset( $_GET['action'] ) ? $_GET['action'] : '';
            $template = YOP_POLL_PATH . 'admin/views/results/votes.php';
            $poll = YOP_Poll_Polls::get_poll_for_admin( $poll_id );
            if ( $poll ) {
                $votes = YOP_Poll_Votes::get_votes_to_display( $params );
                echo YOP_Poll_View::render(
                    $template,
                    array(
                        'params' => $params,
                        'poll' => $poll,
                        'total_votes' => $votes['total_votes'],
                        'votes_pages' => $votes['total_pages'],
                        'total_pages' => $votes['total_pages'],
                        'votes' => $votes['votes'],
                        'pagination' => $votes['pagination'],
                        'date_format' => self::$date_format,
                        'time_format' => self::$time_format
                    )
                );
            } else {
                $error = __( 'Invalid poll', 'yop-poll' );
                $template = YOP_POLL_PATH . 'admin/views/general/error.php';
                echo YOP_Poll_View::render(
                    $template,
                    array(
                        'error' => $error
                    )
                );
            }
        }
    }
    public function get_yop_poll_votes_customs() {
        if ( check_ajax_referer( 'yop-poll-get-vote-customs', '_token', false ) ) {
            $limit = 10;
            if(isset($_POST['page']) && $_POST['page'] != "") {
                $page = $_POST['page'];
                $offset = $limit * ($page-1);
            } else {
                $page = 1;
                $offset = 0;
            }
            $votes = YOP_Poll_Votes::get_vote_by_poll( $_POST['poll_id'], $limit, $offset );
            $cf_string = '';
            if( count( $votes ) > 0 ) {
                foreach ( $votes as $vote ) {
                    $vote_data = unserialize( $vote->vote_data );
                    $custom_fields = [];
                    foreach ( $vote_data['elements'] as $vde ) {
                        if ( 'custom-field' === $vde['type'] ) {
                            $custom_fields[] = [ 'id' => $vde['id'], 'data' => isset( $vde['data'][0] ) ? $vde['data'][0] : '' ];
                        }
                    }
                    if ( count( $custom_fields ) > 0 ) {
                        $cf_string .= '<tr>';
                        foreach ($custom_fields as $cf ) {
                            $cf_string .= '<td>' . $cf['data'] . '</td>';
                        }
                        $cf_string .= '</tr>';
                    }
                }
                wp_send_json_success( $cf_string );
            } else {
                wp_send_json_success( $cf_string );
            }
        } else {
            wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
        }
    }
	public function manage_support() {
		$template = YOP_POLL_PATH . 'admin/views/support/view.php';
		echo YOP_Poll_View::render( $template );
	}
	public function migrate_old_tables() {
		$template = YOP_POLL_PATH . 'admin/views/general/migrate-old-tables.php';
		echo YOP_Poll_View::render( $template );
	}
	public function manage_logs () {
		if ( current_user_can( 'yop_poll_add' ) ) {
			$params['q'] = isset( $_GET['q']) ? $_GET['q'] : '';
			$params['order_by'] = isset( $_GET['order_by'] ) ? $_GET['order_by'] : '';
			$params['sort_order'] = isset( $_GET['sort_order'] ) ? $_GET['sort_order'] : 'asc';
			$params['page_no'] = isset( $_GET['page_no'] ) ? $_GET['page_no'] : '1';
            $logs = YOP_Poll_Logs::get_logs( $params );
            $template = YOP_POLL_PATH . 'admin/views/logs/view.php';
            echo YOP_Poll_View::render( $template, array(
                'logs' => $logs['logs'],
                'params' => $params,
                'total_logs' => $logs['total_logs'],
                'total_pages' => $logs['total_pages'],
                'pagination' => $logs['pagination'],
                'date_format' => self::$date_format,
                'time_format' => self::$time_format
            ) );
		}
	}
	public function get_log_details() {
        if ( check_ajax_referer( 'yop-poll-view-logs', '_token', false ) ) {
            if ( isset( $_POST['log_id'] ) && ( 0 < intval( $_POST['log_id'] ) ) ) {
                $log_owner = YOP_Poll_Logs::get_owner( $_POST['log_id'] );
				$current_user = wp_get_current_user();
                if ( $log_owner == $current_user->ID ) {
                    $results = YOP_Poll_Logs::get_log_details( $_POST['log_id'] );
                    $details_string = '';
                    foreach ( $results as $res ) {
                        if ( 'custom-field' === $res['question']) {
                            $details_string .= "<div>" . __( 'Custom Field', 'yop-poll' ) . ': ' . $res['caption'];
                            $details_string .= '<div style="padding-left: 10px;">' . __( 'Answer', 'yop-poll' ) . ': ' .
                                $res['answers'][0]['answer_value'] . '</div>';
                        } else {
                            $details_string .= "<div>" . __('Question', 'yop-poll' ). ': ' . $res['question'];
                            foreach ( $res['answers'] as $ra ) {
                                $details_string .= '<div style="padding-left: 10px;">' . __( 'Answer', 'yop-poll' ) . ': ' . $ra['answer_value'] . '</div>';
                            }
                        }
                        $details_string .= '</div>';
                    }
                    wp_send_json_success( [ 'details' => $details_string ] );
                } else {
                    wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
                }
            } else {
                wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
            }
        } else {
            wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
        }
    }
	public function manage_bans() {
		$_action = isset( $_GET['action'] ) ? $_GET['action'] : '';
		switch ( $_action) {
			case 'add': {
				$this->show_add_ban();
				break;
			}
			case 'edit': {
				$this->show_edit_ban( $_GET['ban_id'] );
				break;
			}
			default: {
				$this->show_bans();
				break;
			}
		}
	}
	public function show_bans() {
		if ( current_user_can( 'yop_poll_add' ) ) {
			$params['q'] = isset( $_GET['q']) ? $_GET['q'] : '';
			$params['order_by'] = isset( $_GET['order_by'] ) ? $_GET['order_by'] : '';
			$params['sort_order'] = isset( $_GET['sort_order'] ) ? $_GET['sort_order'] : 'asc';
			$params['page_no'] = isset( $_GET['page_no'] ) ? $_GET['page_no'] : '1';
			$template = YOP_POLL_PATH . 'admin/views/bans/view.php';
			$bans = YOP_Poll_Bans::get_bans( $params );
			echo YOP_Poll_View::render( $template, array(
				'bans' => $bans['bans'],
				'params' => $params,
				'total_bans' => $bans['total_bans'],
				'total_pages' => $bans['total_pages'],
				'pagination' => $bans['pagination'],
				'date_format' => self::$date_format,
				'time_format' => self::$time_format
			) );
		}
	}
	public function show_add_ban() {
		if ( current_user_can( 'yop_poll_add' ) ) {
			$polls = YOP_Poll_Polls::get_names();
			$template = YOP_POLL_PATH . 'admin/views/bans/add.php';
			echo YOP_Poll_View::render( $template, array(
				'polls' => $polls
			) );
		}
	}
	public function create_ban() {
		if ( current_user_can( 'yop_poll_add' ) && check_ajax_referer( 'yop-poll-add-ban', '_token', false ) ) {
			$result = YOP_Poll_Bans::add( json_decode( wp_unslash( $_POST['ban'] ) ) );
			if ( true === $result['success'] ) {
				wp_send_json_success( __( 'Ban successfully added', 'yop-poll' ) );
			} else {
				wp_send_json_error( $result['error'] );
			}
		} else {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
		}
	}
	public function show_edit_ban( $ban_id ) {
		if ( 0 < intval( $ban_id ) ) {
			$current_user = wp_get_current_user();
			$ban_owner = YOP_Poll_Bans::get_owner( $ban_id );
			if (
				( ( $ban_owner === $current_user->ID ) && ( current_user_can( 'yop_poll_edit_own' ) ) ) ||
				( ( $ban_owner !== $current_user->ID ) && ( current_user_can( 'yop_poll_edit_others' ) ) )
			) {
				$ban = YOP_Poll_Bans::get_ban( $ban_id );
				if ( false !== $ban ) {
					$polls = YOP_Poll_Polls::get_names();
					$template = YOP_POLL_PATH . 'admin/views/bans/edit.php';
					echo YOP_Poll_View::render( $template, array(
						'ban' => $ban['ban'],
						'polls' => $polls
					));
				} else {
					echo __( 'You don\'t have sufficient permissions to access this page', 'yop-poll' );
				}
			}
		}
	}
	public function delete_single_ban() {
		if ( check_ajax_referer( 'yop-poll-view-bans', '_token', false ) ) {
			if ( isset( $_POST['ban_id'] ) && ( 0 < intval( $_POST['ban_id'] ) ) ) {
				$ban_id = sanitize_text_field( $_POST['ban_id'] );
				$current_user = wp_get_current_user();
				$ban_owner = YOP_Poll_Bans::get_owner( $ban_id );
				if (
					( ( $ban_owner === $current_user->ID ) && ( current_user_can( 'yop_poll_delete_own' ) ) ) ||
					( ( $ban_owner !== $current_user->ID ) && ( current_user_can( 'yop_poll_delete_others' ) ) )
				) {
					$result = YOP_Poll_Bans::delete( $ban_id );
					if ( true === $result['success'] ) {
						wp_send_json_success( __( 'Ban successfully deleted', 'yop-poll' ) );
					} else {
						wp_send_json_error( $result['error'] );
					}
				} else {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
				}
			} else {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
			}
		} else {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
		}
	}
	public function update_ban() {
		$ban = json_decode( wp_unslash( $_POST['ban'] ) );
		$ban_owner = YOP_Poll_Bans::get_owner( $ban->ban->id );
		$current_user = wp_get_current_user();
		if ( check_ajax_referer( 'yop-poll-edit-ban', '_token', false ) ) {
			if (
				( ( $ban_owner === $current_user->ID ) && ( current_user_can( 'yop_poll_edit_own' ) ) ) ||
				( ( $ban_owner !== $current_user->ID ) && ( current_user_can( 'yop_poll_edit_others' ) ) )
			) {
				$result = YOP_Poll_Bans::update( $ban );
				if ( true === $result['success'] ) {
					wp_send_json_success( __( 'Ban successfully updated', 'yop-poll' ) );
				} else {
					wp_send_json_error( $result['error'] );
				}
			} else {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
			}
		} else {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
		}
	}
	public function delete_bulk_ban() {
		if ( check_ajax_referer( 'yop-poll-bulk-bans', '_token', false ) ) {
			$bans = json_decode( wp_unslash( $_POST['bans'] ) );
			$success = 0;
			$current_user = wp_get_current_user();
			foreach ( $bans as $ban ) {
				$ban_owner = YOP_Poll_Bans::get_owner( $ban );
				if (
					( ( $ban_owner === $current_user->ID ) && ( current_user_can( 'yop_poll_delete_own' ) ) ) ||
					( ( $ban_owner !== $current_user->ID ) && ( current_user_can( 'yop_poll_delete_others' ) ) )
				) {
					$result = YOP_Poll_Bans::delete( $ban );
					if ( true === $result['success'] ) {
						$success++;
					} else {
						$success--;
					}
				} else {
					$success--;
				}
			}
			if ( $success === intval( count( $bans ) ) ) {
				wp_send_json_success( _n(
					'Ban successfully deleted',
					'Bans successfully deleted',
					count( $bans ),
					'yop-poll' )
				);
			} else {
				wp_send_json_error( _(
					'Error deleting ban',
					'Error deleting bans',
					count( $bans ),
					'yop-poll' )
				);
			}
		} else {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
		}
	}
	public function record_vote() {
		$vote_data = json_decode( wp_unslash( $_POST['data'] ) );
		if ( isset( $vote_data->pollId ) && ( 0 < intval( $vote_data->pollId ) ) ) {
			if ( check_ajax_referer( 'yop-poll-vote-' . $vote_data->pollId, '_token', false ) ) {
				$result = YOP_Poll_Votes::add( $vote_data );
				if ( true === $result['success'] ) {
					wp_send_json_success( __( 'Vote Recorded', 'yop-poll' ) );
				} else {
					wp_send_json_error( $result['error'] );
				}
			} else {
				wp_send_json_error( __( 'Invalid data 1', 'yop-poll' ) );
			}
		} else {
			wp_send_json_error( __( 'Invalid data 2', 'yop-poll' ) );
		}
	}
	public function record_wordpress_vote() {
		if ( isset( $_GET['poll_id'] ) && ( 0 < intval( $_GET['poll_id'] ) ) ) {
			$template = YOP_POLL_PATH . 'admin/views/general/addnewwordpressvote.php';
			echo YOP_Poll_View::render( $template, array(
				'poll_id' => $_GET['poll_id']
			) );
		} else {
			echo 'no go';
		}
		wp_die();
	}
	public function get_vote_details () {
        if ( check_ajax_referer( 'yop-poll-get-vote-details', '_token', false ) ) {
            if ( isset( $_POST['voteid'] ) && ( intval( $_POST['voteid'] ) > 0 ) ) {
                $results = YOP_Poll_Votes::get_vote_details( $_POST['voteid'] );
                $details_string = '';
                foreach ( $results as $res ) {
                    if ( 'custom-field' === $res['question']) {
                        $details_string .= "<div>" . __('Custom Field', 'yop-poll' ) . ': ' . $res['caption'];
                        $details_string .= '<div style="padding-left: 10px;">' . __( 'Answer', 'yop-poll' ) . ': ' .
                            $res['answers'][0]['answer_value'] . '</div>';
                    } else {
                        $details_string .= "<div>" . __('Question', 'yop-poll' ). ': ' . $res['question'];
                        foreach ( $res['answers'] as $ra ) {
                            $details_string .= '<div style="padding-left: 10px;">' . __( 'Answer', 'yop-poll' ) . ': ' . $ra['answer_value'] . '</div>';
                        }
                    }
                    $details_string .= '</div>';
                    }
                    wp_send_json_success( [ 'details' => $details_string ] );
            } else {
                wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
            }
        } else {
            wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
        }
    }
    public function delete_single_vote() {
        if ( check_ajax_referer( 'yop-poll-get-vote-details', '_token', false ) ) {
            $poll_id = sanitize_text_field( $_POST['poll_id'] );
            $vote_id = sanitize_text_field( $_POST['vote_id'] );
            $success = 0;
            $current_user = wp_get_current_user();
            $vote_owner = YOP_Poll_Votes::get_owner( $vote_id );
            if (
                ( ( $vote_owner === $current_user->ID ) && ( current_user_can( 'yop_poll_delete_own' ) ) ) ||
                ( ( $vote_owner !== $current_user->ID ) && ( current_user_can( 'yop_poll_delete_others' ) ) )
            ) {
                if ( $vote_id >0 ) {
                    $result = YOP_Poll_Votes::delete_vote( $vote_id, $poll_id );
                    if ( true === $result ) {
                        wp_send_json_success( __( 'Vote successfully deleted', 'yop-poll' ) );
                    } else {
                        wp_send_json_error( __( 'Error deleting vote', 'yop-poll' ) );
                    }
                }
            } else {
                wp_send_json_error( __( 'Error deleting vote', 'yop-poll' ) );
            }
        } else {
            wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
        }
    }
    public function delete_bulk_votes() {
        if ( check_ajax_referer( 'yop-poll-bulk-votes', '_token', false ) ) {
            $votes = json_decode( wp_unslash( $_POST['votes'] ) );
            $poll_id = $_POST['poll_id'];
            $success = 0;
            $current_user = wp_get_current_user();
            foreach ( $votes as $vote ) {
                $vote_owner = YOP_Poll_Votes::get_owner( $vote );
                if (
                    ( ( $vote_owner === $current_user->ID ) && ( current_user_can( 'yop_poll_delete_own' ) ) ) ||
                    ( ( $vote_owner !== $current_user->ID ) && ( current_user_can( 'yop_poll_delete_others' ) ) )
                ) {
                    $votes_to_be_deleted[] = $vote;
                    if ( count( $votes_to_be_deleted ) >0 ) {
                        $result = YOP_Poll_Votes::delete_vote( $vote, $poll_id );
                        if ( true === $result ) {
                            $success++;
                        } else {
                            $success--;
                        }
                    }
                } else {
                    $success--;
                }
            }
            if ( $success === intval( count( $votes ) ) ) {
                wp_send_json_success( _n(
                        'Vote successfully deleted',
                        'Votes successfully deleted',
                        count( $votes ),
                        'yop-poll' )
                );
            } else {
                wp_send_json_error( _(
                        'Error deleting vote',
                        'Error deleting votes',
                        count( $votes ),
                        'yop-poll' )
                );
            }
        } else {
            wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
        }
    }
    public function delete_single_log() {
        if ( check_ajax_referer( 'yop-poll-view-logs', '_token', false ) ) {
            if ( isset( $_POST['log_id'] ) && ( 0 < intval( $_POST['log_id'] ) ) ) {
				$log_id = sanitize_text_field( $_POST['log_id'] );
                $log_owner = YOP_Poll_Logs::get_owner( $log_id );
				$current_user = wp_get_current_user();
                if (
                    ( ( $log_owner === $current_user->ID ) && ( current_user_can( 'yop_poll_delete_own' ) ) ) ||
                    ( ( $log_owner !== $current_user->ID ) && ( current_user_can( 'yop_poll_delete_others' ) ) )
                ) {
                    $result = YOP_Poll_Logs::delete( $log_id );
                    if ( true === $result['success'] ) {
                        wp_send_json_success( __( 'Log successfully deleted', 'yop-poll' ) );
                    } else {
                        wp_send_json_error( $result['error'] );
                    }
                } else {
                    wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
                }
            } else {
                wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
            }
        } else {
            wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
        }
    }
    public function delete_bulk_log() {
        if ( check_ajax_referer( 'yop-poll-bulk-logs', '_token', false ) ) {
            $logs = json_decode( wp_unslash( $_POST['logs'] ) );
            $success = 0;
			$current_user = wp_get_current_user();
            foreach ( $logs as $log ) {
                $log_owner = YOP_Poll_Logs::get_owner( $log );
                if (
                    ( ( $log_owner === $current_user->ID ) && ( current_user_can( 'yop_poll_delete_own' ) ) ) ||
                    ( ( $log_owner !== $current_user->ID ) && ( current_user_can( 'yop_poll_delete_others' ) ) )
                ) {
                    $result = YOP_Poll_Logs::delete( $log );
                    if ( true === $result['success'] ) {
                        $success++;
                    } else {
                        $success--;
                    }
                } else {
                    $success--;
                }
            }
            if ( $success === intval( count( $logs ) ) ) {
                wp_send_json_success( _n(
                        'Log successfully deleted',
                        'Logs successfully deleted',
                        count( $logs ),
                        'yop-poll' )
                );
            } else {
                wp_send_json_error( _(
                        'Error deleting log',
                        'Error deleting logs',
                        count( $logs ),
                        'yop-poll' )
                );
            }
        } else {
            wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
        }
    }
	public function manage_settings() {
        $unserialized_settings = array();
        if ( current_user_can( 'yop_poll_add' ) ) {
            $template = YOP_POLL_PATH . 'admin/views/settings/view.php';
            $yop_poll_settings = get_option( 'yop_poll_settings' );
            if ( $yop_poll_settings ) {
                $unserialized_settings = unserialize( $yop_poll_settings );
            }
            echo YOP_Poll_View::render( $template, array( 'settings' => $unserialized_settings ) );
        }
    }
	public function save_settings () {
        if ( current_user_can( 'yop_poll_add' ) ) {
            if ( check_ajax_referer( 'yop-poll-update-settings', '_token', false ) ) {
                $result = YOP_Poll_Settings::save_settings( json_decode( wp_unslash( $_POST['settings'] ) ) );
                if ( true === $result['success'] ) {
                    wp_send_json_success( __( 'Settings updated', 'yop-poll' ) );
                } else {
                    wp_send_json_error( $result['error'] );
                }
            } else {
                wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
            }
        } else {
            wp_send_json_error( __( 'You are not allowed to perform this action', 'yop-poll' ) );
        }
	}
	public function add_votes_manually() {
		if ( isset( $_POST['id'] ) && ( 0 < intval( $_POST['id'] ) ) ) {
			if ( check_ajax_referer( 'yop-poll-add-votes-manually', '_token', false ) ) {
				$poll_id = intval( $_POST['id'] );
				$votes_data = json_decode( wp_unslash( $_POST['data'] ) );
				$result = YOP_Poll_Polls::add_votes_manually( $poll_id, $votes_data );
				if ( true === $result['success'] ) {
					wp_send_json_success( __( 'Votes Succesfully Added', 'yop-poll' ) );
				} else {
					wp_send_json_error( $result['error'] );
				}
			} else {
				wp_send_json_error( __( 'Invalid data 1', 'yop-poll' ) );
			}
		} else {
			wp_send_json_error( __( 'Invalid data 2', 'yop-poll' ) );
		}
	}
	public function create_poll_for_frontend() {
		if ( ( true === isset( $_POST['poll_id'] ) ) && ( '' !== $_POST['poll_id'] )  ) {
			$params = array();
			$poll_id = sanitize_text_field( $_POST['poll_id'] );
			$params['tracking_id'] = sanitize_text_field( $_POST['tracking_id'] );
			$params['show_results'] = sanitize_text_field( $_POST['show_results'] );
			$poll_for_output = YOP_Poll_Public::generate_poll_for_ajax( $poll_id, $params );
			if ( false !== $poll_for_output ) {
				wp_send_json_success( $poll_for_output );
			} else {
				wp_send_json_error( __( 'Error generating poll', 'yop-poll' ) );
				wp_die();
			}
		}
	}
	public function stop_showing_guide() {
		YOP_Poll_Settings::update_show_guide( 'no' );
		wp_send_json_success( __( 'Setting Updated', 'yop-poll' ) );
	}
	public function send_guide() {
		$user_input = sanitize_text_field( $_POST['input'] );
		$url = 'https://admin.yoppoll.com/';
        $request_string = array(
            'body'       => array(
                'action'  => 'send-guide',
                'input' =>  $user_input
            ),
            'user-agent' => 'WordPress/' . YOP_POLL_VERSION . ';'
        );
        $result = wp_remote_post( $url, $request_string );
        if( ! is_wp_error( $result ) && ( 200 === $result['response']['code'] ) ) {
            $response = unserialize( $result['body'] );
        } else {
            $response = null;
		}
		YOP_Poll_Settings::update_show_guide( 'no' );
		wp_send_json_success( __( 'Guide Sent', 'yop-poll' ) );
	}
	public function show_upgrade_to_pro() {
		$template = YOP_POLL_PATH . 'admin/views/general/upgrade-page.php';
		echo YOP_Poll_View::render( $template, array(
			'link' => menu_page_url( 'yop-polls', false )
		) );
	}
}
