<?php
$actions = $this->settings_obj;
$poll_actions = $this->polls_obj;

if (isset($_REQUEST['ays_submit'])) {
	$actions->store_data($_REQUEST);
}
if (isset($_GET['ays_poll_tab'])) {
	$ays_poll_tab = sanitize_text_field($_GET['ays_poll_tab']);
} else {
	$ays_poll_tab = 'tab1';
}
$db_data = $actions->get_db_data();

global $wp_roles;
$ays_users_roles = $wp_roles->role_names;

$mailchimp_res      = ($actions->ays_get_setting('mailchimp') === false) ? json_encode(array()) : $actions->ays_get_setting('mailchimp');
$mailchimp          = json_decode($mailchimp_res, true);
$mailchimp_username = isset($mailchimp['username']) ? $mailchimp['username'] : '';
$mailchimp_api_key  = isset($mailchimp['apiKey']) ? $mailchimp['apiKey'] : '';

$option_res         = ($actions->ays_get_setting('options') === false) ? json_encode(array()) : $actions->ays_get_setting('options');
$options            = json_decode($option_res, true);

$answers_sound = isset($options['answers_sound']) ? $options['answers_sound'] : '';

 //Poll title length
$poll_title_length = (isset($options['poll_title_length']) && intval($options['poll_title_length']) != 0) ? absint(intval($options['poll_title_length'])) : 5;

// Default Category for poll
$ays_default_cat =  isset($options['default_category']) && !empty($options['default_category']) ? explode("," , $options['default_category']) : array("1");

$poll_get_all_cats = $poll_actions->get_categories();

// Poll Types
$poll_default_types = array(
                        "choosing" => "Choosing",
                        "rating"   => "Rating",
                        "voting"   => "Voting",
                        "text"     => "Text"
                    );
$ays_default_type =  isset($options['default_type']) && $options['default_type'] != "" ? esc_attr($options['default_type']) : "choosing";

// Poll expired and unpublished message
$all_shortcode_message = isset($options['all_shortcode_message'])  && $options['all_shortcode_message'] != '' ? esc_attr($options['all_shortcode_message']) : '';

// Show result view
$poll_show_result_view = isset($options['show_result_view']) ? $options['show_result_view'] : 'standart';

// Animation Top 
$poll_animation_top = (isset($options['poll_animation_top']) && $options['poll_animation_top'] != '') ? absint(intval($options['poll_animation_top'])) : 100 ;
$options['poll_enable_animation_top'] = isset($options['poll_enable_animation_top']) ? $options['poll_enable_animation_top'] : 'on';
$poll_enable_animation_top = (isset($options['poll_enable_animation_top']) && $options['poll_enable_animation_top'] == "on") ? true : false;
//User History Page shortcode
$default_ays_poll_user_page_columns = array(
    'poll_name'  => 'Poll name',
    'vote_date'  => 'Vote Date',
    'vote_answer'=> 'Vote Answer'
);

// Fields placeholders | Start

$fields_placeholders_res      = ($actions->ays_get_setting('fields_placeholders') === false) ? json_encode(array()) : $actions->ays_get_setting('fields_placeholders');
$fields_placeholders          = json_decode( stripcslashes( $fields_placeholders_res ) , true);

$poll_fields_placeholder_name  = (isset($fields_placeholders['poll_fields_placeholder_name']) && $fields_placeholders['poll_fields_placeholder_name'] != '') ? stripslashes( esc_attr( $fields_placeholders['poll_fields_placeholder_name'] ) ) : 'Name';

$poll_fields_placeholder_email = (isset($fields_placeholders['poll_fields_placeholder_email']) && $fields_placeholders['poll_fields_placeholder_email'] != '') ? stripslashes( esc_attr( $fields_placeholders['poll_fields_placeholder_email'] ) ) : 'E-mail';

$poll_fields_placeholder_phone = (isset($fields_placeholders['poll_fields_placeholder_phone']) && $fields_placeholders['poll_fields_placeholder_phone'] != '') ? stripslashes( esc_attr( $fields_placeholders['poll_fields_placeholder_phone'] ) ) : 'Phone';

// Fields placeholders | End


?>
<div class="wrap" style="position:relative;">
    <div class="container-fluid">
        <form method="post">
            <input type="hidden" name="ays_poll_tab" value="<?php echo htmlentities($ays_poll_tab); ?>" id="ays_poll_active_tab_settings">
            <h1 class="wp-heading-inline">
				<?php
				echo __('Settings', $this->plugin_name);
				?>
            </h1>
			<?php
			if (isset($_REQUEST['status'])) {
                $actions->poll_settings_notices( sanitize_text_field( $_REQUEST['status'] ) );
			}
			?>
            <hr/>
            <div class="ays-settings-wrapper">
                <div>
                    <div class="nav-tab-wrapper" style="position:sticky; top:35px;">
                        <a href="#tab1" data-tab="tab1"
                           class="nav-tab <?php echo ($ays_poll_tab == 'tab1') ? 'nav-tab-active' : ''; ?>">
							<?php echo __("General", $this->plugin_name); ?>
                        </a>
                        <a href="#tab2" data-tab="tab2"
                           class="nav-tab <?php echo ($ays_poll_tab == 'tab2') ? 'nav-tab-active' : ''; ?>">
							<?php echo __("Integrations", $this->plugin_name); ?>
                        </a>
                        <a href="#tab3" data-tab="tab3"
                           class="nav-tab <?php echo ($ays_poll_tab == 'tab3') ? 'nav-tab-active' : ''; ?>">
                            <?php echo __("Shortcodes", $this->plugin_name); ?>
                        </a>
                        <a href="#tab4" data-tab="tab4"
                           class="nav-tab <?php echo ($ays_poll_tab == 'tab4') ? 'nav-tab-active' : ''; ?>">
                            <?php echo __("Fields placeholders", $this->plugin_name); ?>
                        </a>
                    </div>
                </div>
                <div class="ays-poll-tabs-wrapper">
                    <div id="tab1"
                         class="ays-poll-tab-content <?php echo ($ays_poll_tab == 'tab1') ? 'ays-poll-tab-content-active' : ''; ?>">
                        <p class="ays-subtitle"><?php echo __('General Settings', $this->plugin_name) ?></p>
                        <hr/>
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;"><i class="ays_fa ays_poll_fa_question_circle"></i></strong>
                                <h5><?php echo __('Default parameters for Poll',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_disable_ip_storing">
                                        <?= __('Disable IP Storing', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip"
                                           data-placement="top"
                                           title="<?= __("After enabling this option, the IP address of the users will not be stored in the database.  Note: If this option is enabled, then the Limitation options connected with IP will not work.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="checkbox"
                                           name="ays_disable_ip_storing"
                                           id="ays_disable_ip_storing"
                                           value="on" <?php echo (isset($options['disable_ip_storing']) && $options['disable_ip_storing'] == 'on') ? 'checked' : ''; ?>
                                    >
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_answer_default_count">
                                        <?= __('Answer Default Count', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip"
                                           data-placement="top"
                                           title="<?= __("Define the default count for the answers which will be displayed in the Add new poll page. (This will work only with choosing type.)", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="number"
                                           class="ays-text-input"
                                           name="ays_answer_default_count"
                                           min="2"
                                           id="ays_answer_default_count"
                                           value="<?php echo isset($options['answer_default_count']) ? $options['answer_default_count'] : 2; ?>"
                                    >
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_default_cat">
                                        <?= __('Polls default category', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip"
                                           data-placement="top"
                                           title="<?= __("Choose the category of the poll which will be selected by default each time you create a poll by the Add New button.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <select id="ays_poll_default_cat" name="ays_poll_default_cat[]" multiple>
                                            <?php
                                                $cat_content = "";
                                                $cat_selected = "";
                                                foreach($poll_get_all_cats as $key => $value){
                                                    $cat_id       = isset($value['id']) && $value['id'] != "" ? esc_attr($value['id']) : "";
                                                    $cat_title    = isset($value['title']) && $value['title'] != "" ? esc_attr($value['title']) : "";
                                                    $cat_selected = in_array( $cat_id , $ays_default_cat) ? "selected" : "";
                                                    $cat_content .= "<option value=".$cat_id." ".$cat_selected.">".$cat_title."</option>";
                                                }
                                                echo $cat_content;
                                            ?>
                                    </select>
                                </div>
                            </div> <!-- Default Category -->
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_default_type">
                                        <?= __('Polls default type', $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip"
                                           data-placement="top"
                                           title="<?= __("Choose the type of the poll which will be selected by default each time you create a poll by the Add New button.", $this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <select id="ays_poll_default_type" name="ays_poll_default_type">
                                            <?php
                                                $type_content = "";
                                                $type_selected = "";
                                                foreach($poll_default_types as $type_key => $type_value){
                                                    $type_id       = isset($type_key) && $type_key != "" ? $type_key : "";
                                                    $type_title    = isset($type_value) && $type_value != "" ? $type_value : "";
                                                    $type_selected = ($ays_default_type == $type_id) ? "selected" : "";
                                                    $type_content .= "<option value=".$type_id." ".$type_selected.">".$type_title."</option>";
                                                }
                                                echo $type_content;
                                            ?>
                                    </select>
                                </div>
                            </div> <!-- Default Type -->
                        </fieldset>
                        <hr/>
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;"><i class="ays_poll_fa ays_poll_fa_music"></i></strong>
                                <h5><?php echo __('Poll answers sound',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_questions_default_type">
                                        <?php echo __( "Sound for answers", $this->plugin_name ); ?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('The selected sound will be played in the poll if the Enable answers sound option is enabled from the particular poll settings.',$this->plugin_name); ?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <div class="ays-bg-music-container">
                                        <a class="add-poll-bg-music" href="javascript:void(0);"><?php echo __("Select sound", $this->plugin_name); ?></a>
                                        <audio controls src="<?php echo $answers_sound; ?>"></audio>
                                        <input type="hidden" name="ays_poll_answers_sound" class="ays_poll_bg_music" value="<?php echo $answers_sound; ?>">
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <hr/>
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;"><i class="ays_poll_fas ays_poll_fa_text"></i></strong>
                                <h5><?php echo __('Excerpt words count in list tables',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_title_length">
                                        <?php echo __( "Polls list table", $this->plugin_name ); ?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Determine the length of the polls to be shown in the Polls List Table by putting your preferred count of words in the following field. For example: if you put 10,  you will see the first 10 words of each poll in the Polls page of your dashboard.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="number" name="ays_poll_title_length" id="ays_poll_title_length" class="ays-text-input" value="<?php echo $poll_title_length; ?>">
                                </div>
                            </div>
                        </fieldset>
                        <hr>
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;"><i class="ays_poll_fa ays_poll_fa_trash"></i></strong>
                                <h5><?php echo __('Erase Poll data',$this->plugin_name)?></h5>
                            </legend>
                            <?php if( isset( $_GET['del_stat'] ) ): ?>
                            <blockquote style="border-color:#46b450;background: rgba(70, 180, 80, 0.2);">
                                <?php echo __("Results up to a ".sanitize_text_field($_GET['mcount'])." month ago deleted successfully.", $this->plugin_name); ?>
                            </blockquote>
                            <hr>
                            <?php endif; ?>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_delete_results_by">
                                        <?php echo __( "Delete results older then 'X' the month", $this->plugin_name ); ?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Specify count of months and save changes. Attention! it will remove submissions older than specified months permanently.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-4">
                                    <input type="number" name="ays_delete_results_by" id="ays_delete_results_by" class="ays-text-input">
                                </div>
                                <div class="col-sm-4">
                                    <input type="submit" name="ays_submit" id="ays_submit" class="button button-primary ays-button" value="Apply changes">
                                </div>
                            </div>
                        </fieldset>
                        <hr/>
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;"><i class="ays_poll_fas ays_poll_fa-code"></i></strong>
                                <h5><?php echo __('Animation Top',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_enable_animation_top">
                                        <?php echo __( "Enable animation", $this->plugin_name ); ?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Enable animation of the scroll offset of the poll container. It works when the poll container is visible on the screen partly and the user starts the poll and moves from one question to another.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="checkbox" name="ays_poll_enable_animation_top" id="ays_poll_enable_animation_top" value="on" <?php echo $poll_enable_animation_top ? 'checked' : ''; ?>>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_animation_top">
                                        <?php echo __( "Scroll offset(px)", $this->plugin_name ); ?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Define the scroll offset of the poll container after the animation starts. It works when the poll container is visible on the screen partly and the user starts the poll and moves from one question to another.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="number" name="ays_poll_animation_top" id="ays_poll_animation_top" class="ays-text-input" value="<?php echo $poll_animation_top; ?>">
                                </div>
                            </div>                            
                        </fieldset> <!-- Animation Top -->
                        <hr/>
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;"><i class="ays_poll_fa ays_poll_fa_globe"></i></strong>
                                <h5><?php echo __('Who will have permission to Poll menu',$this->plugin_name)?></h5>
                            </legend>
                            <div class="col-sm-12" style="padding:20px;">
                                <div class="pro_features" style="">
                                    <div style="margin-right:20px;">
                                        <p style="font-size:20px;">
                                            <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                            <a href="https://ays-pro.com/wordpress/poll-maker/" target="_blank" title="Developer package"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                        </p>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_user_roles">
                                            <?php echo __( "Select user role", $this->plugin_name ); ?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Control and manage who can have access to the plugin from the dashboard.',$this->plugin_name)?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select disabled name="ays_user_roles_poll[]" id="ays_user_roles_poll" class="ays_select2_pro_disabled" multiple>
                                            <option selected value="admin">Administrator</option>
                                        </select>
                                    </div>
                                    <blockquote>
                                        <?php echo __( "Ability to manage Poll Maker plugin only for selected user roles.", $this->plugin_name ); ?>
                                    </blockquote>
                                </div>
                            </div>
                        </fieldset>
                        <hr/>
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;"><i class="ays_poll_fas ays_poll_fa-check"></i></strong>
                                <h5><?php echo __("Auto approve user's request",$this->plugin_name)?></h5>
                            </legend>
                            <div class="col-sm-12" style="padding:20px;">
                                <div class="pro_features" style="">
                                    <div style="margin-right:20px;">
                                        <p style="font-size:20px;">
                                            <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                            <a href="https://ays-pro.com/wordpress/poll-maker/" target="_blank" title="Developer package"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                        </p>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_poll_auto_approve">
                                            <?php echo __( "Enable auto-approve", $this->plugin_name ); ?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('If the option is enabled, the user requests from the Request Form shortcode will automatically be approved and added to the Polls page.',$this->plugin_name); ?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="ays-poll-auto-approve">                                        
                                            <input type="checkbox" >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div id="tab2"
                         class="ays-poll-tab-content <?php echo ($ays_poll_tab == 'tab2') ? 'ays-poll-tab-content-active' : ''; ?>">
                        <p class="ays-subtitle"><?php echo __('Integrations', $this->plugin_name) ?></p>
                        <hr/>
                        <fieldset>
                            <legend>
                                <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/mailchimp_logo.png" alt="">
                                <h5><?php echo __('MailChimp',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <div class="form-group row" aria-describedby="aaa">
                                        <div class="col-sm-3">
                                            <label for="ays_mailchimp_username">
                                                <?php echo __('MailChimp Username',$this->plugin_name)?>
                                            </label>
                                        </div>
                                        <div class="col-sm-9">
                                            <input type="text"
                                                   class="ays-text-input"
                                                   id="ays_mailchimp_username"
                                                   name="ays_mailchimp_username"
                                                   value="<?php echo $mailchimp_username; ?>"
                                            />
                                        </div>
                                    </div>
                                    <hr/>
                                    <div class="form-group row" aria-describedby="aaa">
                                        <div class="col-sm-3">
                                            <label for="ays_mailchimp_api_key">
                                                <?php echo __('MailChimp API Key',$this->plugin_name)?>
                                            </label>
                                        </div>
                                        <div class="col-sm-9">
                                            <input type="text"
                                                   class="ays-text-input"
                                                   id="ays_mailchimp_api_key"
                                                   name="ays_mailchimp_api_key"
                                                   value="<?php echo $mailchimp_api_key; ?>"
                                            />
                                        </div>
                                    </div>
                                    <blockquote>
                                        <?php echo sprintf( __( "You can get your API key from your ", $this->plugin_name ) . "<a href='%s' target='_blank'> %s.</a>", "https://us20.admin.mailchimp.com/account/api/", __( "Account Extras menu", $this->plugin_name ) ); ?>
                                    </blockquote>
                                </div>
                            </div>
                        </fieldset><!-- Mailchimp integration -->
                        <hr/>
                        <fieldset>
                            <legend>
                                <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/campaignmonitor_logo.png" alt="">
                                <h5><?php echo __('Campaign Monitor',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row" style="padding:0;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="pro_features" style="justify-content:flex-end;">
                                        <div style="margin-right:20px;">
                                            <p style="font-size:20px;">
                                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="DEVELOPER feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <div class="form-group row" aria-describedby="aaa">
                                                <div class="col-sm-3">
                                                    <label for="ays_monitor_client">
                                                        Campaign Monitor <?= __('Client ID', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <input type="text"
                                                           class="ays-text-input"
                                                           id="ays_monitor_client"
                                                           name="ays_monitor_client"
                                                           value=""
                                                    >
                                                </div>
                                            </div>
                                            <hr/>
                                            <div class="form-group row" aria-describedby="aaa">
                                                <div class="col-sm-3">
                                                    <label for="ays_monitor_api_key">
                                                        Campaign Monitor <?= __('API Key', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <input type="text"
                                                           class="ays-text-input"
                                                           id="ays_monitor_api_key"
                                                           name="ays_monitor_api_key"
                                                           value=""
                                                    >
                                                </div>
                                            </div>
                                            <blockquote>
                                                <?= __("You can get your API key and Client ID from your Account Settings page", $this->plugin_name); ?>
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset><!-- Campaign monitor integration PRO version!!! -->
                        <hr/>
                        <fieldset>
                            <legend>
                                <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/zapier_logo.png" alt="">
                                <h5><?php echo __('Zapier',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row" style="padding:0;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="pro_features" style="justify-content:flex-end;">
                                        <div style="margin-right:20px;">
                                            <p style="font-size:20px;">
                                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="DEVELOPER feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <div class="form-group row" aria-describedby="aaa">
                                                <div class="col-sm-3">
                                                    <label for="ays_zapier_hook">
                                                        <?= __('Zapier Webhook URL', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <input type="text"
                                                           class="ays-text-input"
                                                           id="ays_zapier_hook"
                                                           name="ays_zapier_hook"
                                                           value=""
                                                    >
                                                </div>
                                            </div>
                                            <blockquote>
                                                <?php echo sprintf(__("If you do not have any ZAP created, go " . "<a href='%s' target='_blank'>%s</a>" . ". Remember to choose Webhooks by Zapier as Trigger App.", $this->plugin_name), "https://zapier.com/app/editor/", "here"); ?>
                                            </blockquote>
                                            <blockquote>
                                                <?= __("We will send you all data from poll information form with \"AysPoll\" key by POST method", $this->plugin_name); ?>
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset> <!-- Zapier integration PRO version!!! -->
                        <hr/>
                        <fieldset>
                            <legend>
                                <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/activecampaign_logo.png" alt="">
                                <h5><?php echo __('ActiveCampaign',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row" style="padding:0;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="pro_features" style="justify-content:flex-end;">
                                        <div style="margin-right:20px;">
                                            <p style="font-size:20px;">
                                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="DEVELOPER feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <div class="form-group row" aria-describedby="aaa">
                                                <div class="col-sm-3">
                                                    <label for="ays_active_camp_url">
                                                        <?= __('API Access URL', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <input type="text"
                                                           class="ays-text-input"
                                                           id="ays_active_camp_url"
                                                           name="ays_active_camp_url"
                                                           value=""
                                                    >
                                                </div>
                                            </div>
                                            <hr/>
                                            <div class="form-group row" aria-describedby="aaa">
                                                <div class="col-sm-3">
                                                    <label for="ays_active_camp_api_key">
                                                        <?= __('API Access Key', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <input type="text"
                                                           class="ays-text-input"
                                                           id="ays_active_camp_api_key"
                                                           name="ays_active_camp_api_key"
                                                           value=""
                                                    >
                                                </div>
                                            </div>
                                            <blockquote>
                                                <?= __("Your API URL and Key can be found in your account on the My Settings page under the \"Developer\" tab", $this->plugin_name); ?>
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset> <!-- Activecampaign integration PRO version!!! -->
                        <hr/>
                        <fieldset>
                            <legend>
                                <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/slack_logo.png" alt="">
                                <h5><?php echo __('Slack',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row" style="padding:0;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="pro_features" style="justify-content:flex-end;">
                                        <div style="margin-right:20px;">
                                            <p style="font-size:20px;">
                                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="DEVELOPER feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <div class="form-group row" aria-describedby="aaa">
                                                <div class="col-sm-3">
                                                    <button id="slackInstructionsPopOver" type="button" class="btn btn-info"
                                                            data-toggle="popover"
                                                            title="<?= __("Slack Integration Setup Instructions", $this->plugin_name) ?>"><?= __("Instructions", $this->plugin_name) ?></button>
                                                    
                                                </div>
                                            </div>
                                            <div class="form-group row" aria-describedby="aaa">
                                                <div class="col-sm-3">
                                                    <label for="ays_slack_client">
                                                        <?= __('App Client ID', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <input type="text"
                                                           class="ays-text-input"
                                                           id="ays_slack_client"
                                                           name="ays_slack_client"
                                                           value=""
                                                    >
                                                </div>
                                            </div>
                                            <hr/>
                                            <div class="form-group row" aria-describedby="aaa">
                                                <div class="col-sm-3">
                                                    <label for="ays_slack_oauth">
                                                        <?= __('Slack Authorization', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    
                                                        <span class="btn btn-success pointer-events-none">
                                                        <?= __("Authorized", $this->plugin_name) ?></span>
                                                </div>
                                            </div>
                                            <hr/>
                                            <div class="form-group row" aria-describedby="aaa">
                                                <div class="col-sm-3">
                                                    <label for="ays_slack_secret">
                                                        <?= __('App Client Secret', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <input type="text"
                                                           class="ays-text-input"
                                                           id="ays_slack_secret"
                                                           name="ays_slack_secret"
                                                           value="" >
                                                </div>
                                            </div>
                                            <hr/>
                                            <div class="form-group row" aria-describedby="aaa">
                                                <div class="col-sm-3">
                                                    <label for="ays_slack_oauth">
                                                        <?= __('App Access Token', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <button type="button"
                                                            class="btn btn-outline-secondary disabled"><?= __("Need Authorization", $this->plugin_name) ?>
                                                    </button>
                                                    <input type="hidden" id="ays_slack_token" name="ays_slack_token" value="">
                                                </div>
                                            </div>
                                            <blockquote>
                                                <?= __("You can get your App Client ID and Client Secret from your App's the Basic Information page", $this->plugin_name); ?>
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </fieldset> <!-- Slack integration PRO version!!! -->
                        <hr/>
                        <fieldset>
                            <legend>
                                <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/sheets_logo.png" alt="">
                                <h5><?php echo __('Google sheets',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row" style="padding:0;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="pro_features" style="justify-content:flex-end;">
                                        <div style="margin-right:20px;">
                                            <p style="font-size:20px;">
                                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="DEVELOPER feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <div class="form-group row" aria-describedby="aaa">
                                                <div class="col-sm-3">
                                                    <button id="googleInstructionsPopOver" type="button" class="btn btn-info" data-toggle="popover" title="" data-original-title="Google Integration Setup Instructions" aria-describedby="popover188544">Instructions</button>
                                                    <div class="d-none">
                                                        <p>1. <a href="https://console.developers.google.com/apis/credentials" target="_blank">Create</a> new Google Oauth cliend ID credentials (if you do not still have).</p>
                                                        <p>2. Choose the application type as <b>Web application</b></p>
                                                        <p>3. Add the following link in the <b>Authorized redirect URIs</b> field</p>
                                                        <p>
                                                            <code></code>
                                                        </p>
                                                        <p>4. Click on the <b>Create</b> button</p>
                                                        <p>5. Copy the <b>Your Cliend ID</b> from the opened popup and paste it in the Google Client ID field.Then click on the Sign In button to complete authorization</p>
                                                        <p>6. After the successful authorization,copy the <b>Your Client ID</b> and paste it in the Google Client ID field again. Also, copy the <b>Your Client Secret</b> and paste it in the Google Client Secret field.</p>
                                                        <p>7. Then click <b>Get token</b> button to get your token</p>
                                                        <p>8. If the token is given successfully, click on the <b>Save Changes</b> button.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-3">
                                                    <label for="">
                                                        <?= __('Google Client ID', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <input type="text" class="ays-text-input">
                                                </div>
                                            </div>
                                            <hr/>                               
                                            <div class="form-group row">
                                                <div class="col-sm-3">
                                                    <label for="">
                                                        <?= __('Google Sign in', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">                                       
                                                    <button type="button" class="btn btn-outline-secondary" >
                                                        <?= __("Sign in", $this->plugin_name) ?>
                                                    </button>                                       
                                                </div>
                                            </div>
                                            <hr/>
                                            <div class="form-group row">
                                                <div class="col-sm-3">
                                                    <label for="ays_poll_google_secret">
                                                        <?= __('Google Client Secret', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <input type="text" class="ays-text-input " readonly>
                                                </div>
                                            </div>                                
                                            <hr/>
                                            <div class="form-group row">
                                                <div class="col-sm-3">
                                                    <label for="">
                                                        <?= __('Get token', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <button type="button" id=""
                                                                class="btn btn-outline-secondary">
                                                            <?= __("Get token", $this->plugin_name) ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset> <!-- Google sheet integration PRO version!!! -->
                        <hr/>
                        <fieldset>
                            <legend>
                                <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/sendgrid_logo.png" alt="">
                                <h5><?php echo __('SendGrid',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row" style="padding:0;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="pro_features" style="justify-content:flex-end;">
                                        <div style="margin-right:20px;">
                                            <p style="font-size:20px;">
                                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="DEVELOPER feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <div class="form-group row">
                                                <div class="col-sm-3">
                                                    <label for="">
                                                        <?php echo __('SendGrid API Key',$this->plugin_name)?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <input type="text" class="ays-text-input" />
                                                </div>
                                            </div>
                                            <hr/>                                    
                                            <blockquote>
                                                <?php echo sprintf( __( "You can get your API key from ", $this->plugin_name ) . "<a href='%s' target='_blank'> %s.</a>", "https://app.sendgrid.com/settings/api_keys", "sendgrid.com" ); ?>
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset> <!-- SendGrid integration PRO version!!! -->
                        <hr/>
                        <fieldset>
                            <legend>
                                <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/gamipress_logo.png" alt="">
                                <h5><?php echo __('GamiPress',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row" style="padding:0;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="pro_features" style="justify-content:flex-end;">
                                        <div style="margin-right:20px;">
                                            <p style="font-size:20px;">
                                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="DEVELOPER feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">     
                                            <blockquote>
                                                <?php echo __( "Install the GamiPress plugin to use the integration. Configure the settings from the Automatic Points Awards section from the GamiPres plugin.", $this->plugin_name ); ?>
                                                <br>
                                                <?php echo __( "After enabling the integration, the Poll Maker will automatically be added to the event list.", $this->plugin_name ); ?>
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset> <!-- GamiPress integration PRO version!!! -->
                        <hr>
                        <fieldset>
                            <legend>
                                <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/mad-mimi-logo-min.png" alt="">
                                <h5><?php echo __('Mad Mimi',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row" style="padding:0;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="pro_features" style="justify-content:flex-end;">
                                        <div style="margin-right:20px;">
                                            <p style="font-size:20px;">
                                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="DEVELOPER feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <div class="form-group row">
                                                <div class="col-sm-3">
                                                    <label for="ays_poll_mad_mimi_user_name">
                                                        <?= __('Username', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <input type="text" class="ays-text-input" id="ays_poll_mad_mimi_user_name">
                                                </div>
                                            </div>
                                            <hr/>
                                            <div class="form-group row">
                                                <div class="col-sm-3">
                                                    <label for="ays_poll_mad_mimi_api_key">
                                                        <?= __('API Key', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <input type="text" class="ays-text-input">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset><!-- MAD MIMI PRO version!!!-->
                        <hr>
                        <fieldset>
                            <legend>
                                <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/get_response.png" alt="">
                                <h5><?php echo __('GetResponse',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row" style="padding:0;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="pro_features" style="justify-content:flex-end;">
                                        <div style="margin-right:20px;">
                                            <p style="font-size:20px;">
                                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="DEVELOPER feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <div class="form-group row" aria-describedby="aaa">
                                                <div class="col-sm-3">
                                                    <label for="ays_poll_getresponse_api_key">
                                                        <?= __('GetResponse API Key', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <input type="text"
                                                            class="ays-text-input"
                                                    >
                                                </div>
                                            </div>
                                            <blockquote>
                                                <?php echo sprintf(__("You can get your API key from your <a href='%s' target='_blank'>account</a>.", $this->plugin_name), "#"); ?>
                                            </blockquote>
                                            <blockquote>
                                                <?php echo __("For security reasons, unused API keys expire after 90 days. When that happens, you’ll need to generate a new key.", $this->plugin_name) ?>
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset><!-- GET RESPONSE -->
                        <hr>
                        <fieldset>
                            <legend>
                                <img class="ays_integration_logo" src="<?php echo POLL_MAKER_AYS_ADMIN_URL; ?>/images/integrations/convertkit_logo.png" alt="">
                                <h5><?php echo __('ConvertKit',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row" style="padding:0;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="pro_features" style="justify-content:flex-end;">
                                        <div style="margin-right:20px;">
                                            <p style="font-size:20px;">
                                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="DEVELOPER feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <div class="form-group row" aria-describedby="aaa">
                                                <div class="col-sm-3">
                                                    <label for="ays_poll_convert_kit">
                                                        ConvertKit <?= __('API Key', $this->plugin_name) ?>
                                                    </label>
                                                </div>
                                                <div class="col-sm-9">
                                                    <input type="text"
                                                        class="ays-text-input"
                                                    >
                                                </div>
                                            </div>                                
                                            <blockquote>
                                                <?php echo sprintf( __( "You can get your API Key from your ", $this->plugin_name ) . "<a href='%s' target='_blank'> %s.</a>", "#", "Account" ); ?>
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset><!-- CONVERT KIT -->
                    </div>
                    <div id="tab3"
                         class="ays-poll-tab-content <?php echo ($ays_poll_tab == 'tab3') ? 'ays-poll-tab-content-active' : ''; ?>">
                        <p class="ays-subtitle"><?php echo __('Shortcodes', $this->plugin_name) ?></p>
                        <hr/>
                        <fieldset>
                            <legend>
                                    <strong style="font-size:30px;">[ ]</strong>
                                    <h5 class="ays-subtitle"><?php echo __('Shortcode for all polls',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="all_ays_poll_shortcodes">
                                        <?php echo __("Shortcode for all polls ", $this->plugin_name); ?>
                                    </label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" name="all_ays_poll_shortcodes" id="all_ays_poll_shortcodes" onclick="this.setSelectionRange(0, this.value.length)"readonly="" class="ays-text-input" value='[ays_poll_all display="published/all"]'>                                        
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="ays_poll_all_polls_shortcodes">
                                        <?php echo __("Message", $this->plugin_name); ?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('The notification message will be appeared when all polls of the given category expires or becomes unpublished.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" class="ays-text-input" name="ays_poll_all_polls_shortcodes" id="ays_poll_all_polls_shortcodes" value="<?php echo $all_shortcode_message;?>">                                        
                                </div>
                            </div>
                            <hr>
                            <blockquote>
                                <ul class="ays-poll-general-settings-blockquote-ul">
                                    <li>
                                        <?php echo __("Paste the shortcode into any of your posts to show all polls together.", $this->plugin_name); ?>
                                    </li>
                                    <li>
                                        <?php
                                            echo sprintf(
                                                __( '%sDisplay%s', $this->plugin_name ) . ' - ' . __( 'Choose the method of displaying. Example: display="published".', $this->plugin_name ),
                                                '<b>',
                                                '</b>'
                                            );
                                        ?>
                                        <ul class='ays-poll-general-settings-ul'>
                                            <li>
                                                <?php
                                                    echo sprintf(
                                                        __( '%spublished%s', $this->plugin_name ) . ' - ' . __( 'If you set the method as published, it will show only published polls excluding expired polls.', $this->plugin_name ),
                                                        '<b>',
                                                        '</b>'
                                                    );
                                                ?>
                                            </li>
                                            <li>
                                                <?php
                                                    echo sprintf(
                                                        __( '%sall%s', $this->plugin_name ) . ' - ' . __( 'If you set the method as all, it will show all polls including expired polls.', $this->plugin_name ),
                                                        '<b>',
                                                        '</b>'
                                                    );
                                                ?>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </blockquote>
                        </fieldset>
                        <hr/>
                        <fieldset>
                            <legend>
                                    <strong style="font-size:30px;">[ ]</strong>
                                    <h5 class="ays-subtitle"><?php echo __('Shortcode result by ID',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="ays_poll_shortcodes_by_id">
                                        <?php echo __("Shortcode result by ID ", $this->plugin_name); ?>
                                    </label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" name="ays_poll_shortcodes_by_id" id="ays_poll_shortcodes_by_id" onclick="this.setSelectionRange(0, this.value.length)"readonly="" class="ays-text-input" value='[ayspoll_results id="Your Poll ID"]'>
                                        
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label>
                                        <?php echo __('Show results by',$this->plugin_name)?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Select the way of displaying the results: Bar chart , Pie chart or Column Chart.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline ays_poll_loader">
                                            <label class="form-check-label ays_poll_check_label"
                                                    for="ays_poll_show_res_standart"> <?= __('Bar Chart', $this->plugin_name); ?> </label>
                                            <input type="radio" id="ays_poll_show_res_standart" name="ays_poll_show_result_view"
                                                    value="standart" <?= ($poll_show_result_view == 'standart') ? 'checked' : ''; ?>>
                                        </div>
                                        <div class="form-check form-check-inline ays_poll_loader">
                                            <label class="form-check-label ays_poll_check_label"
                                                    for="ays_poll_show_res_pie_chart"> <?= __('Pie Chart', $this->plugin_name); ?> </label>
                                            <input type="radio" id="ays_poll_show_res_pie_chart" name="ays_poll_show_result_view"
                                                    value="pie_chart" <?= ($poll_show_result_view == 'pie_chart') ? 'checked' : ''; ?>>
                                        </div>
                                        <div class="form-check form-check-inline ays_poll_loader">
                                            <label class="form-check-label ays_poll_check_label"
                                                   for="ays_poll_show_res_column_chart"> <?= __('Column Chart', $this->plugin_name); ?> </label>
                                            <input type="radio" id="ays_poll_show_res_column_chart" name="ays_poll_show_result_view"
                                                   value="column_chart" <?= ($poll_show_result_view == 'column_chart') ? 'checked' : ''; ?> />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <blockquote>
                                <?php echo __("Paste the shortcode into any of your posts to show only the result page of the particular poll.", $this->plugin_name); ?>
                            </blockquote>
                        </fieldset>
                        <hr>
                        <fieldset>
                            <legend>
                                    <strong style="font-size:30px;">[ ]</strong>
                                    <h5 class="ays-subtitle"><?php echo __('Recent Polls Settings',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="ays_poll_shortcodes_recent_by_id">
                                        <?php echo __("Shortcode", $this->plugin_name); ?>
                                    </label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" name="ays_poll_shortcodes_recent_by_id" id="ays_poll_shortcodes_recent_by_id" onclick="this.setSelectionRange(0, this.value.length)"readonly="" class="ays-text-input" value='[ays_display_polls orderby="random/recent" count="5"]'>
                                        
                                </div>
                            </div>
                            <blockquote>
                                <ul class="ays-poll-general-settings-blockquote-ul">
                                    <li>
                                        <?php echo __("Copy the following shortcode, configure it based on your preferences and paste it into the post.", $this->plugin_name); ?>
                                    </li>
                                    <ul class="ays-poll-general-settings-ul">
                                        <li>
                                            <?php
                                                echo sprintf(
                                                    __( '%sRandom%s', $this->plugin_name ) . ' - ' . __( 'If you set the ordering method as random and gave a value to count option, then it will randomly display that given amount of polls from your created polls.', $this->plugin_name ),
                                                    '<b>',
                                                    '</b>'
                                                );
                                            ?>
                                        </li>
                                        <li>
                                            <?php
                                                echo sprintf(
                                                    __( '%sRecent%s', $this->plugin_name ) . ' - ' . __( 'If you set the ordering method as recent and gave a value to count option, then it will display that given amount of polls from your recently created polls.', $this->plugin_name ),
                                                    '<b>',
                                                    '</b>'
                                                );
                                            ?>
                                        </li>
                                    </ul>
                                </ul>
                            </blockquote>
                        </fieldset>
                        <hr>
                        <fieldset>
                                <legend>
                                    <strong style="font-size:30px;"><i class="ays_fa ays_poll_fa-list"></i></strong>
                                    <h5><?php echo __('Poll Categories',$this->plugin_name)?></h5>
                                </legend>
                            <div class="col-sm-12" style="padding:50px 0;">
                                <div class="pro_features" style="justify-content:flex-end;">
                                    <div style="margin-right:20px;">
                                        <p style="font-size:20px;">
                                            <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                            <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                        </p>
                                    </div>
                                </div>
                                <blockquote>
                                    <p style="margin:0;"><?php echo __( "Paste the shortcode into any of your posts to show all/random polls from the given category by list/grid view.", $this->plugin_name ); ?></p>
                                </blockquote>
                                <hr/>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_invidLead">
                                            <?php echo __( "Poll category shortcode", $this->plugin_name ); ?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Copy the following shortcode, configure it based on your preferences and paste it into the post. Put the shortcode of your preferred category,  choose the method of displaying (all/random), and the design of the layout(list/grid). If you set the method as All, it will show all polls from the given category, if you set the method as Random, please give a value to count option too, and it will randomly display that given amount of polls from the given category.',$this->plugin_name)?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="ays_invidLead" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_poll_cat id="Your Poll Cat ID" display="all/random" count="5" layout="list/grid"]'>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <hr>
                        <fieldset>
                                <legend>
                                    <strong style="font-size:30px;">[ ]</strong>
                                    <h5 class="ays-subtitle"><?php echo __('Global Leaderboard Settings',$this->plugin_name)?></h5>
                                </legend>
                                <div class="col-sm-12" style="padding:20px 0;">
                                    <div class="pro_features" style="justify-content:flex-end;">
                                        <div style="margin-right:20px;">
                                            <p style="font-size:20px;">
                                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                            </p>
                                        </div>
                                    </div>
                                <blockquote>
                                    <?php echo __( "Paste the shortcode into any of your posts to show the list of the top users who have passed your polls.", $this->plugin_name ); ?>
                                </blockquote>
                                <hr>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_globLead">
                                            <?php echo __( "Global Leaderboard shortcode", $this->plugin_name ); ?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('You can copy the shortcode and paste it to any post/page to see the list of the top user’s who passed any poll.',$this->plugin_name)?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="ays_globLead" class="ays-text-input" value='[ays_poll_gleaderboard]'>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_gleadboard_count">
                                            <?php echo __('Users count',$this->plugin_name)?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('How many users’ results will be shown in the leaderboard.',$this->plugin_name)?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="number"
                                            class="ays-text-input"                 
                                            id="ays_gleadboard_count" 
                                            name="ays_gleadboard_count">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_gleadboard_width">
                                            <?php echo __('Width',$this->plugin_name)?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('The width of the Leaderboard box. It accepts only numeric values. For 100% leave it blank.',$this->plugin_name)?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="number"
                                            class="ays-text-input"                 
                                            id="ays_gleadboard_width" 
                                            name="ays_gleadboard_width">
                                        <span style="display:block;" class="ays_poll_small_hint_text"><?php echo __("For 100% leave blank", $this->plugin_name);?></span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label>
                                            <?php echo __('Users group by',$this->plugin_name)?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Select the way for grouping the results. If you want to make Leaderboard for logged in users, then choose ID. It will collect results by WP user ID. If you want to make Leaderboard for guests, then you need to choose Email and enable Information Form and Email, Name options from poll settings. It will group results by emails and display guests Names.',$this->plugin_name)?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <label class="ays_poll_loader">
                                            <input type="radio" name="ays_gleadboard_orderby" value="id">
                                            <span><?php echo __( "ID", $this->plugin_name); ?></span>
                                        </label>
                                        <label class="ays_poll_loader">
                                            <input type="radio" name="ays_gleadboard_orderby" value="email">
                                            <span><?php echo __( "Email", $this->plugin_name); ?></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_gleadboard_color">
                                            <?php echo __('Color',$this->plugin_name)?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Top color of the leaderboard',$this->plugin_name)?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="ays_gleadboard_color" name="ays_gleadboard_color" data-alpha="true">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_gleadboard_custom_css">
                                            <?php echo __('Custom CSS',$this->plugin_name)?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Field for entering your own CSS code',$this->plugin_name)?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <textarea class="ays-textarea" id="ays_gleadboard_custom_css" name="ays_gleadboard_custom_css" cols="30"
                                              rows="10" style="height: 80px;"></textarea>
                                    </div>
                                </div> <!-- Custom global leadboard CSS -->
                            </div>                        
                        </fieldset>
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;">[ ]</strong>
                                <h5 class="ays-subtitle"><?php echo __('Global Leaderboard By Category Settings',$this->plugin_name)?></h5>
                            </legend>
                            <!-- shortcode  -->
                            <div class="col-sm-12" style="padding:30px;">
			                    <div class="pro_features" style="justify-content:flex-end;">
			                        <div style="margin-right: 20px;">
			                            <p >
			                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
			                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
			                            </p>
                                    </div>
                                    <div style="position: absolute; top: 15px;margin-right: 20px;">
			                            <p >
			                                <?php echo __("This feature is available only in ", $this->plugin_name); ?>
			                                <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
			                            </p>
			                        </div>
                                </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_cat_globLead">
                                        <?php echo __( "Shortcode", $this->plugin_name ); ?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Paste the shortcode into any of your posts or pages to show the list of the top users who have voted your polls. It will print the attempts count of each poll participant, as well.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text"  value='[ays_poll_cat_gleaderboard id="Your Poll Category ID"]'>
                                </div>
                            </div>
                            <hr/>
                            <!-- users count  -->
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_cat_gleadboard_count">
                                        <?php echo __('Users count',$this->plugin_name)?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Specify how many users’ results to be shown on the leaderboard.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="number"
                                        class="ays-text-input"
                                    />
                                </div>
                            </div>
                            <hr/>
                            <!-- witdth  -->
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_cat_gleadboard_width">
                                        <?php echo __('Width',$this->plugin_name)?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __("Set the width of the leaderboard's box. It accepts only numeric values. For 100%, leave the field blank.",$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="number"
                                        class="ays-text-input"
                                    />
                                    <span style="display:block;" class="ays_poll_small_hint_text"><?php echo __("For 100% leave blank", $this->plugin_name);?></span>
                                </div>
                            </div>
                            <hr/>
                            <!-- users group by  -->
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label>
                                        <?php echo __('Group users by',$this->plugin_name)?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Select the way for grouping the results.
                                        By ID - Choose by ID if you want to make a leaderboard for logged-in users. It will collect results by WordPress user ID.
                                        By  Email - Choose by email if you want to make the leaderboard for guests. In this case,  do not forget to activate the Information Form of the given polls.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <label class="ays_poll_loader">
                                        <input type="radio"/>
                                        <span><?php echo __( "ID", $this->plugin_name); ?></span>
                                    </label>
                                    <label class="ays_poll_loader">
                                        <input type="radio"/>
                                        <span><?php echo __( "Email", $this->plugin_name); ?></span>
                                    </label>
                                </div>
                            </div>
                            <hr/>
                            <!-- color  -->
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_cat_gleadboard_color">
                                        <?php echo __('Color',$this->plugin_name)?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __("Choose the color of the leaderboard's box.",$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" >
                                </div>
                            </div>
                            <hr/>
                            <!-- custom css  -->
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_poll_cat_gleadboard_custom_css">
                                        <?php echo __('Custom CSS',$this->plugin_name)?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Enter your own custom CSS code.',$this->plugin_name)?>">
                                            <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <textarea class="ays-textarea" id="ays_poll_cat_gleadboard_custom_css" name="ays_poll_cat_gleadboard_custom_css" cols="30"
                                          rows="10" style="height: 80px;"></textarea>
                                </div>
                            </div>
                            <hr>                            
                            <blockquote>
                                <?php echo __( "Paste the shortcode into any of your posts or pages to show the list of the top users who have voted your polls. It will print the attempts count of each poll participant, as well.", $this->plugin_name ); ?>
                            </blockquote>
                            </div>
                        </fieldset><!-- Global leaderboard by category-->
                        <hr/>                     
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;"><i class="ays_fa ays_poll_fa-poll"></i></strong>
                                <h5><?php echo __( 'Request Form' , $this->plugin_name )?></h5>
                            </legend>
                            <div class="col-sm-12" style="padding:25px;">
                                <div class="pro_features" style="justify-content:flex-end;">
                                    <div style="margin-right:20px;">
                                        <p style="font-size:20px;">
                                            <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                            <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                        </p>
                                    </div>
                                </div>                            
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_glob_lead">
                                            <?php echo __( "Shortcode", $this->plugin_name ); ?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Copy the following shortcode and paste it into your desired post. It will allow users to send a request for building a poll with simple settings (Poll title, question, answers). Find the list of the requests in the Requests page, which is located on the Poll Maker left navbar. For accepting the request, the admin needs to click on the Approve button next to the given poll.',$this->plugin_name)?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="ays_glob_lead" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_poll_request_form]'>
                                    </div>
                                </div>
                                <hr/>
                                <blockquote>
                                    <p style="margin:0;"><?php echo __( "Ability to allow users to create a poll from the front-end.", $this->plugin_name ); ?></p>
                                </blockquote>
                            </div>
                        </fieldset>  
                        <hr>
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;">[ ]</strong>
                                <h5><?php echo __('User History Settings',$this->plugin_name)?></h5>
                            </legend>
                            <div class="col-sm-12" style="padding:25px;">
                                <div class="pro_features" style="justify-content:flex-end;">
                                    <div style="margin-right:20px;">
                                        <p style="font-size:20px;">
                                            <?php echo __("This feature is available only in ", $this->plugin_name); ?>
                                            <a href="https://ays-pro.com/wordpress/poll-maker" target="_blank" title="Developer feature"><?php echo __("PRO version!!!", $this->plugin_name); ?></a>
                                        </p>
                                    </div>
                                </div> 
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="ays_poll_user_history">
                                            <?php echo __( "Shortcode", $this->plugin_name ); ?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Paste the shortcode into any of your posts or pages to show the current user’s votes history. Each user will see individually presented content based on their taken polls.',$this->plugin_name)?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="ays_poll_user_history" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_poll_user_history]'>
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <label>
                                            <?php echo __( "User history results table columns", $this->plugin_name ); ?>
                                            <a class="ays_help" data-toggle="tooltip" title="<?php echo __('You can sort table columns and select which columns must display on the front-end.',$this->plugin_name)?>">
                                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                                            </a>
                                        </label>
                                        <div class="ays-show-user-history-table-wrap">
                                            <ul class="ays-show-user-history-table">
                                                <?php
                                                    foreach ($default_ays_poll_user_page_columns as $key => $val) {
                                                        ?>
                                                        <li class="ays-user-history-option-row ui-state-default">
                                                            <input type="checkbox" id="ays_show_<?php echo $key; ?>" value="<?php echo $key; ?>" class="ays-checkbox-input"/>
                                                            <label for="ays_show_<?php echo $key; ?>">
                                                                <?php echo $val; ?>
                                                            </label>
                                                        </li>
                                                        <?php
                                                    }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset><!-- User history shortcode -->
                    </div>
                    <div id="tab4" class="ays-poll-tab-content <?php echo ($ays_poll_tab == 'tab4') ? 'ays-poll-tab-content-active' : ''; ?>">
                        <p class="ays-subtitle">
                            <?php echo __('Fields placeholders',$this->plugin_name)?>
                            <a class="ays_help" data-toggle="tooltip" data-html="true" title="<p style='margin-bottom:3px;'><?php echo __( 'If you make a change here, these words will not be translated either․', $this->plugin_name ); ?>">
                                <i class="ays_poll_fas ays_poll_fa-info-circle"></i>
                            </a>
                        </p>
                        <blockquote>
                            <p>
                                <?php echo __( "With the help of this section, you can change the fields' placeholders of the Information form. Find the available fields in the User data tab of your polls.", $this->plugin_name ); ?>
                                <span class="ays-poll-blockquote-span"><?php echo __( "Note: If you make a change here, these words will not be translated either.", $this->plugin_name ); ?></span>
                            </p>

                        </blockquote>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label for="ays_poll_fields_placeholder_name">
                                    <?php echo __( "Name", $this->plugin_name ); ?>
                                </label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" id="ays_poll_fields_placeholder_name" name="ays_poll_fields_placeholder_name" class="ays-text-input ays-text-input-short"  value='<?php echo $poll_fields_placeholder_name; ?>'>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label for="ays_poll_fields_placeholder_email">
                                    <?php echo __( "Email", $this->plugin_name ); ?>
                                </label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" id="ays_poll_fields_placeholder_email" name="ays_poll_fields_placeholder_email" class="ays-text-input ays-text-input-short"  value='<?php echo $poll_fields_placeholder_email; ?>'>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label for="ays_poll_fields_placeholder_phone">
                                    <?php echo __( "Phone", $this->plugin_name ); ?>
                                </label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" id="ays_poll_fields_placeholder_phone" name="ays_poll_fields_placeholder_phone" class="ays-text-input ays-text-input-short"  value='<?php echo $poll_fields_placeholder_phone; ?>'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr/>
			<?php
            $loader_iamge = "<span class='display_none'><img src=".POLL_MAKER_AYS_ADMIN_URL."/images/loaders/loading.gif></span>";
			wp_nonce_field('settings_action', 'settings_action');
			$other_attributes = array();
			submit_button(__('Save changes', $this->plugin_name), 'primary ays-button', 'ays_submit', false, $other_attributes);
            echo $loader_iamge;
			?>
        </form>
    </div>
</div>