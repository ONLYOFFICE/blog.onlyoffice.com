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
<div class="advent-announce push advent-mobile-hide <?php echo $lang ?>">
    <?php
        $banner_url = 'https://www.onlyoffice.com/blog/2023/04/meet-onlyoffice-docspace/';
        if($current_language == WEB_ROOT_URL.'/'.'de'){
            $banner_url = 'https://www.onlyoffice.com/blog/de/2023/04/docspace/';
        }else if($current_language == WEB_ROOT_URL.'/'.'es'){
            $banner_url = 'https://www.onlyoffice.com/blog/es/2023/04/descubre-onlyoffice-docspace/';
        }else if($current_language == WEB_ROOT_URL.'/'.'pt-br'){
            $banner_url = 'https://www.onlyoffice.com/blog/pt-br/2023/04/conheca-o-onlyoffice-docspace/';
        }else if($current_language == WEB_ROOT_URL.'/'.'it'){
            $banner_url = 'https://www.onlyoffice.com/blog/it/2023/04/onlyoffice-docspace/';
        }else if($current_language == WEB_ROOT_URL.'/'.'ja'){
            $banner_url = 'https://www.onlyoffice.com/blog/ja/2023/04/onlyoffice-docspace/';
        }else if($current_language == WEB_ROOT_URL.'/'.'zh-hans'){
            $banner_url = 'https://www.onlyoffice.com/blog/zh-hans/2023/04/meet-onlyoffice-docspace/';
        }
    ?>
    <a href="<?php echo $banner_url ?>">
        <div class="advent-announce-text">
            <?php _e('<b>ONLYOFFICE DocSpace released:</b> improve document collaboration with offices, customers, and partners. <b>Use it for free!</b>', 'teamlab-blog-2-0'); ?>
        </div>
    </a>
</div>
<div class="advent-announce push advent-desktop-hide">
    <a  href="<?php echo $banner_url ?>">
        <div class="advent-announce-text">
            <?php _e('<b>ONLYOFFICE DocSpace</b> released', 'teamlab-blog-2-0'); ?>
        </div>
    </a>
</div>

<body <?php body_class(); ?>>
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
                                <li class="pushy-submenu"><a class="menuitem <?php echo get_locale(); ?>" id="navitem_features"><?php _e('Products', 'teamlab-blog-2-0'); ?></a>
                                    <div id="navitem_features_menu">
                                        <ul class="dropdown-content akkordeon">
                                            <li id="navitem_editors_third_level_menu">
                                                <ul class="navitem_2nd_menu">
                                                    <li>
                                                        <a class="dropdown-item" id="navitem_features_docs_overview" href="<?php echo $current_language ?>/office-suite.aspx"><?php _e('Docs', 'teamlab-blog-2-0'); ?></a>
                                                        <p class="features_info"><?php _e('Editors to integrate into your business platform', 'teamlab-blog-2-0'); ?></p>
                                                        <a class="dropdown-item" id="navitem_features_docspace" href="<?php echo $current_language ?>/docspace.aspx"><?php _e('DocSpace', 'teamlab-blog-2-0'); ?></a>
                                                        <p class="features_info"><?php _e('Platform to collaborate with your partners and clients', 'teamlab-blog-2-0'); ?></p>
                                                        <a class="dropdown-item" id="navitem_features_workspace" href="<?php echo $current_language ?>/workspace.aspx"><?php _e('Workspace', 'teamlab-blog-2-0'); ?></a>
                                                        <p class="features_info"><?php _e('Platform to collaborate with your team', 'teamlab-blog-2-0'); ?></p>
                                                        <a class="dropdown-item" id="navitem_features_connectors" href="<?php echo $current_language ?>/all-connectors.aspx"><?php _e('Connectors', 'teamlab-blog-2-0'); ?></a>
                                                        <p class="features_info"><?php _e('Ready-to-use apps to integrate Docs with your platform', 'teamlab-blog-2-0'); ?></p>
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
                                                <a id="navitem_features_perform_task" class="dropdown-item mobile_no_link"><?php _e('Perform your tasks online', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_features_fill_forms" class="dropdown-item" href="https://oforms.onlyoffice.com/?_ga=2.65702894.1943010297.1683094995-2135282031.1669802332"><?php _e('Find and fill out oforms', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_features_convert_text" class="dropdown-item" href="<?php echo $current_language ?>/text-file-converter.aspx"><?php _e('Convert text files', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_features_convert_speadsheets" class="dropdown-item" href="<?php echo $current_language ?>/spreadsheet-converter.aspx"><?php _e('Convert spreadsheets', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_features_convert_presentations" class="dropdown-item" href="<?php echo $current_language ?>/presentation-converter.aspx"><?php _e('Convert presentations', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_features_convert_pdf" class="dropdown-item" href="<?php echo $current_language ?>/pdf-converter.aspx"><?php _e('Convert PDFs', 'teamlab-blog-2-0'); ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <!--
                                -->
                                <li class="pushy-submenu"><a class="menuitem" id="navitem_integrations" href="<?php echo $current_language ?>/for-enterprises.aspx"><?php _e('Enterprise', 'teamlab-blog-2-0'); ?></a></li>
                                <!--
                               -->
                                <li class="pushy-submenu"><a class="menuitem" id="navitem_developers"><?php _e('Developers', 'teamlab-blog-2-0'); ?></a>
                                    <div id="navitem_developers_menu">
                                        <ul class="dropdown-content akkordeon">
                                            <li class="pushy-link">
                                                <a class="dropdown-item" id="navitem_document_developer" href="<?php echo $current_language ?>/developer-edition.aspx"><?php _e('Docs Developer', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_fd_conversion_api" href="<?php echo $current_language ?>/conversion-api.aspx"><?php _e('Conversion API', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_fd_doc_builder" href="<?php echo $current_language ?>/document-builder.aspx"><?php _e('Document Builder', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_fd_api" href="https://api.onlyoffice.com/?_ga=2.72517459.1943010297.1683094995-2135282031.1669802332" target="_blank" rel="noreferrer noopener"><?php _e('API Documentation', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_fd_pricing" href="<?php echo $current_language ?>/developer-edition-prices.aspx"><?php _e('Pricing', 'teamlab-blog-2-0'); ?></a>
                                                <a class="dropdown-item" id="navitem_fd_get" href="<?php echo $current_language ?>/download-docs.aspx?from=downloadintegrationmenu#docs-developer"><?php _e('Get it now', 'teamlab-blog-2-0'); ?></a>
                                            </li>
                                            <li id="navitem_security_third_level_menu">
                                                <a id="navitem_features_see_it" class="dropdown-item" href="<?php echo $current_language ?>/see-it-in-action.aspx"><?php _e('See it in action!', 'teamlab-blog-2-0'); ?></a>
                                                <a id="oforms_div" class="menu_pic_div" href="<?php echo $current_language ?>/see-it-in-action.aspx">
                                                    <div id="see_it_img" class="menu_pic_img"></div>
                                                    <p id="see_it_action_header" class="menu_pic_header">
                                                    <?php _e('Curious to know what the interface looks like and try the main functionality?', 'teamlab-blog-2-0'); ?>
                                                    </p>
                                                </a>
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
                                                   <a class="dropdown-item" id="navitem_download_docspace"><?php _e('DocSpace', 'teamlab-blog-2-0'); ?></a>
                                                   <ul class="navitem_2nd_menu">
                                                       <li>
                                                           <a class="nav_item_nowrap_link" id="navitem_docspace_signup" href="<?php echo $current_language ?>/docspace-registration.aspx"><?php _e('Sign up for cloud', 'teamlab-blog-2-0'); ?></a>
                                                       </li>
                                                   </ul>
                                                   <a class="dropdown-item" id="navitem_download_docs_ee"><?php _e('Docs Enterprise', 'teamlab-blog-2-0'); ?></a>
                                                   <ul class="navitem_2nd_menu">
                                                       <li>
                                                           <a class="nav_item_nowrap_link" id="navitem_docs_signup" href="<?php echo $current_language ?>/docs-registration.aspx"><?php _e('Sign up for cloud', 'teamlab-blog-2-0'); ?></a>
                                                       </li>
                                                       <li>
                                                            <a class="nav_2nd_menu_link" id="navitem_docs_onpremises" href="<?php echo $current_language ?>/download-docs.aspx?from=downloadintegrationmenu#docs-enterprise"><?php _e('Install on-premises', 'teamlab-blog-2-0'); ?></a>
                                                       </li>
                                                   </ul>
                                                   <a class="dropdown-item" id="navitem_fb_workspace"><?php _e('Workspace', 'teamlab-blog-2-0'); ?></a>
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
                                                            <a class="dropdown-item" id="navitem_download_docs_de"><?php _e('Docs Developer', 'teamlab-blog-2-0'); ?></a>
                                                            <ul class="navitem_2nd_menu">
                                                                <li>
                                                                    <a class="nav_item_nowrap_link" id="navitem_docs_de_onpremises" href="<?php echo $current_language ?>/download-docs.aspx?from=downloadintegrationmenu#docs-developer"><?php _e('Install on-premises', 'teamlab-blog-2-0'); ?></a>
                                                                </li>
                                                            </ul>
                                                            <a class="dropdown-item" id="navitem_download_builder" href="<?php echo $current_language ?>/download-builder.aspx"><?php _e('Document Builder', 'teamlab-blog-2-0'); ?></a>
                                                        </li>
                                                        <li>
                                                            <a id="navitem_download_for_community" class="dropdown-item mobile_no_link"><?php _e('For community', 'teamlab-blog-2-0'); ?></a>
                                                            <a class="dropdown-item" id="navitem_download_docs_ce" href="<?php echo $current_language ?>/download-docs.aspx#docs-community"><?php _e('Docs Community', 'teamlab-blog-2-0'); ?></a>
                                                            <a class="dropdown-item" id="navitem_download_code_git" href="https://github.com/ONLYOFFICE/" target="_blank" rel="noreferrer noopener"><?php _e('Code on GitHub', 'teamlab-blog-2-0'); ?></a>
                                                        </li>
                                                    </ul>
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
                                                        <a class="dropdown-item" id="navitem_fb_docs_ee" href="<?php echo $current_language ?>/docspace-prices.aspx"><?php _e('DocSpace', 'teamlab-blog-2-0'); ?></a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" id="navitem_fb_docs_enterprice"  href="<?php echo $current_language ?>/docs-enterprise-prices.aspx"><?php _e('Docs Enterprise', 'teamlab-blog-2-0'); ?></a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" id="navitem_fb_workspace"  href="<?php echo $current_language ?>/workspace-prices.aspx"><?php _e('Workspace', 'teamlab-blog-2-0'); ?></a>
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
                                                <a id="navitem_hosting_providers" class="dropdown-item" href="<?php echo $current_language ?>/hosting-providers.aspx"><?php _e('Hosting providers', 'teamlab-blog-2-0'); ?></a>
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
                                <input type="text" name="s" id="s" class="search-field" placeholder="<?php _e('Search blog', 'teamlab-blog-2-0'); ?>" value="<?php the_search_query(); ?>" />
                            </div>
                        </form>
                    <?php  } ?>
                    <div class="langselector push">
                        <div id="LanguageSelector" class="custom-select">
                            <?php language_selector(array("en","fr", "de", "es", "pt", "it", "cs", "ja", "zh", "el")); ?>
                        </div>
                    </div>
                </div>
                <div class="hidden">
                    <div class="overlay"></div>
                    <div class="popup">
                        <?php include get_template_directory() . '/' . 'subscribe-blue.php' ?>
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