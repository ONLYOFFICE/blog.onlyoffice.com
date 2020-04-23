<?php
class Yop_Poll_DbSchema {
	private $charset;
	public function __construct() {
		$this->charset = $GLOBALS['wpdb']->get_charset_collate();
		$this->initialize_tables_names();
	}
	public function create_tables() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$this->create_table_templates();
		$this->install_templates();
		$this->create_table_skins();
		$this->install_skins();
		$this->create_table_polls();
		$this->create_table_elements();
		$this->create_table_subelements();
		$this->create_table_bans();
		$this->create_table_votes();
		$this->create_table_logs();
		$this->create_table_other_answers();
	}
	public static function initialize_tables_names() {
		$GLOBALS['wpdb']->yop_poll_polls = $GLOBALS['wpdb']->prefix . 'yoppoll_polls';
		$GLOBALS['wpdb']->yop_poll_elements = $GLOBALS['wpdb']->prefix . 'yoppoll_elements';
		$GLOBALS['wpdb']->yop_poll_subelements = $GLOBALS['wpdb']->prefix . 'yoppoll_subelements';
		$GLOBALS['wpdb']->yop_poll_bans = $GLOBALS['wpdb']->prefix . 'yoppoll_bans';
		$GLOBALS['wpdb']->yop_poll_votes = $GLOBALS['wpdb']->prefix . 'yoppoll_votes';
		$GLOBALS['wpdb']->yop_poll_logs = $GLOBALS['wpdb']->prefix . 'yoppoll_logs';
		$GLOBALS['wpdb']->yop_poll_templates = $GLOBALS['wpdb']->prefix . 'yoppoll_templates';
		$GLOBALS['wpdb']->yop_poll_skins = $GLOBALS['wpdb']->prefix . 'yoppoll_skins';
		$GLOBALS['wpdb']->yop_poll_other_answers = $GLOBALS['wpdb']->prefix . 'yoppoll_other_answers';
	}
	public function create_table_polls() {
		$create_table_sql = "CREATE TABLE `{$GLOBALS['wpdb']->yop_poll_polls}` (
			id int(11) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			template int(11) NOT NULL,
			template_base varchar(255) NOT NULL,
			skin_base varchar(255) NOT NULL,
			author bigint(20) NOT NULL,
			stype varchar(20) NOT NULL,
			status varchar(20) NOT NULL,
			meta_data longtext NOT NULL,
			total_submits int(11) NOT NULL,
			total_submited_answers int(11) NOT NULL,
			added_date datetime NOT NULL,
			modified_date datetime NOT NULL,
			PRIMARY KEY (id)
		) {$this->charset};";
		dbDelta( $create_table_sql );
	}
	public function create_table_elements() {
		$create_table_sql = "CREATE TABLE `{$GLOBALS['wpdb']->yop_poll_elements}` (
			id int(11) NOT NULL AUTO_INCREMENT,
			poll_id int(11) NOT NULL,
			etext text NOT NULL,
			author bigint(20) NOT NULL,
			etype varchar(20) NOT NULL,
			status varchar(20) NOT NULL,
			sorder int(11) NOT NULL,
			meta_data longtext NOT NULL,
			added_date datetime NOT NULL,
			modified_date DATETIME NOT NULL,
			PRIMARY KEY (id)
		) {$this->charset};";
		dbDelta( $create_table_sql );
	}
	public function create_table_subelements() {
		$create_table_sql = "CREATE TABLE `{$GLOBALS['wpdb']->yop_poll_subelements}` (
			id int(11) NOT NULL AUTO_INCREMENT,
			poll_id int(11) NOT NULL,
			element_id int(11) NOT NULL,
			stext text not null,
			author bigint(20) NOT NULL,
			stype varchar(20) NOT NULL,
			status varchar(20) NOT NULL,
			sorder int(11) NOT NULL,
			meta_data longtext NOT NULL,
			total_submits int(11) NOT NULL,
			added_date datetime NOT NULL,
			modified_date datetime NOT NULL,
			PRIMARY KEY (id)
		) {$this->charset};";
		dbDelta( $create_table_sql );
	}
	public function create_table_bans() {
		$create_table_sql = "CREATE TABLE `{$GLOBALS['wpdb']->yop_poll_bans}` (
			id int(11) NOT NULL AUTO_INCREMENT,
			author bigint(20) NOT NULL,
			poll_id int(11) NOT NULL,
			b_by varchar(255) NOT NULL,
			b_value varchar(255) NOT NULL,
			added_date datetime NOT NULL,
			modified_date datetime NOT NULL,
			PRIMARY KEY (id)
		) {$this->charset};";
		dbDelta( $create_table_sql);
	}
	public function create_table_votes() {
		$create_table_sql = "CREATE TABLE `{$GLOBALS['wpdb']->yop_poll_votes}` (
			id int(11) NOT NULL AUTO_INCREMENT,
			poll_id int(11) NOT NULL,
			user_id bigint(20) NOT NULL,
			user_email varchar(255) NULL,
			user_type varchar(100) NOT NULL,
			ipaddress varchar(100) NOT NULL,
			tracking_id varchar(255) NOT NULL,
			voter_id varchar(255) NOT NULL,
			voter_fingerprint varchar(255) NOT NULL,
			vote_data longtext NOT NULL,
			status varchar(10) NOT NULL,
			added_date datetime NOT NULL,
			PRIMARY KEY ( id )
		) {$this->charset};";
		dbDelta( $create_table_sql );
	}
	public function create_table_logs() {
		$create_table_sql = "CREATE TABLE `{$GLOBALS['wpdb']->yop_poll_logs}` (
			id int(11) NOT NULL AUTO_INCREMENT,
			poll_id int(11) NOT NULL,
			poll_author bigint(20) NOT NULL,
			user_id bigint(20) NOT NULL,
			user_email varchar(255) NULL,
			user_type varchar(100) NOT NULL,
			ipaddress varchar(100) NOT NULL,
			tracking_id varchar(255) NOT NULL,
			voter_id varchar(255) NOT NULL,
			voter_fingerprint varchar(255) NOT NULL,
			vote_data longtext NOT NULL,
			vote_message longtext NOT NULL,
			added_date datetime NOT NULL,
			PRIMARY KEY ( id )
		) {$this->charset};";
		dbDelta( $create_table_sql );
	}
	public function create_table_templates() {
		$create_table_sql = "CREATE TABLE `{$GLOBALS['wpdb']->yop_poll_templates}` (
			id int(11) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			base varchar(255) NOT NULL,
			description text NOT NULL,
			html_preview text NOT NULL,
			added_date datetime NOT NULL,
			PRIMARY KEY (id)
		) {$this->charset};";
		dbDelta( $create_table_sql );
	}
	public function create_table_skins() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$create_table_sql = "CREATE TABLE `{$GLOBALS['wpdb']->yop_poll_skins}` (
			id int(11) NOT NULL AUTO_INCREMENT,
			template_base varchar(255) NOT NULL,
			name varchar(255) NOT NULL,
			base varchar(255) NOT NULL,
			description text NOT NULL,
			html_preview text NOT NULL,
			skin_type ENUM('predefined', 'custom') DEFAULT 'predefined',
			author bigint(20) NOT NULL,
			meta_data longtext NOT NULL,
			added_date datetime NOT NULL,
			modified_date datetime NOT NULL,
			PRIMARY KEY (id)
		) {$this->charset};";
		dbDelta( $create_table_sql );
	}
	public function install_templates() {
		$plugin_version = get_option( 'yop_poll_version' );
		$available_templates = YOP_Poll_Templates::get_templates();
		$table = $GLOBALS['wpdb']->yop_poll_templates;
		$templates = array(
			0 => array(
				'name' => 'Basic Template',
				'base' => 'basic',
				'description' => '',
				'html_preview' => '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px;padding-bottom: 14px;">
						<div class="row">
							<div class="col-md-12">
								<div class="basic-inner">
									<form class="basic-form">
										<div class="basic-elements">
											<div class="basic-element basic-question basic-question-text-vertical">
												<div class="basic-question-title">
													<h5>Poll Question</h5>
												</div><!-- end basic-question-title -->
												<ul class="basic-answers">
													<li class="basic-answer">
														<div class="basic-answer-content basic-text-vertical">
															<input type="radio" checked>    
															<label class="basic-text">Answer 1</label>
														</div>
													</li>
													<li class="basic-answer">
														<div class="basic-answer-content basic-text-vertical">
															<input type="radio">    
															<label class="basic-text">Answer 2</label>
														</div>
													</li>
													<li class="basic-answer">
														<div class="basic-answer-content basic-text-vertical">
															<input type="radio">    
															<label class="basic-text">Answer 3</label>
														</div>
													</li>
													<li class="basic-answer">
														<div class="basic-answer-content basic-text-vertical">
															<input type="radio">    
															<label class="basic-text">Answer 4</label>
														</div>
													</li>
													<li class="basic-answer">
														<div class="basic-answer-content basic-text-vertical">
															<input type="radio">    
															<label class="basic-text">Answer 5</label>
														</div>
													</li>
												</ul><!-- end basic-answers -->
											</div><!-- end basic-element basic-question -->
											<div class="clearfix"></div>
										</div><!-- end basic-elements -->
										<div class="basic-vote">
											<a href="#" class="button basic-vote-button">Vote</a>
										</div><!-- end basic-vote -->
									</form><!-- end basic-form" -->
								</div><!-- end basic-inner -->
							</div><!-- end col-md-12 -->
						</div><!-- end row -->
					</div><!-- end basic-yop-poll-container -->',
				 'added_date' => current_time( 'mysql' )
			),
			1 => array(
				'name' => 'Basic Template With Pretty Controls',
				'base' => 'basic-pretty',
				'description' => '',
				'html_preview' => '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="square" data-cscheme="red">
						<div class="row">
							<div class="col-md-12">
								<div class="basic-inner">
									<form class="basic-form">
										<div class="basic-elements">
											<div class="basic-element basic-question basic-question-text-vertical">
												<div class="basic-question-title">
													<h5>Poll Question</h5>
												</div><!-- end basic-question-title -->
												<ul class="basic-answers">
													<li class="basic-answer">
														<div class="basic-answer-content basic-text-vertical">
															<input type="radio" checked>    
															<label class="basic-text">Answer 1</label>
														</div>
													</li>
													<li class="basic-answer">
														<div class="basic-answer-content basic-text-vertical">
															<input type="radio">    
															<label class="basic-text">Answer 2</label>
														</div>
													</li>
													<li class="basic-answer">
														<div class="basic-answer-content basic-text-vertical">
															<input type="radio">    
															<label class="basic-text">Answer 3</label>
														</div>
													</li>
													<li class="basic-answer">
														<div class="basic-answer-content basic-text-vertical">
															<input type="radio">    
															<label class="basic-text">Answer 4</label>
														</div>
													</li>
													<li class="basic-answer">
														<div class="basic-answer-content basic-text-vertical">
															<input type="radio">    
															<label class="basic-text">Answer 5</label>
														</div>
													</li>
												</ul><!-- end basic-answers -->
											</div><!-- end basic-element basic-question -->
											<div class="clearfix"></div>
										</div><!-- end basic-elements -->
										<div class="basic-vote">
											<a href="#" class="button basic-vote-button">Vote</a>
										</div><!-- end basic-vote -->
									</form><!-- end basic-form" -->
								</div><!-- end basic-inner -->
							</div><!-- end col-md-12 -->
						</div><!-- end row -->
					</div><!-- end basic-yop-poll-container -->',
				'added_date' => current_time( 'mysql' )
			)
		);
		foreach ( $templates as $template ) {
			if ( false === YOP_Poll_Templates::template_already_exists( $template['base'], $available_templates ) ) {
				$GLOBALS['wpdb']->insert( $table, $template );
			}
		}
	}
	public function create_table_other_answers() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$create_table_sql = "CREATE TABLE `{$GLOBALS['wpdb']->yop_poll_other_answers}` (
			id INT(11) NOT NULL AUTO_INCREMENT,
			poll_id INT(11) NOT NULL,
			element_id INT(11) NOT NULL,
			vote_id INT(11) NOT NULL,
			answer LONGTEXT NOT NULL,
			status VARCHAR(10) NOT NULL,
			added_date DATETIME NOT NULL,
			PRIMARY KEY ( id )
		) {$this->charset};";
		dbDelta( $create_table_sql );
	}
	public function install_skins() {
		$table = $GLOBALS['wpdb']->yop_poll_skins;
		$available_skins = YOP_Poll_Skins::get_skins();
		$skins=array(
			0 => array(
				'template_base' => 'basic',
				'name' => 'Orange Def',
				'base' => 'orange-def',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container orange-def" style="padding-top: 14px;padding-bottom: 14px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'left'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ee7600',
							'borderSize' => '0px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#fff',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => ''
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			1 => array(
				'template_base' => 'basic',
				'name' => 'Dark V1',
				'base' => 'dark-v1',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container dark-v1" style="padding-top: 15px;padding-bottom: 15px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize( 
					array(
						'poll' => array(
							'backgroundColor' => '#555555',
							'borderSize' => '0px',
							'borderColor' => '#555555',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#ffffff',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			2 => array(
				'template_base' => 'basic',
				'name' => 'Gray V1',
				'base' => 'gray-v1',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container grey" style="padding-top: 15px;padding-bottom: 15px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">nswer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#EEEEEE',
							'borderSize' => '0px',
							'borderColor' => '#EEEEEE',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
								text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			3 => array(
				'template_base' => 'basic',
				'name' => 'White V1',
				'base' => 'white-v1',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v1" style="padding-top: 15px;padding-bottom: 15px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '0px',
							'borderColor' => '#ffffff',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid]  .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			4 => array(
				'template_base' => 'basic',
				'name' => 'Dark V2',
				'base' => 'dark-v2',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container dark-v2" style="padding-top: 15px;padding-bottom: 15px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#555555',
							'borderSize' => '0px',
							'borderColor' => '#555555',
							'borderRadius' => '7px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#ffffff',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			5 => array(
				'template_base' => 'basic',
				'name' => 'White V2',
				'base' => 'white-v2',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px;padding-bottom: 14px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			6 => array(
				'template_base' => 'basic',
				'name' => 'Orange V1',
				'base' => 'orange-v1',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container orange-v1" style="padding-top: 15px;padding-bottom: 15px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#FB6911',
							'borderSize' => '0px',
							'borderColor' => '#FB6911',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#ffffff',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			7 => array(
				'template_base' => 'basic',
				'name' => 'Orange V2',
				'base' => 'orange-v2',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container orange-v2">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '0px',
							'borderColor' => '#ffffff',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#ffffff',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}
								.basic-yop-poll-container[data-uid] .basic-question-title h5 {
									background-color: #FB6911;
									padding: 5px 0;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			8 => array(
				'template_base' => 'basic',
				'name' => 'Orange V3',
				'base' => 'orange-v3',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container orange-v3" style="padding-top: 12px;padding-bottom: 12px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#FB6911',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#55555',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			9 => array(
				'template_base' => 'basic',
				'name' => 'Orange V4',
				'base' => 'orange-v4',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container orange-v4" style="padding-top: 12px;padding-bottom: 12px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#FB6911',
							'borderRadius' => '7px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#555555',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			10 => array(
				'template_base' => 'basic',
				'name' => 'Orange V5',
				'base' => 'orange-v5',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container orange-v5" style="padding-top: 15px;padding-bottom: 15px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#FB6911',
							'borderSize' => '0px',
							'borderColor' => '#FB6911',
							'borderRadius' => '7px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#ffffff',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			11 => array(
				'template_base' => 'basic',
				'name' => 'Green V1',
				'base' => 'green-v1',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container green-v1" style="padding-top: 15px;padding-bottom: 15px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#3F8B43',
							'borderSize' => '0px',
							'borderColor' => '#3F8B43',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#ffffff',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			12 => array(
				'template_base' => 'basic',
				'name' => 'Green V2',
				'base' => 'green-v2',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container green-v2">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text"> Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text"> Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text"> Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text"> Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text"> Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button"> Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '0px',
							'borderColor' => '#ffffff',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			13 => array(
				'template_base' => 'basic',
				'name' => 'Green V3',
				'base' => 'green-v3',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container green-v3" style="padding-top: 12px;padding-bottom: 12px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#3F8B43',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			14 => array(
				'template_base' => 'basic',
				'name' => 'Green V4',
				'base' => 'green-v4',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container green-v4" style="padding-top: 12px;padding-bottom: 12px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text"> Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#3F8B43',
							'borderRadius' => '7px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			15 => array(
				'template_base' => 'basic',
				'name' => 'Green V5',
				'base' => 'green-v5',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container green-v5" style="padding-top: 15px;padding-bottom: 15px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#3F8B43',
							'borderSize' => '0px',
							'borderColor' => '#3F8B43',
							'borderRadius' => '7px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			16 => array(
				'template_base' => 'basic',
				'name' => 'Red V1',
				'base' => 'red-v1',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container red-v1" style="padding-top: 15px;padding-bottom: 15px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#B70004',
							'borderSize' => '0px',
							'borderColor' => '#B70004',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			17 => array(
				'template_base' => 'basic',
				'name' => 'Red V2',
				'base' => 'red-v2',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container red-v2">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '0px',
							'borderColor' => '#ffffff',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}
								.basic-yop-poll-container[data-uid] .basic-question-title h5 {
									background-color: #B70004;
									padding: 5px 0;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			18 => array(
				'template_base' => 'basic',
				'name' => 'Red V3',
				'base' => 'red-v3',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container red-v3" style="padding-top: 12px;padding-bottom: 12px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#B70004',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			19 => array(
				'template_base' => 'basic',
				'name' => 'Red V4',
				'base' => 'red-v4',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container red-v4" style="padding-top: 12px;padding-bottom: 12px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#B70004',
							'borderRadius' => '7px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			20 => array(
				'template_base' => 'basic',
				'name' => 'Red V5',
				'base' => 'red-v5',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container red-v5" style="padding-top: 15px;padding-bottom: 15px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#B70004',
							'borderSize' => '0px',
							'borderColor' => '#B70004',
							'borderRadius' => '7px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '5px',
							'paddingTopBottom' => '10px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			21 => array(
				'template_base' => 'basic',
				'name' => 'Blue V1',
				'base' => 'blue-v1',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container blue-v1" style="padding-top: 15px;padding-bottom: 15px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#327BD6',
							'borderSize' => '0px',
							'borderColor' => '#327BD6',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			22 => array(
				'template_base' => 'basic',
				'name' => 'Blue V2',
				'base' => 'blue-v2',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container blue-v2">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '0px',
							'borderColor' => '#ffffff',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}
								.basic-yop-poll-container[data-uid] .basic-question-title h5 {
									background-color: #327BD6;
									padding: 5px 0;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			23 => array(
				'template_base' => 'basic',
				'name' => 'Blue V3',
				'base' => 'blue-v3',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container blue-v3" style="padding-top: 12px;padding-bottom: 12px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#327BD6',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			24 => array(
				'template_base' => 'basic',
				'name' => 'Blue V4',
				'base' => 'blue-v4',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container blue-v4" style="padding-top: 12px;padding-bottom: 12px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#327BD6',
							'borderRadius' => '7px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			25 => array(
				'template_base' => 'basic',
				'name' => 'Blue V5',
				'base' => 'blue-v5',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container blue-v5" style="padding-top: 15px;padding-bottom: 15px;">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#327BD6',
							'borderSize' => '0px',
							'borderColor' => '#327BD6',
							'borderRadius' => '7px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#ffffff',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => '',
							'colorScheme' => ''
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			26 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Square Black',
				'base' => 'square-black',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="square" data-cscheme="black">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'square',
							'colorScheme' => 'black'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			27 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Square Red',
				'base' => 'square-red',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="square" data-cscheme="red">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'square',
							'colorScheme' => 'red'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			28 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Square Green',
				'base' => 'square-green',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="square" data-cscheme="green">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'square',
							'colorScheme' => 'green'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			29 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Square Blue',
				'base' => 'square-blue',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="square" data-cscheme="blue">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'square',
							'colorScheme' => 'blue'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			30 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Square Aero',
				'base' => 'square-aero',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="square" data-cscheme="aero">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'square',
							'colorScheme' => 'aero'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			31 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Square Grey',
				'base' => 'square-grey',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="square" data-cscheme="grey">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'square',
							'colorScheme' => 'grey'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			32 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Square Orange',
				'base' => 'square-orange',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="square" data-cscheme="orange">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'square',
							'colorScheme' => 'orange'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			33 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Square Yellow',
				'base' => 'square-yellow',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="square" data-cscheme="yellow">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'square',
							'colorScheme' => 'yellow'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			34 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Square Pink',
				'base' => 'square-pink',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="square" data-cscheme="pink">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'square',
							'colorScheme' => 'pink'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			35 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Square Purple',
				'base' => 'square-purple',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="square" data-cscheme="purple">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'square',
							'colorScheme' => 'purple'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			36 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Flat Black',
				'base' => 'flat-black',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="flat" data-cscheme="black">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'flat',
							'colorScheme' => 'black'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			37 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Flat Red',
				'base' => 'flat-red',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="flat" data-cscheme="red">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'flat',
							'colorScheme' => 'red'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			38 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Flat Green',
				'base' => 'flat-green',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="flat" data-cscheme="green">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'flat',
							'colorScheme' => 'green'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			39 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Flat Blue',
				'base' => 'flat-blue',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="flat" data-cscheme="blue">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'flat',
							'colorScheme' => 'blue'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			40 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Flat Aero',
				'base' => 'flat-aero',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="flat" data-cscheme="aero">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'flat',
							'colorScheme' => 'aero'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			41 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Flat Grey',
				'base' => 'flat-grey',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="flat" data-cscheme="grey">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'flat',
							'colorScheme' => 'grey'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			42 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Flat Orange',
				'base' => 'flat-orange',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="flat" data-cscheme="orange">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'flat',
							'colorScheme' => 'orange'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			43 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Flat Yellow',
				'base' => 'flat-yellow',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="flat" data-cscheme="yellow">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'flat',
							'colorScheme' => 'yellow'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			44 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Flat Pink',
				'base' => 'flat-pink',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="flat" data-cscheme="pink">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'flat',
							'colorScheme' => 'pink'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			45 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Flat Purple',
				'base' => 'flat-purple',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="flat" data-cscheme="purple">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'flat',
							'colorScheme' => 'purple'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			46 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Minimal Black',
				'base' => 'minimal-black',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="minimal" data-cscheme="black">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'minimal',
							'colorScheme' => 'black'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			47 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Minimal Red',
				'base' => 'minimal-red',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="minimal" data-cscheme="red">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'minimal',
							'colorScheme' => 'red'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			48 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Minimal Green',
				'base' => 'minimal-green',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="minimal" data-cscheme="green">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'minimal',
							'colorScheme' => 'green'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			49 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Minimal Blue',
				'base' => 'minimal-blue',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="minimal" data-cscheme="blue">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'minimal',
							'colorScheme' => 'blue'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			50 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Minimal Aero',
				'base' => 'minimal-aero',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="minimal" data-cscheme="aero">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'minimal',
							'colorScheme' => 'aero'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			51 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Minimal Grey',
				'base' => 'minimal-grey',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="minimal" data-cscheme="grey">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'minimal',
							'colorScheme' => 'grey'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			52 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Minimal Orange',
				'base' => 'minimal-orange',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="minimal" data-cscheme="orange">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'minimal',
							'colorScheme' => 'orange'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			53 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Minimal Yellow',
				'base' => 'minimal-yellow',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="minimal" data-cscheme="yellow">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'minimal',
							'colorScheme' => 'yellow'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			54 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Minimal Pink',
				'base' => 'minimal-pink',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="minimal" data-cscheme="pink">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'minimal',
							'colorScheme' => 'pink'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			),
			55 => array(
				'template_base' => 'basic-pretty',
				'name' => 'Minimal Purple',
				'base' => 'minimal-purple',
				'description' => '',
				'html_preview'=> '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="minimal" data-cscheme="purple">
								<div class="row">
									<div class="col-md-12">
										<div class="basic-inner">
											<form class="basic-form">
												<div class="basic-elements">
													<div class="basic-element basic-question basic-question-text-vertical">
														<div class="basic-question-title">
															<h5>Poll Question</h5>
														</div><!-- end basic-question-title -->
														<ul class="basic-answers">
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio" checked>    
																	<label class="basic-text">Answer 1</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 2</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 3</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 4</label>
																</div>
															</li>
															<li class="basic-answer">
																<div class="basic-answer-content basic-text-vertical">
																	<input type="radio">    
																	<label class="basic-text">Answer 5</label>
																</div>
															</li>
														</ul><!-- end basic-answers -->
													</div><!-- end basic-element basic-question -->
													<div class="clearfix"></div>
												</div><!-- end basic-elements -->
												<div class="basic-vote">
													<a href="#" class="button basic-vote-button">Vote</a>
												</div><!-- end basic-vote -->
											</form><!-- end basic-form" -->
										</div><!-- end basic-inner -->
									</div><!-- end col-md-12 -->
								</div><!-- end row -->
							</div><!-- end basic-yop-poll-container -->',
				'skin_type' => 'predefined',
				'author' => 0,
				'meta_data' => serialize(
					array(
						'poll' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000000',
							'borderRadius' => '5px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '10px'
						),
						'questions' => array(
							'textColor' => '#000',
							'textSize' => '16px',
							'textWeight' => 'normal',
							'textAlign' => 'center'
						),
						'answers' => array(
							'paddingLeftRight' => '0px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal',
							'skin' => 'minimal',
							'colorScheme' => 'purple'
						),
						'buttons' => array(
							'backgroundColor' => '#ffffff',
							'borderSize' => '1px',
							'borderColor' => '#000',
							'borderRadius' => '0px',
							'paddingLeftRight' => '10px',
							'paddingTopBottom' => '5px',
							'textColor' => '#000',
							'textSize' => '14px',
							'textWeight' => 'normal'
						),
						'captcha' => array(),
						'errors' => array(
							'borderLeftColorForSuccess' => '#008000',
							'borderLeftColorForError' => '#ff0000',
							'borderLeftSize' => '10px',
							'paddingTopBottom' => '0px',
							'textColor' => '#000000',
							'textSize' => '14',
							'textWeight' => 'normal'
						),
						'custom' => array(
							'css' => '.basic-yop-poll-container[data-uid] .basic-vote {
									text-align: center;
								}'
						)
					)
				),
				'added_date' => current_time( 'mysql' )
			)
		);
		foreach ( $skins as $skin ) {
			if ( false === YOP_Poll_Skins::skin_already_exists( $skin['template_base'], $skin['base'], $available_skins ) ) {
				$GLOBALS['wpdb']->insert( $table, $skin );
			}
		}
	}
	public function update_table_templates() {
		$templates_preview = array(
			'basic' => '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px;padding-bottom: 14px;">
					<div class="row">
						<div class="col-md-12">
							<div class="basic-inner">
								<form class="basic-form">
									<div class="basic-elements">
										<div class="basic-element basic-question basic-question-text-vertical">
											<div class="basic-question-title">
												<h5>Poll Question</h5>
											</div><!-- end basic-question-title -->
											<ul class="basic-answers">
												<li class="basic-answer">
													<div class="basic-answer-content basic-text-vertical">
														<input type="radio" checked>    
														<label class="basic-text">Answer 1</label>
													</div>
												</li>
												<li class="basic-answer">
													<div class="basic-answer-content basic-text-vertical">
														<input type="radio">    
														<label class="basic-text">Answer 2</label>
													</div>
												</li>
												<li class="basic-answer">
													<div class="basic-answer-content basic-text-vertical">
														<input type="radio">    
														<label class="basic-text">Answer 3</label>
													</div>
												</li>
												<li class="basic-answer">
													<div class="basic-answer-content basic-text-vertical">
														<input type="radio">    
														<label class="basic-text">Answer 4</label>
													</div>
												</li>
												<li class="basic-answer">
													<div class="basic-answer-content basic-text-vertical">
														<input type="radio">    
														<label class="basic-text">Answer 5</label>
													</div>
												</li>
											</ul><!-- end basic-answers -->
										</div><!-- end basic-element basic-question -->
										<div class="clearfix"></div>
									</div><!-- end basic-elements -->
									<div class="basic-vote">
										<a href="#" class="button basic-vote-button">Vote</a>
									</div><!-- end basic-vote -->
								</form><!-- end basic-form" -->
							</div><!-- end basic-inner -->
						</div><!-- end col-md-12 -->
					</div><!-- end row -->
				</div><!-- end basic-yop-poll-container -->',
			'basic-pretty' => '<div class="basic-yop-poll-container white-v2" style="padding-top: 14px; padding-bottom: 14px;" data-temp="basic-pretty" data-skin="square" data-cscheme="red">
					<div class="row">
						<div class="col-md-12">
							<div class="basic-inner">
								<form class="basic-form">
									<div class="basic-elements">
										<div class="basic-element basic-question basic-question-text-vertical">
											<div class="basic-question-title">
												<h5>Poll Question</h5>
											</div><!-- end basic-question-title -->
											<ul class="basic-answers">
												<li class="basic-answer">
													<div class="basic-answer-content basic-text-vertical">
														<input type="radio" checked>    
														<label class="basic-text">Answer 1</label>
													</div>
												</li>
												<li class="basic-answer">
													<div class="basic-answer-content basic-text-vertical">
														<input type="radio">    
														<label class="basic-text">Answer 2</label>
													</div>
												</li>
												<li class="basic-answer">
													<div class="basic-answer-content basic-text-vertical">
														<input type="radio">    
														<label class="basic-text">Answer 3</label>
													</div>
												</li>
												<li class="basic-answer">
													<div class="basic-answer-content basic-text-vertical">
														<input type="radio">    
														<label class="basic-text">Answer 4</label>
													</div>
												</li>
												<li class="basic-answer">
													<div class="basic-answer-content basic-text-vertical">
														<input type="radio">    
														<label class="basic-text">Answer 5</label>
													</div>
												</li>
											</ul><!-- end basic-answers -->
										</div><!-- end basic-element basic-question -->
										<div class="clearfix"></div>
									</div><!-- end basic-elements -->
									<div class="basic-vote">
										<a href="#" class="button basic-vote-button">Vote</a>
									</div><!-- end basic-vote -->
								</form><!-- end basic-form" -->
							</div><!-- end basic-inner -->
						</div><!-- end col-md-12 -->
					</div><!-- end row -->
				</div><!-- end basic-yop-poll-container -->'
		);
		if (  ( true === $this->check_if_column_exists( $GLOBALS['wpdb']->yop_poll_templates, 'image_preview' ) ) &&
			( true === $this->check_if_column_exists( $GLOBALS['wpdb']->yop_poll_templates, 'html_vertical' ) ) &&
			( true === $this->check_if_column_exists( $GLOBALS['wpdb']->yop_poll_templates, 'html_horizontal' ) ) &&
			( true === $this->check_if_column_exists( $GLOBALS['wpdb']->yop_poll_templates, 'html_columns' ) )
		 ) {
			$alter_query = "ALTER TABLE `{$GLOBALS['wpdb']->yop_poll_templates}` DROP `image_preview`, DROP `html_vertical`, DROP `html_horizontal`, DROP `html_columns`";
			$GLOBALS['wpdb']->query( $alter_query );
		}
		$update_query = "UPDATE `{$GLOBALS['wpdb']->yop_poll_templates}` SET `html_preview` = '" . $templates_preview['basic'] . "' WHERE `base` = 'basic'";
		$GLOBALS['wpdb']->query( $update_query );
		$update_query = "UPDATE `{$GLOBALS['wpdb']->yop_poll_templates}` SET `html_preview` = '" . $templates_preview['basic-pretty'] . "' WHERE `base` = 'basic-pretty'";
		$GLOBALS['wpdb']->query( $update_query );
	}
	public function update_table_polls_add_skin_field() {
		if ( false === $this->check_if_column_exists( $GLOBALS['wpdb']->yop_poll_polls, 'skin_base' ) ) {
			$alter_query = "ALTER TABLE `{$GLOBALS['wpdb']->yop_poll_polls}` ADD `skin_base` varchar(255) AFTER `template_base`";
			$GLOBALS['wpdb']->query( $alter_query );
		}
		$update_query = "UPDATE `{$GLOBALS['wpdb']->yop_poll_polls}` SET `skin_base` = 'orange-def'";
		$GLOBALS['wpdb']->query( $update_query );
	}
	private function check_if_column_exists( $table_name, $column_name ) {
		if ( 0 == $GLOBALS['wpdb']->get_var( $GLOBALS['wpdb']->prepare(
				"SELECT count(1) FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA` = %s AND `TABLE_NAME` = %s AND `COLUMN_NAME` = %s ",
				DB_NAME, $table_name, $column_name
			) ) ) {
			return false;
		}
		return true;
	}
	public function delete_tables() {
		$query = 'DROP TABLE IF EXISTS ' . $GLOBALS['wpdb']->yop_poll_polls;
		$GLOBALS['wpdb']->query( $query );
		$query = 'DROP TABLE IF EXISTS ' . $GLOBALS['wpdb']->yop_poll_elements;
		$GLOBALS['wpdb']->query( $query );
		$query = 'DROP TABLE IF EXISTS ' . $GLOBALS['wpdb']->yop_poll_subelements;
		$GLOBALS['wpdb']->query( $query );
		$query = 'DROP TABLE IF EXISTS ' . $GLOBALS['wpdb']->yop_poll_bans;
		$GLOBALS['wpdb']->query( $query );
		$query = 'DROP TABLE IF EXISTS ' . $GLOBALS['wpdb']->yop_poll_votes;
		$GLOBALS['wpdb']->query( $query );
		$query = 'DROP TABLE IF EXISTS ' . $GLOBALS['wpdb']->yop_poll_logs;
		$GLOBALS['wpdb']->query( $query );
		$query = 'DROP TABLE IF EXISTS ' . $GLOBALS['wpdb']->yop_poll_templates;
		$GLOBALS['wpdb']->query( $query );
		$query = 'DROP TABLE IF EXISTS ' . $GLOBALS['wpdb']->yop_poll_skins;
		$GLOBALS['wpdb']->query( $query );
		$query = 'DROP TABLE IF EXISTS ' . $GLOBALS['wpdb']->yop_poll_other_answers;
		$GLOBALS['wpdb']->query( $query );
	}
}
