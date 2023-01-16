<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Teamlab_Blog_2.0
 */
global $current_language;
global $sitepress;
$lang = $sitepress->get_current_language();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Tag Manager -->
    <script>
        (function(a, s, y, n, c, h, i, d, e) {
            s.className += ' ' + y;
            h.start = 1 * new Date;
            h.end = i = function() {
                s.className = s.className.replace(RegExp(' ?' + y), '')
            };
            (a[n] = a[n] || []).hide = h;
            setTimeout(function() {
                i();
                h.end = null
            }, c);
            h.timeout = c;
        })(window, document.documentElement, 'async-hide', 'dataLayer', 4000, {
            'GTM-PMBZ8H3': true
        });
    </script>
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-5NW47TX');
    </script>
    <!-- End Google Tag Manager -->
    <script>
        (function(i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function() {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

        ga('create', 'UA-12442749-5', 'auto', {
            'name': 'www',
            'allowLinker': true
        });
        ga('require', 'linker');
        ga('www.linker:autoLink', ['onlyoffice.com', 'onlyoffice.eu', 'onlyoffice.sg', 'avangate.com']);
        ga('www.send', 'pageview');

        ga('create', 'UA-12442749-21', 'auto', {
            'name': 'testTracker',
            'allowLinker': true
        });
        ga('require', 'linker');
        ga('testTracker.linker:autoLink', ['onlyoffice.com', 'onlyoffice.eu', 'onlyoffice.sg', 'avangate.com']);
        ga('testTracker.send', 'pageview');
    </script>
    <meta content="text/html; charset=<?php bloginfo('charset'); ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <link rel="icon" href="<?php bloginfo('template_directory'); ?>/images/favicon.ico" type="image/x-icon" />
    <!-- <script type="text/javascript">

		// var onloadCallback = function() {
		// 	grecaptcha.render('popupCaptcha', {'sitekey' : '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'});  // test public key
		// };

	</script>
    <script src='https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=en'></script> -->
    <?php wp_head() ?>
</head>
<div class="advent-announce push advent-mobile-hide">
    <?php
    $banner_url = 'https://www.onlyoffice.com/blog/2022/11/meet-onlyoffice-docs-saas/';
     if($current_language == WEB_ROOT_URL.'/'.'de'){
         $banner_url = 'https://www.onlyoffice.com/blog/de/2022/11/begrusen-sie-onlyoffice-docs-saas-die-cloudbasierte-version-der-onlyoffice-online-suite/';
     }else if($current_language == WEB_ROOT_URL.'/'.'fr'){
         $banner_url = 'https://www.onlyoffice.com/blog/fr/2022/11/decouvrez-onlyoffice-docs-saas/';
     }else if($current_language == WEB_ROOT_URL.'/'.'es'){
         $banner_url = 'https://www.onlyoffice.com/blog/es/2022/11/descubre-onlyoffice-docs-saas/';
     }else if($current_language == WEB_ROOT_URL.'/'.'it'){
         $banner_url = 'https://www.onlyoffice.com/blog/it/2022/11/onlyoffice-docs-saas-nel-cloud/';
     }else if($current_language == WEB_ROOT_URL.'/'.'zh'){
         $banner_url = 'https://www.onlyoffice.com/blog/zh-hans/2022/11/meet-onlyoffice-docs-saas/';
     }else if($current_language == WEB_ROOT_URL.'/'.'ja'){
         $banner_url = 'https://www.onlyoffice.com/blog/ja/2022/11/onlyoffice-onlyoffice-docs-saas/';
     }else if($current_language == WEB_ROOT_URL.'/'.'pt'){
         $banner_url = 'https://www.onlyoffice.com/blog/pt-br/2022/11/conheca-o-onlyoffice-docs-saas/';
     }
    ?>
    <a href="<?php echo $banner_url ?>">
        <div class="advent-announce-text">
            <div>
                <div><?php _e('Meet <b>ONLYOFFICE Docs Cloud</b>, complete office software as a service', 'teamlab-blog-2-0'); ?></div>
            </div>
        </div>
    </a>
</div>
<div class="advent-announce push advent-desktop-hide">
    <a  href="<?php echo $banner_url ?>">
        <div class="advent-announce-text">
            <div>
                <div><?php _e('Meet <b>ONLYOFFICE Docs Cloud</b>', 'teamlab-blog-2-0'); ?></div>
            </div>
        </div>
    </a>
</div>

<body <?php body_class(); ?>>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5NW47TX" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div class="BaseSide">
        <div class="mainpart">
            <div class="site-overlay"></div>
            <header>
                <div class="narrowheader <?php echo $lang ?>">
                    <div class="logo push">
                        <a href="<?php echo $current_language ?>"></a>
                    </div>
                    <div class="ham_menu push menu-btn pushy-link">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <nav class="pushy pushy-left">
                        <div class="pushy-content">
                            <ul class="all-menu-items">
                                <li class="pushy-submenu"><a class="menuitem <?php echo get_locale(); ?>" id="navitem_features"><?php _e('Features', 'teamlab-blog-2-0'); ?></a>
                                    <div id="navitem_features_menu">
                                        <ul class="dropdown-content akkordeon">
                                            <li id="navitem_editors_third_level_menu">
                                                <ul class="navitem_2nd_menu">
                                                    <li>
                                                        <a class="dropdown-item" id="navitem_features_docs_overview" href="<?php echo $current_language ?>/office-suite.aspx"><?php _e('Docs overview', 'teamlab-blog-2-0'); ?></a>
                                                        <a class="dropdown-item" id="navitem_features_document_editor" href="<?php echo $current_language ?>/document-editor.aspx"><?php _e('Document Editor', 'teamlab-blog-2-0'); ?></a>
                                                        <a class="dropdown-item" id="navitem_features_spreadsheet_editor" href="<?php echo $current_language ?>/spreadsheet-editor.aspx"><?php _e('Spreadsheet Editor', 'teamlab-blog-2-0'); ?></a>
                                                        <a class="dropdown-item" id="navitem_features_presentation_editor" href="<?php echo $current_language ?>/presentation-editor.aspx"><?php _e('Presentation Editor', 'teamlab-blog-2-0'); ?></a>
                                                        <a class="dropdown-item" id="navitem_features_form_creator" href="<?php echo $current_language ?>/form-creator.aspx"><?php _e('Form creator', 'teamlab-blog-2-0'); ?></a>
                                                        <a class="dropdown-item" id="navitem_features_pdf_reader" href="<?php echo $current_language ?>/pdf-reader.aspx"><?php _e('PDF reader & converter', 'teamlab-blog-2-0'); ?></a>
                                                        <a class="dropdown-item" id="navitem_features_security" href="<?php echo $current_language ?>/security.aspx"><?php _e('Security', 'teamlab-blog-2-0'); ?></a>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li id="navitem_comserver_third_level_menu">
                                                <ul class="navitem_2nd_menu">
                                                    <li>
                                                        <a id="navitem_solutions_clients_workspace" class="dropdown-item mobile_no_link"><?php _e('Desktop & mobile apps', 'teamlab-blog-2-0'); ?></a>
                                                        <a id="navitem_solutions_clients_apps" class="dropdown-item" href="<?php echo $current_language ?>/desktop.aspx"><?php _e('For desktop', 'teamlab-blog-2-0'); ?></a>
                                                        <a id="navitem_solutions_clients_mobile_ios" class="dropdown-item" href="<?php echo $current_language ?>/office-for-ios.aspx"><?php _e('For iOS', 'teamlab-blog-2-0'); ?></a>
                                                        <a id="navitem_solutions_clients_mobile_android" class="dropdown-item" href="<?php echo $current_language ?>/office-for-android.aspx"><?php _e('For Android', 'teamlab-blog-2-0'); ?></a>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li id="navitem_oforms_third_level_menu">
                                                <a id="navitem_features_see_it" class="dropdown-item" href="https://oforms.onlyoffice.com/"><?php _e('See it in action!', 'teamlab-blog-2-0'); ?></a>
                                                <div id="oforms_div" class="menu_pic_div">
                                                    <div id="see_it_img" class="menu_pic_img"></div>
                                                    <p id="see_it_action_header" class="menu_pic_header">
                                                        <?php _e('Curious to know what the interface looks like and try the main functionality?', 'teamlab-blog-2-0'); ?>
                                                    </p>
                                                </div>
                                                <a id="navitem_features_oforms" class="dropdown-item" href="https://oforms.onlyoffice.com/"><?php _e('OFORMs', 'teamlab-blog-2-0'); ?></a>
                                                <div class="menu_pic_div">
                                                    <div id="oforms_img" class="menu_pic_img"></div>
                                                    <p id="oforms_header" class="menu_pic_header">
                                                        <?php _e('OFORMS, free <span class="nowrap">ready-to-fill</span> out online document forms', 'teamlab-blog-2-0'); ?>
                                                    </p>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <!--
                                -->
                                <li class="pushy-submenu"><a class="menuitem" id="navitem_integrations"><?php _e('For business', 'teamlab-blog-2-0'); ?></a>
                                    <div id="navitem_integrations_menu">
                                        <ul class="dropdown-content akkordeon">
                                            <li>
                                                <a class="dropdown-item" id="navitem_fb_docs_ee"  href="<?php echo $current_language ?>/docs-enterprise.aspx"><?php _e('Docs Enterprise', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_integrations_docs_cloud"  href="<?php echo $current_language ?>/docs-cloud.aspx"><?php _e('Docs Cloud', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_fb_workspace"  href="<?php echo $current_language ?>/workspace.aspx"><?php _e('Workspace', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item mobile_no_link" id="navitem_solutions_clients_workspace" ><?php _e('Other Integrations', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_integrations_nextcloud"  href="<?php echo $current_language ?>/office-for-nextcloud.aspx"><?php _e('Nextcloud', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_integrations_owncloud"  href="<?php echo $current_language ?>/office-for-owncloud.aspx"><?php _e('ownCloud', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_integrations_confluence"  href="<?php echo $current_language ?>/office-for-confluence.aspx"><?php _e('Confluence', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_integrations_alfresco"  href="<?php echo $current_language ?>/office-for-alfresco.aspx"><?php _e('Alfresco', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_integrations_moodle"  href="<?php echo $current_language ?>/office-for-moodle.aspx"><?php _e('Moodle', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_integrations_others" href="<?php echo $current_language ?>/all-connectors.aspx"><?php _e('All', 'teamlab-blog-2-0'); ?></a>
                                            </li>
                                            <li id="navitem_education_third_level_menu">
                                                <a id="navitem_education_for_business" class="dropdown-item" href="<?php echo $current_language ?>/education.aspx"><?php _e('ONLYOFFICE for education', 'teamlab-blog-2-0'); ?></a>
                                                <div id="for_business_div" class="menu_pic_div">
                                                    <div id="for_education_img" class="menu_pic_img"></div>
                                                    <p id="for_developers_header" class="menu_pic_header">
                                                        <?php _e('Edit and collaborate on docs within your eLearning platform', 'teamlab-blog-2-0'); ?>
                                                    </p>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <!--
                               -->
                                <li class="pushy-submenu"><a class="menuitem" id="navitem_developers"><?php _e('For developers', 'teamlab-blog-2-0'); ?></a>
                                    <div id="navitem_developers_menu">
                                        <ul class="dropdown-content akkordeon">
                                            <li class="pushy-link">
                                                <a class="dropdown-item" id="navitem_document_developer" href="<?php echo $current_language ?>/developer-edition.aspx"><?php _e('Docs Developer', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_fd_conversion_api" href="<?php echo $current_language ?>/conversion-api.aspx"><?php _e('Conversion API', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_fd_doc_builder" href="<?php echo $current_language ?>/document-builder.aspx"><?php _e('Document Builder', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_fd_api" href="https://api.onlyoffice.com/?_ga=2.32381309.2134056570.1665380851-389734306.1663091039" target="_blank" rel="noreferrer noopener"><?php _e('API Documentation', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_fd_get" href="<?php echo $current_language ?>/download-docs.aspx?from=downloadintegrationmenu#docs-developer"><?php _e('Get it now', 'teamlab-blog-2-0'); ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <!--
                                -->
                                <li class="pushy-submenu"><a class="menuitem <?php echo get_locale(); ?>" id="navitem_download"><?php _e('Get ONLYOFFICE', 'teamlab-blog-2-0'); ?></a>
                                    <div id="navitem_download_menu">
                                        <ul class="dropdown-content">
                                            <li class="pushy-link">
                                               <ul>
                                                   <a id="navitem_download_docs" class="dropdown-item mobile_no_link"><?php _e('For business', 'teamlab-blog-2-0'); ?></a>
                                                   <a class="dropdown-item" id="navitem_fb_docs_ee"  href="<?php echo $current_language ?>/docs-enterprise.aspx"><?php _e('Docs Enterprise', 'teamlab-blog-2-0'); ?></a>
                                                   <a class="dropdown-item" id="navitem_fb_docs_cloud"  href="<?php echo $current_language ?>/docs-registration.aspx"><?php _e('Docs Cloud', 'teamlab-blog-2-0'); ?></a>
                                                   <a class="dropdown-item" id="navitem_fb_workspace"  href="<?php echo $current_language ?>/workspace.aspx"><?php _e('Workspace', 'teamlab-blog-2-0'); ?></a>
                                                   <ul class="navitem_2nd_menu">
                                                       <li><a class="nav_item_nowrap_link" id="navitem_download_signin" href="<?php echo $current_language ?>/signin.aspx"><?php _e('Sign in', 'teamlab-blog-2-0'); ?></a> /
                                                           <a class="nav_item_nowrap_link second" id="navitem_download_signup" href="<?php echo $current_language ?>/registration.aspx"><?php _e('Sign up for cloud', 'teamlab-blog-2-0'); ?></a>
                                                       </li>
                                                       <li><a class="nav_2nd_menu_link" id="navitem_download_onpremises" href="<?php echo $current_language ?>/download-workspace.aspx"><?php _e('Install on-premises', 'teamlab-blog-2-0'); ?></a>
                                                       </li>
                                                   </ul>
                                                   <a id="navitem_link_personal" class="dropdown-item" href="<?php echo $current_language ?>/download-connectors.aspx"><?php _e('Connectors', 'teamlab-blog-2-0'); ?></a>
                                                   <a id="navitem_download_desktop" class="dropdown-item" href="<?php echo $current_language ?>/download-desktop.aspx"><?php _e('Desktop & mobile apps', 'teamlab-blog-2-0'); ?></a>
                                               </ul>
                                            </li>
                                            <li class="pushy-link">
                                                <div class="download-last-area">
                                                    <ul class="download-inner-list">
                                                        <li>
                                                            <a id="navitem_download_docs" class="dropdown-item mobile_no_link"><?php _e('For developers', 'teamlab-blog-2-0'); ?></a>
                                                            <a class="dropdown-item" id="navitem_download_docs_de" href="<?php echo $current_language ?>/download-docs.aspx?from=downloadintegrationmenu#docs-developer"><?php _e('Docs Developer', 'teamlab-blog-2-0'); ?></a>
                                                            <a class="dropdown-item" id="navitem_download_builder" href="<?php echo $current_language ?>/download-builder.aspx"><?php _e('Document Builder', 'teamlab-blog-2-0'); ?></a>
                                                        </li>
                                                        <li>
                                                            <a id="navitem_download_for_community" class="dropdown-item mobile_no_link"><?php _e('For community', 'teamlab-blog-2-0'); ?></a>
                                                            <a class="dropdown-item" id="navitem_download_docs_ce" href="<?php echo $current_language ?>/download-docs.aspx#docs-community"><?php _e('Docs Community', 'teamlab-blog-2-0'); ?></a>
                                                            <a class="dropdown-item" id="navitem_download_download_bundles" href="<?php echo $current_language ?>/download.aspx#bundles"><?php _e('Bundles', 'teamlab-blog-2-0'); ?></a>
                                                            <a class="dropdown-item" id="navitem_download_code_git" href="https://github.com/ONLYOFFICE/" target="_blank" rel="noreferrer noopener"><?php _e('Code on GitHub', 'teamlab-blog-2-0'); ?></a>
                                                        </li>
                                                    </ul>
                                                    <div id="navitem_hosting_third_level_menu">
                                                        <a id="navitem_download_hosting" class="dropdown-item" href="<?php echo $current_language ?>/hosting-providers.aspx"><?php _e('Web hosting', 'teamlab-blog-2-0'); ?></a>
                                                        <div id="compare_div" class="menu_pic_div">
                                                            <p id="hosting_header" class="menu_pic_header">
                                                                <?php _e('Get web hosting from some of the best providers', 'teamlab-blog-2-0'); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </li>
                                    </div>
                                </li>
                                <!--
                              -->
                                <li class="pushy-submenu"><a class="menuitem <?php echo get_locale(); ?>" id="navitem_prices"><?php _e('Pricing', 'teamlab-blog-2-0'); ?></a>
                                    <div id="navitem_prices_menu">
                                        <ul class="dropdown-content">
                                            <li id="navitem_docs_third_level_menu">
                                                <a id="navitem_prices_docs" class="dropdown-item mobile_no_link"><?php _e('For business', 'teamlab-blog-2-0'); ?></a>
                                                <ul class="navitem_2nd_menu">
                                                    <li>
                                                        <a class="dropdown-item" id="navitem_fb_docs_ee" href="<?php echo $current_language ?>/docs-enterprise-prices.aspx"><?php _e('Docs', 'teamlab-blog-2-0'); ?></a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" id="navitem_fb_workspace"  href="<?php echo $current_language ?>/workspace.aspx"><?php _e('Workspace', 'teamlab-blog-2-0'); ?></a>
                                                    </li>
                                                </ul>
                                                <a id="navitem_prices_workspace" class="dropdown-item mobile_no_link"><?php _e('For developers', 'teamlab-blog-2-0'); ?></a>
                                                <ul>
                                                    <li>
                                                        <a class="dropdown-item" id="navitem_developer_edition" href="<?php echo $current_language ?>/developer-edition-prices.aspx"><?php _e('Docs Developer', 'teamlab-blog-2-0'); ?></a>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li id="navitem_reseller_third_level_menu">
                                                <a id="navitem_prices_reseller" class="dropdown-item" href="<?php echo $current_language ?>/find-partners.aspx"><?php _e('Buy from an ONLYOFFICE reseller', 'teamlab-blog-2-0'); ?></a>
                                                <div id="reseller_div" class="menu_pic_div">
                                                    <?php
                                                        if($current_language == WEB_ROOT_URL.'/'.'fr') {
                                                    ?>
                                                        <div id="reseller_img" class="menu_pic_img reseller_img_fr"></div>
                                                    <?php } else { ?>
                                                        <div id="reseller_img" class="menu_pic_img"></div>
                                                    <?php } ?>
                                                    <p id="reseller_header" class="menu_pic_header">
                                                        <?php _e('Find out the list of all the authorized ONLYOFFICE resellers in your area', 'teamlab-blog-2-0'); ?></a>
                                                    </p>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <!--
                                -->
                                <li class="pushy-submenu partnership_menu_item"><a class="menuitem <?php echo get_locale(); ?>" id="navitem_partners"><?php _e('Partners', 'teamlab-blog-2-0'); ?></a>
                                    <div id="navitem_partnership_menu">
                                        <ul class="dropdown-content">
                                            <li class="pushy-link">
                                                <a id="navitem_resellers" class="dropdown-item" href="<?php echo $current_language ?>/resellers.aspx"><?php _e('Resellers', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_hosters" class="dropdown-item" href="<?php echo $current_language ?>/affiliates.aspx"><?php _e('Affiliates', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_technology_partners" class="dropdown-item" href="<?php echo $current_language ?>/technology-partners.aspx"><?php _e('Technology partners', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_find_partners" class="dropdown-item" href="<?php echo $current_language ?>/find-partners.aspx"><?php _e('Find partners', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_submit_request" class="dropdown-item" href="<?php echo $current_language ?>/partnership-request.aspx"><?php _e('Submit request', 'teamlab-blog-2-0'); ?></a>
                                            </li>
                                            <li id="navitem_education_eve_events_third_level_menu">
                                                <a id="navitem_education_eve_events" class="dropdown-item" href="<?php echo $current_language ?>/events.aspx"><?php _e('Events', 'teamlab-blog-2-0'); ?></a>
                                                <div id="education_eve_div" class="menu_pic_div">
                                                    <div id="education_eve_img" class="menu_pic_img"></div>
                                                    <p id="education_eve_header" class="menu_pic_header"><?php _e('Meet the ONLYOFFICE team', 'teamlab-blog-2-0'); ?></p>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <!--
                                -->
                                <li class="pushy-submenu about_menu_item"><a class="menuitem" id="navitem_about"><?php _e('Resources', 'teamlab-blog-2-0'); ?></a>
                                    <div id="navitem_about_menu">
                                        <ul class="dropdown-content">
                                            <li class="pushy-link">
                                                <a id="navitem_about_about" class="dropdown-item" href="<?php echo $current_language ?>/about.aspx"><?php _e('About ONLYOFFICE', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_about_customers" class="dropdown-item" href="<?php echo $current_language ?>/customers.aspx"><?php _e('Customers', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_about_contribute" class="dropdown-item" href="<?php echo $current_language ?>/contribute.aspx"><?php _e('Contribute', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_about_vacancies" class="dropdown-item" href="<?php echo $current_language ?>/vacancies.aspx"><?php _e('Jobs', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_about_awards" class="dropdown-item" href="<?php echo $current_language ?>/awards.aspx"><?php _e('Awards', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_about_events" class="dropdown-item" href="<?php echo $current_language ?>/events.aspx"><?php _e('Events', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_about_giftshop" class="dropdown-item" target="_blank" rel="noreferrer noopener" href="https://shop.spreadshirt.com/onlyoffice"><?php _e('Gift shop', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_about_contacts" class="dropdown-item" href="<?php echo $current_language ?>/contacts.aspx"><?php _e('Contacts', 'teamlab-blog-2-0'); ?></a>
                                            </li>
                                            <li id="navitem_about_third_level_menu">
                                                <a id="navitem_about_blog" class="dropdown-item" href="<?php echo icl_get_home_url() ?>"><?php _e('Blog', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_about_forum" class="dropdown-item" href="https://forum.onlyoffice.com/"><?php _e('Forum', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_about_pressdownloads" class="dropdown-item" href="<?php echo $current_language ?>/press-downloads.aspx"><?php _e('Press downloads', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_about_helpcenter" class="dropdown-item" href="https://helpcenter.onlyoffice.com/"><?php _e('Help Center', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_about_whitepapers" class="dropdown-item" href="<?php echo $current_language ?>/whitepapers.aspx"><?php _e('White papers', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_about_webinars" class="dropdown-item" href="<?php echo $current_language ?>/webinars.aspx"><?php _e('Webinars', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_about_training_courses" class="dropdown-item" href="<?php echo $current_language ?>/training-courses.aspx"><?php _e('Training courses', 'teamlab-blog-2-0'); ?></a>
                                                <?php if(!($current_language == WEB_ROOT_URL.'/'.'zh')) { ?>
                                                    <a id="navitem_about_compare" class="dropdown-item" href="<?php echo $current_language ?>/document-editor-comparison.aspx"><?php _e('Compare to other suites', 'teamlab-blog-2-0'); ?></a>
                                                <?php } ?>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </nav>
                    <?php if (is_home()) {
                    } elseif (is_search()) {
                    } else { ?>
                        <form role="search" method="get" class="serach-header" action="<?php bloginfo('home'); ?>/">
                            <div class="input-header push">
                                <input type="text" name="s" id="s" class="search-field" placeholder="<?php _e('Find news, tips and how-tos', 'teamlab-blog-2-0'); ?>" value="<?php the_search_query(); ?>" />
                            </div>
                        </form>
                    <?php  } ?>
                    <div class="langselector push">
                        <div id="LanguageSelector" class="custom-select">
                            <?php language_selector(array("en","fr", "de", "es", "pt", "it", "cs", "ja", "zh")); ?>
                        </div>
                    </div>
                </div>
                <div class="hidden">
                    <div class="overlay"></div>
                    <div class="popup">
                        <div class="subscribe-blue">
                            <h3><?php _e('Newsletter', 'teamlab-blog-2-0'); ?></h3>
                            <p><?php _e('Get the latest ONLYOFFICE news', 'teamlab-blog-2-0'); ?></p>
                            <div id="InputBox2" class="inputBox forPressPage">
                                <input id="subscribe-email-input2" class="main-input" placeholder="<?php _e('Your email', 'teamlab-blog-2-0') ?>" />
                                <div id="email-sub-button2" class="inputButton pressPage">
                                    <?php _e('Subscribe', 'teamlab-blog-2-0') ?>
                                    <div class="loader"></div>
                                </div>
                                <p class="errorMessage empty"><?php _e('Email is empty', 'teamlab-blog-2-0') ?></p>
                                <p class="errorMessage incorrect"><?php _e('Email is incorrect', 'teamlab-blog-2-0') ?>
                                </p>
                                <p class="errorMessage used"><?php _e('Email is used', 'teamlab-blog-2-0') ?></p>
                                <p class="errorMessage recaptcha"><?php _e('Incorrect recaptcha', 'teamlab-blog-2-0') ?>
                                </p>
                            </div>
                            <span><a href="https://help.onlyoffice.com/products/files/doceditor.aspx?fileid=5048502&doc=SXhWMEVzSEYxNlVVaXJJeUVtS0kyYk14YWdXTEFUQmRWL250NllHNUFGbz0_IjUwNDg1MDIi0" target="_blank"><?php _e('By clicking “Subscribe”, you understand and agree to <u>our Privacy statement</u>', 'teamlab-blog-2-0'); ?></a></span>
                        </div>
                        <div class="subscribe-white">
                            <h4><?php _e('Confirm your subscription', 'teamlab-blog-2-0'); ?></h4>
                            <p><?php _e('We sent an email message with confirmation to your email address', 'teamlab-blog-2-0'); ?>
                            </p>
                        </div>
                        <div class="close-popup"></div>
                    </div>
                </div>
                <div class="overlay-dark"></div>
                <div class="overlay-padding">
                    <img class="img-overlay">
                    <div class="close-overlay"></div>
                </div>

            </header>
        </div>
    </div>