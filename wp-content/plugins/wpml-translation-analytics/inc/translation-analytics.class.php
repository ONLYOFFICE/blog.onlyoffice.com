<?php
/**
 * Translation Analytics
 * Holds the functionality for sending and displaying Translation Snapshots.
 */
class WPML_Translation_Analytics{
    private $messages = array();
    
    function __construct(){
        add_action('init', array($this,'init'));
        add_action('icl_send_translation_snapshots',
            array($this, 'send_translation_snapshots'));
    }
    
    function __destruct(){}
   
    /**
     * Handles plugin activation rountines.
     * Schedules a cron event to send the translation snapshots to ICanLocalize.
     */
    function plugin_activate() {
        wp_schedule_event(current_time('timestamp'), 'daily',
            'icl_send_translation_snapshots');
    }
    
    /**
     * Handles the plugin deactivation routines.
     * Removes any schedule cron event.
     */
    function plugin_deactivate() {
        wp_clear_scheduled_hook('icl_send_translation_snapshots');
    }
    
    function init(){
        $this->plugin_localization();
        // If WPML not active, doesn't load the plugin
        if(!$this->is_wpml_active()){
            return False;  
        }
        
        if(is_admin()){        
            add_action('admin_menu', array($this,'menu'));               
        }
        // add message to WPML dashboard widget
        add_action('icl_dashboard_widget_content',
        array($this, 'icl_dashboard_widget_content'));   
    }
    
    /**
     * Menu item to appear under WPML menu.
     */
    function menu(){
	    if(!defined('ICL_PLUGIN_PATH')) return;
		global $sitepress;
		if(!isset($sitepress) || (method_exists($sitepress,'get_setting') && !$sitepress->get_setting( 'setup_complete' ))) return;

		$top_page = apply_filters('icl_menu_main_page',
        basename(ICL_PLUGIN_PATH).'/menu/languages.php');
        add_submenu_page(
            $top_page, 
            __('Translation Analytics','wpml-translation-analytics'),
            __('Translation Analytics','wpml-translation-analytics'),
            'wpml_manage_translation_analytics', WPML_TRANSLATION_ANALYTICS_FOLDER.'/menu/main.php'
        );               
    }
    
    /**
     * The content to be displayed on the dashboard widget.
    */
    function icl_dashboard_widget_content(){
        ?>
        <div>
            <a href="javascript:void(0)"
            onclick="jQuery(this).parent().next('.wrapper').slideToggle();"
            style="display:block; padding:5px; border: 1px solid #eee;
                margin-bottom:2px; background-color: #F7F7F7;">
            <?php _e('Translation Analytics', 'wpml-translation-analytics') ?>      
            </a>
        </div>
        
        <div class="wrapper" style="display:none; padding: 5px 10px;
            border: 1px solid #eee; border-top: 0px; margin:-11px 0 2px 0;">
            <p>
            <?php echo __('WPML Translation Analytics allows you to see the
                status of your translations and shows you warnings when
                completion time may not be met based on planned schedule
                versus actual progress.', 'wpml-translation-analytics') ?>
            </p>
            <p>
                <a class="button secondary" href="
                    <?php echo 'admin.php?page='
                    . basename(WPML_TRANSLATION_ANALYTICS_PATH)
                    . '/menu/main.php'?>">
                    <?php echo __('View Translation Analytics',
                    'wpml-translation-analytics') ?>
                </a>
            </p>
        </div>
        <?php
    }
    
    /**
     * Alows adding messages to the plugin message area.
     *
     * @param string $text The message to be displayed
     * @param string $type The type of message to be displayed
     */
    function add_message($text, $type='updated'){
        $this->messages[] = array('type'=>$type, 'text'=>$text);        
    }
    
    /**
     * Displays the current plugin messages.
    */
    function show_messages(){
        if(!empty($this->messages)){            
            foreach($this->messages as $m){
                printf('<div class="%s fade"><p>%s</p></div>',
                    $m['type'], $m['text']);
            }
        }
    }
    
    /**
     * Create an account for the website.
     * This is used in case the website project has not been set up yet.
     */
    function create_account(){
        global $sitepress, $sitepress_settings, $wpdb;
        
        $user = array();
        $user['create_account'] = 1;
        $user['anon'] = 1;
        $user['platform_kind'] = 2;
        $user['cms_kind'] = 1;
        $user['blogid'] = $wpdb->blogid ? $wpdb->blogid : 1;
        $user['url'] = get_option('home');
        $user['title'] = get_option('blogname');
        $user['description'] =  isset($sitepress_settings['icl_site_description']) ? 
            $sitepress_settings['icl_site_description'] : '';
        $user['is_verified'] = 1;
        $user['interview_translators'] = $sitepress_settings['interview_translators'];
        $user['project_kind'] = isset($sitepress_settings['website_kind']) ? 
            $sitepress_settings['website_kind'] : 2;
        $user['pickup_type'] = intval($sitepress_settings['translation_pickup_method']);
        $user['ignore_languages'] = 1;

        if (defined('ICL_AFFILIATE_ID') && defined('ICL_AFFILIATE_KEY')) {
            $user['affiliate_id'] = ICL_AFFILIATE_ID;
            $user['affiliate_key'] = ICL_AFFILIATE_KEY;
        }
        $notifications = 0;
        if (isset($sitepress_settings['icl_notify_complete'])) {
            if ($sitepress_settings['icl_notify_complete']) {
                $notifications += 1;
            }
            if ($sitepress_settings['alert_delay']) {
                $notifications += 2;
            }
        }
        $user['notifications'] = $notifications;
      
        require_once ICL_PLUGIN_PATH . '/lib/icl_api.php';
        $icl_query = new ICanLocalizeQuery();
        list($site_id, $access_key) = $icl_query->createAccount($user);
        if (!$site_id){
            $user['pickup_type'] = ICL_PRO_TRANSLATION_PICKUP_POLLING;
            list($site_id, $access_key) = $icl_query->createAccount($user);
        }
        if ($site_id) {
            if($user['pickup_type'] == ICL_PRO_TRANSLATION_PICKUP_POLLING){
                $sitepress_settings['translation_pickup_method'] =
            ICL_PRO_TRANSLATION_PICKUP_POLLING;
            }            
            $sitepress_settings['site_id'] = $site_id;
            $sitepress_settings['access_key'] = $access_key;
            $sitepress_settings['icl_account_email'] = isset($user['email']) ?
                $user['email'] : '';
            $sitepress->get_icl_translator_status($sitepress_settings);
            $sitepress->save_settings($sitepress_settings);
        }
    }
    
    /**
     * Displays a frame showing the translation analytics obtained from
     * ICanLocalize.
     */
    function show_translation_analytics_dashboard(){
        global $sitepress, $sitepress_settings;

        // Create a new account if needed
        if (!isset($sitepress_settings['site_id'])) {
            $this->create_account();
        }

        // Try sending first translation snapshot, if nothing was sent yet
        $icl_settings = $sitepress->get_settings();
        if(!isset($icl_settings['snapshot_word_count'])){
            $this->send_translation_snapshots();
        }
        
        if (isset($sitepress_settings['site_id'])) {
            $translation_analytics_link =  ICL_API_ENDPOINT
                . '/translation_analytics/overview?'
                . 'accesskey=' . $sitepress_settings['access_key']
                . '&wid=' . $sitepress_settings['site_id']
                . '&project_id=' . $sitepress_settings['site_id']
                . '&project_type=Website' 
                . '&lc=' . $sitepress->get_locale($sitepress->get_admin_language())
                . '&from_cms=1';
            echo "<iframe id=\"ifm\" src=$translation_analytics_link></iframe>";
        } else {
            echo __('An unknown error has occurred when communicating with the ICanLocalize server. Please try again.', 'sitepress') .'<br/><br/>';
        }
    }
    
    /**
     * Sends the translation snapshot to ICanLocalize.
     * 
     * @param $array $params The parameters to be added when sending the snapshot
     */
    function send_translation_snapshot_to_icl($params){
        global $sitepress, $sitepress_settings;
    
        $params['accesskey'] = $sitepress_settings['access_key'];
        $params['website_id'] = $sitepress_settings['site_id'];
        
        require_once ICL_PLUGIN_PATH . '/lib/Snoopy.class.php';
        require_once ICL_PLUGIN_PATH . '/lib/xml2array.php';
        require_once ICL_PLUGIN_PATH . '/lib/icl_api.php';
    
        $icl_query = new ICanLocalizeQuery();
        $request = ICL_API_ENDPOINT
            . "/translation_snapshots/create_by_cms.xml";
        $response = $icl_query->_request($request, 'POST', $params);

        if (!$response){
            error_log(
                'Translation Analytics: Could not send translation snapshot:\n' 
                    . $icl_query->error()
            );
        }
    }

    /** * Creates and sends translation snapshots.  *
     * Called by the scheduled cron event.
     */
    function send_translation_snapshots(){
	    if(!defined('ICL_PLUGIN_PATH')) return;

        require_once ICL_PLUGIN_PATH . '/inc/translation-management/pro-translation.class.php';
        global $sitepress, $sitepress_settings;
        
        if (isset($sitepress_settings['site_id'])) {
            $jobs = $this->get_translation_jobs();
            $total_word_count = $this->get_translation_word_count($jobs);

            // Do not send snapshots when nothing changed
            $icl_settings = $sitepress->get_settings();
            if (isset($icl_settings['snapshot_word_count']) and 
                ($icl_settings['snapshot_word_count'] == $total_word_count)) {
                return;
            } else {
                $icl_settings['snapshot_word_count'] = $total_word_count;
                $sitepress->save_settings($icl_settings);
            }

            $datetime = new DateTime();
            if(!empty($total_word_count['total'])){
            foreach ($total_word_count['total'] as $from => $to_list){
                foreach($to_list as $to => $value){
                    // Convert language names to icanlocalize format
                    
                    $from_language_name = ICL_Pro_Translation::server_languages_map($from);
                    $to_language_name = ICL_Pro_Translation::server_languages_map($to);
                    
                    $params = array (
                        'date' => $datetime->format("Y-m-d\TH:i:s-P"),
                        'from_language_name' => $from_language_name,
                        'to_language_name' => $to_language_name,
                        'words_to_translate' => $value,
                        'translated_words' => $total_word_count['finished'][$from][$to],
                    );
                    $this->send_translation_snapshot_to_icl($params);
                }
            }}
        }
    }
    
    /**
     * Retrieves translations jobs from the database.
     * 
     * @param string $service The service used on the translation jobs
     *
     * @return array The retrieved jobs
     */
    function get_translation_jobs($service = 'local'){
	    if(!defined('ICL_PLUGIN_PATH')) return;
        require_once ICL_PLUGIN_PATH . '/inc/translation-management/translation-management.class.php';
        
        global $wpdb;
        $where = " s.status > " . ICL_TM_NOT_TRANSLATED;
        $where .= " AND s.status <> " . ICL_TM_DUPLICATE;
    
        // Getting translations for all services
        //$where .= " AND s.translation_service='{$service}'";
    
        $orderby = ' j.job_id DESC ';
        
        $jobs = $wpdb->get_results(
            "SELECT j.job_id, t.trid, t.language_code, t.source_language_code,
            l1.english_name AS language_name, l2.english_name AS source_language_name,
            s.translation_id, s.status, s.translation_service 
            FROM {$wpdb->prefix}icl_translate_job j
                JOIN {$wpdb->prefix}icl_translation_status s ON j.rid = s.rid
                JOIN {$wpdb->prefix}icl_translations t ON s.translation_id = t.translation_id
                JOIN {$wpdb->prefix}icl_languages l1 ON t.language_code  = l1.code
                JOIN {$wpdb->prefix}icl_languages l2 ON t.source_language_code  = l2.code
            WHERE {$where} AND revision IS NULL
            ORDER BY {$orderby}
            "
        );
    
        foreach($jobs as $job){
            $job->elements = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}icl_translate
                    WHERE job_id = %d  AND field_translate = 1
                    ORDER BY tid ASC", 
												$job->job_id
            ));
        $job->original_post_type = $wpdb->get_var($wpdb->prepare("
        SELECT element_type
        FROM {$wpdb->prefix}icl_translations
        WHERE trid=%d AND language_code=%s",
        $job->trid, $job->source_language_code));
        }
        return $jobs;
    }

	/**
	 *  Updates the number of words (total and finished).
	 *
	 * @param array   $total_word_count The word count for each language pair
	 * @param string  $from             The language id of the original language
	 *
	 * @param         int               The word count for the translation
	 *
	 * @param string  $content          The translation content
	 * @param boolean $finished         Indicates if the content translation is done
	 *
	 * @oaram string $to The language id of the translation language
	 *
	 * @return array
	 */
    function update_word_count(&$total_word_count, $from, $to, $content, $finished){
        require_once ICL_PLUGIN_PATH . '/inc/wpml-api.php';
        $word_count = wpml_get_word_count($content);
        $word_count = $word_count['count'];
        $total_word_count['total'][$from][$to] += $word_count;
        if($finished){
            $total_word_count['finished'][$from][$to] += $word_count;
        }
        return $word_count;
    }
   
    /**
     * Calculates the word count for each language pair according to the
     * given jobs.
     * 
     * @param array $jobs The $jobs used to count the number of words.
     *
     * @return array The word count, total and finished, for each language
     * pair
     */
    function get_translation_word_count($jobs){
        global $sitepress;
        $total_word_count = array();
        
        foreach($jobs as $job){
            $from = $job->source_language_name;
            $to = $job->language_name;
            
            // Initializes language pair word count
            if (!isset($total_word_count['total'][$from][$to])){
                $total_word_count['total'][$from][$to] = 0;
                $total_word_count['finished'][$from][$to] = 0;
            }
            
            foreach($job->elements as $element){
                $icl_tm_original_content = TranslationManagement::decode_field_data(
                $element->field_data, $element->field_format);
                $translatable_taxonomies = $sitepress->get_translatable_taxonomies(
                    false, $job->original_post_type);
                
                if($element->field_type=='tags' || $element->field_type=='categories' ||
                        in_array($element->field_type, $translatable_taxonomies)){
                    foreach($icl_tm_original_content as $k=>$c){
                        $word_count = $this->update_word_count(
                            $total_word_count, $from, $to,
                            $icl_tm_original_content[$k], $element->field_finished
                        );
                        //print $icl_tm_original_content[$k] . "($word_count words) <br>";
                    }
                } else {
                    $word_count = $this->update_word_count($total_word_count,
                        $from, $to, $icl_tm_original_content, $element->field_finished);
                    //print $icl_tm_original_content . "($word_count words) <br>";
                }
            }
        }
        return $total_word_count;
    }
    
     // Localization
    function plugin_localization(){
        load_plugin_textdomain( 'wpml-translation-analytics', false,
            WPML_TRANSLATION_ANALYTICS_FOLDER . '/locale');
    }
    
    /**
     * Checks if WPML is active. If not display a warning message.
     * Also checks if WPML is in a compatible version.
     */
    function is_wpml_active(){
        if(!defined('ICL_SITEPRESS_VERSION') || ICL_PLUGIN_INACTIVE){
            if ( !function_exists('is_multisite') || !is_multisite() ) {
                add_action('admin_notices', array($this, '_no_wpml_warning'));
            }
            return false;            
        } elseif(version_compare(ICL_SITEPRESS_VERSION, '2.0.5', '<')){
            add_action('admin_notices', array($this, '_old_wpml_warning'));
            return false;            
        }
        return true;
    }
    
    /**
     * Displays a warning in case WPML is not activated.
     */
    function _no_wpml_warning(){
        ?>
        <div class="message error"><p>
        <?php printf(__(
            'WPML Translation Analytics is enabled but not effective.
        It requires <a href="%s">WPML</a> in order to work.',
            'wpml-translation-analytics'), 'https://wpml.org/'); ?></p></div>
        <?php
    }
    
    /**
     * Display a warining in case WPML version is not compatible.
     */
    function _old_wpml_warning(){
        ?>
        <div class="message error"><p>
        <?php printf(__('WPML Translation Management is enabled but not effective.
        It is not compatible with  <a href="%s">WPML</a> versions prior 2.0.5.',
            'wpml-translation-analytics'), 'https://wpml.org/'); ?></p></div>
        <?php
    }
}
