<?php

class WPML_CRED_Glue {
    public $settings;
    private $glue_form_type = 'translation';
    
    function __construct() {
               global $pagenow,$iclTranslationManagement;
               
        add_action('init', array($this, 'scripts_load'));
        add_action('init', array($this,'on_wp_init'), 1);
        
        if(isset($_GET['action']) && ($_GET['action'] == 'edit_translation' || $_GET['action'] == 'create_translation')){
            $iclTranslationManagement = new TranslationManagement;
        }
          
         add_action('cred_admin_menu_after_forms', array($this, 'admin_menu_setup'), 3, 1);  
        
        // localization
        $this->loadLocale();
        
        $this->settings = get_option('wpml_cred_glue_settings');
        
        if(!$this->settings){
            $this->settings['controls_after_content'] = true;
            $this->settings['dismiss'] = false;
            $this->save_settings($this->settings);            
        }
                
        
        if($this->settings['controls_after_content']){
             add_filter('the_content', array($this,'cred_wpml_glue_front_controls'));
        }
               
        //adding translation form option in CRED
        add_filter('cred_admin_form_type_options', array($this,'cred_wpml_glue_generate_translation_form_option'),10,3);
        
        //adding button to insert original
        add_filter('cred_wpml_glue_generate_insert_original_button', array($this,'cred_wpml_glue_generate_insert_original_button'));
         
        //adding button to insert button block
        add_filter('cred_wpml_glue_generate_insert_button_block', array($this,'cred_wpml_glue_generate_insert_button_block'),10,2);
        
        //adding original content
        add_filter('glue_add_original_content',array($this,'cred_wpml_glue_add_original_content'));
        
        //adding controls widget
        $this->glue_controls_widget_init();
        
        // custom hook for adding the controls to the template
        add_action('cred_wpml_frontend_translation', array($this, 'glue_generate_controls_echo'));
        
        //adding controls shortcode
        add_shortcode('cred_wpml_frontend_translation', array($this, 'cred_wpml_glue_front_controls'));
        
        //adding original content shortcode
        add_shortcode('cred_frontend_translation_original_content', array($this, 'wpml_cred_glue_original_content_shortcode'));        
        
        //add check sync fields
        add_filter('glue_check_sync', array($this, 'cred_wpml_glue_check_sync'),10,2);
        
        //add sync fields
        add_action('wp_print_scripts', array($this, 'wp_print_scripts'));
        
        //add term filter when creation translation 
        add_filter('terms_clauses', array($this, 'terms_clauses'));
        
        //check user privileges
        add_filter('cred_wpml_glue_check_user_privileges',array($this,'cred_wpml_glue_check_user_privileges'));
        
        //check is translated post type
        add_filter('cred_wpml_glue_is_translated_and_unique_post_type',array($this,'cred_wpml_glue_is_translated_and_unique_post_type'));
        
        //form action filter
        add_filter('cred_submit_complete',array($this,'cred_wpml_glue_submit_complete'),10,2);
        
        //save post (check post status)
        add_action('cred_save_data',array($this,'cred_save_data'),10,2);
        
        //add translation manager filters
        add_filter('wpml_tm_save_post_trid_value',array($this,'wpml_tm_save_post_trid_value'));
        add_filter('wpml_tm_save_post_lang_value',array($this,'wpml_tm_save_post_lang_value'));
        
        //add sitepress filters
        add_filter('wpml_save_post_lang',array($this,'wpml_save_post_lang'));
        add_filter('wpml_save_post_trid_value',array($this,'wpml_save_post_trid_value'),10,2);
        add_filter('wpml_create_term_lang',array($this,'wpml_create_term_lang'));
        
        
        
        
        //add title to form
        if($pagenow == 'post-new.php' || $pagenow == 'post.php'){
            add_action('admin_print_scripts', array($this,'cred_wpml_glue_set_form_info'));         
        }
    
        //add potential parents filter
        add_filter('wpml_cred_potential_parents_filter', array($this,'potential_parents_filter'),10,2);
        
        //Add an option to select if translators can translate in the admin, front-end or both
        add_action('show_user_profile', array($this, 'add_user_profile_option'));
        add_action('personal_options_update', array($this, 'save_user_options'));

        //add user filter on the translation dashboard page
        add_filter('wpml_tm_translators_list',array($this,'cred_wpml_translation_dashboard_check_user'));

    }    
     
    function on_wp_init(){
        if(get_current_user_id() && !get_user_meta(get_current_user_id(), 'wpml_cred_user_option', true)){
            update_user_meta(get_current_user_id(),'wpml_cred_user_option','both');
        }

        if(get_current_user_id() && get_user_meta(get_current_user_id(), 'wpml_cred_user_option', true) == 'page'){
            //add filter to tanslations link
            add_filter('wpml_link_to_translation',array($this,'cred_wpml_link_to_translation'),10,3);
        }
    }
     
   //load css 
   function scripts_load(){
       wp_enqueue_style('glue', CRED_WPML_GLUE_PLUGIN_URL . '/css/style.css', CRED_WPML_GLUE_VERSION);
   } 
    
    // load locale file
    function loadLocale()
    {
        // load translations from locale
        load_plugin_textdomain('cred-wpml', false, CRED_WPML_LOCALE_PATH);
    }
    
    // Menus
    function admin_menu_setup(){
                
    if (!defined('CRED_FE_VERSION') || version_compare(CRED_FE_VERSION,'1.1.4','<')){        
         add_menu_page(__( 'CRED Frontend Translation', 'cred-wpml' ), __( 'CRED Frontend Translation', 'cred-wpml' ), 'manage_options', CRED_WPML_GLUE_PLUGIN_FOLDER . '/menu/settings.php', null);
    }else{ 
        add_submenu_page('CRED_Forms', __( 'CRED Frontend Translation', 'cred-wpml' ), __( 'CRED Frontend Translation', 'cred-wpml' ), 'manage_options', CRED_WPML_GLUE_PLUGIN_FOLDER . '/menu/settings.php');
     
    }
                       
    }

    //save settings in DB
    function save_settings($settings=null){
        if(!is_null($settings)){
            foreach($settings as $k=>$v){
                if(is_array($v)){
                    foreach($v as $k2=>$v2){
                        $this->settings[$k][$k2] = $v2;
                    }
                }else{
                    $this->settings[$k] = $v;
                }
            }
        }
        if(!empty($this->settings)){
            update_option('wpml_cred_glue_settings', $this->settings);
        }
        do_action('wpml_cred_glue_settings', $settings);
    }
    
    //generate controls for translations
    //return html
    function cred_wpml_glue_front_controls($content = '') {
        global $post,$sitepress,$wpdb,$sitepress_settings;

        //check is single page && check user settings
         if(!is_singular() || (isset($_GET['action']) && in_array($_GET['action'], array('edit_translation','create_translation'))) 
                 || (get_current_user_id() && get_user_meta(get_current_user_id(), 'wpml_cred_user_option', true) == 'admin'))
                 return $content;
        
        $forms = $this->cred_wpml_glue_get_translation_forms();
        
        if (!empty($forms)) {
            foreach ($forms as $key => $form_post_type) {
                if ($post->post_type == $form_post_type) {
                    $form_id = $key;
                    break;
                }
            }
        }
        
        if(!isset($form_id))
            return $content;
        $controls = wpml_generate_controls($post->ID, $form_id);

        $content .= '<div class="glue_front_controls">';

        //check if translated
        $translated = $wpdb->get_row($wpdb->prepare("SELECT source_language_code IS NULL AS original FROM {$wpdb->prefix}icl_translations WHERE element_id = %d AND element_type = %s", $post->ID,'post_'.$post->post_type));
        if(!$translated->original){
            $current_language = $sitepress->get_language_for_element($post->ID, 'post_' . $post->post_type);
            $translator = $wpdb->get_row($wpdb->prepare("SELECT meta_value LIKE '%s' AS translator FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s",'%'.$current_language.'%',get_current_user_id(),$wpdb->prefix.'language_pairs'));
                      
            if($translator && $translator->translator){
                //generate edit link for this translation           
                $content .= '<b>' . __("Edit translation", "cred-wpml") . '</b>&nbsp;';
                $post_url = get_permalink($post->ID);
                if(false===strpos($post_url,'?') || (false===strpos($post_url,'?') && $sitepress_settings['language_negotiation_type'] != '3')){
                    $content .= '<a href = "?action=edit_translation&cred-edit-form='.$form_id.'" title = "'. esc_attr__("edit translation", "cred-wpml").'">';
                }else{
                    $content .= '<a href = "'.$post_url.'&action=edit_translation&cred-edit-form='.$form_id.'" title = "'. esc_attr__("edit translation", "cred-wpml").'">';
                }
                
                $content .= '<img border = "0" src = "' . CRED_WPML_GLUE_PLUGIN_URL . '/img/edit_translation.png" alt = "'. esc_attr__("edit translation", "cred-wpml").'" width = "16" height = "16"></a><br><br>';
                              
            }
        }
        
        $flag = false;

        foreach ($controls as $key => $control) {

            if ($control['action'] == 'create') {
                if (!$flag) {
                    $content .= '<b>' . __("Translate this content", "cred-wpml") . '</b>
        <table width = "auto" class = "glue_front_controls_table">
            <tbody><tr>                    
                    <td colspan="2" align = "right"><b>' . __("Translate", "cred-wpml") . '</b></td>
                </tr>';
                    $flag = true;
                }

                $content .= '<tr>';
                $content .= '<td style = "padding-left: 4px;">' . $control['language'] . '</td>';

                $trid = $sitepress->get_element_trid($post->ID, 'post_'. $post->post_type);
                $translation_id = $wpdb->get_var($wpdb->prepare("SELECT translation_id FROM {$wpdb->prefix}icl_translations WHERE trid=%d AND language_code=%s",$trid , $key));

                if($translation_id){
                    $trnsl_status = $wpdb->get_row($wpdb->prepare("SELECT status,translator_id FROM {$wpdb->prefix}icl_translation_status WHERE translation_id = %d", $translation_id));
                    if($trnsl_status->translator_id == get_current_user_id() && ($trnsl_status->status == ICL_TM_WAITING_FOR_TRANSLATOR || $trnsl_status->status == ICL_TM_IN_PROGRESS)){
                        $content .= '<td align = "right"><a href = "#" title = "'. esc_attr__("Please finish translating in translation interface", "cred-wpml").'"><img border = "0" src = "' . CRED_WPML_GLUE_PLUGIN_URL . '/img/edit_translation_disabled.png" alt = "'. esc_attr__("Waiting for translator, you can't edit this translation", "cred-wpml").'" width = "16" height = "16"></a></td>';
                    }elseif($trnsl_status->status == ICL_TM_WAITING_FOR_TRANSLATOR){
                        $content .= '<td align = "right"><a href = "#" title = "'. esc_attr__("Waiting for translator, you can't edit this translation", "cred-wpml").'"><img border = "0" src = "' . CRED_WPML_GLUE_PLUGIN_URL . '/img/edit_translation_disabled.png" alt = "'. esc_attr__("Waiting for translator, you can't edit this translation", "cred-wpml").'" width = "16" height = "16"></a></td>';
                    }elseif($trnsl_status->status == ICL_TM_IN_PROGRESS){
                        $content .= '<td align = "right"><a href = "#" title = "'. esc_attr__("Translation in progress, you can't edit this translation", "cred-wpml").'"><img border = "0" src = "' . CRED_WPML_GLUE_PLUGIN_URL . '/img/edit_translation_disabled.png" alt = "'. esc_attr__("Translation in progress, you can't edit this translation", "cred-wpml").'" width = "16" height = "16"></a></td>';
                    }else{
                        $content .= '<td align = "right"><a href = "' . $control['url'] . '" title = "'. esc_attr__("add translation", "cred-wpml").'"><img border = "0" src = "' . CRED_WPML_GLUE_PLUGIN_URL . '/img/add_translation.png" alt = "'. esc_attr__("add translation", "cred-wpml").'" width = "16" height = "16"></a></td>';
                    }
                }else{
                    $content .= '<td align = "right"><a href = "' . $control['url'] . '" title = "'. esc_attr__("add translation", "cred-wpml").'"><img border = "0" src = "' . CRED_WPML_GLUE_PLUGIN_URL . '/img/add_translation.png" alt = "'. esc_attr__("add translation", "cred-wpml").'" width = "16" height = "16"></a></td>';
                }

                $content .= '</tr>';
            }
        }

        if ($flag)
            $content .= '</tbody></table>';

        $flag = false;

        foreach ($controls as $key => $control) {

            if ($control['action'] == 'edit') {
                if (!$flag) {
                    $content .= '<div class = "glue_box_paragraph"><b>' . __("Translations", "cred-wpml") . '</b>';
                    $content .= '<table width = "auto" class ="glue_front_controls_table"><tbody>';
                    $flag = true;
                }
                $content .= '<tr>';
                $content .= '<td style = "padding-left: 4px;">' . $control['language'] . '</td>';
                if($key == $sitepress->get_default_language()){
                    $content .= '<td align = "right"><a href = "#" title = "'. esc_attr__("You can't edit original document using CRED frontend translation", "cred-wpml").'"><img border = "0" src = "' . CRED_WPML_GLUE_PLUGIN_URL . '/img/edit_translation_disabled.png" alt = "'. esc_attr__("You can't edit original document using CRED frontend translation", "cred-wpml").'" width = "16" height = "16"></a></td>';
                }else{
                    $trid = $sitepress->get_element_trid($post->ID, 'post_'. $post->post_type);
                    $translation_id = $wpdb->get_var($wpdb->prepare("SELECT translation_id FROM {$wpdb->prefix}icl_translations WHERE trid=%d AND language_code=%s",$trid , $key));

                    if($translation_id){
                        $trnsl_status = $wpdb->get_row($wpdb->prepare("SELECT status,translator_id FROM {$wpdb->prefix}icl_translation_status WHERE translation_id = %d", $translation_id));
                        if($trnsl_status->translator_id == get_current_user_id() && ($trnsl_status->status == ICL_TM_WAITING_FOR_TRANSLATOR || $trnsl_status->status == ICL_TM_IN_PROGRESS)){
                            $content .= '<td align = "right"><a href = "#" title = "'. esc_attr__("Please finish translating in translation interface", "cred-wpml").'"><img border = "0" src = "' . CRED_WPML_GLUE_PLUGIN_URL . '/img/edit_translation_disabled.png" alt = "'. esc_attr__("Waiting for translator, you can't edit this translation", "cred-wpml").'" width = "16" height = "16"></a></td>';
                        }elseif($trnsl_status->status == ICL_TM_WAITING_FOR_TRANSLATOR){
                            $content .= '<td align = "right"><a href = "#" title = "'. esc_attr__("Waiting for translator, you can't edit this translation", "cred-wpml").'"><img border = "0" src = "' . CRED_WPML_GLUE_PLUGIN_URL . '/img/edit_translation_disabled.png" alt = "'. esc_attr__("Waiting for translator, you can't edit this translation", "cred-wpml").'" width = "16" height = "16"></a></td>';
                        }elseif($trnsl_status->status == ICL_TM_IN_PROGRESS){
                            $content .= '<td align = "right"><a href = "#" title = "'. esc_attr__("Translation in progress, you can't edit this translation", "cred-wpml").'"><img border = "0" src = "' . CRED_WPML_GLUE_PLUGIN_URL . '/img/edit_translation_disabled.png" alt = "'. esc_attr__("Translation in progress, you can't edit this translation", "cred-wpml").'" width = "16" height = "16"></a></td>';
                        }else{
                            $content .= '<td align = "right"><a href = "' . $control['url'] . '" title = "'. esc_attr__("edit translation", "cred-wpml").'"><img border = "0" src = "' . CRED_WPML_GLUE_PLUGIN_URL . '/img/edit_translation.png" alt = "'. esc_attr__("edit translation", "cred-wpml").'" width = "16" height = "16"></a></td>';
                        }
                    }else{
                        $content .= '<td align = "right"><a href = "' . $control['url'] . '" title = "'. esc_attr__("edit translation", "cred-wpml").'"><img border = "0" src = "' . CRED_WPML_GLUE_PLUGIN_URL . '/img/edit_translation.png" alt = "'. esc_attr__("edit translation", "cred-wpml").'" width = "16" height = "16"></a></td>';
                }

                }
                $content .= '</tr>';
            }
        }

        if ($flag)
            $content .= '</tbody></table></div>';

        $content .= '</div>';

        return $content;
    }

    // add original content for creating translation
    // return HTML
    function cred_wpml_glue_add_original_content($post_data) {
        global $wpdb;
        if(!isset($_GET['action']))
            return '';
        
        if($_GET['action'] == 'edit_translation'){
            global $post,$sitepress;
            if(isset($post)){
                $trid = $sitepress->get_element_trid($post->ID, 'post_'. $post->post_type);
                $source_lang = $sitepress->get_default_language();
            }else{
                return '';
            }
        }elseif($_GET['action'] == 'create_translation'){
            $trid = $_GET['trid'];
            $source_lang = $_GET['source_lang'];            
        }else{
           return ''; 
        }
        
       $original_id = $wpdb->get_var($wpdb->prepare("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid=%d AND language_code=%s",$trid , $source_lang));
        
        switch ($post_data) {
            case 'post_title':                
                $out = wpml_get_original_content($original_id, 'title');                
                break;
            case 'post_content':
                $out = '<textarea id="wpml_cred_glue_original_content" >'.wpml_get_original_content($original_id, 'content').'</textarea>';
                break;
            case 'post_excerpt':
                $out = wpml_get_original_content($original_id, 'excerpt');
                break;
            case 'category':
                $categories = wpml_get_original_content($original_id, 'categories');
                $out = '';
                
                if(!empty($categories))
                foreach($categories as $key=>$category){
                    $out .= '<p>'.$category.'</p>';
                }                
                break;
            case 'post_tag':
                $tags = wpml_get_original_content($original_id, 'tags');
                $out = '';               
                foreach($tags as $key=>$tag){                    
                    $out .= '<p>'.$tag.'</p>';
                }                
                break;                  
            default:
                
                $out = '';
                $taxonomies = wpml_get_original_content($original_id, 'taxonomies',$post_data);
                
                foreach ($taxonomies as $key=>$taxonomy) {                    
                    if($key == $post_data){                        
                        foreach ($taxonomy as $tax) {
                            $out .= '<p>'.$tax.'</p>';                            
                        } 
                        break;
                    }                   
                }
                
                if(!empty($out))
                    break;
                
                $custom_fields = wpml_get_original_content($original_id, 'custom_fields',$post_data);                 
              
                if (!empty($custom_fields)) {
                    foreach ($custom_fields as $custom_field) {                          
                        if (!$this->cred_wpml_glue_check_sync(false,$post_data)) {
                            $out .= '<p>' . $custom_field . '</p>';
                        }
                    }
                }

                break;
        }
        
        if(!empty($out)){
            return '<div class="cred_frontend_translation_field">'.$out.'</div>';
        }else{
            return false;
        }       
    }

    // render original field shortcode
    function wpml_cred_glue_original_content_shortcode($atts, $content=""){       
        return $this->cred_wpml_glue_add_original_content($atts['field']);
    }
    
    // add new form type in CRED form type select
    function cred_wpml_glue_generate_translation_form_option($form_types,$settings, $form) {
        $form_types[$this->glue_form_type] = __('Form for translating content', 'cred-wpml');
        return $form_types;
    }
    
    // generate insert original content checkbox on form setting page ("Auto-generate form pop-up")
    function cred_wpml_glue_generate_insert_original_button(){
        global $post;
        $form_setting = get_post_meta($post->ID,'_cred_form_settings',true);
        $display = '';
        if(isset($form_setting->form['type']) && $form_setting->form['type'] != $this->glue_form_type){
            $display = "display:none!important";
        }
        $html = "<li id='wpml_cred_include_original' style='".$display."'><label class='cred-label'>
        <input type='checkbox'  name='wpml_cred_glue_include_original' id='wpml_cred_glue_include_original' value='1'/><span class='cred-checkbox-replace'>&nbsp;". __('Show the original content for translation','cred-wpml')."</span>
	</label></li>";      
        return $html;
    }
    
    // generate insert original content pop-up on form setting page
    function cred_wpml_glue_generate_insert_button_block($addon_buttons,$insert_after){
        global $post;
        $form_setting = get_post_meta($post->ID,'_cred_form_settings',true);
        $display = '';
        if(isset($form_setting->form['type']) && $form_setting->form['type'] != $this->glue_form_type){
            $display = "display:none!important";
        }
        $html = "<span class='cred-media-button' id='wpml_cred_include_original_block' style='".$display."'>
        <a href='javascript:void(0)' id='glue-original-fields' class='cred-button' title='".esc_attr__('Original content fields','cred-wpml')."'>".__('Original content fields','cred-wpml')."</a>";
        $html.="<div id='glue-original-shortcodes-box' class='cred-popup-box'>
        <div class='cred-popup-heading'>
        <h3>". __('Original content Fields (click to insert)','cred-wpml')."</h3>
        <a href='javascript:void(0);' title='". esc_attr__('Close','cred-wpml')."' class='cred-close-button cred-cred-cancel-close'></a>
        </div>
        <div id='glue-original-shortcodes-box-inner' class='cred-popup-inner'>";
        $all_fields = explode(',',$this->glue_get_original_field_list());
        foreach($all_fields as $field){
          $html .= "<a href='javascript:void(0);' class='button cred_field_add' rel='".$field."'>".__($field,'cred-wpml')."</a>";  
        }
                      
        $html .= "</div></div></span>";
        return $this->array_insert_after((int)$insert_after, $addon_buttons, $html);
        
    }
    
    // echo controls for translations
    function glue_generate_controls_echo(){
        $content = $this->cred_wpml_glue_front_controls();
        echo $content;
    }
    
    // add controls widget
    function glue_controls_widget_init(){
        wp_register_sidebar_widget('glue_controls_widget', __('Frontend translation controls', 'cred-wpml'), array(&$this,'generate_glue_widget_controls'));
        wp_register_widget_control('glue_controls_widget_control', __('Frontend translation controls', 'cred-wpml'), array(&$this, 'set_widget'));
    }
    
    function generate_glue_widget_controls($args){
       extract($args, EXTR_SKIP);
       echo $before_widget;
       $this->glue_generate_controls_echo();
       echo $after_widget;       
    }
    
    function set_widget(){
        
    }
    
    // add term filter when creation translation 
    function terms_clauses($clauses) {
        if (isset($_GET['action']) && $_GET['action'] == 'create_translation') {
          
            if (isset($_GET['source_lang'])) {
                $src_lang = $_GET['source_lang'];
            } else {
                global $sitepress;
                $src_lang = $sitepress->get_current_language();
            }
            if (isset($_GET['to_lang'])) {
                $lang = $_GET['to_lang'];
            } else {
                $lang = $src_lang;
            }
            $clauses['where'] = str_replace("icl_t.language_code = '" . $src_lang . "'", "icl_t.language_code = '" . $lang . "'", $clauses['where']);
        }
        return $clauses;
    }
    
    // check sync fields 
    function cred_wpml_glue_check_sync($sync,$field) {
        global $sitepress_settings;
        
       //is_translated_taxonomy        
        
        if (in_array($field, array('post_parent')) || (isset($sitepress_settings['translation-management']['custom_fields_translation'][$field]) && in_array($sitepress_settings['translation-management']['custom_fields_translation'][$field] ,array(0,1))) || (isset($sitepress_settings['taxonomies_sync_option'][$field]) && $sitepress_settings['taxonomies_sync_option'][$field] != 1)) {
            return true;
        } else{            
            return false;
        }
    }
    
    // print js scripts
    function wp_print_scripts() {
        global $wpdb, $sitepress,$sitepress_settings;
        if(!headers_sent())
            return ;
        
        if (isset($_GET['trid']) && isset($_GET['action']) && $_GET['action'] == 'create_translation' && $sitepress_settings['sync_post_taxonomies']) {
            
            $post_id = $wpdb->get_row($wpdb->prepare("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid=%d AND source_language_code IS NULL", $_GET['trid']));
            $post_type = get_post_type($post_id->element_id);           
            $source_lang = isset($_GET['source_lang']) ? $_GET['source_lang'] : $sitepress->get_default_language();
            $elem_lang = isset($_GET['to_lang']) ? $_GET['to_lang'] : $sitepress->get_default_language();
            $translatable_taxs = $sitepress->get_translatable_taxonomies(true, $post_type);
            $all_taxs = get_object_taxonomies($post_type);

            //pre-select post relationship for TYPES
            $parent_js = '';
            if(defined('WPCF_VERSION')){
                $custom_types = get_option('wpcf-custom-types');
                if($custom_types){
                    foreach($custom_types as $key => $custom_type){
                        if($key == $post_type && isset($custom_type['post_relationship']['belongs'])){
                            //parents
                            $belongs = $custom_type['post_relationship']['belongs'];
                            foreach ($belongs as $key => $belong) {
                                $parent_id = get_post_meta($post_id->element_id,'_wpcf_belongs_'.$key.'_id',true);                                
                                if($parent_id){
                                    $trsl_parent = icl_object_id($parent_id, $key, false, $elem_lang);
                                    if($trsl_parent){
                                       $sel_name = 'name="_wpcf_belongs_'.$key.'_id"';
                                       $value = 'value = "'.$trsl_parent.'"';
                                       $parent_js .= "jQuery('select[".$sel_name."] option').removeAttr('selected'); 
                                           jQuery('select[".$sel_name."] option[".$value."]').attr('selected','selected'); "; 
                                    }
                                }
                                    
                            }
                        }elseif(isset($custom_type['post_relationship']['has'])){
                            //childs   
                            $parent_post_type = $key;
                            $childs = $custom_type['post_relationship']['has'];
                            foreach ($childs as $key => $child) {                                
                                if($key == $post_type){
                                    $parent_id = get_post_meta($post_id->element_id,'_wpcf_belongs_'.$parent_post_type.'_id',true);                                    
                                    if($parent_id){
                                        $trsl_parent = icl_object_id($parent_id, $parent_post_type, false, $elem_lang);
                                        if($trsl_parent){                                            
                                            $sel_name = 'name="_wpcf_belongs_'.$parent_post_type.'_id"';
                                            $value = 'value = "'.$trsl_parent.'"';
                                            $parent_js .= "jQuery('select[".$sel_name."] option').removeAttr('selected'); 
                                                jQuery('select[".$sel_name."] option[".$value."]').attr('selected','selected'); "; 
                                        }
                                    }
                                }
                                    
                            }
                            
                        }                        
                    }
                }
            }
            
            $translations = $sitepress->get_element_translations($_GET['trid'], 'post_' . $post_type);

            $js = array();
            if (!empty($all_taxs))
                foreach ($all_taxs as $tax) {
                    $tax_detail = get_taxonomy($tax);
                    $terms = get_the_terms($translations[$source_lang]->element_id, $tax);
                    $term_names = array();
                    if ($terms)
                        foreach ($terms as $term) {
                        
                            if ($tax_detail->hierarchical) {
                               
                                if (in_array($tax, $translatable_taxs)) {
                                    
                                    $term_id = icl_object_id($term->term_id, $tax, false,$elem_lang);
                                   
                                } else {
                                    $term_id = $term->term_id;
                                }
                                $input = 'input[name="' . $tax . '[]"]';
                                $js[] = "jQuery('".$input."').each(function(){if(jQuery(this).val() == ".$term_id.")jQuery(this).attr('checked', 'checked');});";
                            } else {
                                if (in_array($tax, $translatable_taxs)) {
                                    $term_id = icl_object_id($term->term_id, $tax, false,$elem_lang);
                                   
                                    if ($term_id) {
                                         remove_filter('get_term', array($sitepress,'get_term_adjust_id'), 1);
                                        $term = get_term_by('id', $term_id, $tax);
                                        add_filter('get_term', array($sitepress,'get_term_adjust_id'), 1, 1);
                                      
                                        $term_names[$tax] = esc_js($term->name);
                                    }
                                } else {
                                    $term_names[$tax] = esc_js($term->name);
                                }
                            }
                        }

                    if ($term_names) {                        
                        foreach($term_names as $key=>$term_name){
                            $input = 'input[name="' . $key . '"]';
                            $js[] = "jQuery('".$input."').parent().find('input.myzebra-text').val('" .$term_name . "');";
                        }
                    }
                }
           
                echo '<script type="text/javascript">';
                echo PHP_EOL . '// <![CDATA[' . PHP_EOL;
                echo 'addLoadEvent(function(){' . PHP_EOL;
                
                echo PHP_EOL . 'jQuery(document).ready(function() { ';
                if ($js) {
                echo join(PHP_EOL, $js);
                }                                  
                echo $parent_js;
                
                if ($js) {
                echo 'jQuery(".myzebra-add-new-term").click();
                    jQuery(\'html, body\').prop({scrollTop:0});';
                }
                
                echo 'if(jQuery("#wpml_cred_glue_original_content").size() > 0){
                    
                       jQuery("#wpml_cred_glue_original_content").cleditor({
                        width:        500, 
                        height:       250,
                       controls:     // controls to add to the toolbar
                        " source ",
                        });
                        
                        jQuery("#wpml_cred_glue_original_content").closest(".cred_frontend_translation_field").css("display","inline-block");
                    }
                    });' . PHP_EOL;
                 echo '});' . PHP_EOL;
                echo PHP_EOL . '// ]]>' . PHP_EOL;
                echo '</script>';
       
        }elseif(isset($_GET['action']) && $_GET['action'] == 'edit_translation'){
            echo '<script type="text/javascript">';
            echo PHP_EOL . '// <![CDATA[' . PHP_EOL;
            echo 'addLoadEvent(function(){' . PHP_EOL;
            echo PHP_EOL . 'jQuery(document).ready(function() { ';
            echo 'if(jQuery("#wpml_cred_glue_original_content").size() > 0){

                    jQuery("#wpml_cred_glue_original_content").cleditor({
                    width:        500, 
                    height:       250,
                    controls:     // controls to add to the toolbar
                    " source ",
                    });                        
                    jQuery("#wpml_cred_glue_original_content").closest(".cred_frontend_translation_field").css("display","inline-block");
                }
                });
                });' . PHP_EOL;
           
            echo PHP_EOL . '// ]]>' . PHP_EOL;
            echo '</script>';
        }
    }
    
    // check user privileges
    function cred_wpml_glue_check_user_privileges() {
        global $sitepress, $wpdb;
        //check if user can create translation
        if (!get_current_user_id() || (isset($_GET['action']) && $_GET['action'] == 'create_translation' && isset($_GET['source_lang']) && isset($_GET['to_lang']) 
                && !wpml_check_user_is_translator($_GET['source_lang'], $_GET['to_lang']))
                || (get_current_user_id() && get_user_meta(get_current_user_id(), 'wpml_cred_user_option', true) == 'admin'))
            return false;
        
                
        //check if user can edit post in current language
        if (isset($_GET['action']) && $_GET['action'] == 'edit_translation' && get_current_user_id()) {
            $translation_languages = $wpdb->get_row($wpdb->prepare("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s",get_current_user_id(),$wpdb->prefix.'language_pairs'));
            $current_lang = $sitepress->get_current_language();
            foreach (unserialize($translation_languages->meta_value) as $key => $language) {
                if ($key == $current_lang || key_exists($current_lang, $language))
                    return true;
            }
            return false;
        }

        return true;
    }
    
    
    //check is translated and unique post type
    //$post_type = string (exmpl.: post,page, etc.)
    //returned bool (true - post_type is translated and unique,false - post_type is not translated or not unique)
    function cred_wpml_glue_is_translated_and_unique_post_type($post_type) {
        global $sitepress,$wpdb;
        
        if(isset($_GET['post'])){
            $post_id = $_GET['post'];
            $current_form = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_cred_form_settings' AND post_id = %d",$post_id));
            $curent_form_settings = unserialize($current_form);       
        
            if (!empty($curent_form_settings) && $curent_form_settings->form['type'] == $this->glue_form_type) {
            $forms = $this->cred_wpml_glue_get_translation_forms();
                $form_wizard = get_post_meta($post_id, '_cred_wizard', true);
            foreach ($forms as $form_post_type) {            
                    if(($form_post_type == $post_type && isset($curent_form_settings) && $curent_form_settings->post['post_type'] != $form_post_type)){                                         
                    return false;
                }
            }            
            return (bool)$sitepress->is_translated_post_type($post_type);
        }
        }      
      return true;
        
    }
    
    //get all translation forms
    //return array('form_id'=>post_type)) of translation forms
    function cred_wpml_glue_get_translation_forms() {
        global $wpdb;
        $translated_forms = array();
        $forms = $wpdb->get_results($wpdb->prepare("SELECT post_id,meta_value FROM $wpdb->postmeta WHERE meta_key = %s ORDER BY post_id DESC", '_cred_form_settings'));
        foreach ($forms as $form) {
            $form_settings = unserialize($form->meta_value);
            if (isset($form_settings->post['post_type']) && $form_settings->form['type'] == $this->glue_form_type) {
                $translated_forms[$form->post_id] = $form_settings->post['post_type'];
            }
        }
        return $translated_forms;
    }
    
    //auto complete fields when form creating from glue plugin on settings page
    function cred_wpml_glue_set_form_info() {  
        global $wpdb,$pagenow;
      
        if (isset($_GET['post']) && isset($_GET['action']) && $_GET['action'] == 'edit') {
            $post_id = $_GET['post'];     
            $form_wizard = get_post_meta($post_id, '_cred_wizard', true);

            if (get_post_type($post_id) == 'cred-form'){                
                
                $current_form = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_cred_form_settings' AND post_id = %d ",$post_id ));
                $curent_form_settings = unserialize($current_form);     
                
                $forms = $this->cred_wpml_glue_get_translation_forms();
                $js = '';
                $js_add = '';
                $action_after_submit_form = "'post'";
                

                foreach ($forms as $form_post_type) {
                    if((!empty($form_post_type) && (isset($curent_form_settings) && (isset($curent_form_settings->post['post_type']) && $curent_form_settings->post['post_type'] != $form_post_type) || $curent_form_settings->form['type'] != $this->glue_form_type ))){
                        $value = "value = '".$form_post_type."'";
                        $js .= 'jQuery("select#cred_post_type option['.$value.']").hide();';
                        $js_add .= 'jQuery("select#cred_post_type option['.$value.']").show();';
                    }
                }
               
                $orig_fields = $this->glue_get_original_field_list();

                echo '<script type="text/javascript">';
                echo PHP_EOL . '// <![CDATA[' . PHP_EOL;
                echo 'addLoadEvent(function(){' . PHP_EOL;
                echo PHP_EOL . 'jQuery(document).ready(function() {
                    
                fill_fields();
                if(jQuery("select#cred_form_type").val() == "'.$this->glue_form_type.'"){
                    jQuery("#wpml_cred_include_original,#wpml_cred_include_original_block").attr("style","display:inline-block!important");   
                }
                jQuery("select#cred_form_type").change(function(){
                    if(jQuery(this).val() == "'.$this->glue_form_type.'"){
                        jQuery("#wpml_cred_include_original,#wpml_cred_include_original_block").attr("style","display:inline-block!important");
                        jQuery("select#cred_post_type option").removeAttr("selected");
                        jQuery("select#cred_post_type option[value=\"\"]").remove();
                        jQuery("select#cred_post_type").prepend("<option value=\"\"></option>");
                        jQuery("select#cred_post_type option[value=\"\"]").attr("selected","selected");                         
                        '.$js.'                    
                    }else{
                    jQuery("#wpml_cred_include_original,#wpml_cred_include_original_block").attr("style","display:none!important");
                        jQuery("select#cred_post_type option[value=\"\"]").remove();
                        '.$js_add.'
                    }
                });

                jQuery("#cred-wizard-button-next,#cred-wizard-button-prev").click(function(){
                    fill_fields();                    
                });

                jQuery("#glue-original-fields").live("click",function(){
                        jQuery("#glue-original-shortcodes-box").parent("span").css("z-index", "100");
                        jQuery("#glue-original-shortcodes-box").css("display", "block");
                    });
                    
                jQuery("#glue-original-shortcodes-box-inner a.cred_field_add").live("click",function(){
                    InsertAtCursor(document.getElementById("content"),"[cred_frontend_translation_original_content field=\""+jQuery(this).attr("rel")+"\"]");
                    jQuery("#glue-original-shortcodes-box").css("display", "none");
                });

                function fill_fields(){
                    
                    var post_type_cookie = get_cookie("_glue_post_type");

                    if(typeof(post_type_cookie) != "undefined" && post_type_cookie != "null"){  
                        
                        jQuery("select#cred_form_type").removeAttr("selected");
                        jQuery("select#cred_form_type option[value=\"translation\"]").attr("selected","selected");
                        jQuery("select#cred_form_type").change();

                        jQuery("select#cred_post_type").removeAttr("selected");
                        jQuery("select#cred_post_type option[value=\""+post_type_cookie+"\"]").attr("selected","selected");
                        jQuery("select#cred_post_type").change();
                        
                    jQuery("select#cred_form_success_action option[value='.$action_after_submit_form.']").attr("selected","selected");
                    jQuery("select#cred_form_success_action").change();
                    jQuery("#cred-wizard-button-next").removeAttr("disabled");
                   
                }

                    jQuery("#cred-scaffold-button-button").click(function(){               
                        if(jQuery("select#cred_form_type").val() == "'.$this->glue_form_type.'" && !jQuery("#cred-scaffold-box").is(":visible")){                            
                            if(jQuery("#wpml_cred_glue_include_original").is(":checked")){
                                setTimeout(function(){
                                if(jQuery("#cred_include_wpml_scaffold").is(":checked")){
                                    auto_insert_shortcodes(1);
                                }else{
                                    auto_insert_shortcodes();
                                }},100);
                            }else{
                                setTimeout(function(){jQuery("#wpml_cred_glue_include_original").click();},100);
                            }
                        }
                    });

                    jQuery("#wpml_cred_glue_include_original").live("click",function(){
                    if(jQuery(this).val() == "1"){
                    jQuery(this).val("0");
                                if(jQuery("#cred_include_wpml_scaffold").is(":checked")){
                                    auto_insert_shortcodes(1);
                                }else{
                                auto_insert_shortcodes();
                                }
                            }else{
                                jQuery(this).val("1");
                                if(jQuery("#cred_include_wpml_scaffold").is(":checked")){
                                   auto_delete_shortcodes(1);
                                }else{
                                auto_delete_shortcodes();
                            }                        
                            }                        
                    });                 
                    
                    jQuery("#cred_include_wpml_scaffold").live("click",function(){
                        if(jQuery(this).is(":checked")){
                           auto_insert_shortcodes(1);
                        }else{
                           auto_insert_shortcodes();
                        }
                    });

                 }
                 
                function auto_insert_shortcodes(localization){
                    localization = (typeof localization == "undefined")?"":localization;
                        var form_value = jQuery("#cred-scaffold-area").val();
                        if(form_value != ""){
                            var str = "'.$orig_fields.'";
                            var arr = str.split(",");
                           
                            for(var i in arr){
                            
                            var regexp = new RegExp();
                            var field = arr[i].replace(/^wpcf-/,"");
                            
                            regexp.compile("(\[cred_field[ ]+field=\""+field+"\".*?\])");   

                                var label = original_field_label(field);
                                
                                if(localization){
                                    label = "<div class=\"cred-label\">[wpml-string context=\"cred-form-translation form for '.$curent_form_settings->post['post_type'].'-'.$post_id.'\" name=\"original_"+field+"\"]"+label+"[/wpml-string]</div>";
                                }else{
                                    label = "<div class=\"cred-label\">"+label+"</div>";
                                }
                                form_value = form_value.replace(regexp,"\$1"+"\n\t\t"+label+"\n\t\t[cred_frontend_translation_original_content field=\""+arr[i]+"\"]");

                            }
                            jQuery("#cred-scaffold-area").val(form_value); 

                        }
                }
                
                function original_field_label(field){
                                switch(field){
                                    case "post_title":
                                      label = "Original Name:";
                                      break;
                                    case "post_content":
                                      label = "Original Description:";
                                      break;
                                    case "post_excerpt":
                                      label = "Original Excerpt:";
                                      break;
                                    case "category":
                                      label = "Original Categories:";
                                      break;
                                    case "post_tag":
                                      label = "Original Tags:";
                                      break; 
                                    default:
                                      label = "Original "+field+"s:";
                                      break;  
                                }
                    return label;
                }
                
                function auto_delete_shortcodes(localization){
                    localization = (typeof localization == "undefined")?"":localization;
                        var form_value = jQuery("#cred-scaffold-area").val();
                        if(form_value != ""){
                            var str = "'.$orig_fields.'";
                            var arr = str.split(",");

                            for(var i in arr){
                            
                            var regexp = new RegExp();
                            var field = arr[i].replace(/^wpcf-/,"");
                            
                                var label = original_field_label(field);
                                
                                if(localization){
                                    form_value = form_value.replace(\'\n\t\t<div class="cred-label">\[wpml-string context="cred-form-translation form for '.$curent_form_settings->post['post_type'].'-'.$post_id.'" name="original_\'+field+\'"\]\'+label+\'\[\/wpml-string\]<\/div>\',"");
                                }else{
                                    form_value = form_value.replace(\'\n\t\t<div class="cred-label">\'+label+\'</div>\',"");
                                }
                                
                                regexp.compile("\n\t\t\[cred_frontend_translation_original_content[ ]+field=\""+field+"\"[\s\S]*?]");   

                            form_value = form_value.replace(regexp,"");
                            
                            }
                           jQuery("#cred-scaffold-area").val(form_value);
                            }
                }
               
                function get_cookie(name){
                    var i,x,y,ARRcookies=document.cookie.split(";");
                        for (i=0;i<ARRcookies.length;i++) {
                            x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
                            y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
                            x=x.replace(/^\s+|\s+$/g,"");
                            if (x==name)
                                return unescape(y);
                        }
                }

                    function InsertAtCursor(myField, myValue1, myValue2){  
                    
                        cred_cred.getCodeMirror().focus()   
                        if (!cred_cred.getCodeMirror().somethingSelected())
                        {
                            var current_cursor=cred_cred.getCodeMirror().getCursor(true);
                            cred_cred.getCodeMirror().setSelection(current_cursor, current_cursor);
                        }
                        if (myValue2) 
                            cred_cred.getCodeMirror().replaceSelection(myValue1 + codemirror.getSelection() + myValue2);
                        else
                            cred_cred.getCodeMirror().replaceSelection(myValue1);

                        jQuery(myField).trigger("paste");
                    } 

                });' . PHP_EOL;
                echo PHP_EOL . '});' . PHP_EOL;
                echo PHP_EOL . '// ]]>' . PHP_EOL;
                echo '</script>';
            }
        
        if(($pagenow == 'post.php' && isset($_GET['action']) && $_GET['action'] == 'edit' && (!$form_wizard || $form_wizard < 0))){            
            $this->set_cookie_to_null();
        }
        
            
        }
        
        if($pagenow == 'post-new.php' && !isset($_GET['glue_post_type']) && isset($_GET['post_type']) && $_GET['post_type'] == 'cred-form'){
           $this->set_cookie_to_null(); 
        }
        
        if (isset($_GET['glue_post_type'])) {
            echo '<script type="text/javascript">';
            echo PHP_EOL . '// <![CDATA[' . PHP_EOL;
            echo 'addLoadEvent(function(){' . PHP_EOL;
            echo PHP_EOL .'jQuery(document).ready(function() {
                jQuery("#titlewrap input").focus(); 
                jQuery("#titlewrap input").val("'.esc_js(__("Translation form for ".$_GET["glue_post_type"], "cred-wpml")).'"); 
                jQuery("#title").trigger("keyup");                   
                
            });
            document.cookie = "_glue_post_type='.$_GET['glue_post_type'].'";' . PHP_EOL;
            echo PHP_EOL . '});' . PHP_EOL;
            echo PHP_EOL . '// ]]>' . PHP_EOL;
            echo '</script>';            
        }
    }
    
    function set_cookie_to_null(){
        echo '<script type="text/javascript">';
            echo PHP_EOL . '// <![CDATA[' . PHP_EOL;
            echo 'addLoadEvent(function(){' . PHP_EOL;
            echo PHP_EOL .'
                document.cookie = "_glue_post_type=null";
           ' . PHP_EOL;
            echo PHP_EOL . '});' . PHP_EOL;
            echo PHP_EOL . '// ]]>' . PHP_EOL;
            echo '</script>'; 
    }
    
    //get original fields list
    function glue_get_original_field_list() {
        //original fields
        $orig_fields = "post_title,post_content,post_excerpt";
        $taxonomies = get_taxonomies();
        $exclude = array('nav_menu', 'link_category', 'post_format');
        $taxonomies = array_diff($taxonomies, $exclude);

        sort($taxonomies, SORT_STRING);

        foreach ($taxonomies as $taxonomy) {
            $orig_fields .= ',' . $taxonomy;
        }

        $fm = CRED_Loader::get('MODEL/Fields');

        $custom_fields = $fm->getPostTypeCustomFields('post', array(), false, 1);

        foreach ($custom_fields as $custom_field) {
            $orig_fields .= ',' . $custom_field;
        }
        
        return $orig_fields;
    }
    
    
 /*
 * Inserts a new key/value after the key in the array.
 *
 * @param $position
 *   The position to insert after.
 * @param $array
 *   An array to insert in to.
 * @param $new_value
 *   An value to insert.
 *
 * @return
 *   The new array if the key exists, FALSE otherwise.
 *
 */
function array_insert_after($position, array &$array, $new_value) { 
    
            $new = array();
            
            $new = array_slice($array, 0, (int)$position, true);

            $new += array('original_button' => $new_value);

            $new += array_slice($array, (int)$position, count($array)-(int)$position, true);

            return $new;
    }
    
 
 /*
  * Redirect to edit post if selected "Keep displaying this form" in form settings   
 */ 
 function cred_wpml_glue_submit_complete($result, $thisform){
     if($thisform['form_type'] == $this->glue_form_type){
            
         $form_setting = get_post_meta($thisform['id'],'_cred_form_settings',true);
            if(isset($form_setting) && !empty($form_setting->form['action'])){
             define('ICL_DOING_REDIRECT',1);
             $permalink = get_permalink($result);               
                if($form_setting->form['action'] == 'form'){
                    global $sitepress_settings;
                    if(false===strpos($permalink,'?') || (false===strpos($permalink,'?') && $sitepress_settings['language_negotiation_type'] != '3')){
                        wp_redirect($permalink.'?action=edit_translation&cred-edit-form='.$thisform['id']);
                    }else{
                        wp_redirect($permalink.'&action=edit_translation&cred-edit-form='.$thisform['id']);
                    }
                    
                }elseif($form_setting->form['action'] == 'post'){
                    global $sitepress;
                    $lang = $sitepress->get_language_for_element($result, 'post_' . get_post_type($result));
                    $sitepress->switch_lang($lang);
                    wp_redirect($permalink);
                }


         }          
     }    
}   
    
    // add options for user profile page
    function add_user_profile_option(){             
        
       $html = '<h3>'.__("CRED Frontend Translation settings", "cred-wpml").'</h3>';
       $checked = get_user_meta(get_current_user_id(), 'wpml_cred_user_option', true) == 'both'?' checked="checked" ':'';
       $html .= '<input type="radio" name="wpml_cred_user_option" value="both" '.$checked.' />&nbsp;<label>'.__("Allow translating via the WordPress admin and public pages", "cred-wpml").'</label><br>';
       $checked = get_user_meta(get_current_user_id(), 'wpml_cred_user_option', true) == 'admin'?' checked="checked" ':'';
       $html .= '<input type="radio" name="wpml_cred_user_option" value="admin" '.$checked.' />&nbsp;<label>'.__("Allow translating via the WordPress admin only", "cred-wpml").'</label><br>';
       $checked = get_user_meta(get_current_user_id(), 'wpml_cred_user_option', true) == 'page'?' checked="checked" ':'';
       $html .= '<input type="radio" name="wpml_cred_user_option" value="page" '.$checked.' />&nbsp;<label>'.__("Allow translating via public pages only", "cred-wpml").'</label><br>';

       echo $html;
    }
    
    //save option in DB
    function save_user_options(){
        $user_id = $_POST['user_id'];
        if($user_id){
            update_user_meta($user_id,'wpml_cred_user_option',isset($_POST['wpml_cred_user_option']) ? $_POST['wpml_cred_user_option'] : '');
        }
    }
    
    // generate translation link
    function cred_wpml_generate_translation_link($post_id,$exist_translation,$to_lang){
        global $sitepress;
        $current_language = $sitepress->get_language_for_element($post_id, 'post_' . get_post_type($post_id));
        
        if(!wpml_check_user_is_translator($current_language,$to_lang))
            return false;
        
        $forms = $this->cred_wpml_glue_get_translation_forms();
        
        if (!empty($forms)) {
            foreach ($forms as $key => $form_post_type) {
                if (get_post_type($post_id) == $form_post_type) {
                    $form_id = $key;
                    break;
                }
            }
        }
        
        if(!isset($form_id))
            return false;

        if($exist_translation){
           $transl_post_id = icl_object_id($post_id, get_post_type($post_id), false, $to_lang);
           if($transl_post_id){
               return get_permalink($transl_post_id).'?action=edit_translation&cred-edit-form='.$form_id;
           }
        }else{   
           $trid = $sitepress->get_element_trid($post_id,'post_' . get_post_type($post_id));
           return get_permalink($post_id).'?action=create_translation&trid='.$trid.'&to_lang='.$to_lang.'&source_lang='.$current_language.'&cred-edit-form='.$form_id;
        }
    }
    
    // filter for translation link
    function cred_wpml_link_to_translation($link, $exist_translation, $to_lang){
        global $id,$sitepress_settings;

        if($sitepress_settings['translation-management']['doc_translation_method'] == ICL_TM_TMETHOD_PRO){
            return $link;        
        }else{
            $new_link = $this->cred_wpml_generate_translation_link($id,$exist_translation,$to_lang);
            if($new_link){
                return $new_link;
            }else{
                return $link;
            } 
        }
    }
    
    // potential parents filter
    function potential_parents_filter($parents, $args) {
        global $sitepress;
        
        if (isset($_GET['action'])) {
            if ($_GET['action'] == 'create_translation' && isset($_GET['to_lang'])) {
                $lang = $_GET['to_lang'];
                $sitepress->switch_lang($lang);
                $parents = get_posts($args);
                $sitepress->switch_lang($_GET['source_lang']);
                return $parents;
            } else {
                return $parents;
            }
        } else {
            return $parents;
            }
    }
    
    // translation-management $trid filter 
    function wpml_tm_save_post_trid_value($trid){
        if(isset($_GET['action']) && isset($_GET['trid']) && isset($_GET['to_lang']) && $_GET['action'] == 'create_translation'){
            $trid = $_GET['trid'];              
        }
        return $trid;
    }
    
    // translation-management $lang filter
    function wpml_tm_save_post_lang_value($lang){
        if(isset($_GET['action']) && isset($_GET['trid']) && isset($_GET['to_lang']) && $_GET['action'] == 'create_translation'){              
               $lang = $_GET['to_lang'];
        }
        return $lang;
    }
    
    // sitepress $lang filter
    function wpml_save_post_lang($lang){
        if(empty($_POST['icl_post_language']) && isset($_GET['action']) && isset($_GET['to_lang']) && $_GET['action'] == 'create_translation'){
               $lang = $_GET['to_lang'];
        }
        return $lang;
    }
    
    // sitepress $trid filter
    function wpml_save_post_trid_value($trid,$post_status){
        if(isset($_GET['action']) && isset($_GET['trid']) && $_GET['action'] == 'create_translation' && $post_status != 'auto-draft'){
               $trid = $_GET['trid'];
        }
        return $trid;
    }
    
    // sitepress $term_lang filter
    function wpml_create_term_lang($term_lang){
        global $sitepress;
        if(isset($_GET['action'])){
            if(isset($_GET['to_lang']) && $_GET['action'] == 'create_translation'){
               $term_lang = $_GET['to_lang'];
            }elseif($_GET['action'] == 'edit_translation'){
               $term_lang = $sitepress->get_current_language();                
            }
        }    
       return $term_lang;
    }
    
    //filter post status
    function cred_save_data($new_post_id, $thisform){
        if($thisform['form_type'] == $this->glue_form_type){
            global $wpdb,$sitepress;
            $current_form = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_cred_form_settings' AND post_id = %d ",$thisform['id'] ));
            $curent_form_settings = unserialize($current_form);
            if($curent_form_settings->post['post_status'] == 'original'){
                $trid = $sitepress->get_element_trid($new_post_id, 'post_'. get_post_type($new_post_id));
                $original_id = $wpdb->get_row($wpdb->prepare("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid=%d AND source_language_code IS NULL", $trid));
                $up_post = array();
                $up_post['ID'] = $new_post_id;
                $up_post['post_status'] = get_post_status($original_id);
                wp_update_post($up_post);
                
            }
        }        
    }
    
    //add user filter on the translation dashboard page
    //if set "Allow translating via public pages only" on the user setting page, delete this user from available translators
    function cred_wpml_translation_dashboard_check_user($translators){
        foreach($translators as $key=>$translator){
            if(get_user_meta($translator->ID, 'wpml_cred_user_option', true) == 'page'){
                unset($translators[$key]);
            }
        }
        return $translators;
    }

}