/*globals icl_ajx_url*/

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

	/** @namespace jQuery.browser.msie */
	if(jQuery.browser.msie){ // TODO: jQuery.browser.msie: version deprecated: 1.3, removed: 1.9
			jQuery('#icl_xliff_newlines_form').submit(icl_xliff_set_newlines);
	}else{
			jQuery(document).delegate('#icl_xliff_newlines_form', 'submit', icl_xliff_set_newlines);
	}
	
	function icl_xliff_set_newlines(e) {
    e.preventDefault();

    var $form = jQuery(this);
    var $submitButton = $form.find(':submit');

    $submitButton.prop('disabled', true);
    var $ajaxLoader = jQuery(icl_ajxloaderimg).insertBefore($submitButton);

    jQuery.ajax({
        type: "POST",
        url: icl_ajx_url,
        dataType: 'json',
        data: 'icl_ajx_action=set_xliff_newlines&'+$form.serialize(),
        success: function(msg){
            if ( !msg.error ){
                // @todo: write some response handling if any
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
	
});