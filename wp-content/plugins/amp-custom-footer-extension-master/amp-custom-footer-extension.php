<?php
/*
Plugin Name: AMP Custom Footer Extension
Plugin URI: https://wordpress.org/plugins/accelerated-mobile-pages/
Description: Extension made for AMP for WP to add a custom footer.
Version: 1.0
Author:  Mikhail Tyuftyaev
Author URI: http://ampforwp.com/ 
License: GPL2
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('amp_post_template_footer','amp_custom_footer_extension');
	function amp_custom_footer_extension() { ?>
	<div class="amp-custom-banner-after-post">
		<amp-accordion class="sample" expand-single-section animate>
			<section class="first_section">
				<h4 id="footer-accordion-1"><span class="underline"><?php _e('Developers', 'teamlab-blog-2-0'); ?></span></h4>
				<ul>
                    <li><p><a href="<?php echo $current_language?>/developer-edition.aspx"><?php _e('Developer Edition', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/document-builder.aspx"><?php _e('Document Builder', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a target="_blank" id="navitem_api" href="https://api.onlyoffice.com/"><?php _e('API', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a target="_blank" href="https://github.com/ONLYOFFICE/"><?php _e('Code on GitHub', 'teamlab-blog-2-0'); ?></a></p></li>
                </ul>
			</section>
			<section class="second_section">
				<h4 id="footer-accordion-2"><span class="underline"><?php _e('Security', 'teamlab-blog-2-0'); ?></span></h4>
				<ul>
                    <li><p><a href="<?php echo $current_language?>/security.aspx#access_control"><?php _e('Authentication and access control', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/security.aspx#data_protection"><?php _e('Data protection', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/security.aspx#data_encryption"><?php _e('Data encryption', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/private-rooms.aspx"><?php _e('Private rooms', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="https://www.onlyoffice.com/blog/2018/05/how-onlyoffice-complies-with-gdpr/"><?php _e('GDPR compliance', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="https://www.onlyoffice.com/blog/2020/10/how-onlyoffice-complies-with-hipaa/"><?php _e('HIPAA compliance', 'teamlab-blog-2-0'); ?></a></p></li>
                </ul>
			</section>
			<section class="third_section">
				<h4 id="footer-accordion-3"><span class="underline"><?php _e('Integrations', 'teamlab-blog-2-0'); ?></span></h4>
				<ul>
                    <li><p><a href="<?php echo $current_language?>/office-for-nextcloud.aspx"><?php _e('Nextcloud', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/office-for-owncloud.aspx"><?php _e('ownCloud', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/office-for-confluence.aspx"><?php _e('Confluence', 'teamlab-blog-2-0'); ?></a></p><li>
                    <li><p><a href="<?php echo $current_language?>/office-for-alfresco.aspx"><?php _e('Alfresco', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/office-for-sharepoint.aspx"><?php _e('SharePoint', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/office-for-liferay.aspx"><?php _e('Liferay', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/office-for-humhub.aspx"><?php _e('HumHub', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/office-for-plone.aspx"><?php _e('Plone', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/office-for-nuxeo.aspx"><?php _e('Nuxeo', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/office-for-chamilo.aspx"><?php _e('Chamilo', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/all-connectors.aspx"><?php _e('Others', 'teamlab-blog-2-0'); ?></a></p></li>
                </ul>
			</section>
			<section class="fourth_section">
				<h4 id="footer-accordion-4"><span class="underline"><?php _e('Support', 'teamlab-blog-2-0'); ?></span></h4>
				<ul>
                    <li><p><a href="<?php echo $current_language?>/support.aspx"><?php _e('Premium support', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/support-contact-form.aspx"><?php _e('Support contact form', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a target="_blank" href="https://cloud.onlyoffice.org/"><?php _e('SaaS forum', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a target="_blank" href="https://dev.onlyoffice.org/"><?php _e('Server forum', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/demo-order.aspx"><?php _e('Order demo', 'teamlab-blog-2-0'); ?></a></p></li>
                </ul>
			</section>
			<section class="five_section">
				<h4 id="footer-accordion-5"><span class="underline"><?php _e('Resources', 'teamlab-blog-2-0'); ?></span></h4>
				<ul>
                    <li><p><a target="_blank" href="https://helpcenter.onlyoffice.com/index.aspx"><?php _e('Help Center', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/app-directory.aspx"><?php _e('App Directory', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a  href="<?php echo $current_language?>/document-editor-comparison.aspx"><?php _e('Compare to other suites', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/contribute.aspx"><?php _e('Contribute', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/webinars.aspx"><?php _e('Webinars', 'teamlab-blog-2-0'); ?></a></p></li>
                    <li><p><a href="<?php echo $current_language?>/legalterms.aspx"><?php _e('Legal notice', 'teamlab-blog-2-0'); ?></a></p></li>
                </ul>
			</section>
			<section class="six_section">
			<h4 id="footer-accordion-5"><span class="underline"><?php _e('Contact Us', 'teamlab-blog-2-0'); ?></span></h4>
			<ul>
                <li>
                        <?php _e('Sales questions', 'teamlab-blog-2-0'); ?>
                    	<a class="emailus" href="mailto:sales@onlyoffice.com">sales@onlyoffice.com</a>
                </li>
                <li>
                    <?php _e('Partner inquiries', 'teamlab-blog-2-0'); ?>
                   	<a class="emailus" href="mailto:partners@onlyoffice.com">partners@onlyoffice.com</a>
                </li>
                <li>
                	<?php _e('Press inquiries', 'teamlab-blog-2-0'); ?>
                    <a class="emailus" href="mailto:press@onlyoffice.com">press@onlyoffice.com</a>
                </li >
                <li>
                    <p><a href="<?php echo $current_language?>/call-back-form.aspx"><?php _e('Request a call', 'teamlab-blog-2-0'); ?></a></p>
                </li>
            </ul>
			</section>
		</amp-accordion>
		<div class="SocialLinks">
                    <h6><?php _e('Follow us on:', 'teamlab-blog-2-0'); ?></h6>
                        <ul class="ListSocLink">
                            <li><a target="_blank" href="https://www.facebook.com/pages/OnlyOffice/833032526736775" onmouseup="PageTrack('GoTo_facebook');" title="Follow us on Facebook" class="faceBook">
                                    <label class="social_grey_fb" title="Facebook"></label>
                                </a></li>
                            <li><a rel="nofollow" target="_blank" onmouseup="PageTrack('GoTo_twitter');" href="https://twitter.com/ONLY_OFFICE" title="Follow us on Twitter" class="twitter">
                                    <label class="social_grey_twi" title="Twitter"></label>
                                </a></li>
                            <li><a rel="nofollow" target="_blank" onmouseup="PageTrack('GoTo_linkedin');" href="https://www.linkedin.com/groups/ONLYOFFICE-6726380" title="Follow us on LinkedIn" class="linkedin">
                                    <label class="social_grey_in" title="LinkedIn"></label>
                                </a></li>
                            <li><a rel="nofollow" target="_blank" onmouseup="PageTrack('GoTo_youtube');" href="https://www.youtube.com/user/onlyofficeTV" title="Follow us on YouTube" class="youtube">
                                    <label class="social_grey_tube" title="YouTube"></label>
                                </a></li>  
                            <li><a rel="nofollow" target="_blank" onmouseup="PageTrack('GoTo_blog');" href="https://www.onlyoffice.com/blog" title="Read our blog" class="blog">
                                    <label title="Blog" class="social_grey_blog"></label>
                                </a></li>
                            <li><a rel="nofollow" target="_blank" onmouseup="PageTrack('GoTo_medium');" href="https://medium.com/onlyoffice" title="Follow us on Medium" class="medium">
                                    <label title="Medium" class="social_grey_medium"></label>
                                </a></li>
                            <li><a rel="nofollow" target="_blank" onmouseup="PageTrack('GoTo_instagram');" class="medium" title="Follow us on Instagram" href="https://www.instagram.com/the_onlyoffice/" >
                                <label title="Instagram" class="social_grey_instagram"></label>
                            </a></li>
                            <li><a class="github" title="Follow us on GitHub" href="https://github.com/ONLYOFFICE/" onmouseup="PageTrack('GoTo_medium');" target="_blank">
                                <label title="GitHub" class="social_grey_github"></label>
                            </a></li>
                            <li><a class="fosstodon" title="Follow us on Fosstodon" href="https://fosstodon.org/@ONLYOFFICE" onmouseup="PageTrack('GoTo_medium');" target="_blank">
                                <label title="Fosstodon" class="social_grey_fosstodon"></label>
                            </a></li>
                        </ul>
                        <div class="copyReserved">&copy; <?php _e('Ascensio System SIA', 'teamlab-blog-2-0'); ?> <?php echo date("Y"); ?>. <?php _e('All rights reserved', 'teamlab-blog-2-0'); ?></div>
                    </div>
	</div>
	<?php 
	}

add_action('amp_post_template_css', 'amp_custom_footer_extension_styling');
	function amp_custom_footer_extension_styling() { ?>
		.amp-custom-banner-after-post {
			text-align: center
		}

		#footer-accordion-1, #footer-accordion-2, #footer-accordion-3, #footer-accordion-4, #footer-accordion-5, #footer-accordion-6 {
			margin-bottom: 0;
			padding: 14px 0;
			line-height: 1;
			background-color: #fff;
		}
		.amp-custom-banner-after-post section ul li::marker,
		.amp-custom-banner-after-post .SocialLinks ul li::marker{
			content: none;
		}
		.amp-custom-banner-after-post section ul{
			padding: 14px 0;
		}
		.amp-custom-banner-after-post section ul li,
		.amp-custom-banner-after-post section ul li a,
		.amp-custom-banner-after-post section ul li p,
		.amp-custom-banner-after-post section ul li p a{
			line-height: 18px;
    		margin: 0 0 7px;
		}
		
		.amp-custom-banner-after-post .SocialLinks h6{
			padding: 14px 0;
			line-height: 1;
			margin-bottom: 0;
		}
		.amp-custom-banner-after-post .copyReserved{
			padding: 50px 0;
		}
        .ListSocLink {
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        justify-content: space-between;
        -webkit-flex-wrap: wrap;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        margin: 12px auto 10px;
        max-width: 300px;
        }
        .ListSocLink li {
        list-style-type: none;
        display: inline-block;
        width: 26px;
        height: 42px;
        margin: 0;
        padding-right: 24px;
        vertical-align: middle
        }
        .ListSocLink li label {
        background-repeat: no-repeat;
        background-image: url("<?php echo WEB_ROOT_URL ?>/blog/wp-content/themes/teamlab-blog-2-0/images/color_social_icons.svg");
        -webkit-filter: grayscale(1);
        filter: grayscale(1);
        display: block;
        height: 24px;
        width: 32px;
        margin: 0;
        vertical-align: middle;
        background-position-y: 0;
        }
        .ListSocLink li label:hover {
        -webkit-filter: grayscale(0);
        filter: grayscale(0)
        }
        .ListSocLink li label:active {
        background-position-y: -41px
        }

        .ListSocLink li label.social_grey_subscribe{
        background-position-x: -430px;
        }

        .ListSocLink li label.social_grey_fb {
        background-position-x: 4px;
        }


        .ListSocLink li label.social_grey_twi {
        background-position-x: -36px;
        }


        .ListSocLink li label.social_grey_in {
        background-position-x: -76px;
        }


        .ListSocLink li label.social_grey_g {
        background-position-x: -75px;
        }


        .ListSocLink li label.social_grey_tube {
        background-position-x: -116px;
        }


        .ListSocLink li label.social_grey_blog {
        background-position-x: -196px;
        }

        .ListSocLink li label.social_grey_medium {
        background-position-x: -236px;
        }

        .ListSocLink li label.social_grey_instagram {
        background-position-x: -276px;
        }

        .ListSocLink li label.social_grey_vk {
        background-position-x: -156px;
        }

        .ListSocLink li label.social_grey_github {
        background-position-x: -316px
        }

        .ListSocLink li label.social_grey_fosstodon {
        background-position-x: -393px
        }
		
<?php }