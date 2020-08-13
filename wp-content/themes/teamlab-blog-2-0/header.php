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
                            <a href="<?php echo WEB_ROOT_URL?>"></a>
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
								  <div class="input-header">
								  	<input type="text" name="s" id="s" class="search-field" placeholder="<?php _e('Find news, tips and how-tos', 'teamlab-blog-2-0'); ?>" value="<?php the_search_query(); ?>"/>
								</div>
								</form>
                          <?php  } ?>
                        </div>
                        <nav class="pushy pushy-left">
                       
                            <div class="pushy-content">
                                <ul class="all-menu-items"><!--
                                --><li class="pushy-submenu"><a class="menuitem <?php echo get_locale();?>" id="navitem_solutions"><?php _e('Solutions', 'teamlab-blog-2-0'); ?></a>
                                    <div id="navitem_solutions_menu">
                                        <ul class="dropdown-content akkordeon">
                                            <li>
                                                <a id="navitem_solutions_saas" class="dropdown-item mobile_no_link" href="<?php echo WEB_ROOT_URL?>/cloud-office.aspx"><?php _e('Cloud Service', 'teamlab-blog-2-0'); ?></a>
                                                <div>
                                                    <div class="navitem_description"><?php _e('Online documents editors and productivity business tools provided as a SaaS', 'teamlab-blog-2-0'); ?></div>
                                                    <ul class="navitem_2nd_menu">
                                                        <li id="navitem_solutions_saas_mobile"><a class="nav_2nd_menu_link" href="<?php echo WEB_ROOT_URL?>/cloud-office.aspx"><?php _e('Overview', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_registration" href="<?php echo WEB_ROOT_URL?>/registration.aspx?from=menu"><?php _e('No installation required. Get started for ', 'teamlab-blog-2-0'); ?><span class="accented"><?php _e('FREE', 'teamlab-blog-2-0'); ?></span></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_business_tools" href="<?php echo WEB_ROOT_URL?>/cloud-office.aspx#business-tools"><?php _e('Complete set of business tools', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_secure_hosting" href="<?php echo WEB_ROOT_URL?>/cloud-office.aspx#secure-hosting"><?php _e('Secure and reliable hosting', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_nonprofit" href="<?php echo WEB_ROOT_URL?>/nonprofit-organizations.aspx"><?php _e('Cloud for non-profits', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" target="_blank" href="https://personal.onlyoffice.com/"><?php _e('Cloud for personal use', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link signIn" target="_blank" href="<?php echo WEB_ROOT_URL?>/signin.aspx"><?php _e('Sign in', 'teamlab-blog-2-0'); ?></a></li>
                                                    </ul>
                                                </div>
                                            </li>
                                            <li>
                                                <a id="navitem_solutions_server_enterprice" class="dropdown-item mobile_no_link" href="<?php echo WEB_ROOT_URL?>/server-solutions.aspx"><?php _e('Enterprise Edition', 'teamlab-blog-2-0'); ?></a>
                                                <div>
                                                    <div class="navitem_description"><?php _e('Online document editors and productivity business tools at private network', 'teamlab-blog-2-0'); ?></div>
                                                    <ul class="navitem_2nd_menu">
                                                        <li id="navitem_solutions_server_enterprice_mobile"><a class="nav_2nd_menu_link" href="<?php echo WEB_ROOT_URL?>/server-solutions.aspx"><?php _e('Overview', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_enterprise_request" href="<?php echo WEB_ROOT_URL?>/enterprise-edition-free.aspx?from=enterprisemenu"><?php _e('Self-hosted. Try for', 'teamlab-blog-2-0'); ?> <span class="accented"><?php _e('FREE', 'teamlab-blog-2-0'); ?></span></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_complete_set" href="<?php echo WEB_ROOT_URL?>/server-solutions.aspx#complete-set"><?php _e('Complete set of business tools', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_security" href="<?php echo WEB_ROOT_URL?>/security.aspx"><?php _e('Enhanced security measures', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_installation_options" href="<?php echo WEB_ROOT_URL?>/server-solutions.aspx#installation-options"><?php _e('Various installation options', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_enterprise_support" href="<?php echo WEB_ROOT_URL?>/support.aspx?from=enterprise"><?php _e('Professional support', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_enterprise_compare" href="<?php echo WEB_ROOT_URL?>/compare-server-editions.aspx"><?php _e('Community Edition vs Enterprise Edition', 'teamlab-blog-2-0'); ?></a></li>
                                                    </ul>
                                                </div>
                                            </li>
                                            <li id="navitem_connectors_third_level_menu">
                                                <a id="navitem_solutions_connectors" class="dropdown-item mobile_no_link" href="<?php echo WEB_ROOT_URL?>/connectors.aspx"><?php _e('Integration Edition', 'teamlab-blog-2-0'); ?></a>
                                                <div>
                                                    <div class="navitem_description"><?php _e('Online document editors and connectors for popular web services', 'teamlab-blog-2-0'); ?></div>
                                                    <ul class="navitem_2nd_menu">
                                                        <li id="navitem_solutions_connectors_mobile"><a class="nav_2nd_menu_link" href="<?php echo WEB_ROOT_URL?>/connectors.aspx"><?php _e('Overview', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_integration_request" href="<?php echo WEB_ROOT_URL?>/connectors-request.aspx?from=integrationmenu"><?php _e('Self-hosted. Get started', 'teamlab-blog-2-0'); ?> <span class="accented"><?php _e('NOW', 'teamlab-blog-2-0'); ?></span></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_connector_nextcloud" href="<?php echo WEB_ROOT_URL?>/connectors-nextcloud.aspx"><?php _e('For Nextcloud', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_connector_owncloud" href="<?php echo WEB_ROOT_URL?>/connectors-owncloud.aspx"><?php _e('For ownCloud', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_connector_alfresco" href="<?php echo WEB_ROOT_URL?>/connectors-alfresco.aspx"><?php _e('For Alfresco', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_connector_confluence" href="<?php echo WEB_ROOT_URL?>/connectors-confluence.aspx"><?php _e('For Confluence', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_connector_sharepoint" href="<?php echo WEB_ROOT_URL?>/connectors-sharepoint.aspx"><?php _e('For SharePoint', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_connector_thirdparty" href="<?php echo WEB_ROOT_URL?>/third-party-connectors.aspx"><?php _e('Third-party connectors', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_integration_support" href="<?php echo WEB_ROOT_URL?>/support.aspx?from=integration"><?php _e('Professional support', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_integration_compare" href="<?php echo WEB_ROOT_URL?>/compare-editions.aspx"><?php _e('Community Edition vs Integration Edition', 'teamlab-blog-2-0'); ?></a></li>
                                                    </ul>
                                                </div>
                                                
                                            </li>
                                            <li>
                                                <a id="navitem_solutions_integration" class="dropdown-item mobile_no_link" href="<?php echo WEB_ROOT_URL?>/developer-edition.aspx"><?php _e('Developer Edition', 'teamlab-blog-2-0'); ?></a>
                                                <div>
                                                    <div class="navitem_description"><?php _e('Online document editors to integrate with a service you are building', 'teamlab-blog-2-0'); ?></div>
                                                    <ul class="navitem_2nd_menu">
                                                        <li id="navitem_solutions_integration_mobile"><a class="nav_2nd_menu_link" href="<?php echo WEB_ROOT_URL?>/developer-edition.aspx"><?php _e('Overview', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_developer_request" href="<?php echo WEB_ROOT_URL?>/developer-edition-request.aspx?from=developermenu"><?php _e('Self-hosted. Try for', 'teamlab-blog-2-0'); ?> <span class="accented"><?php _e('FREE', 'teamlab-blog-2-0'); ?></span></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_developer_licensing" href="<?php echo WEB_ROOT_URL?>/developer-edition.aspx#dual-license"><?php _e('Dual licensing', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" target="_blank" href="https://api.onlyoffice.com/"><?php _e('API', 'teamlab-blog-2-0'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_docbuilder" href="<?php echo WEB_ROOT_URL?>/document-builder.aspx"><?php _e('Document Builder', 'teamlab-blog-2-0'); ?></a></li>
                                                    </ul>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="latestReleasesBox">
                                            <a href="<?php echo WEB_ROOT_URL?>/news.aspx"><?php _e('Latest Releases', 'teamlab-blog-2-0'); ?></a>
                                        </div>
                                    </div>
                                    </li><!--
                                --><li class="pushy-submenu"><a class="menuitem <?php echo get_locale();?>" id="navitem_features"><?php _e('Features', 'teamlab-blog-2-0'); ?></a>
                                        <div id="navitem_features_menu">
                                            <ul class="dropdown-content akkordeon">
                                                <li id="navitem_editors_third_level_menu">
                                                    <a id="navitem_features_editors" class="dropdown-item mobile_no_link" href="<?php echo WEB_ROOT_URL?>/office-suite.aspx"><?php _e('Online Editors', 'teamlab-blog-2-0'); ?></a>
                                                    <div>
                                                        <ul class="navitem_2nd_menu">
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_document_editor" href="<?php echo WEB_ROOT_URL?>/document-editor.aspx"><?php _e('Documents', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_spreadsheet_editor" href="<?php echo WEB_ROOT_URL?>/spreadsheet-editor.aspx"><?php _e('Spreadsheets', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_presentation_editor" href="<?php echo WEB_ROOT_URL?>/presentation-editor.aspx"><?php _e('Presentations', 'teamlab-blog-2-0'); ?></a></li>
                                                            
                                                        </ul>

                                                    </div>
                                                    <span class="navitem_solutions"><a id="navitem_solutions_clients_apps" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/apps.aspx"><?php _e('Desktop Editors', 'teamlab-blog-2-0'); ?></a></span>
                                                    <span class="navitem_solutions"><a id="navitem_solutions_clients_ios" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/office-for-ios.aspx"><?php _e('Office for iOS', 'teamlab-blog-2-0'); ?></a></span>
                                                    <span class="navitem_solutions"><a id="navitem_solutions_clients_android" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/office-for-android.aspx"><?php _e('Office for Android', 'teamlab-blog-2-0'); ?></a></span>
                                                    <span class="navitem_solutions"><a id="navitem_solutions_clients_mobile" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/mobile-projects.aspx"><?php _e('Mobile Projects', 'teamlab-blog-2-0'); ?></a></span>
                                                </li>
                                                <li id="navitem_comserver_third_level_menu">
                                                    <a id="navitem_features_comserver" class="dropdown-item mobile_no_link" href="<?php echo WEB_ROOT_URL?>/collaboration-platform.aspx"><?php _e('Collaboration Platform', 'teamlab-blog-2-0'); ?></a>
                                                    <div>
                                                        <ul class="navitem_2nd_menu">
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_documents" href="<?php echo WEB_ROOT_URL?>/document-management.aspx"><?php _e('Document Management', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_mail" href="<?php echo WEB_ROOT_URL?>/mail.aspx"><?php _e('Mail', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_crm" href="<?php echo WEB_ROOT_URL?>/crm.aspx"><?php _e('CRM', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_projects" href="<?php echo WEB_ROOT_URL?>/projects.aspx"><?php _e('Projects', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_calendar" href="<?php echo WEB_ROOT_URL?>/calendar.aspx"><?php _e('Calendar', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_network" href="<?php echo WEB_ROOT_URL?>/community.aspx"><?php _e('Community', 'teamlab-blog-2-0'); ?></a></li>
                                                        </ul>
                                                    </div>
                                                </li>
                                                <li id="navitem_compare_third_level_menu">
                                                    <a id="navitem_solutions_compare" class="dropdown-item mobile_no_link" href="<?php echo WEB_ROOT_URL?>/document-editor-comparison.aspx"><?php _e ('ONLYOFFICE alternatives', 'teamlab-blog-2-0'); ?></a>
                                                    <div>
                                                        <ul class="navitem_2nd_menu">
                                                            <li id="navitem_solutions_apps_comparison_overview"><a class="nav_2nd_menu_link" href="<?php echo WEB_ROOT_URL?>/document-editor-comparison.aspx"><?php _e ('Overview', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_compare_msoffice" href="<?php echo WEB_ROOT_URL?>/best-microsoft-office-alternative.aspx"><?php _e ('ONLYOFFICE vs MS Office Online', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_compare_google" href="<?php echo WEB_ROOT_URL?>/best-google-docs-alternative.aspx"><?php _e ('ONLYOFFICE vs Google Docs', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_compare_zoho" href="<?php echo WEB_ROOT_URL?>/best-zoho-docs-alternative.aspx"><?php _e ('ONLYOFFICE vs Zoho Docs', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_compare_collabora" href="<?php echo WEB_ROOT_URL?>/best-collabora-alternative.aspx"><?php _e ('ONLYOFFICE vs Collabora Online', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_compare_libreoffice" href="<?php echo WEB_ROOT_URL?>/best-libreoffice-alternative.aspx"><?php _e ('ONLYOFFICE vs LibreOffice', 'teamlab-blog-2-0'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_compare_office365_gsuite" href="<?php echo WEB_ROOT_URL?>/compare-solutions.aspx"><?php _e ('ONLYOFFICE vs Office 365 vs G Suite', 'teamlab-blog-2-0'); ?></a></li>
                                                        </ul>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </li><!--
                                --><li class="pushy-submenu"><a class="menuitem <?php echo get_locale();?>" id="navitem_prices"><?php _e('Pricing', 'teamlab-blog-2-0'); ?></a>
                                        <div id="navitem_prices_menu">
                                            <ul class="dropdown-content">
                                                <li class="pushy-link"><a id="navitem_prices_server_enterprice" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/enterprise-edition.aspx"><?php _e('Enterprise Edition', 'teamlab-blog-2-0'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_prices_connectors" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/integration-edition-prices.aspx"><?php _e('Integration Edition', 'teamlab-blog-2-0'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_prices_integration" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/developer-edition-prices.aspx"><?php _e('Developer Edition', 'teamlab-blog-2-0'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_prices_saas" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/saas.aspx"><?php _e('Cloud Service', 'teamlab-blog-2-0'); ?></a></li>
                                            </ul>
                                        </div>
                                    </li><!--
                                --><li class="pushy-submenu"><a class="menuitem <?php echo get_locale();?>" id="navitem_download"><?php _e('Download', 'teamlab-blog-2-0'); ?></a>
                                        <div class="navitem_download_menu" id="navitem_download_menu">
                                            <ul class="dropdown-content">
                                                <li class="pushy-link" id="navitem_downloadserver_third_level_menu"><a id="navitem_download_enterprise" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/enterprise-edition-free.aspx?from=downloadenterprisemenu"><?php _e('Enterprise Edition', 'teamlab-blog-2-0'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_download_connectors" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/connectors-request.aspx?from=downloadintegrationmenu"><?php _e('Integration Edition', 'teamlab-blog-2-0'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_download_integration" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/developer-edition-request.aspx?from=downloaddevelopermenu"><?php _e('Developer Edition', 'teamlab-blog-2-0'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_download_desktop" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/download-desktop.aspx"><?php _e('Desktop Editors', 'teamlab-blog-2-0'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_download_doc_builder" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/download-document-builder.aspx"><?php _e('Document Builder', 'teamlab-blog-2-0'); ?></a></li>
                                            </ul>
                                        </div>
                                    </li><!--
                                --><li class="pushy-submenu"><a class="menuitem <?php echo get_locale();?>" id="navitem_partners"><?php _e('Partnership', 'teamlab-blog-2-0'); ?></a>
                                    <div class="navitem_partnership_menu" id="navitem_partnership_menu">
                                        <ul class="dropdown-content">
                                            <li class="pushy-link"><a id="navitem_resellers" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/resellers.aspx"><?php _e('For Resellers', 'teamlab-blog-2-0'); ?></a></li>
                                            <li class="pushy-link"><a id="navitem_hosters" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/affiliates.aspx"><?php _e('For Affiliates', 'teamlab-blog-2-0'); ?></a></li>
                                            <li class="pushy-link"><a id="navitem_developers" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/partnership-developers.aspx"><?php _e('For Developers', 'teamlab-blog-2-0'); ?></a></li>
                                            <li class="pushy-link"><a id="navitem_find_partners" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/find-partners.aspx"><?php _e('Find partners', 'teamlab-blog-2-0'); ?></a></li>
                                            <li class="pushy-link"><a id="navitem_sub_request" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/partnership-request.aspx"><?php _e('Submit request', 'teamlab-blog-2-0'); ?></a></li>
                                        </ul>
                                    </div>
                                    </li><!--
                                --><li class="pushy-submenu about_menu_item"><a class="menuitem" id="navitem_about"><?php _e('About', 'teamlab-blog-2-0'); ?></a>
                                        <div id="navitem_about_menu">
                                            <ul class="dropdown-content">
                                                <li class="pushy-link"><a id="navitem_about_about" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/about.aspx"><?php _e('About ONLYOFFICE', 'teamlab-blog-2-0'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_blog" class="dropdown-item" target="_blank" href="<?php echo WEB_ROOT_URL?>/blog/"><?php _e('Blog', 'teamlab-blog-2-0'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_contribute" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/contribute.aspx"><?php _e('Contribute', 'teamlab-blog-2-0'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_customers" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/customers.aspx"><?php _e('Customers', 'teamlab-blog-2-0'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_awards" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/awards.aspx"><?php _e('Awards', 'teamlab-blog-2-0'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_events" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/events.aspx"><?php _e('Events', 'teamlab-blog-2-0'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_webinars" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/webinars.aspx"><?php _e('Webinars', 'teamlab-blog-2-0'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_press" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/press-downloads.aspx"><?php _e('Press downloads', 'teamlab-blog-2-0'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_contacts" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/contacts.aspx"><?php _e('Contacts', 'teamlab-blog-2-0'); ?></a></li>
                                            </ul>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            
                        </nav>
                        <div class="langselector push">
                            <div id="LanguageSelector" class="custom-select">
                                <?php language_selector(array("en","engb","de","ru","fr","cs","es")); ?>
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
                </header>
            </div>
        </div>
	
