<?php
    /**
     * The Header for our theme.
     *
     * Displays all of the <head> section and everything up till <div id="main">
     *
     * @package WordPress
     * @subpackage Twenty_Ten
     * @since Twenty Ten 1.0
     */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>.async-hide { opacity: 1 !important} </style> <script>(function (a, s, y, n, c, h, i, d, e) { s.className += ' ' + y; h.start = 1 * new Date; h.end = i = function () { s.className = s.className.replace(RegExp(' ?' + y), '') }; (a[n] = a[n] || []).hide = h; setTimeout(function () { i(); h.end = null }, c); h.timeout = c; })(window, document.documentElement, 'async-hide', 'dataLayer', 4000, { 'GTM-PMBZ8H3': true });</script>
        <!-- Google Tag Manager -->
        <script>(function (w, d, s, l, i) {
            w[l] = w[l] || []; w[l].push({
            'gtm.start':
            new Date().getTime(), event: 'gtm.js'
            }); var f = d.getElementsByTagName(s)[0],
            j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
            'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', 'GTM-5NW47TX');</script>
            <!-- End Google Tag Manager -->
            <script>
                (function (i, s, o, g, r, a, m) {
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
        <title>
            <?php
                /*
                * Print the <title> tag based on what is being viewed.
                * We filter the output of wp_title() a bit -- see
                * tmblog_filter_wp_title() in functions.php.
                */
                wp_title( '-', true, 'right' );
            ?>
        </title>
        <link href='https://fonts.googleapis.com/css?family=Open+Sans:900,800,700,600,500,400,300&subset=latin,cyrillic-ext,cyrillic,latin-ext' rel="stylesheet" type="text/css" />
        <link rel="icon" href="<?php bloginfo( 'template_directory' ); ?>/images/favicon.ico" type="image/x-icon" />
        <link rel="profile" href="http://gmpg.org/xfn/11" />

        <?php add_action( 'wp_enqueue_scripts', 'add_my_theme_stylesheet' ); ?>

        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

        <?php add_action( 'wp_enqueue_scripts', 'add_my_theme_js' ); ?>
        <?php
            /* We add some JavaScript to pages with the comment form
             * to support sites with threaded comments (when in use).
             */
            if ( is_singular() && get_option( 'thread_comments' ) )
                wp_enqueue_script( 'comment-reply' );
            /* Always have wp_head() just before the closing </head>
             * tag of your theme, or you will break many plugins, which
             * generally use this hook to add elements to <head> such
             * as styles, scripts, and meta tags.
             */
            wp_head();
        ?>
    </head>

    <body <?php body_class(); ?>>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5NW47TX"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
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
                        <div class="langselector push">
                                <div id="LanguageSelector" class="custom-select">
                                <?php language_selector(array("en","engb","de","ru","fr","cs","es")); ?>
                            </div>
                        </div>
                        <nav class="pushy pushy-left">
                       
                            <div class="pushy-content">
                                <ul class="all-menu-items"><!--
                                --><li class="pushy-submenu"><a class="menuitem <?php echo get_locale();?>" id="navitem_solutions"><?php _e('Products', 'tmblog'); ?></a>
                                    <div id="navitem_solutions_menu">
                                        <ul class="dropdown-content akkordeon">
                                            <li>
                                                <a id="navitem_solutions_saas" class="dropdown-item mobile_no_link" href="<?php echo WEB_ROOT_URL?>/cloud-office.aspx"><?php _e('Cloud Service', 'tmblog'); ?></a>
                                                <div>
                                                    <div class="navitem_description"><?php _e('Online documents editors and productivity business tools provided as a SaaS', 'tmblog'); ?></div>
                                                    <ul class="navitem_2nd_menu">
                                                        <li id="navitem_solutions_saas_mobile"><a class="nav_2nd_menu_link" href="<?php echo WEB_ROOT_URL?>/cloud-office.aspx"><?php _e('Overview', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_registration" href="<?php echo WEB_ROOT_URL?>/registration.aspx?from=menu"><?php _e('No installation required. Get started for ', 'tmblog'); ?><span class="accented"><?php _e('FREE', 'tmblog'); ?></span></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_business_tools" href="<?php echo WEB_ROOT_URL?>/cloud-office.aspx#business-tools"><?php _e('Complete set of business tools', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_secure_hosting" href="<?php echo WEB_ROOT_URL?>/cloud-office.aspx#secure-hosting"><?php _e('Secure and reliable hosting', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_nonprofit" href="<?php echo WEB_ROOT_URL?>/nonprofit-organizations.aspx"><?php _e('Cloud for non-profits', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" target="_blank" href="https://personal.onlyoffice.com/"><?php _e('Cloud for personal use', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link signIn" target="_blank" href="<?php echo WEB_ROOT_URL?>/signin.aspx"><?php _e('Sign in', 'tmblog'); ?></a></li>
                                                    </ul>
                                                </div>
                                            </li>
                                            <li>
                                                <a id="navitem_solutions_server_enterprice" class="dropdown-item mobile_no_link" href="<?php echo WEB_ROOT_URL?>/server-solutions.aspx"><?php _e('Enterprise Edition', 'tmblog'); ?></a>
                                                <div>
                                                    <div class="navitem_description"><?php _e('Online document editors and productivity business tools at private network', 'tmblog'); ?></div>
                                                    <ul class="navitem_2nd_menu">
                                                        <li id="navitem_solutions_server_enterprice_mobile"><a class="nav_2nd_menu_link" href="<?php echo WEB_ROOT_URL?>/server-solutions.aspx"><?php _e('Overview', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_enterprise_request" href="<?php echo WEB_ROOT_URL?>/enterprise-edition-free.aspx?from=enterprisemenu"><?php _e('Self-hosted. Try for', 'tmblog'); ?> <span class="accented"><?php _e('FREE', 'tmblog'); ?></span></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_complete_set" href="<?php echo WEB_ROOT_URL?>/server-solutions.aspx#complete-set"><?php _e('Complete set of business tools', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_security" href="<?php echo WEB_ROOT_URL?>/security.aspx"><?php _e('Enhanced security measures', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_installation_options" href="<?php echo WEB_ROOT_URL?>/server-solutions.aspx#installation-options"><?php _e('Various installation options', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_enterprise_support" href="<?php echo WEB_ROOT_URL?>/support.aspx?from=enterprise"><?php _e('Professional support', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_enterprise_compare" href="<?php echo WEB_ROOT_URL?>/compare-server-editions.aspx"><?php _e('Community Edition vs Enterprise Edition', 'tmblog'); ?></a></li>
                                                    </ul>
                                                </div>
                                            </li>
                                            <li id="navitem_connectors_third_level_menu">
                                                <a id="navitem_solutions_connectors" class="dropdown-item mobile_no_link" href="<?php echo WEB_ROOT_URL?>/connectors.aspx"><?php _e('Integration Edition', 'tmblog'); ?></a>
                                                <div>
                                                    <div class="navitem_description"><?php _e('Online document editors and connectors for popular web services', 'tmblog'); ?></div>
                                                    <ul class="navitem_2nd_menu">
                                                        <li id="navitem_solutions_connectors_mobile"><a class="nav_2nd_menu_link" href="<?php echo WEB_ROOT_URL?>/connectors.aspx"><?php _e('Overview', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_integration_request" href="<?php echo WEB_ROOT_URL?>/connectors-request.aspx?from=integrationmenu"><?php _e('Self-hosted. Get started', 'tmblog'); ?> <span class="accented"><?php _e('NOW', 'tmblog'); ?></span></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_connector_nextcloud" href="<?php echo WEB_ROOT_URL?>/connectors-nextcloud.aspx"><?php _e('For Nextcloud', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_connector_owncloud" href="<?php echo WEB_ROOT_URL?>/connectors-owncloud.aspx"><?php _e('For ownCloud', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_connector_alfresco" href="<?php echo WEB_ROOT_URL?>/connectors-alfresco.aspx"><?php _e('For Alfresco', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_connector_confluence" href="<?php echo WEB_ROOT_URL?>/connectors-confluence.aspx"><?php _e('For Confluence', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_connector_sharepoint" href="<?php echo WEB_ROOT_URL?>/connectors-sharepoint.aspx"><?php _e('For SharePoint', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_connector_thirdparty" href="<?php echo WEB_ROOT_URL?>/third-party-connectors.aspx"><?php _e('Third-party connectors', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_integration_support" href="<?php echo WEB_ROOT_URL?>/support.aspx?from=integration"><?php _e('Professional support', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_integration_compare" href="<?php echo WEB_ROOT_URL?>/compare-editions.aspx"><?php _e('Community Edition vs Integration Edition', 'tmblog'); ?></a></li>
                                                    </ul>
                                                </div>
                                                
                                            </li>
                                            <li>
                                                <a id="navitem_solutions_integration" class="dropdown-item mobile_no_link" href="<?php echo WEB_ROOT_URL?>/developer-edition.aspx"><?php _e('Developer Edition', 'tmblog'); ?></a>
                                                <div>
                                                    <div class="navitem_description"><?php _e('Online document editors to integrate with a service you are building', 'tmblog'); ?></div>
                                                    <ul class="navitem_2nd_menu">
                                                        <li id="navitem_solutions_integration_mobile"><a class="nav_2nd_menu_link" href="<?php echo WEB_ROOT_URL?>/developer-edition.aspx"><?php _e('Overview', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_developer_request" href="<?php echo WEB_ROOT_URL?>/developer-edition-request.aspx?from=developermenu"><?php _e('Self-hosted. Try for', 'tmblog'); ?> <span class="accented"><?php _e('FREE', 'tmblog'); ?></span></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_developer_licensing" href="<?php echo WEB_ROOT_URL?>/developer-edition.aspx#dual-license"><?php _e('Dual licensing', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" target="_blank" href="https://api.onlyoffice.com/"><?php _e('API', 'tmblog'); ?></a></li>
                                                        <li><a class="nav_2nd_menu_link" id="navitem_solutions_docbuilder" href="<?php echo WEB_ROOT_URL?>/document-builder.aspx"><?php _e('Document Builder', 'tmblog'); ?></a></li>
                                                    </ul>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="latestReleasesBox">
                                            <a href="<?php echo WEB_ROOT_URL?>/news.aspx"><?php _e('Latest Releases', 'tmblog'); ?></a>
                                        </div>
                                    </div>
                                    </li><!--
                                --><li class="pushy-submenu"><a class="menuitem <?php echo get_locale();?>" id="navitem_features"><?php _e('Apps', 'tmblog'); ?></a>
                                        <div id="navitem_features_menu">
                                            <ul class="dropdown-content akkordeon">
                                                <li id="navitem_editors_third_level_menu">
                                                    <a id="navitem_features_editors" class="dropdown-item mobile_no_link" href="<?php echo WEB_ROOT_URL?>/editors.aspx"><?php _e('Online Editors', 'tmblog'); ?></a>
                                                    <div>
                                                        <ul class="navitem_2nd_menu">
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_document_editor" href="<?php echo WEB_ROOT_URL?>/editors.aspx#documenteditor"><?php _e('Documents', 'tmblog'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_spreadsheet_editor" href="<?php echo WEB_ROOT_URL?>/editors.aspx#spreadsheeteditor"><?php _e('Spreadsheets', 'tmblog'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_presentation_editor" href="<?php echo WEB_ROOT_URL?>/editors.aspx#presentationeditor"><?php _e('Presentations', 'tmblog'); ?></a></li>
                                                            
                                                        </ul>

                                                    </div>
                                                    <span class="navitem_solutions"><a id="navitem_solutions_clients_apps" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/apps.aspx"><?php _e('Desktop Editors', 'tmblog'); ?></a></span>
                                                    <span class="navitem_solutions"><a id="navitem_solutions_clients_ios" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/office-for-ios.aspx"><?php _e('Office for iOS', 'tmblog'); ?></a></span>
                                                    <span class="navitem_solutions"><a id="navitem_solutions_clients_android" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/office-for-android.aspx"><?php _e('Office for Android', 'tmblog'); ?></a></span>
                                                    <span class="navitem_solutions"><a id="navitem_solutions_clients_mobile" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/mobile-projects.aspx"><?php _e('Mobile Projects', 'tmblog'); ?></a></span>
                                                </li>
                                                <li id="navitem_comserver_third_level_menu">
                                                    <a id="navitem_features_comserver" class="dropdown-item mobile_no_link" href="<?php echo WEB_ROOT_URL?>/features.aspx#documents"><?php _e('Collaboration Platform', 'tmblog'); ?></a>
                                                    <div>
                                                        <ul class="navitem_2nd_menu">
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_documents" href="<?php echo WEB_ROOT_URL?>/features.aspx#documents"><?php _e('Document Management', 'tmblog'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_mail" href="<?php echo WEB_ROOT_URL?>/features.aspx#mail"><?php _e('Mail', 'tmblog'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_crm" href="<?php echo WEB_ROOT_URL?>/features.aspx#crm"><?php _e('CRM', 'tmblog'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_projects" href="<?php echo WEB_ROOT_URL?>/features.aspx#projects"><?php _e('Projects', 'tmblog'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_calendar" href="<?php echo WEB_ROOT_URL?>/features.aspx#calendar"><?php _e('Calendar', 'tmblog'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_network" href="<?php echo WEB_ROOT_URL?>/features.aspx#network"><?php _e('Community', 'tmblog'); ?></a></li>
                                                        </ul>
                                                    </div>
                                                </li>
                                                <li id="navitem_compare_third_level_menu">
                                                    <a id="navitem_solutions_compare" class="dropdown-item mobile_no_link" href="<?php echo WEB_ROOT_URL?>/document-editor-comparison.aspx"><?php _e ('ONLYOFFICE alternatives', 'tmblog'); ?></a>
                                                    <div>
                                                        <ul class="navitem_2nd_menu">
                                                            <li id="navitem_solutions_apps_comparison_overview"><a class="nav_2nd_menu_link" href="<?php echo WEB_ROOT_URL?>/document-editor-comparison.aspx"><?php _e ('Overview', 'tmblog'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_compare_msoffice" href="<?php echo WEB_ROOT_URL?>/best-microsoft-office-alternative.aspx"><?php _e ('ONLYOFFICE vs MS Office Online', 'tmblog'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_compare_google" href="<?php echo WEB_ROOT_URL?>/best-google-docs-alternative.aspx"><?php _e ('ONLYOFFICE vs Google Docs', 'tmblog'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_compare_zoho" href="<?php echo WEB_ROOT_URL?>/best-zoho-docs-alternative.aspx"><?php _e ('ONLYOFFICE vs Zoho Docs', 'tmblog'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_compare_collabora" href="<?php echo WEB_ROOT_URL?>/best-collabora-alternative.aspx"><?php _e ('ONLYOFFICE vs Collabora Online', 'tmblog'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_compare_libreoffice" href="<?php echo WEB_ROOT_URL?>/best-libreoffice-alternative.aspx"><?php _e ('ONLYOFFICE vs LibreOffice', 'tmblog'); ?></a></li>
                                                            <li><a class="nav_2nd_menu_link" id="navitem_features_compare_office365_gsuite" href="<?php echo WEB_ROOT_URL?>/compare-solutions.aspx"><?php _e ('ONLYOFFICE vs Office 365 vs G Suite', 'tmblog'); ?></a></li>
                                                        </ul>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </li><!--
                                --><li class="pushy-submenu"><a class="menuitem <?php echo get_locale();?>" id="navitem_prices"><?php _e('Pricing', 'tmblog'); ?></a>
                                        <div id="navitem_prices_menu">
                                            <ul class="dropdown-content">
                                                <li class="pushy-link"><a id="navitem_prices_server_enterprice" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/enterprise-edition.aspx"><?php _e('Enterprise Edition', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_prices_connectors" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/integration-edition-prices.aspx"><?php _e('Integration Edition', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_prices_integration" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/developer-edition-prices.aspx"><?php _e('Developer Edition', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_prices_saas" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/saas.aspx"><?php _e('Cloud Service', 'tmblog'); ?></a></li>
                                            </ul>
                                        </div>
                                    </li><!--
                                --><li class="pushy-submenu"><a class="menuitem <?php echo get_locale();?>" id="navitem_download"><?php _e('Download', 'tmblog'); ?></a>
                                        <div class="navitem_download_menu" id="navitem_download_menu">
                                            <ul class="dropdown-content">
                                                <li class="pushy-link" id="navitem_downloadserver_third_level_menu"><a id="navitem_download_enterprise" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/enterprise-edition-free.aspx?from=downloadenterprisemenu"><?php _e('Enterprise Edition', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_download_connectors" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/connectors-request.aspx?from=downloadintegrationmenu"><?php _e('Integration Edition', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_download_integration" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/developer-edition-request.aspx?from=downloaddevelopermenu"><?php _e('Developer Edition', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_download_desktop" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/download-desktop.aspx"><?php _e('Desktop Editors', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_download_doc_builder" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/download-document-builder.aspx"><?php _e('Document Builder', 'tmblog'); ?></a></li>
                                            </ul>
                                        </div>
                                    </li><!--
                                --><li class="pushy-submenu"><a class="menuitem <?php echo get_locale();?>" id="navitem_partners"><?php _e('Partnership', 'tmblog'); ?></a>
                                    <div class="navitem_partnership_menu" id="navitem_partnership_menu">
                                        <ul class="dropdown-content">
                                            <li class="pushy-link"><a id="navitem_resellers" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/resellers.aspx"><?php _e('For Resellers', 'tmblog'); ?></a></li>
                                            <li class="pushy-link"><a id="navitem_hosters" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/affiliates.aspx"><?php _e('For Affiliates', 'tmblog'); ?></a></li>
                                            <li class="pushy-link"><a id="navitem_developers" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/partnership-developers.aspx"><?php _e('For Developers', 'tmblog'); ?></a></li>
                                            <li class="pushy-link"><a id="navitem_find_partners" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/find-partners.aspx"><?php _e('Find partners', 'tmblog'); ?></a></li>
                                            <li class="pushy-link"><a id="navitem_sub_request" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/partnership-request.aspx"><?php _e('Submit request', 'tmblog'); ?></a></li>
                                        </ul>
                                    </div>
                                    </li><!--
                                --><li class="pushy-submenu about_menu_item"><a class="menuitem" id="navitem_about"><?php _e('About', 'tmblog'); ?></a>
                                        <div id="navitem_about_menu">
                                            <ul class="dropdown-content">
                                                <li class="pushy-link"><a id="navitem_about_about" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/about.aspx"><?php _e('About ONLYOFFICE', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_blog" class="dropdown-item" target="_blank" href="<?php echo WEB_ROOT_URL?>/blog/"><?php _e('Blog', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_contribute" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/contribute.aspx"><?php _e('Contribute', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_stories" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/customers.aspx"><?php _e('Success stories', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_casestudies" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/case-studies.aspx"><?php _e('Case studies', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_customers" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/customers.aspx"><?php _e('Customers', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_awards" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/awards.aspx"><?php _e('Awards', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_events" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/events.aspx"><?php _e('Events', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_webinars" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/webinars.aspx"><?php _e('Webinars', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_press" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/press-downloads.aspx"><?php _e('Press downloads', 'tmblog'); ?></a></li>
                                                <li class="pushy-link"><a id="navitem_about_contacts" class="dropdown-item" href="<?php echo WEB_ROOT_URL?>/contacts.aspx"><?php _e('Contacts', 'tmblog'); ?></a></li>
                                            </ul>
                                        </div>
                                    </li>
                                <li class="phone_wrapper">
                                    <a class="call_phone default" title="+1 (972) 301-8440" href="tel:+19723018440">+1 (972) 301-8440</a>
                                    <a class="singin menuitem" href="<?php echo WEB_ROOT_URL?>/enterprise-edition-free.aspx?from=header"><?php _e('Get onlyoffice', 'tmblog'); ?></a></li>
                                </ul>
                            </div>
                            
                        </nav>
                    </div>
                </header>
                <article>