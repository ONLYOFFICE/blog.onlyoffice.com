<?php
global $sitepress;

$xliff_newlines = $sitepress->get_setting('xliff_newlines') ? intval($sitepress->get_setting('xliff_newlines')) : WPML_XLIFF_NEWLINES_REPLACE;
?>
<div class="wpml-section" id="ml-content-setup-sec-5-1">

    <div class="wpml-section-header">
        <h3><?php _e('XLIFF file options', 'wpml-xliff');?></h3>
    </div>
	
	<div class="wpml-section-content">

        <form name="icl_xliff_newlines_form" id="icl_xliff_newlines_form" action="">
            <?php wp_nonce_field('icl_xliff_newlines_form_nonce', '_icl_nonce'); ?>
            
					
						<p>
                <?php _e('How new lines characters in XLIFF files should be handled?', 'wpml-xliff'); ?>
            </p>
						
						<p>
                <label>
                    <input type="radio" name="icl_xliff_newlines" value="<?php echo WPML_XLIFF_NEWLINES_REPLACE ?>"<?php if ( $xliff_newlines == WPML_XLIFF_NEWLINES_REPLACE ): ?>checked<?php endif ?>/>
                    <?php printf(
														__('All new lines should be replaced by HTML element %s. Use this option if translation tool used by translator does not support new lines characters (for example Virtaal software)', 'wpml-xliff')
														, '&lt;br class="xliff-newline" />'); ?>
                </label>
            </p>
						
						<p>
                <label>
                    <input type="radio" name="icl_xliff_newlines" value="<?php echo WPML_XLIFF_NEWLINES_ORIGINAL ?>"<?php if ( $xliff_newlines == WPML_XLIFF_NEWLINES_ORIGINAL ): ?>checked<?php endif ?>/>
                    <?php _e('Do nothing. If you will select this, all new line characters will stay untouched.', 'wpml-xliff'); ?>
                </label>
            </p>
					

            <p class="buttons-wrap">
                <span class="icl_ajx_response" id="icl_ajx_response_sgtr"></span>
                <input type="submit" class="button-primary" value="<?php _e('Save', 'wpml-xliff')?>" />
            </p>
        </form>
    </div> <!-- .wpml-section-content -->
	
</div>

