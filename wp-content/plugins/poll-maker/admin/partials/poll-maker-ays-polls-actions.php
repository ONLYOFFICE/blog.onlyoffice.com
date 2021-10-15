<?php
$action        = (isset($_GET['action'])) ? sanitize_text_field($_GET['action']) : '';
$active_tab    = (!empty($_GET['active-tab'])) ? sanitize_text_field($_GET['active-tab']) : 'General';
$heading       = '';
$loader_iamge  = '';
$image_text    = __('Add Image', $this->plugin_name);
$image_text_bg = __('Add Image', $this->plugin_name);
$image_text_logo = __('Add Image', $this->plugin_name);

$id              = (isset($_GET['poll'])) ? absint(intval($_GET['poll'])) : null;

if ($action == 'edit') {
    if ($id === null || $id === 0) {
        $url = esc_url_raw(remove_query_arg(array('action', 'poll')));
        wp_safe_redirect($url);
    }
}

$user_id = get_current_user_id();
$user = get_userdata($user_id);
$author = array(
    'id' => $user->ID,
    'name' => $user->data->display_name
);

$poll            = array(
	'title'       => 'Default title',
	'description' => '',
	'categories'  => array(),
	'image'       => '',
	'question'    => '',
	'type'        => 'choosing',
	'view_type'   => '',
	'answers'     => array(),
	'show_title'  => 1,
	'styles'      => '',
	'custom_css'  => '',
	'theme_id'    => 1,
);
$default_colors  = array(
	"main_color"       => "#0C6291",
	"text_color"       => "#0C6291",
	"icon_color"       => "#0C6291",
	"box_shadow_color" => "#000000",
	"bg_color"         => "#FBFEF9",
	"answer_bg_color"  => "#FBFEF9",
	"title_bg_color"   => "#FBFEF9",
	"border_color"     => "#0C6291",
);
$default_options = array(
	'randomize_answers'           => 'off',
	"icon_size"                   => 24,
	"width"                       => 600,
	"btn_text"                    => __('Vote', $this->plugin_name),
	"see_res_btn_text"            => __('See Results', $this->plugin_name),
	"border_style"                => "ridge",
	"border_radius"               => 0,
	"border_width"                => 2,
	"enable_box_shadow"           => "",
	"bg_image"                    => "",
	"hide_results"                => 0,
	"hide_results_text"           => "<p style='text-align: center'>" . __("Thanks for your answer!", $this->plugin_name) . "</p>",
	"allow_not_vote"              => 0,
	"show_social"                 => 0,
	"poll_show_social_ln"         => "on",
	"poll_show_social_fb"         => "on",
	"poll_show_social_tr"         => "on",
	"load_effect"                 => "load_gif",
	"load_gif"                    => "plg_default",
	'limit_users'                 => 0,
	"limitation_message"          => "<p style='text-align: center'>" . __("You have already voted", $this->plugin_name) . "</p>",
	'redirect_url'                => '',
	'redirection_delay'           => '',
	'users_role'                  => '',
	'enable_restriction_pass'     => 0,
	'restriction_pass_message'    => "<p style='text-align: center'>" . __("You don't have permissions for passing the poll", $this->plugin_name) . "</p>",
	'enable_logged_users'         => 0,
	'enable_logged_users_message' => "<p style='text-align: center'>" . __('You must sign in for passing the poll', $this->plugin_name) . "</p>",
	'notify_email_on'             => 0,
	'notify_email'                => '',
	'result_sort_type'            => 'none',
    'redirect_after_submit'       => 0,
	'redirect_users'              => 0,
	'redirect_after_vote_url'     => '',
	'redirect_after_vote_delay'   => '',
	'published'                   => 1,
	'enable_pass_count'           => 'on',
    'activeInterval'              => '',
    'create_date' 				  => current_time( 'mysql' ),
    'author' 					  => $author,
    'deactiveInterval'            => '',
    'enable_background_gradient' => 'off',
    'background_gradient_color_1' => '#103251',
    'background_gradient_color_2' => '#607593',
    'poll_gradient_direction' => 'vertical',
	'activeIntervalSec'           => '',
	'deactiveIntervalSec'         => '',
	'active_date_check'           => '',
	'active_date_message_soon'    => "<p style='text-align: center'>" . __("The poll will be available soon!", $this->plugin_name) . "</p>",
	'active_date_message'         => "<p style='text-align: center'>" . __("The poll has expired!", $this->plugin_name) . "</p>",
	'enable_restart_button'       => 0,
    'enable_vote_btn'             => 1,
	'show_votes_count'            => 1,
    'show_res_percent'            => 1,
	'poll_direction'              => 'ltr',
	'info_form'                   => 0,
	'fields'                      => 'apm_name,apm_email,apm_phone',
	'required_fields'             => 'apm_email',
	'info_form_title'             => "<h5>" . __("Please fill out the form:", $this->plugin_name) . "</h5>",
	'disable_answer_hover'        => 0,
	'enable_asnwers_sound'        => 'off',
	'poll_bg_image_position'      => 'center center',
	'poll_bg_img_in_finish_page'  => 'off',
	'result_in_rgba'              => 'off',
	'answer_sort_type'            => 'default',
	'answer_font_size'             => '16',
	'poll_answer_font_size_mobile' => '16',
	'show_passed_users'           => 'off',
	'logo_image'                  => '',
    'dont_show_poll_cont'         => 'off',
    'see_result_button'           => 'on',
    'see_result_radio'            => 'ays_see_result_button',
    'loader_font_size'            => '',
    'show_answers_numbering'      => 'none',
    'poll_box_shadow_x_offset'    => 0,
    'poll_box_shadow_y_offset'    => 0,
    'poll_box_shadow_z_offset'    => 15,
    'poll_question_size_pc'       => 16,
    'poll_question_size_mobile'   => 16,
    'poll_question_image_object_fit' => "cover",
    'poll_title_font_size'        => 20,
    'poll_title_alignment'        => "center",
    'poll_text_type_length_enable' => "off",
    'poll_text_type_limit_type'    => "characters",
    'poll_text_type_limit_length'  => "",
    'poll_text_type_limit_message' => "off",
    'poll_text_type_placeholder'   => "Your answer",
    'poll_text_type_width'         => "",
    'poll_text_type_width_type'    => "percent",
    'poll_enable_password'         => "off",
    'poll_password'                => "",
    'poll_password_message'        => 'Please enter password',
    'poll_answer_enable_box_shadow' => "off",
    'poll_answer_box_shadow_color'  => "#000000",
    'poll_answer_border_radius'     => 0,
    'poll_enable_answer_image_after_voting' => "off",
);

$settings_options = $this->settings_obj->ays_get_setting('options');
if($settings_options){
    $settings_options = json_decode($settings_options, true);
}else{
    $settings_options = array();
}

switch ( $action ) {
	case 'add':
		$heading = __('Add new poll', $this->plugin_name);
        $options = array_merge($default_options, $default_colors);
        $loader_iamge = "<span class='display_none'><img src=".POLL_MAKER_AYS_ADMIN_URL."/images/loaders/loading.gif></span>";
        // Default category
        $poll_default_cat = isset($settings_options['default_category']) && !empty($settings_options['default_category']) ? explode("," , $settings_options['default_category']) : array("1");
        
        $poll['categories'] = $poll_default_cat;

        // Default type
        $poll_default_type = isset($settings_options['default_type']) && $settings_options['default_type'] != '' ? esc_attr($settings_options['default_type']) : "choosing";
        $poll['type'] = $poll_default_type;
		break;
	case 'edit':
        $heading = __('Edit poll', $this->plugin_name);
        $loader_iamge = "<span class='display_none'><img src=".POLL_MAKER_AYS_ADMIN_URL."/images/loaders/loading.gif></span>";
		$poll    = $this->polls_obj->get_poll_by_id($id);
        if (empty($poll)) {
            $url = esc_url_raw(remove_query_arg(array('action', 'poll')));
            wp_safe_redirect($url);
        }

		$options = array_merge($default_options, $poll['styles']);
		break;
    default:
		$url = esc_url_raw(remove_query_arg(array('action', 'poll')));
        wp_safe_redirect($url);
		break;
}
$enable_pass_count = $options['enable_pass_count'];
$categories        = $this->polls_obj->get_categories();
global $wp_roles;
$ays_users_roles = $wp_roles->roles;
if (isset($_POST['ays_submit']) || isset($_POST['ays_submit_top'])) {
	$this->polls_obj->add_or_edit_polls($_POST, $id);
}
if (isset($_POST['ays_apply_top']) || isset($_POST['ays_apply'])) {
	$this->polls_obj->add_or_edit_polls($_POST, $id, "apply");
}
$style    = "display: none;";
$style_bg = "display: none;";
$style_logo = "display: none;";
$style_logo_check = false;
if (isset($poll['image']) && !empty($poll['image'])) {
    
	$style      = "display: block;";
	$image_text = __('Edit Image', $this->plugin_name);
}
if (isset($options['bg_image']) && !empty($options['bg_image'])) {
	$style_bg      = "display: flex;";
	$image_text_bg = __('Edit Image', $this->plugin_name);
}
if (isset($options['logo_image']) && !empty($options['logo_image'])) {
	$style_logo      = "display: block;";
    $style_logo_check = true;
	$image_text_logo = __('Edit Image', $this->plugin_name);
}

$published = $options['published'];


if (!empty($options['activeInterval']) && !empty($options['deactiveInterval'])) {
	$activateTime   = strtotime($options['activeInterval']);
	$activePoll     = date('Y-m-d', $activateTime);
	$deactivateTime = strtotime($options['deactiveInterval']);
	$deactivePoll   = date('Y-m-d', $deactivateTime);
} else {
	$activePoll     = date('Y-m-d');
	$deactivePoll   = date('Y-m-d');
}

$activePollSec      = isset($options['activeIntervalSec']) && !empty($options['activeIntervalSec']) ? $options['activeIntervalSec'] : '';
$deactivePollSec    = isset($options['deactiveIntervalSec']) && !empty($options['deactiveIntervalSec']) ? $options['deactiveIntervalSec'] : '';

$activePoll = $activePoll . " " . $activePollSec;
$deactivePoll = $deactivePoll . " " . $deactivePollSec;

$randomize_answers = (isset($options['randomize_answers']) && $options['randomize_answers'] == 'on') ? true : false;

$all_fields      = array(
	array(
		"name" => __("Name", $this->plugin_name),
		"slug" => "apm_name",
	),
	array(
		"name" => __("Email", $this->plugin_name),
		"slug" => "apm_email",
	),
	array(
		"name" => __("Phone", $this->plugin_name),
		"slug" => "apm_phone",
	),
);


//INTEGRATIONS
$poll_settings = $this->settings_obj;

$asnwers_sound = (isset($settings_options['answers_sound']) && $settings_options['answers_sound'] != '') ? true : false;

$answer_default_count = (isset($settings_options['answer_default_count']) && $settings_options['answer_default_count'] != '') ? $settings_options['answer_default_count'] : 2;

$answers_sound_status = false;
if($asnwers_sound){
    $answers_sound_status = true;
}

// Answers sound option
$options['enable_asnwers_sound'] = isset($options['enable_asnwers_sound']) ? $options['enable_asnwers_sound'] : 'off';
$enable_asnwers_sound = (isset($options['enable_asnwers_sound']) && $options['enable_asnwers_sound'] == "on") ? true : false;

$mailchimp_res      = ($poll_settings->ays_get_setting('mailchimp') === false) ? json_encode(array()) : $poll_settings->ays_get_setting('mailchimp');
$mailchimp          = json_decode($mailchimp_res, true);
$mailchimp_username = isset($mailchimp['username']) ? $mailchimp['username'] : '';
$mailchimp_api_key  = isset($mailchimp['apiKey']) ? $mailchimp['apiKey'] : '';
$mailchimp_lists    = $mailchimp_api_key ? $this->ays_get_mailchimp_lists($mailchimp_username, $mailchimp_api_key) : array();
$mailchimp_select   = array();
if (!empty($mailchimp_lists) && $mailchimp_lists['total_items'] > 0) {
    foreach ( $mailchimp_lists['lists'] as $list ) {
        $mailchimp_select[] = array(
            'listId'   => $list['id'],
            'listName' => $list['name']
        );
    }
} else {
    $mailchimp_select = __("There are no lists", $this->plugin_name);
}

// MailChimp
$enable_mailchimp = (isset($options['enable_mailchimp']) && $options['enable_mailchimp'] == 'on') ? true : false;
$mailchimp_list = (isset($options['mailchimp_list'])) ? $options['mailchimp_list'] : '';


$fields          = !empty($options['fields']) ? explode(",", $options['fields']) : array();
$required_fields = !empty($options['required_fields']) ? explode(",", $options['required_fields']) : array();

// Show votes count
$options['show_votes_count'] = isset($options['show_votes_count']) ? $options['show_votes_count'] : 1;
$showvotescount = isset($options['show_votes_count']) && intval($options['show_votes_count']) == 1 ? true : false;

// Show result percent
$options['show_res_percent'] = isset($options['show_res_percent']) ? $options['show_res_percent'] : 1;
$show_res_percent = isset($options['show_res_percent']) && intval($options['show_res_percent']) == 1 ? true : false;

// Show result button after schedule
$options['show_result_btn_schedule'] = isset($options['show_result_btn_schedule']) ? $options['show_result_btn_schedule'] : 0;
$showresbtnschedule = isset($options['show_result_btn_schedule']) && intval($options['show_result_btn_schedule']) == 1 ? true : false;

$schedule_show_timer = isset($options['ays_poll_show_timer']) && intval($options['ays_poll_show_timer']) == 1 ? true : false;

$show_timer_type = isset($options['ays_show_timer_type']) && !empty($options['ays_show_timer_type'])? $options['ays_show_timer_type'] : 'countdown';

// Show login form for not logged in users
$options['show_login_form'] = isset($options['show_login_form']) ? $options['show_login_form'] : 'off';
$show_login_form = (isset($options['show_login_form'] ) && $options['show_login_form'] == "on") ? true : false;

// Redirect after voting
$options['redirect_users'] = isset($options['redirect_users']) ? $options['redirect_users'] : 0;
$redirect_users = (isset($options['redirect_users']) && $options['redirect_users'] == 1) ? true : false;

// Results notification by email
$options['notify_email_on'] = isset($options['notify_email_on']) ? $options['notify_email_on'] : 0;
$notify_email_on = (isset($options['notify_email_on']) && $options['notify_email_on'] == 1) ? true : false;

// Background gradient
$options['enable_background_gradient'] = (!isset($options['enable_background_gradient'])) ? 'off' : $options['enable_background_gradient'];
$enable_background_gradient = (isset($options['enable_background_gradient']) && $options['enable_background_gradient'] == 'on') ? true : false;

$background_gradient_color_1 = (isset($options['background_gradient_color_1']) && $options['background_gradient_color_1'] != '' && $enable_background_gradient) ? $options['background_gradient_color_1'] : '#103251';
$background_gradient_color_2 = (isset($options['background_gradient_color_2']) && $options['background_gradient_color_2'] != '' && $enable_background_gradient) ? $options['background_gradient_color_2'] : '#607593';
$poll_gradient_direction = (isset($options['poll_gradient_direction']) && $options['poll_gradient_direction'] != '') ? $options['poll_gradient_direction'] : 'vertical';


// Redirect after submit
$options['redirect_after_submit'] = (!isset($options['redirect_after_submit'])) ? 0 : $options['redirect_after_submit'];
$redirect_after_submit = (isset($options['redirect_after_submit']) && $options['redirect_after_submit'] == 1) ? true : false;
$submit_redirect_url = isset($options['ays_submit_redirect_url']) ? $options['ays_submit_redirect_url'] : '';
// $submit_redirect_delay = isset($options['submit_redirect_delay']) ? $options['submit_redirect_delay'] : '';

$users_role   = (isset($options['users_role']) && $options['users_role'] != "") ? json_decode($options['users_role'], true) : array();

$options['enable_answer_style'] = isset($options['enable_answer_style']) ? $options['enable_answer_style'] : 'on';

$poll_create_date = (isset($options['create_date']) && $options['create_date'] != '') ? $options['create_date'] : "0000-00-00 00:00:00";

// Bg image positioning
$poll_bg_image_position = (isset($options['poll_bg_image_position']) && $options['poll_bg_image_position'] != '') ? $options['poll_bg_image_position'] : "center center";

$poll_bg_img_in_finish_page = (isset($options['poll_bg_img_in_finish_page']) && $options['poll_bg_img_in_finish_page'] == "on") ? true : false;

if(isset($options['author']) && $options['author'] != 'null'){
    $poll_author = $options['author'];
} else {
    $poll_author = array('name' => 'Unknown');
}

$show_create_date = (isset($options['show_create_date']) && $options['show_create_date'] == 1) ? true : false;
$show_author = (isset($options['show_author']) && $options['show_author'] == 1) ? true : false;

$custom_class = (isset($options['custom_class']) && $options['custom_class'] != "") ? $options['custom_class'] : '';

// Results bar in RGBA
$options['result_in_rgba'] = isset($options['result_in_rgba']) ? $options['result_in_rgba'] : 'off';
$result_in_rgba = (isset($options['result_in_rgba']) && $options['result_in_rgba'] == "on") ? true : false;

// Enable View more button
$options['enable_view_more_button'] = isset($options['enable_view_more_button']) ? $options['enable_view_more_button'] : 'off';
$enable_view_more_button = (isset($options['enable_view_more_button']) && $options['enable_view_more_button'] == 'on' ) ? true : false;
$poll_view_more_button_count = (isset($options['poll_view_more_button_count']) && $options['poll_view_more_button_count'] != '' ) ? absint(intval($options['poll_view_more_button_count'])) : 0;


// Poll Min Height
$poll_min_height = (isset($options['poll_min_height']) && $options['poll_min_height'] != '') ? absint(intval($options['poll_min_height'])) : '';

// Poll answer font size 
$poll_answer_font_size = (isset($options['answer_font_size']) && $options['answer_font_size'] != '') ? esc_html($options['answer_font_size']) : '15';

// Poll answers font size on mobile
$poll_answer_font_size_mobile  = (isset($options['poll_answer_font_size_mobile']) && $options['poll_answer_font_size_mobile'] != '') ? esc_attr($options['poll_answer_font_size_mobile']) : '16';

// Poll show passed users 
$poll_show_passed_users = isset($options['show_passed_users']) ? esc_html($options['show_passed_users']) : 'off';
$poll_show_passed_users_checked = isset($poll_show_passed_users) && $poll_show_passed_users == 'on' ? 'checked' : '';
$poll_show_passed_users_count = isset($options['poll_show_passed_users_count']) && $options['poll_show_passed_users_count'] != "" ? intval(esc_attr($options['poll_show_passed_users_count'])) : 3;

// Poll Logo image
$poll_logo_image = isset($options['logo_image']) && $options['logo_image'] != '' ? esc_url($options['logo_image']) : '';
$poll_check_logo = isset($poll_logo_image) && $poll_logo_image != '' ? true : false;
$poll_logo_img = $poll_check_logo ? 'ays_logo_image_on' : 'display_none';
$poll_logo_for_live_container = $poll_check_logo ? 'ays_logo_cont_image_on' : '';

//

$checking_answer_hover_live = ($options['disable_answer_hover'] == 1) ? 'disable_hover' : 'ays_enable_hover';

// Poll schedule container on/off
$poll_check_exp_cont = (isset($options['dont_show_poll_cont']) && $options['dont_show_poll_cont'] == 'on') ? 'checked' : '';

// Poll see results button in limitations
$poll_see_result_button = (isset($options['see_result_button']) && $options['see_result_button'] == 'on') ? 'checked' : '';
$poll_see_result_button_check = (isset($options['see_result_button']) && $options['see_result_button'] == 'on') ? true : false;
$poll_see_result_button_cont = (isset($options['see_result_button']) && $options['see_result_button'] != 'on') ? 'ays_poll_display_none' : '';
$poll_see_result_radio = (isset($options['see_result_radio']) && $options['see_result_radio'] != '') ? esc_attr($options['see_result_radio']) : 'ays_see_result_button';
$poll_see_result_botton_show = isset($poll_see_result_radio) && $poll_see_result_radio == 'ays_see_result_button' ? 'checked' : '';
$poll_see_result_immediately = isset($poll_see_result_radio) && $poll_see_result_radio == 'ays_see_result_immediately' ? 'checked' : '';

// Loader font size
$poll_loader_font_size = (isset($options['loader_font_size']) && $options['loader_font_size'] != '') ? esc_attr($options['loader_font_size']) : '';
$poll_loader_size_enable = isset($options['load_effect']) && $options['load_effect'] == "load_gif" ? "" : "display_none";
$poll_loader_size_line_enable = isset($options['load_effect']) && $options['load_effect'] == "load_gif" ? "" : "ays_hr_display_none";

// Show answers numbering
$show_answers_numbering = (isset($options['show_answers_numbering']) && sanitize_text_field( $options['show_answers_numbering'] ) != '') ? sanitize_text_field( $options['show_answers_numbering'] ) : 'none';

// Default border color
$main_color = isset($options['main_color']) && $options['main_color'] != "" ? $options['main_color'] : $default_colors['main_color'];
$default_border = isset($options['border_color']) && $options['border_color'] != "" ? $options['border_color'] : $main_color;

// Poll load effect message
$poll_effect_message = isset($options['effect_message']) && $options['effect_message'] != "" ? $options['effect_message'] : "";

// Poll title
$poll_title = isset($poll['title']) && $poll['title'] != "" ?  stripslashes(htmlentities($poll['title'])) : "Default title";

// Box shadow coords
//  Box Shadow X offset
$poll_box_shadow_x_offset = (isset($options['poll_box_shadow_x_offset']) && $options['poll_box_shadow_x_offset'] != '' && $options['poll_box_shadow_x_offset'] != 0 ) ? intval( esc_attr( $options['poll_box_shadow_x_offset'] ) ) : 0;

//  Box Shadow Y offset
$poll_box_shadow_y_offset = (isset($options['poll_box_shadow_y_offset']) && $options['poll_box_shadow_y_offset'] != '' && $options['poll_box_shadow_y_offset'] != 0 ) ? intval( esc_attr( $options['poll_box_shadow_y_offset'] ) ) : 0;

//  Box Shadow Z offset
$poll_box_shadow_z_offset = (isset($options['poll_box_shadow_z_offset']) && $options['poll_box_shadow_z_offset'] != '' && $options['poll_box_shadow_z_offset'] != 0 ) ? intval( esc_attr( $options['poll_box_shadow_z_offset'] ) ) : 15;

// Poll Vote Reason
$poll_vote_reason = (isset($options['poll_vote_reason']) && $options['poll_vote_reason'] == 'on' ) ? "checked" : "";

// Allow multivote
$poll_allow_multivote  = isset($options['poll_allow_multivote']) && $options['poll_allow_multivote'] == 'on' ? "checked" : "";
$poll_enable_multivote_answer = $poll_allow_multivote == "checked" ? true : false;
$poll_multivote_answer_count = (isset($options['poll_allow_multivote_count']) && $options['poll_allow_multivote_count'] != '') ? $options['poll_allow_multivote_count'] : '1';

// Allow collect user info
$poll_allow_collecting_users_data = (isset($options['poll_allow_collecting_users_data']) && $options['poll_allow_collecting_users_data'] == 'on') ? "checked" : "";

// Show answers icon
$poll_answer_icon_check = (isset($options['poll_answer_icon_check']) && $options['poll_answer_icon_check'] == 'on') ? true : false;
$poll_answer_icon       = isset($options['poll_answer_icon']) ? $options['poll_answer_icon'] : 'radio';


// Every Answer redirect delay
$poll_every_answer_redirect_delay = isset($options['poll_every_answer_redirect_delay']) && $options['poll_every_answer_redirect_delay'] != "" ? esc_attr($options['poll_every_answer_redirect_delay']) : ""; 
$poll_enable_answer_redirect_delay = isset($options['poll_enable_answer_redirect_delay']) && $options['poll_enable_answer_redirect_delay'] == "on" ? true : false; 

// Show Answers image after voting
$poll_enable_answer_image_after_voting = isset($options['poll_enable_answer_image_after_voting']) && $options['poll_enable_answer_image_after_voting'] == "on" ? true : false; 

// Poll logo image url
$poll_logo_image_url       = isset($options['poll_logo_url']) && $options['poll_logo_url'] != "" ? esc_attr($options['poll_logo_url']) : ""; 
$poll_logo_image_url_check = isset($options['poll_enable_logo_url']) && $options['poll_enable_logo_url'] == "on" ? true : false; 

// Poll question font size
$poll_question_font_size_pc     = isset($options['poll_question_size_pc']) && $options['poll_question_size_pc'] != "" ? esc_attr($options['poll_question_size_pc']) : 16; 
$poll_question_font_size_mobile = isset($options['poll_question_size_mobile']) && $options['poll_question_size_mobile'] != "" ? esc_attr($options['poll_question_size_mobile']) : 16;

// Poll question image height
$poll_question_image_height     = isset($options['poll_question_image_height']) && $options['poll_question_image_height'] != "" ? esc_attr($options['poll_question_image_height']) : ""; 

// Poll container max-width for mobile
$poll_mobile_max_width = (isset($options['poll_mobile_max_width']) && $options['poll_mobile_max_width'] != "") ? esc_attr($options['poll_mobile_max_width']) : '';

// ==== BUTTON STYLES START ====
// Buttons font size
$poll_buttons_font_size = (isset($options['poll_buttons_font_size']) && $options['poll_buttons_font_size'] != "") ? esc_attr($options['poll_buttons_font_size']) : '17';

// Buttons Left / Right padding
$poll_buttons_left_right_padding = (isset($options['poll_buttons_left_right_padding']) && $options['poll_buttons_left_right_padding'] != '') ? esc_attr($options['poll_buttons_left_right_padding']) : '20';

// Buttons Top / Bottom padding
$poll_buttons_top_bottom_padding = (isset($options['poll_buttons_top_bottom_padding']) && $options['poll_buttons_top_bottom_padding'] != '') ? esc_attr($options['poll_buttons_top_bottom_padding']) : '10';

// Buttons border radius
$poll_buttons_border_radius = (isset($options['poll_buttons_border_radius']) && $options['poll_buttons_border_radius'] != "") ? esc_attr($options['poll_buttons_border_radius']) : '3';

// Buttons Width
$poll_buttons_width = (isset($options['poll_buttons_width']) && $options['poll_buttons_width'] != "") ? esc_attr($options['poll_buttons_width']) : '';

$poll_button_selected = isset($options['poll_buttons_size']) && $options['poll_buttons_size'] != "" ? esc_attr($options['poll_buttons_size']) : ""; 
// ==== BUTTON STYLES END ====

// ==== Allow Answer options ====
// Allow custom answer
$poll_allow_answer = (isset($options['poll_allow_answer']) && $options['poll_allow_answer'] == "on") ? "checked" : "";
// Require admin approval
$poll_allow_answer_require = (isset($options['poll_allow_answer_require']) && $options['poll_allow_answer_require'] == "on") ? "checked" : "";

// ==== ====

// Poll answer view type
$poll_answer_view_type = (isset($options['poll_answer_view_type']) && $options['poll_answer_view_type'] != "") ? esc_attr($options['poll_answer_view_type']) : "list";

// Poll answer image height
$poll_answer_image_height = (isset($options['poll_answer_image_height']) && $options['poll_answer_image_height'] != "") ? esc_attr($options['poll_answer_image_height']) : "150";

// Poll answer image height
$poll_question_image_object_fit = (isset($options['poll_question_image_object_fit']) && $options['poll_question_image_object_fit'] != "") ? esc_attr($options['poll_question_image_object_fit']) : "cover";

// Poll answer image object fit
$poll_answer_object_fit   = (isset($options['poll_answer_object_fit']) && $options['poll_answer_object_fit'] != "") ? esc_attr($options['poll_answer_object_fit']) : "cover";

// Poll answer padding
$poll_answer_padding      = (isset($options['poll_answer_padding']) && $options['poll_answer_padding'] != "") ? esc_attr($options['poll_answer_padding']) : "10";

// Poll answer gap
$poll_answer_margin      = (isset($options['poll_answer_margin']) && $options['poll_answer_margin'] != "") ? esc_attr($options['poll_answer_margin']) : "10";

// Poll title font size
$poll_title_font_size    = (isset($options['poll_title_font_size']) && $options['poll_title_font_size'] != "") ? absint(intval(esc_attr($options['poll_title_font_size']))) : "20";

// Poll title alignment
$poll_title_alignment    = ( isset($options['poll_title_alignment']) && $options['poll_title_alignment'] != "" ) ? esc_attr($options['poll_title_alignment']) : "center";

// Poll view type
$poll_view_type_for_text    = ( isset($poll['view_type']) && $poll['view_type'] == "paragraph" ) ? "paragraph" : "short_text";

// ===== Poll text type options start =====
$poll_text_type_length_enable = ( isset($options['poll_text_type_length_enable']) && $options['poll_text_type_length_enable'] == "on" ) ? true : false;
$poll_text_type_limit_type    = ( isset($options['poll_text_type_limit_type']) && $options['poll_text_type_limit_type'] != "" ) ? esc_attr($options['poll_text_type_limit_type']) : "characters";
$poll_text_type_limit_length  = ( isset($options['poll_text_type_limit_length']) && $options['poll_text_type_limit_length'] != "" ) ? esc_attr($options['poll_text_type_limit_length']) : "";
$poll_text_type_limit_message = ( isset($options['poll_text_type_limit_message']) && $options['poll_text_type_limit_message'] == "on" ) ?  true : false;
$poll_text_type_placeholder   = ( isset($options['poll_text_type_placeholder']) && $options['poll_text_type_placeholder'] != "" ) ?  stripslashes(esc_attr($options['poll_text_type_placeholder'])) : "";
$poll_text_type_width         = ( isset($options['poll_text_type_width']) && $options['poll_text_type_width'] != "" ) ?  stripslashes(esc_attr($options['poll_text_type_width'])) : "";
$poll_text_type_width_type    = ( isset($options['poll_text_type_width_type']) && $options['poll_text_type_width_type'] != "" ) ?  esc_attr($options['poll_text_type_width_type']) : "percent";
// ===== Poll text type options end =====

$poll_enable_password  = ( isset($options['poll_enable_password']) && $options['poll_enable_password'] == "on" ) ?  true : false;
$poll_password         = ( isset($options['poll_password']) && $options['poll_password'] != "" ) ?  stripslashes(esc_attr($options['poll_password'])) : "";
$poll_password_message = ( isset($options['poll_password_message']) && $options['poll_password_message'] != "" ) ?  stripslashes($options['poll_password_message']) : "Please enter password";

// == Poll answer box shadow ==
// Poll answer box shadow enable
$poll_answer_enable_box_shadow = (isset($options['poll_answer_enable_box_shadow']) && $options['poll_answer_enable_box_shadow'] == "on") ? true : false;
// Poll answer box shadow color
$poll_answer_box_shadow_color  = (isset($options['poll_answer_box_shadow_color']) && $options['poll_answer_box_shadow_color'] != "") ? esc_attr($options['poll_answer_box_shadow_color']) : "#000000";

// Poll answer box shadow color
$poll_answer_border_radius  = (isset($options['poll_answer_border_radius']) && $options['poll_answer_border_radius'] != "") ? esc_attr($options['poll_answer_border_radius']) : 0;

$poll_social_buttons = isset($options['show_social']) && $options['show_social'] ? true : false;
$poll_show_social_ln = isset($options['poll_show_social_ln']) && $options['poll_show_social_ln'] == "on" ? true : false;
$poll_show_social_fb = isset($options['poll_show_social_fb']) && $options['poll_show_social_fb'] == "on" ? true : false;
$poll_show_social_tr = isset($options['poll_show_social_tr']) && $options['poll_show_social_tr'] == "on" ? true : false;

?>
<!--LIVE PREVIEW STYLES-->
<?php
$emoji = array(
	"<i class='ays_poll_far ays_poll_fa-dizzy'></i>",
	"<i class='ays_poll_far ays_poll_fa-smile'></i>",
	"<i class='ays_poll_far ays_poll_fa-meh'></i>",
	"<i class='ays_poll_far ays_poll_fa-frown'></i>",
	"<i class='ays_poll_far ays_poll_fa-tired'></i>",
); ?>
<style>
    /*save changing properties of poll in the css-variables*/
    :root {
        /*colors*/
        --theme-main-color: <?=$options['main_color']?>;
        --theme-bg-color: <?=$options['bg_color']?>;
        --theme-answer-bg-color: <?= isset($options['answer_bg_color']) && !empty($options['answer_bg_color']) ? $options['answer_bg_color'] : $options['bg_color'] ?>;
        --theme-title-bg-color: <?= isset($options['title_bg_color']) && !empty($options['title_bg_color']) ? $options['title_bg_color'] : $options['bg_color'] ?>;
        --theme-text-color: <?=$options['text_color']?>;
        --theme-icon-color: <?=$options['icon_color']?>;
        /*options*/
        --poll-width: <?= (int) $options['width'] > 0 ? (int) $options['width'] . "px" : "100%" ?>;
        --poll-border-style: <?=$options['border_style']?>;
        --poll-border-radius: <?= absint($options['border_radius'])?>px;
        --poll-border-width: <?= absint($options['border_width'])?>px;
        --poll-box-shadow: <?= (isset($options['box_shadow_color']) && !empty($options['box_shadow_color'])) ? $options['box_shadow_color'] . ' 0px 0px 10px 0px' : '' ?>;
        --poll-bagckround-image: <?= !empty($options['bg_image']) ? "url({$options['bg_image']})" : "unset" ?>;
        --poll-icons-size: <?= absint($options['icon_size']) >= 10 ? absint($options['icon_size']) : 24 ?>px;
        --poll-display-title: <?= $poll['show_title'] ? "block" : "none"?>;
        --poll-display-image-box: <?= !empty($poll['image']) ? "block" : "none" ?>;

    }

    
    input[type='button'].ays-poll-btn{
		font-size: <?php echo $poll_buttons_font_size; ?>px;
		padding: <?php echo $poll_buttons_top_bottom_padding."px ".$poll_buttons_left_right_padding."px"; ?>;
		border-radius: <?php echo $poll_buttons_border_radius; ?>px;
		width:  <?php echo $poll_buttons_width; ?>px;
	}
</style>
<!--LIVE PREVIEW STYLES END-->
<div class="wrap">
    <div class="container-fluid">
        <form class="ays-poll-form" id="ays-poll-form" method="post">
            <input type="hidden" name="ays_poll_active_tab" id="ays_poll_active_tab"
                   value="<?php echo htmlentities($active_tab); ?>"/>
           	<input type="hidden" name="ays_poll_ctrate_date" value="<?php echo $poll_create_date; ?>">
           	<input type="hidden" name="ays_poll_author" value="<?php echo htmlentities(json_encode($poll_author)); ?>">
            <h1 class="wp-heading-inline">
				<?php
				echo "$heading";
                $save_attributes = array('id' => 'ays-button-top-apply');
                $save_close_attributes = array('id' => 'ays-button-top');
				submit_button(__('Save and close', $this->plugin_name), 'primary', 'ays_submit_top', false, $save_close_attributes);
                submit_button(__('Save', $this->plugin_name), '', 'ays_apply_top', false, $save_attributes);
                echo $loader_iamge;
				?>
            </h1>
            <div>
                <p class="ays-subtitle">
                    <strong class="ays_poll_title_in_top">
						<?php echo stripslashes(htmlentities($poll['title'])); ?>
                    </strong>
                </p>
                <?php if($id !== null): ?>
                <div class="row">
                    <div class="col-sm-3">
                        <label> <?php echo __( "Shortcode text for editor", $this->plugin_name ); ?> </label>
                    </div>
                    <div class="col-sm-9">
                        <p style="font-size:14px; font-style:italic;">
                            <?php echo __("To insert the Poll into a page, post or text widget, copy shortcode", $this->plugin_name); ?>
                            <strong class="ays-poll-shortcode-box" data-toggle="tooltip" title="<?php echo __('Click for copy.',$this->plugin_name);?>" onClick="selectElementContents(this)" style="font-size:16px; font-style:normal;"><?php echo "[ays_poll id=".$id."]"; ?></strong>
                            <?php echo " " . __( "and paste it at the desired place in the editor.", $this->plugin_name); ?>
                        </p>
                    </div>
                </div>
                <?php endif;?>
            </div>
            <hr>
            <div class="nav-tab-wrapper">
                <a href="#tab1" data-title="General"
                   class="nav-tab <?= $active_tab == 'General' ? 'nav-tab-active' : ''; ?>">
					<?= __('General', $this->plugin_name); ?>
                </a>
                <a href="#tab2" data-title="Styles"
                   class="nav-tab <?= $active_tab == 'Styles' ? 'nav-tab-active' : ''; ?>">
                    <?= __('Styles', $this->plugin_name); ?>
                </a>
                <a href="#tab3" data-title="Settings"
                   class="nav-tab <?= $active_tab == 'Settings' ? 'nav-tab-active' : ''; ?>">
					<?= __('Settings', $this->plugin_name); ?>
                </a>
                <a href="#tab8" data-title="Results Settings"
                   class="nav-tab <?= $active_tab == 'Results Settings' ? 'nav-tab-active' : ''; ?>">
					<?= __('Results Settings', $this->plugin_name); ?>
                </a>
                <a href="#tab4" data-title="Limitations"
                   class="nav-tab <?= $active_tab == 'Limitations' ? 'nav-tab-active' : ''; ?>">
					<?= __("Limitations", $this->plugin_name); ?>
                </a>                
                <a href="#tab5" data-title="Userdata"
                   class="nav-tab <?= $active_tab == 'Userdata' ? 'nav-tab-active' : ''; ?>">
					<?= __('User Data', $this->plugin_name); ?>
                </a>
                <a href="#tab6" data-title="Email"
                   class="nav-tab <?= $active_tab == 'Email' ? 'nav-tab-active' : ''; ?>">
					<?= __('Email', $this->plugin_name); ?>
                </a>
                <a href="#tab7" data-title="Integrations"
                   class="nav-tab <?= $active_tab == 'Integrations' ? 'nav-tab-active' : ''; ?>">
					<?= __('Integrations', $this->plugin_name); ?>
                </a>
            </div>
            <div id="tab1" class="ays-poll-tab-content <?= $active_tab == 'General' ? 'ays-poll-tab-content-active' : ''; ?>">
                <p class="ays-subtitle">
					<?= __('General options', $this->plugin_name); ?>
                </p>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for='ays-poll-title'>
							<?= __('Title', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip"
                               data-placement="top"
                               title="<?= __("Define a name for your poll which will be shown as a headline inside the poll.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9 ays_divider_left">
                        <input type="text" class="ays-text-input" id='ays-poll-title' name='ays-poll-title'
                               data-required="false" value="<?= $poll_title ?>"/>
                    </div>
                </div>
                <hr>
                <div class='form-group row'>
                    <div class="col-sm-3">
                        <label for='ays-poll-category'>
							<?= __('Categories', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip"
                               data-placement="top"
                               title="<?= __("Choose the category your poll belongs to. For creating a category, go to the `Categories` page (find it on the Poll Maker left navbar).", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9 ays_divider_left">
						<?php if (!empty($categories)): ?>
                            <select id="ays-poll-category" class="apm-cat-select2" name="ays-poll-categories[]" multiple
                                    data-placeholder='<?= __("Select category", $this->plugin_name) ?>'>
								<?php
								foreach ( $categories as $cat ) {
									?>
                                    <option value="<?= $cat['id']; ?>" <?= in_array($cat['id'], $poll['categories']) ? 'selected' : ''; ?>>
										<?= $cat['title']; ?>
                                    </option>
								<?php }
								?>
                            </select>
						<?php else: ?>
                            <a href="?page=poll-maker-ays-cats&action=add"><?= __("Create category", $this->plugin_name) ?></a>
						<?php endif; ?>
                    </div>
                </div>
                <hr>
                <div class="col-sm-12" style="padding:20px;">
                    <div class="pro_features" style="justify-content:flex-end;">
                        <div>
                            <p style="font-size:12px; margin-right: 0;">
                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                            </p>
                        </div>
                    </div>
	                <div class="form-group row ays_toggle_parent">
	                    <div class="col-sm-2">
	                        <label for="ays_add_post_for_poll">
	                            <?php echo __('Create post for poll',$this->plugin_name)?>
	                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('A new WordPress post will be created automatically and will include the shortcode of this poll. This function will be executed only once. You can find this post on Posts page, which will have the same title as the poll. The image of the poll will be the featured image of the post.',$this->plugin_name)?>">
	                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
	                            </a>
	                        </label>
	                    </div>
	                    <div class="col-sm-1">
	                        <input type="checkbox" id="ays_add_post_for_poll" name="ays_add_post_for_poll" value="on" class="ays_toggle_checkbox"/>                        
	                    </div>
	                    <div class="col-sm-9 ays_toggle_target ays_divider_left">
	                        <div class="form-group row">
	                            <div class="col-sm-4">
	                                <label for="ays_add_postcat_for_poll">
	                                    <?php echo __('Choose Post Categories',$this->plugin_name)?>
	                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Choose the category of the new post. For creating a category, go to the Categories page of the Posts.',$this->plugin_name)?>">
	                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
	                                    </a>
	                                </label>
	                            </div>
	                            <div class="col-sm-8">
	                                <div class="input-group">
	                                    <select id="ays_add_postcat_for_poll"
	                                            class="apm-cat-select2 ays_select2_pro_disabled"
	                                            multiple disabled>                                
	                                    </select>
	                                </div>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </div>
                <hr>
                <div class="ays-field form-group">
                    <label for='ays-poll-question'>
						<?= __('Question', $this->plugin_name); ?><a class="ays_help" data-toggle="tooltip" data-placement="top" title="<?= __("Write the main content/question, which will be shown inside the poll.", $this->plugin_name); ?>">
                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                        </a>
                        <a href="javascript:void(0)" class="add-question-image button">
							<?= $image_text; ?><a class="ays_help" data-toggle="tooltip" data-placement="top" title="<?= __("Add an image to the question.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a></a>
                    </label>
                    <div class="ays-poll-question-image-container" style="<?= $style; ?>">
                        <span class="ays-remove-question-img"></span>
                        <img src="<?= $poll['image']; ?>" id="ays-poll-img"/>
                        <input type="hidden" name="ays_poll_image" id="ays-poll-image" value="<?= $poll['image']; ?>"/>
                    </div>
					<?php
					$content   = stripslashes($poll["question"]);
					$editor_id = 'ays-poll-question';
					$settings  = array(
						'editor_height' => '15',
						'textarea_name' => 'ays_poll_question',
						'editor_class'  => 'ays-textarea',
						'media_buttons' => true,
						'tinymce'       => array(
							"init_instance_callback" => "function(editor) {
                                editor.on('Change', function(e) {
                                    document.querySelector('.box-apm .ays_question').innerHTML = e.level.content;
                                });
                            }",
						)
					);
					wp_editor($content, $editor_id, $settings);
					?>
                </div>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="ays-poll-type">
							<?= __('Type', $this->plugin_name); ?>
							<a class="ays_help" data-toggle="tooltip" data-html="true" data-placement="top" title="<p style='margin-bottom:3px;'><?php echo htmlentities(__( 'Choose your preferred type of poll.' , $this->plugin_name )); ?>
								<p style='padding-left:10px;margin-bottom:3px;'><strong><?php echo __( 'Choosing', $this->plugin_name ); ?></strong> <?php echo htmlentities(__( ' - you write the possible answers in the field of “Answers” which are 2 by default.' , $this->plugin_name )); ?></p>
                                <p style='padding-left:10px;margin-bottom:3px;'><strong><?php echo __( 'Rating', $this->plugin_name ); ?></strong><?php echo __( '- you need to choose the appearance(Stars/Emoji) and the scale of assessment(3-5) and let the users rate.', $this->plugin_name ); ?></p>
                                <p style='padding-left:10px;margin-bottom:3px;'><strong><?php echo __( 'Voting', $this->plugin_name ); ?></strong><?php echo __( '- you need to choose the appearance(Hand/Emoji) and let the users choose between those two opposing variants.', $this->plugin_name ); ?></p>
                                <p style='padding-left:10px;margin:0;'><strong><?php echo __( 'Versus', $this->plugin_name ); ?></strong> <?php echo __( '- you need to choose two different answers and let the user compare and choose one of them.  Moreover, you can select the versus direction, icon and position.', $this->plugin_name ); ?></p>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9 apm-types-row d-flex apm-pro-feature-block ays_poll_types_pro">
                        <div class="ays_poll_type_image_div col ">
                            <label for="type_choosing"
                                   class="<?= $poll['type'] == 'choosing' ? "apm_active_type" : ""; ?>">
                                <p><?= __('Choosing', $this->plugin_name) ?></p>
                                <img src="<?= POLL_MAKER_AYS_ADMIN_URL . '/images/polls/choosing.png' ?>"
                                     alt="<?= __('Choosing', $this->plugin_name) ?>">
                            </label>
                            <input type="radio" name="ays-poll-type" id="type_choosing"
                                   value="choosing" <?= $poll['type'] == 'choosing' ? "checked" : ""; ?>>
                        </div>
                        <div class="ays_poll_type_image_div col">
                            <label for="type_rating" class="<?= $poll['type'] == 'rating' ? "apm_active_type" : ""; ?>">
                                <p><?= __('Rating', $this->plugin_name) ?></p>
                                <img src="<?= POLL_MAKER_AYS_ADMIN_URL . '/images/polls/rating.png' ?>"
                                     alt="<?= __('Rating', $this->plugin_name) ?>">
                            </label>
                            <input type="radio" name="ays-poll-type" id="type_rating"
                                   value="rating" <?= $poll['type'] == 'rating' ? "checked" : ""; ?>>
                        </div>
                        <div class="ays_poll_type_image_div col">
                            <label for="type_voting" class="<?= $poll['type'] == 'voting' ? "apm_active_type" : ""; ?>">
                                <p><?= __('Voting', $this->plugin_name) ?></p>
                                <img src="<?= POLL_MAKER_AYS_ADMIN_URL . '/images/polls/voting.png' ?>"
                                     alt="<?= __('Voting', $this->plugin_name) ?>">
                            </label>
                            <input type="radio" name="ays-poll-type" id="type_voting"
                                   value="voting" <?= $poll['type'] == 'voting' ? "checked" : ""; ?>>
                        </div>
                        <div class="ays_poll_type_image_div col">
                            <label for="type_text" class="<?= $poll['type'] == 'text' ? "apm_active_type" : ""; ?>">
                                <p><?= __('Text', $this->plugin_name) ?></p>
                                <img src="<?= POLL_MAKER_AYS_ADMIN_URL . '/images/polls/text-type.png' ?>"
                                     alt="<?= __('Voting', $this->plugin_name) ?>">
                            </label>
                            <input type="radio" name="ays-poll-type" id="type_text"
                                   value="text" <?= $poll['type'] == 'text' ? "checked" : ""; ?>>
                        </div>
                        <div class="ays_poll_type_image_div_pro col">
	                        <div class="col-sm-12" style="padding:20px;">
			                    <div class="pro_features" style="justify-content:flex-end;">
			                        <div>
			                            <p style="font-size:12px; margin-right: 0;">
			                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
			                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
			                            </p>
			                        </div>
			                    </div>
                                <div class="ays_poll_type_image_div col">
                                    <label for="type_dropdown">
                                        <p><?= __('Dropdown', $this->plugin_name) ?></p>
                                        <img src="<?= POLL_MAKER_AYS_ADMIN_URL . '/images/polls/select.png' ?>"
                                            alt="<?= __('Dropdown', $this->plugin_name) ?>">
                                    </label>
                                    <input type="radio" class="ays-poll-types" value="dropdown">
                                </div>
                            </div>
                        </div>
                        <div class="ays_poll_type_image_div_pro col">
	                        <div class="col-sm-12" style="padding:20px;">
			                    <div class="pro_features" style="justify-content:flex-end;">
			                        <div>
			                            <p style="font-size:12px; margin-right: 0;">
			                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
			                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
			                            </p>
			                        </div>
			                    </div>
		                        <div class="col">
		                            <label>
		                                <p><?= __('Versus', $this->plugin_name); ?></p>
		                                <img src="<?= POLL_MAKER_AYS_ADMIN_URL . '/images/polls/versus.png' ?>"
		                                     alt="<?= __('Versus', $this->plugin_name); ?>"
		                                     title="<?= __("It is PRO version feature", $this->plugin_name); ?>">
		                            </label>
		                        </div>
	                        </div>
                        </div>
                        <div class="ays_poll_type_image_div_pro col">
	                        <div class="col-sm-12" style="padding:20px;">
			                    <div class="pro_features" style="justify-content:flex-end;">
			                        <div>
			                            <p style="font-size:12px; margin-right: 0;">
			                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
			                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
			                            </p>
			                        </div>
			                    </div>
                                <div class="ays_poll_type_image_div col">
                                    <label for="type_range" class="<?php echo $poll['type'] == 'range' ? "apm_active_type" : ""; ?>">
                                        <p><?= __('Range', $this->plugin_name) ?></p>
                                        <img src="<?php echo POLL_MAKER_AYS_ADMIN_URL . '/images/polls/range.png'; ?>" alt="<?php echo __('Range', $this->plugin_name) ?>" width='110' height='110'>
                                    </label>
                                    <input type="radio" name="ays-poll-type" id="type_range" class="ays-poll-types" value="range" <?php echo $poll['type'] == 'range' ? "checked" : ""; ?>/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                    </div>
                    <div class="col-sm-9">
                        <blockquote>
							<?= __('If you change the type, the number of counted answers will be annulled.', $this->plugin_name); ?>
                        </blockquote>
                    </div>
                </div>
                <hr>
                <div class="if-choosing apm-poll-type poll-type-block form-group ays-poll-type-block row ays_toggle_parent">
                    <div class="col-sm-12">
                        <div class="form-group row">
                            <div class="col-3">
                                <label for="ays_poll_allow_add_answers">
                                    <?= __('Allow custom answer', $this->plugin_name); ?>
                                    <a class="ays_help" data-toggle="tooltip"
                                    data-placement="top"
                                    title="<?= __("Allow users to add their custom answer.", $this->plugin_name); ?>">
                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                    </a>
                                </label>
                            </div>
                            <div class="col-1">
                                <input type="checkbox" name="ays_poll_allow_add_answers" id="ays_poll_allow_add_answers" class="ays_toggle_checkbox"
                                value="on" <?= $poll_allow_answer ?>>
                            </div>
                            <div class="col-sm-8">
                                <div class="form-group row allow_add_answers_not_show_up ays_toggle_target" <?= $poll_allow_answer ? '' : 'style="display:none;"'; ?>>
                                    <div class="col-sm-4">
                                        <label for="ays_poll_allow_answer_require">
                                            <?= __('Require admin approval', $this->plugin_name); ?>
                                            <a class="ays_help" data-toggle="tooltip"
                                            data-placement="top"
                                            title="<?= __("If the option is enabled, the answers added by users will require admin approval to be shown up inside the poll (public).", $this->plugin_name); ?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="checkbox" name="ays_poll_allow_answer_require" id="ays_poll_allow_answer_require"
                                            value="on" <?= $poll_allow_answer_require ?> />
                                    </div>
                                    <div class="col-sm-6 row">
                                        <div class="col-sm-4">
                                            <label for="ays_poll_require_approve_select_all" style="padding-top: 5px;">
                                                <?= __('Select all', $this->plugin_name); ?>
                                            </label>
                                        </div>
                                        <div class="col-sm-8 row" style="align-items: center;">
                                            <input type="checkbox" id="ays_poll_require_approve_select_all"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div> <!-- Allow custom answer -->
                <hr class="ays_hr_on" <?php echo $poll['type'] == 'choosing' ? "" : "style='display: none;'"?>/>
                <div class="if-choosing poll-type-block form-group ays-poll-type-block">
                    <div class="col-sm-3 ays-poll-type-block">
                        <div class="form-group row ays-poll-type-block">
                            <label for="ays-poll-answer">
								<?php echo __('Answers', $this->plugin_name); ?>
								<a class="ays-add-answer">
	                                <i class="ays_poll_fas ays_poll_fa-plus-square ays-click-once" title="<?= __("Add answer", $this->plugin_name); ?>" id="add-answer"></i>
	                            </a>
	                        </label>
                        </div>
                    </div>

                    <div class="col-sm-9">
                        <div class="form-group row">
                            <div class="col-sm-3">
                            	<label for="ays_redirect_after_submit" >
		                            <?php echo __('Redirect',$this->plugin_name)?>
		                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Enable redirection to the custom URL(s) after the user votes the poll. Assign different URLs to each answer separately.',$this->plugin_name)?>">
		                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
		                            </a>
		                        </label>
                            </div>
                            <div class="col-sm-9">
                            	<input type="checkbox" class="ays-enable-timer1 ays_toggle_checkbox" id="ays_redirect_after_submit" name="ays_redirect_after_submit" value="on" <?php echo $redirect_after_submit ? 'checked' : '' ?>/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="if-choosing poll-type-block form-group row ays-poll-type-block">
                	<div class="col-sm-12">
                		<table class="ays-answers-table" id="ays-answers-table" ays_default_count="<?php echo 2; ?>">
						    <thead>
						        <tr class="ui-state-default">
						            <th class="th-150"><?php echo __('Ordering', $this->plugin_name); ?></th>
						            <th style="width: 100vw;"><?php echo __('Answer', $this->plugin_name); ?></th>
						            <th class="th-350 ays-answer-redirect-row <?php echo ($redirect_after_submit) ? '' : 'ays_poll_display_none'; ?>"><?php echo __('Redirect', $this->plugin_name); ?></th>
						            <th class="th-150 only_pro"><?php echo __('Image', $this->plugin_name); ?></th>
						            <th class="th-150"><?php echo __('Delete', $this->plugin_name); ?></th>
						        </tr>
						    </thead>
						    <tbody class="">
						    <?php 
						    $answers = $poll['answers'];
						    $answers_count = (! empty($answers) ) ? count($answers) : $answer_default_count;
                            $loop_iteration = 0;
                            $rows_count = count($answers);
                            $ays_key_enter = "";
                            $user_add_html = '';
						    if (count($answers) > 0  && $poll['type'] == 'choosing') :
						    	foreach ($answers as $index => $answer) {
                                    $user_add_html = '';
						            $class = (($index + 1) % 2 == 0) ? "even" : "";
						            $answer_val = stripslashes(htmlentities($answer["answer"]));
						            $answer_id  = $answer["id"];
                                    $answer_img_class  = (isset($answer['answer_img']) && $answer['answer_img'] != '') ? 'display:block' : 'display:none;';
                                    $answer_img  = (isset($answer['answer_img']) && $answer['answer_img'] == '') ? '' : $answer['answer_img'];
						            $answer_redirect  = $answer['redirect'];
                                    $user_added = (isset($answer['user_added']) && $answer['user_added'] == 1) ? true : false;
                                    $show_user_added = (isset($answer['show_user_added']) && $answer['show_user_added'] == 1) ? true : false;

                                    if ((isset($options['poll_allow_answer']) && $options['poll_allow_answer'] == "on") 
                                        && (isset($options['poll_allow_answer_require']) && $options['poll_allow_answer_require'] == "on")) {
                                        $disable_show_user_added = '';
                                    }else{
                                        $disable_show_user_added = 'display: none;';
                                    }

                                    $show_user_added_checkbox = '';
                                    if ($show_user_added == 1) {
                                        $show_user_added_checkbox = 'checked';
                                    }
                                    if ( $user_added ) { 
                                        $user_add_html = '<input type="checkbox" class="ays_show_user_added" style="'. $disable_show_user_added .'" title="Show up on the poll" '. $show_user_added_checkbox .' />';
                                    }

                                    if(isset($answer['show_user_added']) && $answer['show_user_added'] == 1){
                                        $check_show_user_added = "1";
                                    }elseif (isset($answer['show_user_added']) && $answer['show_user_added'] == 0) {
                                        $check_show_user_added = "0";
                                    }else{
                                        $check_show_user_added = "1";
                                    }
                                    
                                    if($loop_iteration == $rows_count - 1){
                                        $ays_key_enter = "ays_poll_enter_key";
                                    }
                                    $loop_iteration++;
						            ?>
						            <tr class="ays-answer-row ui-state-default <?php echo $class; ?>">
						                <td class="ays-sort"><i class="ays_poll_fas ays_poll_fa_arrows" aria-hidden="true"></i></td>
                                        <td>
                                            <div class="ays_poll_display_flex">
                                                <input type="hidden" class="<?php echo ( $user_added ) ? 'ays_show_user_added_hid' : ''; ?>" name="ays_poll_show_user_added[]" value="<?= $check_show_user_added ?>" />
                                                <input type="text" class="ays-text-input ays-answer-value <?php echo $ays_key_enter;?>" name="ays-poll-answers[]" data-id="<?php echo $index;?>" value="<?php echo $answer_val; ?>">
                                                <?php echo $user_add_html; ?>
                                                <input type="hidden" name="ays-poll-answers-ids[]" data-id="<?php echo $index;?>" value="<?php echo $answer_id; ?>">
                                            </div>
                                        </td>
						                <td class="ays-answer-redirect-row <?php echo ($redirect_after_submit) ? '' : 'ays_poll_display_none'; ?>">
                                        	<input type="text" class="ays-text-input ays_redirect_active" id="ays_submit_redirect_url_<?php echo $answer_id; ?>" name="ays_submit_redirect_url[]" value="<?php echo $answer_redirect; ?>"/>
						                </td>
						                <td>
						                    <label class='ays-label' for='ays-answer'>
						                        <a class="ays-poll-add-answer-image" style="<?php echo ($answer_img == '') ? 'display:block;' : 'display:none'; ?>"><?php echo __('Add' , $this->plugin_name)?></a>
						                    </label>
                                            <div class="ays-poll-answer-image-container" style="<?php echo $answer_img_class; ?>">
                                                <span class="ays-poll-remove-answer-img"></span>
                                                <img src="<?php echo $answer_img; ?>" class="ays-poll-answer-img"/>
                                                <input type="hidden" name="ays-poll-answers-images[]" class="ays-poll-answer-image-path" value="<?php echo $answer_img; ?>">
                                            </div>
						                </td>
						                <td>
						                    <a href="javascript:void(0)" class="ays-delete-answer" data-id="<?php echo $index;?>" data-lid="<?php echo $index;?>">
						                        <i class="ays_poll_fas ays_poll_fa_minus_square" aria-hidden="true"></i>
						                    </a>
						                </td>
						            </tr>
						            <?php
						        }
						        ?>
						    <?php
						    else:
						        for ($dac_i=0; $dac_i < intval($answer_default_count); $dac_i++) :
                                    if($loop_iteration == intval($answer_default_count) - 1){
                                        $ays_key_enter = "ays_poll_enter_key";
                                    }
                                    $loop_iteration++;
					                $ays_even_or_not =  ($dac_i%2 !=0) ? 'even' : '';
						        ?>
						        <tr class="ays-answer-row ui-state-default <?php echo $ays_even_or_not; ?>">
						            <td class="ays-sort"><i class="ays_poll_fas ays_poll_fa_arrows" aria-hidden="true"></i></td>
                                    <td class="ays-choosing-answer-container">
                                        <div class="ays_poll_display_flex">
                                            <input type="text" class="ays-text-input ays-answer-value <?php echo $ays_key_enter;?>" name="ays-poll-answers[]" data-id="<?php echo $dac_i;?>" value="<?= __("Answer ".($dac_i+1), $this->plugin_name); ?>">
                                            <?php echo $user_add_html; ?>
                                            <input type="hidden" name="ays-poll-answers-ids[]" data-id="<?php echo $dac_i;?>" value="0">
                                        </div>
                                    </td>
						            <td class="ays-answer-redirect-row <?php echo ($redirect_after_submit) ? '' : 'ays_poll_display_none'; ?>">
                                    	<input type="text" class="ays-text-input ays_redirect_active" id="ays_submit_redirect_url_<?php echo $dac_i; ?>" name="ays_submit_redirect_url[]" value=""/>
					                </td>
						            <td>
						                <label class='ays-label' for='ays-answer'>
						                    <a class="ays-poll-add-answer-image"><?php echo __('Add',$this->plugin_name); ?></a>
						                </label>
                                        <div class="ays-poll-answer-image-container" style="display:none;">
                                            <span class="ays-poll-remove-answer-img"></span>
                                            <img src="" class="ays-poll-answer-img"/>
                                            <input type="hidden" name="ays-poll-answers-images[]" class="ays-poll-answer-image-path" value=""/>
                                        </div>
						            </td>

						            <td>
						                <a href="javascript:void(0)" class="ays-delete-answer" data-id="<?php echo $dac_i;?>" data-lid="<?php echo $dac_i;?>">
						                    <i class="ays_poll_fas ays_poll_fa_minus_square" aria-hidden="true"></i>
						                </a>
						            </td>
						        </tr>
						        <?php
					             endfor;
						    endif;
						    ?>
						    </tbody>
						</table>						
						<input type="hidden" id="ays_poll_answers_count" value="<?php echo $answers_count ?>">
						<input type="hidden" id="ays_answer_checker" value="<?php echo $checking_answer_hover_live ?>">
                	</div>
                </div>
                <div class="if-choosing ays-answers-toolbar-bottom poll-type-block" >
                    <label for="ays-poll-answer">
                        <?php echo __('Answers', $this->plugin_name); ?>
                        <a class="ays-add-answer">
                            <i class="ays_poll_fas ays_poll_fa-plus-square" title="<?= __("Add answer", $this->plugin_name); ?>" id="add-answer"></i>
                        </a>
                    </label>
                </div>
                <div class="if-voting poll-type-block  form-group row">
                    <div class="col-sm-3">
                        <label for="ays-poll-vote-type">
							<?= __('Answers', $this->plugin_name); ?><a class="ays_help" data-toggle="tooltip" data-placement="top" title="<?= __("Select the appearance of the poll.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a></label>
                    </div>
                    <div class="col-sm-9 answers-col">
                        <select class="ays-select" id="ays-poll-vote-type" name="ays-poll-vote-type">
                            <option value='hand' <?= $poll['view_type'] == 'hand' ? "selected" : ""; ?>>
								<?= __('Hand', $this->plugin_name); ?>
                            </option>
                            <option value="emoji" <?= $poll['view_type'] == 'emoji' ? "selected" : ""; ?>>
								<?= __('Emoji', $this->plugin_name); ?>
                            </option>
                        </select>
                        <?php    
                        switch ($poll['view_type']) {
                            case 'hand':
                                $vote_res = 'ays_poll_far ays_poll_fa-thumbs-up';
                                $rate_res = 'ays_poll_fas ays_poll_fa-star';
                                break;

                            case 'emoji':
                                $vote_res = 'ays_poll_fas ays_poll_fa-smile';
                                $rate_res = 'ays_poll_fas ays_poll_fa-smile';
                                break;

                            case 'star':
                                $rate_res = 'ays_poll_fas ays_poll_fa-star';
                                $vote_res = 'ays_poll_far ays_poll_fa-thumbs-up';
                                break;
                            
                            default:
                                $vote_res = 'ays_poll_far ays_poll_fa-thumbs-up';
                                $rate_res = 'ays_poll_fas ays_poll_fa-star';
                                break;
                        }
						?>
                        <i id="vote-res" class="<?php echo $vote_res; ?>"></i>
                    </div>
                </div>
                <div class="if-rating poll-type-block  form-group row">
                    <div class="col-sm-3">
                        <label for="ays-poll-rate-type">
							<?= __('Answers', $this->plugin_name); ?><a class="ays_help" data-toggle="tooltip" data-placement="top" title="<?= __("Select the appearance and the scale of assessment of the poll.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a></label>
                    </div>
                    <div class="col-sm-9 answers-col">
                        <select class="ays-select" id="ays-poll-rate-type" name="ays-poll-rate-type">
                            <option value='star' <?= $poll['view_type'] == 'star' ? "selected" : ""; ?>>
								<?= __('Stars', $this->plugin_name); ?>
                            </option>
                            <option value="emoji" <?= $poll['view_type'] == 'emoji' ? "selected" : ""; ?>>
								<?= __('Emoji', $this->plugin_name); ?>
                            </option>
                        </select>
                        <select class="ays-select" id="ays-poll-rate-value" name="ays-poll-rate-value">
                            <option value="<?= count($poll['answers']); ?>" selected>
								<?= count($poll['answers']); ?>
                            </option>
                        </select>
                        <i id="rate-res" class="<?php echo $rate_res; ?>"></i>
                    </div>
                </div>
                <div class="if-text poll-type-block form-group row">
                    <div class="col-sm-3">
                        <label for="">
							<?= __('Choose text type', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" data-html="true" data-placement="top" title="<p style='margin-bottom:3px;'>
                                    <?php echo __( 'Choose the type of the question:' , $this->plugin_name ); ?>
										<p style='padding-left:10px;margin-bottom:3px;'><strong><?php echo __( 'Short text', $this->plugin_name ); ?></strong><?php echo __( ' - a question that requires to be answered by writing short text.' , $this->plugin_name ); ?></p>
		                                <p style='padding-left:10px;margin-bottom:3px;'><strong><?php echo __( 'Paragraph', $this->plugin_name ); ?></strong><?php echo __( ' - a question that requires to be answered by writing text.', $this->plugin_name ); ?></p>">
                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9 answers-col row">
                        <div class="d-flex" style="padding: 0 15px;">
                            <div class="form-check-inline">
                                <input type="radio" id="ays_poll_text_type_short" name="ays_poll_text_type" value="short_text" class="ays-poll-text-types-type" <?php echo ($poll_view_type_for_text == "short_text") ? "checked" : ""; ?>>
                                <label for="ays_poll_text_type_short"><?php echo __("Short text", $this->plugin_name);?>
                                </label>
                            </div>
                            <div class="form-check-inline">
                                <input type="radio" id="ays_poll_text_type_paragraph" name="ays_poll_text_type" value="paragraph" class="ays-poll-text-types-type" <?php echo ($poll_view_type_for_text == "paragraph") ? "checked" : ""; ?>>
                                <label for="ays_poll_text_type_paragraph"><?php echo __("Paragraph", $this->plugin_name);?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="ays_hr_on_text" <?php echo $poll['type'] == 'text' ? "" : "style='display: none;'"?>>
                <div class="if-text poll-type-block form-group row">
                    <div class="col-sm-3">
                        <label for='ays_poll_text_type_placeholder'>
							<?= __("Placeholder", $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
                               title="<?= __("Write your preferred word to show in the placeholder field.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-3 ays_divider_left">
                        <input type="text" name="ays_poll_text_type_placeholder" id="ays_poll_text_type_placeholder" value="<?php echo $poll_text_type_placeholder?>">
                    </div>
                </div>
                <hr class="ays_hr_on_text" <?php echo $poll['type'] == 'text' ? "" : "style='display: none;'"?>>
                <div class="if-text poll-type-block form-group row">
                    <div class="col-sm-3">
                        <label for='ays_poll_text_type_width'>
							<?= __("Width", $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
                               title="<?= __("Specify the width of the text field.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-3 ays_divider_left">
                        <input type="number" name="ays_poll_text_type_width" id="ays_poll_text_type_width" value="<?php echo $poll_text_type_width; ?>">
                    </div>
                    <div class="col-sm-6">
                        <select class="ays-text-input ays-text-input-short ays_enable_answer_field ays_poll_select_short" name="ays_poll_text_type_width_type" id="ays_poll_text_type_width_type">
                            <option value="percent" <?php echo ($poll_text_type_width_type == "percent") ? "selected" : ""; ?>>%</option>
                            <option value="pixel"   <?php echo ($poll_text_type_width_type == "pixel") ? "selected" : ""; ?>>px</option>
                        </select>
                    </div>
                </div>
                <hr class="ays_hr_on_text" <?php echo $poll['type'] == 'text' ? "" : "style='display: none;'"?>>
                <div class="if-text poll-type-block form-group row ays_toggle_parent">
                    <div class="col-sm-3">
                        <label for="ays_poll_enable_question_length">
							<?= __('Maximum length of a text field', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" data-html="true" data-placement="top" title="<?php echo __( 'Restrict the number of characters/words to be inserted in the text field by the user.' , $this->plugin_name ); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1 ays_divider_left">
                        <input type="checkbox" class="ays-enable-timer1 ays_toggle_checkbox" id="ays_poll_enable_question_length" name="ays_poll_enable_question_length" value="on" <?php echo ($poll_text_type_length_enable) ? 'checked' : ''; ?>>                           
                    </div>
                    <div class="col-sm-7 ays_toggle_target ays_divider_left <?php echo ($poll_text_type_length_enable) ? '' : 'display_none'; ?>">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label for="ays_poll_question_limit_text_type">
                                    <?php echo __('Limit by', $this->plugin_name); ?>
                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Choose your preferred type of limitation.',$this->plugin_name); ?>">
                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                    </a>
                                </label>
                            </div>
                            <div class="col-sm-8">
                                <select class="ays-text-input ays-text-input-select" id="ays_poll_question_limit_text_type" name="ays_poll_question_limit_text_type" style="max-width: 100%;">
                                    <option value='characters' <?php echo ($poll_text_type_limit_type == 'characters') ? 'selected' : '' ?> ><?php echo __( 'Characters' , $this->plugin_name ); ?></option>
                                    <option value='words' <?php echo ($poll_text_type_limit_type == 'words') ? 'selected' : '' ?> ><?php echo __( 'Words' , $this->plugin_name ); ?></option>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label for="ays_poll_question_text_max_length">
                                    <?php echo __('Length', $this->plugin_name); ?>
                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Indicate the length of the characters/words.',$this->plugin_name); ?>">
                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                    </a>
                                </label>
                            </div>
                            <div class="col-sm-8">
                                <input type="number" id="ays_poll_question_text_max_length" class="ays-text-input" name="ays_poll_question_text_max_length" value="<?php echo $poll_text_type_limit_length; ?>" style="width: 100%;">
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label for="ays_poll_question_enable_text_message">
                                    <?php echo __('Show word/character counter', $this->plugin_name); ?>
                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Tick the checkbox and the live box will appear under the text field. It will indicate the current state of word/character usage.',$this->plugin_name); ?>">
                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                    </a>
                                </label>
                            </div>
                            <div class="col-sm-8">
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" id="ays_poll_question_enable_text_message" name="ays_poll_question_enable_text_message" value="on" <?php echo ($poll_text_type_limit_message) ? "checked" : ""; ?> />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
            </div>
            <div id="tab2" class="ays-poll-tab-content <?= $active_tab == 'Styles' ? 'ays-poll-tab-content-active' : ''; ?>">
                <p class="ays-subtitle">
					<?= __('Styling options', $this->plugin_name); ?>
                </p>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-1">
                        <label for='ays-poll-theme' class="ays_label_flex">
							<?= __('Theme', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
                               title="<?= __("Choose your preferred, ready to use template and customize it with the options below.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-11 apm-themes-row d-flex apm-pro-feature-block" data-themeid="<?php echo $poll['theme_id']; ?>">
                        <div class="ays_poll_theme_image_div col">
                            <label for="theme_minimal" <?= ($poll['theme_id'] == 3) ? 'class="apm_active_theme"' : '' ?>>
                                <p><?= __('Minimal', $this->plugin_name) ?></p>
                                <img src="<?= POLL_MAKER_AYS_ADMIN_URL . '/images/themes/minimal.png' ?>"
                                     alt="<?= __('Minimal', $this->plugin_name) ?>">
                            </label>
                            <input type="radio" name="ays_poll_theme" id="theme_minimal"
                                   value="3" <?= ($poll['theme_id'] == 3) ? 'checked' : '' ?>>
                        </div>
                        <div class="ays_poll_theme_image_div col">
                            <label for="theme_classic_light" <?= ($poll['theme_id'] <= 1) ? 'class="apm_active_theme"' : '' ?>>
                                <p><?= __('Classic Light', $this->plugin_name) ?></p>
                                <img src="<?= POLL_MAKER_AYS_ADMIN_URL . '/images/themes/ClassicLight.png' ?>"
                                     alt="<?= __('Classic Light', $this->plugin_name) ?>">
                            </label>
                            <input type="radio" name="ays_poll_theme" id="theme_classic_light"
                                   value="1" <?= ($poll['theme_id'] <= 1) ? 'checked' : '' ?>>
                        </div>
                        <div class="ays_poll_theme_image_div col">
                            <label for="theme_classic_dark" <?= ($poll['theme_id'] == 2) ? 'class="apm_active_theme"' : '' ?>>
                                <p><?= __('Classic Dark', $this->plugin_name) ?></p>
                                <img src="<?= POLL_MAKER_AYS_ADMIN_URL . '/images/themes/ClassicDark.png' ?>"
                                     alt="<?= __('Classic Dark', $this->plugin_name) ?>">
                            </label>
                            <input type="radio" name="ays_poll_theme" id="theme_classic_dark"
                                   value="2" <?= ($poll['theme_id'] == 2) ? 'checked' : '' ?>>
                        </div>
                        <div class="col">
	                        <div class="col-sm-12" style="padding:20px;">
			                    <div class="pro_features" style="justify-content:flex-end;">
			                        <div style="margin-right:20px;">
			                            <p style="font-size:20px;">
			                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
			                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
			                            </p>
			                        </div>
			                    </div>
		                        <div class="ays_poll_theme_image_div_pro">
			                        <div class="ays_poll_theme_image_div col apm-pro-feature">
			                            <label>
			                                <p>Light Shape</p>
			                                <img src="<?= POLL_MAKER_AYS_ADMIN_URL . '/images/themes/LightShape.png' ?>"
			                                     alt="Light Shape"
			                                     title="<?= __("It is PRO version feature", $this->plugin_name); ?>">
			                            </label>
			                        </div>
			                        <div class="ays_poll_theme_image_div col apm-pro-feature">
			                            <label>
			                                <p>Dark Shape</p>
			                                <img src="<?= POLL_MAKER_AYS_ADMIN_URL . '/images/themes/DarkShape.png' ?>"
			                                     alt="Light Shape"
			                                     title="<?= __("It is PRO version feature", $this->plugin_name); ?>">
			                            </label>
			                        </div>
			                        <div class="ays_poll_theme_image_div col apm-pro-feature">
			                            <label>
			                                <p>Coffee Fluid</p>
			                                <img src="<?= POLL_MAKER_AYS_ADMIN_URL . '/images/themes/CoffeeFluid.png' ?>"
			                                     alt="Coffee Fluid"
			                                     title="<?= __("It is PRO version feature", $this->plugin_name); ?>">
			                            </label>
			                        </div>
			                        <div class="ays_poll_theme_image_div col apm-pro-feature">
			                            <label>
			                                <p><?= __("Aquamarine", $this->plugin_name) ?></p>
			                                <img src="<?= POLL_MAKER_AYS_ADMIN_URL . '/images/themes/Aquamarine.png' ?>"
			                                     alt="Aquamarine"
			                                     title="<?= __("It is PRO version feature", $this->plugin_name); ?>">
			                            </label>
			                        </div>	                        
		                        </div>
	                        </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="col">
                    <div class="row">
                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays-poll-main-color'>
										<?= __('Main Color', $this->plugin_name); ?>
                                        <a class="ays_help"
                                           data-toggle="tooltip"
                                           data-placement="top"
                                           title="<?= __("Specify the color of the poll's main attributes. It includes border color, the color of the rate percentage and the background color of the vote button.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="text" class="ays-text-input" data-alpha="true" id='ays-poll-main-color'
                                           name='ays_poll_main_color'
                                           value="<?= !empty($options['main_color']) ? $options['main_color'] : $default_colors['main_color']; ?>"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays-poll-text-color'>
										<?= __('Text Color', $this->plugin_name); ?>
                                        <a class="ays_help"
                                           data-toggle="tooltip"
                                           data-placement="top"
                                           title="<?= __("Specify the color of the text written inside the poll.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="text" class="ays-text-input" data-alpha="true" id='ays-poll-text-color'
                                           name='ays_poll_text_color'
                                           value="<?= !empty($options['text_color']) ? $options['text_color'] : $default_colors['text_color']; ?>"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays-poll-icon-color'>
										<?= __('Icons Color', $this->plugin_name); ?>
                                        <a class="ays_help"
                                           data-toggle="tooltip"
                                           data-placement="top"
                                           title="<?= __("Specify the icon color in voting and rating types.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="text" class="ays-text-input" data-alpha="true" id='ays-poll-icon-color'
                                           name='ays_poll_icon_color'
                                           value="<?= !empty($options['icon_color']) ? $options['icon_color'] : $default_colors['icon_color']; ?>"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays-poll-bg-color'>
										<?= __('Background Color', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" data-placement="top" title="<?= __("Specify the background color of the poll.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="text" class="ays-text-input" data-alpha="true" id='ays-poll-bg-color'
                                           name='ays_poll_bg_color'
                                           value="<?= !empty($options['bg_color']) ? $options['bg_color'] : $default_colors['bg_color']; ?>"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays-poll-bg-image'>
										<?= __('Background Image', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" data-placement="top"
                                           title="<?= __("Add a background image to the poll. If you add a background image, the background color will not be applied.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <a href="javascript:void(0)" class="add-bg-image button">
										<?= $image_text_bg; ?>
                                    </a>
                                    <div class="form-group row" style="<?= $style_bg; ?>">
		                                <div class="ays-poll-bg-image-container">
		                                    <span class="ays-remove-bg-img"></span>
		                                    <img src="<?= isset($options['bg_image']) ? $options['bg_image'] : ""; ?>"
		                                         id="ays-poll-bg-img"/>
		                                    <input type="hidden" name="ays_poll_bg_image" id="ays-poll-bg-image"
		                                           value="<?= isset($options['bg_image']) ? $options['bg_image'] : ""; ?>"/>
                                            <input type="hidden" name="ays_poll_bg_image-pos" id="ays-poll-bg-image-pos"
                                           value="<?= isset($options['poll_bg_image_position']) ? $options['poll_bg_image_position'] : ""; ?>"/>
		                                </div>
		                            </div>
                                    <hr>
                                    <div class="form-group row">
				                        <div class="col-sm-12">
				                            <label for="ays_poll_bg_image_position">
				                                <?php echo __( "Background image position", $this->plugin_name ); ?>
				                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('The position of background image of the polls',$this->plugin_name)?>">
				                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
				                                </a>
				                            </label>
				                            <select id="ays_poll_bg_image_position" name="ays_poll_bg_image_position" class="ays-text-input ays-text-input-short" style="display:inline-block;">
				                                <option value="left top" <?php echo $poll_bg_image_position == "left top" ? "selected" : ""; ?>><?php echo __( "Left Top", $this->plugin_name ); ?></option>
				                                <option value="left center" <?php echo $poll_bg_image_position == "left center" ? "selected" : ""; ?>><?php echo __( "Left Center", $this->plugin_name ); ?></option>
				                                <option value="left bottom" <?php echo $poll_bg_image_position == "left bottom" ? "selected" : ""; ?>><?php echo __( "Left Bottom", $this->plugin_name ); ?></option>
				                                <option value="center top" <?php echo $poll_bg_image_position == "center top" ? "selected" : ""; ?>><?php echo __( "Center Top", $this->plugin_name ); ?></option>
				                                <option value="center center" <?php echo $poll_bg_image_position == "center center" ? "selected" : ""; ?>><?php echo __( "Center Center", $this->plugin_name ); ?></option>
				                                <option value="center bottom" <?php echo $poll_bg_image_position == "center bottom" ? "selected" : ""; ?>><?php echo __( "Center Bottom", $this->plugin_name ); ?></option>
				                                <option value="right top" <?php echo $poll_bg_image_position == "right top" ? "selected" : ""; ?>><?php echo __( "Right Top", $this->plugin_name ); ?></option>
				                                <option value="right center" <?php echo $poll_bg_image_position == "right center" ? "selected" : ""; ?>><?php echo __( "Right Center", $this->plugin_name ); ?></option>
				                                <option value="right bottom" <?php echo $poll_bg_image_position == "right bottom" ? "selected" : ""; ?>><?php echo __( "Right Bottom", $this->plugin_name ); ?></option>
				                            </select>
				                        </div>
				                    </div>
				                    <hr>
				                    <div class="form-group row">
				                        <div class="col-sm-8">
				                            <label for="ays_poll_bg_img_in_finish_page">
				                                <?php echo __( "Hide background image on result page", $this->plugin_name ); ?>
				                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('If this option is enabled background image of poll will disappear on the result page.',$this->plugin_name)?>">
				                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
				                                </a>
				                            </label>
				                        </div>
				                        <div class="col-sm-4">
				                            <input type="checkbox" class="ays_toggle ays_toggle_slide"
				                                   id="ays_poll_bg_img_in_finish_page"
				                                   name="ays_poll_bg_img_in_finish_page"
				                                    <?php echo ($poll_bg_img_in_finish_page) ? 'checked' : ''; ?>/>
				                            <label for="ays_poll_bg_img_in_finish_page" style="display:inline-block;margin-left:10px;" class="ays_switch_toggle">Toggle</label>
				                        </div>
				                    </div>
                                </div>
                            </div>                          
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays-poll-box-shadow-color'><?= __('Answer styles', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" data-placement="top"
                                           title="<?= __("Highlight the background of the answers' boxes. The option works only with choosing type.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="checkbox" class="ays_toggle ays_toggle_slide"
                                           id="ays_poll_enable_answer_style"
                                           name="ays_poll_enable_answer_style" <?= ($options['enable_answer_style'] == 'on') ? 'checked' : ''; ?>>
                                    <label for="ays_poll_enable_answer_style" class="ays_switch_toggle">Toggle</label>
                                    <div class="col-sm-12 ays_toggle_target ays_divider_top ays_answer_style"
                                         style="margin-top: 10px; padding-top: 10px; <?= ($options['enable_answer_style'] == 'on') ? '' : 'display:none;' ?>">
                                        <label for="ays-poll-box-shadow-color">
											<?= __('Answers Background Color', $this->plugin_name) ?>
                                            <a class="ays_help" data-toggle="tooltip"
                                               title="<?= __("Specify the background color of the answers' boxes.", $this->plugin_name) ?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                        <input type="text" class="ays-text-input" data-alpha="true"
                                           id='ays-poll-answer-bg-color'
                                           name='ays_poll_answer_bg_color'
                                           value="<?= isset($options['answer_bg_color']) && !empty($options['answer_bg_color']) ? $options['answer_bg_color'] : 'rgba(255,255,255,0)' ?>"/>
                                    </div>
	                                <div class="col-sm-12 ays_toggle_target ays_divider_top ays_answer_style" style="margin-top: 10px; padding-top: 10px; <?= ($options['enable_answer_style'] == 'on') ? '' : 'display:none;' ?>">
                                        <div>
                                            <label for='ays-poll-border-side'>
                                                <?= __('Border side', $this->plugin_name); ?>
                                                <a class="ays_help"
                                                data-toggle="tooltip"
                                                data-placement="top"
                                                title="<?= __("Choose your preferred style for the border of the answers' boxes.", $this->plugin_name); ?>">
                                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                </a>
                                            </label>                                        
                                        </div>
                                        <div id="ays-poll-border-side-box">
                                            <select name="ays_poll_border_side" id="ays-poll-border-side"
                                                    class="ays-select ays-select-search">
                                                <option value="all_sides" <?= isset($options['answer_border_side']) && $options['answer_border_side'] == "all_sides" ? 'selected' : ''; ?>>
                                                    <?= __("All sides", $this->plugin_name); ?>
                                                </option>
                                                <option value="none" <?= isset($options['answer_border_side']) && $options['answer_border_side'] == "none" ? 'selected' : ''; ?>>
                                                    <?= __("None", $this->plugin_name); ?>
                                                </option>
                                                <option value="top" <?= isset($options['answer_border_side']) && $options['answer_border_side'] == "top" ? 'selected' : ''; ?>>
                                                    <?= __("Top", $this->plugin_name); ?>
                                                </option>
                                                <option value="bottom" <?= isset($options['answer_border_side']) && $options['answer_border_side'] == "bottom" ? 'selected' : ''; ?>>
                                                    <?= __("Bottom", $this->plugin_name); ?>
                                                </option>
                                                <option value="left" <?= isset($options['answer_border_side']) && $options['answer_border_side'] == "left" ? 'selected' : ''; ?>>
                                                    <?= __("Left", $this->plugin_name); ?>
                                                </option>
                                                <option value="right" <?= isset($options['answer_border_side']) && $options['answer_border_side'] == "right" ? 'selected' : ''; ?>>
                                                    <?= __("Right", $this->plugin_name); ?>
                                                </option>	                                        
                                            </select>                                        
                                        </div>
	                                </div>
                                    <div class="col-sm-12 ays_toggle_target ays_divider_top ays_answer_style" style="margin-top: 10px; padding-top: 10px; <?= ($options['enable_answer_style'] == 'on') ? '' : 'display:none;' ?>">
                                        <div>
                                            <label for='ays_answer_font_size'>
                                                    <?= __('Answer font size', $this->plugin_name); ?>
                                                    <a class="ays_help"
                                                    data-toggle="tooltip"
                                                    data-placement="top"
                                                    title="<?= __("Choose your preferred size for the font of the answers. Size should be not less than 5 and not higher than 90", $this->plugin_name); ?>">
                                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                    </a>
                                            </label>                                        
                                        </div>                                        
                                        <div class="ays_answer_font_size_box">
                                            <input type="number" class="ays-text-input ays-text-input-short ays-poll-answer-font-size-all" name="ays_answer_font_size" id="ays_answer_font_size" value="<?php echo $poll_answer_font_size;?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-12 ays_toggle_target ays_divider_top ays_answer_style" style="margin-top: 10px; padding-top: 10px; <?= ($options['enable_answer_style'] == 'on') ? '' : 'display:none;' ?>">
                                        <div>
                                            <label for='ays_poll_answer_font_size_mobile'>
                                                    <?= __('Answer font size for mobile', $this->plugin_name); ?>
                                                    <a class="ays_help"
                                                    data-toggle="tooltip"
                                                    data-placement="top"
                                                    title="<?= __("Choose your preferred size for the font of the answers on mobile devies. Size should be not less than 5 and not higher than 90", $this->plugin_name); ?>">
                                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                    </a>
                                            </label>                                        
                                        </div>                                        
                                        <div class="ays_answer_font_size_box">
                                            <input type="number" class="ays-text-input ays-text-input-short ays-poll-answer-font-size-all" name="ays_poll_answer_font_size_mobile" id="ays_poll_answer_font_size_mobile" data-device="mobile" value="<?php echo $poll_answer_font_size_mobile;?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-12 ays_toggle_target ays_divider_top ays_answer_style" style="margin-top: 10px; padding-top: 10px; <?= ($options['enable_answer_style'] == 'on') ? '' : 'display:none;' ?>">
                                        <div>
                                            <label for="ays_ans_img_height" class="ays_enable_answer_style">
                                                <?php echo __('Answer image height (px)',$this->plugin_name)?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Height of answers images.',$this->plugin_name)?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div>
                                            <input type="number" class="ays-text-input ays_enable_answer_field" id='ays_poll_answer_img_height' name='ays_poll_answer_img_height' value="<?php echo $poll_answer_image_height; ?>" />
                                        </div>
                                    </div> <!-- Answers image height -->
                                    <div class="col-sm-12 ays_toggle_target ays_divider_top ays_answer_style" style="margin-top: 10px; padding-top: 10px; <?= ($options['enable_answer_style'] == 'on') ? '' : 'display:none;' ?>">
                                        <div >
                                            <label for="ays_poll_background_size">
                                                <?php echo __('Answer image object fit' , $this->plugin_name); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Specify how an answers’ images should be resized to fit its container.',$this->plugin_name); ?>">
                                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div id="ays_poll_image_background_size_box">
                                            <select id="ays_poll_image_background_size" name="ays_poll_image_background_size" class="ays-select">
                                                <option value="cover" <?php echo ($poll_answer_object_fit == 'cover') ? 'selected' : ''; ?>>
                                                    <?php echo __('Cover',$this->plugin_name)?>
                                                </option>
                                                <option value="fill" <?php echo ($poll_answer_object_fit == 'fill') ? 'selected' : ''; ?>>
                                                    <?php echo __('Fill',$this->plugin_name)?>
                                                </option>
                                                <option value="contain" <?php echo ($poll_answer_object_fit == 'contain') ? 'selected' : ''; ?>>
                                                    <?php echo __('Contain',$this->plugin_name)?>
                                                </option>
                                                <option value="scale-down" <?php echo ($poll_answer_object_fit == 'scale-down') ? 'selected' : ''; ?>>
                                                    <?php echo __('Scale-down',$this->plugin_name)?>
                                                </option>
                                                <option value="none" <?php echo ($poll_answer_object_fit == 'none') ? 'selected' : ''; ?>>
                                                    <?php echo __('None',$this->plugin_name)?>
                                                </option>
                                            </select>
                                        </div>
                                    </div> <!-- Answer image object fit -->
                                    <div class="col-sm-12 ays_toggle_target ays_divider_top ays_answer_style" style="margin-top: 10px; padding-top: 10px; <?= ($options['enable_answer_style'] == 'on') ? '' : 'display:none;' ?>">
                                        <div>
                                            <label for="ays_answers_padding" class="ays_enable_answer_style">
                                                <?php echo __('Answer padding (px)',$this->plugin_name)?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Padding of answers.',$this->plugin_name)?>">
                                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div>
                                            <input type="number" class="ays-text-input ays_enable_answer_field" id='ays_poll_answers_padding' name='ays_poll_answers_padding' value="<?php echo $poll_answer_padding; ?>"/>
                                        </div>
                                    </div> <!-- Answers padding -->
                                    <div class="col-sm-12 ays_toggle_target ays_divider_top ays_answer_style" style="margin-top: 10px; padding-top: 10px; <?= ($options['enable_answer_style'] == 'on') ? '' : 'display:none;' ?>">
                                        <div >
                                            <label for="ays_answers_margin" class="ays_enable_answer_style">
                                                <?php echo __('Answer gap (px)',$this->plugin_name)?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Gap between answers.',$this->plugin_name)?>">
                                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div>
                                            <input type="number" class="ays-text-input ays_enable_answer_field" id='ays_poll_answers_margin' name='ays_poll_answers_margin' value="<?php echo $poll_answer_margin; ?>" />
                                        </div>
                                    </div> <!-- Answers gap -->
                                    <div class="col-sm-12 ays_toggle_target ays_divider_top ays_answer_style" style="margin-top: 10px; padding-top: 10px; <?= ($options['enable_answer_style'] == 'on') ? '' : 'display:none;' ?>">
                                        <div>
                                            <label for="ays_answers_border_radius">
                                                <?php echo __('Answer border radius',$this->plugin_name)?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Specify the radius of the answers container.',$this->plugin_name)?>">
                                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div>
                                            <input type="number" class="ays-text-input ays_enable_answer_field" name="ays_poll_answer_border_radius" id="ays_poll_answer_border_radius" value="<?php echo $poll_answer_border_radius; ?>"/>
                                        </div>
                                    </div> <!-- Answers border radius -->
                                </div>
                            </div>                            
                            <hr>                            
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_show_answers_icon">
                                        <?php echo __('Answer icon',$this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Choose your preferred icon for the answers.',$this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="checkbox" class="ays_toggle ays_toggle_slide" id="ays_poll_show_answers_icon" name="ays_poll_show_answers_icon" <?php echo ($poll_answer_icon_check) ? 'checked' : ''; ?> />
                                    <label for="ays_poll_show_answers_icon" class="ays_switch_toggle" style="margin-bottom: 15px;">Toggle</label>
                                    <div class="col-sm-12 ays_toggle_target ays_divider_top <?php echo ($poll_answer_icon_check) ? '' : 'display_none'; ?>" style="padding-top: 10px;">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label ays_poll_answer_icon" for="poll_answer_icon_radio"> 
                                                <input type="radio" class="ays_poll_answ_icon" id="poll_answer_icon_radio" name="ays_poll_answer_icon" value="radio" <?= ($poll_answer_icon == 'radio') ? 'checked' : ''; ?> />
                                        </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                        <label class="form-check-label ays_poll_answer_icon" for="poll_answer_icon_checkbox"> 
                                                <input type="radio" class="ays_poll_answ_icon" id="poll_answer_icon_checkbox" name="ays_poll_answer_icon" value="checkbox" <?= ($poll_answer_icon == 'checkbox') ? 'checked' : ''; ?> />
                                        </label>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- Answer icon -->
			                <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_answers_view" class="ays_enable_answer_style">
                                        <?php echo __('Answer view',$this->plugin_name)?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Select the view of the answers: List or Grid. Select the column number if you have chosen Grid view.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <div class="form-group row">
                                        <div class="col-sm-8 ays_divider_left">
                                            <select class="ays-text-input ays-text-input-short ays_enable_answer_field" id="ays_answers_view" name="ays_poll_choose_answer_type">
                                                <option value="list" <?php echo ($poll_answer_view_type == 'list') ? 'selected' : ''; ?>>
                                                    <?php echo __('List',$this->plugin_name)?>
                                                </option>
                                                <option value="grid" <?php echo ($poll_answer_view_type == 'grid') ? 'selected' : ''; ?>>
                                                    <?php echo __('Grid',$this->plugin_name)?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4 grid_column_count" style="display:<?#php echo $dispaly_prop_grid; ?>">
                                            <select class="ays-text-input ays-text-input-short ays_enable_answer_field" id="ays_answers_grid_column" name="ays_answers_grid_column" style="width: 70px;">
                                                <option value='2' selected>
                                                   2
                                                </option>
                                                <option value='3' class="ays-poll-grid-type-columns">
                                                   3
                                                </option>
                                                <option value='4' class="ays-poll-grid-type-columns">
                                                   4
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- Answers view --> 
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_answers_box_shadow_enable">
                                        <?php echo __('Answer box shadow',$this->plugin_name)?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Allow answer container box shadow',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="checkbox" class="ays_toggle ays_toggle_slide" id="ays_poll_answers_box_shadow_enable" name="ays_poll_answers_box_shadow_enable" value="on" <?php echo ($poll_answer_enable_box_shadow) ? "checked" : ""; ?>>
                                    <label for="ays_poll_answers_box_shadow_enable" class="ays_switch_toggle">Toggle</label>
                                    <div class="col-sm-12 ays_toggle_target ays_divider_top <?php echo ($poll_answer_enable_box_shadow) ? "" : "display_none"; ?>" style="margin-top: 10px; padding-top: 10px;">
                                        <label for="ays_poll_answers_box_shadow_color">
                                            <?php echo __('Answer shadow color',$this->plugin_name)?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('The shadow color of answers container',$this->plugin_name)?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                        <input type="text" class="ays-text-input" data-alpha="true" data-default-color="#000000" value="<?php echo $poll_answer_box_shadow_color; ?>" id="ays_poll_answers_box_shadow_color" name="ays_poll_answers_box_shadow_color">
                                    </div>
                                </div>
                            </div> <!-- Answers box shadow -->
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays-poll-title-bg-color'>
										<?= __('Title Background Color', $this->plugin_name); ?>
                                        <a class="ays_help"
                                           data-toggle="tooltip"
                                           data-placement="top"
                                           title="<?= __("Specify the background color of the title.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="text" class="ays-text-input" data-alpha="true"
                                           id='ays-poll-title-bg-color'
                                           name='ays_poll_title_bg_color'
                                           value="<?= !empty($options['title_bg_color']) ? $options['title_bg_color'] : 'rgba(255,255,255,0)' ?>"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays_poll_title_font_size'>
										<?= __('Title font size', $this->plugin_name); ?>
                                        <a class="ays_help"
                                           data-toggle="tooltip"
                                           data-placement="top"
                                           title="<?= __("Specify the font size of the title.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="number"
                                           class="ays-text-input ays-text-input-short"
                                           id='ays_poll_title_font_size' 
                                           name='ays_poll_title_font_size'
                                           value="<?= $poll_title_font_size ?>"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays_poll_title_alignment'>
										<?= __('Title alignment', $this->plugin_name); ?>
                                        <a class="ays_help"
                                           data-toggle="tooltip"
                                           data-placement="top"
                                           title="<?= __("Specify the alignment of the title.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <select id="ays_poll_title_alignment" name="ays_poll_title_alignment">
                                        <option value="left" <?php echo ($poll_title_alignment == "left") ? "selected" : "";?>><?php echo __("Left" , $this->plugin_name); ?></option>
                                        <option value="center" <?php echo ($poll_title_alignment == "center") ? "selected" : "";?>><?php echo __("Center" , $this->plugin_name); ?></option>
                                        <option value="right" <?php echo ($poll_title_alignment == "right") ? "selected" : "";?>><?php echo __("Right" , $this->plugin_name); ?></option>
                                    </select>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays-poll-icon-size'>
										<?= __('Icon size (px)', $this->plugin_name); ?>
                                        <a class="ays_help"
                                           data-toggle="tooltip"
                                           data-placement="top"
                                           title="<?= __("Specify the size of the icons in rating and voting types of the poll in pixels. It should be 10 and more.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="number" class="ays-text-input ays-text-input-short"
                                           id='ays-poll-icon-size' name='ays_poll_icon_size'
                                           value="<?= (isset($options['icon_size'])) ? $options['icon_size'] : '24'; ?>"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays-poll-width'>
										<?= __('Width (px)', $this->plugin_name); ?>
                                        <a class="ays_help"
                                           data-toggle="tooltip"
                                           data-placement="top"
                                           title="<?= __("Specify the width of the poll in pixels. If you put 0, the width will be 100%.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="number" step="50" min="0" class="ays-text-input ays-text-input-short"
                                           id='ays-poll-width' name='ays_poll_width'
                                           value="<?= $options['width'] ?>"/>
                                </div>
                            </div>
                            <hr/>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays_poll_min_height'>
                                        <?php echo __('Min-height', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Set the poll minimum height by entering a numeric value.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="number" class="ays-text-input ays-text-input-short" id='ays_poll_min_height' name='ays_poll_min_height' value="<?php echo $poll_min_height; ?>"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays-poll-border-style'>
										<?= __('Border style', $this->plugin_name); ?>
                                        <a class="ays_help"
                                           data-toggle="tooltip"
                                           data-placement="top"
                                           title="<?= __("Choose your preferred style of the border.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <select name="ays_poll_border_style" id="ays-poll-border-style"
                                            class="ays-select ays-select-search">
                                        <option value="solid" <?= $options['border_style'] == "solid" ? 'selected' : ''; ?>>
											<?= __("Solid", $this->plugin_name); ?>
                                        </option>
                                        <option value="dashed" <?= $options['border_style'] == "dashed" ? 'selected' : ''; ?>>
											<?= __("Dashed", $this->plugin_name); ?>
                                        </option>
                                        <option value="dotted" <?= $options['border_style'] == "dotted" ? 'selected' : ''; ?>>
											<?= __("Dotted", $this->plugin_name); ?>
                                        </option>
                                        <option value="double" <?= $options['border_style'] == "double" ? 'selected' : ''; ?>>
											<?= __("Double", $this->plugin_name); ?>
                                        </option>
                                        <option value="groove" <?= $options['border_style'] == "groove" ? 'selected' : ''; ?>>
											<?= __("Groove", $this->plugin_name); ?>
                                        </option>
                                        <option value="ridge" <?= $options['border_style'] == "ridge" ? 'selected' : ''; ?>>
											<?= __("Ridge", $this->plugin_name); ?>
                                        </option>
                                        <option value="inset" <?= $options['border_style'] == "inset" ? 'selected' : ''; ?>>
											<?= __("Inset", $this->plugin_name); ?>
                                        </option>
                                        <option value="outset" <?= $options['border_style'] == "outset" ? 'selected' : ''; ?>>
											<?= __("Outset", $this->plugin_name); ?>
                                        </option>
                                        <option value="none" <?= $options['border_style'] == "none" ? 'selected' : ''; ?>>
											<?= __("None", $this->plugin_name); ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays-poll-border-radius'><?= __('Border radius', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" data-placement="top"
                                           title="<?= __("Define the radius of the corners of the poll container. Allows adding rounded corners to it.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="number" min="0"
                                           class="ays-text-input ays-text-input-short"
                                           id='ays-poll-border-radius' name='ays_poll_border_radius'
                                           value="<?= (isset($options['border_radius']) && $options['border_radius']) ? $options['border_radius'] : '0'; ?>"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays-poll-border-width'><?= __('Border width', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" data-placement="top"
                                           title="<?= __("Specify the width of the border. For the Coffee Fluid theme, border-width will always be 1px.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="number" min="0"
                                           class="ays-text-input ays-text-input-short"
                                           id='ays-poll-border-width' name='ays_poll_border_width'
                                           value="<?= isset($options['border_width']) &&  $options['border_width'] != '' ? $options['border_width'] : 2; ?>"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays-poll-border-color'><?= __('Border color', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" data-placement="top"
                                           title="<?= __("Specify the color of the border.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="text"
                                           class="ays-text-input"
                                           data-alpha="true"
                                           id='ays-poll-border-color'
                                           name='ays_poll_border_color'
                                           value="<?= $default_border ?>"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays-poll-box-shadow-color'><?= __('Box shadow', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" data-placement="top"
                                           title="<?= __("Add a shadow to your poll container. Moreover, you can choose the color of it.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="checkbox" class="ays_toggle ays_toggle_slide"
                                           id="ays_poll_enable_box_shadow"
                                           name="ays_poll_enable_box_shadow" <?= (isset($options['enable_box_shadow']) && $options['enable_box_shadow'] == 'on') ? 'checked' : ''; ?>>
                                    <label for="ays_poll_enable_box_shadow" class="ays_switch_toggle">Toggle</label>
                                    <div class="col-sm-12 ays_toggle_target ays_divider_top"
                                         style="margin-top: 10px; padding-top: 10px; <?= (isset($options['enable_box_shadow']) && $options['enable_box_shadow'] == "on") ? '' : 'display:none;' ?>">
                                        <label for="ays-poll-box-shadow-color">
											<?= __('Box shadow color', $this->plugin_name) ?>
                                            <a class="ays_help" data-toggle="tooltip"
                                               title="--><?= __('The shadow color of Poll container', $this->plugin_name) ?>">
                                                <i class="ays_fa ays_fa_info_circle"></i>
                                            </a>
                                        </label>
                                        <input type="text" class="ays-shadow-input" data-alpha="true" id='ays-poll-box-shadow-color'
                                               name='ays_poll_box_shadow_color'
                                               value="<?= (isset($options['box_shadow_color']) && !empty($options['box_shadow_color'])) ? $options['box_shadow_color'] : '#000000'; ?>"/>
                                    </div>
                                    <!---->
                                    <hr class="ays_toggle_target" style="<?= (isset($options['enable_box_shadow']) && $options['enable_box_shadow'] == "on" ) ? '' : 'display:none' ?>">
                                    <div class="form-group row ays_toggle_target" style="<?= (isset($options['enable_box_shadow']) && $options['enable_box_shadow'] == "on" ) ? '' : 'display:none' ?>">
                                        <div class="col-sm-12">
                                            <div class="col-sm-3" style="display: inline-block;">
                                                <span class="ays_poll_small_hint_text"><?php echo __('X', $this->plugin_name); ?></span>
                                                <input type="number" class="ays-text-input ays-text-input-90-width ays-box-shadow-coord-change" id='ays_poll_box_shadow_x_offset' name='ays_poll_box_shadow_x_offset' value="<?php echo $poll_box_shadow_x_offset; ?>" />
                                            </div>
                                            <div class="col-sm-3 ays_divider_left" style="display: inline-block;">
                                                <span class="ays_poll_small_hint_text"><?php echo __('Y', $this->plugin_name); ?></span>
                                                <input type="number" class="ays-text-input ays-text-input-90-width ays-box-shadow-coord-change" id='ays_poll_box_shadow_y_offset' name='ays_poll_box_shadow_y_offset' value="<?php echo $poll_box_shadow_y_offset; ?>" />
                                            </div>
                                            <div class="col-sm-3 ays_divider_left" style="display: inline-block;">
                                                <span class="ays_poll_small_hint_text"><?php echo __('Z', $this->plugin_name); ?></span>
                                                <input type="number" class="ays-text-input ays-text-input-90-width ays-box-shadow-coord-change" id='ays_poll_box_shadow_z_offset' name='ays_poll_box_shadow_z_offset' value="<?php echo $poll_box_shadow_z_offset; ?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <!---->
                                </div>
                            </div>
                            <hr>
                            <!-- ---------Aro start gradient -->
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays-enable-background-gradient">
                                        <?php echo __('Background Gradient',$this->plugin_name)?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Add a color gradient background in the poll. Moreover, you can choose Color 1, Color 2 and the direction of the gradient.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="checkbox" class="ays_toggle ays_toggle_slide"
                                           id="ays-enable-background-gradient"
                                           name="ays_enable_background_gradient"
                                            <?php echo ($enable_background_gradient) ? 'checked' : ''; ?>/>
                                    <label for="ays-enable-background-gradient" class="ays_switch_toggle">Toggle</label>
                                    <div class="row ays_toggle_target" style="margin: 10px 0 0 0; padding-top: 10px; <?php echo ($enable_background_gradient) ? '' : 'display:none;' ?>">
                                        <div class="col-sm-12 ays_divider_top" style="margin-top: 10px; padding-top: 10px;">
                                            <label for='ays-background-gradient-color-1'>
                                                <?php echo __('Color 1', $this->plugin_name); ?>
                                            </label>
                                            <input type="text" class="ays-text-input" id='ays-background-gradient-color-1' name='ays_background_gradient_color_1' data-alpha="true" value="<?php echo $background_gradient_color_1; ?>"/>
                                        </div>
                                        <div class="col-sm-12 ays_divider_top" style="margin-top: 10px; padding-top: 10px;">
                                            <label for='ays-background-gradient-color-2'>
                                                <?php echo __('Color 2', $this->plugin_name); ?>
                                            </label>
                                            <input type="text" class="ays-text-input" id='ays-background-gradient-color-2' name='ays_background_gradient_color_2' data-alpha="true" value="<?php echo $background_gradient_color_2; ?>"/>
                                        </div>
                                        <div class="col-sm-12 ays_divider_top" style="margin-top: 10px; padding-top: 10px;">
                                            <label for="ays_poll_gradient_direction">
                                                <?php echo __('Gradient direction',$this->plugin_name)?>
                                            </label>
                                            <select id="ays_poll_gradient_direction" name="ays_poll_gradient_direction" class="ays-text-input">
                                                <option <?php echo ($poll_gradient_direction == 'vertical') ? 'selected' : ''; ?> value="vertical"><?php echo __( 'Vertical', $this->plugin_name); ?></option>
                                                <option <?php echo ($poll_gradient_direction == 'horizontal') ? 'selected' : ''; ?> value="horizontal"><?php echo __( 'Horizontal', $this->plugin_name); ?></option>
                                                <option <?php echo ($poll_gradient_direction == 'diagonal_left_to_right') ? 'selected' : ''; ?> value="diagonal_left_to_right"><?php echo __( 'Diagonal left to right', $this->plugin_name); ?></option>
                                                <option <?php echo ($poll_gradient_direction == 'diagonal_right_to_left') ? 'selected' : ''; ?> value="diagonal_right_to_left"><?php echo __( 'Diagonal right to left', $this->plugin_name); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- -----------end -->
                            <hr>                                
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays_questions_font_size'>
                                        <?php echo __('Question font size', $this->plugin_name); ?> (px)
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Specify the font size of the questions( only for <p> tag ). It accepts only numerical values.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <div class="row">
                                        <div class="col-sm-5">
                                            <label for='ays_poll_answers_font_size_pc'>
                                                <?php echo __('On PC', $this->plugin_name); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Define the font size for PC devices.',$this->plugin_name)?>">
                                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="number" class="ays-text-input ays-poll-question-font-size" name="ays_poll_answers_font_size_pc" id="ays_poll_answers_font_size_pc" value="<?php echo $poll_question_font_size_pc;?>">
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-5">
                                            <label for='ays_poll_answers_font_size_mobile'>
                                                <?php echo __('On mobile', $this->plugin_name); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Define the font size for mobile devices.',$this->plugin_name)?>">
                                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="number" class="ays-text-input ays-poll-question-font-size" name="ays_poll_answers_font_size_mobile" id="ays_poll_answers_font_size_mobile" value="<?php echo $poll_question_font_size_mobile;?>">
                                        </div>
                                    </div>
                                </div>
                            </div><!-- Question font size -->                            
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays_poll_question_image_height'><?= __('Question image height', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" data-placement="top"
                                           title="<?= __("Specify the height of question image of the Poll.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="number"
                                           class="ays-text-input ays-text-input-short"
                                           id='ays_poll_question_image_height' name='ays_poll_question_image_height'
                                           value="<?php echo $poll_question_image_height; ?>"/>
                                </div>
                            </div><!-- Question image height -->  
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays_poll_question_image_object_fit'><?= __('Question object fit', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" data-placement="top"
                                           title="<?= __("Specify the height of question image of the Poll.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <select class="ays-text-input ays-text-input-short" id="ays_poll_question_image_object_fit" name="ays_poll_question_image_object_fit">
                                        <option value="cover"      <?php echo ($poll_question_image_object_fit == "cover")      ? "selected" : ""; ?>><?php echo __("Cover" , $this->plugin_name); ?></option>
                                        <option value="fill"       <?php echo ($poll_question_image_object_fit == "fill")       ? "selected" : ""; ?>><?php echo __("Fill" , $this->plugin_name); ?></option>
                                        <option value="contain"    <?php echo ($poll_question_image_object_fit == "contain")    ? "selected" : ""; ?>><?php echo __("Contain" , $this->plugin_name); ?></option>
                                        <option value="scale-down" <?php echo ($poll_question_image_object_fit == "scale-down") ? "selected" : ""; ?>><?php echo __("Scale-down" , $this->plugin_name); ?></option>
                                        <option value="none"       <?php echo ($poll_question_image_object_fit == "none")       ? "selected" : ""; ?>><?php echo __("None" , $this->plugin_name); ?></option>
                                    </select>
                                </div>
                            </div><!-- Question image object fit -->  
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays_poll_mobile_max_width'>
                                        <?php echo __('Poll max-width for mobile', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" data-placement="top" title="<?php echo __('Poll container max-width for mobile in percentage. This option will work for the screens with less than 640 pixels width.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="number" class="ays-text-input ays-text-input-short" id='ays_poll_mobile_max_width'
                                        name='ays_poll_mobile_max_width' style="display: inline-block;"
                                        value="<?php echo $poll_mobile_max_width; ?>"/> %
                                        <span class="ays_poll_small_hint_text"><?php echo __("For 100% leave blank", $this->plugin_name);?></span>
                                </div>
                            </div> <!-- Poll max-width for mobile -->
                            <hr>
                            <!-- Buttons Styles Start -->
                            <p class="ays-subtitle ays-poll-subtitle-button"><?php echo __('Button Styles',$this->plugin_name); ?></p>
                            <hr/>                            
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_buttons_size">
                                        <?php echo __('Buttons Size',$this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Select the size of the button(s) inside the poll.',$this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <select class="ays-text-input ays-text-input-short" id="ays_poll_buttons_size" name="ays_poll_buttons_size">
                                        <option value="small"  <?php echo ($poll_button_selected == 'small') ? 'selected' : ''; ?>><?php echo __('Small',$this->plugin_name); ?></option>
                                        <option value="medium" <?php echo ($poll_button_selected == 'medium') || $poll_button_selected == '' ? 'selected' : ''; ?>><?php echo __('Medium',$this->plugin_name); ?></option>
                                        <option value="large"  <?php echo ($poll_button_selected) && $poll_button_selected == 'large' ? 'selected' : ''; ?>><?php echo __('Large',$this->plugin_name); ?></option>
                                    </select>
                                </div>
                            </div> <!-- Buttons Size -->
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays_poll_buttons_font_size'>
                                        <?php echo __('Buttons font size', $this->plugin_name); ?> (px)
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Define the size of the button(s) inside the poll in pixels.',$this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="number" class="ays-text-input ays-text-input-short" id='ays_poll_buttons_font_size' name='ays_poll_buttons_font_size' value="<?php echo $poll_buttons_font_size; ?>"/>
                                </div>
                            </div> <!-- Buttons font size -->
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_buttons_padding">
                                        <?php echo __('Buttons padding (px)',$this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Define the distance between the text and the border of the button in pixels․',$this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <div class="col-sm-5" style="display: inline-block; padding-left: 0;">
                                        <span class="ays_poll_small_hint_text"><?php echo __('Left / Right',$this->plugin_name); ?></span>
                                        <input type="number" class="ays-text-input ays_buttons_padding" id='ays_poll_buttons_left_right_padding' name='ays_poll_buttons_left_right_padding' value="<?php echo $poll_buttons_left_right_padding; ?>" style="width: 100px;" />
                                    </div>
                                    <div class="col-sm-5 ays_divider_left" style="display: inline-block;">
                                        <span class="ays_poll_small_hint_text"><?php echo __('Top / Bottom',$this->plugin_name); ?></span>
                                        <input type="number" class="ays-text-input ays_buttons_padding" id='ays_poll_buttons_top_bottom_padding' name='ays_poll_buttons_top_bottom_padding' value="<?php echo $poll_buttons_top_bottom_padding; ?>" style="width: 100px;" />
                                    </div>
                                </div>
                            </div> <!-- Buttons padding -->
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_buttons_border_radius">
                                        <?php echo __('Buttons border radius',$this->plugin_name); ?> (px)
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Define the radius of the corners of the button. Allows adding rounded corners to the button.',$this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="number" class="ays-text-input ays-text-input-short" id="ays_poll_buttons_border_radius" name="ays_poll_buttons_border_radius" value="<?php echo $poll_buttons_border_radius; ?>"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for='ays_poll_buttons_width'>
                                        <?php echo __('Buttons width', $this->plugin_name); ?> (px)
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Set the button width in pixels. For an initial width, leave the field blank.', $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="number" class="ays-text-input ays-text-input-short" id='ays_poll_buttons_width'name='ays_poll_buttons_width' value="<?php echo $poll_buttons_width; ?>"/>
                                    <span style="display:block;" class="ays_poll_small_hint_text"><?php echo __('For an initial width, leave the field blank.', $this->plugin_name); ?></span>
                                </div><!-- Buttons width --> 
                            </div> <!-- Buttons Styles End -->
                            <hr>
                            <!--PRO ANSWER STYLEs-->
                            <div class="col-sm-12" style="padding:30px;">
			                    <div class="pro_features" style="justify-content:flex-end;">
			                        <div>
			                            <p style="font-size:15px;">
			                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
			                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
			                            </p>
                                    </div>
                                    <div style="position: absolute; top: 15px;">
			                            <p style="font-size:15px;">
			                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
			                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
			                            </p>
			                        </div>
                                </div>
                                <div class="form-group row">
                                    <p class="ays-subtitle" style="margin-top:0;"><?php echo __('Answers Styles',$this->plugin_name)?></p>
                                </div> 
                                <div class="form-group row">
                                    <div class="" style="width: 100%;">
                                        <hr/>
                                        <div class="form-group row">
                                            <div class="col-sm-5">
                                                <label for="ays_answers_border">
                                                    <?php echo __('Answer border',$this->plugin_name)?>
                                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Allow answer border',$this->plugin_name)?>">
                                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                    </a>
                                                </label>
                                            </div>
                                            <div class="col-sm-7 ays_divider_left">
                                                <input type="checkbox" class="ays_toggle ays_toggle_slide" id="ays_answers_border" name="ays_answers_border" value="on"
                                                    checked/>
                                                <label for="ays_answers_border" class="ays_switch_toggle">Toggle</label>
                                                <div class="col-sm-12 ays_toggle_target ays_divider_top" style="margin-top: 10px; padding-top: 10px;">
                                                    <label for="ays_answers_border_width">
                                                        <?php echo __('Border width',$this->plugin_name)?> (px)
                                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('The width of answers border',$this->plugin_name)?>">
                                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                        </a>
                                                    </label>
                                                    <input type="number" class="ays-text-input" id='ays_answers_border_width' name='ays_answers_border_width'
                                                        value="" min="0"/>
                                                </div>
                                                <div class="col-sm-12 ays_toggle_target ays_divider_top" style="margin-top: 10px; padding-top: 10px;">
                                                    <label for="ays_answers_border_style">
                                                        <?php echo __('Border style',$this->plugin_name)?>
                                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('The style of answers border',$this->plugin_name)?>">
                                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                        </a>
                                                    </label>
                                                    <select id="ays_answers_border_style" name="ays_answers_border_style" class="ays-text-input">
                                                        <option>Solid</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-12 ays_toggle_target ays_divider_top" style="margin-top: 10px; padding-top: 10px;">
                                                    <label for="ays_answers_border_color">
                                                        <?php echo __('Border color',$this->plugin_name)?>
                                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('The color of the answers border',$this->plugin_name)?>">
                                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                        </a>
                                                    </label>
                                                    <input class="ays-text-input wp-color-picker" id="ays_answers_border_color" type="text" data-alpha="true" data-default-color="#000000" value="#000000">
                                                </div>
                                            </div>
                                        </div> <!-- Answers border -->
                                        <hr/>
                                        <div class="form-group row ays_grid_show">
                                            <div class="col-sm-5">
                                                <label for="ays_poll_show_answers_caption">
                                                    <?php echo __('Show answers caption',$this->plugin_name)?>
                                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Show answers caption near the answer image. This option will be work only when answer has image.',$this->plugin_name); ?>">
                                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                    </a>
                                                </label>
                                            </div>
                                            <div class="col-sm-7 ays_divider_left">
                                                <input type="checkbox" class="ays_toggle ays_toggle_slide" value='on'/>
                                                <label for="ays_poll_show_answers_caption" class="ays_switch_toggle">Toggle</label>
                                            </div>
                                        </div> <!-- Show answers caption -->
                                        <hr/>
                                        <div class="form-group row">
                                            <div class="col-sm-5">
                                                <label for="ays_ans_img_caption_style">
                                                    <?php echo __('Answers image caption style',$this->plugin_name)?>
                                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Height of answers images.',$this->plugin_name)?>">
                                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                    </a>
                                                </label>
                                            </div>
                                            <div class="col-sm-7 ays_divider_left">
                                                <select class="ays-text-input ays-text-input-short">
                                                    <option><?php echo __('Outside', $this->plugin_name); ?></option>
                                                </select>
                                            </div>
                                        </div> <!-- Answers image caption style -->
                                        <hr/>
                                        <div class="form-group row">
                                            <div class="col-sm-5">
                                                <label for="ays_ans_img_caption_position">
                                                    <?php echo __('Answers image caption position',$this->plugin_name)?>
                                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Height of answers images.',$this->plugin_name)?>">
                                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                    </a>
                                                </label>
                                            </div>
                                            <div class="col-sm-7 ays_divider_left">
                                                <select class="ays-text-input ays-text-input-short">
                                                    <option value="top"><?php echo __('Top', $this->plugin_name); ?></option>
                                                </select>
                                            </div>
                                        </div> <!-- Answers image caption position -->
                                        <hr>
                                    </div>
                                </div>
                            </div>
                            <hr/>
			                <div class="form-group row">
			                    <div class="col-sm-4">
			                        <label for="ays_disable_answer_hover">
										<?= __('Disable answers hover', $this->plugin_name); ?>
			                            <a class="ays_help" data-toggle="tooltip"
			                               data-placement="top"
			                               title="<?= __("Disable the hover effect for answers.", $this->plugin_name); ?>">
			                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
			                            </a>
			                        </label>
			                    </div>
			                    <div class="col-sm-8 ays_divider_left">
			                        <input type="checkbox"
			                               name="ays_disable_answer_hover"
			                               id="ays_disable_answer_hover"
			                               value="on" <?php echo ($options['disable_answer_hover'] == 1) ? 'checked' : ''; ?>									
			                        >
			                    </div>
			                </div>                            
                            <hr>
                            <div class="form-group row ays_toggle_parent">
			                    <div class="col-sm-4">
			                        <label for="ays_disable_answer_hover">
										<?= __('Poll Logo', $this->plugin_name); ?>
			                            <a class="ays_help" data-toggle="tooltip"
			                               data-placement="top"
			                               title="<?= __("Add logo image for poll. Advisable size for image is 50x50", $this->plugin_name); ?>">
			                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
			                            </a>
			                        </label>
			                    </div>
			                    <div class="col-sm-8 ays_logo_container ays_divider_left">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <a href="javascript:void(0)" class="add-logo-image button"><?= $image_text_logo; ?></a>                                    
                                        </div>
                                        <div class="col-sm-8 ays_logo_image_remove ays_divider_left" style="<?= $style_logo; ?>">
                                            <div class="ays-poll-logo-image-container">
                                                <div class="col-sm-3" style="padding:0;">
                                                    <img src="<?= isset($options['logo_image']) ? $options['logo_image'] : ""; ?>"
                                                        id="ays-poll-logo-img" class="ays_poll_logo_image_main" width="55" height="55"/>
                                                    <input type="hidden" name="ays_poll_logo_image" id="ays-poll-logo-image"
                                                        value="<?= isset($options['logo_image']) ? $options['logo_image'] : ""; ?>"/>                                            
                                                </div>
                                                <div class="col-sm-9">
                                                    <a href="javascript:void(0)" class="add-logo-remove-image button"><?= __("Remove" , $this->plugin_name); ?></a>                                             
                                                </div>
                                            </div>
                                        </div>
                                    </div>                                    
                                    <div class="form-group row ays-poll-toggle-image-url-box <?php echo !$style_logo_check ? "display_none" : "";?>">
                                        <div class="col-sm-4 ">
                                            <label for="ays_disable_answer_hover">
                                                <?= __('Logo URL', $this->plugin_name); ?>
                                                <a class="ays_help" data-toggle="tooltip"
                                                data-placement="top"
                                                title="<?= __("Add a URL link to the poll's logo image.", $this->plugin_name); ?>">
                                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                                </a>
                                            </label>                                   
                                        </div>
                                        <div class="col-sm-8 ays_divider_left">
                                            <input type="checkbox"
                                                   name="ays_poll_logo_enable_image_url"
                                                   id="ays_poll_logo_enable_image_url"
                                                   value="on" class="ays_toggle ays_toggle_slide" <?php echo $poll_logo_image_url_check ? "checked" : ""; ?>>
                                            <label for="ays_poll_logo_enable_image_url" class="ays_switch_toggle"></label>
                                            <hr class="ays_toggle_target <?php echo $poll_logo_image_url_check ? "" : "display_none"; ?>">
                                            <div class="row ays_toggle_target <?php echo $poll_logo_image_url_check ? "" : "display_none"; ?>" style="padding:0 15px;" >
                                                <input type="text"
                                                       name="ays_poll_logo_image_url"
                                                       id="ays_poll_logo_image_url"
                                                       value="<?php echo $poll_logo_image_url?>" style="width: 100%;" class="ays-text-input" placeholder="URL">
                                            </div>
                                        </div>
                                    </div>
			                    </div>
			                </div> 
                            <!-- Poll logo start -->
                            <hr/>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_custom_class">
                                        <?php echo __('Custom class for poll container',$this->plugin_name)?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Use your custom HTML class for adding your custom styles to the poll container.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8 ays_divider_left">
                                    <input type="text" class="ays-text-input" name="ays_poll_custom_class" id="ays_poll_custom_class" placeholder="myClass myAnotherClass..." value="<?php echo $custom_class; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12 ays_divider_left" style="position: relative;">
                            <style id='apm-custom-css'>
                                <?=$poll['custom_css']?>
                            </style>
							<?php
                            
							$content = "
                            <div class='box-apm-scroll ays-poll-style-tab-live-container'>
                                <div class='box-apm {$poll['type']}-poll ".$poll_logo_for_live_container."' id=''>
                                    <div class='apm-title-box'>
                                        <h5>{$poll['title']}</h5>
                                    </div>";
                            
                            $content .= "<div class='ays_question'>" . stripslashes($poll['question']) . "</div>
                                    <div class='apm-img-box'>";
							$content .= !empty($poll['image']) ? "<img class='ays-poll-img' src='{$poll['image']}'>" : "";
                            $checking_answer_hover = ($options['disable_answer_hover'] == 1) ? 'disable_hover' : 'ays_enable_hover';
							$content .= "</div>
                                    <div class='apm-answers ays_poll_list_view_container'>";
                            $minimalTheme = ($poll['theme_id'] == 3) ? 'ays_poll_minimal_theme' : '' ;
                            $minimalThemeBtn = ($poll['theme_id'] == 3) ? 'ays_poll_minimal_theme_btn' : '' ;
                            if ($poll_answer_icon_check && $poll['theme_id'] != 3) {
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
							switch ( $poll['type'] ) {
                                case 'choosing':
                                    if(empty($poll['answers'])){
                                        for ($i = 0 ; $i < $answer_default_count ; $i++){
                                            $content .= "<div class='apm-choosing answer- ".$minimalTheme." ays-poll-field ays_poll_list_view_item' data-id=".$i." data-lid=".$i.">
                                                            <input type='radio' name='answer' id='radio-".$i."-' value='".$i."'>
                                                            <label class='ays_label_poll ".$checking_answer_hover." ays_label_font_size ".$answer_icon_class." ays-poll-answer-more-options' for='radio-".$i."-'>Answer ".($i+1)."</label>
                                                            
                                                        </div>";
                                        }
                                    }
                                    else{
                                        foreach ( $poll['answers'] as $index => $answer ) {
                                            $answer_image = isset($answer['answer_img']) && $answer['answer_img'] != "" ? "<div><img src=".esc_attr($answer['answer_img'])." class='ays-poll-answer-image-live'></div>" : "";
                                            $poll_class_for_answer_label = "";
                                            $poll_class_for_answer_label_text = "";
                                            if($answer_image != ""){
                                                $poll_class_for_answer_label = "ays_poll_label_without_padding";
                                                $poll_class_for_answer_label_text = "ays_poll_label_text_with_padding";
                                            } 
                                            $content .= "<div class='apm-choosing answer- ".$minimalTheme." ays-poll-field ays_poll_list_view_item' data-id=".$index." data-lid=".$index.">
                                                            <input type='radio' name='answer' id='radio-".$index."-' value='{$answer['id']}'>
                                                            <label class='ays_label_poll ".$checking_answer_hover." ays_label_font_size ".$poll_class_for_answer_label." ays-poll-answer-more-options' for='radio-".$index."-'>".$answer_image."<div><span class='ays-poll-each-answer ".$poll_class_for_answer_label_text."'>" . stripcslashes($answer['answer']) . "</span></div></label>                                                            
                                                        </div>";
                                        }
                                    }
									break;
								case 'voting':
                                    $poll_view_type_voting = isset($poll['view_type']) && $poll['view_type'] != "" ? $poll['view_type'] : "hand";
									switch ( $poll_view_type_voting ) {
										case 'hand':
											foreach ( $poll['answers'] as $index => $answer ) {
												$content .= "<div class='apm-voting answer-'><input type='radio' name='answer' id='radio-$index-' value='{$answer['id']}'>
                                                                <label for='radio-$index-'>";
												$content .= ((int) $answer['answer'] > 0 ? "<i class='ays_poll_far ays_poll_fa-thumbs-up'></i>" : "<i class='ays_poll_far ays_poll_fa-thumbs-down'></i>") . "</label></div>";
											}
											break;
										case 'emoji':
											foreach ( $poll['answers'] as $index => $answer ) {
												$content .= "<div class='apm-voting answer-'><input type='radio' name='answer' id='radio-$index-' value='{$answer['id']}'>
                                                                <label for='radio-$index-'>";
												$content .= ((int) $answer['answer'] > 0 ? $emoji[1] : $emoji[3]) . "</label></div>";
											}
											break;
										default:										
											break;
									}
									break;
								case 'rating':
                                    $poll_view_type_rating = isset($poll['view_type']) && $poll['view_type'] != "" ? $poll['view_type'] : "star";
									switch ( $poll_view_type_rating ) {
										case 'star':
											foreach ( $poll['answers'] as $index => $answer ) {
												$content .= "<div class='apm-rating answer-'><input type='radio' name='answer' id='radio-$index-' value='{$answer['id']}'>
                                                                <label for='radio-$index-'><i class='ays_poll_far ays_poll_fa-star'></i></label></div>";
											}
											break;
										case 'emoji':
											foreach ( $poll['answers'] as $index => $answer ) {
												$content .= "<div class='apm-rating answer-'><input type='radio' name='answer' id='radio-$index-' value='{$answer['id']}'>
                                                                <label class='emoji' for='radio-$index-'>" . $emoji[(count($poll['answers']) / 2 - $index + 1.5)] . "</label></div>";
											}
											break;
										default:										
											break;
									}
									break;								
								case 'text':
                                    $poll_view_type_text = isset($poll['view_type']) && $poll['view_type'] != "" ? $poll['view_type'] : "short_text";
									switch ( $poll_view_type_text ) {
										case 'short_text':
                                            $content .= "<div class='ays-poll-maker-text-live-preview'>
                                                            <input type='text' id='ays_poll_text_type_short_live' readonly class='ays-poll-text-type-fields'>
                                                            <label for='ays_poll_text_type_short_live'></label>
                                                        </div>";											
											break;
										case 'paragraph':
                                            $content .= "<div class='ays-poll-maker-text-live-preview'>
                                                            <textarea id='ays_poll_text_type_paragraph_live' readonly class='ays-poll-text-type-fields'></textarea>
                                                            <label for='ays_poll_text_type_paragraph_live'></label>
                                                        </div>";
											break;
										default:										
											break;
									}
									break;								
								default:										
									break;								
							}
							$content .= "</div>
                                    <div class='apm-button-box' " . (isset($options['enable_vote_btn']) && $options['enable_vote_btn'] == 0 ? "style='display:none'" : "") . ">
                                        <input type='button' name='ays_finish_poll' class='btn ays-poll-btn {$poll['type']}-btn ".$minimalThemeBtn." '" . 'value="' . ((isset($options['btn_text']) && '' != $options['btn_text']) ? stripslashes($options['btn_text']) : 'Vote') . '">
                                    </div>';
                                    $content .= "<div class='".$poll_logo_img." ays_live_logo_container'>";
                            if($poll_check_logo){
                                $content .= "<img src=".$poll_logo_image." width='55' height='55' class='ays_poll_logo_image_main'>";
                            }        
                            $content .= "</div>";
                                    
                            $content .='</div>
                            </div>';
							print $content;
							?>
                        </div>
                    </div>
                    <hr/>
                    <div class="form-group row">
                        <div class="col-sm-2">
                            <label for="ays_custom_css">
								<?= __("Custom CSS", $this->plugin_name); ?>
							</label>
							<a class="ays_help" data-toggle="tooltip" data-placement="top" title="<?= __("In this field, you can write your own CSS code. For example: p{color:red !important}", $this->plugin_name); ?>">
                    			<i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                			</a>
                			<br>
                        </div>
                        <div class="col-sm-10 ays_divider_left">
                            <textarea class="ays-textarea" id="ays_custom_css" name="ays_custom_css" cols="30" rows="10"><?= (isset($poll['custom_css'])) ? $poll['custom_css'] : ''; ?></textarea>
                        </div>
                    </div>
                </div>
             	<hr>
            </div>
            <div id="tab3" class="ays-poll-tab-content <?= $active_tab == 'Settings' ? 'ays-poll-tab-content-active' : ''; ?>">
                <p class="ays-subtitle">
					<?= __('Feature options', $this->plugin_name); ?>
                </p>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label>
							<?= __('Status', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
                               title="<?= __('Choose whether the poll is active or not. If you choose an unpublished option, the poll won’t be shown anywhere on your website. (You do not need to remove shortcodes).', $this->plugin_name) ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <div class="d-flex">
                            <div class="form-check form-check-inline">
                                <input type="radio" id="ays-publish" name="ays_publish"
                                       value="1" <?= ($published == 1) ? 'checked' : ''; ?> />
                                <label class="form-check-label"
                                       for="ays-publish"> <?= __('Published', $this->plugin_name); ?> </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="ays-unpublish" name="ays_publish"
                                       value="0" <?= ($published == 0) ? 'checked' : ''; ?> />
                                <label class="form-check-label"
                                       for="ays-unpublish"> <?= __('Unpublished', $this->plugin_name); ?> </label>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="form-group row ays_toggle_parent">
                    <div class="col-sm-3">
                        <label for='ays_poll_allow_multivote'>
                            <?= __('Allow multivote', $this->plugin_name); ?>
                            <a  class="ays_help"  data-toggle="tooltip" data-placement="top" title="<?= __("Allow users to choose more than one answer. It will work with choosing type.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" name="ays_poll_allow_multivote" id="ays_poll_allow_multivote" class="ays-enable-timer1 ays_toggle_checkbox" value="on" <?php echo $poll_allow_multivote; ?>>
                    </div>
                    <div class="col-sm-8 ays_toggle_target ays_divider_left <?php echo $poll_enable_multivote_answer ? '' : 'display_none'; ?>">
                        <input type="number" name="ays_poll_multivote_count" id="ays_poll_multivote_count" class="ays-enable-timerl ays-text-input" value="<?php echo $poll_multivote_answer_count; ?>">
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for='show-title'>
							<?= __('Show Title', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip"
                               data-placement="top"
                               title="<?= __("Enable to show the title as a headline inside the poll.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <input type="checkbox" name="show_title" id="show-title"
                               value="show" <?= $poll['show_title'] ? 'checked' : ''; ?>>
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label>
							<?= __('Alignment', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
                               title="<?= __('Choose the alignment of the content of the poll.', $this->plugin_name) ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <div class="d-flex">
                            <div class="form-check form-check-inline">
                                <input type="radio" id="apm-dir-ltr" name="ays_poll_direction"
                                       value="ltr" <?= (isset($options['poll_direction']) && $options['poll_direction'] == 'ltr') ? 'checked' : ''; ?> />
                                <label class="form-check-label"
                                       for="apm-dir-ltr"> <?= __('Left', $this->plugin_name); ?> </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="apm-dir-center" name="ays_poll_direction"
                                       value="center" <?= (isset($options['poll_direction']) && $options['poll_direction'] == 'center') ? 'checked' : ''; ?> />
                                <label class="form-check-label"
                                       for="apm-dir-center"> <?= __('Center', $this->plugin_name); ?> </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="apm-dir-rtl" name="ays_poll_direction"
                                       value="rtl" <?= (isset($options['poll_direction']) && $options['poll_direction'] == 'rtl') ? 'checked' : ''; ?> />
                                <label class="form-check-label"
                                       for="apm-dir-rtl"> <?= __('Right', $this->plugin_name); ?> </label>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="col-sm-12" style="padding:20px 0;">
                    <div class="pro_features" style="justify-content:flex-end;">
                        <div style="margin-right:20px;">
                            <p style="font-size:20px;">
                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                            </p>
                        </div>
                    </div>                
	                <div class="form-group row">
	                    <div class="col-sm-3">
	                        <label for='ays-poll-allow-anonymity'>
					            <?= __('Allow anonymity', $this->plugin_name); ?>
	                            <a class="ays_help" data-toggle="tooltip"
	                               data-placement="top"
	                               title="<?= __("It allows participants to respond to your polls without ever revealing their identities, even if they are registered on your website. After enabling the option, the WP User and User IP will not be stored in the database.", $this->plugin_name); ?>">
	                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
	                            </a>
	                        </label>
	                    </div>
	                    <div class="col-sm-9">
	                        <input type="checkbox" name="ays_allow_anonymity" id="ays-poll-allow-anonymity"
	                               value="1" >
	                    </div>
	                </div>
	            </div>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for='show-poll-create-dates'>
                            <?= __('Show creation date', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip"
                               data-placement="top"
                               title="<?= __("Enable to show the creation date inside the poll.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <input type="checkbox" name="show_poll_creation_date" id="show-poll-create-dates"
                               value="1" <?= $show_create_date ? 'checked' : '' ?> >
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for='show-poll-author'>
                            <?= __('Show author', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip"
                               data-placement="top"
                               title="<?= __("Enable to show the author inside the poll.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <input type="checkbox" name="show_poll_author" id="show-poll-author"
                               value="1" <?= $show_author ? 'checked' : '' ?> >
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="schedule_the_poll">
							<?= __('Schedule', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
                               title="<?= __('Specify the period of time when the poll will be active. Choose the start and the end date and write the pre-start and expiration messages.', $this->plugin_name) ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1">
                        <input  type="checkbox"
                                id="schedule_the_poll"   
                                class="active_date_check"
                                name="active_date_check" <?= (isset($options['active_date_check']) && !empty($options['active_date_check'])) ? 'checked' : '' ?>>
                    </div>
                    <div class="col-sm-8">
                        <div class="row">
                            <div class="col-sm-12 active_date ays_divider_left"
                                style="display:  <?php echo (isset($options['active_date_check']) && $options['active_date_check'] == 'on') ? 'block' : 'none' ?>">
                                <!-- -1- -->                                 
                                 <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label class="form-check-label" for="ays-active"> <?php echo __('Start date:', $this->plugin_name); ?> </label>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="input-group mb-3">
                                            <input type="text" class="ays-text-input ays-text-input-short ays_actDect" id="ays-active" name="ays-active"
                                               value="<?php echo $activePoll; ?>" placeholder="<?php echo current_time( 'mysql' ); ?>">
                                            <div class="input-group-append">
                                                <label for="ays-active" class="input-group-text">
                                                    <span><i class="ays_fa ays_fa_calendar"></i></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- -2- -->
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label class="form-check-label" for="ays-deactive"> <?php echo __('End date:', $this->plugin_name); ?> </label>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="input-group mb-3">
                                            <input type="text" class="ays-text-input ays-text-input-short ays_actDect" id="ays-deactive" name="ays-deactive"
                                               value="<?php echo $deactivePoll; ?>" placeholder="<?php echo current_time( 'mysql' ); ?>">
                                            <div class="input-group-append">
                                                <label for="ays-deactive" class="input-group-text">
                                                    <span><i class="ays_fa ays_fa_calendar"></i></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>  
                                <!-- ////////// -->
                                <hr>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for='ays_poll_show_timer'>
                                            <?= __('Show timer', $this->plugin_name); ?>
                                            <a class="ays_help" data-toggle="tooltip"
                                               data-placement="top"
                                               title="<?= __("Enable to show the countdown or the end date in the poll, when it is scheduled.", $this->plugin_name); ?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-1">
                                        <input type="checkbox" name="ays_poll_show_timer" id="ays_poll_show_timer"
                                               value="1" <?= $schedule_show_timer ? 'checked' : '' ?> >
                                    </div>
                                    <div class="col-sm-8">
                                    	<div class="ays_show_time" style="display:  <?php echo $schedule_show_timer ? 'block;' : 'none;'; ?>">
	                                    	<div class="d-flex">
					                            <div class="form-check form-check-inline">
					                                <input type="radio" id="show_time_countdown" name="ays_show_timer_type"
					                                       value="countdown" <?= $show_timer_type == 'countdown' ? 'checked' : ''; ?> />
					                                <label class="form-check-label"
					                                       for="show_time_countdown"> <?= __('Show countdown', $this->plugin_name); ?> </label>
					                            </div>
					                            <div class="form-check form-check-inline">
					                                <input type="radio" id="show_time_enddate" name="ays_show_timer_type"
					                                       value="enddate" <?= $show_timer_type == 'enddate' ? 'checked' : ''; ?> />
					                                <label class="form-check-label"
					                                       for="show_time_enddate"> <?= __('Show end date', $this->plugin_name); ?> </label>
					                            </div>
					                        </div>
                                		</div>
                                	</div>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                         <label class="form-check-label"
                                           for="active_date_message"><?= __("Pre start message:", $this->plugin_name) ?></label>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="editor">
                                            <?php
                                            $content   = !empty($options['active_date_message_soon']) ? stripslashes($options['active_date_message_soon']) : stripslashes($default_options['active_date_message_soon']);
                                            $editor_id = 'active_date_message_soon';
                                            $settings  = array(
                                                'editor_height'  => '4',
                                                'textarea_name'  => 'active_date_message_soon',
                                                'editor_class'   => 'ays-textarea',
                                                'media_elements' => false
                                            );
                                            wp_editor($content, $editor_id, $settings);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                         		<hr>
                                <!-- -3- -->
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                         <label class="form-check-label"
                                           for="active_date_message"><?= __("Expiration message:", $this->plugin_name) ?></label>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="editor">
                                            <?php
                                            $content   = !empty($options['active_date_message']) ? stripslashes($options['active_date_message']) : stripslashes($default_options['active_date_message']);
                                            $editor_id = 'active_date_message';
                                            $settings  = array(
                                                'editor_height'  => '4',
                                                'textarea_name'  => 'active_date_message',
                                                'editor_class'   => 'ays-textarea',
                                                'media_elements' => false
                                            );
                                            wp_editor($content, $editor_id, $settings);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for='show_result_btn_schedule'>
                                            <?= __('Show result button', $this->plugin_name); ?>
                                            <a class="ays_help" data-toggle="tooltip"
                                               data-placement="top"
                                               title="<?= __("Enable to show the result button after the schedule.", $this->plugin_name); ?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-9">
                                        <input type="checkbox" name="show_result_btn_schedule" id="show_result_btn_schedule"
                                               value="1" <?= $showresbtnschedule ? 'checked' : '' ?> >
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <div class="col-sm-3">
                                        <label for='ays_show_poll_container'>
                                            <?= __("Don't show poll", $this->plugin_name); ?>
                                            <a class="ays_help" data-toggle="tooltip"
                                               data-placement="top"
                                               title="<?= __("Do not show the poll container on the front-end at all when it is expired or has not started yet.", $this->plugin_name); ?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-9">
                                        <input type="checkbox" name="ays_show_poll_container" id="ays_show_poll_container"
                                               value="on" <?php echo $poll_check_exp_cont; ?> >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for='ays-poll-allow-not-vote'>
							<?= __('Allow not to vote', $this->plugin_name); ?>
							<a 	class="ays_help" 
								data-toggle="tooltip" 
								data-placement="top" 
								title="<?= __("Allow the user to directly see the results without participating in the vote. If the option is checked, it is impossible to hide the results.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a></label>
                    </div>
            		<div class="col-sm-1">
                        <input type="checkbox" name="ays-poll-allow-not-vote" id="ays-poll-allow-not-vote" value="allow" <?= isset($options['allow_not_vote']) && $options['allow_not_vote'] ? 'checked' : ''; ?>>
                    </div>
                    <div class="col-sm-8">
                    	<div class="row">
		                    <div class="col-sm-3 ays_divider_left">
		                        <label for="ays-poll-btn-text">
									<?= __("Results button text", $this->plugin_name); ?>
		                            <a class="ays_help"
		                               data-toggle="tooltip"
		                               data-placement="top"
		                               title="<?= __("Write the text of the button, which shows results.", $this->plugin_name); ?>">
		                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
		                            </a>
		                        </label>
		                    </div>
		                    <div class="col-sm-9">
		                        <input type="text" class="ays-text-input ays-text-input-short" id="ays-poll-btn-text" name="ays_poll_res_btn_text" value="<?= (isset($options['see_res_btn_text']) && '' != $options['see_res_btn_text']) ? stripslashes($options['see_res_btn_text']) : __("See Results", $this->plugin_name); ?>"/>
		                    </div>
                    	</div>
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3" style="padding-right: 0px;">
                        <label for="ays_enable_pass_count">
							<?= __('Show passed users count', $this->plugin_name) ?>
                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
                               title="<?= __('Show how many users have passed the poll. It will be shown at the bottom right corner inside the poll.', $this->plugin_name) ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <input type="checkbox" id="ays_enable_pass_count" name="ays_enable_pass_count"
                               value="on" <?= ($enable_pass_count == 'on') ? 'checked' : ''; ?> />
                    </div>
                </div>
                <hr>
                <div class="form-group row ays_toggle_parent">
                    <div class="col-sm-3">
                        <label for="ays_redirect_after_vote">
							<?= __('Redirect after voting', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
                               title="<?= __('Enable redirection to the custom URL after the user votes the poll.', $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
            		<div class="col-sm-1">
                        <input type="checkbox" id="ays_redirect_after_vote" class="ays_toggle_checkbox" name="ays_redirect_after_vote" value="on" <?php echo ($redirect_users) ? 'checked' : ''; ?> />
                    </div>
                    <div class="col-sm-8 ays_toggle_target" style="<?php echo ($redirect_users) ? '' : 'display: none;'; ?>">
                    	<div class="row">
		                    <div class="col-sm-12 ays_divider_left">
		                        <div class="form-group row">
		                            <div class="col-sm-3">
		                                <label for="redirection_url">
											<?= __('URL', $this->plugin_name); ?>
		                                    <a class="ays_help" data-toggle="tooltip" data-placement="top" title="<?= __('Choose the Redirection URL for redirecting after the user takes the poll.', $this->plugin_name); ?>">
		                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
		                                    </a>
		                                </label>
		                            </div>
		                            <div class="col-sm-9">
		                                <input type="text" class="ays-text-input" name="redirection_url" placeholder="https://www.google.com" id="redirection_url" value="<?= isset($options['redirect_after_vote_url']) && !empty($options['redirect_after_vote_url']) ? $options['redirect_after_vote_url'] : ""; ?>" size="25">
		                            </div>
		                        </div>
		                        <hr>
		                        <div class="form-group row">
		                        	<div class="col-sm-3">
		                                <label for="redirectio_delay">
											<?= __('Delay', $this->plugin_name); ?>
		                                    <a class="ays_help" data-toggle="tooltip" data-placement="top"
		                                       title="<?= __('Choose the redirection delay in seconds after the user votes the poll.', $this->plugin_name); ?>">
		                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
		                                    </a>
		                                </label>
		                            </div>
		                            <div class="col-sm-9">
		                                <input type="text" class="ays-text-input" name="redirection_delay" id="redirectio_delay" value="<?= isset($options['redirect_after_vote_delay']) && !empty($options['redirect_after_vote_delay']) ? $options['redirect_after_vote_delay'] : ""; ?>" size="15">
		                            </div>
		                        </div>
		                    </div>
                    	</div>
	                    
                    </div>
                </div>
                <hr/>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="randomize-answers">
							<?= __('Randomize Answers', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip"
                               data-placement="top"
                               title="<?= __("Enable to show the answers of the poll in a random sequence.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <input type="checkbox"
                               name="randomize_answers"
                               id="randomize-answers"
                               value="on"
							<?php echo $randomize_answers ? 'checked' : ''; ?>
                        />
                    </div>
                </div>
                <hr/>
                <div class="form-group row ays_toggle_parent">
                    <div class="col-sm-3" style="padding-right: 0px;">
                        <label for="ays_enable_asnwers_sound">
                            <?php echo __('Enable answers sound',$this->plugin_name)?>
                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Enable to play a sound when the user clicks on one of the answers. To select a sound, go to the Poll answers sound option in the General Settings page.',$this->plugin_name)?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" id="ays_enable_asnwers_sound"
                               name="ays_poll_enable_asnwers_sound" class="ays_toggle_checkbox"
                               value="on" <?php echo $enable_asnwers_sound ? 'checked' : ''; ?>/>
                    </div>
                    <div class="col-sm-8 if_answer_sound ays_toggle_target ays_divider_left" style="<?php echo $enable_asnwers_sound ? '' : 'display:none;' ?>">
                        <?php if($answers_sound_status): ?>
                        <blockquote class=""><?php echo __('Sound are selected. For change sounds go to', $this->plugin_name); ?> <a href="?page=poll-maker-ays-settings" target="_blank"><?php echo __('General options', $this->plugin_name); ?></a> <?php echo __('page', $this->plugin_name); ?></blockquote>
                        <?php else: ?>
                        <blockquote class=""><?php echo __('Sound are not selected. For selecting sounds go to', $this->plugin_name); ?> <a href="?page=poll-maker-ays-settings" target="_blank"><?php echo __('General options', $this->plugin_name); ?></a> <?php echo __('page', $this->plugin_name); ?></blockquote>
                        <?php endif; ?>
                    </div>
                </div>
                <hr/>                
                <div class="form-group row">
                    <div class="col-3">
                        <label for="ays-poll-reason">
                            <?= __('Vote Reason', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
                                title="<?= __("Allow users to add their vote reason", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-9">
                        <input type="checkbox" name="ays-poll-reason" id="ays-poll-reason" <?php echo $poll_vote_reason; ?>>
                    </div>	                    
                </div>
                <hr/>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="ays_enable_vote_button">
							<?= __('Enable Vote button', $this->plugin_name) ?>
                            <a class="ays_help" data-toggle="tooltip"
                               title="<?= __('Show the vote button during the vote. If this option is disabled, then the user needs to click on the answer to vote without a chance of changing it.', $this->plugin_name) ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" id="ays_enable_vote_button" name="ays_enable_vote_button"
                               value="1" <?= (isset($options['enable_vote_btn']) && $options['enable_vote_btn'] == 0) ? '' : 'checked' ?> />
                    </div>
                    <div class="col-sm-2 ays_divider_left">
                        <label for="ays-poll-btn-text_vote">
							<?= __("Vote button text", $this->plugin_name); ?>
                            <a class="ays_help"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="<?= __("Write the text of the vote button.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-6">
                        <input type="text" class="ays-text-input ays-text-input-short"
                               id="ays-poll-btn-text_vote" name="ays_poll_btn_text"
                               value="<?= stripslashes($options['btn_text']) ?>"/>
                    </div>
                </div>
                <hr>
                <div class="form-group row ays_toggle_parent">
                    <div class="col-sm-3">
                        <label for="ays_enable_view_more_button">
							<?= __('Enable View more button', $this->plugin_name) ?>
                            <a class="ays_help" data-toggle="tooltip"
                               title="<?php echo __('Show only part of the answers and show the rest of the answers only when the user clicks on this button. It works only with the choosing type of polls.', $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" class="ays_toggle_checkbox" id="ays_enable_view_more_button" name="ays_enable_view_more_button"
                               value="on" <?php echo ($enable_view_more_button) ? 'checked' : ''; ?> />
                    </div>
                    <div class="col-sm-8 ays_divider_left ays_toggle_target" style="<?php echo ($enable_view_more_button) ? '' : 'display: none'; ?>">
                       	<div class="form-group row">
                       		<div class="col-sm-3">
                       			<label for="ays_poll_view_more_button_count">
									<?php echo __("Count", $this->plugin_name); ?>
		                            <a class="ays_help" data-toggle="tooltip" data-placement="top" title="<?php echo __("Indicate the number of answers which will be shown in the first place.", $this->plugin_name); ?>">
		                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
		                            </a>
		                        </label>
                       		</div>
                       		<div class="col-sm-9">
                       			<input type="text" class="ays-text-input ays-text-input-short" id="ays_poll_view_more_button_count" name="ays_poll_view_more_button_count" value="<?php echo $poll_view_more_button_count; ?>"/>
                       		</div>
                       	</div>
                    </div>
                </div>
                <hr>                
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="ays_answers_sort_select">
							<?php echo __('Answers sorting', $this->plugin_name) ?>
                            <a class="ays_help" data-toggle="tooltip" data-html="true" data-placement="top"
                               title="<?php
								echo __("Select the way of arrangement of the answers on the voting page of the poll. This works only with the Choosing type.", $this->plugin_name) .
									"<ul style='list-style-type: circle;padding-left: 20px;'>".
                                        "<li>". __('Ascending – the sort is alphabetical from A to Z.',$this->plugin_name) ."</li>".
                                        "<li>". __('Descending – the sort is alphabetical from Z to A.',$this->plugin_name) ."</li>".
                                        "<li>". __('Most voted – by most votes',$this->plugin_name) ."</li>".
                                        "<li>". __('Less voted - by less votes',$this->plugin_name) ."</li>".
                                        "<li>". __('Default - upon your preferences',$this->plugin_name) ."</li>".
                                    "</ul>";
							?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <select name="ays_answers_sort_select" id="ays_answers_sort_select" class="ays-select">
                            <option value="default"    <?= isset($options['answer_sort_type']) && $options['answer_sort_type'] == "default" ? 'selected' : ''; ?>><?php echo __('Default', $this->plugin_name) ?></option>
                            <option value="ascending"  <?= isset($options['answer_sort_type']) && $options['answer_sort_type'] == "ascending" ? 'selected' : ''; ?>><?php echo __('Ascending', $this->plugin_name) ?></option>
                            <option value="descending" <?= isset($options['answer_sort_type']) && $options['answer_sort_type'] == "descending" ? 'selected' : ''; ?>><?php echo __('Descending', $this->plugin_name) ?></option>
                            <option value="votes_asc"  <?= isset($options['answer_sort_type']) && $options['answer_sort_type'] == "votes_asc" ? 'selected' : ''; ?>><?php echo __('Most Voted', $this->plugin_name) ?></option>
                            <option value="votes_desc" <?= isset($options['answer_sort_type']) && $options['answer_sort_type'] == "votes_desc" ? 'selected' : ''; ?>><?php echo __('Less Voted', $this->plugin_name) ?></option>
                        </select>
                    </div>
                </div>                
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label>
                            <?php echo __('Answers numbering',$this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Assign numbering to each answer in ascending sequential order. Choose your preferred type from the list.',$this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <select class="ays-text-input ays-text-input-short" name="ays_poll_show_answers_numbering" style="width: 200px;">
                            <option <?php echo $show_answers_numbering == "none" ? "selected" : ""; ?> value="none"><?php echo __( "None", $this->plugin_name); ?></option>
                            <option <?php echo $show_answers_numbering == "1." ? "selected" : ""; ?> value="1."><?php echo __( "1.", $this->plugin_name); ?></option>
                            <option <?php echo $show_answers_numbering == "1)" ? "selected" : ""; ?> value="1)"><?php echo __( "1)", $this->plugin_name); ?></option>
                            <option <?php echo $show_answers_numbering == "A." ? "selected" : ""; ?> value="A."><?php echo __( "A.", $this->plugin_name); ?></option>
                            <option <?php echo $show_answers_numbering == "A)" ? "selected" : ""; ?> value="A)"><?php echo __( "A)", $this->plugin_name); ?></option>
                            <option <?php echo $show_answers_numbering == "a." ? "selected" : ""; ?> value="a."><?php echo __( "a.", $this->plugin_name); ?></option>
                            <option <?php echo $show_answers_numbering == "a)" ? "selected" : ""; ?> value="a)"><?php echo __( "a)", $this->plugin_name); ?></option>
                        </select>
                    </div>
                </div> <!-- Show answers numbering -->
                <hr>
                <div class="form-group row">
                    <div class="col-sm-12" style="padding:20px;">
                        <div class="pro_features pro_features_integrations" style="justify-content:flex-end;">
                            <div style="margin-right:20px;">
                                <p style="font-size:20px;">
                                    <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                    <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                </p>
                            </div>
                        </div>
                        <div class="form-group row ays_toggle_parent">
                            <div class="col-sm-3">
                                <label for="ays_show_votes_before_voting">
                                    <?= __('Show votes count per answer before voting', $this->plugin_name); ?>
                                    <a class="ays_help" data-toggle="tooltip" data-placement="top"
                                    title="<?= __('Display the votes count per answer to the poll participants beforehand. It will show the last result. There are two ways to represent the votes count: by count and by percentage.', $this->plugin_name) ?>">
                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                    </a>
                                </label>
                            </div>
                            <div class="col-sm-1">
                                <input type="checkbox" class="ays_toggle_checkbox"/>
                            </div>
                            <div class="col-sm-8 ays_divider_left ays_toggle_target" >
                                <div class="d-flex">
                                    <div class="form-check-inline ays_poll_loader">
                                        <label class="form-check-label">
                                            <input type="radio" value="by_count" />
                                            <span><?= __('By count', $this->plugin_name); ?></span>
                                        </label>
                                    </div>
                                    <div class="form-check-inline ays_poll_loader">
                                        <label class="form-check-label">
                                            <input type="radio" value="by_percentage"/>
                                            <span><?= __('By percentage', $this->plugin_name); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- Status -->
                    </div> 
                </div> 
            </div>
            <div id="tab4" class="ays-poll-tab-content <?= $active_tab == 'Limitations' ? 'ays-poll-tab-content-active' : ''; ?>">
                <p class="ays-subtitle">
					<?= __('Limitation of Users', $this->plugin_name); ?>
                </p>
                <hr/>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="apm_limit_users">
							<?= __('Maximum number of attempts per user', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
                               title="<?= __('This option allows to block the users who have already voted.', $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" id="apm_limit_users" name="apm_limit_users"
                               value="on" <?= $options['limit_users'] ? 'checked' : ''; ?> />
                    </div>
                    <div class="if-limit-users col-sm-8 ays_divider_left">
                        <div class="ays-limitation-options">
                            <!-- Limitation method -->
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="ays_redirect_method">
				                        <?= __('Detects users by', $this->plugin_name); ?>
		                            	<a class="ays_help" data-toggle="tooltip" data-html="true" data-placement="top" title="<p style='margin-bottom:3px;'><?php echo __( 'Choose the method of detection of the user:' , $this->plugin_name ); ?>
                                        <p style='padding-left:10px;margin-bottom:3px;'><strong><?php echo __( 'By IP', $this->plugin_name ); ?></strong> <?php echo __( ' - Detect the users by their IP addresses and limit them. It will work both for guests and registered users. Note: in general, IP is not a static variable, it is constantly changing when the user changes his location/ WIFI/ Internet provider.' , $this->plugin_name ); ?></p>
                                        <p style='padding-left:10px;margin-bottom:3px;'><strong><?php echo __( 'By User ID', $this->plugin_name ); ?></strong><?php echo __( ' - Detect the users by their WP User IDs and limit them. It will work only for registered users. Recommended using this method to get more reliable results.', $this->plugin_name ); ?></p>
                                        <p style='padding-left:10px;margin-bottom:3px;'><strong><?php echo __( 'By Cookie ', $this->plugin_name ); ?></strong><?php echo __( ' - Detect the users by their browser cookies and limit them.  It will work both for guests and registered users.', $this->plugin_name ); ?></p>
                                        <p style='padding-left:10px;margin-bottom:3px;'><strong><?php echo __( 'By Cookie and IP ', $this->plugin_name ); ?></strong><?php echo __( ' - Detect the users both by their browser cookies and IP addresses and limit them. It will work both for guests and registered users.', $this->plugin_name ); ?></p>"
                                        >
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
				                    </label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="ays-poll-sel-fields d-flex p-0">
                                        <div class="ays-poll-check-box mr-2">
                                            <input type="radio" id="ays_limit_method_ip" name="ays_limit_method"
                                                   value="ip" <?= (!empty($options['limit_users_method']) && $options['limit_users_method'] == 'ip') || empty($options['limit_users_method']) ? "checked" : "" ?> />
                                            <label class="form-check-label"
                                                   for="ays_limit_method_ip"><?= __('IP', $this->plugin_name); ?> </label>
                                        </div>
                                        <div class="ays-poll-check-box mr-2">
                                            <input type="radio" id="ays_limit_method_user" name="ays_limit_method"
                                                   value="user" <?= !empty($options['limit_users_method']) && $options['limit_users_method'] == 'user' ? "checked" : "" ?> />
                                            <label class="form-check-label"
                                                   for="ays_limit_method_user"><?= __('User ID', $this->plugin_name); ?> </label>
                                        </div>
                                        <div class="ays-poll-check-box mr-2">
                                            <input type="radio" id="ays_limit_method_cookie" name="ays_limit_method"
                                                   value="cookie" <?= !empty($options['limit_users_method']) && $options['limit_users_method'] == 'cookie' ? "checked" : "" ?> />
                                            <label class="form-check-label"
                                                   for="ays_limit_method_cookie"><?= __('Cookie', $this->plugin_name); ?> </label>
                                        </div>
                                        <div class="ays-poll-check-box mr-2">
                                            <input type="radio" id="ays_limit_method_cookie_ip" name="ays_limit_method"
                                                   value="cookie_ip" <?= !empty($options['limit_users_method']) && $options['limit_users_method'] == 'cookie_ip' ? "checked" : "" ?> />
                                            <label class="form-check-label"
                                                   for="ays_limit_method_cookie_ip"><?= __('Cookie and IP', $this->plugin_name); ?> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <!-- Limitation message -->
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="ays_limitation_message">
										<?= __('Message', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" data-placement="top"
                                           title="<?= __('Write the message for those who have already passed the poll under the given conditions.', $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-9">
									<?php
									$content   = !empty($options['limitation_message']) ? stripslashes($options['limitation_message']) : stripslashes($default_options['limitation_message']);
									$editor_id = 'ays_limitation_message';
									$settings  = array(
										'editor_height'  => '4',
										'textarea_name'  => 'ays_limitation_message',
										'editor_class'   => 'ays-textarea',
										'media_elements' => false
									);
									wp_editor($content, $editor_id, $settings);
									?>
                                </div>
                            </div>
                            <hr>
                            <!-- Limitation redirect url -->
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="ays_redirect_url">
										<?= __('Redirect URL', $this->plugin_name); ?>
										<a class="ays_help" data-toggle="tooltip" data-placement="top"
                                           title="<?= __('Enable redirection to the custom URL for those who have already passed the poll under the given conditions.', $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
									</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" name="ays_redirect_url" id="ays_redirect_url"
                                           class="ays-text-input"
                                           value="<?= $options['redirect_url'] ?>"/>
                                </div>
                            </div>
                            <hr>
                            <!-- Limitation redirect delay -->
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="ays_redirection_delay">
										<?= __('Redirect delay (sec)', $this->plugin_name); ?>
										<a class="ays_help" data-toggle="tooltip" data-placement="top"
                                           title="<?= __('Choose the redirection delay in seconds. If you set it to 0, the redirection will be disabled.', $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
									</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="number" name="ays_redirection_delay" id="ays_redirection_delay"
                                           class="ays-text-input"
                                           value="<?= $options['redirection_delay'] ?>"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="ays_see_result_show">
										<?= __('See results', $this->plugin_name); ?>
										<a class="ays_help" data-toggle="tooltip" data-html="true" data-placement="top"
                                           title="<p style='margin-bottom:3px;'><?php echo __( 'Display the live results of the poll to those who have already passed the poll under the given conditions. There are two ways of displaying the results:' , $this->plugin_name ); ?>
										<p style='padding-left:10px;margin-bottom:3px;'><strong><?php echo __( 'After clicking on the button:', $this->plugin_name ); ?></strong> <?php echo __( '- It will show the results after clicking on the See Results button.' , $this->plugin_name ); ?></p>
		                                <p style='padding-left:10px;margin-bottom:3px;'><strong><?php echo __( 'Directly:', $this->plugin_name ); ?></strong><?php echo __( ' - It will show the results immediately.', $this->plugin_name ); ?></p>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>                                    
									</label>
                                </div>
                                <div class="col-sm-9 row">
                                    <div class="col-sm-2" style="height: 41px;">
                                        <input type="checkbox" id="ays_see_result_show" name="ays_see_result_show" class="ays_poll_show_hide_buttons" value="on" <?php echo $poll_see_result_button; ?>>
                                    </div>
                                    <div class="col-sm-10 ays-poll-sel-fields row p-0 <?php echo $poll_see_result_button_cont; ?>" id="ays_poll_show_hide_button" style="height: 41px;">
                                        <div class="ays-poll-check-box mr-2">
                                            <input type="radio" id="ays_see_result_button_show" name="ays_poll_see_result_show" class="ays_poll_show_hide_buttons" value="ays_see_result_button" <?php echo $poll_see_result_botton_show?>>
                                            <label for="ays_see_result_button_show" class="form-check-label"><?= __('After clicking on the button', $this->plugin_name); ?></label>
                                        </div>
                                        <div class="ays-poll-check-box mr-2">
                                            <input type="radio" id="ays_see_result_button_hide" name="ays_poll_see_result_show" class="ays_poll_show_hide_buttons" value="ays_see_result_immediately" <?php echo $poll_see_result_immediately?>>
                                            <label for="ays_see_result_button_hide" class="form-check-label"><?= __('Directly', $this->plugin_name); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-sm-12" style="padding:35px 0;">
							    <div class="pro_features" style="justify-content:flex-end;">
							        <div style="margin-right:20px;">
							            <p style="font-size:13px;">
							                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
							                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
							            </p>
							        </div>
							    </div>
							    <div class="form-group row d-flex">
					                <div class="col-sm-3">
					                    <label for="ays_attempts_count">
					                        <?php echo __('Attempts count',$this->plugin_name)?>
					                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Specify the count of the attempts per user for passing the poll.',$this->plugin_name)?>">
					                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
					                        </a>
					                    </label>
					                </div>
					                <div class="col-sm-9">
					                    <input type="number" min="1" name="ays_attempts_count" id="ays_attempts_count" class="ays-enable-timerl ays-text-input">
					                </div>
						        </div>
							</div>
                        </div>
                    </div>
                </div><!-- Maximum number of attempts per user -->
                <hr/>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="ays_enable_logged_users">
							<?= __('Only for logged in users', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
                               title="<?= __('After enabling this option, only logged-in users will be able to pass the poll.', $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" id="ays_enable_logged_users" name="ays_enable_logged_users"
                               value="on" <?= $options['enable_logged_users'] == 1 ? 'checked' : ''; ?> />
                    </div>
                    <div class="if-logged-in col-sm-8">
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label for="ays_logged_in_message">
									<?= __('Message', $this->plugin_name); ?>
                                    <a class="ays_help" data-toggle="tooltip" data-placement="top"
                                       title="<?= __('Write the message for unauthorized users.', $this->plugin_name); ?>">
                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                    </a>
                                </label>
                            </div>
                            <div class="col-sm-9">
								<?php
								$content   = !empty($options['enable_logged_users_message']) ? stripslashes($options['enable_logged_users_message']) : $default_options['enable_logged_users_message'];
								$editor_id = 'ays_logged_in_message';
								$settings  = array(
									'editor_height'  => '4',
									'textarea_name'  => 'ays_enable_logged_users_message',
									'editor_class'   => 'ays-textarea',
									'media_elements' => false
								);
								wp_editor($content, $editor_id, $settings);
								?>
                            </div>
                        </div>
                        <hr/>                        
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label for="ays_show_login_form">
                                    <?php echo __('Show Login form',$this->plugin_name)?>
                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Enable to show the login form.',$this->plugin_name)?>">
                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                    </a>
                                </label>
                            </div>
                            <div class="col-sm-9">
                                <input type="checkbox" class="ays-enable-timer1" id="ays_show_login_form" name="ays_show_login_form" value="on" <?php echo ($show_login_form && $options['enable_logged_users'] == 1) ? 'checked' : ''; ?>/>
                            </div>
                        </div>
                    </div>
                </div> <!-- Only for logged in users -->
                <hr/>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="ays_enable_restriction_pass">
							<?= __('Only for selected user role', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
                               title="<?= __('After enabling this option, only the user roles mentioned in the list will be able to pass the poll.', $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" id="ays_enable_restriction_pass" name="ays_enable_restriction_pass"
                               value="on" <?= (isset($options['enable_restriction_pass']) &&
						                       $options['enable_restriction_pass'] == 1) ? 'checked' : ''; ?> />
                    </div>
                    <div class="if-users-roles col-sm-8 ays_divider_left">
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label for="ays_users_roles">
									<?= __('User role', $this->plugin_name); ?></label>
                            </div>
                            <div class="col-sm-9">
                                <select name="ays_users_roles[]" id="ays_users_roles" class="ays-select" multiple>
									<?php
									foreach ($ays_users_roles as $key => $user_role) {
		                                $selected_role = "";
		                                if(is_array($users_role)){
		                                    if(in_array($user_role['name'], $users_role)){
		                                        $selected_role = 'selected';
		                                    }else{
		                                        $selected_role = '';
		                                    }
		                                }else{
		                                    if($users_role == $user_role['name']){
		                                        $selected_role = 'selected';
		                                    }else{
		                                        $selected_role = '';
		                                    }
		                                }
		                                echo "<option value='" . $user_role['name'] . "' " . $selected_role . ">" . $user_role['name'] . "</option>";
		                            }
									?>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label for="restriction_pass_message">
									<?= __('Message', $this->plugin_name); ?>
                                    <a class="ays_help" data-toggle="tooltip" data-placement="top"
                                       title="<?= __('Write the message for those who aren’t included in the list.', $this->plugin_name); ?>">
                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                    </a>
                                </label>
                            </div>
                            <div class="col-sm-9">
								<?php
								$content   = !empty($options['restriction_pass_message']) ? stripslashes($options['restriction_pass_message']) : stripslashes($default_options['restriction_pass_message']);
								$editor_id = 'restriction_pass_message';
								$settings  = array(
									'editor_height'  => '4',
									'textarea_name'  => 'restriction_pass_message',
									'editor_class'   => 'ays-textarea',
									'media_elements' => false
								);
								wp_editor($content, $editor_id, $settings);
								?>
                            </div>
                        </div>
                    </div>
                </div> <!-- Only for selected user role -->
                <hr>                
                <div class="form-group row ays_toggle_parent">
                    <div class="col-sm-3">
                        <label for="ays_poll_enable_password">
                            <?php echo __('Password for passing Poll', $this->plugin_name)?>
                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Choose a password for users to pass the poll.' , $this->plugin_name)?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" class="ays-enable-timer1 ays_toggle_checkbox" id="ays_poll_enable_password"
                                name="ays_poll_enable_password" value="on" <?php echo ($poll_enable_password) ? "checked" : ""; ?>>
                    </div>
                    <div class="col-sm-8 ays_toggle_target ays_divider_left <?php echo ($poll_enable_password) ? "" : "display_none"; ?>">
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label for="ays_poll_password">
                                    <?php echo __('Password' , $this->plugin_name)?>
                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Write your password.' , $this->plugin_name)?>">
                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                    </a>
                                </label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="ays_poll_password" id="ays_poll_password" class="ays-enable-timerl ays-text-input" value="<?php echo $poll_password; ?>">
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label for="ays_poll_password">
                                    <?php echo __('Message' , $this->plugin_name)?>
                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Write your password.' , $this->plugin_name)?>">
                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                    </a>
                                </label>
                            </div>
                            
                            <div class="col-sm-9">
                                <?php 
                                    $content   = $poll_password_message;
                                    $editor_id = 'ays-poll-password-message';
                                    $settings  = array('editor_height' => '8', 'textarea_name' => 'ays_poll_password_message', 'editor_class' => 'ays-textarea', 'media_elements' => false);
                                    wp_editor($content, $editor_id, $settings);
                                ?>
                            </div>
                        </div>
                    </div>
			    </div><!-- Password for passing Poll PRO Feautre-->
                <hr>
                <div class="col-sm-12" style="padding:20px 0;">
				    <div class="pro_features" style="justify-content:flex-end;">
				        <div style="margin-right:20px;">
				            <p style="font-size:15px;">
				                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
				                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
				            </p>
				        </div>
				    </div>
	                <div class="form-group row ays_toggle_parent">
				        <div class="col-sm-3">
				            <label for="ays_enable_tackers_count">
				                <?php echo __('Limitation count of takers', $this->plugin_name)?>
				                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('You can choose how many users can pass the poll.',$this->plugin_name)?>">
				                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
				                </a>
				            </label>
				        </div>
				        <div class="col-sm-1">
				            <input type="checkbox" class="ays-enable-timer1 ays_toggle_checkbox" id="ays_enable_tackers_count"
				                   name="ays_enable_tackers_count" value="on">
				        </div>
				        <div class="col-sm-8 ays_toggle_target ays_divider_left">
				            <div class="form-group row">
				                <div class="col-sm-3">
				                    <label for="ays_tackers_count">
				                        <?php echo __('Count',$this->plugin_name)?>
				                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('The number of users who can pass the poll.',$this->plugin_name)?>">
				                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
				                        </a>
				                    </label>
				                </div>
				                <div class="col-sm-9">
				                    <input type="number" name="ays_tackers_count" id="ays_tackers_count" class="ays-enable-timerl ays-text-input"
				                           >
				                </div>
				            </div>
				        </div>
				    </div>
				</div><!-- Limitation count of takers Poll PRO Feautre-->
	            <hr>
	            <div class="col-sm-12" style="padding:25px 0;">
				    <div class="pro_features" style="justify-content:flex-end;">
				        <div style="margin-right:20px;">
				            <p style="font-size:16px;">
				                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
				                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
				            </p>
				        </div>
				    </div>
		            <div class="form-group row ays_toggle_parent">
				        <div class="col-sm-3">
				            <label for="ays_enable_vote_limitation">
				                <?php echo __('Allow to vote once per session', $this->plugin_name)?>
				                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('If you enable this feature, you can select the frequency the users can have access to the poll after the first attempt. For example, if you give a 1-day value to the session period, the user will have access to the poll once in 1-day.' , $this->plugin_name)?>">
				                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
				                </a>
				            </label>
				        </div>
				        <div class="col-sm-1">
				            <input type="checkbox" class="ays-enable-timer1 ays_toggle_checkbox" id="ays_enable_vote_limitation"
				                   name="ays_enable_vote_limitation" value="on" >
				        </div>
				        <div class="col-sm-8 ays_toggle_target ays_divider_left <">
				            <div class="form-group row">
				                <div class="col-sm-3">
				                    <label for="ays_vote_limitation">
				                        <?php echo __('Session Period' , $this->plugin_name)?>
				                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Specify the time for one session.' , $this->plugin_name)?>">
				                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
				                        </a>
				                    </label>
				                </div>
				                <div class="col-sm-7">
				                    <input type="text" name="ays_vote_limitation" id="ays_vote_limitation" class="ays-enable-timerl ays-text-input">
	                                
				                </div>
				                <div class="col-sm-2">
	                                <select name="ays_vote_limitation_time_period" id="ays_vote_limitation_period">                           
	                                    <option>Minute(s)
	                                    </option>
	                                </select>
				                </div>                            
				            </div>
	                        <div class="form-group row d-flex w-100">
	                            <div class="col-sm-3">
	                                <label for="vote_limit_message">
						    			<?= __('Message', $this->plugin_name); ?>
	                                    <a class="ays_help" data-toggle="tooltip" data-placement="top"
	                                       title="<?= __('Write the message, which will be shown during the restricted time, when the user has already passed his/her limit for the session.', $this->plugin_name); ?>">
	                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
	                                    </a>
	                                </label>
	                            </div>
	                            <div class="col-sm-9">
						    		<?php		    		
						    		$editor_id = 'vote_limit_message';
						    		$settings  = array(
						    			'editor_height'  => '4',
						    			'textarea_name'  => 'vote_limit_message',
						    			'editor_class'   => 'ays-textarea',
						    			'media_elements' => false
						    		);
						    		wp_editor('This feature is available only in PRO version!!!', 'editor_id', $settings);
						    		?>
	                            </div>
	                        </div>    
				        </div>
				    </div>
                </div> <!-- Allow to vote once per session PRO Feautre-->
                <hr>
                <div class="col-sm-12" style="padding:25px 0;">
				    <div class="pro_features" style="justify-content:flex-end;">
				        <div style="margin-right:20px;">
				            <p style="font-size:16px;">
				                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
				                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
				            </p>
				        </div>
                    </div>
                    <div class="form-group row ays_toggle_parent">
                        <div class="col-sm-3">
                            <label for="enable_limit_by_country">
                                <?php echo __('Limit by country', $this->plugin_name)?>
                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __(' After enabling this option, the given poll will not be available in the selected country.' , $this->plugin_name)?>">
                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                </a>
                            </label>
                        </div>
                        <div class="col-sm-1">
                            <input type="checkbox" class="ays-enable-timer1"/>
                        </div>
                        <div class="col-sm-8 ays_toggle_target ays_divider_left">
                            <select class="ays-text-input ays-text-input-short" style="width: 15vw;">                            
                                    <option>Andorra</option>
                            </select>
                        </div>
                    </div> 
                </div> <!-- Limit by country PRO Feautre -->
            	<hr>
            </div>
            <div id="tab5" class="ays-poll-tab-content <?= $active_tab == 'Userdata' ? 'ays-poll-tab-content-active' : ''; ?>">
                <p class="ays-subtitle">
					<?= __('User Data Settings', $this->plugin_name); ?>
                </p>
                <hr/>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label>
							<?= __('Information form title', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
                               title="<?= __("Write the title of the Information Form which will be shown at the top of the Form Fields.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9 ays_divider_left">
						<?php
						$content   = stripslashes($options['info_form_title']);
						$editor_id = 'ays-poll-info-form-text';
						$settings  = array(
							'editor_height'  => '2',
							'textarea_name'  => 'ays-poll-info-form-text',
							'editor_class'   => 'ays-textarea',
							'media_elements' => false
						);
						wp_editor($content, $editor_id, $settings);
						?>
                    </div>
                </div>
                <hr>
                <div class="form-group row ays_toggle_parent">
                    <div class="col-sm-2">
                        <label for='ays_poll_info_form'>
							<?= __('Enable Information Form', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip"
                               data-placement="top"
                               title="<?= __("After enabling this option, the user will have to fill out the information form (data form for the user’s personal information) after submitting the poll.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" class="ays_toggle_checkbox" name="ays_poll_info_form" id="ays_poll_info_form" value="on" <?php echo ($options['info_form'] == 1) ? 'checked' : ''; ?>>
                    </div>
                    <div class="col-sm-9 ays_divider_left ays_toggle_target_inverse <?php echo (isset($options['info_form']) && $options['info_form'] == 1) ? 'display_none' : ''; ?>">	                    
                    <div class="col-sm-7">
                        <label for="ays_allow_collecting_logged_in_users_data" style="margin-right:20px;">
                            <?php echo __('Allow collecting information of logged in users',$this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Allow collecting information from logged in users. Email and name of users will be stored in the database. Email options will be work for these users.', $this->plugin_name)?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                        <input type="checkbox" id="ays_allow_collecting_logged_in_users_data" value="on" name="ays_allow_collecting_logged_in_users_data" <?php echo $poll_allow_collecting_users_data; ?>>
                    </div> <!-- Allow collecting information of logged in users -->
                    </div>
                    <div class="ays_poll_info_form col-sm-9 ays_toggle_target" style="border-left: 1px solid #ccc; <?php echo $options['info_form'] == 1 ? "display: block;" : "display: none;"; ?>">
                        <div>
                            <label>
	                            <?php echo __('Form Fields', $this->plugin_name); ?>
                                <a class="ays_help" data-toggle="tooltip" data-placement="top" title="<?= __("Select which fields the user should fill out.", $this->plugin_name); ?>">
                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                </a>
                            </label>
                            <hr>
                        </div>
                        <div>
                            <div class="ays-poll-sel-fields d-flex">
	                            <?php foreach ( $all_fields as $field ): ?>
                                    <div class="ays-poll-check-box mr-2">
                                        <input type="checkbox" name="ays-poll-form-fields[]" value="<?= $field['slug']; ?>" id="ays-poll-form-field-<?= $field['slug']; ?>"
		                                    <?= (array_search($field['slug'], $fields) !== false) ? "checked" : ""; ?>>
                                        <label for="ays-poll-form-field-<?= $field['slug']; ?>">
	                                        <?= ucfirst($field['name']); ?></label>
                                    </div>
	                            <?php endforeach; ?>
                            </div>
                            <hr>
                        </div>
                        <div>
                            <label>
	                            <?= __('Required Fields', $this->plugin_name); ?>
                                <a class="ays_help" data-toggle="tooltip" data-placement="top"
                                   title="<?= __("Select which fields are required.", $this->plugin_name); ?>">
                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                </a>
                            </label>
                            <hr>
                        </div>
                        <div>
                            <div class="ays-poll-req-fields d-flex">
	                            <?php foreach ( $all_fields as $field ): ?>
                                    <div class="ays-poll-check-box mr-2"
                                         id="ays-poll-box-rfield-<?= $field['slug']; ?>" <?= (array_search($field['slug'],
		                                    $fields) === false) ? "style='display:none'" : ""; ?>>
                                        <input type="checkbox" name="ays-poll-form-required-fields[]"
                                               value="<?= $field['slug']; ?>"
                                               id="ays-poll-form-rfield-<?= $field['slug']; ?>" <?= (array_search($field['slug'],
		                                        $required_fields) !== false) ? "checked" : ""; ?>>
                                        <label for="ays-poll-form-rfield-<?= $field['slug']; ?>">
	                                        <?= ucfirst($field['name']); ?></label>
                                    </div>
	                            <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <hr>
                </div>
                <hr>

                <div class="form-group row">
                	<div class="col-sm-12" style="padding:20px;">
	                    <div class="pro_features" style="justify-content:flex-end;">
	                        <div style="margin-right:20px;">
	                            <p style="font-size:20px;">
	                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
	                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
	                            </p>
	                        </div>
	                    </div>
	                    <div class="form-group row">
		                    <div class="col-sm-3">
		                        <label>
									<?= __('Add custom fields', $this->plugin_name); ?>
									<a class="ays_help" data-toggle="tooltip" data-placement="top" title="<?= __("You can create custom fields with the following types: text, textarea, number, telephone, date, e-mail, URL, color, checkbox.", $this->plugin_name); ?>">
	                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
	                                </a>
		                        </label>
		                    </div>
		                    <div class="col-sm-9">
		                        <blockquote>
									<?= __("For creating custom fields click ", $this->plugin_name); ?>
		                            <a href="?page=<?= $this->plugin_name; ?>-poll-attributes"><?= __("here", $this->plugin_name); ?></a>
		                        </blockquote>
		                    </div>
		                </div>
	                </div>
                </div>
                <hr>
            </div>
            <div id="tab6" class="ays-poll-tab-content <?= $active_tab == 'Email' ? 'ays-poll-tab-content-active' : ''; ?>">
                <p class="ays-subtitle">
					<?= __('Email settings', $this->plugin_name); ?>
                </p>
                <hr>
	            <div class="form-group row ays_toggle_parent">
                    <div class="col-sm-3">
                        <label for="ays_notify_by_email_on">
							<?= __('Results notification by email', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
                               title="<?= __('If the option is enabled, the admin(or your provided email) will receive an email notification about votes at each time.', $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                	<div class="col-sm-1">
                        <input type="checkbox" class="ays_toggle_checkbox" id="ays_notify_by_email_on" name="ays_notify_by_email_on" value="on" <?php echo ($notify_email_on) ? 'checked' : ''; ?> />
                    </div>
                    <div class="col-sm-8 ays_toggle_target" style="<?php echo ($notify_email_on) ? '' : 'display: none;'; ?>">
                        <div class="row ays_divider_left">
                    		<div class="col-sm-3">
                                <label for="ays_notify_email">
									<?php echo __('Email address', $this->plugin_name); ?>
                                    <a class="ays_help" data-toggle="tooltip" data-placement="top"
                                       title="<?= __('If you want to set another email, enter it here. Leave it blank for an admin email.', $this->plugin_name); ?>">
                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                    </a>
                                </label>
                            </div>
                            <div class="col-sm-9">
                                <input type="email" class="ays-text-input" name="ays_notify_email" id="ays_notify_email" value="<?= isset($options['notify_email']) && !empty($options['notify_email']) ? $options['notify_email'] : get_option('admin_email'); ?>" size="30">
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="col-sm-12" style="padding:20px;">
                    <div class="pro_features" style="justify-content:flex-end;">
                        <div style="margin-right:20px;">
                            <p style="font-size:20px;">
                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                            </p>
                        </div>
                    </div>
					<div class="form-group row" >
	                    <div class="col-sm-3">
	                        <label for="ays_enable_mail_user">
	                            <?= __('Send Mail to User', $this->plugin_name); ?>
	                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
	                            title="<?= __('Send the message to the emails of the users after the submission of the poll.', $this->plugin_name); ?>">
	                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
	                            </a>
	                        </label>
	                    </div>
	                    <div class="col-sm-1">                       
	                            <input type="checkbox" id="ays_enable_mail_user" name="ays_enable_mail_user" value="on" />
	                    </div>
	                    <div class="col-sm-8 if-enable-email_note">
	                       
	                    </div>
	                </div>
                </div>
                <hr>
                <div class="col-sm-12" style="padding:15px;">
                    <div class="pro_features" style="justify-content:flex-end;">
                        <div>
                            <p style="font-size:15px;">
                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                            </p>
                        </div>
                        <div style="position: absolute; top: 15px;">
                            <p style="font-size:15px;">
                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                            </p>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-4">
                            <label>
                                <?php echo __('Email configuration',$this->plugin_name)?>
                                <a  class="ays_help" data-toggle="tooltip" title="<?php echo __('Set up the attributes of the sending email.',$this->plugin_name); ?>">
                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                </a>
                            </label>
                        </div>
                        <div class="col-sm-8 ays_divider_left">
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="ays_poll_email_configuration_from_email">
                                        <?php echo __('From email',$this->plugin_name)?>
                                        <a class="ays_help" data-toggle="tooltip" data-html="true" title="<?php 
                                            echo htmlspecialchars( sprintf(
                                                __('Specify the email address from which the results will be sent. If you leave the field blank, the sending email address will take the default value — %spoll_maker@{your_site_url}%s.',$this->plugin_name),
                                                '<em>',
                                                '</em>'
                                            ) );
                                        ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" class="ays-text-input" />
                                </div>
                            </div> <!-- From email -->
                            <hr/>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="ays_poll_email_configuration_from_name">
                                        <?php echo __('From name',$this->plugin_name)?>
                                        <a class="ays_help" data-toggle="tooltip" data-html="true" title="<?php 
                                            echo htmlspecialchars( sprintf(
                                                __("Specify the name that will be displayed as the sender of the results. If you don't enter any name, it will be %sPoll Maker%s.",$this->plugin_name),
                                                '<em>',
                                                '</em>'
                                            ) );
                                        ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" class="ays-text-input" >
                                </div>
                            </div><!-- From name -->
                            <hr/>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="ays_poll_email_configuration_from_subject">
                                        <?php echo __('Subject',$this->plugin_name)?>
                                        <a  class="ays_help" data-toggle="tooltip" title="<?php echo __("Fill in the subject field of the message. If you don't, it will take the poll title.",$this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" class="ays-text-input">
                                </div>
                            </div> <!-- Subject -->
                            <hr/>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="ays_poll_email_configuration_replyto_email">
                                        <?php echo __('Reply to email',$this->plugin_name)?>
                                        <a  class="ays_help" data-toggle="tooltip" title="<?php echo __("Specify to which email the poll taker can reply. If you leave the field blank, the email address won't be specified.",$this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" class="ays-text-input">
                                </div>
                            </div> <!-- Reply to email -->
                            <hr/>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="ays_poll_email_configuration_replyto_name">
                                        <?php echo __('Reply to name',$this->plugin_name)?>
                                        <a  class="ays_help" data-toggle="tooltip" title="<?php echo __("Specify the name of the email address to which the poll taker can reply. If you leave the field blank, the name won't be specified.",$this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" class="ays-text-input">
                                </div>
                            </div> <!-- Reply to name -->
                        </div>
                    </div> <!-- Email Configuration -->
                </div> 
                <hr>
            </div>
            <div id="tab7" class="ays-poll-tab-content <?= $active_tab == 'Integrations' ? 'ays-poll-tab-content-active' : ''; ?>">
                <p class="ays-subtitle">
                    <?= __('Integrations settings', $this->plugin_name); ?>
                </p>
                <hr/>
                <fieldset>
                    <legend>
                        <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/mailchimp_logo.png" alt="">
                        <h5><?php echo __('MailChimp Settings',$this->plugin_name)?></h5>
                    </legend>
                    <?php
                    if(count($mailchimp) > 0):
                        ?>
                        <?php
                        if($mailchimp_username == "" || $mailchimp_api_key == ""):
                            ?>
                            <blockquote class="error_message">
                                <?php echo __(
                                    sprintf(
                                        __("For enabling this option, please go to ",$this->plugin_name)." %s ". __("page and fill all options.",$this->plugin_name),
                                        "<a style='color:blue;text-decoration:underline;font-size:20px;' href='?page=$this->plugin_name-settings&ays_poll_tab=tab2'>". __("this",$this->plugin_name)."</a>"
                                    ),
                                    $this->plugin_name );
                                ?>
                            </blockquote>
                        <?php
                        else:
                            ?>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_enable_mailchimp">
                                        <?php echo __('Enable MailChimp',$this->plugin_name)?>
                                    </label>
                                </div>
                                <div class="col-sm-1">
                                    <input type="checkbox" class="ays-enable-timer1" id="ays_enable_mailchimp"
                                           name="ays_enable_mailchimp"
                                           value="on"
                                        <?php
                                        if($mailchimp_username == "" || $mailchimp_api_key == ""){
                                            echo "disabled";
                                        }else{
                                            echo ($enable_mailchimp == 'on') ? 'checked' : '';
                                        }
                                        ?>/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_mailchimp_list">
                                        <?php echo __('MailChimp list',$this->plugin_name)?>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <?php if(is_array($mailchimp_select)): ?>
                                        <select name="ays_mailchimp_list" id="ays_mailchimp_list"
                                            <?php
                                            if($mailchimp_username == "" || $mailchimp_api_key == ""){
                                                echo 'disabled';
                                            }
                                            ?>>
                                            <option value="" disabled selected>Select list</option>
                                            <?php foreach($mailchimp_select as $mlist): ?>
                                                <option <?php echo ($mailchimp_list == $mlist['listId']) ? 'selected' : ''; ?>
                                                        value="<?php echo $mlist['listId']; ?>"><?php echo $mlist['listName']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <span><?php echo $mailchimp_select; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php
                        endif;
                        ?>
                    <?php
                    else:
                        ?>
                        <blockquote class="error_message">
                            <?php echo __(
                                sprintf(
                                    __("For enabling this option, please go to ",$this->plugin_name)." %s ". __("page and fill all options.",$this->plugin_name),
                                    "<a style='color:blue;text-decoration:underline;font-size:20px;' href='?page=$this->plugin_name-settings&ays_poll_tab=tab2'>". __("this",$this->plugin_name)."</a>"
                                ),
                                $this->plugin_name );
                            ?>
                        </blockquote>
                    <?php
                    endif;
                    ?>
                </fieldset><!-- MailChimp Settings -->
                <hr/>
                <fieldset>
                    <legend>
                        <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/campaignmonitor_logo.png" alt="">
                        <h5><?php echo __('Campaign Monitor Settings', $this->plugin_name) ?></h5>
                    </legend>
                    <div class="form-group row">
                        <div class="col-sm-12" style="padding:20px;">
                            <div class="pro_features pro_features_integrations" style="justify-content:flex-end;">
                                <div style="margin-right:20px;">
                                    <p style="font-size:20px;">
                                        <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                        <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_enable_monitor">
                                        <?php echo __('Enable Campaign Monitor', $this->plugin_name) ?>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="checkbox" class="ays-enable-timer1" id="ays_enable_monitor" name="ays_enable_monitor" value="on"/>
                                </div>
                            </div>	                            
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_monitor_list">
                                        <?php echo __('Campaign Monitor list', $this->plugin_name) ?>
                                    </label>
                                </div>
                                <div class="col-sm-8">                                    
                                    <select name="ays_monitor_list" id="ays_monitor_list">
                                        <option value="" disabled selected><?= __("Select List", $this->plugin_name) ?></option>
                                    </select>                                    
                                </div>
                        	</div>
                        </div>
                    </div>
                </fieldset><!-- Campaign Monitor Settings PRO Feature -->
                <hr/>
                <fieldset>
                    <legend>
                        <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/zapier_logo.png" alt="">
                        <h5><?php echo __('Zapier Integration Settings', $this->plugin_name) ?></h5>
                    </legend>
                    <div class="form-group row">
                        <div class="col-sm-12" style="padding:20px;">
                            <div class="pro_features pro_features_integrations" style="justify-content:flex-end;">
                                <div style="margin-right:20px;">
                                    <p style="font-size:20px;">
                                        <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                        <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                    </p>
                                </div>
                            </div> 
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_enable_zapier">
                                        <?php echo __('Enable Zapier Integration', $this->plugin_name) ?>
                                    </label>
                                </div>
                                <div class="col-sm-1">
                                    <input type="checkbox" class="ays-enable-timer1" id="ays_enable_zapier" name="ays_enable_zapier" value="on"/>
                                </div>
                                <div class="col-sm-3">
                                    <button type="button"                                           
                                            id="testZapier"
                                            class="btn btn-outline-secondary">
                                        <?= __("Send test data", $this->plugin_name) ?>
                                    </button>
                                    <a class="ays_help" data-toggle="tooltip" style="font-size: 16px;"
                                       title="<?= __('We will send you a test data, and you can catch it in your ZAP for configure it.', $this->plugin_name) ?>">
                                        <i class="ays_fa ays_fa_info_circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset> <!-- Zapier Integration Settings PRO Feature -->
                <hr/>
                <fieldset>
                    <legend>
                        <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/activecampaign_logo.png" alt="">
                        <h5><?php echo __('Active Campaign Settings', $this->plugin_name) ?></h5>
                    </legend>
                    <div class="form-group row">
                        <div class="col-sm-12" style="padding:20px;">
                            <div class="pro_features pro_features_integrations" style="justify-content:flex-end;">
                                <div style="margin-right:20px;">
                                    <p style="font-size:20px;">
                                        <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                        <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_enable_active_camp">
                                        <?php echo __('Enable ActiveCampaign', $this->plugin_name) ?>
                                    </label>
                                </div>
                                <div class="col-sm-1">
                                    <input type="checkbox" class="ays-enable-timer1" id="ays_enable_active_camp" name="ays_enable_active_camp" value="on"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_active_camp_list">
                                        <?php echo __('ActiveCampaign list', $this->plugin_name) ?>
                                    </label>
                                </div>
                                <div class="col-sm-8">                                   
                                    <select name="ays_active_camp_list" id="ays_active_camp_list">
                                        <option value="" disabled
                                                selected><?= __("Select List", $this->plugin_name) ?></option>
                                        <option value=""><?= __("Just create contact", $this->plugin_name) ?></option>
                                    </select>                                    
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_active_camp_automation">
                                        <?php echo __('ActiveCampaign automation', $this->plugin_name) ?>
                                    </label>
                                </div>
                                <div class="col-sm-8">                                    
                                    <select name="ays_active_camp_automation" id="ays_active_camp_automation">
                                        <option value="" disabled
                                                selected><?= __("Select List", $this->plugin_name) ?></option>
                                        <option value=""><?= __("Just create contact", $this->plugin_name) ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset><!-- Active Campaign Settings PRO Feature -->
                <hr/>
                <fieldset>
                    <legend>
                        <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/slack_logo.png" alt="">
                        <h5><?php echo __('Slack Settings', $this->plugin_name) ?></h5>
                    </legend>
                    <div class="form-group row">
                        <div class="col-sm-12" style="padding:20px;">
                            <div class="pro_features pro_features_integrations" style="justify-content:flex-end;">
                                <div style="margin-right:20px;">
                                    <p style="font-size:20px;">
                                        <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                        <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_enable_slack">
                                        <?php echo __('Enable Slack integration', $this->plugin_name) ?>
                                    </label>
                                </div>
                                <div class="col-sm-1">
                                    <input type="checkbox" class="ays-enable-timer1" id="ays_enable_slack" name="ays_enable_slack" value="on"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_slack_conversation">
                                        <?php echo __('Slack conversation', $this->plugin_name) ?>
                                    </label>
                                </div>
                                <div class="col-sm-8">                                    
                                    <select id="ays_slack_conversation">
                                        <option value="" disabled
                                                selected><?= __("Select Channel", $this->plugin_name) ?></option>
                                    </select>                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    </fieldset> <!-- Slack Settings PRO Feature -->
                <hr>
                <fieldset>
                    <legend>
                        <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/sheets_logo.png" alt="">
                        <h5><?php echo __('Google Sheet Settings', $this->plugin_name) ?></h5>
                    </legend>
                    <div class="form-group row">
                        <div class="col-sm-12" style="padding:20px;">
                            <div class="pro_features pro_features_integrations" style="justify-content:flex-end;">
                                <div style="margin-right:20px;">
                                    <p style="font-size:20px;">
                                        <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                        <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_enable_google">
                                        <?php echo __('Enable Google integration', $this->plugin_name) ?>
                                    </label>
                                </div>
                                <div class="col-sm-1">
                                    <input type="checkbox" class="ays-enable-timer1"
                                        value="on">                      
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset> <!-- Google Sheet Settings PRO Feature -->
                <hr>
                <fieldset>
                    <legend>
                        <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/mad-mimi-logo-min.png" alt="">
                        <h5><?php echo __('Mad Mimi', $this->plugin_name) ?></h5>
                    </legend>
                    <div class="form-group row">
                        <div class="col-sm-12" style="padding:20px;">
                            <div class="pro_features pro_features_integrations" style="justify-content:flex-end;">
                                <div style="margin-right:20px;">
                                    <p style="font-size:20px;">
                                        <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                        <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                    </p>
                                </div>
                            </div>
                            <hr/>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_enable_mad_mimi">
                                        <?php echo __('Enable Mad Mimi', $this->plugin_name) ?>
                                    </label>
                                </div>
                                <div class="col-sm-1">
                                    <input type="checkbox" class="ays-enable-timer1">
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_mad_mimi_list">
                                        <?php echo __('Select List', $this->plugin_name) ?>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <?php 
                                        $mad_mimi_select  = "<select id='ays_poll_mad_mimi_list'>";
                                        $mad_mimi_select .= "<option value=''>Select list</option>";
                                        $mad_mimi_select .= "</select>";
                                        echo $mad_mimi_select;
                                        
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset> <!-- Mad Mimi -->
                <hr>
                <fieldset>
                    <legend>
                        <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/get_response.png" alt="">
                        <h5><?php echo __('GetResponse Settings', $this->plugin_name) ?></h5>
                    </legend>
                    <div class="form-group row">
                        <div class="col-sm-12" style="padding:20px;">
                            <div class="pro_features pro_features_integrations" style="justify-content:flex-end;">
                                <div style="margin-right:20px;">
                                    <p style="font-size:20px;">
                                        <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                        <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_enable_getResponse">
                                        <?php echo __('Enable GetResponse', $this->plugin_name) ?>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="checkbox"
                                        class="ays-enable-timer1">
                                </div>                 
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_getResponse_list">
                                        <?php echo __('GetResponse List', $this->plugin_name) ?>
                                    </label>
                                </div>                                        
                                <div class="col-sm-8">
                                    <?php 
                                        $mad_mimi_select  = "<select id='ays_poll_mad_mimi_list'>";
                                        $mad_mimi_select .= "<option value=''>Select list</option>";
                                        $mad_mimi_select .= "</select>";
                                        echo $mad_mimi_select;
                                        
                                    ?>
                                </div>  
                            </div>
                        </div>
                    </div>
                </fieldset> <!-- GetResponse Settings -->
                <hr>
                <fieldset>
                    <legend>
                        <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/convertkit_logo.png" alt="">
                        <h5><?php echo __('ConvertKit Settings',$this->plugin_name)?></h5>
                    </legend>
                    <div class="form-group row">
                        <div class="col-sm-12" style="padding:20px;">
                            <div class="pro_features pro_features_integrations" style="justify-content:flex-end;">
                                <div style="margin-right:20px;">
                                    <p style="font-size:20px;">
                                        <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                        <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_enable_convertkit">
                                        <?php echo __('Enable ConvertKit',$this->plugin_name)?>
                                    </label>
                                </div>
                                <div class="col-sm-1">
                                    <input type="checkbox" class="ays-enable-timer1"/>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_convertKit_list">
                                        <?php echo __('ConvertKit list',$this->plugin_name)?>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <select id="ays_poll_convertKit_list">                                    
                                        <option value="" disabled selected>Select list</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset> <!-- ConvertKit Settings -->
                <hr>                
            </div>
            <div id="tab8" class="ays-poll-tab-content <?= $active_tab == 'Results Settings' ? 'ays-poll-tab-content-active' : ''; ?>">
            	<p class="ays-subtitle">
					<?= __('Results Settings', $this->plugin_name); ?>
                </p>
                <hr/>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for='show-res-percent'>
                            <?= __('Show answer percent', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip"
                               data-placement="top"
                               title="<?= __("Enable to show the percentage of each question on the progressbar on the result page of the poll.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <input type="checkbox" name="show_res_percent" id="show-res-percent"
                               value="1" <?= $show_res_percent ? 'checked' : '' ?> >
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for='ays-poll-hide-results'>
							<?= __('Hide results', $this->plugin_name); ?>
							<a 	class="ays_help" 
								data-toggle="tooltip"
								data-placement="top"
								title="<?= __("Disable to show voting results to the users on the result page of the poll.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <input type="checkbox" name="ays-poll-hide-results" id="ays-poll-hide-results"
                               value="hide" <?= isset($options['hide_results']) && $options['hide_results'] ? 'checked' : ''; ?>>
                    </div>
                </div>
                <hr>
                <div class="form-group row if-ays-poll-hide-results">
                    <div class="col-sm-3">
                        <label for='ays-poll-hide-results-msg'>
							<?= __('Message instead of results', $this->plugin_name); ?>
							<a 	class="ays_help"
								data-toggle="tooltip"
								data-placement="top"
								title="<?= __("Message that will appear instead of the votting results after the poll", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a></label>
                    </div>
                    <div class="col-sm-9">
						<?php
						$content   = !empty($options['hide_results_text']) ? stripslashes($options['hide_results_text']) : stripslashes($default_options['hide_results_text']);
						$editor_id = 'ays-poll-hide-results-text';
						$settings  = array(
							'editor_height'  => '4',
							'textarea_name'  => 'ays-poll-hide-results-text',
							'editor_class'   => 'ays-textarea',
							'media_elements' => false
						);
						wp_editor($content, $editor_id, $settings);
						?>
                    </div>
                </div>
                <hr class="if-ays-poll-hide-results">
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="ays_poll_result_message">
                            <?php echo __('Result Message',$this->plugin_name)?>
                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Write the message, which will be shown on the result page of the poll.',$this->plugin_name)?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9">
	                    <div class="form-group row">
	                    	<div class="col-sm-12">
	                        	<input type="checkbox" name="ays_poll_result_message" id="ays_poll_result_message"
	                               value="hide" <?= isset($options['hide_result_message']) && $options['hide_result_message'] ? 'checked' : ''; ?>>
	                    	</div>
	                    	<div class="col-sm-12 if_poll_hide_result_message"  style="margin-top: 15px;">
		                        <?php
		                        $content = wpautop(stripslashes((isset($options['result_message'])) ? $options['result_message'] : ''));
		                        $editor_id = 'ays_result_message';
		                        $settings = array('editor_height' => '10', 'textarea_name' => 'ays_result_message', 'editor_class' => 'ays-textarea', 'media_elements' => false);
		                        wp_editor($content, $editor_id, $settings);
		                        ?>
		                    </div>
	                    </div>
                    </div>                    
                </div>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for='ays-poll-load-effect'>
							<?php echo __('Results sorting', $this->plugin_name); ?>
							<a class="ays_help" data-toggle="tooltip" data-html="true" data-placement="top" title="<?php
								echo __("Select the way of arrangement of the results on the result page of the poll.", $this->plugin_name) .
									"<ul style='list-style-type: circle;padding-left: 20px;'>".
                                        "<li>". __('Ascending – the smallest to largest',$this->plugin_name) ."</li>".
                                        "<li>". __('Descending – the largest to smallest',$this->plugin_name) ."</li>".
                                        "<li>". __('None',$this->plugin_name) ."</li>".
                                    "</ul>";
							?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a></label>
                    </div>
                    <div class="col-sm-9">
                        <select name="ays-poll-result-sort-type" id="ays-poll-result-sort-type" class="ays-select">
                            <option value="none" <?= isset($options['result_sort_type']) && $options['result_sort_type'] == "none" ? 'selected' : ''; ?>>
								<?= __("None", $this->plugin_name); ?>
                            </option>
                            <option value="ASC" <?= isset($options['result_sort_type']) && $options['result_sort_type'] == "ASC" ? 'selected' : ''; ?>>
								<?= __("Ascending", $this->plugin_name); ?>
                            </option>
                            <option value="DESC" <?= isset($options['result_sort_type']) && $options['result_sort_type'] == "DESC" ? 'selected' : ''; ?>>
								<?= __("Descending", $this->plugin_name); ?>
                            </option>
                        </select>
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for='show-votes-count'>
                            <?= __('Show votes count', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip"
                               data-placement="top"
                               title="<?= __("Enable to show the total number of votes of each answer on the result page of the poll.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <input type="checkbox" name="show_votes_count" id="show-votes-count"
                               value="1" <?= $showvotescount ? 'checked' : '' ?> >
                    </div>
                </div>
                <hr>

                <!-- Loading effect start -->

                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for='ays-poll-load-effect'>
							<?= __('Loading effect', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip"
                               data-placement="top"
                               title="<?= __("Choose the loading effect of the displaying poll results.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <select name="ays-poll-load-effect" id="ays-poll-load-effect" class="ays-select">
                            <option value="load_gif" <?= isset($options['load_effect']) && $options['load_effect'] == "load_gif" ? 'selected' : ''; ?>>
								<?= __("Loading GIF", $this->plugin_name); ?>
                            </option>
                            <option value="opacity" <?= isset($options['load_effect']) && $options['load_effect'] == "opacity" ? 'selected' : ''; ?>>
								<?= __("Opacity", $this->plugin_name); ?>
                            </option>
                            <option value="blur" <?= isset($options['load_effect']) && $options['load_effect'] == "blur" ? 'selected' : ''; ?>>
								<?= __("Blur", $this->plugin_name); ?>
                            </option>
                            <option value="message" <?= isset($options['load_effect']) && $options['load_effect'] == "message" ? 'selected' : ''; ?>>
								<?= __("Message", $this->plugin_name); ?>
                            </option>
                            <option class="apm-pro-feature" disabled
                                    title="<?= __("It is PRO version feature", $this->plugin_name); ?>" value="pro">
								<?= __("Custom GIF", $this->plugin_name); ?>
                            </option>
                        </select>
                    </div>
                    <div class="if-loading-gif col-sm-6 row">
                        <div class="apm-loader d-flex justify-content-between align-items-center">
                            <input type="radio"
                                   name="ays-poll-load-gif" <?= isset($options['load_effect']) && $options['load_effect'] == "load_gif" && isset($options['load_gif']) && $options['load_gif'] == 'plg_default' ? 'checked' : ''; ?>
                                   id="plg_default" value="plg_default">
                            <label for="plg_default" class="apm-loading-gif">
                                <div class="loader loader--style3">
                                    <svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg"
                                         xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="100%"
                                         height="100%" viewBox="0 0 50 50" style="enable-background:new 0 0 50  50;"
                                         xml:space="preserve">
                                        <path fill="#000"
                                              d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,       0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,  14.615,   6.543,14.615,14.615H43.935z">
                                            <animateTransform attributeType="xml" attributeName="transform"
                                                              type="rotate" from="0 25 25" to="360 25 25" dur="0.7s"
                                                              repeatCount="indefinite"/>
                                        </path>
                                    </svg>
                                </div>
                            </label>
                        </div>
                        <div class="apm-loader d-flex justify-content-between align-items-center">
                            <input type="radio"
                                   name="ays-poll-load-gif" <?= isset($options['load_effect']) && $options['load_effect'] == "load_gif" && isset($options['load_gif']) && $options['load_gif'] == 'plg_1' ? 'checked' : ''; ?>
                                   id="plg_1" value="plg_1">
                            <label for="plg_1" class="apm-loading-gif">
                                <div class="loader loader--style5">
                                    <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                                         xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="100%"
                                         height="100%" viewBox="0 0 24 30" style="enable-background:new 0 0 50 50;"
                                         xml:space="preserve">
                                        <rect x="0" y="0" width="4" height="10" fill="#333">
                                            <animateTransform attributeType="xml" attributeName="transform"
                                                              type="translate" values="0 0; 0 20; 0 0" begin="0"
                                                              dur="0.8s" repeatCount="indefinite"/>
                                        </rect>
                                        <rect x="10" y="0" width="4" height="10" fill="#333">
                                            <animateTransform attributeType="xml" attributeName="transform"
                                                              type="translate" values="0 0; 0 20; 0 0" begin="0.2s"
                                                              dur="0.8s" repeatCount="indefinite"/>
                                        </rect>
                                        <rect x="20" y="0" width="4" height="10" fill="#333">
                                            <animateTransform attributeType="xml" attributeName="transform"
                                                              type="translate" values="0 0; 0 20; 0 0" begin="0.4s"
                                                              dur="0.8s" repeatCount="indefinite"/>
                                        </rect>
                                    </svg>
                                </div>
                            </label>
                        </div>
                        <div class="apm-loader d-flex justify-content-between align-items-center">
                            <input type="radio"
                                   name="ays-poll-load-gif" <?= isset($options['load_effect']) && $options['load_effect'] == "load_gif" && isset($options['load_gif']) && $options['load_gif'] == 'plg_2' ? 'checked' : ''; ?>
                                   id="plg_2" value="plg_2">
                            <label for="plg_2" class="apm-loading-gif">
                                <div class="loader loader--style8">
                                    <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                                         xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="100%"
                                         height="100%" viewBox="0 0 24 30" style="enable-background:new 0 0 50 50;"
                                         xml:space="preserve">
                                        <rect x="0" y="10" width="4" height="10" fill="#333" opacity="0.2">
                                            <animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2"
                                                     begin="0s" dur="0.7s" repeatCount="indefinite"/>
                                            <animate attributeName="height" attributeType="XML" values="10; 20; 10"
                                                     begin="0s" dur="0.7s" repeatCount="indefinite"/>
                                            <animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0s"
                                                     dur="0.7s" repeatCount="indefinite"/>
                                        </rect>
                                        <rect x="8" y="10" width="4" height="10" fill="#333" opacity="0.2">
                                            <animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2"
                                                     begin="0.15s" dur="0.7s" repeatCount="indefinite"/>
                                            <animate attributeName="height" attributeType="XML" values="10; 20; 10"
                                                     begin="0.15s" dur="0.7s" repeatCount="indefinite"/>
                                            <animate attributeName="y" attributeType="XML" values="10; 5; 10"
                                                     begin="0.15s" dur="0.7s" repeatCount="indefinite"/>
                                        </rect>
                                        <rect x="16" y="10" width="4" height="10" fill="#333" opacity="0.2">
                                            <animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2"
                                                     begin="0.3s" dur="0.7s" repeatCount="indefinite"/>
                                            <animate attributeName="height" attributeType="XML" values="10; 20; 10"
                                                     begin="0.3s" dur="0.7s" repeatCount="indefinite"/>
                                            <animate attributeName="y" attributeType="XML" values="10; 5; 10"
                                                     begin="0.3s" dur="0.7s" repeatCount="indefinite"/>
                                        </rect>
                                    </svg>
                                </div>
                            </label>
                        </div>
                        <div class="apm-loader d-flex justify-content-between align-items-center">
                            <input type="radio"
                                   name="ays-poll-load-gif" <?= isset($options['load_effect']) && $options['load_effect'] == "load_gif" && isset($options['load_gif']) && $options['load_gif'] == 'plg_3' ? 'checked' : ''; ?>
                                   id="plg_3" value="plg_3">
                            <label for="plg_3" class="apm-loading-gif">
                                <div class="loader loader--style5">
                                    <svg width="100%" height="100%" viewBox="0 0 105 105"
                                         xmlns="http://www.w3.org/2000/svg" fill="#000">
                                        <circle cx="12.5" cy="12.5" r="12.5">
                                            <animate attributeName="fill-opacity" begin="0s" dur="0.9s" values="1;.2;1"
                                                     calcMode="linear" repeatCount="indefinite"/>
                                        </circle>
                                        <circle cx="12.5" cy="52.5" r="12.5" fill-opacity=".5">
                                            <animate attributeName="fill-opacity" begin="100ms" dur="0.9s"
                                                     values="1;.2;1" calcMode="linear" repeatCount="indefinite"/>
                                        </circle>
                                        <circle cx="52.5" cy="12.5" r="12.5">
                                            <animate attributeName="fill-opacity" begin="300ms" dur="0.9s"
                                                     values="1;.2;1" calcMode="linear" repeatCount="indefinite"/>
                                        </circle>
                                        <circle cx="52.5" cy="52.5" r="12.5">
                                            <animate attributeName="fill-opacity" begin="600ms" dur="0.9s"
                                                     values="1;.2;1" calcMode="linear" repeatCount="indefinite"/>
                                        </circle>
                                        <circle cx="92.5" cy="12.5" r="12.5">
                                            <animate attributeName="fill-opacity" begin="800ms" dur="0.9s"
                                                     values="1;.2;1" calcMode="linear" repeatCount="indefinite"/>
                                        </circle>
                                        <circle cx="92.5" cy="52.5" r="12.5">
                                            <animate attributeName="fill-opacity" begin="400ms" dur="0.9s"
                                                     values="1;.2;1" calcMode="linear" repeatCount="indefinite"/>
                                        </circle>
                                        <circle cx="12.5" cy="92.5" r="12.5">
                                            <animate attributeName="fill-opacity" begin="700ms" dur="0.9s"
                                                     values="1;.2;1" calcMode="linear" repeatCount="indefinite"/>
                                        </circle>
                                        <circle cx="52.5" cy="92.5" r="12.5">
                                            <animate attributeName="fill-opacity" begin="500ms" dur="0.9s"
                                                     values="1;.2;1" calcMode="linear" repeatCount="indefinite"/>
                                        </circle>
                                        <circle cx="92.5" cy="92.5" r="12.5">
                                            <animate attributeName="fill-opacity" begin="200ms" dur="0.9s"
                                                     values="1;.2;1" calcMode="linear" repeatCount="indefinite"/>
                                        </circle>
                                    </svg>
                                </div>
                            </label>
                        </div>
                        <div class="apm-loader d-flex justify-content-between align-items-center">
                            <input type="radio"
                                   name="ays-poll-load-gif" <?= isset($options['load_effect']) && $options['load_effect'] == "load_gif" && isset($options['load_gif']) && $options['load_gif'] == 'plg_4' ? 'checked' : ''; ?>
                                   id="plg_4" value="plg_4">
                            <label for="plg_4" class="apm-loading-gif">
                                <div class="loader loader--style4">
                                    <svg width="100%" height="100%" viewBox="0 0 57 57"
                                         xmlns="http://www.w3.org/2000/svg" stroke="#000">
                                        <g fill="none" fill-rule="evenodd">
                                            <g transform="translate(1 1)" stroke-width="2">
                                                <circle cx="5" cy="50" r="5">
                                                    <animate attributeName="cy" begin="0s" dur="2.2s"
                                                             values="50;5;50;50" calcMode="linear"
                                                             repeatCount="indefinite"/>
                                                    <animate attributeName="cx" begin="0s" dur="2.2s" values="5;27;49;5"
                                                             calcMode="linear" repeatCount="indefinite"/>
                                                </circle>
                                                <circle cx="27" cy="5" r="5">
                                                    <animate attributeName="cy" begin="0s" dur="2.2s" from="5" to="5"
                                                             values="5;50;50;5" calcMode="linear"
                                                             repeatCount="indefinite"/>
                                                    <animate attributeName="cx" begin="0s" dur="2.2s" from="27" to="27"
                                                             values="27;49;5;27" calcMode="linear"
                                                             repeatCount="indefinite"/>
                                                </circle>
                                                <circle cx="49" cy="50" r="5">
                                                    <animate attributeName="cy" begin="0s" dur="2.2s"
                                                             values="50;50;5;50" calcMode="linear"
                                                             repeatCount="indefinite"/>
                                                    <animate attributeName="cx" from="49" to="49" begin="0s" dur="2.2s"
                                                             values="49;5;27;49" calcMode="linear"
                                                             repeatCount="indefinite"/>
                                                </circle>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                            </label>
                        </div>
                        <div class="apm-loader d-flex justify-content-between align-items-center apm-pro-feature">
                            <input type="radio" disabled id="loaderp" value="pro">
                            <label for="loaderp" class="apm-loading-gif"
                                   title="<?= __("It is PRO feature", $this->plugin_name) ?>">
                                <div class="loader loader--style4">
                                    <svg width="100%" height="100%" viewBox="0 0 135 140"
                                         xmlns="http://www.w3.org/2000/svg" fill="#000">
                                        <rect y="10" width="15" height="120" rx="6">
                                            <animate attributeName="height" begin="0.5s" dur="1s"
                                                     values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
                                                     repeatCount="indefinite"/>
                                            <animate attributeName="y" begin="0.5s" dur="1s"
                                                     values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
                                                     repeatCount="indefinite"/>
                                        </rect>
                                        <rect x="30" y="10" width="15" height="120" rx="6">
                                            <animate attributeName="height" begin="0.25s" dur="1s"
                                                     values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
                                                     repeatCount="indefinite"/>
                                            <animate attributeName="y" begin="0.25s" dur="1s"
                                                     values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
                                                     repeatCount="indefinite"/>
                                        </rect>
                                        <rect x="60" width="15" height="140" rx="6">
                                            <animate attributeName="height" begin="0s" dur="1s"
                                                     values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
                                                     repeatCount="indefinite"/>
                                            <animate attributeName="y" begin="0s" dur="1s"
                                                     values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
                                                     repeatCount="indefinite"/>
                                        </rect>
                                        <rect x="90" y="10" width="15" height="120" rx="6">
                                            <animate attributeName="height" begin="0.25s" dur="1s"
                                                     values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
                                                     repeatCount="indefinite"/>
                                            <animate attributeName="y" begin="0.25s" dur="1s"
                                                     values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
                                                     repeatCount="indefinite"/>
                                        </rect>
                                        <rect x="120" y="10" width="15" height="120" rx="6">
                                            <animate attributeName="height" begin="0.5s" dur="1s"
                                                     values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
                                                     repeatCount="indefinite"/>
                                            <animate attributeName="y" begin="0.5s" dur="1s"
                                                     values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
                                                     repeatCount="indefinite"/>
                                        </rect>
                                    </svg>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="if-loading-message col-sm-6 row">
                        <input type="text" class="ays-text-input ays-text-input-short" name="ays_poll_load_effect_message" value="<?php echo $poll_effect_message; ?>">
                    </div>
                </div>                
                <hr class="<?php echo $poll_loader_size_line_enable; ?> ays_line_changeing">
                <div class="form-group row <?php echo $poll_loader_size_enable; ?> ays_load_gif_cont">
                    <div class="col-sm-3">
                            <label for="ays_loader_font_size">
                                <?= __('Loading effect size', $this->plugin_name) ?>
                                <a class="ays_help" data-toggle="tooltip"
                                title="<?= __('Define the size of the loading effect.It will get the default value if you leave it blank.', $this->plugin_name) ?>">
                                    <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                </a>
                            </label>
                        </div>
                        <div class="col-sm-9">
                            <input type="number" class="ays-text-input ays-poll-answer-results-short-input" id="ays_loader_font_size"
                                name="ays_loader_font_size"
                                value="<?= $poll_loader_font_size; ?>"/>
                    </div>
                </div>
                <hr>
                <!-- Loading effect end -->
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="ays_enable_restart_button">
							<?= __('Enable restart button', $this->plugin_name) ?>
                            <a class="ays_help" data-toggle="tooltip"
                               title="<?= __('Show the restart button on the result page of the poll for restarting the poll and taking it again.', $this->plugin_name) ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" class="ays-enable-timer1" id="ays_enable_restart_button"
                               name="ays_enable_restart_button"
                               value="on" <?= (isset($options['enable_restart_button']) && $options['enable_restart_button']) ? 'checked' : '' ?> />
                    </div>
                </div>
                <hr>
                <div class="form-group row ays_toggle_parent">
                    <div class="col-sm-3">
                        <label for='ays-poll-show-social'>
							<?= __('Social share buttons', $this->plugin_name); ?>
							<a 	class="ays_help"
								data-toggle="tooltip"
								data-placement="top"
								title='<?= __("Enable to show social media share buttons on the result page of the poll. (Facebook, Twitter, Linkedin)", $this->plugin_name); ?>'>
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a></label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" name="ays-poll-show-social" class="ays_toggle_checkbox" id="ays-poll-show-social"
                               value="show" <?= $poll_social_buttons ? 'checked' : ''; ?>>
                    </div>
                    <div class="col-sm-8 ays_toggle_target ays_divider_left <?php echo $poll_social_buttons ? '' : 'display_none'; ?>">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label for="ays_poll_enable_linkedin_share_button">
                                    <i class="ays_poll_fas ays_poll-fa-linkedin"></i>
                                    <?php echo __('Linkedin button',$this->plugin_name)?>
                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Display LinkedIn social button so that the users can share the page on which your poll is posted.',$this->plugin_name); ?>">
                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                    </a>
                                </label>
                            </div>
                            <div class="col-sm-8">
                                <input type="checkbox" class="ays-enable-timer1" id="ays_poll_enable_linkedin_share_button" name="ays_poll_enable_linkedin_share_button" value="on" <?php echo ( $poll_show_social_ln ) ? "checked" : ""; ?>/>
                            </div>
                        </div>
                        <hr/>
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label for="ays_poll_enable_facebook_share_button">
                                    <i class="ays_poll_fas ays_poll_facebook"></i>
                                    <?php echo __('Facebook button',$this->plugin_name)?>
                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Display Facebook social button so that the users can share the page on which your poll is posted.',$this->plugin_name); ?>">
                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                    </a>
                                </label>
                            </div>
                            <div class="col-sm-8">
                                <input type="checkbox" class="ays-enable-timer1" id="ays_poll_enable_facebook_share_button" name="ays_poll_enable_facebook_share_button" value="on" <?php echo ( $poll_show_social_fb ) ? "checked" : ""; ?>/>
                            </div>
                        </div>
                        <hr/>
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label for="ays_poll_enable_twitter_share_button">
                                    <i class="ays_fa ays_fa_twitter_square"></i>
                                    <?php echo __('Twitter button',$this->plugin_name)?>
                                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Display Twitter social button so that the users can share the page on which your poll is posted.',$this->plugin_name); ?>">
                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                    </a>
                                </label>
                            </div>
                            <div class="col-sm-8">
                                <input type="checkbox" class="ays-enable-timer1" id="ays_poll_enable_twitter_share_button" name="ays_poll_enable_twitter_share_button" value="on" <?php echo ( $poll_show_social_tr ) ? "checked" : ""; ?>/>
                            </div>
                        </div>
                        <!-- <hr/> -->
                        <!-- <div class="form-group row">
                            <div class="col-sm-4">
                                <label for="ays_survey_enable_vkontakte_share_button">
                                    <i class="ays_fa ays_fa_vk"></i>
                                    <?php //echo __('VKontakte button',$this->plugin_name)?>
                                    <a class="ays_help" data-toggle="tooltip" title="<?php //echo __('Display VKontakte social button so that the users can share the page on which your survey is posted.',$this->plugin_name); ?>">
                                        <i class="ays_fa ays_fa_info_circle"></i>
                                    </a>
                                </label>
                            </div>
                            <div class="col-sm-8">
                                <input type="checkbox" class="ays-enable-timer1" id="ays_survey_enable_vkontakte_share_button" name="ays_survey_enable_vkontakte_share_button" value="on" <?php //echo ( $survey_social_button_vk ); ?>/>
                            </div>
                        </div> -->
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for='ays-poll-res-rgba'>
							<?= __('Results bar in RGBA', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip"
                               data-placement="top"
                               title="<?= __("If the option is enabled, the opacity of the result bar color will depend on the number of votes.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <input type="checkbox" name="ays-poll-res-rgba" id="ays-poll-res-rgba"
                               value="on" <?php echo  ($result_in_rgba) ? 'checked' : ''; ?>>
                    </div>
                </div>
                <hr>
                <div class="form-group row ays_toggle_parent">
                    <div class="col-sm-3">
                        <label for='ays_poll_show_users'>
							<?= __('Show passed users avatars', $this->plugin_name); ?>
                            <a class="ays_help" data-toggle="tooltip"
                               data-placement="top"
                               title="<?= __("If this option is enabled, you will see users' avatars(profile pictures) who have already voted on the result page.", $this->plugin_name); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" name="ays_poll_show_users" id="ays_poll_show_users" class="ays_toggle_checkbox"
                               value="on" <?php echo $poll_show_passed_users_checked; ?>>
                    </div>
                    <div class="col-sm-8 <?php echo $poll_show_passed_users_checked == "checked" ? "" : "display_none";?> ays_toggle_target">
                        <div class="row">
                            <div class="col-sm-3 ays_divider_left">
                                <label for='ays_poll_show_users_count'>
                                    <?= __('Users count', $this->plugin_name); ?>
                                    <a class="ays_help" data-toggle="tooltip"
                                    data-placement="top"
                                    title="<?= __("Indicate the count of users' avatars to be shown.", $this->plugin_name); ?>">
                                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                    </a>
                                </label>
                            </div>
                            <div class="col-sm-9">
                                <input type="number"
                                    name="ays_poll_show_users_count" 
                                    id="ays_poll_show_users_count" 
                                    class="ays-text-input ays-poll-answer-results-short-input"
                                    value="<?php echo $poll_show_passed_users_count; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="form-group row ays_toggle_parent">
                    <div class="col-sm-3">
                        <label for="ays_poll_enable_answer_redirect_delay">
                            <?= __('Answer Redirect Delay', $this->plugin_name) ?>
                            <a class="ays_help" data-toggle="tooltip"
                            title="<?= __('Choose the redirection delay in seconds after the user votes the poll. The option works if you have enabled the redirection of each answer individually from the General tab.', $this->plugin_name) ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" id="ays_poll_enable_answer_redirect_delay" name="ays_poll_enable_answer_redirect_delay" class="ays_toggle_checkbox" value="on" <?php echo ($poll_enable_answer_redirect_delay) ? "checked" : ""; ?>>
                    </div>
                    <div class="col-sm-8 ays_toggle_target <?php echo ($poll_enable_answer_redirect_delay) ? "" : "display_none"; ?>">
                        <input type="number"
                               class="ays-text-input ays-poll-answer-results-short-input"
                               id="ays_poll_answer_redirect_delay"
                               name="ays_poll_answer_redirect_delay"
                               value="<?= $poll_every_answer_redirect_delay; ?>"/>
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="ays_poll_enable_answer_image_after_voting">
                            <?= __('Show answers image', $this->plugin_name) ?>
                            <a class="ays_help" data-toggle="tooltip"
                            title="<?= __('Tick this option to see the answers images after voting. Note it works only with the choosing type and you should have at least one answer with an image to use it.', $this->plugin_name) ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </label>
                    </div>
                    <div class="col-sm-1">
                        <input type="checkbox" id="ays_poll_enable_answer_image_after_voting" name="ays_poll_enable_answer_image_after_voting" value="on" <?php echo ($poll_enable_answer_image_after_voting) ? "checked" : ""; ?>>
                    </div>
                </div>
                <hr>
                <div class="col-sm-12" style="padding:20px 10px 50px;">
                    <div class="pro_features" style="justify-content:flex-end;">
                        <div style="margin-right:20px;">
                            <p style="font-size:20px;">
                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                            </p>
                        </div>
                    </div>
	                <div class="form-group row">
	                    <div class="col-3">
	                        <label for="apm_allow_add_answers">
								<?= __('Show results by', $this->plugin_name); ?>
	                            <a class="ays_help" data-toggle="tooltip" data-placement="top"
	                               title="<?= __("Select the way of displaying the results on the result page of the poll.", $this->plugin_name); ?>">
	                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
	                            </a>
	                        </label>
	                    </div>
	                    <div class="col-sm-9">
	                       <div class="d-flex">
	                            <div class="form-check form-check-inline ays_poll_loader">
	                                <input type="radio" checked/>
	                                <label class="form-check-label ays_poll_check_label">
                                        <?= __('Bar chart', $this->plugin_name); ?>
                                    </label>
	                            </div>
	                            <div class="form-check form-check-inline ays_poll_loader">
	                                <input type="radio"/>
	                                <label class="form-check-label ays_poll_check_label"> 
                                        <?= __('Pie Chart', $this->plugin_name); ?>
                                    </label>
                                </div>
                                <div class="form-check form-check-inline ays_poll_loader">
                                    <input type="radio"/>
                                    <label class="form-check-label ays_poll_check_label">
                                         <?= __('Column Chart', $this->plugin_name); ?> 
                                    </label>
                                </div>
                                <div class="form-check form-check-inline ays_poll_loader if_versus_type">
                                    <input type="radio"/>
                                    <label class="form-check-label ays_poll_check_label">
                                         <?= __('Versus Chart', $this->plugin_name); ?>
                                    </label>
                                </div>
                                <div class="form-check form-check-inline ays_poll_loader if_rating_type" style="<?php echo $poll['view_type'] == 'star' ? '' : 'display: none'?>">
                                    <input type="radio"/>
                                    <label class="form-check-label ays_poll_check_label" for="ays_poll_show_res_rating_chart">
                                         <?= __('Rating Chart', $this->plugin_name); ?>
                                    </label>
                                </div>
	                        </div>
	                    </div>	                    
	                </div>
                </div>
                <hr/>
                <div class="col-sm-12" style="padding:30px;">
                    <div class="pro_features" style="justify-content:flex-end;">
                        <div>
                            <p style="font-size:15px;">
                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                            </p>
                        </div>
                        <div style="position: absolute; top: 15px;">
                            <p style="font-size:15px;">
                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                            </p>
                        </div>
                    </div>
                    <p class="ays-subtitle ays-poll-subtitle-button"><?php echo __("Result message based on the answer" , $this->plugin_name); ?></p> 
                    <hr>
	                <div class="form-group row">
	                    <div class="col-sm-3">
	                        <label for="ays_show_answer_message">
	                            <?php echo __('Show Answer message',$this->plugin_name)?>
	                            <a class="ays_help" data-toggle="tooltip"
	                               data-placement="top"
	                               title="<?= __("Show different messages based on the answer.", $this->plugin_name); ?>">
	                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
	                            </a>
	                        </label>
	                    </div>
	                    <div class="col-sm-9">
	                        <input type="checkbox" class="ays-enable-timer1" id="ays_show_answer_message"
	                        name=""
	                        value="on">
	                    </div>
	                </div> 
	                <hr/>
	                <!--Result message -->
	                <div class='form-group row'>
	                	<div class="ays_reset_answers_div">
	                     	<input type="button" name="ays_reset_answers" id="ays_reset_answers" class="button ays-button" value="Reset answers">
	                 	</div>
	                     <a class="ays_help" data-toggle="tooltip" data-placement="top" title="<?= __("RRefresh the below answers table after adding or removing answer(s) in the General tab.", $this->plugin_name); ?>">
	                         <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
	                     </a>
	                 </div>
	                <hr>
	                <?php
	                    $content = '<div class="ays-field-dashboard ays-table-wrap">                            
	                                    <table class="ays-answers-table ">
	                                        <thead>
	                                            <tr class="ui-state-default">
	                                                <th>'.__('Answers' , $this->plugin_name).'</th>
	                                                <th>'.__('Text' , $this->plugin_name).'</th>                                                     
	                                                <th>'.__('Image' , $this->plugin_name).'</th>
	                                            </tr>
	                                        </thead>
	                                        <tbody>';	                        
	                        $content .= '<tr class="ays-interval-row">
	                                        <td>
	                                            <span>'.__("Answer 1", $this->plugin_name).'</span>
	                                            <input type="hidden" name="ays_answer_id[]">
	                                        </td>
	                                        <td>
	                                            <textarea type="text" name="ays_answer_message[]" class="interval__text"></textarea>
	                                        </td>
	                                        <td class="ays-answer-image-td">
	                                            <label class="ays-label" for="ays-answer">
	                                                <a href="javascript:void(0)" class="add-answer-image" >
	                                                    '.__("Add" , $this->plugin_name).'
	                                                </a>
	                                            </label>
	                                            <div class="ays-answer-image-container ays-interval-image-container">
	                                                <span class="ays-remove-answer-img"></span>
	                                                <img src="" class="ays-answer-img">
	                                                <input type="hidden" name="interval_image[]" class="ays-answer-image"
	                                                    >
	                                            </div>
	                                        </td>
	                                    </tr>
	                                    <tr class="ays-interval-row">
	                                        <td>
	                                            <span>'.__("Answer 2", $this->plugin_name).'</span>
	                                            <input type="hidden" name="ays_answer_id[]">
	                                        </td>
	                                        <td>
	                                            <textarea type="text" name="ays_answer_message[]" class="interval__text"></textarea>
	                                        </td>
	                                        <td class="ays-answer-image-td">
	                                            <label class="ays-label" for="ays-answer">
	                                                <a href="javascript:void(0)" class="add-answer-image" >
	                                                    '.__("Add" , $this->plugin_name).'
	                                                </a>
	                                            </label>
	                                            <div class="ays-answer-image-container ays-interval-image-container">
	                                                <span class="ays-remove-answer-img"></span>
	                                                <img src="" class="ays-answer-img">
	                                                <input type="hidden" name="interval_image[]" class="ays-answer-image"
	                                                    >
	                                            </div>
	                                        </td>
	                                    </tr>';
	                    $content .= '</tbody></table></div>';
	                    echo $content;	                
	                ?>
                </div>
                <hr/>
            </div>
            <div class="ays_save_buttons_content">
                <h1>
                    <?php
                    wp_nonce_field('poll_action', 'poll_action');
                    $save_close_bottom_attributes = array('id' => 'ays-button');
                    $save_bottom_attributes = array('id' => 'ays-button-apply');
                    submit_button(__('Save and close', $this->plugin_name), 'primary', 'ays_submit', false, $save_close_bottom_attributes);
                    submit_button(__('Save', $this->plugin_name), '', 'ays_apply', false, $save_bottom_attributes);
                    echo $loader_iamge;
                    ?>
                </h1>
                <div class="ays_save_default_button_box">
                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __( "Saves the assigned settings of the current poll as default. After clicking on this button, each time creating a new poll, the system will take the settings and styles of the current poll. If you want to change and renew it, please click on this button on another poll." , $this->plugin_name ); ?>">
                        <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                    </a>
                    <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="This property aviable only in pro version">
                        <input type="button" class="button ays_default_btn ays-loader-banner" value="Save as default">
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>