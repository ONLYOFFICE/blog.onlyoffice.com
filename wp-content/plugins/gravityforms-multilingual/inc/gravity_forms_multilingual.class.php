<?php

define('ICL_GRAVITY_FORM_ELEMENT_TYPE','gravity_form');

class Gravity_forms_multilingual{


    function __construct($ext = false){

        add_action('init', array($this,'init'));
    }

    function __destruct(){
        return;
    }


    function init(){
    	if (!$this->required_plugins()) return;

    	/* WPML hooks */
        add_filter('WPML_get_translatable_types', array($this,'get_translatable_types'));
        add_filter('WPML_get_translatable_items', array($this,'get_translatable_items'), 10, 3);
        add_filter('WPML_get_translatable_item', array($this,'get_translatable_item'), 10, 2);
        add_filter('WPML_get_link', array($this,'get_link'), 10, 4);
        add_filter('WPML_make_external_duplicate', array($this,'make_duplicate'),10,2);
        add_filter('page_link',array($this,'gform_redirect'),10,3);
        //add_filter('post_link',array($this,'gform_redirect'),10,3);

        /* GF frontend hooks: form rendering and submission */
        add_filter('gform_pre_render', array($this, 'gform_pre_render'),10,2);
		add_filter('gform_pre_submission_filter', array($this, 'gform_pre_submission_filter'));
        add_filter('gform_notification', array($this, 'gform_notification'), 10, 3);
        add_filter('gform_field_validation',array($this,'gform_field_validation'),10,4);
        add_filter('gform_merge_tag_filter',array($this,'gform_merge_tag_filter'),10,5);

        /* GF admin hooks */
        add_action('gform_after_save_form', array($this,'update_form_translations'), 10, 2);
        add_action('gform_pre_confirmation_save', array($this,'update_confirmation_translations'),10,2);
        add_action('gform_pre_notification_save', array($this,'update_notifications_translations'),10,2);
        add_action('gform_after_delete_form', array($this,'after_delete_form'));
		add_action( 'gform_after_delete_field', array($this,'after_delete_field'), 10, 2 ); //remove translations of deleted field

    }

    /**
	*
	* Check for missing plugins
	*/

	function required_plugins() {
    	$this->missing = array();
		$allok = true;

		if(!defined('ICL_SITEPRESS_VERSION') || ICL_PLUGIN_INACTIVE || version_compare(ICL_SITEPRESS_VERSION, '2.0.5', '<')){
				$this->missing['WPML'] = 'http://wpml.org';
				$allok = false;
		}

		if(!class_exists('GFForms')){
			$this->missing['Gravity Forms'] = 'http://www.gravityforms.com/';
			$allok = false;
		}

		if(!defined('WPML_TM_VERSION')){
			$this->missing['WPML Translation Management'] = 'http://wpml.org';
			$allok = false;
		}

		if(!defined('WPML_ST_VERSION')){
			$this->missing['WPML String Translation'] = 'http://wpml.org';
			$allok = false;
		}

		if (!$allok) {
			add_action('admin_notices', array($this,'missing_plugins_warning'));
		}
		return $allok;
	}

	function missing_plugins_warning() {
		$missing = '';
		$counter = 0;
		foreach ($this->missing as $title => $url) {
			$counter ++;
			if ($counter == sizeof($this->missing)) {
				$sep = '';
			} elseif ($counter == sizeof($this->missing) - 1) {
				$sep = ' ' . __('and', 'plugin woocommerce') . ' ';
			} else {
				$sep = ', ';
			}
			$missing .= '<a href="' . $url . '">' . $title . '</a>' . $sep;
		}
	?>
		<div class="message error"><p><?php printf(__('Gravity Forms Multilingual is enabled but not effective. It requires %s in order to work.', 'plugin woocommerce'), $missing); ?></p></div>
	<?php
	}


	/**
	*
	* WPML hooks
	*/

	function gform_id($post_id) {
    	//return form id if $post_id is an 'external' GF type
    	
    	$prefix = 'external_'.ICL_GRAVITY_FORM_ELEMENT_TYPE.'_';
    	$len = strlen($prefix);
    	if (is_string($post_id) && substr($post_id,0,$len) == $prefix)
    		return (int)substr($post_id,$len);
    	return false; //not a gravity_form type
    }


    function gform_redirect($link,$post_id,$sample) {
   	   	//fix for default lang parameter settings + default wordpress permalinks
    	
    	global $sitepress;
    	$icl_settings = $sitepress->get_settings();
    	if ($icl_settings['language_negotiation_type'] == 3) {
    		$link = str_replace('&amp;lang=','&lang=',$link);
    	}
    	return $link;
    }


    function get_link($item, $id, $anchor, $hide_empty) {
        if ($item == "") {
            $id = $this->gform_id($id);
            if (!$id) 
            	return; //not ours
            
			if (false === $anchor) {
				global $wpdb;
				$g_form = $wpdb->get_row($wpdb->prepare("
					SELECT * FROM {$wpdb->prefix}rg_form WHERE id = %d", $id));
				$anchor = $g_form->title;
			}

            $item = sprintf('<a href="%s">%s</a>', 'admin.php?page=gf_edit_forms&id=' . $id, $anchor);
        }
            
        return $item;
    }

    

    function get_translatable_types($types) {
        // Tell WPML that we want gravity forms translated

        $types[ICL_GRAVITY_FORM_ELEMENT_TYPE] = 'Gravity form';

        return $types;
    }

   
    function new_external_item($g_form,$get_string_data=false) {
    	 //create a new external item for the Translation Dashboard or for translation jobs

    	$item = new stdClass();
        $item->external_type = true;
        $item->type = ICL_GRAVITY_FORM_ELEMENT_TYPE;
        $item->id = $g_form->id;
        $item->ID = $g_form->id;
        $item->post_type = ICL_GRAVITY_FORM_ELEMENT_TYPE;
        $item->post_id = 'external_' . $item->post_type . '_' . $item->id;
        $item->post_date = @$g_form->modified;
        $item->post_status = $g_form->is_active ? __('Active', 'gravity-forms-ml') : __('Inactive', 'gravity-forms-ml');
        $item->post_title = $g_form->title;
		$item->is_translation = false;

		if($get_string_data)
			$item->string_data = $this->_get_form_strings($item->id);
		return $item;
    }

    function get_translatable_item($item, $id) {
		//for TranslationManagement::send_jobs 
        
        if ($item == null) {
        	global $wpdb;
        	$id = $this->gform_id($id);
        	if (!$id) 
        		return; //not ours

			$g_form = $wpdb->get_row($wpdb->prepare("
					SELECT * FROM {$wpdb->prefix}rg_form WHERE id = %d",
                    $id));
			$item = $this->new_external_item($g_form,true);
		}
		return $item;
	}

	function get_translatable_items($items, $type, $filter) {
        //  for the Translation Dashboard

        if ($type == ICL_GRAVITY_FORM_ELEMENT_TYPE) {
        	global $wpdb, $sitepress;
        	$icl_el_type = 'post_'.ICL_GRAVITY_FORM_ELEMENT_TYPE;
        	$default_lang = $sitepress->get_default_language();

            $g_forms = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}rg_form");

            foreach($g_forms as $k=>$g_form){

            	//create item and add it to the translation table if required
             	$new_item = $this->new_external_item($g_form,false);
                $post_trid = $sitepress->get_element_trid($new_item->id, $icl_el_type);
                if (!$post_trid) {
                   $sitepress->set_element_language_details($new_item->id, $icl_el_type, false, $default_lang, null, false);
                   $post_trid = $sitepress->get_element_trid($new_item->id, $icl_el_type);
                }

                // get translation status for each item
                $post_translations = $sitepress->get_element_translations($post_trid,  $icl_el_type);

                foreach ($post_translations as $lang => $translation) {
                        $res = $wpdb->get_row("SELECT status, needs_update, md5 FROM {$wpdb->prefix}icl_translation_status WHERE translation_id={$translation->translation_id}");
                        if ($res) {
                            $_suffix = str_replace('-','_',$lang);
                            $index = 'status_' . $_suffix;
                            $new_item->$index = $res->status;
                            $index = 'needs_update_' . $_suffix;
                            $new_item->$index = $res->needs_update;
                        }
                }

                $items[] = $new_item;
           }
        }

        //error_log(__FUNCTION__.var_export($items,true));
    	return $items;
    }
               

    /**
    *
    * Functions to set up translation packages
    */

    function _get_form_keys() {
    	if (!isset($this->_form_keys)) {
    		$this->_form_keys = array('title', 'description', 'limitEntriesMessage',
						   'scheduleMessage',
						   'postTitleTemplate',
						   'postContentTemplate',
						   /*'confirmation-message', //obsolete
						   'autoResponder-subject',
						   'autoResponder-message',*/
						   'button-text',
						   'button-imageUrl',
						   'lastPageButton-text',
						   'lastPageButton-imageUrl',
						   );
    	}
    	return apply_filters('gform_multilingual_form_keys', $this->_form_keys);
    }

    function _get_field_keys() {
    	if (!isset($this->_field_keys)) {
    		$this->_field_keys = array('label', 'adminLabel', 'description', 'defaultValue', 'errorMessage');
    	}
		return apply_filters('gform_multilingual_field_keys', $this->_field_keys);
    }

	function _get_form_strings($form_id) {

		$form = RGFormsModel::get_form_meta($form_id, true);
		$form = RGFormsModel::add_default_properties($form);

		$string_data = array();

		$form_keys = $this->_get_form_keys();

		foreach ($form_keys as $key) {
			$parts = explode('-', $key);
			if (sizeof($parts) == 1) {
				if (isset($form[$key]) && $form[$key] != '') {
					$string_data[$key] = $form[$key];
				}
			} else {
				if (isset($form[$parts[0]][$parts[1]]) && $form[$parts[0]][$parts[1]] != '') {
					$string_data[$key] = $form[$parts[0]][$parts[1]];
				}
			}
		}


		///- Paging Page Names           - $form["pagination"]["pages"][i]
		if (isset($form["pagination"])) {
			foreach ($form['pagination']['pages'] as $key => $page_title) {
				$string_data['page-'.($key+1).'-title'] = $page_title;
			}
		}

		//Fields (including paging fields)
		$keys = $this->_get_field_keys();

		foreach ($form['fields'] as $id => $field) {
			if ($field['type'] != 'page') {
				foreach ($keys as $key) {
					if (isset($field[$key]) && $field[$key] != '') {
						$string_data['field-' . $field['id'] . '-' . $key] = $field[$key];
					}
				}
			}

			switch ($field['type']) {
				case 'text':
				case 'textarea':
				case 'email':
				case 'number':
				case 'section':
					break;

				case 'html':
					$string_data[$form['id'] . '_field-' . $field['id'] . '-content'] = $field['content'];
					break;

				case 'page':
					// page breaks are stored as belonging to the next page,
					// but their buttons are actually displayed in the previous page
					foreach (array('text','imageUrl') as $key) {
						if (isset($form['fields'][$id]['nextButton'][$key])) {
							$string_data['page-' . ($field['pageNumber']-1) . '-nextButton-'.$key] = $field['nextButton'][$key];
						}
						if (isset($form['fields'][$id]['previousButton'][$key])) {
							$string_data['page-' . ($field['pageNumber']-1) . '-previousButton-'.$key] = $field['previousButton'][$key];
						}
					}
					break;

				case 'select':
				case 'multiselect':
				case 'checkbox':
				case 'radio':
				case 'list':
                    if(!empty($field['choices'])) {
						foreach ($field['choices'] as $index => $choice) {
							$string_name = substr('field-' . $field['id'] . '-choice-' . $choice['text'],0,158);
							$string_data[$string_name] = $choice['text'];
						}
					}
					break;

				case 'product':
				case 'option':
                    if(!empty($field['choices'])) {
						foreach ($field['choices'] as $index => $choice) {
							$string_name = substr('product-' . $field['id'] . '-choice-' . $choice['text'],0,158);
							$string_data[$string_name] = $choice['text'];
							if (isset($choice['price'])) {
								$string_data[$string_name.'-price'] = $choice['price'];
							}
						}
					} elseif (isset($field['basePrice'])) {
						$string_data['product-'.$field['id'].'-basePrice'] = $field['basePrice'];
					}
					break;
				case 'post_custom_field':
					$string_data['field-' . $field['id'] . '-customFieldTemplate'] = $field["customFieldTemplate"];
					break;
				case 'post_category':
					if(isset($field["categoryInitialItem"])){
						$string_data['field-' . $field['id'] . '-categoryInitialItem'] = $field["categoryInitialItem"];
					}
					break;


			}

		}

		// confirmations
		foreach ($form['confirmations'] as $key => $confirm) {
			switch ($confirm['type']) {
				case 'message':
					$string_data["field-confirmation-message_".$confirm['name']] = $confirm['message']; //add prefix 'field-' to get a textarea editor box
				break;
				case 'redirect':
					$string_data["confirmation-redirect_".$confirm['name']] = $confirm['url'];
				break;
				case 'page':
					$string_data["confirmation-page_".$confirm['name']] = $confirm['pageId'];
				break;
			}
		}

		//notifications: translate only those for user submitted emails
		if (!empty($form['notifications'])){
			foreach ($form['notifications'] as $key => $notif) {
				if ($notif['toType'] == 'field' || $notif['toType'] == 'email' || $notif['toType'] == 'email') {
					$string_data["notification-subject_".$notif['name']] = $notif['subject'];
					$string_data["field-notification-message_".$notif['name']] = $notif['message'];
				}
			}
		}

		return $string_data;

	}


	/**
	*
	* Front-end form rendering
	*/

	function gform_pre_render($form,$ajax) {
		//render the form

		global $sitepress;

		$current_lang = $sitepress->get_current_language();
		if (isset($this->_current_forms[$form['id']][$current_lang])) {
			return $this->_current_forms[$form['id']][$current_lang];
		}

		$form_keys = $this->_get_form_keys();

		foreach ($form_keys as $key) {
			$parts = explode('-', $key);
			if (sizeof($parts) == 1) {
				if (isset($form[$key]) && $form[$key] != '') {
		            $form[$key] = icl_t('gravity_form', $form['id'] . '_' . $key, $form[$key]);
				}
			} else {
				if (isset($form[$parts[0]][$parts[1]]) && $form[$parts[0]][$parts[1]] != '') {
		            $form[$parts[0]][$parts[1]] = icl_t('gravity_form', $form['id'] . '_' . $key, $form[$parts[0]][$parts[1]]);
				}
			}
		}

		///- Paging Page Names           - $form["pagination"]["pages"][i]
		if (isset($form["pagination"])) {
			foreach ($form['pagination']['pages'] as $key => $page_title) {
				$form['pagination']['pages'][$key] =
					icl_t('gravity_form',$form['id'].'_page-'.($key+1).'-title',$form['pagination']['pages'][$key]);
			}
		}

		//Fields (including paging fields)
		$keys = $this->_get_field_keys();

		foreach ($form['fields'] as $id => $field) {

			foreach ($keys as $key) {
				if (isset($field[$key]) && $field[$key] != '' && $field['type'] != 'page') {
					$form['fields'][$id][$key] = icl_t('gravity_form', $form['id'] . '_field-' . $field['id'] . '-' . $key, $field[$key]);
				}
			}

			switch ($field['type']) {
				case 'text':
				case 'textarea':
				case 'email':
				case 'number':
				case 'section':
					break;

				case 'html':
					$form['fields'][$id]['content'] = icl_t('gravity_form', $form['id'] . '_field-' . $field['id'] . '-content', $field['content']);
					break;

				case 'page':
					foreach (array('text','imageUrl') as $key) {
						if (isset($form['fields'][$id]['nextButton'][$key])) {
							$form['fields'][$id]['nextButton'][$key] = icl_t('gravity_form', $form['id'] . '_page-' . ($field['pageNumber']-1) . '-nextButton-'.$key, $field['nextButton'][$key]);
						}
						if (isset($form['fields'][$id]['previousButton'][$key])) {
							$form['fields'][$id]['previousButton'][$key] = icl_t('gravity_form', $form['id'] . '_page-' . ($field['pageNumber']-1) . '-previousButton-'.$key, $field['previousButton'][$key]);
						}
					}

					break;

				case 'select':
				case 'multiselect':
				case 'checkbox':
				case 'radio':
				case 'list':
					if (!empty($field['choices'])) {
						foreach ($field['choices'] as $index => $choice) {
							$string_name = substr($form['id'] . '_field-' . $field['id'] . '-choice-' . $choice['text'], 0, 160); // limit to 160 chars
							$form['fields'][$id]['choices'][$index]['text'] = icl_t('gravity_form', $string_name, $choice['text']);
						}
					}
					break;
				case 'product':
				case 'option':
                	if(!empty($field['choices'])) {
						foreach ($field['choices'] as $index => $choice) {
							$string_name = substr($form['id'] .'_product-' . $field['id'] . '-choice-' . $choice['text'],0,160);
							$form['fields'][$id]['choices'][$index]['text'] = icl_t('gravity_form', $string_name, $choice['text']);
							if (isset($choice['price'])) {
								$form['fields'][$id]['choices'][$index]['price'] = icl_t('gravity_form',$string_name.'-price', $choice['price']);
							}
						}
					} elseif (isset($field['basePrice'])) {
						$form['fields'][$id]['basePrice'] = icl_t('gravity_form',$form['id'].'_product-'. $field['id'].'-basePrice',$field['basePrice']);
					}
					break;

				case 'post_custom_field':
					$form['fields'][$id]['customFieldTemplate'] =
					icl_t('gravity_form',$form['id'] . '_field-' . $field['id'] . '-customFieldTemplate', $field["customFieldTemplate"]);
					break;
				case 'post_category':
					$form['fields'][$id]['categoryInitialItem'] = icl_t('gravity_form',$form['id'].'_field-'.$field['id'].'-categoryInitialItem');
					break;
			}

		}

		if (isset($form['pagination']['pages'])) {
			foreach ($form['pagination']['pages'] as $key => $page_title) {
				$form['pagination']['pages'][$key] =
					icl_t('gravity_form',$form['id'].'_page-'.($key+1).'-title',$form['pagination']['pages'][$key]);
			}
			if (isset($form['pagination']['progressbar_completion_text']))
				$form['pagination']['progressbar_completion_text'] =
					icl_t('gravity_form',$form['id'].'_progressbar_completion_text',$form['pagination']['progressbar_completion_text']);

		}

		if (isset($form['lastPageButton'])) {
			$form['lastPageButton'] = icl_t('gravity_form',$form['id'].'_lastPageButton',$form['lastPageButton']);
		}

		$this->_current_forms[$form['id']][$current_lang] = $form;
		
		return $form;
	}


	function gform_pre_submission_filter($form) {
		// Translate confirmations before submission

		$form = $this->gform_pre_render($form,false);
		if (!empty($form['confirmations'])) {
			foreach($form['confirmations'] as $key => &$confirmation) {
				switch ($confirmation['type']) {
					case 'message':
						$confirmation['message'] = icl_t('gravity_form',$form['id']."_field-confirmation-message_".$confirmation['name'],$confirmation['message']);
						//error_log("$key. ".$form['confirmations'][$key]['message']);
					break;
					case 'redirect':
					global $sitepress;
						$confirmation['url'] = str_replace('&amp;lang=','&lang=',$sitepress->convert_url(
							icl_t('gravity_form',$form['id']."_confirmation-redirect_".$confirmation['name'], $confirmation['url'])));
						//error_log("Redirecting to ".$confirmation['url']);
					break;
					case 'page':
						//error_log("page ".icl_object_id(icl_t('gravity_form',$form['id']."_confirmation-page_".$confirmation['name'], $confirmation['pageId']),'page',true));
						$confirmation['pageId'] = icl_object_id(icl_t('gravity_form',$form['id']."_confirmation-page_".$confirmation['name'], $confirmation['pageId']),'page',true);
					break;
				}
			}
		}
		global $sitepress;
		$current_lang = $sitepress->get_current_language();
		$this->_current_forms[$current_lang][$form['id']] = $form;
		return $form;
	}

	/*function gform_confirmation($form) {
		//doesn't work, because GF recomputes the confirmation later from original form
		if (isset($form['confirmation'])) {
			$confirmation = $form['confirmation'];
			switch ($confirmation['type']) {
				case 'message':
					$confirmation['message'] = icl_t('gravity_form',$form['id']."_confirmation-message_".$confirmation['name'],$confirmation['message']);
					//error_log($form['confirmations'][$key]['message']);
					break;
				case 'redirect':
				global $sitepress;
					$confirmation['url'] = str_replace('&amp;lang=','&lang=',$sitepress->convert_url(
						icl_t('gravity_form',$form['id']."_confirmation-redirect_".$confirmation['name'], $confirmation['url'])));
					//error_log("Redirecting to ".$confirmation['url']);
					break;
				case 'page':
					//error_log("page ".icl_object_id(icl_t('gravity_form',$form['id']."_confirmation-page_".$confirmation['name'], $confirmation['pageId']),'page',true));
					$confirmation['pageId'] = icl_object_id(icl_t('gravity_form',$form['id']."_confirmation-page_".$confirmation['name'], $confirmation['pageId']),'page',true);
					break;
			}
		}
		$form['confirmation'] = $confirmation;
		error_log(__FUNCTION__.var_export($form['confirmation'],true));

		return $form;
	}*/

	function gform_notification($notification, $form, $lead) {
		// translate notifications
		
		//error_log("Notif ".var_export($notification,true));
		if ($form['notifications'][$notification['id']]['toType'] == 'email' || $form['notifications'][$notification['id']]['toType'] == 'field') {
			$notification['subject'] = icl_t('gravity_form',$form['id']."_notification-subject_".$notification['name'],$notification['subject']);
			$notification['message'] = icl_t('gravity_form',$form['id']."_field-notification-message_".$notification['name'],$notification['message']);

		}

		return $notification;
	}

	function gform_field_validation($result,$value,$form,$field) {
		// translate validation messages
    	if (!$result['is_valid']) {
    		$result['message'] = icl_t('gravity_form',$form['id'].'_field-'.$field['id'].'-errorMessage',$result['message']);
    	}
    	return $result;
    }

    function get_form($form_id,$lang=null) {
    	//get translated form
    	global $sitepress;
    	if (!$lang) $lang = $sitepress->get_current_language();
    	if (isset($this->_current_forms[$form_id][$lang]))
    		return $this->_current_forms[$form_id][$lang];
    	return $this->gform_pre_render(RGFormsModel::get_form_meta($form_id),false);
    }

    function gform_merge_tag_filter($value, $input_id, $match, $field, $raw_value) {
     	//get translated field value to use with merge tags

    	global $sitepress;

    	//error_log(__FUNCTION__."$value $input_id ".var_export($raw_value,true));
    	if (RGFormsModel::get_input_type($field)!= 'multiselect') {
    		return $value;
    	}

    	$options = array();
    	$value = explode(',',$value);
    	foreach ($value as $selected) {
    		$options[] = GFCommon::selection_display($selected, $field,$currency=NULL,$use_text=true);
    	}

    	return implode(', ',$options);
    }

    /**
    *
    * Functions to update translations when forms are modified in admin
    */

    function update_icl_translate($rid,$post) {

    	global $wpdb, $iclTranslationManagement;

		$job_id = $wpdb->get_var($wpdb->prepare("SELECT MAX(job_id) FROM {$wpdb->prefix}icl_translate_job WHERE rid=%d GROUP BY rid", $rid));
        $elements = $wpdb->get_results($wpdb->prepare("SELECT field_type, field_data, tid, field_translate FROM {$wpdb->prefix}icl_translate
        												WHERE job_id=%d",$job_id),OBJECT_K);

        foreach ($post->string_data as $field_type => $field_value) {
        	$field_data = base64_encode($field_value);
        	if (!isset($elements[$field_type])) {
        		//insert new field

        		$data = array(
	                'job_id'            => $job_id,
	                'content_id'        => 0,
	                'field_type'        => $field_type,
	                'field_format'      => 'base64',
	                'field_translate'   => 1,
	                'field_data'        => $field_data,
	                'field_data_translated' => 0,
	                'field_finished'    => 0
            	);

        		$wpdb->insert($wpdb->prefix.'icl_translate', $data);
        	} elseif ($elements[$field_type]->field_data != $field_data) {
        		//update field value
        		$wpdb->update($wpdb->prefix.'icl_translate',
                        array('field_data'=>$field_data, 'field_finished'=>0),
                        array('tid'=>$elements[$field_type]->tid)
                );
        	}
        }

        foreach ($elements as $field_type => $el) {
        	//delete fields that are no longer present
        	if ($el->field_translate && !isset($post->string_data[$field_type])) {
        		$wpdb->delete($wpdb->prefix.'icl_translate',array('tid' => $el->tid),array('%d'));
        	}
        }
    }


	/**
	 * Update translations
	 *
	 * @param array $form - form information
	 * @param bool  $is_new - set to true for newly created form (first save without fields)
	 * @param bool  $needs_update - when deleting single field we do not need to change the translation status of the form
	 */
	function update_form_translations( $form, $is_new, $needs_update = true ) {

		global $sitepress, $wpdb, $iclTranslationManagement;

    	$post_id = 'external_'.ICL_GRAVITY_FORM_ELEMENT_TYPE.'_'.$form['id'];
    	$post = $this->get_translatable_item(null,$post_id);
    	$default_lang = $sitepress->get_default_language();
    	$icl_el_type = 'post_'.ICL_GRAVITY_FORM_ELEMENT_TYPE;
    	$trid = $sitepress->get_element_trid($form['id'], $icl_el_type);

    	if ($is_new) {
    		$sitepress->set_element_language_details($post->id, $icl_el_type, false, $default_lang, null, false);
			//for new form nothing more to do
			 return;
    	}

        $sql = "
        	SELECT t.translation_id, s.md5 FROM {$wpdb->prefix}icl_translations t
        		NATURAL JOIN {$wpdb->prefix}icl_translation_status s
        	WHERE t.trid=%d AND t.source_language_code IS NOT NULL";
        $element_translations = $wpdb->get_results( $wpdb->prepare( $sql, $trid ) );

        if ( !empty( $element_translations ) ) {

        	$md5 = $iclTranslationManagement->post_md5($post);

        	if ($md5 != $element_translations[0]->md5) { //all translations need update

  			    $translation_package = $iclTranslationManagement->create_translation_package($post);

  		        foreach ($element_translations as $trans) {
  		        	$_prevstate = $wpdb->get_row($wpdb->prepare("
                        SELECT status, translator_id, needs_update, md5, translation_service, translation_package, timestamp, links_fixed
                        FROM {$wpdb->prefix}icl_translation_status
                        WHERE translation_id = %d
                    ", $trans->translation_id), ARRAY_A);
                    if(!empty($_prevstate)){
                        $data['_prevstate'] = serialize($_prevstate);
                    }
  		        	$data = array('translation_id' => $trans->translation_id,
                    				'translation_package' => serialize($translation_package),
                    				'md5' => $md5,
                    				);

					//update only when something changed (we do not need to change status when deleting a field)
					if ($needs_update){
						$data['needs_update'] = 1;
					}

  		        	list( $rid, $update ) = $iclTranslationManagement->update_translation_status( $data );
  		        	$this->update_icl_translate($rid,$post);

					//change job status only when needs update
					if ( $needs_update ){
						$job_id = $wpdb->get_var($wpdb->prepare("SELECT MAX(job_id) FROM {$wpdb->prefix}icl_translate_job WHERE rid=%d GROUP BY rid", $rid));
						if ($job_id){
							$wpdb->update(
								"{$wpdb->prefix}icl_translate_job",
								array( 'translated' => 0 ),
								array( 'job_id' => $job_id ),
								array( '%d' ),
								array( '%d' )
							);
						}
					}
				}
			}
		}
	}

    

    function after_delete_form($form_id) {

    	global $sitepress, $wpdb;

		$icl_el_type = 'post_'.ICL_GRAVITY_FORM_ELEMENT_TYPE;
		$trid = $sitepress->get_element_trid($form_id,$icl_el_type);
		$translation_ids = $wpdb->get_col($wpdb->prepare("SELECT translation_id FROM {$wpdb->prefix}icl_translations WHERE trid=%d AND element_type=%s", $trid, $icl_el_type));

		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}icl_translations WHERE trid=%d", $trid));

		if (!empty($translation_ids)) foreach ($translation_ids as $tid) {
				$rid = $wpdb->get_var($wpdb->prepare("SELECT rid FROM {$wpdb->prefix}icl_translation_status WHERE translation_id=%d", $tid));
                $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}icl_translation_status WHERE translation_id=%d", $tid));
                if($rid){
                    $jobs = $wpdb->get_col($wpdb->prepare("SELECT job_id FROM {$wpdb->prefix}icl_translate_job WHERE rid=%d", $rid));
                    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}icl_translate_job WHERE rid=%d", $rid));
                    if(!empty($jobs)){
                        $wpdb->query("DELETE FROM {$wpdb->prefix}icl_translate WHERE job_id IN (".join(',', $jobs).")");
                    }
                }
		}
	}

	/**
	 * Remove translations of deleted field
	 *
	 * @param $form_id
	 * @param $field_id
	 */
	function after_delete_field( $form_id, $field_id ) {

		$form_meta = RGFormsModel::get_form_meta($form_id);
		//it is not new form (second parameter) and when deleting field do not need to update status (third parameter)
		$this->update_form_translations($form_meta, false, false);

	}

	function update_notifications_translations($notification,$form) {

		$this->update_form_translations($form,false);
		return $notification;
	}

	function update_confirmation_translations($confirmation,$form) {

		$this->update_form_translations($form,false);
		return $confirmation;
	}

	function make_duplicate($post_id,$lang) {
    	global $wpdb, $sitepress, $iclTranslationManagement;

    	//error_log("duplicating $post_id ".$this->gform_id($post_id));

	    $item = $this->get_translatable_item(null,$post_id);
	    $form_id = $this->gform_id($post_id);

	    if (is_null($item))
	    	return $post_id; //leave it untouched, not ours

		$icl_el_type = 'post_'.ICL_GRAVITY_FORM_ELEMENT_TYPE;
		$default_lang = $sitepress->get_default_language();

		$trid = $sitepress->get_element_trid($form_id,$icl_el_type);
		if (!$trid) {
			$sitepress->set_element_language_details($form_id, $icl_el_type, null, $default_lang, null, false);
			$trid = $sitepress->get_element_trid($form_id, $icl_el_type);
		}

		$translation_id = $wpdb->get_var($wpdb->prepare("SELECT translation_id FROM {$wpdb->prefix}icl_translations
														WHERE trid=%d AND language_code=%s AND source_language_code=%s",
														$trid,$lang,$default_lang));
		if (!$translation_id)
			$translation_id = $sitepress->set_element_language_details(null, $icl_el_type, $trid, $lang, $default_lang);
		$translation_package = $iclTranslationManagement->create_translation_package($item);
		$translator_id = 0;
		$translation_service = 'local';

	    // add translation_status record
        $data = array(
            'translation_id'        => $translation_id,
            'status'                => ICL_TM_COMPLETE, //don't mark it as duplicate so that it can be edited with TE
            'translator_id'         => 0,
            'needs_update'          => 0,
            'md5'                   => $iclTranslationManagement->post_md5($item),
            'translation_service'   => $translation_service,
            'translation_package'   => serialize($translation_package)
        );


        list($rid, $update) = $iclTranslationManagement->update_translation_status($data);

        //error_log("rid $rid up $update".var_export($translation_package,true));
        $job_id = $iclTranslationManagement->add_translation_job($rid, $translator_id, $translation_package);
        $wpdb->update($wpdb->prefix . 'icl_translate_job', array('translated'=>1), array('job_id'=>$job_id));
        //$job = $iclTranslationManagement->get_translation_job($job_id);
        //error_log(var_export($job,true));
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}icl_translate
        								SET field_data_translated = field_data, field_finished=1
        								WHERE job_id=%d AND field_translate=1",$job_id));

    	return $job_id;
    }

}
