jQuery(document).ready(function(){
    jQuery.browser = {};
    (function () {
        jQuery.browser.msie = false;
        jQuery.browser.version = 0;
        if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
            jQuery.browser.msie = true;
            jQuery.browser.version = RegExp.$1;
        }
    })();
    jQuery('#icl_tm_selected_user').change(function(){
        if(jQuery(this).val()){
            jQuery('.icl_tm_lang_pairs').slideDown();
        }else{
            jQuery('.icl_tm_lang_pairs').slideUp();
            jQuery('#icl_tm_adduser .icl_tm_lang_pairs_to').hide();
            jQuery('#icl_tm_add_user_errors span').hide();
        }

    });

    jQuery('#icl_tm_adduser .icl_tm_from_lang').change(function(){
        if(jQuery(this).attr('checked')){
           jQuery(this).parent().parent().find('.icl_tm_lang_pairs_to').slideDown();
        }else{
            jQuery(this).parent().parent().find('.icl_tm_lang_pairs_to').find(':checkbox').removeAttr('checked');
            jQuery(this).parent().parent().find('.icl_tm_lang_pairs_to').slideUp();
        }
    });

    jQuery('a[href="#hide-advanced-filters"]').click(function(){
        athis = jQuery(this);
        icl_save_dashboard_setting('advanced_filters',0,function(f){
            jQuery('#icl_dashboard_advanced_filters').slideUp()
            athis.hide();
            jQuery('a[href="#show-advanced-filters"]').show();
        });
    })

    jQuery('a[href="#show-advanced-filters"]').click(function(){
        athis = jQuery(this);
        icl_save_dashboard_setting('advanced_filters',1,function(f){
            jQuery('#icl_dashboard_advanced_filters').slideDown()
            athis.hide();
            jQuery('a[href="#hide-advanced-filters"]').show();
        });
    })

    /* word count estimate */

    jQuery('#icl-tm-translation-dashboard td :checkbox').click(icl_tm_update_word_count_estimate);
    jQuery('#icl-tm-translation-dashboard th :checkbox').click(icl_tm_select_all_documents);
    jQuery('#icl_tm_languages :radio').change(icl_tm_enable_submit);
    jQuery('#icl_tm_languages :radio').change(icl_tm_dup_warn);

    jQuery('#icl_tm_languages thead a').click(icl_tm_batch_selection);

    jQuery(document).delegate('.icl_tj_select_translator select', 'change', icl_tm_assign_translator);

    jQuery('#icl_tm_editor .handlediv').click(function(){
        if(jQuery(this).parent().hasClass('closed')){
            jQuery(this).parent().removeClass('closed');
        }else{
            jQuery(this).parent().addClass('closed');
        }
    })

    jQuery('.icl_tm_toggle_visual').click(function(){
        var inside = jQuery(this).closest('.inside');
        jQuery('.icl-tj-original .html', inside).hide();
        jQuery('.icl-tj-original .visual', inside).show();
        jQuery('.icl_tm_orig_toggle a', inside).removeClass('active');
        jQuery(this).addClass('active');
        return false;
    });

    jQuery('.icl_tm_toggle_html').click(function(){
        var inside = jQuery(this).closest('.inside');
        jQuery('.icl-tj-original .html', inside).show();
        jQuery('.icl-tj-original .visual', inside).hide();
        jQuery('.icl_tm_orig_toggle a', inside).removeClass('active');
        jQuery(this).addClass('active');
        return false;
    })

    jQuery('.icl_tm_finished').change(function(){
        jQuery(this).parent().parent().find('.icl_tm_error').hide();


		var regExp = /\[([^\]]+)\]/;
		var matches =  regExp.exec(jQuery(this).attr('name')) ; //extract values in []
		var field_id = matches[1]; //get field id from first []

		var field = jQuery(this).attr('name').replace(/finished/,'data');

        if(field == 'fields[body][data]'){
            var datatemp = '';

			try{
                datatemp = tinyMCE.get('body').getContent();
            }catch(err){;}

			var data = jQuery('*[name="'+field+'"]').val() + datatemp;
        }
        else if(jQuery(this).hasClass('icl_tmf_multiple')){
            var data = 1;
            jQuery('[name*="'+field+'"]').each(function(){
                data = data * jQuery(this).val().length;
            });
        }else{

            var datatemp = '';
            try{
                datatemp = tinyMCE.get(field_id).getContent();
            }catch(err){;}

            var data = jQuery('[name="'+field+'"]*').val() + datatemp;
        }

        if(jQuery(this).attr('checked') && !data){
            jQuery(this).parent().parent().find('.icl_tm_error').show();
            jQuery(this).removeAttr('checked');
        }
    });

    jQuery('#icl_tm_editor .icl_tm_finished').change(icl_tm_update_complete_cb_status);

    jQuery('#icl_tm_editor').submit(function () {
        var formnoerr = true;
        var validation_error = jQuery('#icl_tm_validation_error');

        validation_error.hide();

        jQuery('.icl_tm_finished:checked').each(function () {
            var field = jQuery(this).attr('name').replace(/finished/, 'data');

            var current_input_field = jQuery('*[name="' + field + '"]');
            if (field == 'fields[body][data]' || current_input_field.hasClass('wp-editor-area')) {
                var data = current_input_field.val() + tinyMCE.get(field).getContent();
            } else if (jQuery(this).hasClass('icl_tmf_multiple')) {
                var data = 1;
                jQuery('[name*="' + field + '"]').each(function () {
                    data = data * jQuery(this).val().length;
                });
            } else {
                var data = jQuery('[name="' + field + '"]*').val();
            }

            if (!data) {
                validation_error.fadeIn();
                jQuery(this).removeAttr('checked');
                icl_tm_update_complete_cb_status();
                formnoerr = false;
            }
        });

        return formnoerr;
    });

    if (jQuery('#radio-local').is(':checked')) {
      jQuery('#local_translations_add_translator_toggle').slideDown();
    }

    var icl_tm_users_quick_search = {

        attach_listener : function (){
            var searchTimer;

            jQuery('#icl_quick_src_users').keydown( function(e){

                jQuery('#icl_tm_selected_user').val('');
                jQuery('#icl_quick_src_users').css('border-color', '#ff0000');
                icl_add_translators_form_check_submit();


                var t = jQuery(this);

                if( 13 == e.which ) {
                    icl_tm_users_quick_search.update( t );
                    return false;
                }

                if( e.keyCode == 40 && jQuery('.icl_tm_auto_suggest_dd').length){

                    jQuery('.icl_tm_auto_suggest_dd').focus();


                }else if( e.which >= 32 && e.which <=127 || e.which == 8) {

                    jQuery('#icl_user_src_nf').remove();

                    if( searchTimer ) clearTimeout(searchTimer);

                    searchTimer = setTimeout(function(){
                        icl_tm_users_quick_search.update( t );
                    }, 400);

                }



            } ).attr('autocomplete','off');

            icl_tm_users_quick_search.select_listener();

            jQuery('#icl_quick_src_users').focus(function(){
                if(jQuery('.icl_tm_auto_suggest_dd').length){
                    jQuery('.icl_tm_auto_suggest_dd').css('visibility', 'visible');
                }
            })


            jQuery('#icl_quick_src_users').blur(function(){
                setTimeout(function(){
                    if(jQuery('.icl_tm_auto_suggest_dd').length && !jQuery('select.icl_tm_auto_suggest_dd').is(':focus') ){
                        jQuery('.icl_tm_auto_suggest_dd').css('visibility', 'hidden');
                    }
                }, 500);
            })



        },

        update : function(input){

            var panel, params,
            minSearchLength = 2,
            q = input.val();

            panel = input.parent();

            if( q.length < minSearchLength ){
                jQuery('select.icl_tm_auto_suggest_dd', panel).remove();
                return;
            }

            params = {
                'action': 'icl_tm_user_search',
                'q': q
            };


            jQuery('img.waiting', panel).show();
            jQuery('select.icl_tm_auto_suggest_dd', panel).remove();

            jQuery.post( ajaxurl, params, function(response) {
                icl_tm_users_quick_search.ajax_response(response, params, panel, input);
            });

        },

        ajax_response : function (response, params, panel, input){

            jQuery('#icl_user_src_nf').remove();
            input.after(response);
            jQuery('img.waiting', panel).hide();

        },

        select_listener : function(){

            /*
            jQuery(document).delegate('.icl_tm_auto_suggest_dd option', 'click', function(){
                icl_tm_users_quick_search.select(jQuery(this).val());
            });
            */

            jQuery(document).delegate('.icl_tm_auto_suggest_dd', 'change', function(){
                icl_tm_users_quick_search.select(jQuery(this).val());
            });


            jQuery(document).delegate('.icl_tm_auto_suggest_dd', 'keydown', function(e){
                if(e.which == 13){
                    icl_tm_users_quick_search.select(jQuery(this).val());
                    e.preventDefault();
                }
            });

            return;
            /*
            jQuery(document).delegate('.icl_tm_auto_suggest_dd', 'change', function(){

                var spl = jQuery(this).val().split('|');
                jQuery('#icl_tm_selected_user').val(spl[0]);
                spl.shift();
                jQuery('#icl_quick_src_users').val(spl.join('|'));
                jQuery(this).remove();
            })
            */
        },

        select : function(val){
            var spl = val.split('|');
            jQuery('#icl_tm_selected_user').val(spl[0]);
            spl.shift();
            jQuery('#icl_quick_src_users').val(spl.join('|')).css('border-color', 'inherit');
            jQuery('.icl_tm_auto_suggest_dd').remove();
            icl_add_translators_form_check_submit();
        }

    }

    icl_tm_users_quick_search.attach_listener();


    icl_add_translators_form_check_submit();
    var icl_active_service = jQuery("input[name='services']:checked").val();


    jQuery('input[name=services]').change(function() {
      if (jQuery('#radio-local').is(':checked')) {
        jQuery('#local_translations_add_translator_toggle').slideDown();
      } else {
        jQuery('#local_translations_add_translator_toggle').slideUp();
      }
      icl_active_service = jQuery(this).val();
      icl_add_translators_form_check_submit();
    });

    jQuery('#edit-from').change(function() {
      icl_add_translators_form_check_submit();
    });

    jQuery('#edit-to').change(function() {
      icl_add_translators_form_check_submit();
    });

    jQuery('#icl_add_translator_submit').click(function() {
      var url = jQuery('#'+icl_active_service+'_setup_url').val();
      if (url !== undefined) {
        url = url.replace(/from_replace/, jQuery('#edit-from').val());
        url = url.replace(/to_replace/, jQuery('#edit-to').val());
        icl_thickbox_reopen(url);
        return false;
      }
      jQuery('#icl_tm_add_user_errors span').hide();
      if (jQuery('input[name=services]').val() == 'local' && jQuery('#icl_tm_selected_user').val() == 0){
          jQuery('#icl_tm_add_user_errors .icl_tm_no_to').show();
          return false;
      }
    });

    jQuery('#icl_add_translator_form_toggle').click(function() {
      jQuery('#icl_add_translator_form_wrapper').slideToggle(function(){
        if (jQuery('#icl_add_translator_form_wrapper').is(':hidden')) {
          var caption = jQuery('#icl_add_translator_form_toggle').val().replace(/<</, '>>');
        } else {
          var caption = jQuery('#icl_add_translator_form_toggle').val().replace(/>>/, '<<');
        }
        jQuery('#icl_add_translator_form_toggle').val(caption);
      });

      return false;
    });

    jQuery('#icl_side_by_site a[href=#cancel]').click(function(){
        var thisa = jQuery(this);
        jQuery.ajax({
            type: "POST", url: ajaxurl, data: 'action=dismiss_icl_side_by_site',
            success: function(msg){
                    thisa.parent().parent().fadeOut();
                }
            });
        return false;
    });


    if (typeof(icl_tb_init) != 'undefined') {
        icl_tb_init('a.icl_thickbox');
        icl_tb_set_size('a.icl_thickbox');
    }

    var cache = '&cache=1';
    if (location.href.indexOf("main.php&sm=translators") !== -1 || location.href.indexOf('/post.php') !== -1 || location.href.indexOf('/edit.php') != -1) {
        cache = '';
    }

	var _icl_nonce_gts = jQuery('#_icl_nonce_gts');
	if (_icl_nonce_gts.length) {
		jQuery.ajax({
			type: "POST",
			url: icl_ajx_url,
			dataType: 'json',
			data: "icl_ajx_action=get_translator_status" + cache + '&_icl_nonce=' + _icl_nonce_gts.val(),
			success: function (msg) {
				if (cache === '') {
				}
			}
		});
	}

	var icl_tdo_options = jQuery('#icl_tdo_options');
	if (icl_tdo_options.length) {
		icl_tdo_options.submit(iclSaveForm);
	}

    jQuery('.icl_tm_copy_link').click(function () {
        var type = jQuery(this).attr('id').replace(/^icl_tm_copy_link_/, '');

        var job_id = jQuery('[name="job_id"]').val();

        var copy_link_element = jQuery(this).parent();

        icl_get_job_original_contents(job_id, type, copy_link_element);

        return false;
    });

    // Translator notes - translation dashboard - start
    jQuery('.icl_tn_link').click(function(){
        jQuery('.icl_post_note:visible').slideUp();
        thisl = jQuery(this);
        spl = thisl.attr('id').split('_');
        doc_id = spl[3];
        if(jQuery('#icl_post_note_'+doc_id).css('display') != 'none'){
            jQuery('#icl_post_note_'+doc_id).slideUp();
        }else{
            jQuery('#icl_post_note_'+doc_id).slideDown();
            jQuery('#icl_post_note_'+doc_id+' textarea').focus();
        }
        return false;
    });

    jQuery('.icl_post_note textarea').keyup(function(){
        if(jQuery.trim(jQuery(this).val())){
            jQuery('.icl_tn_clear').removeAttr('disabled');
        }else{
            jQuery('.icl_tn_clear').attr('disabled', 'disabled');
        }
    });
    jQuery('.icl_tn_clear').click(function(){
        jQuery(this).closest('table').prev().val('');
        jQuery(this).attr('disabled','disabled');
    })
    jQuery('.icl_tn_save').click(function(){
        thisa = jQuery(this);
        thisa.closest('table').find('input').attr('disabled','disabled');
        tn_post_id = thisa.closest('table').find('.icl_tn_post_id').val();
        jQuery.ajax({
                type: "POST",
                url: icl_ajx_url,
                data: "icl_ajx_action=save_translator_note&note="+thisa.closest('table').prev().val()+'&post_id='+tn_post_id + '&_icl_nonce=' + jQuery('#_icl_nonce_stn_' + tn_post_id).val(),
                success: function(msg){
                    thisa.closest('table').find('input').removeAttr('disabled');
                    thisa.closest('table').parent().slideUp();
                    icon_url = jQuery('#icl_tn_link_'+tn_post_id+' img').attr('src');
                    if(thisa.closest('table').prev().val()){
                        jQuery('#icl_tn_link_'+tn_post_id+' img').attr('src', icon_url.replace(/add_translation\.png$/, 'edit_translation.png'));
                    }else{
                        jQuery('#icl_tn_link_'+tn_post_id+' img').attr('src', icon_url.replace(/edit_translation\.png$/, 'add_translation.png'));
                    }
                }
        });

    });
    // Translator notes - translation dashboard - end

    // MC Setup
    jQuery('#icl_doc_translation_method').submit(iclSaveForm);
    jQuery('#icl_page_sync_options').submit(iclSaveForm);
    jQuery('form[name="icl_custom_tax_sync_options"]').submit(iclSaveForm);
    jQuery('form[name="icl_custom_posts_sync_options"]').submit(iclSaveForm);
    jQuery('form[name="icl_cf_translation"]').submit(iclSaveForm);

    if(jQuery.browser.msie){ // TODO: jQuery.browser.msie: version deprecated: 1.3, removed: 1.9
        jQuery('#icl_translation_pickup_mode').submit(icl_tm_set_pickup_method);
    }else{
        jQuery(document).delegate('#icl_translation_pickup_mode', 'submit', icl_tm_set_pickup_method);
    }

    jQuery(document).delegate('#icl_tm_get_translations', 'click', icl_tm_pickup_translations);
    if(jQuery('#icl_sec_tic').length){
        icl_sec_tic_to = window.setTimeout(icl_sec_tic_decrement, 60000);
    }

    jQuery('#icl-translation-jobs th :checkbox').change(iclTmSelectAllJobs);
    jQuery('#icl-tm-jobs-cancel-but').click(iclTmCancelJobs);
    jQuery('#icl-translation-jobs td :checkbox').change(iclTmUpdateJobsSelection);

    iclTmPopulateParentFilter();
    jQuery('#icl_parent_filter_control').change(iclTmPopulateParentFilter);
    jQuery('form[name="translation-dashboard-filter"]').find('select[name="filter[from_lang]"]').change(iclTmPopulateParentFilter);

    jQuery('#icl_tm_jobs_dup_submit').click(function(){return confirm(jQuery(this).next().html());})


    jQuery('#icl_hide_promo').click(function(){
        jQuery.ajax({type:"POST", url:ajaxurl, data: 'action=icl_tm_toggle_promo&value=1', success: function(){
            jQuery('.icl-translation-services').slideUp(function(){jQuery('#icl_show_promo').fadeIn()});
        }});
        return false;
    })
    jQuery('#icl_show_promo').click(function(){
        jQuery.ajax({type:"POST", url:ajaxurl, data: 'action=icl_tm_toggle_promo&value=0', success: function(){
            jQuery('#icl_show_promo').hide();
            jQuery('.icl-translation-services').slideDown()
        }});
        return false;
    })



});

function icl_save_dashboard_setting(setting, value, callback){
        jQuery('#icl_dashboard_ajax_working').fadeIn();
        jQuery.ajax({
            type: "POST",
            url: icl_ajx_url,
            data: 'icl_ajx_action=save_dashboard_setting&setting='+setting+'&value='+value+'_icl_nonce=' + jQuery('#_icl_nonce_sds').val(),
            success: function(msg){
                jQuery('#icl_dashboard_ajax_working').fadeOut();
                callback(msg);
            }
        });
}

function icl_add_translators_form_check_submit() {
  jQuery('#icl_add_translator_submit').attr('disabled', 'disabled');

  if(jQuery('#edit-from').val() != 0 && jQuery('#edit-to').val() != 0 && jQuery('#edit-from').val() != jQuery('#edit-to').val()){
      if (jQuery('#radio-icanlocalize').is(':checked') || jQuery('#radio-local').is(':checked') && jQuery('#icl_tm_selected_user').val()) {
        jQuery('#icl_add_translator_submit').removeAttr('disabled');
      }
  }

}

function icl_tm_update_word_count_estimate(){
    icl_tm_enable_submit();
    var id = jQuery(this).val();
    var val = parseInt(jQuery('#icl-cw-'+id).html());
    var curval = parseInt(jQuery('#icl-tm-estimated-words-count').html());
    if(jQuery(this).attr('checked')){
        var newval = curval + val;
    }else{
        var newval = curval - val;
    }
    jQuery('#icl-tm-estimated-words-count').html(newval);
    icl_tm_update_doc_count();
}

function icl_tm_select_all_documents(){
    if(jQuery(this).attr('checked')){
        jQuery('#icl-tm-translation-dashboard :checkbox').attr('checked','checked');
        jQuery('#icl-tm-estimated-words-count').html(parseInt(jQuery('#icl-cw-total').html()));
    }else{
        jQuery('#icl-tm-translation-dashboard :checkbox').removeAttr('checked');
        jQuery('#icl-tm-estimated-words-count').html('0');
    }
    icl_tm_update_doc_count();
    icl_tm_enable_submit();
}

function icl_tm_update_doc_count(){
    dox = jQuery('#icl-tm-translation-dashboard td :checkbox:checked').length;
    jQuery('#icl-tm-sel-doc-count').html(dox);
    if(dox){
        jQuery('#icl-tm-doc-wrap').fadeIn();
    }else{
        jQuery('#icl-tm-doc-wrap').fadeOut();
    }
}

function icl_tm_enable_submit(){
    var anyaction = false;
    jQuery('#icl_tm_languages :radio:checked').each(function(){
        if(jQuery(this).val() > 0){
            anyaction = true;
            return;
        }
    });

    if( jQuery('#icl-tm-translation-dashboard td :checkbox:checked').length > 0 && anyaction){
        jQuery('#icl_tm_jobs_submit').removeAttr('disabled');
    }else{
        jQuery('#icl_tm_jobs_submit').attr('disabled','disabled');
    }
}

function icl_tm_dup_warn(){
    dupsel = false;
    jQuery('#icl_tm_languages :radio:checked').each(function(){
        if(jQuery(this).val() == 2){
            dupsel = true;
            return;
        }
    });
    if(dupsel) jQuery('#icl_dup_ovr_warn').fadeIn();
    else jQuery('#icl_dup_ovr_warn').fadeOut();
}


function icl_tm_assign_translator(){
    var thiss = jQuery(this);
    var translator_id = thiss.val();
    var translation_controls = thiss.parent().parent().find('.icl_tj_select_translator_controls');
    var job_id = translation_controls.attr('id').replace(/^icl_tj_tc_/,'');
    translation_controls.show();
    translation_controls.find('.icl_tj_cancel').click(function(){
            thiss.val(jQuery('#icl_tj_ov_'+job_id).val());
            translation_controls.hide()
    });
    translation_controls.find('.icl_tj_ok').unbind('click').click(function(){icl_tm_assign_translator_request(job_id, translator_id, thiss)});

}

function icl_tm_assign_translator_request(job_id, translator_id, select){
    var translation_controls = select.parent().parent().find('.icl_tj_select_translator_controls');
    select.attr('disabled', 'disabled');
    translation_controls.find('.icl_tj_cancel, .icl_tj_ok').attr('disabled', 'disabled');
    var tdwrp = select.parent().parent();
    jQuery.ajax({
        type: "POST",
        url: icl_ajx_url,
        dataType: 'json',
        data: 'icl_ajx_action=assign_translator&job_id='+job_id+'&translator_id='+translator_id+'&_icl_nonce=' + jQuery('#_icl_nonce_at').val(),
        success: function(msg){
            if(!msg.error){
                translation_controls.hide();
                if(msg.service == 'icanlocalize'){
                    tdwrp.html(msg.message);
                }else{
                    jQuery('#icl_tj_ov_'+job_id).val(translator_id);
                }
            }else{
                //
            }
            select.removeAttr('disabled');
            translation_controls.find('.icl_tj_cancel, .icl_tj_ok').removeAttr('disabled');
        }
    });

    return false;
}

function icl_tm_update_complete_cb_status(){
    if(jQuery('#icl_tm_editor .icl_tm_finished:checked').length == jQuery('#icl_tm_editor .icl_tm_finished').length){
        jQuery('#icl_tm_editor :checkbox[name=complete]').prop('disabled', false);
    }else{
        jQuery('#icl_tm_editor :checkbox[name=complete]').prop('disabled', true);
    }
}

function icl_tm_set_pickup_method(e) {
    e.preventDefault();

    var $form = jQuery(this);
    var $submitButton = $form.find(':submit');

    $submitButton.prop('disabled', true);
    var $ajaxLoader = jQuery(icl_ajxloaderimg).insertBefore($submitButton);

    jQuery.ajax({
        type: "POST",
        url: icl_ajx_url,
        dataType: 'json',
        data: 'icl_ajx_action=set_pickup_mode&'+$form.serialize(),
        success: function(msg){
            if ( !msg.error ){
                jQuery('#icl_tm_pickup_wrap').load(location.href+' #icl_tm_pickup_wrap', function(resp){
                        jQuery(this).html(jQuery(resp).find('#icl_tm_pickup_wrap').html());
                    }
                );
            }
            else {
                alert(msg.error);
            }
        },
        complete: function() {
            $ajaxLoader.remove();
            $submitButton.prop('disabled',false);
        }
    });

    return false;
}

function icl_tm_pickup_translations(){
    var thisb = jQuery(this);
    thisb.attr('disabled', 'disabled').after(icl_ajxloaderimg);
    jQuery.ajax({
        type: "POST",
        url: icl_ajx_url,
        dataType: 'json',
        data: 'icl_ajx_action=pickup_translations&_icl_nonce='+jQuery('#_icl_nonce_pickt').val(),
        success: function(msg){
            if(!msg.error){
                url_glue = (-1 == location.href.indexOf('?')) ? '?' : '&';
                jQuery('#icl_tm_pickup_wrap').load(location.href+url_glue+'icl_pick_message='+msg.fetched+' #icl_tm_pickup_wrap', function(resp){
                    jQuery(this).html(jQuery(resp).find('#icl_tm_pickup_wrap').html());
                    thisb.removeAttr('disabled').next().remove();
                })
            }else{
                alert(msg.error);
                thisb.removeAttr('disabled').next().remove();
            }

        }
    });
}


function icl_sec_tic_decrement(){
    var curval = parseInt(jQuery('#icl_sec_tic').html());
    if(curval > 0){
        jQuery('#icl_sec_tic').html(curval - 1);
        window.setTimeout(icl_sec_tic_decrement, 60000);
    }else{
        jQuery('#icl_tm_get_translations').removeAttr('disabled');
        jQuery('#icl_tm_get_translations').next().fadeOut();
    }
}

/* MC Setup */

function iclTmSelectAllJobs(){
    if(jQuery(this).attr('checked')){
        jQuery('#icl-translation-jobs :checkbox').attr('checked', 'checked');
        jQuery('#icl-tm-jobs-cancel-but').removeAttr('disabled');
    }else{
        jQuery('#icl-translation-jobs :checkbox').removeAttr('checked');
        jQuery('#icl-tm-jobs-cancel-but').attr('disabled', 'disabled');
    }
}

function iclTmCancelJobs(){

    var tm_prompt = jQuery('#icl-tm-jobs-cancel-msg').html();
    var in_progress = jQuery('tr.icl_tm_status_2 input:checkbox:checked').length;

    if(in_progress > 0){
        tm_prompt += "\n" + jQuery('#icl-tm-jobs-cancel-msg-2').html().replace(/%s/g, in_progress);
        jQuery('tr.icl_tm_status_2 :checkbox:checked').parent().parent().addClass('icl_tm_row_highlight');
    }

    if(!confirm(tm_prompt)){
        jQuery('#icl-tm-jobs-form input[name=icl_tm_action]').val('jobs_filter');
        jQuery('tr.icl_tm_row_highlight').removeClass('icl_tm_row_highlight');
        return false;
    }
    jQuery('#icl-tm-jobs-form input[name=icl_tm_action]').val('cancel_jobs');

    return true;
}

function iclTmUpdateJobsSelection(){
    if(jQuery('#icl-translation-jobs :checkbox:checked').length > 0){
        jQuery('#icl-tm-jobs-cancel-but').removeAttr('disabled');

        if(jQuery('#icl-translation-jobs td :checkbox:checked').length == jQuery('#icl-translation-jobs td :checkbox').length){
            jQuery('#icl-translation-jobs th :checkbox').attr('checked', 'checked');
        }else{
            jQuery('#icl-translation-jobs th :checkbox').removeAttr('checked');
        }

    }else{
        jQuery('#icl-tm-jobs-cancel-but').attr('disabled', 'disabled');
    }
}

function iclTmPopulateParentFilter(){
    var val = jQuery('#icl_parent_filter_control').val();

    jQuery('#icl_parent_filter_drop').html(icl_ajxloaderimg);

    if(val){
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            dataType: 'json',
            data: 'action=icl_tm_parent_filter&type='+val+'&lang=' + jQuery('form[name="translation-dashboard-filter"]').find('select[name="filter[from_lang]"]').val()+'&parent_id='+jQuery('#icl_tm_parent_id').val()+'&parent_all='+jQuery('#icl_tm_parent_all').val(),
            success: function(msg){
                jQuery('#icl_parent_filter_drop').html(msg.html);

                //select page
                jQuery('#filter[parent_id]').val(jQuery('#icl_tm_parent_id').val());
            }
        });
    }else{
        jQuery('#icl_parent_filter_drop').html('');
    }
}

function icl_tm_batch_selection(){

    var action = jQuery(this).attr('href').substr(1);
    var value  = 0;

    if(action == 'translate-all'){
        value = 1;
    }else if(action == 'update-none'){
        value = 0;
    }else if(action == 'duplicate-all'){
        value = 2;
    }

    jQuery('#icl_tm_languages tbody input:radio[value='+value+']').attr('checked', 'checked');

    icl_tm_enable_submit();

    return false;

}

function icl_abort_translation(input, job_id){

    if(!confirm(jQuery('#icl-tm-jobs-cancel-msg-3').html())) return false;

    input.attr('disabled', 'disabled');
    input.after(icl_ajxloaderimg);

    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        dataType: 'json',
        data: 'action=icl_tm_abort_translation&job_id='+job_id,
        success: function(msg){
            if(!msg.error){
                input.val(msg.message);
                window.location.href = window.location.href.replace(/#(.*)/, '');
            }else{
                alert(msg.error);
            }
            input.next().hide();
        }
    });




}

function icl_get_job_original_contents(job_id, field_type, calling_element) {

    var ajax_spinner = jQuery('<span class="spinner" style="float:left;"> </span>');
    calling_element.replaceWith(ajax_spinner);
    ajax_spinner.show();
    var nonce = jQuery('#icl-copy-from-original-nonce').html();
    jQuery.ajax(
      {
          type:     "POST",
          url:      ajaxurl,
          dataType: 'json',
          data:     {
              tm_editor_job_id:    job_id,
              tm_editor_job_field: field_type,
              tm_editor_copy_nonce: nonce,
              action:              'icl_get_job_original_field_content'
          },
          success:  function (response) {

              var custom_editor = false;

              try{
                  custom_editor = tinyMCE.activeEditor;
              }catch(err){;}

              var found_editor = false;
              if (custom_editor && custom_editor.id !== field_type) {
                  //tinyMCE API change
                  jQuery.each(
                    custom_editor.editorManager.editors, function () {
                        var item = this;
                        if ("field-wpcf-" + field_type === item.id || field_type === item.id) {
                            custom_editor = item;
                            found_editor = true;
                        }
                    }
                  );
              } else {
                  found_editor = true;
              }

              if (custom_editor && found_editor && !custom_editor.isHidden()) {
                  custom_editor.insertContent(response.data);
              } else {
                  jQuery('#' + field_type).val(response.data);
              }

              ajax_spinner.fadeOut();
          },
          error:    function () {
              ajax_spinner.replaceWith(calling_element);
          }
      }
    );
}