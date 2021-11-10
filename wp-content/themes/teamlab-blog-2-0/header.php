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
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Tag Manager -->
    <script>(function (a, s, y, n, c, h, i, d, e) { s.className += ' ' + y; h.start = 1 * new Date; h.end = i = function () { s.className = s.className.replace(RegExp(' ?' + y), '') }; (a[n] = a[n] || []).hide = h; setTimeout(function () { i(); h.end = null }, c); h.timeout = c; })(window, document.documentElement, 'async-hide', 'dataLayer', 4000, { 'GTM-PMBZ8H3': true });</script>  <script>(function (w, d, s, l, i) {
            w[l] = w[l] || []; w[l].push({
            'gtm.start':
            new Date().getTime(), event: 'gtm.js'
            }); var f = d.getElementsByTagName(s)[0],
            j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
            'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', 'GTM-5NW47TX');
    </script>
    <!-- End Google Tag Manager -->  
    <script>    (function (i, s, o, g, r, a, m) {
                    i['GoogleAnalyticsObject'] = r; i[r] = i[r] || function () {
                        (i[r].q = i[r].q || []).push(arguments)
                    }, i[r].l = 1 * new Date(); a = s.createElement(o),
                    m = s.getElementsByTagName(o)[0]; a.async = 1; a.src = g; m.parentNode.insertBefore(a, m)
                })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

                ga('create', 'UA-12442749-5', 'auto', { 'name': 'www', 'allowLinker': true });
                ga('require', 'linker');
                ga('www.linker:autoLink', ['onlyoffice.com', 'onlyoffice.eu', 'onlyoffice.sg', 'avangate.com']);
                ga('www.send', 'pageview');

                ga('create', 'UA-12442749-21', 'auto', { 'name': 'testTracker', 'allowLinker': true });
                ga('require', 'linker');
                ga('testTracker.linker:autoLink', ['onlyoffice.com', 'onlyoffice.eu', 'onlyoffice.sg', 'avangate.com']);
                ga('testTracker.send', 'pageview');
    </script>
	<meta content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <link rel="icon" href="<?php bloginfo( 'template_directory' ); ?>/images/favicon.ico" type="image/x-icon" />
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
    $banner_url = 'https://www.onlyoffice.com/blog/2021/08/onlyoffice-docs-v6-4-with-conditional-formatting';
    if($current_language == WEB_ROOT_URL.'/'.'de'){
        $banner_url = 'https://www.onlyoffice.com/blog/de/2021/08/onlyoffice-docs-v6-4-mit-bedingter-formatierung-und-neuen-skalierungsoptionen';
    }else if($current_language == WEB_ROOT_URL.'/'.'fr'){
        $banner_url = 'https://www.onlyoffice.com/blog/fr/2021/08/onlyoffice-docs-v6-4-formatage-conditionnel';  
    }else if($current_language == WEB_ROOT_URL.'/'.'es'){
        $banner_url = 'https://www.onlyoffice.com/blog/es/2021/08/onlyoffice-docs-v6-4-con-el-formato-condicional';
    }else if($current_language == WEB_ROOT_URL.'/'.'it'){
        $banner_url = 'https://www.onlyoffice.com/blog/it/2021/08/onlyoffice-docs-v6-4-con-formattazione-condizionale';
    }?>
    <a id="banner_url" href="<?php echo $banner_url ?>">
        <div class="advent-announce-text">
            <?php _e('<b>ONLYOFFICE Docs v6.4</b> with conditional formatting, new scaling options, and WOPI protocol support ', 'teamlab-blog-2-0'); ?>
        </div>
    </a>
</div>
<div class="advent-announce push advent-desktop-hide">
    <a class="advent-announce-text" href="<?php echo $banner_url ?>">
        <?php _e('<b>ONLYOFFICE Docs v6.4 released</b>', 'teamlab-blog-2-0'); ?>
    </a>
</div>
<body <?php body_class(); ?>>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5NW47TX"  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
	<div class="BaseSide">
            <div class="mainpart">
                <div class="site-overlay"></div>
                <header>
                    <div class="narrowheader">
                        <div class="logo push">
                            <a href="<?php echo $current_language ?>"></a>
                        </div>
                        <div class="ham_menu push menu-btn pushy-link">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <div>
                            <?php if ( is_home() ) {
                            }
                        elseif (is_search()){

                        	}
                            else {?>
                                <form role="search" method="get" class="serach-header" action="<?php bloginfo('home'); ?>/">
								  <div class="input-header push">
								  	<input type="text" name="s" id="s" class="search-field" placeholder="<?php _e('Find news, tips and how-tos', 'teamlab-blog-2-0'); ?>" value="<?php the_search_query(); ?>"/>
								</div>
								</form>
                          <?php  } ?>
                        </div>
                        <nav class="pushy pushy-left">
                       
                            <div class="pushy-content">
                                <ul class="all-menu-items">
                                    <li class="pushy-submenu"><a class="menuitem <?php echo get_locale();?>" id="navitem_features"><?php _e('Products & Features', 'teamlab-blog-2-0'); ?></a>
                                        <div id="navitem_features_menu">
                                            <ul class="dropdown-content akkordeon">
                                                <li id="navitem_editors_third_level_menu">
                                                    <a id="navitem_features_editors" class="dropdown-item mobile_no_link" href="<?php echo $current_language ?>/office-suite.aspx"><?php _e('ONLYOFFICE Docs', 'teamlab-blog-2-0'); ?></a>
                                                    <div>
                                                        <ul class="navitem_2nd_menu">
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_document_editor" href="<?php echo $current_language ?>/document-editor.aspx"><?php _e('Document Editor', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_spreadsheet_editor" href="<?php echo $current_language ?>/spreadsheet-editor.aspx"><?php _e('Spreadsheet Editor', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_presentation_editor" href="<?php echo $current_language ?>/presentation-editor.aspx"><?php _e('Presentation Editor', 'teamlab-blog-2-0'); ?></a></li>
                                                        </ul>

                                                    </div>
                                                    <a id="navitem_features_docs_editions" class="dropdown-item mobile_no_link"><?php _e('Docs Editions', 'teamlab-blog-2-0'); ?></a>
                                                    <div>
                                                        <ul class="navitem_2nd_menu">
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_docs_ee" href="<?php echo $current_language ?>/docs-enterprise.aspx"><?php _e('Enterprise Edition', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_docs_de" href="<?php echo $current_language ?>/developer-edition.aspx"><?php _e('Developer Edition', 'teamlab-blog-2-0'); ?></a></li>
                                                        </ul>
                                                    </div>
                                                    <span class="navitem_solutions"><a id="navitem_solutions_clients_apps" class="dropdown-item" href="<?php echo $current_language ?>/desktop.aspx"><?php _e('ONLYOFFICE for desktop', 'teamlab-blog-2-0'); ?></a></span>
                                                    <span class="navitem_solutions"><a id="navitem_solutions_clients_mobile_ios" class="dropdown-item" href="<?php echo $current_language ?>/office-for-ios.aspx"><?php _e('ONLYOFFICE for iOS', 'teamlab-blog-2-0'); ?></a></span>
                                                    <span class="navitem_solutions"><a id="navitem_solutions_clients_mobile_android" class="dropdown-item" href="<?php echo $current_language ?>/office-for-android.aspx"><?php _e('ONLYOFFICE for Android', 'teamlab-blog-2-0'); ?></a></span>
                                                </li>
                                                <li id="navitem_comserver_third_level_menu">
                                                    <a id="navitem_features_comserver" class="dropdown-item mobile_no_link" href="<?php echo $current_language ?>/workspace.aspx"><?php _e('ONLYOFFICE Workspace', 'teamlab-blog-2-0'); ?></a>
                                                    <div>
                                                        <ul class="navitem_2nd_menu">
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_documents" href="<?php echo $current_language ?>/document-management.aspx"><?php _e('Documents', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_mail" href="<?php echo $current_language ?>/mail.aspx"><?php _e('Mail', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_crm" href="<?php echo $current_language ?>/crm.aspx"><?php _e('CRM', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_projects" href="<?php echo $current_language ?>/projects.aspx"><?php _e('Projects', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_calendar" href="<?php echo $current_language ?>/calendar.aspx"><?php _e('Calendar', 'teamlab-blog-2-0'); ?></a></li>
                                                        </ul>
                                                    </div>
                                                    <span class="navitem_solutions"><a id="navitem_solutions_clients_workspace" class="dropdown-item mobile_no_link"><?php _e('Workspace Editions', 'teamlab-blog-2-0'); ?></a></span>
                                                    <div>
                                                        <ul class="navitem_2nd_menu">
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_cloud_service" href="<?php echo $current_language ?>/cloud-office.aspx"><?php _e('Cloud Edition', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_cloud_service" href="<?php echo $current_language ?>/workspace-enterprise.aspx"><?php _e('Enterprise Edition', 'teamlab-blog-2-0'); ?></a></li>
                                                        </ul>
                                                    </div>
                                                </li>
                                                <li id="navitem_security_third_level_menu">
                                                    <a id="navitem_features_security" class="dropdown-item" href="<?php echo $current_language ?>/security.aspx"><?php _e('Security', 'teamlab-blog-2-0'); ?></a>
                                                    <div id="security_div" class="menu_pic_div">
                                                        <div id="security_img" class="menu_pic_img"></div>
                                                        <p id="security_header" class="menu_pic_header"><?php _e('Meet ONLYOFFICE Private Rooms where every symbol you type is encrypted <span class="nowrap">end-to-end</span>', 'teamlab-blog-2-0'); ?></p>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </li><!--
                                --><li class="pushy-submenu"><a class="menuitem" id="navitem_integrations"><?php _e('Integrations', 'teamlab-blog-2-0'); ?></a>
                                    <div id="navitem_integrations_menu">
                                        <ul class="dropdown-content akkordeon">
                                            <li class="li_without_border">
                                                <a id="navitem_integrations_nextcloud" class="dropdown-item" href="<?php echo $current_language ?>/office-for-nextcloud.aspx"><?php _e('Nextcloud', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_integrations_owncloud" class="dropdown-item" href="<?php echo $current_language ?>/office-for-owncloud.aspx"><?php _e('ownCloud', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_integrations_confluence" class="dropdown-item" href="<?php echo $current_language ?>/office-for-confluence.aspx"><?php _e('Confluence', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_integrations_alfresco" class="dropdown-item" href="<?php echo $current_language ?>/office-for-alfresco.aspx"><?php _e('Alfresco', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_integrations_sharepoint" class="dropdown-item" href="<?php echo $current_language ?>/office-for-sharepoint.aspx"><?php _e('SharePoint', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_integrations_liferay" class="dropdown-item" href="<?php echo $current_language ?>/office-for-liferay.aspx"><?php _e('Liferay', 'teamlab-blog-2-0'); ?></a>
                                            </li>
                                            <li>
                                                <a id="navitem_integrations_humhub" class="dropdown-item" href="<?php echo $current_language ?>/office-for-humhub.aspx"><?php _e('HumHub', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_integrations_plone" class="dropdown-item" href="<?php echo $current_language ?>/office-for-plone.aspx"><?php _e('Plone', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_integrations_nuxeo" class="dropdown-item" href="<?php echo $current_language ?>/office-for-nuxeo.aspx"><?php _e('Nuxeo', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_integrations_chamilo" class="dropdown-item" href="<?php echo $current_language ?>/office-for-chamilo.aspx"><?php _e('Chamilo', 'teamlab-blog-2-0'); ?></a>
                                                <a id="navitem_integrations_others" class="dropdown-item" href="<?php echo $current_language ?>/all-connectors.aspx"><?php _e('Others', 'teamlab-blog-2-0'); ?></a>
                                            </li>
                                            <li id="navitem_integration_third_level_menu">
                                                <a id="navitem_integration_for_developers" class="dropdown-item" href="<?php echo $current_language ?>/developer-edition.aspx"><?php _e('For developers', 'teamlab-blog-2-0'); ?></a>
                                                <div id="for_developers_div" class="menu_pic_div">
                                                    <div id="for_developers_img" class="menu_pic_img"></div>
                                                    <p id="for_developers_header" class="menu_pic_header"><?php _e('Integrate ONLYOFFICE Docs to bring document editing to your app users', 'teamlab-blog-2-0'); ?></p>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    </li><!--
                                --><li class="pushy-submenu"><a class="menuitem <?php echo get_locale();?>" id="navitem_prices"><?php _e('Pricing', 'teamlab-blog-2-0'); ?></a>
                                        <div id="navitem_prices_menu">
                                            <ul class="dropdown-content">
                                                <li id="navitem_docs_third_level_menu">
                                                    <a id="navitem_prices_docs" class="dropdown-item mobile_no_link"><?php _e('ONLYOFFICE Docs', 'teamlab-blog-2-0'); ?></a>
                                                    <div>
                                                        <ul class="navitem_2nd_menu">
                                                            <li><a class="nav_2nd_menu_link" id="navitem_prices_server_enterprice" href="<?php echo $current_language ?>/docs-enterprise-prices.aspx"><?php _e('Enterprise Edition', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_prices_integration" href="<?php echo $current_language ?>/developer-edition-prices.aspx"><?php _e('Developer Edition', 'teamlab-blog-2-0'); ?></a></li>
                                                        </ul>
                                                    </div>
                                                    <a id="navitem_prices_workspace" class="dropdown-item mobile_no_link"><?php _e('ONLYOFFICE Workspace', 'teamlab-blog-2-0'); ?></a>
                                                    <div>
                                                        <ul class="navitem_2nd_menu">
                                                            <li><a class="nav_2nd_menu_link" id="navitem_prices_saas" href="<?php echo $current_language ?>/saas.aspx"><?php _e('Cloud Service', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_prices_enterprise" href="<?php echo $current_language ?>/workspace-enterprise-prices.aspx"><?php _e('Server Enterprise', 'teamlab-blog-2-0'); ?></a></li>
                                                        </ul>
                                                    </div>
                                                </li>
                                                <li id="navitem_reseller_third_level_menu">
                                                    <a id="navitem_prices_reseller" class="dropdown-item" href="<?php echo $current_language ?>/find-partners.aspx"><?php _e('Buy from an ONLYOFFICE reseller', 'teamlab-blog-2-0'); ?></a>
                                                    <div id="reseller_div" class="menu_pic_div">
                                                        <div id="reseller_img" class="menu_pic_img"></div>
                                                        <p id="reseller_header" class="menu_pic_header"><?php _e('Find out the list of all the authorized ONLYOFFICE resellers in your area', 'teamlab-blog-2-0'); ?></a></p>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </li><!--
                                --><li class="pushy-submenu"><a class="menuitem <?php echo get_locale();?>" id="navitem_download"><?php _e('Get ONLYOFFICE', 'teamlab-blog-2-0'); ?></a>
                                        <div id="navitem_download_menu">
                                            <ul class="dropdown-content">
                                                <li class="pushy-link">
                                                    <a id="navitem_download_docs" class="dropdown-item" href="<?php echo $current_language ?>/download-docs.aspx?from=downloadintegrationmenu"><?php _e('ONLYOFFICE Docs', 'teamlab-blog-2-0'); ?></a>
                                                    <a class="dropdown-item mobile_no_link" id="navitem_download_workspace"><?php _e('ONLYOFFICE Workspace', 'teamlab-blog-2-0'); ?></a>
                                                    <ul class="navitem_2nd_menu">
                                                        <li><a class="nav_item_nowrap_link" id="navitem_download_signin" href="<?php echo $current_language ?>/signin.aspx"><?php _e('Sign in', 'teamlab-blog-2-0'); ?></a>
                                                            /
                                                            <a class="nav_item_nowrap_link second" id="navitem_download_signup" href="<?php echo $current_language ?>/registration.aspx"><?php _e('Sign up for cloud', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_download_onpremises" href="<?php echo $current_language ?>/download-workspace.aspx"><?php _e('Install on-premises', 'teamlab-blog-2-0'); ?></a></li>
                                                    </ul>
                                                    <a id="navitem_download_desktop" class="dropdown-item" href="<?php echo $current_language ?>/download-desktop.aspx"><?php _e('ONLYOFFICE desktop and mobile apps', 'teamlab-blog-2-0'); ?></a>
                                                    <a class="dropdown-item" id="navitem_download_products" href="<?php echo $current_language ?>/download.aspx"><?php _e('Other products', 'teamlab-blog-2-0'); ?></a>
                                                </li>
                                                <li id="navitem_compare_third_level_menu">
                                                    <a id="navitem_download_compare" class="dropdown-item" href="<?php echo $current_language ?>/compare-editions.aspx"><?php _e('Compare editions', 'teamlab-blog-2-0'); ?></a>
                                                    <div id="compare_div" class="menu_pic_div">
                                                        <div id="compare_img" class="menu_pic_img"></div>
                                                        <p id="compare_header" class="menu_pic_header"><?php _e('Choose the ONLYOFFICE edition that suits you best.', 'teamlab-blog-2-0'); ?></p>
                                                    </div>
                                                </li> 
                                            </ul>
                                        </div>
                                    </li><!--
                                --><li class="pushy-submenu partnership_menu_item"><a class="menuitem <?php echo get_locale();?>" id="navitem_partners"><?php _e('Partners', 'teamlab-blog-2-0'); ?></a>
                                    <div id="navitem_partnership_menu">
                                        <ul class="dropdown-content">
                                            <li class="pushy-link"><a id="navitem_hosters" class="dropdown-item" href="<?php echo $current_language ?>/affiliates.aspx"><?php _e('Affiliates', 'teamlab-blog-2-0'); ?></a></li>
                                            <li class="pushy-link"><a id="navitem_resellers" class="dropdown-item" href="<?php echo $current_language ?>/resellers.aspx"><?php _e('Resellers', 'teamlab-blog-2-0'); ?></a></li>
                                            <li class="pushy-link"><a id="navitem_find_partners" class="dropdown-item" href="<?php echo $current_language ?>/find-partners.aspx"><?php _e('Find partners', 'teamlab-blog-2-0'); ?></a></li>
                                            <li class="pushy-link"><a id="navitem_submit_request" class="dropdown-item" href="<?php echo $current_language ?>/partnership-request.aspx"><?php _e('Submit request', 'teamlab-blog-2-0'); ?></a></li>
                                        </ul>
                                    </div>
                                    </li><!--
                                --><li class="pushy-submenu about_menu_item"><a class="menuitem" id="navitem_about"><?php _e('About', 'teamlab-blog-2-0'); ?></a>
                                        <div id="navitem_about_menu">
                                            <ul class="dropdown-content">
                                                <li class="pushy-link">
                                                    <a id="navitem_about_about" class="dropdown-item" href="<?php echo $current_language ?>/about.aspx"><?php _e('About ONLYOFFICE', 'teamlab-blog-2-0'); ?></a>
                                                    <a id="navitem_about_blog" class="dropdown-item" href="<?php echo icl_get_home_url() ?>"><?php _e('Blog', 'teamlab-blog-2-0'); ?></a>
                                                    <a id="navitem_about_contribute" class="dropdown-item" href="<?php echo $current_language ?>/contribute.aspx"><?php _e('Contribute', 'teamlab-blog-2-0'); ?></a>
                                                    <a id="navitem_about_customers" class="dropdown-item" href="<?php echo $current_language ?>/customers.aspx"><?php _e('Customers', 'teamlab-blog-2-0'); ?></a>
                                                    <a id="navitem_about_awards" class="dropdown-item" href="<?php echo $current_language ?>/awards.aspx"><?php _e('Awards', 'teamlab-blog-2-0'); ?></a>
                                                    <a id="navitem_about_events" class="dropdown-item" href="<?php echo $current_language ?>/events.aspx"><?php _e('Events', 'teamlab-blog-2-0'); ?></a>
                                                    <a id="navitem_about_pressdownloads" class="dropdown-item" href="<?php echo $current_language ?>/press-downloads.aspx"><?php _e('Press downloads', 'teamlab-blog-2-0'); ?></a>
                                                    <a id="navitem_about_whitepapers" class="dropdown-item" href="<?php echo $current_language ?>/whitepapers.aspx"><?php _e('White papers', 'teamlab-blog-2-0'); ?></a>
                                                    <a id="navitem_about_training_courses" class="dropdown-item" href="<?php echo $current_language ?>/training-courses.aspx"><?php _e('Training courses', 'teamlab-blog-2-0'); ?></a>
                                                    <a id="navitem_about_giftshop" class="dropdown-item" target="_blank" rel="noreferrer noopener" href="https://shop.spreadshirt.com/onlyoffice"><?php _e('Gift shop', 'teamlab-blog-2-0'); ?></a>
                                                    <a id="navitem_about_contacts" class="dropdown-item" href="<?php echo $current_language ?>/contacts.aspx"><?php _e('Contacts', 'teamlab-blog-2-0'); ?></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                        <div class="langselector push">
                            <div id="LanguageSelector" class="custom-select">
                                <?php language_selector(array("en","engb","ru","fr","de","es","pt","it","cs")); ?>
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
                                        <div id="email-sub-button2" class="inputButton pressPage"><?php _e('Subscribe', 'teamlab-blog-2-0') ?>
                                        <div class="loader"></div>
                                        </div>
                                        <p class="errorMessage empty"><?php _e('Email is empty', 'teamlab-blog-2-0') ?></p>
                                    <p class="errorMessage incorrect"><?php _e('Email is incorrect', 'teamlab-blog-2-0') ?></p>
                                    <p class="errorMessage used"><?php _e('Email is used', 'teamlab-blog-2-0') ?></p>
                                    <p class="errorMessage recaptcha"><?php _e('Incorrect recaptcha', 'teamlab-blog-2-0') ?></p>
                                 </div>
                                <span><a href="https://help.onlyoffice.com/products/files/doceditor.aspx?fileid=5048502&doc=SXhWMEVzSEYxNlVVaXJJeUVtS0kyYk14YWdXTEFUQmRWL250NllHNUFGbz0_IjUwNDg1MDIi0" target="_blank" ><?php _e('By clicking “Subscribe”, you understand and agree to <u>our Privacy statement</u>', 'teamlab-blog-2-0'); ?></a></span>
                                </div>
                                <div class="subscribe-white">
                                <h4><?php _e('Confirm your subscription', 'teamlab-blog-2-0'); ?></h4>
                                <p><?php _e('We sent an email message with confirmation to your email address', 'teamlab-blog-2-0'); ?></p>
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
	
