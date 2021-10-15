<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://ays-pro.com/
 * @since      1.0.0
 *
 * @package    Poll_Maker_Ays
 * @subpackage Poll_Maker_Ays/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Poll_Maker_Ays
 * @subpackage Poll_Maker_Ays/public
 * @author     Poll Maker Team <info@ays-pro.com>
 */
class Poll_Maker_Ays_Public {

    /**
     * The settings of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Poll_Maker_Settings_Actions object $settings The current settings of this plugin.
     */
    protected $settings;
	protected $fields_placeholders;
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;
	private static $p;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;
	private static $v;

	/**
	 * The instance of this plugin public class.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Poll_Maker_Ays_Public object.
	 */
	private static $instance = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		self::$p           = $plugin_name;
		self::$v           = $version;

		add_shortcode('ays_poll', array($this, 'ays_poll_generate_shortcode'));
        $this->settings = new Poll_Maker_Settings_Actions($this->plugin_name);
        add_shortcode('ays_poll_all', array($this, 'ays_poll_all_generate_shortcode'));
        add_shortcode('ayspoll_results', array($this, 'ays_poll_results_generate_shortcode'));
		add_shortcode('ays_display_polls', array($this, 'ays_generate_display_polls_method'));
    }
     
	/**
	 * Get instance of this class. Singleton pattern.
	 *
	 * @since    1.4.0
	 */
	public static function get_instance() {
		if (self::$instance == null) {
			self::$instance = new Poll_Maker_Ays_Public(self::$p, self::$v);
		}

		return self::$instance;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Poll_Maker_Ays_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Poll_Maker_Ays_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		wp_enqueue_style( 'ays_poll_font_awesome', plugin_dir_url(__FILE__) . '/css/poll-maker-ays-public-fonts.css', array(), $this->version, 'all');

	}

	public function enqueue_styles_early(){
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/poll-maker-ays-public.css', array(), $this->version, 'all');
		$ays_is_elementor = $this->ays_is_elementor();
		if ( $ays_is_elementor ) {
			wp_enqueue_style( 'ays_poll_font_awesome', plugin_dir_url(__FILE__) . '/css/poll-maker-ays-public-fonts.css', array(), $this->version, 'all');
	    }
	}
	
	public function ays_is_elementor(){
		if ( defined( 'ELEMENTOR_PATH' ) && class_exists( 'Elementor\Widget_Base' ) ) {
            if ( class_exists( 'Elementor\Plugin' ) ) {
				if ( is_callable( 'Elementor\Plugin', 'instance' ) ) {
					$elementor = Elementor\Plugin::instance();
					if ( isset( $elementor->preview ) ) {
						return \Elementor\Plugin::$instance->preview->is_preview_mode();
					}
				}
			}
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Poll_Maker_Ays_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Poll_Maker_Ays_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		//wp_enqueue_script($this->plugin_name . '-font-awesome', "https://use.fontawesome.com/releases/v5.5.0/js/all.js", array('jquery'), $this->version, true);

		wp_enqueue_script("jquery");
		wp_enqueue_script("jquery-effects-core");
		wp_enqueue_script($this->plugin_name . '-ajax-public', plugin_dir_url(__FILE__) . 'js/poll-maker-public-ajax.js', array('jquery'), $this->version, true);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/poll-maker-ays-public.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name.'-category', plugin_dir_url(__FILE__) . 'js/poll-maker-public-category.js', array('jquery'), $this->version, false);
		wp_enqueue_script( $this->plugin_name . '-autosize', plugin_dir_url(__FILE__) . 'js/poll-maker-autosize.js', array( 'jquery' ), $this->version, false );
		wp_localize_script($this->plugin_name . '-ajax-public', 'poll_maker_ajax_public',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'alreadyVoted'  => __( "You have already voted" , $this->plugin_name ),
				'day'           => __( 'day', $this->plugin_name ),
	            'days'          => __( 'days', $this->plugin_name ),
	            'hour'          => __( 'hour', $this->plugin_name ),
	            'hours'         => __( 'hours', $this->plugin_name ),
	            'minute'        => __( 'minute', $this->plugin_name ),
	            'minutes'       => __( 'minutes', $this->plugin_name ),
	            'second'        => __( 'second', $this->plugin_name ),
	            'seconds'       => __( 'seconds', $this->plugin_name ),
	            'thank_message'       => __( 'Your answer has been successfully sent to the admin. Please wait for the approval.', $this->plugin_name ),
			)
		);
	}

	public function show_chart_js() {
		wp_enqueue_script( $this->plugin_name . '-charts-google', plugin_dir_url(__FILE__) . 'js/google-chart.js', array('jquery'), $this->version, true);
	}
    // public function show_column_chart_js() {
    //     // wp_enqueue_script( $this->plugin_name . '-column-chart', 'https://www.gstatic.com/charts/loader.js', array('jquery'), $this->version, true);
    //     wp_enqueue_script( $this->plugin_name . '-column-chart', plugin_dir_url(__FILE__) . 'js/poll-maker-chart-loader.js', array('jquery'), $this->version, true);
    // }	

	public function ays_poll_results_generate_shortcode($attr) {
		ob_start();

		$this->enqueue_styles();
        $this->enqueue_scripts();

		global $wpdb;
		$id = absint(intval($attr['id']));
		$answ_table = esc_sql($wpdb->prefix."ayspoll_answers");
		$polls_table = esc_sql($wpdb->prefix."ayspoll_polls");
		$ans_sql  = "SELECT * FROM ".$answ_table." WHERE poll_id =%d ORDER BY votes DESC";
		$poll_answers = $wpdb->get_results(
			   	  	$wpdb->prepare( $ans_sql, $id),
			   	  	'ARRAY_A'
				  );

		$poll_sql  = "SELECT * FROM ".$polls_table." WHERE id =%d";
		$polls = $wpdb->get_row(
			   	  	$wpdb->prepare( $poll_sql, $id),
			   	  	'ARRAY_A'
				  );

		$settings_options = $this->settings->ays_get_setting('options');
        $result_options_res = ($settings_options === false) ? json_encode(array()) : $settings_options;
        $result_option_res = json_decode($result_options_res, true);

		$chart_style = false;
		if (isset( $result_option_res['show_result_view']) && ( $result_option_res['show_result_view'] == 'pie_chart' ||  $result_option_res['show_result_view'] == 'column_chart' )) {
			$this->show_chart_js();
			// $this->show_column_chart_js();
			$chart_style = true;
		}

		if ($polls == null) {
			$content = '<p style="text-align:center;">No ratings yet</p>';
		}else{
			$show_title = isset($polls['show_title']) && $polls['show_title'] == 0 ? false : true;
			$poll_title = isset($polls['title']) ? $polls['title'] : '';
			$votes_count = $this->get_poll_results_count_by_id($id);
			$poll = $this->get_poll_by_id($id);
			$polls_options = $poll['styles'];
	        if (intval($votes_count['res_count']) > 0) {
				$one_percent = 100/intval($votes_count['res_count']);
	        }else{
	        	$one_percent = 1;
	        }
			$poll_border_color_res = isset($polls_options['border_color']) && $polls_options['border_color'] != "" ? esc_attr($polls_options['border_color']) : '';

			// Poll question font size
			$poll_question_font_size_pc     = isset($polls_options['poll_question_size_pc']) && $polls_options['poll_question_size_pc'] != "" ? esc_attr($polls_options['poll_question_size_pc']) : "16"; 
			$poll_question_font_size_mobile = isset($polls_options['poll_question_size_mobile']) && $polls_options['poll_question_size_mobile'] != "" ? esc_attr($polls_options['poll_question_size_mobile']) : "16";
			$content = "";
			$content .= "<style>
				.ays-poll-main .ays_question p{
					font-size: ".$poll_question_font_size_pc."px;
				}
				
				@media only screen and (max-width: 768px){
					.ays-poll-main .ays_question p{
						font-size: ".$poll_question_font_size_mobile."px;
					}
				}
			</style>";
			$content .= '<div class="ays-poll-main" style="margin-bottom: 1rem; border:2px solid '.$poll_border_color_res.'; background-color: '.$polls_options['bg_color'].';color: '.$polls_options['main_color'].';" id="ays-poll-container-'.htmlentities($id).'">';

			if($show_title){
				$content .= '<div class="apm-title-box">
								<h5 style="text-align:center;">'.stripslashes($poll_title).'</h5>
							</div>';
			}
							
			$content .= '<div class="ays_question">
							<p>'.stripslashes($polls['question']).'</p>
						 </div>';
			if ($votes_count['res_count'] == 0) {
				$content .= '<p style="text-align:center; margin: 0;">No ratings yet</p>';	 	
			}
			if($chart_style){
				$content .= '<div class="results-apm" id="pollAllResultId'.$id.'" style="height:400px;display: flex;justify-content: center;">';
				$aysChartData = array(['','']);
				foreach ($poll_answers as $key => $c_value) {
					$all_votes_chart = 0;
					$real_votes = isset($c_value['votes']) ? intval($c_value['votes']) : 0;
					$all_votes_chart += $real_votes;
					if(isset($poll["type"]) && $poll["type"] == "voting"){
						$c_value['answer'] = $c_value['answer'] == 1 ? "Like" : "Dislike";
					}
					$arr = [$c_value['answer'] , $all_votes_chart];
						array_push($aysChartData,$arr);
				}
				$show_result_view = isset($result_option_res['show_result_view']) && $result_option_res['show_result_view'] != '' ? $result_option_res['show_result_view'] : 'standart';
			}
			else{
				$content .= '<div class="results-apm">';
			}

			$poll_answers_count = count($poll_answers);
			$chart_font_size = "fontSize:12";
			if(isset($poll["type"]) && ($poll["type"] == "voting" || $poll["type"] == "rating" )){
				$chart_font_size = "fontSize:18";
			}

			$title_bg_color = isset($polls_options['main_color']) ? $polls_options['main_color'] : '#fff';
			if($chart_style && $show_result_view == 'pie_chart'){
				$content .=  '
				<script> 
					(function ($) {
						"use strict";    
						$(document).ready(function () {
						
						var aysChartData = '. json_encode($aysChartData).';
							google.charts.load("current", {packages:["corechart"]});
							google.charts.setOnLoadCallback(drawChart);
	
							function drawChart() {
								var dataGoogle = google.visualization.arrayToDataTable(aysChartData);
	
								var options = {
									legend: {position: "right",},
									pieSliceText: "label",
									chartArea:{"left":"100","width":"100%"},
									width: 500,
									height: 400,
									'.$chart_font_size.',
									legend: {textStyle :{color:"'.$title_bg_color.'"}},
									backgroundColor: { fill:"transparent" },
								};
	
								var chart = new google.visualization.PieChart(pollAllResultId'.$id.');
								chart.draw(dataGoogle, options);
							}
						});
					})(jQuery);
				</script>';
			}
			elseif($chart_style && $show_result_view  == 'column_chart'){
				$content .=  '
				<script> 
					(function ($) {
						"use strict";
						$(document).ready(function () {
							var aysChartData = '. json_encode($aysChartData).';
							google.charts.load("current", {"packages":["bar"]});
							google.charts.setOnLoadCallback(drawStuff);
	
							function drawStuff() {
								var dataColumnChart = new google.visualization.arrayToDataTable(aysChartData);
	
								var options = {
									width: 500,
									maxWidth: "100%",
									height: 400,
									legend: { position: "none" },
									axes: {
									x: {
									  0: { side: "bottom"} 
									}
								  },
								  bar: { groupWidth: "90%" }
								};
	
								var chart = new google.charts.Bar(pollAllResultId'.$id.');
							
								chart.draw(dataColumnChart, google.charts.Bar.convertOptions(options));
							}
						});
					})(jQuery);
				</script>';
			}
			else{
				foreach ($poll_answers as $ans_key => $ans_val) {
					$percent = round($one_percent*intval($ans_val['votes']));
					if ($percent == 0) {
						$perc_cont = '';
					}else{
						$perc_cont = $percent.' %';
					}
					switch ($polls['type']) {
						case 'choosing':
							$content .= '<div class="answer-title flex-apm">
											<span class="answer-text">'.stripslashes($ans_val['answer']).'</span>
											<span class="answer-votes">'.$ans_val['votes'].'</span>
										</div>
										<div class="answer-percent" style="width: '.$percent.'%; background-color: '.$polls_options['main_color'].'; color: '.$polls_options['bg_color'].';">'.$perc_cont.'</div>';
							break;

						case 'rating':
							switch ($polls['view_type']) {
								case 'star':
									$star_type  = '';
									for ($i=0; $i < intval($ans_val['answer']); $i++) { 
										$star_type .= '<i class="ays_poll_far ays_poll_fa-star far"></i>';
									}
									$content .= '<div class="answer-title flex-apm">
													<span class="answer-text">'.$star_type.'</span>
													<span class="answer-votes">'.$ans_val['votes'].'</span>
												</div>
												<div class="answer-percent" style="width: '.$percent.'%; background-color: '.$polls_options['main_color'].'; color: '.$polls_options['bg_color'].';">'.$perc_cont.'</div>';
									break;
								
								case 'emoji':
									$emojy_type  = '';
									if ($poll_answers_count == 3) {
										switch (intval($ans_val['answer'])) {
											case 1:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-frown far"></i>';
												break;
											case 2:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-meh far"></i>';
												break;
											case 3:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-smile far"></i>';
												break;
											default:
												break;
										}
									}else{
										switch (intval($ans_val['answer'])) {
											case 1:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-tired far"></i>';
												break;
											case 2:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-frown far"></i>';
												break;
											case 3:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-meh far"></i>';
												break;
											case 4:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-smile far"></i>';
												break;
											case 5:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-dizzy far"></i>';
												break;
											default:
												break;
										}
									}

									$content .= '<div class="answer-title flex-apm">
													<span class="answer-text">'.$emojy_type.'</span>
													<span class="answer-votes">'.$ans_val['votes'].'</span>
												</div>
												<div class="answer-percent" style="width: '.$percent.'%; background-color: '.$polls_options['main_color'].'; color: '.$polls_options['bg_color'].';">'.$perc_cont.'</div>';

									break;
								default:										
									break;
							}
							break;
						case 'voting':
							switch ($polls['view_type']) {
								case 'hand':
									$hand_type  = '';
									if (intval($ans_val['answer'] == 1)) {
										$hand_type = '<i class="ays_poll_far ays_poll_fa-thumbs-up far"></i>';
									}else{
										$hand_type = '<i class="ays_poll_far ays_poll_fa-thumbs-down far"></i>';
									}
									$content .= '<div class="answer-title flex-apm">
													<span class="answer-text">'.$hand_type.'</span>
													<span class="answer-votes">'.$ans_val['votes'].'</span>
												</div>
												<div class="answer-percent" style="width: '.$percent.'%; background-color: '.$polls_options['main_color'].'; color: '.$polls_options['bg_color'].';">'.$perc_cont.'</div>';
									break;
								case 'emoji':
									$emojy_type  = '';
									if (intval($ans_val['answer'] == 1)) { 
										$emojy_type = '<i class="ays_poll_far ays_poll_fa-smile far"></i>';
									}else{
										$emojy_type = '<i class="ays_poll_far ays_poll_fa-frown far"></i>';
									}
									$content .= '<div class="answer-title flex-apm">
													<span class="answer-text">'.$emojy_type.'</span>
													<span class="answer-votes">'.$ans_val['votes'].'</span>
												</div>
												<div class="answer-percent" style="width: '.$percent.'%; background-color: '.$polls_options['main_color'].'; color: '.$polls_options['bg_color'].';">'.$perc_cont.'</div>';

									break;
								default:										
									break;
							}
							break;
						default:										
							break;
					}
					
				}
			}

			$content .= '</div>
						</div>';
		}

        echo $content;

		return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
	}

	public function ays_poll_all_generate_shortcode($attr) {
		ob_start();
		global $wpdb;

		$check_published = "published";
		if(is_array($attr) && !empty($attr)){
			$check_published = isset($attr['display']) && $attr['display'] == "all" ? "all" : "published";
		}

		$poll_table = esc_sql($wpdb->prefix."ayspoll_polls");
		$sql  = "SELECT id FROM ".$poll_table;
		$poll = $wpdb->get_results($sql, 'ARRAY_A');
  
		$this->enqueue_styles();
        $this->enqueue_scripts();
		$checker = array();
		foreach ($poll as $poll_id) {
			$current_id = isset($poll_id['id']) ? $poll_id['id'] : "";
			$check_poll = $this->check_shedule_expired_poll( $current_id );
			$checker[] = $check_poll;
			if($check_published == 'published'){
				if ($check_poll) {
					$this->show_poll($poll_id);
				}
			}
			elseif($check_published == 'all'){
				$this->show_poll($poll_id);
			}
		}

		if(array_sum($checker) == 0){
            $poll_settings   = $this->settings;
            $general_options = ($poll_settings->ays_get_setting('options') !== false) ? json_decode($poll_settings->ays_get_setting('options') , true) : array();
            $message = '';
            if(!empty($general_options)){
                $message = (isset($general_options['all_shortcode_message']) && $general_options['all_shortcode_message'] != '') ? esc_html($general_options['all_shortcode_message']) : '';
            }
            echo '<div class="ays_poll_all_res_none_message">'.$message.'</div>';
        }

		return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
	}

	public function ays_poll_generate_shortcode( $attr ) {
		ob_start();

		$this->enqueue_styles();
        $this->enqueue_scripts();

		$this->show_poll($attr);

		return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
	}

	public function show_poll( $attr ) {
		if (isset($attr['id'])) {
			return $this->ays_poll_generate_html($attr['id']);
		} elseif (isset($attr['cat_id'])) {
			return $this->ays_poll_category_generate_html($attr);
		}
	}

	public function ays_poll_generate_html( $poll_id, $echo = true, $width = -1 ) {

		if (!isset($poll_id) || null == $poll_id) {
			return "";
		}

		$id   = absint($poll_id);
		$poll = $this->get_poll_by_id($id);
		if (empty($poll)) {
			return "";
		}

		$this_poll_id = uniqid("ays-poll-id-");
		$options      = $poll['styles'];
		$poll_settings = $this->settings;
		$general_options = ($poll_settings->ays_get_setting('options') === false) ? json_encode(array()) : json_decode($poll_settings->ays_get_setting('options'), true);

		if (isset($options['published']) && intval($options['published']) === 0) {
			return "";
		}

		$info_form = !empty($options['info_form']) && !empty($options['fields']);
		$info_form_title = !empty($options['info_form_title']) ? $options['info_form_title'] : "<h5>" . __("Please fill out the form:", $this->plugin_name) . "</h5>";
		$fields          = !empty($options['fields']) ? explode(",", $options['fields']) : array();
		$required_fields = !empty($options['required_fields']) ? explode(",", $options['required_fields']) : array();

		$is_expired    = false;
		$is_start_soon = false;
		$startDate = '';
		$endDate = '';
		$current_time = strtotime(current_time( "Y:m:d H:i:s" ));
		$poll_check_exp_cont = isset($options['dont_show_poll_cont']) && $options['dont_show_poll_cont'] == 'on' ? true : false;
		if (isset($options['active_date_check']) && !empty($options['active_date_check'])) {
			if (isset($options['activeInterval']) && isset($options['deactiveInterval'])) {
				if (isset($options['activeIntervalSec']) && !empty($options['activeIntervalSec'])) {
					$startDate = strtotime($options['activeInterval']." ".$options['activeIntervalSec']);
					$startDate_atr = $startDate - $current_time;			
				}
				else{
					$startDate = strtotime($options['activeInterval']);
					$startDate_atr = $startDate - $current_time;
				}

				if (isset($options['deactiveIntervalSec']) && !empty($options['deactiveIntervalSec'])) {
					$endDate   = strtotime($options['deactiveInterval']." ".$options['deactiveIntervalSec']);
					$endDate_atr = $endDate - $current_time;
				}
				else{
					$endDate   = strtotime($options['deactiveInterval']);
					$endDate_atr = $endDate - $current_time;
				}

				if ($startDate > $current_time) {
					$is_start_soon = true;
				}
								
				if ($startDate > $current_time || $endDate < $current_time) {
					$is_expired = true;
				}
			}
		}

		$poll_directions = isset($options['poll_direction']) && $options['poll_direction'] != '' ? $options['poll_direction'] : 'ltr';
		
		switch ($poll_directions) {
			case 'ltr':
				$poll_direction = 'ltr';
				break;

			case 'center':
				$poll_direction = 'center';
				break;

			case 'rtl':
				$poll_direction = 'rtl';
				break;
			default:
				$poll_direction = 'ltr';
				break;
		}

		$load_effect  = isset($options['load_effect']) ? $options['load_effect'] : "opacity";
		$load_gif     = isset($options['load_effect']) && $options['load_effect'] == 'load_gif' && isset($options['load_gif']) ? $options['load_gif'] : "";
		$poll_load_message_data = "";
		if(isset($options['load_effect']) && $options['load_effect'] == "message"){
			$poll_load_message = isset($options['effect_message']) && $options['effect_message'] != "" ?  esc_attr($options['effect_message']) : "";
			$poll_load_message_data = 'data-load-message="'.$poll_load_message.'"';
		}
		$result_sort = isset($options['result_sort_type']) ? $options['result_sort_type'] : "none";
		if (isset($options['show_social']) && $options['show_social'] == 1) {
			$show_social = true;
		} else {
			$show_social = false;
		}

		$without_vote = isset($options['enable_vote_btn']) && $options['enable_vote_btn'] == 0 ? 'apm-answers-without-submit' : "";
		$with_vote = isset($options['enable_vote_btn']) && $options['enable_vote_btn'] != 0 ? true : false;

		// Allow custom answer
		$poll_allow_answer = (isset($options['poll_allow_answer']) && $options['poll_allow_answer'] == "on") ? "checked" : "";
		// Require admin approval
		$poll_allow_answer_require = (isset($options['poll_allow_answer_require']) && $options['poll_allow_answer_require'] == "on") ? "checked" : "";

		if (isset($options['hide_results']) && $options['hide_results'] == 1) {
			$hide_results = "1";
			if (!empty($options['hide_results_text'])) {
				$hide_results_text = wpautop($options['hide_results_text']);
			} else {
				$hide_results_text = __("Thanks for your answer", $this->plugin_name);
			}
		} else {
			$hide_results      = "0";
			$hide_results_text = "";
		}

		if (isset( $options['redirect_after_submit'] ) && $options['redirect_after_submit'] == 1) {
			$redirect_after_vote_url = '';
			$redirect_users          = 0;
			$check_delay = isset($options['poll_enable_answer_redirect_delay']) && $options['poll_enable_answer_redirect_delay'] == "on" ? true : false; 
			$redirect_delay = 0;
			if($check_delay){
				$redirect_delay = isset($options['poll_every_answer_redirect_delay']) && $options['poll_every_answer_redirect_delay'] != "" ? esc_attr($options['poll_every_answer_redirect_delay']) : 0;
			}
			$redirect_url_href       = '';
			$redirect_url_checked    = 1;
			$redirect_after_vote     = "<p class='redirectionAfterVote'>" . __("You will be redirected ".($redirect_delay <= 0 ? "" : " after <span>".$redirect_delay."</span> seconds "), $this->plugin_name) . "</p>";
		}elseif (isset($options['redirect_users']) && $options['redirect_users'] != 0 && !empty($options['redirect_after_vote_url'])) {
			$redirect_after_vote_url = stripslashes($options['redirect_after_vote_url']);
			$redirect_url_href       = '';
			$redirect_users          = $options['redirect_users'];
			$redirect_delay          = $options['redirect_after_vote_delay'];
			$redirect_url_checked    = 0;
			$redirect_after_vote     = "<p class='redirectionAfterVote'>" . __("You will be redirected ".($redirect_delay <= 0 ? "" : " after <span>".$redirect_delay."</span> seconds "), $this->plugin_name) . "</p>";
		} else {
			$redirect_after_vote_url = '';
			$redirect_url_href       = '';
			$redirect_users          = 0;
			$redirect_delay          = 0;
			$redirect_url_checked    = 0;
			$redirect_after_vote     = "";
		}

		$limit_users = 0;
		$limitusers = isset($options['limit_users']) ? intval($options['limit_users']) : 0;
		$load_poll   = false;
		$emoji       = array(
			"<i class='ays_poll_far ays_poll_fa-dizzy'></i>",
			"<i class='ays_poll_far ays_poll_fa-smile'></i>",
			"<i class='ays_poll_far ays_poll_fa-meh'></i>",
			"<i class='ays_poll_far ays_poll_fa-frown'></i>",
			"<i class='ays_poll_far ays_poll_fa-tired'></i>",
		);

		$bg_color        	= $options['bg_color'];
		$bg_image        	= isset($options['bg_image']) ? $options['bg_image'] : '';
		$main_color      	= $options['main_color'];
		$text_color      	= $options['text_color'];
		$icon_color      	= $options['icon_color'];
		$answer_bg_color 	= isset($options['answer_bg_color']) ? $options['answer_bg_color'] : 'transparent';
		$answer_border_side = isset($options['answer_border_side']) ? $options['answer_border_side'] : 'all_sides';
		$title_bg_color  	= isset($options['title_bg_color']) ? $options['title_bg_color'] : 'transparent';

		$enable_pass_count 	= (isset($options['enable_pass_count']) && $options['enable_pass_count'] == 'on') ? true : false;

		$poll_theme = isset($poll['theme_id']) && absint($poll['theme_id']) == 3 ? 'ays-minimal-theme' : '';

		$answer_percent_color = $this->rgb2hex($main_color);
		if (isset($poll['theme_id']) && absint($poll['theme_id']) != '') {
			switch ( absint($poll['theme_id']) ) {
				case 3:
				case 7:
					$answer_percent_color = $this->rgb2hex($icon_color);
					break;
				default:
					$answer_percent_color = $this->rgb2hex($main_color);
					break;
			}
		}

		if ($enable_pass_count) {
			$poll_result_reports = $this->get_poll_results_count_by_id($id);
			$poll_result_reports = "<span class='ays_poll_passed_count'><i class='ays_poll_fa ays_poll_fa-users' aria-hidden='true'></i> " . $poll_result_reports['res_count'] . "</span>";
		} else {
			$poll_result_reports = '';
		}
		if ($width < 0) {
			$poll_width = $options['width'] > 0 ? $options['width'] . "px" : "100%";
		} elseif ($width == 0) {
			$poll_width = "100%";
		} else {
			$poll_width = $width . "px";
		}

		$ays_see_result_button  = (isset($options['see_res_btn_text']) && $options['see_res_btn_text'] != '') ? stripslashes($options['see_res_btn_text']) : 'See Results';

		if ($ays_see_result_button === 'See Results') {
            $ays_see_result_button_text =  __("See Results", $this->plugin_name);
        }else{
            $ays_see_result_button_text = $ays_see_result_button;
        }

		$show_res_btn_sch = (isset($options['show_result_btn_schedule']) && $options['show_result_btn_schedule'] == 1) ? true : false;
		$see_result_button = '';
		$see_result_button = wp_nonce_field('ays_finish_poll', 'ays_finish_poll') . "<div class='apm-button-box'>";
		if($poll['type'] != "text"){
			$see_result_button .= "<input type='button' class='btn ays-poll-btn {$poll['type']}-btn ays-see-res-button-show' data-form='$this_poll_id' value='" . $ays_see_result_button_text . "' data-seeRes='true' >";
		}
		$see_result_button .= '</div>';

		// Results bar in RGBA
		$result_in_rgba = (isset($options['result_in_rgba']) && $options['result_in_rgba'] == 'on' ) ? true : false;

		// Enable View more button
		$enable_view_more_button = (isset($options['enable_view_more_button']) && $options['enable_view_more_button'] == 'on' ) ? true : false;
		$poll_view_more_button_count = (isset($options['poll_view_more_button_count']) && $options['poll_view_more_button_count'] != '' ) ? absint(intval($options['poll_view_more_button_count'])) : 0;

		/* 
         * Poll container background gradient
         * 
         */
        
        // Checking exists background gradient option
        $options['enable_background_gradient'] = (!isset($options['enable_background_gradient'])) ? "off" : $options['enable_background_gradient'];
        
        if(isset($options['background_gradient_color_1']) && $options['background_gradient_color_1'] != ''){
            $background_gradient_color_1 = $options['background_gradient_color_1'];
        }else{
            $background_gradient_color_1 = "#103251";
        }

        if(isset($options['background_gradient_color_2']) && $options['background_gradient_color_2'] != ''){
            $background_gradient_color_2 = $options['background_gradient_color_2'];
        }else{
            $background_gradient_color_2 = "#607593";
        }

        if(isset($options['poll_gradient_direction']) && $options['poll_gradient_direction'] != ''){
            $poll_gradient_direction = $options['poll_gradient_direction'];
        }else{
            $poll_gradient_direction = 'vertical';
        }
        switch($poll_gradient_direction) {
            case "horizontal":
                $poll_gradient_direction = "to right";
                break;
            case "diagonal_left_to_right":
                $poll_gradient_direction = "to bottom right";
                break;
            case "diagonal_right_to_left":
                $poll_gradient_direction = "to bottom left";
                break;
            default:
                $poll_gradient_direction = "to bottom";
        }

        // Poll container background gradient enabled/disabled        
        if(isset($options['enable_background_gradient']) && $options['enable_background_gradient'] == "on"){
            $enable_background_gradient = true;
        }else{
            $enable_background_gradient = false;
        }

       	if( isset($bg_image) && $bg_image != false){
            $poll_styles =  "background-image: url('".$bg_image."');";
        }elseif($enable_background_gradient) {
            $poll_styles =  "background-image: linear-gradient($poll_gradient_direction, $background_gradient_color_1, $background_gradient_color_2);";
        }elseif (isset($bg_color)) {
        	$poll_styles = "background-color: ".$bg_color.";";
        }
        else{

        	$poll_styles = "background-image: unset;";
        }

        $options['enable_answer_style'] = isset($options['enable_answer_style']) ? $options['enable_answer_style'] : 'on';

        $answer_style = $options['enable_answer_style'] == 'on' ? true : false;

        $disable_answer_hover = isset($options['disable_answer_hover']) && $options['disable_answer_hover'] == 1 ? 'disable_hover' : 'ays_enable_hover';

        //Bg image position
        $poll_bg_image_position = (isset($options['poll_bg_image_position']) && $options['poll_bg_image_position'] != "") ? $options['poll_bg_image_position'] : 'center center';
        $poll_bg_img_in_finish_page = (isset($options['poll_bg_img_in_finish_page']) && $options['poll_bg_img_in_finish_page'] == "on") ? 'true' : "false";
        $poll_bg_img_in_finish_page_off_color = (isset($options['bg_color']) && $options['bg_color'] != "") ? esc_attr($options['bg_color']) : "false";

        // Poll minimal height
		$poll_min_height_val = (isset($options['poll_min_height']) && $options['poll_min_height'] != '') ? absint(intval($options['poll_min_height'])) : 0;
		
		// Poll answer font size
		$poll_answer_font_size = (isset($options['answer_font_size']) && $options['answer_font_size'] != '') ? $options['answer_font_size']."px" : '15px';
		
		// Poll answers font size on mobile
		$poll_answer_font_size_mobile  = (isset($options['poll_answer_font_size_mobile']) && $options['poll_answer_font_size_mobile'] != '') ? esc_attr($options['poll_answer_font_size_mobile']) : '16';

		// Poll see results button in limitations
		$poll_see_result_button_check = (isset($options['see_result_button']) && $options['see_result_button'] == 'on') ? true : false;

		// Loading font size 
		$poll_loader_font_size = (isset($options['loader_font_size']) && $options['loader_font_size'] != '') ? esc_attr($options['loader_font_size']) : '';
		if(!isset($options['see_result_button'])){
			$poll_see_result_button_check = true;
		}
		$poll_see_result_radio = (isset($options['see_result_radio']) && $options['see_result_radio'] != '') ? esc_attr($options['see_result_radio']) : 'ays_see_result_button';
		$poll_show_result_button_limit = isset($poll_see_result_radio) && $poll_see_result_radio == 'ays_see_result_button' ? true : false;
		$poll_see_result_immediately = isset($poll_see_result_radio) && $poll_see_result_radio == 'ays_see_result_immediately' ? true : false;
		$poll_show_avatars = isset($options['show_passed_users']) && $options['show_passed_users'] == "on" ? true : false;
		$poll_logo_image = (isset($options['logo_image']) && $options['logo_image'] != '') ? esc_url($options['logo_image']) : '';
		$poll_logo_check = (isset($poll_logo_image) && $poll_logo_image != '') ? true : false;
		$poll_image_cont = '';
		// Poll logo image url
		$poll_logo_image_url       = isset($options['poll_logo_url']) && $options['poll_logo_url'] != "" ? esc_attr($options['poll_logo_url']) : ""; 
		$poll_logo_image_url_check = isset($options['poll_enable_logo_url']) && $options['poll_enable_logo_url'] == "on" ? true : false;
		$poll_logo_image_url_href  = "javascript:void(0)";
		if($poll_logo_image_url_check){
			if($poll_logo_image_url != ""){
				if(filter_var($poll_logo_image_url, FILTER_VALIDATE_URL)){
					$poll_logo_image_url_href = $poll_logo_image_url;
				}
			}
		}
		if($poll_logo_check){
			$poll_image_cont = "<div class='ays-image-logo-show'><a href='".$poll_logo_image_url_href."' class='ays-poll-logo-image-url-link'><img src=".$poll_logo_image." class='ays-poll-image-logo'></a></div>";
		}

        if ($poll_min_height_val == 0) {
        	$poll_min_height = '';
        }else{
        	$poll_min_height = 'min-height: '. $poll_min_height_val .'px;';
		}
		
		// Show answers numbering
		$show_answers_numbering = (isset($options['show_answers_numbering']) && sanitize_text_field( $options['show_answers_numbering'] ) != '') ? sanitize_text_field( $options['show_answers_numbering'] ) : 'none';

		// Poll border color
		$poll_border_color = (isset($options['border_color']) && $options['border_color'] != '') ? sanitize_text_field( $options['border_color'] ) : $main_color;

		$enable_box_shadow = isset($options['enable_box_shadow']) && $options['enable_box_shadow'] == "on" ? true : false;

		$box_shadow_color = isset($options['box_shadow_color']) && $options['box_shadow_color'] != "" ? esc_attr($options['box_shadow_color']) : "";
		//  Box Shadow X offset
		$poll_box_shadow_x_offset = (isset($options['poll_box_shadow_x_offset']) && $options['poll_box_shadow_x_offset'] != '' && $options['poll_box_shadow_x_offset'] != 0 ) ? intval( esc_attr( $options['poll_box_shadow_x_offset'] ) ) : 0;

		//  Box Shadow Y offset
		$poll_box_shadow_y_offset = (isset($options['poll_box_shadow_y_offset']) && $options['poll_box_shadow_y_offset'] != '' && $options['poll_box_shadow_y_offset'] != 0 ) ? intval( esc_attr( $options['poll_box_shadow_y_offset'] ) ) : 0;

		//  Box Shadow Z offset
		$poll_box_shadow_z_offset = (isset($options['poll_box_shadow_z_offset']) && $options['poll_box_shadow_z_offset'] != '' && $options['poll_box_shadow_z_offset'] != 0 ) ? intval( esc_attr( $options['poll_box_shadow_z_offset'] ) ) : 15;

		// Poll Vote Reason
		$poll_vote_reason = (isset($options['poll_vote_reason']) && $options['poll_vote_reason'] == 'on') ? true : false;

		if($enable_box_shadow){
			$box_shadow_offsets = $poll_box_shadow_x_offset . 'px ' . $poll_box_shadow_y_offset . 'px ' . $poll_box_shadow_z_offset . 'px ' . '1px ' . $box_shadow_color;
		}
		else{
			$box_shadow_offsets = "none";
		}

		if ($poll_vote_reason) {
			$vote_reason = "<div class='ays-poll-vote-reason'>
                                <div class='ays-poll-for-reason'>". __("Please add vote reason", $this->plugin_name)."</div>                         
                                <div><textarea name='ays-poll-reason-text' id='ays-poll-reason-text'></textarea></div>
                            </div>";
		} else {
			$vote_reason = "";
		}

		// Show answers icon
		$poll_answer_icon_check = (isset($options['poll_answer_icon_check']) && $options['poll_answer_icon_check'] == 'on') ? true : false;
		$poll_answer_icon       = isset($options['poll_answer_icon']) ? $options['poll_answer_icon'] : 'radio';

		// Poll question font size
		$poll_question_font_size_pc     = isset($options['poll_question_size_pc']) && $options['poll_question_size_pc'] != "" ? esc_attr($options['poll_question_size_pc']) : "16"; 
		$poll_question_font_size_mobile = isset($options['poll_question_size_mobile']) && $options['poll_question_size_mobile'] != "" ? esc_attr($options['poll_question_size_mobile']) : "16";

		// Poll question image height
		$poll_question_image_height = isset($options['poll_question_image_height']) && $options['poll_question_image_height'] != "" ? esc_attr($options['poll_question_image_height'])."px" : "100%"; 

		// Poll answer image height
		$poll_question_image_object_fit = (isset($options['poll_question_image_object_fit']) && $options['poll_question_image_object_fit'] != "") ? esc_attr($options['poll_question_image_object_fit']) : "cover";

		// Poll container max-width for mobile
		$poll_mobile_max_width = isset($options['poll_mobile_max_width']) && $options['poll_mobile_max_width'] != '' ? esc_attr($options['poll_mobile_max_width']) . '%' : "100%";
		
		// ==== Buttons styles start ====
		
        // Buttons font size
        $buttons_font_size          = isset($options['poll_buttons_font_size']) && $options['poll_buttons_font_size'] != '' ? esc_attr($options['poll_buttons_font_size']) . 'px' : '17px';        
        // Buttons Left / Right padding
        $buttons_left_right_padding = isset($options['poll_buttons_left_right_padding']) && $options['poll_buttons_left_right_padding'] != '' ? esc_attr($options['poll_buttons_left_right_padding']) . 'px' : '20px';
        // Buttons Top / Bottom padding
        $buttons_top_bottom_padding = isset($options['poll_buttons_top_bottom_padding']) && $options['poll_buttons_top_bottom_padding'] != '' ? esc_attr($options['poll_buttons_top_bottom_padding']) . 'px' : '10px';
        // Buttons border radius
        $buttons_border_radius      = isset($options['poll_buttons_border_radius']) && $options['poll_buttons_border_radius'] != '' ? esc_attr($options['poll_buttons_border_radius']) . 'px' : '3px';
        // Buttons width
        $buttons_width = isset($options['poll_buttons_width']) && $options['poll_buttons_width'] != '' ? esc_attr($options['poll_buttons_width']) . 'px' : 'auto';

		// Poll View Type
        $answer_view_type = isset($options['poll_answer_view_type']) && $options['poll_answer_view_type'] != '' ? esc_attr($options['poll_answer_view_type']) : 'list';
		$answers_container = "";
		if($answer_view_type == "grid" && $poll['type'] == "choosing"){
			$answers_container = "ays_poll_grid_view_container";
		}
		elseif($answer_view_type == "list" && $poll['type'] == "choosing"){
			$answers_container = "ays_poll_list_view_container";

		}

		// ==== Buttons styles end ====
		$poll_answer_image_height = "150";
		$poll_answer_object_fit   = "cover";
		$poll_answer_padding      = "10";
		$poll_answer_margin 	  = "10";
		if($answer_style){
			// Poll answer image height
			$poll_answer_image_height = (isset($options['poll_answer_image_height']) && $options['poll_answer_image_height'] != "") ? esc_attr($options['poll_answer_image_height']) : "150";
			// Poll answer image object fit
			$poll_answer_object_fit   = (isset($options['poll_answer_object_fit']) && $options['poll_answer_object_fit'] != "") ? esc_attr($options['poll_answer_object_fit']) : "cover";
			// Poll answer padding
			$poll_answer_padding      = (isset($options['poll_answer_padding']) && $options['poll_answer_padding'] != "") ? esc_attr($options['poll_answer_padding']) : "10";
			// Poll answer gap
			$poll_answer_margin      = (isset($options['poll_answer_margin']) && $options['poll_answer_margin'] != "" && intval($options['poll_answer_margin']) != 0) ? esc_attr($options['poll_answer_margin']) : "10";
		}

		// Poll title font size
		$poll_title_font_size    = (isset($options['poll_title_font_size']) && $options['poll_title_font_size'] != "") ? absint(intval(esc_attr($options['poll_title_font_size']))) : "20";

		// Poll title alignment
		$poll_title_alignment    = ( isset($options['poll_title_alignment']) && $options['poll_title_alignment'] != "" ) ? esc_attr($options['poll_title_alignment']) : "center";

		// ===== Poll text type options start =====
		$poll_view_type_text = isset($poll['view_type']) && $poll['view_type'] != "" ? $poll['view_type'] : "short_text";

		$poll_text_type_length_enable = (isset($options['poll_text_type_length_enable']) && $options['poll_text_type_length_enable'] == "on") ? true : false;
		$poll_text_type_limit_type    = (isset($options['poll_text_type_limit_type']) && $options['poll_text_type_limit_type'] != "") ? esc_attr($options['poll_text_type_limit_type']) : "characters";
		$poll_text_type_limit_length  = (isset($options['poll_text_type_limit_length']) && $options['poll_text_type_limit_length'] != "") ? esc_attr($options['poll_text_type_limit_length']) : "";
		$poll_text_type_limit_message = (isset($options['poll_text_type_limit_message']) && $options['poll_text_type_limit_message'] == "on") ?  true : false;
		$poll_text_type_width         = (isset($options['poll_text_type_width']) && $options['poll_text_type_width'] != "") ?  stripslashes(esc_attr($options['poll_text_type_width'])) : "";
		$poll_text_type_width_type    = (isset($options['poll_text_type_width_type']) && $options['poll_text_type_width_type'] != "") ?  esc_attr($options['poll_text_type_width_type']) : "percent";
		
		$poll_class_for_limits = $poll_text_type_length_enable ? "ays_poll_question_limit_length" : "";
		$poll_box_for_limit_message = "";
		
		if($poll_text_type_limit_message && ($poll_text_type_limit_length != "" && intval($poll_text_type_limit_length) != 0)){
			$poll_box_for_limit_message = '<div class="ays_quiz_question_text_conteiner">
												<div class="ays_quiz_question_text_message">
													<span class="ays_poll_question_text_message_span">'. $poll_text_type_limit_length . '</span> ' . $poll_text_type_limit_type . ' '.  __( ' left' , $this->plugin_name ) . '
												</div>
											  </div>';
		}
		$poll_text_type_ready_width = "";
		$poll_text_type_ready_type  = "%";
		if($poll['type'] == 'text'){
			if($poll_view_type_text == "short_text"){
				if($poll_text_type_width == "" || intval($poll_text_type_width) == 0){
					$poll_text_type_ready_width = "60";
					$poll_text_type_ready_type  = "%";
				}
				else{
					$poll_text_type_ready_width = $poll_text_type_width;
					$poll_text_type_ready_type  = ($poll_text_type_width_type == "percent" ? "%" : "px");
				}
			}
			else{
				if($poll_text_type_width == "" || intval($poll_text_type_width) == 0){
					$poll_text_type_ready_width = "100";
					$poll_text_type_ready_type  = "%";
				}
				else{
					$poll_text_type_ready_width = $poll_text_type_width;
					$poll_text_type_ready_type  = ($poll_text_type_width_type == "percent" ? "%" : "px");
				}
			}
		}
		// ===== Poll text type options end =====

        $poll_password_box = '';
		$form_method = '';

		$poll_enable_password = isset($options['poll_enable_password']) && $options['poll_enable_password'] == 'on' ? true : false;		
		$poll_password_poll   = isset($options['poll_password']) && $options['poll_password'] != "" ? stripslashes(esc_attr($options['poll_password'])) : "";

		$password_input_val   = isset($_POST['ays_poll_password_val_'. $id ]) && $_POST['ays_poll_password_val_'. $id ] != "" ? stripslashes(esc_attr($_POST['ays_poll_password_val_'. $id ])) : '';
		$poll_password_message = (isset($options['poll_password_message']) &&  $options['poll_password_message'] != '') ? stripslashes( wpautop( $options['poll_password_message'] ) ) : "<p>" . __( "Please enter password", $this->plugin_name ) . "</p>";

		$poll_check_only_logged = (isset($options['enable_logged_users']) && $options['enable_logged_users'] == 1 && !is_user_logged_in()) ? true : false;
		
        if($poll_enable_password){
			$form_method = "method='post'";
	        $poll_password_box = "<div style='padding:50px;text-align:center;' id='ays-poll-password-". $id ."' >
									<div class='ays-poll-password-title'>
	                                	". $poll_password_message . "
									</div>
									<div class='ays-poll-password-box'>
										<div class='ays-poll-password-input-box'>
											<input type='password' class='ays-poll-password-input' id='ays_poll_password_val_". $id ."' name='ays_poll_password_val_". $id ."' required autocomplete='off'>
										</div>
										<div class='ays-poll-password-button-box'>
											<input type='submit' class='ays-poll-password-button' name='ays_poll_password_sub_". $id ."'  class='ays_poll_password' value='".__( "Submit", $this->plugin_name )."'>    
										</div>
									</div>
	                             </div></div></form></div>";
        }
		$poll_password_check = ($password_input_val == $poll_password_poll) ? true : false;

		// Animation Top (px)
		$poll_animation_top = (isset($general_options['poll_animation_top']) && $general_options['poll_animation_top'] != '') ? absint(intval($general_options['poll_animation_top'])) : 100 ;
		$options['poll_enable_animation_top'] = isset($general_options['poll_enable_animation_top']) ? $general_options['poll_enable_animation_top'] : 'on';
		$poll_enable_animation_top = (isset($general_options['poll_enable_animation_top']) && $general_options['poll_enable_animation_top'] == "on") ? true : false;

		// Answers box shadow
		$answers_box_shadow = (isset($options['poll_answer_enable_box_shadow']) && $options['poll_answer_enable_box_shadow'] == "on") ? true : false;
		$answers_box_shadow_color = isset($options['poll_answer_box_shadow_color']) && $options['poll_answer_box_shadow_color'] != '' ? esc_attr($options['poll_answer_box_shadow_color']) : '#000000';
		
		$answers_box_shadow_content = 'box-shadow:unset';
		if($answers_box_shadow){
			$answers_box_shadow_content = 'box-shadow: 0 0 10px '.$answers_box_shadow_color;
		}

		$poll_answer_border_radius = isset($options['poll_answer_border_radius']) && $options['poll_answer_border_radius'] != '' ? intval(esc_attr($options['poll_answer_border_radius'])) : 0;
		
		$content = "<style>

        #".$this_poll_id.".box-apm {
            width: $poll_width;
            ". $poll_min_height ."
            margin: 0 auto !important;
            border-style: {$options['border_style']};
            border-color: $poll_border_color;
            border-radius: " . ((isset($options['border_radius']) && !empty($options['border_radius'])) ? (int) $options['border_radius'] . 'px' : 0) . ";
            border-width: " . ((isset($options['border_width']) && $options['border_width'] != '') ? (int) $options['border_width'] . 'px' : '2px') . ";
            box-shadow: " . $box_shadow_offsets . ";".
           	$poll_styles."
            background-position: ".$poll_bg_image_position.";
            background-repeat: no-repeat;
            background-size: cover;
            max-width: 100%;
            position: relative;
            padding-bottom: 40px;
        }

        .$this_poll_id.ays-minimal-theme .apm-choosing{
		    display: flex;
		    align-items: center;
		}

        .$this_poll_id div.ays-image-logo-show{
			position: absolute;
			bottom: -5px;
    		left: 1px;
			margin: 2px 0 0 0;
			padding: 2px;
			width: 100%;
			height: 65px;
			text-align: left;		
		}
        .$this_poll_id .ays-poll-image-logo{
			width: 55px;
			height: 55px;
		}

		.$this_poll_id.ays-minimal-theme .apm-choosing input[type=radio]:checked + label, .$this_poll_id.ays-minimal-theme .apm-choosing label.ays_enable_hover:hover{
		    background-color: initial !important;
		    color: $main_color !important;
		    border-color: $main_color !important;
		    font-weight: initial !important;
		    margin:3px 0 !important;
		}

		.$this_poll_id.ays-minimal-theme .apm-choosing input[type=radio]:checked + label *,
		.$this_poll_id.ays-minimal-theme .apm-choosing input[type=checkbox]:checked + label *,
		.$this_poll_id.ays-minimal-theme .apm-choosing label.ays_enable_hover:hover *{
		    color: $main_color;
		}

		.$this_poll_id.ays-minimal-theme .apm-choosing input[type=radio]{			
			border-radius: 50%;
			width: 20px;
			height: 19px;
			margin: 3px !important;
			border: 1px solid #1e8cbe;
			opacity: 1;
		}

		.$this_poll_id.ays-minimal-theme .apm-answers .apm-choosing input[type=radio]:checked::after{
			content: '';
			border-radius: 50%;
			width: 11px;
			height: 11px;
			background-color: #1e8cbe;			 
		}

		.$this_poll_id.ays-minimal-theme .apm-choosing input[type=radio]:focus{
			outline-offset: 0 !important;
    		outline: unset !important;
		}

		.$this_poll_id.ays-minimal-theme .apm-choosing label{
			border-color: $text_color !important;
		    font-weight: initial !important;
		    margin:3px 0 !important;
		}

		.$this_poll_id.ays-minimal-theme .apm-choosing input[type='radio']{
		    display: block !important;
		}

		.$this_poll_id.ays-minimal-theme input[type='button'].ays-poll-btn:hover, .$this_poll_id.ays-minimal-theme input[type='button'].ays-poll-btn:focus{
			text-decoration: none;
		}

		.$this_poll_id.ays-minimal-theme input[type='button'].ays-poll-btn{
		    color: initial !important;
		    background: initial !important;
		    border: 1px solid $text_color;
		    border-radius: 3px;
		}

		.$this_poll_id.ays-minimal-theme .ays_poll_passed_count{
		    color: $text_color !important;
		    background: initial !important;
		    border: 1px solid $text_color;
		    border-radius: 3px;
		}

		.$this_poll_id.ays-minimal-theme .ays_poll_passed_count i.ays_poll_fa:before{
		    color: $text_color !important;		    
		}

        .$this_poll_id.ays-minimal-theme .answer-percent{
        	color: initial !important;
		}

        .$this_poll_id.box-apm span.ays_poll_passed_count{
            background-color: $text_color;
            color: $bg_color;
        }

        #$this_poll_id.box-apm span.ays_poll_passed_count i{
            color: $bg_color;
        }
        #$this_poll_id.box-apm .apm-title-box{
            background-color: $title_bg_color;
        }

        .$this_poll_id .answer-percent {
            background-color: $main_color;
            color: $bg_color !important;
        }
        .$this_poll_id .ays-poll-btn{
            background-color: $main_color !important;
            color: $bg_color !important;
            overflow: hidden;
            background: unset;
        }
        #".$this_poll_id." .ays-poll-view-more-button{
		    border-radius: 0;
		}
        .$this_poll_id.box-apm * {
            color: $text_color;
        }
        .$this_poll_id.box-apm .apm-title-box h5 {
            color: $text_color;
            text-transform: inherit;
            font-family: inherit;
        }
        #".$this_poll_id.".box-apm i {
            color: $icon_color;
            font-size: {$options['icon_size']}px;
            font-style: normal;            
        }

		#".$this_poll_id." .ays-poll-btn{
       		width: " . $buttons_width . ";
			font-size: " . $buttons_font_size . ";
			padding: " . $buttons_top_bottom_padding . " " . $buttons_left_right_padding . ";
			border-radius: " . $buttons_border_radius . ";
		}

		#".$this_poll_id ." .apm-add-answer input.ays-poll-new-answer-apply-text{
            width: 100%;
			margin-bottom: 0;
			margin-right: 5px;
			border-color: ".$main_color."; 
			padding: 7px;
			font-size: 14px;
			color: black;
			height: 40px; 
			outline: none;
			display: inline-block;
        }

        #".$this_poll_id.".box-apm i.ays_poll_far{            
            font-family: 'Font Awesome 5 Free';
        }

        #".$this_poll_id.".box-apm .apm-choosing .ays-poll-each-answer-list{            
			width: initial;
			text-align: initial;
			display: initial;
			padding: 10px;
        }

        #".$this_poll_id.".box-apm .apm-choosing .ays-poll-each-answer-grid{            
			width: 100%;
			text-align: center;
			display: inline-block;
			word-break: break-all;
        }

		
        #".$this_poll_id.".box-apm .apm-choosing .ays_label_poll{            
			width: 100%;
			text-align: center;
			display: flex;
			align-items: center;
			padding: ".$poll_answer_padding."px;
        }

        #".$this_poll_id.".box-apm .apm-choosing .ays-poll-each-image{            
			height: ".$poll_answer_image_height."px;
			object-fit: ".$poll_answer_object_fit.";
        }

        #".$this_poll_id.".box-apm .apm-choosing .ays-poll-each-image-list{
			width: 220px;
        }

        #".$this_poll_id.".box-apm .apm-choosing .ays-poll-each-image-grid{
			width: 100%;
        }


        #".$this_poll_id.".box-apm .apm-choosing .ays-poll-answer-container-label-grid{
			align-items: center;
        }

        #".$this_poll_id.".box-apm .apm-choosing .ays-poll-answer-container-label-list{
			flex-direction: row;
        }

        #".$this_poll_id.".box-apm .ays_poll_grid_view_container{
			display: flex;
			flex-wrap: wrap;
			justify-content: space-between;
			align-items: flex-start;
        }

        #".$this_poll_id.".box-apm .ays-poll-answer-container-gird{
			width: calc(50% - 5px);
			margin-bottom: ".$poll_answer_margin."px;
        }
		
        #".$this_poll_id.".box-apm .ays_poll_label_without_padding{
			padding: 10px;
			align-items: center;
			flex-direction: column;
        }

        #".$this_poll_id.".box-apm .apm-title-box h5{
			font-size: ".$poll_title_font_size."px;
			word-break: break-all;
			word-wrap: break-word;
			text-align: ".$poll_title_alignment.";
        }

        #".$this_poll_id.".box-apm .ays-poll-answer-container-list{			
			margin-bottom: ".$poll_answer_margin."px;
			display: flex;
			width: 100%;
        }

        #".$this_poll_id.".box-apm .ays-poll-maker-text-answer-main input,
		#".$this_poll_id.".box-apm .ays-poll-maker-text-answer-main textarea{
			min-width: 150px;
			max-width: 100%;
			width: ".$poll_text_type_ready_width.$poll_text_type_ready_type.";
        }

        #".$this_poll_id.".box-apm .ays-poll-password-box .ays-poll-password-button-box .ays-poll-password-button{
			background-color: ".$main_color.";
			color: ".$bg_color.";
			border-color: ".$main_color.";
			outline: none;
			box-shadow: unset;
			border: 0;
			transition: .5s;
        }

        #".$this_poll_id.".box-apm .ays-poll-password-box .ays-poll-password-button-box .ays-poll-password-button:hover{
			background-color: ".$main_color."b5;
        }

        #".$this_poll_id.".box-apm .ays-poll-password-box .ays-poll-password-input-box .ays-poll-password-input{
			border-color: ".$main_color.";
        }

		#".$this_poll_id.".box-apm .apm-answers .apm-choosing label.ays_label_poll{            
			".$answers_box_shadow_content.";
			border-radius: ".$poll_answer_border_radius."px;
        }
		
		";

		
		if(!$poll_theme){
			$content .=  "#".$this_poll_id." div.apm-load-message-container{            
				background-color: ".$bg_color." !important;
			}";
		}
        switch ($answer_border_side) {
        	case 'none':
        		$answer_border_type = "border: none";
        		break;
        	case 'all_sides':
        		$answer_border_type = "border: 1px solid ".$main_color;
        		break;
        	case 'top':
        		$answer_border_type = "border-top: 1px solid ".$main_color;
        		break;
        	case 'bottom':
        		$answer_border_type = "border-bottom: 1px solid ".$main_color;
        		break;
        	case 'left':
        		$answer_border_type = "border-left: 1px solid ".$main_color;
        		break;
        	case 'right':
        		$answer_border_type = "border-right: 1px solid ".$main_color;
        		break;
        	default:
        		$answer_border_type = "border: 1px solid ".$main_color;
        		break;
        }
        
        if ( $answer_style ) {
    	 	$content .= "
	        #".$this_poll_id.".choosing-poll label {
	            background-color: $answer_bg_color;
	            ".$answer_border_type.";
	            text-transform: inherit;
	        }";
        }

        $content .= "
        .$this_poll_id.choosing-poll input[type=radio]:checked + label,
		.$this_poll_id:not(.ays-minimal-theme).choosing-poll input[type='checkbox']:checked + label,
        .$this_poll_id.choosing-poll label.ays_enable_hover:hover {
            background-color: $text_color !important;
            color: $bg_color;
        }

		.$this_poll_id:not(.ays-minimal-theme).choosing-poll input[type='checkbox']{
			display: none;
		}
        .$this_poll_id.choosing-poll input[type=radio]:checked + label *,
        .$this_poll_id.choosing-poll input[type=checkbox]:checked + label *,
        .$this_poll_id.choosing-poll label.ays_enable_hover:hover * {
            color: $answer_bg_color;
        }";

		if ($poll_answer_icon_check) {
			$content .= "
		   #".$this_poll_id.".choosing-poll label.ays_label_poll:not(.apm-label-with-bg) span.ays_grid_answer_span{
			   display: inline-block;
			   width: calc(100% - 10px);
			   margin: 0 -15px;
			}
		   #".$this_poll_id.".choosing-poll label.ays_label_poll span.ays_grid_answer_span{
			   display: inline-block;
			   width: calc(100% - 10px);
			}";
	   }

	   if ($poll_answer_icon_check) {
		$content .= 
	   "#".$this_poll_id." label.ays_poll_answer_icon_radio:before,
		#".$this_poll_id." label.ays_poll_answer_icon_checkbox:before{
			content: '';
			display: inline-block;
			background: #ddd;
			background-clip: content-box;
			width: 20px;
			height: 20px;
			border: 3px solid #ccc;
			padding: 3px 3px 3px 3px;
			box-sizing: border-box;
			transition: all .4s linear;
			vertical-align: middle;
			margin-right: 10px;
			margin-bottom: 2px;
		}

		#".$this_poll_id." label.ays_poll_answer_icon[for='poll_answer_icon_radio']:before,
		#".$this_poll_id." label.ays_poll_answer_icon_radio:before{
			border-radius: 50%;
		}

		#".$this_poll_id." input[name='answer']:checked + label.ays_poll_answer_icon_radio:before,
		#".$this_poll_id." input[name='answer']:checked + label.ays_poll_answer_icon_checkbox:before{
			background: green;
			border: 3px solid green;
			padding: 3px 3px 3px 3px;
			background-clip: content-box;
			background-color: green !important;
		}";
	}


	   $content .= ".$this_poll_id .apm-info-form input {
            border-color: $main_color;
        }
        div[class~=".$this_poll_id."] label.ays_label_font_size {
            font-size: $poll_answer_font_size;
        }
        button.ays-poll-next-btn:focus {
            background: unset;
            outline: none;
        }
        button.ays-poll-next-btn:disabled {
            cursor: not-allowed;
            background: dimgrey !important;
            color: white !important;
        }
        button.ays-poll-next-btn:enabled {
            cursor: pointer;
        }
        .$this_poll_id .apm-info-form input {
            color: $text_color !important;
            background-color: $answer_bg_color !important;
        } 

        .ays-poll-main #".$this_poll_id." .ays-poll-img {
            object-fit: ".$poll_question_image_object_fit.";
			height: ".$poll_question_image_height."; 
        }

        .$this_poll_id div.apm-loading-gif .apm-loader svg path,
        .$this_poll_id div.apm-loading-gif .apm-loader svg rect {
            fill: $main_color;
        }" . (isset($load_gif) && $load_gif == 'plg_4' ?
				".$this_poll_id div.apm-loading-gif .apm-loader svg {
            stroke: $main_color;
        }

        .$this_poll_id.choosing-poll .ays_poll_cb_and_a,
        .$this_poll_id.choosing-poll .ays_poll_cb_and_a * {
            color: " . $this->hex2rgba($text_color) . ";
		}
		
		.ays_poll_category-container{
			width: '".$poll_width."',
			maxWidth: '98%',
			fontSize: '16px',
			padding: '10px',
			margin: '0 auto',
			marginTop: '-1rem',
			borderStyle: '{$options['border_style']}',
			borderWidth: '2px',
			borderColor: '$main_color',
			background: '$bg_color',
			color: '$main_color',
			transition: '.3s ease',
			WebkitAppearance: 'none',
			appearance: 'none',
		}

        .$this_poll_id div.apm-loading-gif .apm-loader svg>g {
            fill: $bg_color;
        }"
				: "")
				   . "{$poll['custom_css']}";
		if($poll_loader_font_size != ''){
			$content .= ".$this_poll_id div.apm-loading-gif .apm-loader{
				display: flex;
				justify-content: center;
				align-items: center;
				padding-top: 10px;
			}
			.$this_poll_id div.apm-loading-gif{
				width: 100%;
    			height: 100%;
			}
			";
		}

		$content .= ".$this_poll_id.box-apm .ays_question p{
			font-size: ".$poll_question_font_size_pc."px;
		}
		
		@media only screen and (max-width: 768px){
			.$this_poll_id.box-apm .ays_question p{
				font-size: ".$poll_question_font_size_mobile."px;
			}
			.$this_poll_id.box-apm label.ays_label_font_size {
				font-size: ".$poll_answer_font_size_mobile."px;
			}
		}

		@media screen and (max-width: 768px){
			#".$this_poll_id."{
				max-width: ".$poll_mobile_max_width.";
			}
		}

		";


		$content .= "	   
        </style>
        <script>
            var dataCss = {
				width: '".$poll_width."',
                maxWidth: '98%',
                fontSize: '16px',
                padding: '10px',
                margin: '0 auto',
                marginTop: '-1rem',
                borderStyle: '{$options['border_style']}',
                borderWidth: '2px',
                borderColor: '$main_color',
                background: '$bg_color',
                color: '$main_color',
                transition: '.3s ease',
                WebkitAppearance: 'none',
                appearance: 'none',
                };
            var hoverCss = {
                background: '$main_color',
                color: '$bg_color',
                borderColor: '$bg_color',
            };
        </script>"; 

        if ($poll_direction == 'center') {
        	$poll_direction_center = "style='text-align: center;'";
        	$poll_direction = 'ltr';
        }else{
        	$poll_direction_center = "";        	
        }

		// AV Show login form for not logged in users
        $options['show_login_form'] = isset($options['show_login_form']) ? $options['show_login_form'] : 'off';
        $show_login_form = (isset($options['show_login_form']) && $options['show_login_form'] == "on") ? true : false;
		$add_form = $show_login_form && !is_user_logged_in() ? "" : "<form style='margin-bottom: 0;' ".$form_method.">";

		if(isset($options['show_create_date']) && $options['show_create_date'] == 1){
            $show_create_date = true;
        }else{
            $show_create_date = false;
        }

        if(isset($options['show_author']) && $options['show_author'] == 1){
            $show_author = true;
        }else{
            $show_author = false;
        }

        //Enabled ansers sound
        $enable_asnwers_sound =  isset($options['enable_asnwers_sound']) && $options['enable_asnwers_sound'] == 'on' ? true : false;
         $answers_sound = '';
		 $answers_sound_class = '';
		 $answers_sound_mute  = '';
        if ($enable_asnwers_sound) {

        	$settings_options = ($poll_settings->ays_get_setting('options') === false) ? json_encode(array()) : $poll_settings->ays_get_setting('options');
            $setting_options = json_decode($settings_options, true);
            $answers_sound_path = isset($setting_options['answers_sound']) && !empty($setting_options['answers_sound']) ? $setting_options['answers_sound'] : false;
           
            if ($answers_sound_path != false) {
            	$answers_sound = "<audio id='ays_poll_ans_sound_".$id."' class='ays_poll_ans_sound' src='".$answers_sound_path."'></audio>";
				 $answers_sound_class = 'poll_answers_sound';
				 $answers_sound_mute = "<span class='ays_music_sound ays_sound_active'><i class='ays_poll_far ays_poll_fa-volume_up'></i></span>";

            }
        }        

		//AV show timer
        $activeDateCheck =  isset($options['active_date_check']) && !empty($options['active_date_check']) ? true : false;
        $activeDeactiveDateCheck =  isset($options['deactiveInterval']) && !empty($options['deactiveInterval']) ? true : false;
        $activeActiveDateCheck =  isset($options['activeInterval']) && !empty($options['activeInterval']) ? true : false;
        $show_timer_type = isset($options['ays_show_timer_type']) && !empty($options['ays_show_timer_type']) ? $options['ays_show_timer_type'] : 'countdown';
        $show_timer = '';
		if ( $activeDateCheck && $activeDeactiveDateCheck && !$is_start_soon) {
		    if (isset($options['ays_poll_show_timer']) && $options['ays_poll_show_timer'] == 1) {
				$show_timer .= "<div class='ays_poll_show_timer'>";
				if ($show_timer_type == 'countdown') {
					if ($endDate_atr > 0) {
						$show_timer .= '<p class="show_timer_countdown" data-timer_countdown="'.$endDate_atr.'"></p>';
					}
				}else if ($show_timer_type == 'enddate') {
					$show_timer .= '<p class="show_timer_countdown">'.__('This Poll active until ',$this->plugin_name).gmdate('jS \of F Y H:i:s', intval($endDate)).'</p>';
				}
				$show_timer .= "</div>";
		    }
		}elseif ($activeDateCheck && $activeActiveDateCheck && $is_start_soon) {
			if (isset($options['ays_poll_show_timer']) && $options['ays_poll_show_timer'] == 1) {
				$show_timer .= "<div class='ays_poll_show_timer'>";
				if ($show_timer_type == 'countdown') {
					$show_timer .= '<p class="show_timer_countdown" data-timer_countdown="'.$startDate_atr.'"></p>';
				}else if ($show_timer_type == 'enddate') {
					$show_timer .= '<p class="show_timer_countdown">'.__('This Poll will start ',$this->plugin_name).gmdate('jS \of F Y H:i:s', intval($startDate)).'</p>';
				}
				$show_timer .= "</div>";
		    }
		}

		$show_cd_and_author = "<div class='ays_poll_cb_and_a'>";
        if($show_create_date){
            $poll_create_date = (isset($options['create_date']) && $options['create_date'] != '') ? $options['create_date'] : "0000-00-00 00:00:00";
            if(Poll_Maker_Ays_Admin::validateDate($poll_create_date)){
                $show_cd_and_author .= "<span>".__("Created on",$this->plugin_name)." </span><strong><time>".date("F d, Y", strtotime($poll_create_date))."</time></strong>";
            }else{
                $show_cd_and_author .= "";
            }
        }
        
        if($show_author){
            if(isset($options['author'])){
                if(is_array($options['author'])){
                    $author = $options['author'];
                }else{
                    $author = json_decode($options['author'], true);
                }
            }else{
                $author = array("name"=>"Unknown");
            }
            $user_id = 0;
            if(isset($author['id']) && intval($author['id']) != 0){
                $user_id = intval($author['id']);
            }
            $image = get_avatar($user_id, 32);
            if($author['name'] !== "Unknown"){
                if($show_create_date){
                    $text = __("By", $this->plugin_name);
                }else{
                    $text = __("Created by", $this->plugin_name);
                }
                $show_cd_and_author .= "<span>   ".$text." </span>".$image."<strong>".$author['name']."</strong>";
            }else{
                $show_cd_and_author .= "";
            }
        }

        $show_cd_and_author .= "</div>";

        $poll_login_form = "";
        if($show_login_form){
            $args = array(
                'echo' 		  => false,
                'form_id'     => 'ays_loginform_'.$this_poll_id,
                'id_username' => 'ays_user_login_'.$this_poll_id,
                'id_password' => 'ays_user_pass_'.$this_poll_id,
                'id_remember' => 'ays_rememberme_'.$this_poll_id,
                'id_submit'   => 'ays-submit_'.$this_poll_id
            );
            $poll_login_form = "<div class='ays_poll_login_form'>" . wp_login_form( $args ) . "</div>";
    	}

    	$ays_result_message = isset($options['result_message']) ? do_shortcode(wpautop(stripslashes($options['result_message']))) : '';
    	$result_message = isset($options['hide_result_message']) && $options['hide_result_message'] == 1 ? "<div class='apm-title-box ays_res_mess'>" . $ays_result_message . "</div>" : "";

    	$custom_class = isset($options['custom_class']) && $options['custom_class'] != '' ? $options['custom_class'] : "";

		$content .= "<div style='margin-bottom: 1rem; width:$poll_width' class='ays-poll-main ".$custom_class."' id='ays-poll-container-" . $id . "' data-load-method='".$poll_see_result_immediately."'>
        ".$add_form."
        ".$answers_sound."        
        <div
        $poll_direction_center
        dir='$poll_direction'
        data-loading='$load_effect'
        data-load-gif='$load_gif'
        data-load-gif-font-size='$poll_loader_font_size'
        ".$poll_load_message_data."
        data-hide-bg-image='$poll_bg_img_in_finish_page'
        data-gradient-check='$enable_background_gradient'
        data-gradient-dir='$poll_gradient_direction'
        data-gradient-c1='$background_gradient_color_1'
        data-gradient-c2='$background_gradient_color_2'
        data-hide-bg-image-def-color='$poll_bg_img_in_finish_page_off_color'
        data-show-social='$show_social'
        class='box-apm $poll_theme {$poll['type']}-poll $this_poll_id '
        id='$this_poll_id'
        data-res='$hide_results'
        data-res-sort='$result_sort'
        data-restart ='" . (isset($options['enable_restart_button']) && $options['enable_restart_button'] ? 'true' : 'false') . "'
        data-redirection = '$redirect_users'
        data-redirect-check = '".$redirect_url_checked."'
        data-url-href = '".$redirect_url_href."'
        data-href = '$redirect_after_vote_url'
        data-delay = '$redirect_delay'
        data-id='{$poll['id']}'
        data-res-rgba = '". $result_in_rgba ."'
        data-percent-color = '". $answer_percent_color ."'
        data-enable-top-animation = '". $poll_enable_animation_top ."'
        data-top-animation-scroll = '". $poll_animation_top ."'
        data-info-form='$info_form'
        >" . $poll_result_reports;

		if($poll_enable_password && !$poll_password_check && !$is_expired && !$poll_check_only_logged){
			$content .= $poll_password_box;
			echo $content;
			return;
		}

		$content .= $show_cd_and_author;
		$content .= $show_timer;
		$content .= $poll_image_cont;
		$content .= $answers_sound_mute;
		$content .= ($poll['show_title'] == 1) ? "<div class='apm-title-box'><h5>" . stripslashes($poll['title']) . "</h5></div>" : "";	
		$content .= "<div class='$this_poll_id ays_question'>" . do_shortcode(wpautop(stripslashes($poll['question']))) . "</div>";
		$content .= $poll['image'] ? "<div class='apm-img-box'><img class='ays-poll-img' src='{$poll['image']}'></div>" : "";
		$content .= "<div class='$this_poll_id hideResults'>" . $hide_results_text . "</div>";
		if(($is_expired || $is_start_soon) && $poll_check_exp_cont){
			$content = "";
			return $content;
		}
		if (!$is_expired) {
			//CHECK IF ENABLED ONLY LOGGED IN USERS OPTION
			if (isset($options['enable_logged_users']) && $options['enable_logged_users'] == 1 && !is_user_logged_in()) {
				$logged_users_message = isset($options['enable_logged_users_message']) && $options['enable_logged_users_message'] != '' ? stripslashes($options['enable_logged_users_message']) : "<p>" . __('You must sign in for voting.', $this->plugin_name) . "</p>";

				$content .= "<div class='apm-need-sign-in'>".$logged_users_message."</div>";

				if($logged_users_message !== null){
		            if(!is_user_logged_in()){
						$content .= "<div class='apm-need-sign-in'>".$poll_login_form;
		            }
		        }
			} else {
				$load_poll = true;
				if (isset($options['enable_restriction_pass']) && $options['enable_restriction_pass'] == 1) {
					// Users role Aro start
			        global $wp_roles;
					$user      = wp_get_current_user();
			        $users_roles  = $wp_roles->role_names;
					$message   = (isset($options['restriction_pass_message']) && $options['restriction_pass_message'] != '') ? stripslashes($options['restriction_pass_message']) : ("<p>" . __('You not have permissions for voting.', $this->plugin_name) . "</p>");
			        $users_role = (isset($options['users_role']) && $options['users_role'] != '') ? $options['users_role'] : '';
			        $users_role = json_decode($users_role);
			        if(!empty($users_role)){
			            if (is_array($users_role)) {
			                foreach($users_role as $key => $role){
			                    if(in_array($role, $users_roles)){
			                        $users_role[$key] = array_search($role, $users_roles);
			                    }                        
			                }
			            }else{
			                if(in_array($users_role, $users_roles)){
			                    $users_role = array_search($users_role, $users_roles);
			                }
			            }
			            $is_user_role = false;
			            if(is_array($users_role)){
			                foreach($users_role as $role){                        
			                    if (in_array(strtolower($role), (array)$user->roles)) {
			                        $is_user_role = true;
			                        break;
			                    }
			                }                    
			            }else{
			                if (in_array(strtolower($users_role), (array)$user->roles)) {
			                    $is_user_role = true;
			                }
			            }

			            if (!$is_user_role) {
			                $content   .= "<div class='ays-poll-limitation'>$message</div>";
							$load_poll = false;
			            }
			        }
			    //Aro end
				}
				if ($load_poll) {
					$limit_users = 0;
					global $wpdb;
					if ($limitusers) {
						$report_table = esc_sql($wpdb->prefix."ayspoll_reports");
						$answ_table = esc_sql($wpdb->prefix."ayspoll_answers");
						if ((!empty($options['limit_users_method']) && $options['limit_users_method'] == 'ip') || empty($options['limit_users_method'])) {
							// $user_ips = $this->get_user_ip();
							// $user_ip = esc_sql($user_ips);
							// $args_ip = array($user_ip,$id);
							// $args_tables = array(
							// 	'reports_table' => $report_table,
							// 	'answer_table'  => $answ_table
							// );
							$limit_users = $this->ays_poll_get_limit_user_count_by_ip($id);
							if (isset($_COOKIE['ays_this_poll_cookie_'.$id])) {
								unset($_COOKIE['ays_this_poll_cookie_'.$id]); 
								setcookie('ays_this_poll_cookie_'.$id, null,time() -1, '/');
							}
						}
						elseif((isset($options['limit_users_method']) && $options['limit_users_method'] == 'cookie')){
							$cookie_name = "ays_this_poll_cookie_".$poll_id;
							if(isset($_COOKIE[$cookie_name])){
								$limit_users = 1;
							}
						}
						elseif((isset($options['limit_users_method']) && $options['limit_users_method'] == 'cookie_ip')){
							$cookie_name = "ays_this_poll_cookie_".$poll_id;
							$limit_users_check = $this->ays_poll_get_limit_user_count_by_ip($id);							
							if(isset($_COOKIE[$cookie_name]) || $limit_users_check > 0){
								$limit_users = 1;
							}
							elseif(!isset($_COOKIE[$cookie_name]) || $limit_users_check > 0){
								$limit_users = $this->ays_poll_get_limit_user_count_by_ip($id);
							}
						}
						 else {
							$user_id = is_user_logged_in() ? wp_get_current_user()->ID : 0;
							$args_id = array($user_id, $id);
							$sql = "SELECT COUNT(*) 
									FROM ".$report_table." 
									JOIN ".$answ_table." 
									ON ".$answ_table.".id = ".$report_table.".answer_id 
									WHERE ".$report_table.".user_id = %d 
									AND ".$answ_table.".poll_id = %d";
							if ($user_id > 0) {
								$limit_users = $wpdb->get_var(
							   	  	$wpdb->prepare( $sql, $args_id)
							  	);
							}else{
								$limit_users = 0;
							}
							if (isset($_COOKIE['ays_this_poll_cookie_'.$id])) {
								unset($_COOKIE['ays_this_poll_cookie_'.$id]); 
								setcookie('ays_this_poll_cookie_'.$id, null,time() -1, '/');
							}
						}
					}
					else{
						if (isset($_COOKIE['ays_this_poll_cookie_'.$id])) {
							unset($_COOKIE['ays_this_poll_cookie_'.$id]); 
							setcookie('ays_this_poll_cookie_'.$id, null,time() -1, '/');
						}
					}
					$poll_allow_multivote = isset($options['poll_allow_multivote']) && $options['poll_allow_multivote'] == 'on' ? true : false;
					$poll_multivote_checkbox = $poll_allow_multivote ? 'checkbox' : 'radio';						

					$multivote_answer_count = '';
					$allow_multivote_answer = '';
					if($poll_allow_multivote){
						$multivote_answer_count = (isset($options['poll_allow_multivote_count']) && $options['poll_allow_multivote_count'] != '') ? absint(intval($options['poll_allow_multivote_count'])) : '1';
						$multiple_select = 'multiple';
						$allow_multivote_answer = 'on';
					}else{
						$multiple_select = '';
						$allow_multivote_answer = '';
					}
					if ($limit_users == 0) {

						$view_more_button_flag = false;
						if (isset($poll['type']) && $poll['type'] == 'choosing') {
							if ($enable_view_more_button) {
								if ( $poll_view_more_button_count != 0 && $poll_view_more_button_count < count($poll['answers']) ) {
									$view_more_button_flag = true;
								}
							}
						}

						if ($poll_answer_icon_check && $poll_theme != 'ays-minimal-theme') {
							switch ($poll_answer_icon) {
							   case 'radio':
								   $answer_icon_class = 'ays_poll_answer_icon_radio';
								   break;
							   case 'checkbox':
								   $answer_icon_class = 'ays_poll_answer_icon_checkbox';
								   break;	             	            		
							   default:
								   $answer_icon_class = '';
								   break;
						   }
						}else{
							$answer_icon_class = '';
						}

						$content .= "<div class='apm-answers $without_vote ".$answers_container."'>";
						switch ( $poll['type'] ) {
							case 'choosing':
								$pol_answer_view_type = isset($poll['styles']['poll_answer_view_type']) && $poll['styles']['poll_answer_view_type'] != "" ? esc_attr($poll['styles']['poll_answer_view_type']) : "list";
								$randomize_answers = (isset($poll['styles']['randomize_answers']) && $poll['styles']['randomize_answers'] == 'on') ? true : false;
								$redirect_after_submit = (isset($poll['styles']['redirect_after_submit']) && $poll['styles']['redirect_after_submit'] == 1) ? 'redirect-after-vote-url' : '';
								
								$numbering_type = "";
								if ($randomize_answers) {
									shuffle($poll['answers']);
								}
								$answers_count = isset($poll['answers']) && $poll['answers'] != '' ? count($poll['answers']) : false;
								$numbering_type_arr = array();
								if($answers_count){
									$numbering_type_arr = $this->ays_answer_numbering($show_answers_numbering , $answers_count);
								}
								if($poll_allow_multivote){
									$content .= "<input type='hidden' id='multivot_answer_count' value='".$multivote_answer_count."'/>";
								}
								foreach ( $poll['answers'] as $index => $answer ) {
									if ($answer['user_added'] == 1 && $answer['show_user_added'] ==  0) {
										continue;
									}
									else{
										if ($answer['show_user_added'] ==  0){
											continue;
										}
										else{
											if(!empty($numbering_type_arr)){
												$numbering_type = isset($numbering_type_arr[$index]) && $numbering_type_arr[$index] != "" ? $numbering_type_arr[$index] : "";
												$numbering_type = $numbering_type . " "; 
											}
											$answer_style_class = '';
											if ($view_more_button_flag) {
												if ($poll_view_more_button_count - 1 < $index) {
													$answer_style_class = 'ays_poll_display_none';
												}
											}
											$pol_answer_view_type_show = 'ays_poll_list_view_item';										
											$pol_answer_view_type_image = 'ays-poll-each-image-list';
											$pol_answer_view_type_cont = 'ays-poll-answer-container-list';
											$pol_answer_view_type_label_cont = 'ays-poll-answer-container-label-list';
											if($pol_answer_view_type == "grid"){
												$pol_answer_view_type_label_cont = 'ays-poll-answer-container-label-grid';
												$pol_answer_view_type_cont = 'ays-poll-answer-container-gird';
												$pol_answer_view_type_show = 'ays_poll_grid_view_item';
												$pol_answer_view_type_image = 'ays-poll-each-image-grid';
											}

											$poll_answer_image = isset($answer['answer_img']) && $answer['answer_img'] != "" ? esc_attr($answer['answer_img']) : "";
											$poll_added_style = "";
											if($poll_answer_image && $answer_view_type == 'list'){
												$poll_added_style = "max-width: calc(100% - 220px);";				
												
											}
											$poll_answer_image_show = $poll_answer_image ? "<div><img src=".esc_attr($answer['answer_img'])." class='".$pol_answer_view_type_image." ays-poll-each-image'></div>" : "";
											$poll_class_for_answer_label = "";
											$poll_class_for_answer_label_text = "";
											
											$pol_answer_view_type_text_show = 'ays-poll-each-answer-grid';
											if($poll_answer_image){
												$poll_class_for_answer_label = "ays_poll_label_without_padding";
												$pol_answer_view_type_text_show = 'ays-poll-each-answer-list';	
												
                                                $poll_class_for_answer_label_text = "ays_poll_label_text_with_padding";
											}
											$content .= "
											<div class='apm-choosing answer-$this_poll_id ". $answer_style_class ." ays-poll-field ".$pol_answer_view_type_cont."' >
											<input type=".$poll_multivote_checkbox." name='answer' id='radio-$index-$this_poll_id' value='{$answer['id']}' autocomplete='off'>
											<label for='radio-$index-$this_poll_id' class='ays_label_poll ".$answers_sound_class." ".$redirect_after_submit." ".$disable_answer_hover." ays_label_font_size ".$answer_icon_class." ".$poll_class_for_answer_label." ".$pol_answer_view_type_label_cont."' answers-url='".$answer['redirect']."'>".$poll_answer_image_show." <div style='".$poll_added_style."'><span class='".$pol_answer_view_type_text_show."'>"
											. $numbering_type . stripcslashes(html_entity_decode($answer['answer'])) . 
											"</span></div></label>
											</div>";
										}
									}
								}
								$add_answer_for_grid = isset($poll['view_type']) && $poll['view_type'] == 'grid' ? 'add_answer_for_grid' : '';
								if ($poll_allow_answer && $with_vote) {
									$content .= "
									    <div class='apm-choosing answer-$this_poll_id ".$this_poll_id."_addAnswer apm-add-answer ".$add_answer_for_grid."'>
									          <input type='text' placeholder='" . __("Other - please specify", $this->plugin_name) . "' class='ays-poll-new-answer-apply-text' name='ays_poll_new_answer'>									          
									    </div>
									";
									$allow_multi_vote_for_other = isset($options["poll_allow_multivote"]) && $options["poll_allow_multivote"] == "on" ? true : false;
									if(!$allow_multi_vote_for_other){
										$content .= "
										<div class='ays-poll-add-answer-note ays-poll-add-answer-note-enable'>
											<blockquote class='ays-poll-add-answer-note-text'>".__( 'Note if the other answer is filled, it will be considered as a vote and will ignore the checked answers.', $this->plugin_name)."</blockquote>
										</div>
									";
									}
									
								}
								break;
							case 'voting':
								switch ( $poll['view_type'] ) {
									case 'hand':
										foreach ( $poll['answers'] as $index => $answer ) {
											$content .= "<div class='apm-voting answer-$this_poll_id'><input type='radio' name='answer' id='radio-$index-$this_poll_id' value='{$answer['id']}'>
                                                    <label for='radio-$index-$this_poll_id' class='".$answers_sound_class."'>";
											$content .= ((int) $answer['answer'] > 0 ? "<i class='ays_poll_far ays_poll_fa-thumbs-up'></i>" : "<i class='ays_poll_far ays_poll_fa-thumbs-down'></i>") . "</label></div>";
										}
										break;
									case 'emoji':
										foreach ( $poll['answers'] as $index => $answer ) {
											$content .= "<div class='apm-voting answer-$this_poll_id'><input type='radio' name='answer' id='radio-$index-$this_poll_id' value='{$answer['id']}'>
                                                    <label for='radio-$index-$this_poll_id' class='".$answers_sound_class."'>";
											$content .= ((int) $answer['answer'] > 0 ? $emoji[1] : $emoji[3]) . "</label></div>";
										}
										break;
									default:
										break;
								}
								break;
							case 'rating':
								switch ( $poll['view_type'] ) {
									case 'star':
										foreach ( $poll['answers'] as $index => $answer ) {
											$content .= "<div class='apm-rating answer-$this_poll_id'><input type='radio' name='answer' id='radio-$index-$this_poll_id' value='{$answer['id']}'>
                                                    <label for='radio-$index-$this_poll_id'><i class='ays_poll_far ays_poll_fa-star ".$answers_sound_class."'></i></label></div>";
										}
										break;
									case 'emoji':
										foreach ( $poll['answers'] as $index => $answer ) {
											$content .= "<div class='apm-rating answer-$this_poll_id'><input type='radio' name='answer' id='radio-$index-$this_poll_id' value='{$answer['id']}'>
                                                    <label class='emoji ".$answers_sound_class."' for='radio-$index-$this_poll_id'>" . $emoji[(count($poll['answers']) / 2 - $index + 1.5)] . "</label></div>";
										}
										break;
									default:
										break;
								}
								break;
							case 'text':
								$poll_text_type_placeholder = ( isset($options['poll_text_type_placeholder']) && $options['poll_text_type_placeholder'] != "" ) ?  stripslashes(esc_attr($options['poll_text_type_placeholder'])) : "";
								switch ( $poll_view_type_text ) {
									case 'short_text':
										$content .= "<div class='ays-poll-maker-text-answer-main answer-".$this_poll_id."'>
														<div>
															<input type='text' id='ays-poll-text-type-short-".$this_poll_id."' class='ays-poll-text-types-inputs ".$poll_class_for_limits."' placeholder='".$poll_text_type_placeholder."' name='answer' data-max-length='".$poll_text_type_limit_length."' data-limit-type='".$poll_text_type_limit_type."' autocomplete='off'>
															<label for='ays-poll-text-type-short-".$this_poll_id."'></label>
														</div>
														".$poll_box_for_limit_message."
													</div>";											
										break;
									case 'paragraph':
										$content .= "<div class='ays-poll-maker-text-answer-main answer-".$this_poll_id."'>
														<div>
															<textarea id='ays-poll-text-type-paragraph-".$this_poll_id."' class='ays-poll-text-types-inputs ays-poll-text-types-inputs-only-textarea ".$poll_class_for_limits."' placeholder='".$poll_text_type_placeholder."' name='answer' data-max-length='".$poll_text_type_limit_length."' data-limit-type='".$poll_text_type_limit_type."'></textarea>
															<label for='ays-poll-text-type-paragraph-".$this_poll_id."'></label>
														</div>
														".$poll_box_for_limit_message."
													</div>";
										break;
									default:										
										break;
								}
								break;
							default:
								break;
						}
						
						if ($view_more_button_flag) {
							$content .= '
							<div class="ays-poll-view-more-button-box">
								<input type="button" class="btn ays-poll-btn ays-poll-view-more-button" value="'. __( "View more", $this->plugin_name ) .'">
							</div>';
						}

						$content .= "</div>";
						$content .= "<div class='apm-cashed-fa'>";
						foreach ( $poll['answers'] as $index => $answer ) {
							$content .= "<div>
                                <i class='ays_poll_fas ays_poll_fa-star' style='font-size: 0'></i>
                            </div>";
						}
						$content .= "</div>";
						if ($info_form) {
							$this->fields_placeholders = $this->ays_set_poll_fields_placeholders_texts();
							$content .= "\n
							<div class='apm-info-form' data-text='" . __("Send", $this->plugin_name) . "' style='display: none;'>
								$info_form_title
								<div class='amp-info-form-input-box'>
							";
							foreach ( $fields as $f ) {
								$required = array_search($f, $required_fields) !== false ? "true" : "false";
								switch ( $f ) {
									case "apm_email":
										$content .= "
										<input type='email' check_id='".$this_poll_id."' class='ays_animated_xms' name='$f' data-required='$required' placeholder='".$this->fields_placeholders['emailPlaceholder']."'>
										";
										break;
									case "apm_phone":
										$content .= "
										<input type='tel' class='ays_animated_xms' name='$f' data-required='$required' placeholder='".$this->fields_placeholders['phonePlaceholder']."'>
										";
										break;
									default:
										$content .= "
										<input type='text' class='ays_animated_xms' name='$f' data-required='$required' placeholder='".$this->fields_placeholders['namePlaceholder']."'>
										";
										break;
								}
							}
							$content .= "</div></div>";
						}
					} else {
						$content .= "<div class='ays-poll-limitation'>" . (isset($options['limitation_message']) && $options['limitation_message'] != '' ? stripslashes($options['limitation_message']) : ("<p>" . __("You have already voted.", $this->plugin_name) . "</p>")) . "</div>";
						if (isset($options['redirect_url']) && $options['redirect_url'] != '' && isset($options['redirection_delay']) && $options['redirection_delay'] != 0) {
							$content .= "<div class='apm-redirection apm-redirection-$this_poll_id'>
                                        <p data-id='$this_poll_id' data-href='" . stripslashes($options['redirect_url']) . "' data-delay='{$options['redirection_delay']}'>" . __('Redirecting after', $this->plugin_name)
							            . " <b>{$this->secondsToWords($options['redirection_delay'])}</b>
                                        </p>
                                    </div>";
						}
					}
				}
			}
			
			$show_res_button = !is_user_logged_in() && !$show_login_form ? true : false;
			
			if (is_user_logged_in() || $show_res_button ) {
				$content .= $vote_reason;
				if($limit_users > 0){
					$content .= wp_nonce_field('ays_finish_poll', 'ays_finish_poll');
				}
				else{
					$content .= wp_nonce_field('ays_finish_poll', 'ays_finish_poll') . "<div class='apm-button-box'>";
				}
				if (!empty($options['allow_not_vote']) && $limit_users == 0 && $poll['type'] != "text") {
					$content .= "<input type='button' class='btn ays-poll-btn {$poll['type']}-btn ays-see-res-button-show' data-form='$this_poll_id' value='" . $ays_see_result_button_text . "' data-seeRes='true' >";
				}
				elseif($limit_users > 0){
					if($poll_see_result_button_check){
						if($poll_show_result_button_limit){
							$content .= "<div class='apm-button-box'>";
							if($poll['type'] != "text"){
								$content .= 	"<input type='button' class='btn ays-poll-btn {$poll['type']}-btn ays-see-res-button-show' data-form='$this_poll_id' value='" . $ays_see_result_button_text . "' data-seeRes='true' >";
							}
							$content .= "</div>";

						}
						elseif($poll_see_result_immediately){							
							$result_content = $this->ays_poll_get_results($id);
							$content .= $result_content;
							if($poll_show_avatars){
								$content .= "<script>
									var resLoader = '".POLL_MAKER_AYS_PUBLIC_URL."/images/tail-spin.svg';
									var idChecker = 'ays-poll-container-" . $id . "';
								</script>";
							}
						}
						
					}
				}
				if ($limit_users == 0 && $load_poll) {

					$ays_vote_button = (isset($options['btn_text']) && $options['btn_text'] != '') ? stripslashes($options['btn_text']) : 'Vote';

					if ($ays_vote_button === 'Vote') {
			            $ays_vote_button_text =  __("Vote", $this->plugin_name);
			        }else{
			            $ays_vote_button_text = $ays_vote_button;
			        }

					$content .= "<input type='button' 
	                    name='ays_finish_poll'
	                    class='btn ays-poll-btn {$poll['type']}-btn ays_finish_poll'
	                    data-form='$this_poll_id'
	                    " . (!$load_poll ? "data-allow='false'" : "") .
					            'value="' . $ays_vote_button_text . '"
	                    >';
					
				}
			}
			if(!$limit_users > 0){
				$content .= '</div>';
			}
			
			$content .= $result_message;
			$content .= $redirect_after_vote;
		} elseif ($is_start_soon) {
			$poll_is_start_message = isset($options['active_date_message_soon']) ? stripslashes($options['active_date_message_soon']) : "<p>" . __('The poll will be available soon.', $this->plugin_name) . "</p>";
			$content              .= "<div class='apm_expired_poll'>".$poll_is_start_message."</div>";

		} else {
			$expired_poll_message = isset($options['active_date_message']) ? stripslashes($options['active_date_message']) : "<p>" . __('The poll has expired.', $this->plugin_name) . "</p>";
			$content              .= "<div class='apm_expired_poll'>$expired_poll_message</div>";
			
			if ($show_res_btn_sch) {
				$content .=  $see_result_button;
			}

		}
		$poll_options = $options;
		$content .= "<script>";
			$content .= "
				if(typeof aysPollOptions === 'undefined'){
					var aysPollOptions = [];
				}
				aysPollOptions['".$this_poll_id."']  = '" . base64_encode(json_encode($poll_options)) . "';";
		$content .= "
			</script>";
		$content .= '</div></form></div>';
		if ($echo) {
			echo $content;
		} else {
			return $content;
		}
	}

	public function ays_poll_category_generate_html($attr, $echo = true){
		$id = absint($attr['cat_id']);
		global $wpdb;
		$poll_table = esc_sql($wpdb->prefix . "ayspoll_polls");
		$like = '%' . $wpdb->esc_like( $id ) . '%';
		$sql    = "SELECT id FROM ".$poll_table." WHERE categories LIKE %s;";
		$result = $wpdb->get_results(
			   	  	$wpdb->prepare( $sql, $like),
			   	  	'ARRAY_A'
				  );

		$cat = $this->ays_get_poll_category($id);
		if (empty($cat)) {
			return "";
		}

		//AV Check expired polls
		$check_poll = false;
		$new_res = array();
		$checker = array();
		$widths  = array();
		foreach ($result as $key => $value) {
			$check_poll = $this->check_shedule_expired_poll( $value['id'] );
			
			$polls = $this->get_poll_by_id($value['id']);
			$widths[] = $polls['styles']['width']."px";
			if ($check_poll) {
				$new_res[] = $result[$key];
			}
			$checker[] = $check_poll;
		}
		if(isset($widths[0]) && $widths[0] == "0px"){
			$widths[0] = "98%";
		}
		$cat_opt    = json_decode($cat['options'], true);
		$default_message = 'The polls that belong to this category are expired or unpublished';
		$exp_message = isset($cat_opt['exp_message']) && $cat_opt['exp_message'] != '' ? stripslashes(esc_html($cat_opt['exp_message'])) : $default_message;
		if(array_sum($checker) == 0){
			echo "<div class='ays_exp_cat_message'>".$exp_message."</div>";
		}

		if (empty($new_res)) {
			return "";
		}

		$polls_pool = $this->ays_get_polls_pool($new_res);

		

		$ays_next_button = (isset($cat_opt['next_text']) && $cat_opt['next_text'] != '') ? stripslashes($cat_opt['next_text']) : 'Next';

		if ($ays_next_button === 'Next') {
            $ays_next_button_text =  __("Next", $this->plugin_name);
        }else{
            $ays_next_button_text = $ays_next_button;
		}
		
		$ays_previous_button = (isset($cat_opt['previous_text']) && $cat_opt['previous_text'] != '') ? stripslashes($cat_opt['previous_text']) : 'Previous';

        $ays_previous_button_text = '';
        if ($ays_previous_button === 'Previous') {
            $ays_previous_button_text =  __("Previous", $this->plugin_name);
        }else{
            $ays_previous_button_text = $ays_previous_button;
        }


		$show_next  = isset($cat_opt['allow_skip']) && $cat_opt['allow_skip'] == 'allow' ? 'true' : 'false';
		$show_next_val = ($show_next == 'true') ? 'true' : 'false'; 
		$cat_id     = uniqid('ays-poll-category-pool-');
		$j          = uniqid('JsVariable');
		$content    = "
            <div style='margin:1rem auto;' class='ays_poll_category-container' data-var='".$j."' id='$cat_id'>
			</div>
			<style>
                #".$cat_id." div.previous_next_buttons{
                    display:flex;
					width: ".$widths[0].";
					max-width: 98%;
                }
            </style>
            <script>
                var catContainer".$j." = '".$cat_id."';
                var pollsGlobalPool".$j." = ".$polls_pool.";
                var showNext".$j." = ".$show_next.";
                var showNextVal".$j." = ".$show_next_val.";
                var catIndex".$j." = 0;
				var aysPollBtnText".$j." = '" . $ays_next_button_text . "';
				var showPrev".$j." = false;
				var aysPollPreviousBtnText".$j." = '" . $ays_previous_button_text . "';
				var aysPollWidths".$j." = '" . json_encode($widths) . "';
            </script>";		

		if ($echo) {
			echo $content;
		} else {
			return $content;
		}
	}

	private function get_poll_by_id( $id ) {
		global $wpdb;
		$args_id = absint(intval($id));
		$poll_table = esc_sql($wpdb->prefix."ayspoll_polls");
		$sql  = "SELECT * FROM ".$poll_table." WHERE id=%d";
		$ordering = 'ORDER BY ordering ASC, id ASC';
		$poll = $wpdb->get_row(
		   	  		$wpdb->prepare( $sql, $args_id),
			   	  	'ARRAY_A'
			  	);

		if (empty($poll)) {
			return $poll;
		}
		
		$json            = $poll['styles'];
		$poll['styles']  = json_decode($json, true);
		$poll_type = (isset($poll['type']) && $poll['type'] != '') ? $poll['type'] : '';
		if($poll_type == 'choosing'){
			$answer_ordering = isset($poll['styles']['answer_sort_type']) && $poll['styles']['answer_sort_type'] != '' ? $poll['styles']['answer_sort_type'] : '';		
			if($answer_ordering != ''){
				switch ($answer_ordering){
					case 'ascending':
						$ordering = 'ORDER BY answer ASC';
					break;
					case 'descending':
						$ordering = 'ORDER BY answer DESC';
					break;
					case 'votes_asc':
						$ordering = 'ORDER BY votes DESC, id ASC';
					break;
					case 'votes_desc':
						$ordering = 'ORDER BY votes ASC, id ASC';
					break;
					default:
						$ordering = "ORDER BY ordering ASC, id ASC";
					break;
				}
			}
		}
		$answ_table = esc_sql($wpdb->prefix."ayspoll_answers");
		$sql = "SELECT * FROM ".$answ_table." WHERE poll_id=%d AND show_user_added = %d ".$ordering;

		$poll['answers'] = $wpdb->get_results(
					   	  		$wpdb->prepare( $sql, $args_id, 1),
					   	  		'ARRAY_A'
						   );

		return $poll;
	}

	public function get_poll_results_count_by_id( $id ) {
		global $wpdb;

		$args_id = absint(intval($id));
		$rep_table = esc_sql($wpdb->prefix."ayspoll_reports");
		$answ_table = esc_sql($wpdb->prefix."ayspoll_answers");

		$sql = "SELECT COUNT(*) AS res_count
                FROM ".$rep_table."
                INNER JOIN ".$answ_table." 
                ON ".$answ_table.".id=".$rep_table.".answer_id
                WHERE ".$answ_table.".poll_id = %d";

        $poll = $wpdb->get_row(
		   	  		$wpdb->prepare( $sql, $args_id),
			   	  	'ARRAY_A'
			  	);

		return $poll;
	}

	private static function get_user_ip() {
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP')) {
			$ipaddress = getenv('HTTP_CLIENT_IP');
		} else if (getenv('HTTP_X_FORWARDED_FOR')) {
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		} else if (getenv('HTTP_X_FORWARDED')) {
			$ipaddress = getenv('HTTP_X_FORWARDED');
		} else if (getenv('HTTP_FORWARDED_FOR')) {
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		} else if (getenv('HTTP_FORWARDED')) {
			$ipaddress = getenv('HTTP_FORWARDED');
		} else if (getenv('REMOTE_ADDR')) {
			$ipaddress = getenv('REMOTE_ADDR');
		} else {
			$ipaddress = 'UNKNOWN';
		}

		return $ipaddress;
	}

	public function secondsToWords( $seconds ) {
		$ret = "";
		/*** get the days ***/
		$days = intval(intval($seconds) / (3600 * 24));
		if ($days > 0) {
			$ret .= "$days days ";
		}
		/*** get the hours ***/
		$hours = (intval($seconds) / 3600) % 24;
		if ($hours > 0) {
			$ret .= "$hours hours ";
		}
		/*** get the minutes ***/
		$minutes = (intval($seconds) / 60) % 60;
		if ($minutes > 0) {
			$ret .= "$minutes minutes ";
		}
		/*** get the seconds ***/
		$seconds = intval($seconds) % 60;
		if ($seconds > 0) {
			$ret .= "$seconds seconds";
		}

		return $ret;
	}

	public function ays_get_polls_pool( $array_of_poll_id ) {
		$pool = array();
		foreach ( $array_of_poll_id as $poll ) {
			$pool[] = $this->ays_poll_generate_html($poll['id'], false);
		}

		return json_encode($pool);
	}

	public function ays_get_poll_category( $id ) {
		global $wpdb;
		$cat_id = absint(intval($id));
		$cat_table = esc_sql($wpdb->prefix."ayspoll_categories");
		$sql = "SELECT * FROM ".$cat_table." WHERE id=%d";
		$result = $wpdb->get_row(
			   	  	$wpdb->prepare( $sql, $cat_id),
			   	  	'ARRAY_A'
				  );

		return $result;
	}

	public function ays_finish_poll() {
		global $wpdb;
		if (wp_verify_nonce($_POST["ays_finish_poll"], 'ays_finish_poll')) {
			// $poll_id   = absint($_POST['poll_id']);
			// $answer_id = (isset($_POST['answer']) && $_POST['answer'] !== null) ? $_POST['answer'] : 0;
			$poll_id   = absint($_POST['poll_id']);
			$poll_id = absint($_POST['poll_id']);

			$title_ids = array();
            $answer_titless = array();
			if (isset($_POST['answer'])) {
				if( is_array($_POST['answer']) ){
					$answer_id = array_map('absint', $_POST['answer']);
					$multi_answer_id = array_map('absint', $_POST['answer']);
                	$title_ids = implode(',' , $multi_answer_id);
				}else{
					$answer_id = absint( $_POST['answer'] );

					$answer_id2 = array();
					$answer_id2[] = $answer_id;
	                $multi_answer_id = array();
	                $title_ids = implode(',' , $answer_id2);
				}
			}else{
				$answer_id = 0;
				$answer_id2[] = false;
			}

			if(!is_array($answer_id)){
				$answer_id = absint($answer_id);
			}

			// $title_ids = array();
            // $answer_titless = array();
			// if (isset($_POST['answer']) && is_array($_POST['answer'])) {
			// 	$answer_id = $_POST['answer'];
			// 	$multi_answer_id = $_POST['answer'];
            //     $title_ids = implode(',' , $multi_answer_id);
			// } else {
			// 	$answer_id2 = array();
			// 	$answer_id2[]  = !isset($_POST['answer']) || $_POST['answer'] === null ? false : absint($_POST['answer']);
            //     $multi_answer_id = array();
            //     $title_ids = implode(',' , $answer_id2);
            // }
            if(!empty($title_ids)){
                $answer_titles = $this->get_answer_by_ids($title_ids);
                if(isset($answer_titles) && $answer_titles != null){
                    foreach($answer_titles as $t_key => $t_value){
                        foreach ($t_value as $r_key => $r_value) {
                            if($r_key == 'answer'){
                                $answer_titless[] = $r_value;
                            }
                        }
                    }
                }
            }


            $poll    = $this->get_poll_by_id($poll_id);
			$options = $poll['styles'];
			$poll_answers_count  = isset($poll['answers']) ? count($poll['answers']) : 0;
			$added_answer_id = array();

			$poll_title = (isset($poll['title']) && $poll['title'] != '')  ? stripslashes( sanitize_text_field( $poll['title'] ) ) : '';
			$poll_vote_reason_text = "";
			$poll_vote_reason = false;
			$show_answers_numbering = (isset($options['show_answers_numbering']) && sanitize_text_field( $options['show_answers_numbering'] ) != '') ? sanitize_text_field( $options['show_answers_numbering'] ) : 'none';
			if (isset($options['poll_vote_reason']) && $options['poll_vote_reason'] == "on" && isset($_POST['ays-poll-reason-text'])) {
				// $poll_vote_reason_text = $_POST['ays-poll-reason-text'];
				$poll_vote_reason_text = wp_kses_post( $_POST['ays-poll-reason-text'] );
				$poll_vote_reason = true;
			}
			
			$allow_multi_vote = isset($options["poll_allow_multivote"]) && $options["poll_allow_multivote"] == "on" ? true : false; 
			$allow_add_answer = isset($options["poll_allow_answer"]) && $options["poll_allow_answer"] == "on" ? true : false;
			$check_admin_approval = false;
			$flag_for_added_answer = false;
			if($allow_add_answer && (isset($_POST['ays_poll_new_answer']) && $_POST['ays_poll_new_answer'] != "")){
				$flag_for_added_answer = true;
				$poll_allow_answer_require = isset($options['poll_allow_answer_require']) && $options['poll_allow_answer_require'] == "on" ? true : false;
				if($poll_allow_answer_require){
					$check_admin_approval = true;
				}
				// $poll_answers_count  = isset($poll['answers']) ? count($poll['answers']) : 0; 
				$new_anwer_data = array(
					'poll_id'       => $poll_id,
					'new_answer'    => sanitize_text_field($_POST['ays_poll_new_answer']),
					'admin_require' => $poll_allow_answer_require,
					'answers_count' => $poll_answers_count,
					'if_text_type'  => false
				);
				
				$added_answer_id = $this->ays_add_answer_poll($new_anwer_data);
				
				if((is_array($answer_id) && !empty($answer_id)) && $allow_multi_vote){
					$answer_id[] = $added_answer_id['new_id'];
				}
				elseif(isset($answer_id) && !is_array($answer_id) && $answer_id > 0  && $allow_multi_vote){						
					$answer_id = explode(" " , $answer_id);
					array_push($answer_id , $added_answer_id['new_id']);
				}
				else{
					$answer_id = $added_answer_id['new_id'];
				}
			}
			$poll_type_for_text_type = isset($poll['type']) && $poll['type'] == "text" ? true : false; 
			if($poll_type_for_text_type){
				$text_anwer_data = array(
					'poll_id'       => $poll_id,
					'new_answer'    => isset($_POST['answer']) && $_POST['answer'] != '' ? sanitize_text_field($_POST['answer']) : "",
					'admin_require' => false,
					'answers_count' => $poll_answers_count,
					'if_text_type'  => true
				);
				
				$added_answer_id = $this->ays_add_answer_poll($text_anwer_data);
				$answer_id = $added_answer_id['new_id'];
			}
			if ($answer_id > 0 || is_array($answer_id)) {
				if (isset($options['limit_users']) && $options['limit_users'] == 1) {
					$user_id = is_user_logged_in() ? wp_get_current_user()->ID : 0;
					$limit_users_method = isset($options['limit_users_method']) ? sanitize_text_field($options['limit_users_method']) : 'ip';
					$user_voted_count = 0;
					if ($limit_users_method == 'ip') {
						$user_voted_count = $this->ays_poll_get_limit_user_count_by_ip($poll_id);
					}					
					elseif($limit_users_method == 'cookie'){
						$cookie_name = "ays_this_poll_cookie_".$poll_id;
						if(!isset($_COOKIE[$cookie_name])){
							$cookie_value = "ays_vote_limitation_cookie_value";
							$time_limit = time() + (86400 * 30);
							setcookie($cookie_name, $cookie_value,$time_limit , '/');
						}else{							
							$user_voted_count = 1;
						}		
					}			
					elseif($limit_users_method == 'cookie_ip'){
						$cookie_name = "ays_this_poll_cookie_".$poll_id;
						$limit_voted_count = $this->ays_poll_get_limit_user_count_by_ip($poll_id);
						if(!isset($_COOKIE[$cookie_name])){
							$cookie_value = "ays_vote_limitation_cookie_value";
							$time_limit = time() + (86400 * 30);
							setcookie($cookie_name, $cookie_value,$time_limit , '/');
						}
						else{
							$user_voted_count = 1;
						}
						if(isset($_COOKIE[$cookie_name]) || $limit_voted_count > 0){
							$user_voted_count = 1;
						}
						elseif(!isset($_COOKIE[$cookie_name]) || $limit_voted_count > 0){
							$user_voted_count = $this->ays_poll_get_limit_user_count_by_ip($poll_id);
						}
					}			
					else{
						if ($user_id != 0) {
							$user_voted_count = $this->ays_poll_get_limit_user_count_by_id($poll_id,$user_id);
						}else{
							$user_voted_count = $this->ays_poll_get_limit_user_count_by_ip($poll_id);
						}
					}

					$user_voted_count = absint(intval(($user_voted_count))); 

					if ( $user_voted_count > 0 ) {
						$res = $this->get_poll_by_id($poll_id);
						$res['voted_status'] = false;
						ob_end_clean();
						$ob_get_clean = ob_get_clean();
						echo json_encode($res);
						wp_die();
					}
				}

				if (!empty($options['info_form'])) {
	                $user_email = isset($_POST["apm_email"]) && $_POST['apm_email'] !== null ? sanitize_email($_POST["apm_email"]) : '';
				}else{
					$user_email = '';
				}

				$check_allowing = (isset($options['poll_allow_collecting_users_data']) && $options['poll_allow_collecting_users_data'] == 'on') ? true : false;
				$check_fields   = (isset($options['info_form']) && $options['info_form'] == 1) ? true : false;
				if($check_allowing && !$check_fields){
					$this_user = wp_get_current_user();
					if($this_user->ID != 0){
						$_POST["apm_email"] = $this_user->data->user_email;
						$user_email = isset($_POST["apm_email"]) ? sanitize_email($_POST["apm_email"]) : "";
						$_POST['apm_name'] = $this_user->data->display_name;
					}                
				}
                // MailChimp

                if (isset($options['enable_mailchimp']) && $options['enable_mailchimp'] == 'on') {
                    if (isset($options['mailchimp_list']) && $options['mailchimp_list'] != "") {
                        $poll_settings = $this->settings;
                        $mailchimp_res = ($poll_settings->ays_get_setting('mailchimp') === false) ? json_encode(array()) : $poll_settings->ays_get_setting('mailchimp');
                        $mailchimp = json_decode($mailchimp_res, true);
                        $mailchimp_username = isset($mailchimp['username']) ? $mailchimp['username'] : '';
                        $mailchimp_api_key = isset($mailchimp['apiKey']) ? $mailchimp['apiKey'] : '';
                        $mailchimp_list = (isset($options['mailchimp_list'])) ? $options['mailchimp_list'] : '';
                        $mailchimp_email = $user_email;
                        $user_name = isset($_POST['apm_name']) ? explode(" ", wp_filter_post_kses($_POST['apm_name'])) : array();
                        $mailchimp_fname = (isset($user_name[0]) && $user_name[0] != "") ? $user_name[0] : "";
                        $mailchimp_lname = (isset($user_name[1]) && $user_name[1] != "") ? $user_name[1] : "";
                        $user_phone = isset($_POST['apm_phone']) ? explode(" ", wp_filter_post_kses($_POST['apm_phone'])) : array();
                        $mailchimp_phone = (isset($user_phone[0]) && $user_phone[0] != "") ? $user_phone[0] : "";
                        if ($mailchimp_username != "" && $mailchimp_api_key != "") {
                            $args = array(
                                "email" => $mailchimp_email,
                                "fname" => $mailchimp_fname,
                                "lname" => $mailchimp_lname,
                                "pnumber" => $mailchimp_phone,
                            );
                            $mresult = $this->ays_add_mailchimp_transaction($mailchimp_username, $mailchimp_api_key, $mailchimp_list, $args);
                        }
                    }
                }

				// $answer = $this->get_answer_by_id($answer_id);

				// $votes  = isset($answer['voted']) && $answer['voted'] !== null ? $answer['voted'] : 0;
				// $votes++;
				
				$other_info = array(
					"name"  => "",
					"email" => "",
					"phone" => "",
				);
				
				if (!empty($options['info_form']) || $check_allowing) {
					$other_info = array(
						"name"  => !empty($_POST['apm_name']) ? sanitize_text_field($_POST['apm_name']) : "",
						"email" => !empty($_POST['apm_email']) ? sanitize_email($_POST['apm_email']) : "",
						"phone" => !empty($_POST['apm_phone']) ? sanitize_text_field($_POST['apm_phone']) : "",
					);
				}
				if($poll_vote_reason){
					$other_info['vote_reason'] = $poll_vote_reason_text;
				}
				// global $wpdb;
				$answ_table = esc_sql($wpdb->prefix."ayspoll_answers");
				$report_table = esc_sql($wpdb->prefix."ayspoll_reports");

				// AV  IP Storing
				$settings_table = esc_sql($wpdb->prefix."ayspoll_settings");
				$key_meta = esc_sql('options');
				$sql_ip = "SELECT meta_value FROM ".$settings_table." WHERE meta_key = %s";
        		$res_ip = $wpdb->get_var(
                    $wpdb->prepare( $sql_ip, $key_meta)
                );

        		$options_res = ($res_ip === false) ? json_encode(array()) : $res_ip;
				$option_res = json_decode($options_res, true);

				$user_ips = isset($option_res['disable_ip_storing']) && $option_res['disable_ip_storing'] == 'on' ? '' : $this->get_user_ip();

				
				$user_ip = esc_sql($user_ips);
				$multi_answer_ids = array();
				if((is_array($answer_id) && !empty($answer_id)) && $allow_multi_vote){
					$multi_answer_ids = $answer_id;
					$answer_changed_id = $answer_id[0];
					foreach($answer_id as $a_key => $a_id){
						$this_answer = $this->get_answer_by_id_multi($a_id);
						if(isset($this_answer[0])){
							$votes_new  = isset($this_answer[0]['votes']) ? intval($this_answer[0]['votes']) : 0;
							$votes_new++;
							$wpdb->update(
								$answ_table,
								array('votes' => $votes_new),
								array('id' => $a_id),
								array('%d'),
								array('%d')
							);

						}
						
					}
				}
				else{
					$answer_changed_id = $answer_id;
					$multi_answer_ids[] = $answer_changed_id;
					$answer = $this->get_answer_by_id($answer_id);
					$votes  = isset($answer['votes']) && $answer['votes'] !== null ? intval($answer['votes']) : 0;
					$votes++;
					$wpdb->update(
						$answ_table,
						array('votes' => $votes),
						array('id' => $answer_id),
						array('%d'),
						array('%d')
					);
				}
				$wpdb->insert(
					$report_table,
					array(
						'answer_id'  => $answer_changed_id,
						'user_ip'    => $user_ip,
						'user_id'    => is_user_logged_in() ? wp_get_current_user()->ID : 0,
						'vote_date'  => esc_sql( sanitize_text_field( $_REQUEST['end_date'] ) ),
						'other_info' => json_encode($other_info),
						'multi_answer_ids' => json_encode($multi_answer_ids)
					),
					array('%d', '%s', '%s', '%s', '%s' , '%s')
				);
				// $answers = $this->get_answer_by_id($answer_changed_id);
				if (!empty($options['notify_email_on'])) {
					$notify_admin_email = $options['notify_email'];
					$use_answered = '';
                    if(isset($answer_titless) && !empty($answer_titless)){
                        $use_answered = implode(', ', $answer_titless);
                    }
                    $subject =  $poll_title;
                    $headers = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                    $attachment = array();
					$mail_text = 
                    sprintf( __( "Someone answer %s in your %s poll on %s.", $this->plugin_name ), 
                        $use_answered,
                        '"' . $poll_title . '"',
                        "<a href='" . home_url() . "' target='_blank'>" . home_url() . "</a>"
                    );
					wp_mail($notify_admin_email, $subject, $mail_text, $headers, $attachment);
				}
				
			}
			$res = $this->get_poll_by_id($poll_id);
			$res['voted_status'] = true;
			$numbering_arr = array();
			// $res['numbering'] = "none";
			// if($show_answers_numbering != "none"){
			// 	$answer_count = isset($res['answers']) && !empty($res['answers']) ? count($res['answers']) : false;
			// 	if($answer_count){
			// 		$numbering_arr = $this->ays_answer_numbering($show_answers_numbering , $answer_count);
			// 		$res['numbering'] = $numbering_arr;
			// 	}
			// }
			$check_user = false;
			if(isset($options['show_passed_users']) && $options['show_passed_users'] == 'on'){
				$check_user = true;
				$poll_avatar_user_count = isset($options['poll_show_passed_users_count']) && $options['poll_show_passed_users_count'] != "" ? $options['poll_show_passed_users_count'] : 3;
				$results_table = $wpdb->prefix."ayspoll_reports";
				$all_answers = isset($res['answers']) && !empty($res['answers']) ? $res['answers'] : array();
				$answer_ids = array();
				if(isset($all_answers) && !empty($all_answers)){
					foreach($all_answers as $answer => $value){
						$answer_ids[] = $value['id'];					
					}
				}
				$answer_ids = implode(',' , $answer_ids);
				$sql_users = "SELECT `user_id` , `answer_id`
							  FROM ".$results_table."
							  WHERE `answer_id` IN (".$answer_ids.")
							  GROUP BY user_id, answer_id
							  ORDER BY vote_date DESC";
							  $user_res = $wpdb->get_results($sql_users , ARRAY_A);
				$user_pic_args = array(
					"class" => "ays-user-profile-pic"
				);
				$users_res_array = array();
				foreach($user_res as $ar){
					// if( isset( $users_res_array[$ar['answer_id']] ) ){
					// 	continue;
					// }
					$users_res_array[$ar['answer_id']][] = intval( $ar['user_id'] );
				}

				if(isset($all_answers) && !empty($all_answers)){		
					foreach($all_answers as $key => $answer){
						$user_answers = array();
						if( array_key_exists( $answer['id'], $users_res_array ) ){
								$user_answers = $this->ays_poll_get_avatars($answer['id'] , $users_res_array);
						}
						$res['answers'][$key]['avatar'] = $user_answers;
					}	
				}
				$res['check_user_pic'] = $check_user;
				$res['check_user_pic_count'] = $poll_avatar_user_count;
				$res['check_user_pic_url'] = POLL_MAKER_AYS_PUBLIC_URL.'/images/more.png';
				$res['check_user_pic_loader'] = POLL_MAKER_AYS_PUBLIC_URL.'/images/tail-spin.svg';
			}
			$res['check_admin_approval'] = $check_admin_approval;
			ob_end_clean();
			$ob_get_clean = ob_get_clean();
			echo json_encode($res);
			wp_die();
		}
	}

	public function ays_poll_get_avatars($answer , $users){
		$user_answers = array();
		$user_pic_args = array(
			"class" => "ays-user-profile-pic"
		);
		foreach($users[$answer] as $res_key => $res_value){
			if($res_value == 0){
				continue;
			}
			$user_avatars = get_avatar($res_value, 24, $default = '', $alt = '', $user_pic_args);
			$user_avatars = isset($user_avatars) && $user_avatars ? $user_avatars : '' ;
			$user_answers[] = "<div class='ays-users-profile-pics'>".$user_avatars."</div>";								
		}
		return $user_answers;
	}

    public function ays_add_mailchimp_transaction( $username, $api_key, $list_id, $args ) {

        $email = isset($args['email']) ? $args['email'] : null;
        $fname = isset($args['fname']) ? $args['fname'] : "";
        $lname = isset($args['lname']) ? $args['lname'] : "";
        $phone = isset($args['pnumber']) ? $args['pnumber'] : "";

        $api_prefix = explode("-", $api_key)[1];

        $fields = array(
            "email_address" => $email,
            "status"        => "subscribed",
            "merge_fields"  => array(
                "FNAME" => $fname,
                "LNAME" => $lname,
                "PHONE" => $phone
            )
        );
        $curl   = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => "https://" . $api_prefix . ".api.mailchimp.com/3.0/lists/" . $list_id . "/members/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_USERPWD        => "$username:$api_key",
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => json_encode($fields),
            CURLOPT_HTTPHEADER     => array(
                "Content-Type: application/json",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #: " . $err;
        } else {
            return $response;
        }
    }

    private function get_answer_by_id( $id ) {
		global $wpdb;
        $args_id = absint(intval($id));
        $answ_table = esc_sql($wpdb->prefix."ayspoll_answers");
        $rep_table = esc_sql($wpdb->prefix."ayspoll_reports");
		$sql = "SELECT a.*, COUNT(r.id) as voted FROM ".$answ_table." as a
                JOIN ".$rep_table." as r
                ON FIND_IN_SET(a.id , r.answer_id)
                WHERE a.id = %d";
		$answ_id = $wpdb->get_row(
			   	  		$wpdb->prepare( $sql, $args_id),
				   	  	'ARRAY_A'
				  	);

		return $answ_id;
	}

	public static function get_answer_by_ids( $ids ) {
		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}ayspoll_answers
                WHERE id IN (".$ids.")";
		return $wpdb->get_results($sql, 'ARRAY_A');
    }

    public function check_shedule_expired_poll( $args ) {
    	$id = absint(intval($args));
    	$poll_options = $this->get_poll_by_id($id);
		$options = $poll_options['styles'];
			
		$is_expired = true;
		$startDate = '';
		$endDate = '';
		$current_time = strtotime(current_time( "Y:m:d H:i:s" ));
		if (isset($options['active_date_check']) && !empty($options['active_date_check'])) {
			if (isset($options['activeInterval']) && isset($options['deactiveInterval'])) {
				if (isset($options['activeIntervalSec']) && !empty($options['activeIntervalSec'])) {
					$startDate = strtotime($options['activeInterval']." ".$options['activeIntervalSec']);							
				}
				else{
					$startDate = strtotime($options['activeInterval']);					
				}

				if (isset($options['deactiveIntervalSec']) && !empty($options['deactiveIntervalSec'])) {
					$endDate   = strtotime($options['deactiveInterval']." ".$options['deactiveIntervalSec']);					
				}
				else{
					$endDate   = strtotime($options['deactiveInterval']);					
				}
								
				if ($startDate < $current_time && $endDate > $current_time) {
					$is_expired = true;
				}else{
					$is_expired = false;
				}
			}
		}
		$published = (isset($options['published']) && intval($options['published']) === 0) ? false : true;
		return $is_expired && $published ? true : false;
	}

	protected function hex2rgba($color, $opacity = false){

        $default = 'rgb(0,0,0)';

        //Return default if no color provided
        if (empty($color))
            return $default;

        //Sanitize $color if "#" is provided
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }else{
            return $color;
        }

        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
            $hex = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
        } elseif (strlen($color) == 3) {
            $hex = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
        } else {
            return $default;
        }

        //Convert hexadec to rgb
        $rgb = array_map('hexdec', $hex);

        //Check if opacity is set(rgba or rgb)
        if ($opacity) {
            if (abs($opacity) > 1)
                $opacity = 1.0;
            $output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
        } else {
            $output = 'rgb(' . implode(",", $rgb) . ')';
        }

        //Return rgb(a) color string
        return $output;
    }

    public function rgb2hex( $rgb ) {
		if ($rgb[0] == '#') {
			return $rgb;
		}
		$colors = explode(',', rtrim(explode('(', $rgb)[1], ')'));

		return sprintf("#%02x%02x%02x", $colors[0], $colors[1], $colors[2]);
	}

    public static function ays_poll_get_limit_user_count_by_id($poll_id, $user_id){
	    global $wpdb;

	    $id = absint(intval($poll_id));
		$reports_table = esc_sql($wpdb->prefix."ayspoll_reports");
		$answers_table = esc_sql($wpdb->prefix."ayspoll_answers");

		$sql = "SELECT COUNT(*)
		        FROM ".$reports_table."
                INNER JOIN ".$answers_table." 
                ON ".$answers_table.".id=".$reports_table.".answer_id
                WHERE ".$answers_table.".poll_id = ".$id." AND ".$reports_table.".user_id = ".$user_id;
        
	    $result = intval($wpdb->get_var($sql));

	    return $result;
	}

	public static function ays_poll_get_limit_user_count_by_ip($poll_id){
	    global $wpdb;
	    $id = absint(intval($poll_id));
	    $user_ip = self::get_user_ip();
		$reports_table = esc_sql($wpdb->prefix."ayspoll_reports");
		$answers_table = esc_sql($wpdb->prefix."ayspoll_answers");

		$sql = "SELECT COUNT(*)
		        FROM ".$reports_table."
                INNER JOIN ".$answers_table." 
                ON ".$answers_table.".id=".$reports_table.".answer_id
                WHERE ".$answers_table.".poll_id = ".$id." AND ".$reports_table.".user_ip = '". $user_ip ."'";
        
	    $result = intval($wpdb->get_var($sql));

	    return $result;
	}

	public function ays_poll_get_results($id) {

		global $wpdb;
		$content = '';
		$id = absint(intval($id));
		$answ_table = esc_sql($wpdb->prefix."ayspoll_answers");
		$polls_table = esc_sql($wpdb->prefix."ayspoll_polls");
		

		$poll_sql  = "SELECT * FROM ".$polls_table." WHERE id =%d";
		$polls = $wpdb->get_row(
			   	  	$wpdb->prepare( $poll_sql, $id),
			   	  	'ARRAY_A'
				  );
		$votes_count = $this->get_poll_results_count_by_id($id);
		$poll = $this->get_poll_by_id($id);
		$polls_options = $poll['styles'];
		
		
		$poll_answer_sorting = isset($polls_options['result_sort_type']) && $polls_options['result_sort_type'] != "none" ? $polls_options['result_sort_type'] : "ASC";
		$ans_sql  = "SELECT * FROM ".$answ_table." WHERE poll_id =%d ORDER BY votes ".$poll_answer_sorting;
		$poll_answers = $wpdb->get_results(
			   	  	$wpdb->prepare( $ans_sql, $id),
			   	  	'ARRAY_A'
				  );
		if ($polls == null) {
			$content = '<p style="text-align:center;">No ratings yet</p>';
		}else{
			// $votes_count = $this->get_poll_results_count_by_id($id);
			// $poll = $this->get_poll_by_id($id);
			// $polls_options = $poll['styles'];
			$content .= "<div class='results-apm'>";
	        if (intval($votes_count['res_count']) > 0) {
				$one_percent = 100/intval($votes_count['res_count']);
	        }else{
	        	$one_percent = 1;
			}
			
			$poll_show_answer_perc = isset($polls_options['show_res_percent']) && $polls_options['show_res_percent'] == 1 ? true : false;
			$poll_show_votes_count = isset($polls_options['show_votes_count']) && $polls_options['show_votes_count'] == 1 ? true : false;
			$poll_main_color = isset($polls_options['main_color']) && $polls_options['main_color'] != '' ? esc_attr($polls_options['main_color']) : '';
			$poll_bg_color = isset($polls_options['bg_color']) && $polls_options['bg_color'] != '' ? esc_attr($polls_options['bg_color']) : '';
			$poll_hide_result = isset($polls_options['hide_results']) && $polls_options['hide_results'] == 1 ? true : false;
			$poll_hide_result_message_check = isset($polls_options['hide_result_message']) && $polls_options['hide_result_message'] == 1 ? true : false;
			$poll_hide_result_message = isset($polls_options['hide_results_text']) && $polls_options['hide_results_text'] != "" ? wpautop($polls_options['hide_results_text']) : "";
			$poll_show_avatars = isset($polls_options['show_passed_users']) && $polls_options['show_passed_users'] == "on" ? true : false;
			$poll_avatars_count = isset($polls_options['poll_show_passed_users_count']) && $polls_options['poll_show_passed_users_count'] != "" ? $polls_options['poll_show_passed_users_count'] : 3;
			if($poll_hide_result){
				$content .= "<div >" . $poll_hide_result_message . "</div>";
			}
			else{
				$poll_answers_count = count($poll_answers);

				if($poll_show_avatars){
					$results_table = $wpdb->prefix."ayspoll_reports";
					$answer_ids = array();
					if(isset($poll_answers) && !empty($poll_answers)){
						foreach($poll_answers as $answer => $value){
							$answer_ids[] = $value['id'];					
						}
					}
					$answer_ids = implode(',' , $answer_ids);
					$sql_users = "SELECT `user_id` , `answer_id`
								  FROM ".$results_table."
								  WHERE `answer_id` IN (".$answer_ids.")
								  GROUP BY user_id, answer_id
								  ORDER BY vote_date DESC";
					$user_res = $wpdb->get_results($sql_users , ARRAY_A);
					$user_pic_args = array(
						"class" => "ays-user-profile-pic"
					);
					$users_res_array = array();
					foreach($user_res as $ar){
						$users_res_array[$ar['answer_id']][] = intval( $ar['user_id'] );
					}
	
					if(isset($poll_answers) && !empty($poll_answers)){		
						foreach($poll_answers as $key => $answer){
							$user_answers = array();
							if( array_key_exists( $answer['id'], $users_res_array ) ){
									$user_answers = $this->ays_poll_get_avatars($answer['id'] , $users_res_array);
							}
							$poll_answers[$key]['avatar'] = $user_answers;
						}	
					}
				}
				foreach ($poll_answers as $ans_key => $ans_val) {
					$poll_avatars_content = "";
					$poll_user_avatars = "";
					if(isset($ans_val["avatar"]) && !empty($ans_val["avatar"])){
						$x = array_splice($ans_val["avatar"] , 0 ,$poll_avatars_count);
						$poll_user_avatars = implode(" " , $x);

					}
					if($poll_show_avatars && $poll_user_avatars != ""){
						$poll_avatars_content = '<div class="ays-user-count">
													'.$poll_user_avatars.' 
													<div class="ays-users-profile-pics">
														<img src="'.POLL_MAKER_AYS_PUBLIC_URL.'/images/more.png" width="24" height="24" class="ays-user-image-more" data-answer-id='.$ans_val["id"].'>
													</div>
												</div>';
					}
 					$perc_cont = '';
					$percent = round($one_percent*intval($ans_val['votes']));
					if($poll_show_answer_perc){
						if ($percent == 0) {
							$perc_cont = '';
						}else{
							$perc_cont = $percent.' %';
						}

					}
					$answer_votes_count = '';
					if($poll_show_votes_count){
						$answer_votes_count = isset($ans_val['votes']) ? esc_attr($ans_val['votes']) : '';
					}
					switch ($polls['type']) {
						case 'choosing':
							$content .= '<div class="answer-title flex-apm">
											<span class="answer-text">'.stripslashes($ans_val['answer']).'</span>
											<span class="answer-votes">'.$answer_votes_count.'</span>
										</div>
										'.$poll_avatars_content.'
										<div class="answer-percent" style="width: '.$percent.'%; background-color: '.$poll_main_color.'; color: '.$poll_bg_color.';">'.$perc_cont.'</div>';
							break;

						case 'rating':
							switch ($polls['view_type']) {
								case 'star':
									$star_type  = '';
									for ($i=0; $i < intval($ans_val['answer']); $i++) { 
										$star_type .= '<i class="ays_poll_far ays_poll_fa-star far"></i>';
									}
									$content .= '<div class="answer-title flex-apm">
													<span class="answer-text">'.$star_type.'</span>
													<span class="answer-votes">'.$answer_votes_count.'</span>
												</div>
												'.$poll_avatars_content.'
												<div class="answer-percent" style="width: '.$percent.'%; background-color: '.$poll_main_color.'; color: '.$poll_bg_color.';">'.$perc_cont.'</div>';
									break;
								
								case 'emoji':
									$emojy_type  = '';
									if ($poll_answers_count == 3) {
										switch (intval($ans_val['answer'])) {
											case 1:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-frown far"></i>';
												break;
											case 2:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-meh far"></i>';
												break;
											case 3:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-smile far"></i>';
												break;
											default:
												break;
										}
									}else{
										switch (intval($ans_val['answer'])) {
											case 1:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-tired far"></i>';
												break;
											case 2:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-frown far"></i>';
												break;
											case 3:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-meh far"></i>';
												break;
											case 4:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-smile far"></i>';
												break;
											case 5:
												$emojy_type .= '<i class="ays_poll_far ays_poll_fa-dizzy far"></i>';
												break;
											default:
												break;
										}
									}

									$content .= '<div class="answer-title flex-apm">
													<span class="answer-text">'.$emojy_type.'</span>
													<span class="answer-votes">'.$answer_votes_count.'</span>
												</div>
												<div class="answer-percent" style="width: '.$percent.'%; background-color: '.$poll_main_color.'; color: '.$poll_bg_color.';">'.$perc_cont.'</div>';

									break;
								default:										
									break;
							}
							break;

						case 'voting':
							switch ($polls['view_type']) {
								case 'hand':
									$hand_type  = '';
									if (intval($ans_val['answer'] == 1)) {
										$hand_type = '<i class="ays_poll_far ays_poll_fa-thumbs-up far"></i>';
									}else{
										$hand_type = '<i class="ays_poll_far ays_poll_fa-thumbs-down far"></i>';
									}
									$content .= '<div class="answer-title flex-apm">
													<span class="answer-text">'.$hand_type.'</span>
													<span class="answer-votes">'.$answer_votes_count.'</span>
												</div>
												'.$poll_avatars_content.'
												<div class="answer-percent" style="width: '.$percent.'%; background-color: '.$poll_main_color.'; color: '.$poll_bg_color.';">'.$perc_cont.'</div>';
									break;
								
								case 'emoji':
									$emojy_type  = '';
									if (intval($ans_val['answer'] == 1)) { 
										$emojy_type = '<i class="ays_poll_far ays_poll_fa-smile far"></i>';
									}else{
										$emojy_type = '<i class="ays_poll_far ays_poll_fa-frown far"></i>';
									}
									$content .= '<div class="answer-title flex-apm">
													<span class="answer-text">'.$emojy_type.'</span>
													<span class="answer-votes">'.$answer_votes_count.'</span>
												</div>
												'.$poll_avatars_content.'
												<div class="answer-percent" style="width: '.$percent.'%; background-color: '.$poll_main_color.'; color: '.$poll_bg_color.';">'.$perc_cont.'</div>';

									break;
								default:										
									break;
							}
							break;
						default:										
							break;
					}
					
				}
			}
			
		}
		$content .= "</div>";
        return $content;
	}

	public function ays_answer_numbering($numbering , $count){
        $keyword_arr = array();
        switch ($numbering) {
            case '1.':
                $char_min_val = 1;
                for($x = $char_min_val; $x <= $count; $x++){
                    $keyword_arr[] = $x .".";
                }
                break;
            case '1)':
                $char_min_val = 1;
                for($x = $char_min_val; $x <= $count; $x++){
                    $keyword_arr[] = $x .")";
                }
                break;
            case 'A.':
                $columns = array();
					$keyword_arr = $this->ays_poll_generate_keyword_array($count);
					foreach($keyword_arr as $key => $value){
						$columns[] = $value . ".";
					}
					$keyword_arr = $columns;
                break;
            case 'A)':
					$columns = array();
					$keyword_arr = $this->ays_poll_generate_keyword_array($count);
					foreach($keyword_arr as $key => $value){
						$columns[] = $value . ")";
					}
					$keyword_arr = $columns;
                break;
            case 'a.':
                $columns = array();
					$keyword_arr = $this->ays_poll_generate_keyword_array($count);
					foreach($keyword_arr as $key => $value){
						$columns[] = strtolower($value) . ".";
					}
					$keyword_arr = $columns;
                break;
            case 'a)':
                $columns = array();
					$keyword_arr = $this->ays_poll_generate_keyword_array($count);
					foreach($keyword_arr as $key => $value){
						$columns[] = strtolower($value) . ")";
					}
					$keyword_arr = $columns;
                break;
            default:
                break;
        }
        return $keyword_arr;
	}
	public static function ays_poll_generate_keyword_array( $max_val ) {
        if (is_null($max_val) || $max_val == '') {
            $max_val = 6; //'F';
        }
        $max_val = absint(intval($max_val)) - 1;
        $keyword_arr = array();
        $letters = range('A', 'Z');
        if($max_val <= 25){
            $max_alpha_val = $letters[$max_val];
        }
        elseif($max_val > 25){
          $dividend = ($max_val + 1);
          $max_alpha_val = '';
          $modulo;
          while ($dividend > 0){
            $modulo = ($dividend - 1) % 26;
            $max_alpha_val = $letters[$modulo] . $max_alpha_val;
            $dividend = floor((($dividend - $modulo) / 26));
          }
        }
        $keyword_arr = self::ays_poll_create_columns_array( $max_alpha_val );
        return $keyword_arr;
    }
    public static function ays_poll_create_columns_array($end_column, $first_letters = '') {
        $columns = array();
        $letters = range('A', 'Z');
        $length = strlen($end_column);
        // Iterate over 26 letters.
        foreach ($letters as $letter) {
            // Paste the $first_letters before the next.
            $column = $first_letters . $letter;
            // Add the column to the final array.
            $columns[] = $column;
            // If it was the end column that was added, return the columns.
            if ($column == $end_column)
                return $columns;
        }
        // Add the column children.
        foreach ($columns as $column) {
            // Don't itterate if the $end_column was already set in a previous itteration.
            // Stop iterating if you've reached the maximum character length.
            if (!in_array($end_column, $columns) && strlen($column) < $length) {
              $new_columns = self::ays_poll_create_columns_array($end_column, $column);
              // Merge the new columns which were created with the final columns array.
              $columns = array_merge($columns, $new_columns);
            }
        }
        return $columns;
    }

	public function get_answer_by_id_multi( $id ) {
		global $wpdb;
        $args_id = absint(intval($id));
        $answ_table = esc_sql($wpdb->prefix."ayspoll_answers");
        $rep_table = esc_sql($wpdb->prefix."ayspoll_reports");
		$sql = "SELECT a.*, COUNT(r.id) as voted FROM ".$answ_table." as a
                JOIN ".$rep_table." as r
                ON r.answer_id = a.id
                WHERE a.id = %d";

		$answ_id = $wpdb->get_results(
			   	  		$wpdb->prepare( $sql, $args_id),
				   	  	'ARRAY_A'
				  	);

		return $answ_id;
    }

	// Users avatars
	public function ays_poll_get_current_answer_users_pics(){
		global $wpdb;
		$results_table = $wpdb->prefix."ayspoll_reports";
		
		$answer_id = isset($_POST['answer_id']) && $_POST['answer_id'] != "" ? absint($_POST['answer_id']) : null;
		$user_answers = array();

		if(isset($answer_id)){
			$sql_users = "SELECT `user_id`
							FROM ".$results_table."
							WHERE `answer_id` = ".$answer_id."
							GROUP BY user_id, answer_id
							ORDER BY vote_date DESC";
							$user_res = $wpdb->get_results($sql_users , ARRAY_A);
			if(isset($user_res)){
				$user_pic_args = array(
					"class" => "ays-user-profile-pic-popup"
				);
				
				foreach($user_res as $key => $value){
					$user_id = isset($value['user_id']) && $value['user_id'] != "" ? intval($value['user_id']) : 0;
					if($user_id == 0){
						continue;
					}
					
					$user_avatars = get_avatar($user_id, 24, $default = '', $alt = '', $user_pic_args);
					$user_data = get_userdata($user_id);
					$user_name = "";
					if(isset($user_data)){
						if(isset($user_data->data)){
							$user_name = isset($user_data->data->display_name) && $user_data->data->display_name != "" ? esc_attr($user_data->data->display_name) : ""; 
						}
					}
					$user_avatars = isset($user_avatars) && $user_avatars ? $user_avatars : '';
					$user_answers[] = "<div class='ays-users-profile-pics-popup'><div>".$user_avatars."</div><div class='ays-users-profile-pics-popup-text'><span class='ays-poll-modal-names'>".$user_name."</span></div></div>";	
				}
			}
		}
		echo json_encode($user_answers);
		wp_die();
	}

	public function ays_add_answer_poll($data) {
		global $wpdb;
		$poll_id    = absint($data['poll_id']);
		$new_answer = wp_filter_kses($data['new_answer']);
        $poll_add_answer_require = $data['admin_require'];
        $poll_answers_count = isset($data['answers_count']) && $data['answers_count'] > 0 ? intval($data['answers_count']) : 1;
        $if_text_type = isset($data['if_text_type']) && $data['if_text_type'] ? true : false;

        $show_user_added = 0;
		$votes = 0;
		if($if_text_type){
			$votes = 1;
		}
        if(!$poll_add_answer_require){
            $show_user_added = 1;
        }
		$wpdb->insert(
			"{$wpdb->prefix}ayspoll_answers",
			array(
				'poll_id'    => $poll_id,
				'answer'     => $new_answer,
				'votes'      => $votes,
				'ordering'   => ($poll_answers_count + 1),
				'user_added' => 1,
				'show_user_added' => $show_user_added
			),
			array(
				'%d',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
			)
		);

		$last_id = $wpdb->insert_id;
		return array(
				"new_id" => strval($last_id)
			);
	}

	public function ays_generate_display_polls_method($attr){
		$recent_poll_ids = $this->ays_recent_poll_ids($attr);
		$content = '<div class="ays_poll_recent_polls">';
		$polls = array();
        foreach ($recent_poll_ids as $key => $last_poll_id) {
            $poll_id = (isset($last_poll_id['id']) && intval($last_poll_id['id']) != '') ? intval($last_poll_id['id']) : '';
            $shortcode = '[ays_poll id="'.$poll_id.'"]';
            $polls[] = do_shortcode( $shortcode );
        }
        $content .= implode( '', $polls );
		$content .= '</div>';
		return str_replace(array("\r\n", "\n", "\r"), "\n", $content);
	}

	public function ays_recent_poll_ids($data){
		global $wpdb;
        $polls_table = $wpdb->prefix.'ayspoll_polls';

        $ays_recent_poll_order_by = (isset($data['orderby']) && $data['orderby'] != '') ? sanitize_text_field($data['orderby']) : "recent";
        $ays_recent_poll_count = (isset($data['count']) && $data['count'] != '') ? intval($data['count']) : 5;

        $last_polls_sql = "SELECT id FROM {$polls_table} WHERE styles LIKE '%\"published\":1%' ";

        switch ($ays_recent_poll_order_by) {
            case 'recent':
                $last_polls_sql .= "ORDER BY id DESC LIMIT ".$ays_recent_poll_count;
                break;
            case 'random':
                $last_polls_sql .= "ORDER BY RAND() LIMIT ".$ays_recent_poll_count;
                break;
            default:
                $last_polls_sql .= "ORDER BY id DESC LIMIT ".$ays_recent_poll_count;
                break;
        }

        $last_poll_ids = $wpdb->get_results($last_polls_sql,'ARRAY_A');

        return $last_poll_ids;
	}

	public function ays_set_poll_fields_placeholders_texts(){

        /*
         * Get Poll fields placeholders from database
         */

        $settings_placeholders_texts = $this->settings->ays_get_setting('fields_placeholders');
        if($settings_placeholders_texts){
            $settings_placeholders_texts = json_decode(stripcslashes($settings_placeholders_texts), true);
        }else{
            $settings_placeholders_texts = array();
        }

        $poll_fields_placeholder_name  = (isset($settings_placeholders_texts['poll_fields_placeholder_name']) && $settings_placeholders_texts['poll_fields_placeholder_name'] != '') ? stripslashes( esc_attr( $settings_placeholders_texts['poll_fields_placeholder_name'] ) ) : 'Name';

        $poll_fields_placeholder_email = (isset($settings_placeholders_texts['poll_fields_placeholder_email']) && $settings_placeholders_texts['poll_fields_placeholder_email'] != '') ? stripslashes( esc_attr( $settings_placeholders_texts['poll_fields_placeholder_email'] ) ) : 'E-mail';

        $poll_fields_placeholder_phone = (isset($settings_placeholders_texts['poll_fields_placeholder_phone']) && $settings_placeholders_texts['poll_fields_placeholder_phone'] != '') ? stripslashes( esc_attr( $settings_placeholders_texts['poll_fields_placeholder_phone'] ) ) : 'Phone';


		$poll_fields_placeholder_name_text  = $poll_fields_placeholder_name  === 'Name'  ? __('Name',  $this->plugin_name) : $poll_fields_placeholder_name;
		$poll_fields_placeholder_email_text = $poll_fields_placeholder_email === 'Email' ? __('Email', $this->plugin_name) : $poll_fields_placeholder_email;
		$poll_fields_placeholder_phone_text = $poll_fields_placeholder_phone === 'Phone' ? __('Phone', $this->plugin_name) : $poll_fields_placeholder_phone;

        $texts = array(
            'namePlaceholder'       => $poll_fields_placeholder_name_text,
            'emailPlaceholder'      => $poll_fields_placeholder_email_text,
            'phonePlaceholder'      => $poll_fields_placeholder_phone_text,
        );

        return $texts;
    }


	// public function ays_poll_by_ip($args_ip, $args_tables){
	// 	global $wpdb;
	// 	$reports_table = isset($args_tables['reports_table']) && $args_tables['reports_table'] != '' ? $args_tables['reports_table'] : ''; 
	// 	$answer_table  = isset($args_tables['answer_table']) && $args_tables['answer_table'] != '' ? $args_tables['answer_table'] : ''; 
	// 	$sql = "SELECT COUNT(*) 
	// 			FROM ".$reports_table." 
	// 			JOIN ".$answer_table." 
	// 			ON ".$answer_table.".id = ".$reports_table.".answer_id 
	// 			WHERE ".$reports_table.".user_ip = %s 
	// 			AND ".$answer_table.".poll_id = %d";
	// 	$limit_users = $wpdb->get_var(
	// 		$wpdb->prepare( $sql, $args)
	// 		);

	// 	return $limit_users;
	// }
}
