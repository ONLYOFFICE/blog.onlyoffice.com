<?php


class WPML_xliff{

	public $success;
	public $error;

	function __construct($ext = false){
			// For xliff upload or download we need to make sure other plugins are loaded first.
			$init_priority = ( isset( $_POST[ 'xliff_upload' ] ) || ( isset( $_GET[ 'wpml_xliff_action' ] ) && $_GET[ 'wpml_xliff_action' ] == 'download' ) ) ? 1501 : 10;
			add_action('init', array($this,'init'), $init_priority );
    }

    function __destruct(){
        return;
    }

	function init() {

		$this->attachments = array();

		$this->error = null;

		$this->plugin_localization();

		// Check if WPML is active. If not display warning message and don't load WPML-media
		if ( !defined( 'ICL_SITEPRESS_VERSION' ) ) {
			add_action( 'admin_notices', array( $this, '_no_wpml_warning' ) );

			return false;
		} elseif ( version_compare( ICL_SITEPRESS_VERSION, '2.0.5', '<' ) ) {
			add_action( 'admin_notices', array( $this, '_old_wpml_warning' ) );

			return false;
		}

		if ( is_admin() ) {

			if ( !defined( 'DOING_AJAX' ) ) {
				wp_enqueue_script( 'wpml-xliff-scripts', WPML_XLIFF_URL . '/res/js/scripts.js', array( 'jquery' ), WPML_XLIFF_VERSION );
			}

			add_action( 'admin_head', array( $this, 'js_scripts' ) );

			add_action( 'icl_ajx_custom_call', array( $this, 'ajax_calls' ), 10, 2 );

			global $sitepress, $sitepress_settings;

			if ( !$sitepress->get_setting( 'xliff_newlines' ) ) {
				$sitepress->set_setting( 'xliff_newlines', WPML_XLIFF_NEWLINES_REPLACE );
			}

			if ( 1 < count( $sitepress->get_active_languages() ) ) {

				add_filter( 'WPML_translation_queue_actions', array( $this, 'translation_queue_add_actions' ) );
				add_action( 'WPML_translation_queue_do_actions_export_xliff_12', array( $this, 'translation_queue_do_actions_export_xliff' ), 10, 1 );
				add_action( 'WPML_translation_queue_do_actions_export_xliff_11', array( $this, 'translation_queue_do_actions_export_xliff' ), 10, 1 );
				add_action( 'WPML_translation_queue_do_actions_export_xliff_10', array( $this, 'translation_queue_do_actions_export_xliff' ), 10, 1 );

				add_action( 'WPML_translator_notification', array( $this, 'translator_notification' ), 10, 0 );

				add_filter( 'WPML_new_job_notification', array( $this, 'new_job_notification' ), 10, 2 );
				add_filter( 'WPML_new_job_notification_body', array( $this, 'new_job_notification_body' ), 10, 2 );
				add_filter( 'WPML_new_job_notification_attachments', array( $this, 'new_job_notification_attachments' ) );
			}

			if ( isset( $_GET[ 'wpml_xliff_action' ] ) && $_GET[ 'wpml_xliff_action' ] == 'download' && $_GET[ 'nonce' ] = wp_create_nonce( 'xliff-export' ) ) {
				$this->export_xliff();
			}

			if ( isset( $_POST[ 'xliff_upload' ] ) ) {
				$this->error = $this->import_xliff( $_FILES[ 'import' ] );
				if ( is_wp_error($this->error) ) {
					add_action( 'admin_notices', array( $this, '_error' ) );
				}
			}

			if ( isset( $_POST[ 'icl_tm_action' ] ) && $_POST[ 'icl_tm_action' ] == 'save_notification_settings' ) {
				$include_xliff = false;
				if ( isset( $_POST[ 'include_xliff' ] ) && $_POST[ 'include_xliff' ] ) {
					$include_xliff = true;
				}

				$sitepress->save_settings( array( 'include_xliff_in_notification' => $include_xliff ) );
				$sitepress_settings[ 'include_xliff_in_notification' ] = $include_xliff;
			}
		}

		return true;
	}
		
	function ajax_calls($call, $data){
        global $sitepress_settings, $sitepress;
				switch($call){
					case 'set_xliff_newlines':
						$method = intval($data['icl_xliff_newlines']);
						$iclsettings['xliff_newlines'] = $method;
						$sitepress->save_settings($iclsettings);
						echo json_encode(array('message'=>'OK', 'value_saved' => $method));
						break;
				}
	}
	
	function new_job_notification($mail, $job_id) {
		global $sitepress_settings;
		
		if (isset($sitepress_settings['include_xliff_in_notification']) && $sitepress_settings['include_xliff_in_notification']) {
			$xliff_file = $this->get_xliff_file($job_id);
		
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			$temp_dir = get_temp_dir();
		
			$file_name = $temp_dir . get_bloginfo( 'name' ) . '-translation-job-' . $job_id . '.xliff';
			
			$fh = fopen($file_name, 'w');
			if ($fh) {
				fwrite($fh, $xliff_file);
				fclose($fh);
				$mail['attachment'] = $file_name;
				
				$this->attachments[$job_id] = $file_name;
				
				$mail['body'] .= __(' - An xliff file is attached.', 'wpml-xliff');
			}
		}		
		
		return $mail;
	}

	function new_job_notification_body($body, $tj_url) {
		
		if (strpos($body, __(' - An xliff file is attached.', 'wpml-xliff')) !== FALSE) {
			$body = str_replace(sprintf(__('You can view your other translation jobs here: %s', 'sitepress'), $tj_url), sprintf(__('To return the completed translation and view other translation jobs, go here: %s', 'wpml-xliff'), $tj_url) . "\n" . sprintf(__('For help, see translator guidelines: %s', 'wpml-xliff'), 'https://wpml.org/?page_id=8021'), $body);
		}
		
		return $body;		
	}
	
	function _get_zip_name_from_attachments() {
		return $this->_get_zip_name_from_jobs(array_keys($this->attachments));
	}
	
	function _get_zip_name_from_jobs($job_ids) {
		$min_job = min($job_ids);
		$max_job = max($job_ids);
		
		if ($max_job == $min_job) {
			return get_bloginfo( 'name' ) . '-translation-job-' . $max_job . '.zip';
		} else {
			return get_bloginfo( 'name' ) . '-translation-job-' . $min_job . '-' . $max_job . '.zip';
		}
	}
	
	function new_job_notification_attachments($attachments) {

		// check for xliff attachments and add them to a zip file.

		$found = false;		

		require_once( WPML_XLIFF_PATH . '/inc/CreateZipFile.inc.php' );
		$archive = new CreateZipFile();
		
		foreach ($attachments as $index => $attachment) {
			if (in_array($attachment, $this->attachments)) {
				$fh = fopen($attachment, 'r');
				$xliff_file = fread($fh, filesize($attachment));
				fclose($fh);
				$archive->addFile($xliff_file, basename($attachment));

				unset($attachments[$index]);
				$found = true;
			}
		}
		
		if ($found) {
			// add the zip file to the attachments.
			$archive_data = $archive->getZippedfile();

			require_once(ABSPATH . 'wp-admin/includes/file.php');
			$temp_dir = get_temp_dir();
		
			$file_name = $temp_dir . $this->_get_zip_name_from_attachments();
			
			$fh = fopen($file_name, 'w');
			fwrite($fh, $archive_data);
			fclose($fh);
			
			$attachments[] = $file_name;
			
		}
		return $attachments;		
	}
	
	
	
	function get_xliff_file( $job_id, $xliff_version = '1.2' ) {
		global $iclTranslationManagement, $sitepress;
		
		$new_line = "\n";
		
		$job = $iclTranslationManagement->get_translation_job((int)$job_id, false, false, 1); // don't include not-translatable and don't auto-assign
		
		$xliff_file = '<?xml version="1.0" encoding="utf-8" standalone="no"?>' . $new_line;


		if ( 0 == strcmp( $xliff_version, '1.0' ) ){
			//add DOCTYPE only for version 1.0
			$xliff_file .= '<!DOCTYPE xliff PUBLIC "-//XLIFF//DTD XLIFF//EN" "http://www.oasis-open.org/committees/xliff/documents/xliff.dtd">' . $new_line;
			$xliff_namespace = '';
		}else{
			//for other versions add namespace
			$xliff_namespace = 'xmlns="urn:oasis:names:tc:xliff:document:'.$xliff_version.'"';
		}

		$xliff_file .= '<xliff version="'.$xliff_version.'" '.$xliff_namespace .'>'. $new_line;
		$xliff_file .= '   <file original="' . $job_id . '-'. md5($job_id . $job->original_doc_id) . '" source-language="' . $job->source_language_code . '" target-language="' . $job->language_code . '" datatype="plaintext">' . $new_line;
		$xliff_file .= '      <header />' . $new_line;
		$xliff_file .= '      <body>' . $new_line;
		
		foreach ($job->elements as $element) {
			if ($element->field_translate == '1') {
				
				$field_data = $element->field_data;
				$field_data_translated = $element->field_data_translated;
				
				$field_data_translated = $iclTranslationManagement->decode_field_data($field_data_translated, $element->field_format);
				$field_data = $iclTranslationManagement->decode_field_data($field_data, $element->field_format);
				
				// check for untranslated fields and copy the original if required.
				
				if (!isset($field_data_translated) || $field_data_translated == '') {
					$field_data_translated = $field_data;
				}
				// check for empty array
				if (is_array($field_data_translated)) {
					$empty = true;
					foreach($field_data_translated as $translated_value) {
						if ($translated_value != '') {
							$empty = false;
							break;
						}

					}						
					if ($empty) {
						$field_data_translated = $field_data;
					}
				}
				if (is_array($field_data)) {
					$field_data = implode(', ', $field_data);
				}
				if (is_array($field_data_translated)) {
					$field_data_translated = implode(', ', $field_data_translated);
				}
				
				if ($field_data != '') {
					
					if ($sitepress->get_setting('xliff_newlines') == WPML_XLIFF_NEWLINES_REPLACE) {
						$field_data = str_replace("\n", '<br class="xliff-newline" />', $field_data);
						$field_data_translated = str_replace("\n", '<br class="xliff-newline" />', $field_data_translated);
					}

					$xliff_file .= '         <trans-unit resname="' . $element->field_type. '" restype="string" datatype="html" id="' . $element->field_type. '">' . $new_line;

					$xliff_file .= '            <source><![CDATA[' . $field_data . ']]></source>' . $new_line;
					
					$xliff_file .= '            <target><![CDATA[' . $field_data_translated . ']]></target>' . $new_line;
					
					$xliff_file .= '         </trans-unit>' . $new_line;
				}
			}
		}

		$xliff_file .= '      </body>' . $new_line;
		$xliff_file .= '   </file>' . $new_line;
		$xliff_file .= '</xliff>';
		
		return $xliff_file;
	}
	
	function export_xliff() {
		global $wpdb, $current_user;
		get_currentuserinfo();
		
		$data = $_GET['xliff_export_data'];
		$data = unserialize(base64_decode($data));

		//by default set xliff version to 1.2
		$xliff_version = '1.2';
		//check for required xliff version from action name
		if ( isset( $data['action2'] )) {
			$xliff_action = $data['action2'];
			//we are looking for action in format: export_xliff_VERSION
			if (strpos($xliff_action, 'export_xliff_') === 0){
				switch( ltrim( $xliff_action, 'export_xliff_') ){

					case '10' : $xliff_version = '1.0';
								break;
					case '11' : $xliff_version = '1.1';
								break;
					case '12' : $xliff_version = '1.2';
								break;
				}
			}
		};


		require_once(WPML_XLIFF_PATH . '/inc/CreateZipFile.inc.php');
	
		$archive = new CreateZipFile();
		
		$job_ids = array();
		foreach ($data['job'] as $job_id => $dummy) {
			$xliff_file = $this->get_xliff_file($job_id, $xliff_version);
			
			// assign the job to this translator
			$rid = $wpdb->get_var("SELECT rid FROM {$wpdb->prefix}icl_translate_job WHERE job_id={$job_id}");
			$wpdb->update($wpdb->prefix . 'icl_translate_job', array('translator_id' => $current_user->ID), array('job_id'=>$job_id));
			$wpdb->update($wpdb->prefix . 'icl_translation_status', 
				array('translator_id' => $current_user->ID), 
				array('rid'=>$rid)
			);
		
			$archive->addFile($xliff_file, get_bloginfo( 'name' ) . '-translation-job-' . $job_id . '.xliff');
			
			$job_ids[] = $job_id;
		}
		
		$archive_data = $archive->getZippedfile();
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment; filename=" . $this->_get_zip_name_from_jobs($job_ids));
		//header("Content-Encoding: gzip");
		header("Content-Length: ". strlen($archive_data));
		
		echo $archive_data;
		exit;
		
	}

	function _stop_redirect($location) {
		// Stop any redirects from happening when we call the
		// translation manager to save the translations.
		return null;
	}

	/**
	 * @param array $file
	 *
	 * @return bool|WP_Error
	 */
	function import_xliff($file) {
		
	  global $current_user;
	  get_currentuserinfo();

		// We don't want any redirects happening when we save the translation
		add_filter('wp_redirect', array($this, '_stop_redirect'));
		
		global $iclTranslationManagement;

		$this->success = array();
		
		$contents = array();

		// test for a zip file
		$zip_file = false;
		
		$fh = fopen($file['tmp_name'], 'r');
		$data = fread($fh, 4);
		fclose($fh);
		
		if ($data[0] == 'P' && $data[1] == 'K' && $data[2] == chr(03) && $data[3] == chr(04)) {
			$zip_file = true;
		}
		
		if ($zip_file) {
			if ( class_exists('ZipArchive')) {
				$z = new ZipArchive();
			
				// PHP4-compat - php4 classes can't contain constants
				$zopen = $z->open($file['tmp_name'], /* ZIPARCHIVE::CHECKCONS */ 4);
				if ( true !== $zopen )
					return new WP_Error('incompatible_archive', __('Incompatible Archive.'));
			
				for ( $i = 0; $i < $z->numFiles; $i++ ) {
					if ( ! $info = $z->statIndex($i) )
						return new WP_Error('stat_failed', __('Could not retrieve file from archive.'));
	
					$content = $z->getFromIndex($i);
					if ( false === $content )
						return new WP_Error('extract_failed', __('Could not extract file from archive.'), $info['name']);
		
					$contents[$info['name']] = $content;
				}
			} else {
				require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
			
				$archive = new PclZip($file['tmp_name']);
	
				// Is the archive valid?
				if ( false == ($archive_files = $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING)) )
					return new WP_Error('incompatible_archive', __('Incompatible Archive.'), $archive->errorInfo(true));
			
				if ( 0 == count($archive_files) )
					return new WP_Error('empty_archive', __('Empty archive.'));
				
				foreach($archive_files as $content) {
					$contents[$content['filename']] = $content['content'];
				}
			}
		} else {
			$fh = fopen($file['tmp_name'], 'r');
			$data = fread($fh, $file['size']);
			fclose($fh);
			$contents[$file['name']] = $data;
		}
		
		foreach ($contents as $name => $content) {
			if (!function_exists('simplexml_load_string')) {
				return new WP_Error('xml_missing', __('The Simple XML library is missing.', 'wpml-xliff'));
			}
			$xml = simplexml_load_string($content);
			
			if (!$xml) {
				return new WP_Error('not_xml_file', sprintf(__('The xliff file (%s) could not be read.', 'wpml-xliff'), $name));
			}
			
			$file_attributes = $xml->file->attributes();
			if (!$file_attributes || !isset($file_attributes['original'])) {
				return new WP_Error('not_xml_file', sprintf(__('The xliff file (%s) could not be read.', 'wpml-xliff'), $name));
			}
			
			$original = (string)$file_attributes['original'];
			list($job_id, $md5) = explode('-', $original);

			$job = $iclTranslationManagement->get_translation_job((int)$job_id, false, false, 1); // don't include not-translatable and don't auto-assign
			
			if ( !$job || ( $md5 != md5( $job_id . $job->original_doc_id ) ) ) {
				return new WP_Error('xliff_doesnt_match', __('The uploaded xliff file doesn\'t belong to this system.', 'wpml-xliff'));
			}

			if ($current_user->ID != $job->translator_id) {
				return new WP_Error('not_your_job', sprintf(__('The translation job (%s) doesn\'t belong to you.', 'wpml-xliff'), $job_id));
			}
			
			$data = array('job_id' => $job_id, 'fields' => array(), 'complete' => 1);
			
			foreach ($xml->file->body->children() as $node) {
				$attr = $node->attributes();
				$type = (string)$attr['id'];
				$source = (string)$node->source;
				$target = (string)$node->target;
				
				foreach ($job->elements as $element) {
					if ($element->field_type == $type) {
						$target = str_replace('<br class="xliff-newline" />', "\n", $target);
						if ($element->field_format == 'csv_base64') {
							$target = explode(',', $target);
						}
						$field = array();
						$field['data'] = $target;
						$field['finished'] = 1;
						$field['tid'] = $element->tid;
						$field['field_type'] = $element->field_type;
						$field['format'] = $element->field_format;
						
						$data['fields'][] = $field;
						break;
					}
				}
			}
			
			$iclTranslationManagement->save_translation($data);
			
			$this->success[] = sprintf(__('Translation of job %s has been uploaded and completed.', 'wpml-xliff'), $job_id);
		}
		
		if (sizeof($this->success) > 0) {
	        add_action('admin_notices', array($this, '_success'));
			return true;
		}

		return false;
	}
	
	function translation_queue_add_actions($actions) {
		$actions['export_xliff_12'] = __('Export XLIFF 1.2', 'wpml-xliff');
		$actions['export_xliff_11'] = __('Export XLIFF 1.1', 'wpml-xliff');
		$actions['export_xliff_10'] = __('Export XLIFF 1.0', 'wpml-xliff');
		
		return $actions;
	}

	function translation_queue_do_actions_export_xliff($data) {
		
		if (isset($data['job'])) {
			// Add an on load javascript event and redirect to a download link.
			
			$data = base64_encode(serialize($data));
			$nonce = wp_create_nonce('xliff-export');
			?>
			
			<script type="text/javascript">
				
				var xliff_export_data = "<?php echo $data; ?>";
				var xliff_export_nonce = "<?php echo $nonce; ?>";
				addLoadEvent(function(){
					window.location = "<?php echo htmlentities($_SERVER['REQUEST_URI']) ?>&wpml_xliff_action=download&xliff_export_data=" + xliff_export_data + "&nonce=" + xliff_export_nonce;
					});
							
			</script>
			
			<?php
		} else {
			$this->error = new WP_Error('xliff_no_documents', __('No translation jobs were selected for export.', 'wpml-xliff'));
	        add_action('admin_notices', array($this, '_error'));
		}
	}
	
    function menu(){
	    if(!defined('ICL_PLUGIN_PATH')) return;
        $top_page = apply_filters('icl_menu_main_page', basename(ICL_PLUGIN_PATH).'/menu/languages.php');
		
        add_submenu_page($top_page,
							__('XLIFF','wpml-xliff'), 
							__('XLIFF','wpml-xliff'), 'manage_options',
							'wpml-xliff', array($this,'menu_content'));
    }
    
    function menu_content(){
        global $wpdb;
		
        include WPML_XLIFF_PATH . '/menu/management.php';
    }

    
    function _no_wpml_warning(){
        ?>
        <div class="message error"><p><?php printf(__('WPML XLIFF is enabled but not effective. It requires <a href="%s">WPML</a> in order to work.', 'wpml-translation-management'), 
            'https://wpml.org/'); ?></p></div>
        <?php
    }
    
    function _old_wpml_warning(){
        ?>
        <div class="message error"><p><?php printf(__('WPML XLIFF is enabled but not effective. It is not compatible with  <a href="%s">WPML</a> versions prior 2.0.5.', 'wpml-translation-management'), 
            'https://wpml.org/'); ?></p></div>
        <?php
    }
	
    function _error(){
        ?>
        <div class="message error"><p><?php echo $this->error->get_error_message()?></p></div>
        <?php
    }    
	
    function _success(){
        ?>
        <div class="message updated"><p><ul>
		<?php
			foreach($this->success as $message) {
				echo '<li>' . $message . '</li>';
			}
		?>
		</ul></p></div>
        <?php
    }    
	
    // Localization
    function plugin_localization(){
        load_plugin_textdomain( 'wpml-xliff', false, WPML_XLIFF_FOLDER . '/locale');
    }

    function js_scripts(){
		global $pagenow;

		if(!defined('WPML_TM_FOLDER')) return;

		if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == WPML_TM_FOLDER . '/menu/translations-queue.php') {
	        $form_data = '<br /><form enctype="multipart/form-data" method="post" id="translation-xliff-upload" action="">';
			$form_data .= '<table class="widefat"><thead><tr><th>' . __('Import XLIFF', 'wpml-xliff') . '</th></tr></thead><tbody><tr><td>';
			
			$form_data .= '<label for="upload-xliff-file">' . __('Select the xliff file or zip file to upload from your computer:&nbsp;', 'wpml-xliff') . '</label>';
			$form_data .= '<input type="file" id="upload-xliff-file" name="import" /><input type="submit" value="' . __('Upload', 'wpml-xliff') . '" name="xliff_upload" id="xliff_upload" class="button-secondary action" />';
			
			$form_data .=  '</td></tr></tbody></table>';

			$form_data .= '</form>';
			?>
			<script type="text/javascript">
				addLoadEvent(function(){                     
					jQuery('form[name$="translation-jobs-action"]').append('<?php echo $form_data?>');
				});
			</script>
			
			<?php
		}
	}

	function translator_notification() {
		global $sitepress_settings;
		
		$checked = '';
		if (isset($sitepress_settings['include_xliff_in_notification']) && $sitepress_settings['include_xliff_in_notification']) {
			$checked = 'checked="checked"';
		}
		?>
		<input type="checkbox" name="include_xliff" id="icl_include_xliff" value="1" <?php echo $checked; ?>/>
        <label for="icl_include_xliff"><?php _e('Include XLIFF files in notification emails', 'wpml-xliff'); ?></label>
		<?php
		
	}
}

global $WPML_xliff;
if(!isset($WPML_xliff)) {
	$WPML_xliff = new WPML_xliff();
}