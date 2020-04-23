<?php
/*
  Plugin Name: CRED Frontend Translation
  Plugin URI: http://wpml.org/
  Description:  CRED Frontend Translation. <a href="http://wpml.org/">Documentation</a>.
  Author: ICanLocalize
  Author URI: http://wpml.org/
  Version: 1.1
 */
add_action('plugins_loaded', 'cred_wpml_glue_load', 2);

if (defined('CRED_WPML_GLUE_VERSION'))
    return;
define('CRED_WPML_GLUE_VERSION', '1.1');
define('CRED_WPML_GLUE_PLUGIN_PATH', dirname(__FILE__));
define('CRED_WPML_GLUE_PLUGIN_FOLDER', basename(CRED_WPML_GLUE_PLUGIN_PATH));
define('CRED_WPML_GLUE_PLUGIN_URL', plugins_url() . '/' . CRED_WPML_GLUE_PLUGIN_FOLDER);
define('CRED_WPML_LOCALE_PATH',CRED_WPML_GLUE_PLUGIN_FOLDER.'/locale');
define('WPML_LOAD_API_SUPPORT',true);



require CRED_WPML_GLUE_PLUGIN_PATH . '/cred-wpml-glue.php';



function cred_wpml_glue_load() {    
    
    if(defined('ICL_SITEPRESS_VERSION')){
       global $sitepress_settings; 
       require_once ICL_PLUGIN_PATH . '/lib/xml2array.php';
        require_once ICL_PLUGIN_PATH . '/inc/translation-management/translation-management.class.php';
    }

    if (!defined('ICL_SITEPRESS_VERSION') || version_compare(ICL_SITEPRESS_VERSION,'2.9','<') || empty($sitepress_settings['setup_complete']) || !defined('CRED_FE_VERSION') || version_compare(CRED_FE_VERSION,'1.1.4','<') || !defined('WPML_TM_VERSION')) {
        add_action('admin_notices', '_no_wpml_and_cred_warning');
        return false;
    }else{
        global $wpml_cred_glue;

        $wpml_cred_glue = new WPML_CRED_Glue;
    }
}

function _no_wpml_and_cred_warning() {
    ?>
    <div class="message error"><p><?php printf(__('The CRED Frontend Translation Plugin is only effective when <a href="%s">WPML 2.9</a> (or higher), <a href="%s">CRED 1.1.4</a> (or higher) and <a href="%s">WPML Translation Management</a> are installed and active. Also, WPML must be configured.', 'cred-wpml'), 'http://wpml.org/', 'http://wp-types.com/home/cred/','http://wpml.org/');
    ?></p></div>
    <?php
}