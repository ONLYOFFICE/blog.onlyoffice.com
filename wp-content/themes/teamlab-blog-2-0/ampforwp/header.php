<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $current_language;
global $redux_builder_amp ?>
<?php amp_header_core() ?>
<?php
do_action( 'levelup_head');
if( !ampforwp_levelup_compatibility('hf_builder_head') ){
    $header_type = ampforwp_get_setting('header-type');
    if(!defined('AMPFORWP_LAYOUTS_FILE')){
        if( !in_array($header_type,array(1,2,3,10)) ) {
            $header_type = 1;
        }
    }
?>
<?php if($header_type == '1'){?>
<?php do_action('ampforwp_admin_menu_bar_front'); 
      do_action('ampforwp_reading_progress_bar'); ?>
<header class="header h_m h_m_1">
    <?php do_action('ampforwp_header_top_design4'); ?>
    <input type="checkbox" id="offcanvas-menu" on="change:AMP.setState({ offcanvas_menu: (event.checked ? true : false) })"  [checked] = "offcanvas_menu"  class="tg" />
    <div class="hamb-mnu">
        <aside class="m-ctr">
            <div class="m-scrl">
                <div class="menu-heading clearfix">
                    <label for="offcanvas-menu" class="c-btn"></label>
                </div><!--end menu-heading-->

                <?php if ( amp_menu(false) ) : ?>
                    <nav class="m-menu">
                       <?php amp_menu();?>
                    </nav><!--end slide-menu -->
                <?php endif; ?>
                <nav class="m-menu">
                    <ul class="amp-menu">
                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                            <a class="dropdown-toggle bold uppercase" data-toggle="dropdown"><?php _e('Solutions', 'teamlab-blog-2-0'); ?></a>
                            <input type="checkbox" class="toggle" id="drop-15">
                            <label for="drop-15" class="toggle"></label>
                            <ul class="submenu">
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold"><?php _e('By size', 'teamlab-blog-2-0'); ?></a>
                                    <input type="checkbox" class="toggle" id="drop-16">
                                    <label for="drop-16" class="toggle"></label>
                                    <ul class="submenu">
                                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                            <a href="<?php echo $current_language ?>/for-small-business.aspx"><?php _e('SMBs', 'teamlab-blog-2-0'); ?></a>
                                        </li>
                                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                            <a href="<?php echo $current_language ?>/for-enterprises.aspx"><?php _e('Enterprises', 'teamlab-blog-2-0'); ?></a>
                                        </li>
                                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                            <a href="<?php echo $current_language ?>/home-use.aspx"><?php _e('Home use', 'teamlab-blog-2-0'); ?></a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold"><?php _e('By industry', 'teamlab-blog-2-0'); ?></a>
                                    <input type="checkbox" class="toggle" id="drop-17">
                                    <label for="drop-17" class="toggle"></label>
                                    <ul class="submenu">
                                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                            <a href="<?php echo $current_language ?>/for-developers.aspx"><?php _e('Developers', 'teamlab-blog-2-0'); ?></a>
                                        </li>
                                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                            <a href="<?php echo $current_language ?>/for-hosting-providers.aspx"><?php _e('Hosting providers', 'teamlab-blog-2-0'); ?></a>
                                        </li>
                                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                            <a href="<?php echo $current_language ?>/for-government.aspx"><?php _e('Government', 'teamlab-blog-2-0'); ?></a>
                                        </li>
                                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                            <a href="<?php echo $current_language ?>/healthcare.aspx"><?php _e('Healthcare', 'teamlab-blog-2-0'); ?></a>
                                        </li>
                                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                            <a href="<?php echo $current_language ?>/for-research.aspx"><?php _e('Research', 'teamlab-blog-2-0'); ?></a>
                                        </li>
                                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                            <a href="<?php echo $current_language ?>/education.aspx"><?php _e('Education', 'teamlab-blog-2-0'); ?></a>
                                        </li>
                                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                            <a href="<?php echo $current_language ?>/nonprofit-organizations.aspx"><?php _e('Nonprofits', 'teamlab-blog-2-0'); ?></a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/customers.aspx"><?php _e('Customer stories', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                
                            </ul>
                        </li>
                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                            <a class="dropdown-toggle bold uppercase" data-toggle="dropdown"><?php _e('Products & Features', 'teamlab-blog-2-0'); ?></a>
                            <input type="checkbox" class="toggle" id="drop-18">
                            <label for="drop-18" class="toggle"></label>
                            <ul class="submenu">
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/office-suite.aspx"><?php _e('ONLYOFFICE Docs', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="submenu menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="margin-left" href="<?php echo $current_language ?>/document-editor.aspx"><?php _e('Document Editor', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="margin-left" href="<?php echo $current_language ?>/spreadsheet-editor.aspx"><?php _e('Spreadsheet Editor', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="margin-left" href="<?php echo $current_language ?>/presentation-editor.aspx"><?php _e('Presentation Editor', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu bold">
                                    <a href="<?php echo $current_language ?>/all-connectors.aspx"><?php _e('ONLYOFFICE Connectors', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu bold">
                                    <a href="<?php echo $current_language ?>/desktop.aspx"><?php _e('ONLYOFFICE for desktop', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu bold">
                                    <a href="<?php echo $current_language ?>/office-for-ios.aspx"><?php _e('ONLYOFFICE for iOS', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu bold">
                                    <a href="<?php echo $current_language ?>/office-for-android.aspx"><?php _e('ONLYOFFICE for Android', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu bold">
                                    <a class="bold" href="<?php echo $current_language ?>/collaboration-platform.aspx"><?php _e('ONLYOFFICE Groups', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="margin-left" href="<?php echo $current_language ?>/document-management.aspx"><?php _e('Documents', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="margin-left" href="<?php echo $current_language ?>/mail.aspx"><?php _e('Mail', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="margin-left" href="<?php echo $current_language ?>/crm.aspx"><?php _e('CRM', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="margin-left" href="<?php echo $current_language ?>/projects.aspx"><?php _e('Projects', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="margin-left" href="<?php echo $current_language ?>/calendar.aspx"><?php _e('Calendar', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="margin-left" href="<?php echo $current_language ?>/community.aspx"><?php _e('Community', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu bold">
                                    <a class="bold" href="<?php echo $current_language ?>/workspace.aspx"><?php _e('ONLYOFFICE Workspace', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="margin-left" href="<?php echo $current_language ?>/cloud-office.aspx"><?php _e('Cloud Service', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu bold">
                                    <a class="bold" href="<?php echo $current_language ?>/security.aspx"><?php _e('Security', 'teamlab-blog-2-0'); ?></a>
                                </li>
                            </ul>
                        </li>
                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                            <a class="dropdown-toggle bold uppercase" data-toggle="dropdown"><?php _e('Pricing', 'teamlab-blog-2-0'); ?></a>
                            <input type="checkbox" class="toggle" id="drop-19">
                            <label for="drop-19" class="toggle"></label>
                            <ul class="submenu">
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold"><?php _e('ONLYOFFICE Docs', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="margin-left" href="<?php echo $current_language ?>/docs-enterprise-prices.aspx"><?php _e('Enterprise Edition', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="margin-left" href="<?php echo $current_language ?>/developer-edition-prices.aspx"><?php _e('Developer Edition', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold"><?php _e('ONLYOFFICE Workspace', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="margin-left" href="<?php echo $current_language ?>/saas.aspx"><?php _e('Cloud Service', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="margin-left" href="<?php echo $current_language ?>/workspace-enterprise-prices.aspx"><?php _e('Server Enterprise', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/find-partners.aspx"><?php _e('Buy from an ONLYOFFICE reseller', 'teamlab-blog-2-0'); ?></a>
                                </li>
                            </ul>
                        </li>
                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                            <a class="dropdown-toggle bold uppercase" data-toggle="dropdown"><?php _e('Get ONLYOFFICE', 'teamlab-blog-2-0'); ?></a>
                            <input type="checkbox" class="toggle" id="drop-20">
                            <label for="drop-20" class="toggle"></label>
                            <ul class="submenu">
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/signin.aspx"><?php _e('Sign in', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/registration.aspx"><?php _e('Sign up for ONLYOFFICE Cloud', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/download.aspx?from=downloadintegrationmenu"><?php _e('Open source packages', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/download-commercial.aspx"><?php _e('Commercial packages', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/download-desktop.aspx"><?php _e('Desktop and mobile apps', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/compare-editions.aspx"><?php _e('Compare editions', 'teamlab-blog-2-0'); ?></a>
                                </li>
                            </ul>
                        </li>
                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                            <a class="dropdown-toggle bold uppercase" data-toggle="dropdown"><?php _e('Partners', 'teamlab-blog-2-0'); ?></a>
                            <input type="checkbox" class="toggle" id="drop-21">
                            <label for="drop-21" class="toggle"></label>
                            <ul class="submenu">
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/affiliates.aspx"><?php _e('Affiliates', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/resellers.aspx"><?php _e('Resellers', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/find-partners.aspx"><?php _e('Find partners', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/partnership-request.aspx"><?php _e('Submit request', 'teamlab-blog-2-0'); ?></a>
                                </li>
                            </ul>
                        </li>
                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                            <a class="dropdown-toggle bold uppercase" data-toggle="dropdown"><?php _e('About', 'teamlab-blog-2-0'); ?></a>
                            <input type="checkbox" class="toggle" id="drop-22">
                            <label for="drop-22" class="toggle"></label>
                            <ul class="submenu">
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/about.aspx"><?php _e('About ONLYOFFICE', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo icl_get_home_url() ?>"><?php _e('Blog', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/contribute.aspx"><?php _e('Contribute', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/customers.aspx"><?php _e('Customers', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/awards.aspx"><?php _e('Awards', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/events.aspx"><?php _e('Events', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/press-downloads.aspx"><?php _e('Press downloads', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/whitepapers.aspx"><?php _e('White papers', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" target="_blank" href="https://shop.spreadshirt.com/onlyoffice"><?php _e('Gift shop', 'teamlab-blog-2-0'); ?></a>
                                </li>
                                <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children link-menu">
                                    <a class="bold" href="<?php echo $current_language ?>/contacts.aspx"><?php _e('Contacts', 'teamlab-blog-2-0'); ?></a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
                <?php do_action('ampforwp_after_amp_menu');?>

                <?php if ($redux_builder_amp['menu-search'] ) { ?>
                    <div class="m-srch">
                        <?php amp_search();?>
                    </div>
                <?php } ?>
                <?php if ( true == $redux_builder_amp['menu-social'] ) { ?>
                <div class="m-s-i">
                    <ul>
                        <?php if($redux_builder_amp['enbl-fb']){?>
                        <li>
                            <a title="facebook" class="s_fb" target="_blank" <?php ampforwp_rel_attributes_social_links(); ?> href="<?php echo esc_url($redux_builder_amp['enbl-fb-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-tw']){?>
                        <li>
                            <a title="twitter" class="s_tw" target="_blank" <?php ampforwp_rel_attributes_social_links(); ?> href="<?php echo esc_url($redux_builder_amp['enbl-tw-prfl-url']); ?>">
                            </a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-gol']){?>
                        <li>
                            <a title="google plus" class="s_gp" target="_blank" <?php ampforwp_rel_attributes_social_links(); ?> href="<?php echo esc_url($redux_builder_amp['enbl-gol-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-lk']){?>
                        <li>
                            <a title="linkedin" class="s_lk" target="_blank" <?php ampforwp_rel_attributes_social_links(); ?> href="<?php echo esc_url($redux_builder_amp['enbl-lk-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-pt']){?>
                        <li>
                            <a title="pinterest" class="s_pt" target="_blank" <?php ampforwp_rel_attributes_social_links(); ?> href="<?php echo esc_url($redux_builder_amp['enbl-pt-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-yt']){?>
                        <li>
                            <a title="youtube" class="s_yt" target="_blank" <?php ampforwp_rel_attributes_social_links(); ?> href="<?php echo esc_url($redux_builder_amp['enbl-yt-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-inst']){?>
                        <li>
                            <a title="instagram" class="s_inst" target="_blank" <?php ampforwp_rel_attributes_social_links(); ?> href="<?php echo esc_url($redux_builder_amp['enbl-inst-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-vk']){?>
                        <li>
                            <a title="vkontakte" class="s_vk" target="_blank" <?php ampforwp_rel_attributes_social_links(); ?> href="<?php echo esc_url($redux_builder_amp['enbl-vk-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-rd']){?>
                        <li>
                            <a title="reddit" class="s_rd" target="_blank" <?php ampforwp_rel_attributes_social_links(); ?> href="<?php echo esc_url($redux_builder_amp['enbl-rd-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-tbl']){?>
                        <li>
                            <a title="tumblr" class="s_tbl" target="_blank" <?php ampforwp_rel_attributes_social_links(); ?> href="<?php echo esc_url($redux_builder_amp['enbl-tbl-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                         <?php if(ampforwp_get_setting('enbl-telegram')){?>
                        <li>
                            <a title="telegram" class="s_telegram" target="_blank" <?php ampforwp_rel_attributes_social_links(); ?> href="<?php echo esc_url(ampforwp_get_setting('enbl-telegram-prfl-url')); ?>"></a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>
                <?php if( true == $redux_builder_amp['amp-swift-menu-cprt']){?>
                <div class="cp-rgt">
                    <?php amp_non_amp_link(); ?>
                </div>
                <?php } ?>
            </div><!-- /.m-srl -->
        </aside><!--end menu-container-->
        <label for="offcanvas-menu" class="fsc"></label>
        <div class="cntr">
            <div class="head h_m_w">
                <?php  if(ampforwp_get_setting('ampforwp-amp-menu-swift') == true) {?>
                <div class="h-nav">
                    <label for="offcanvas-menu" class="t-btn"></label>
                </div><!--end menu-->
                <?php } ?>
                <div class="logo">
                    <?php amp_logo(); ?>
                </div><!-- /.logo -->
                <div class="h-1">
                    <?php if( true == $redux_builder_amp['amp-swift-search-feature'] ){ ?>
                        <div class="h-srch h-ic">
                            <a title="search" class="lb icon-src" href="#search"></a>
                            <div class="lb-btn"> 
                                <div class="lb-t" id="search">
                                   <?php amp_search();?>
                                   <a title="close" class="lb-x" href="#"></a>
                                </div> 
                            </div>
                        </div><!-- /.search -->
                    <?php } ?>
                    <?php if( isset( $redux_builder_amp['amp-swift-cart-btn'] ) && true == $redux_builder_amp['amp-swift-cart-btn'] ) { ?>
                        <div class="h-shop h-ic">
                            <a href="<?php echo esc_url(ampforwp_wc_cart_page_url()); ?>" class="isc"></a>
                        </div>
                    <?php } ?>
                    <?php if ( true == $redux_builder_amp['ampforwp-callnow-button'] ) { ?>
                        <div class="h-call h-ic">
                            <a title="call telephone" href="tel:<?php echo esc_attr($redux_builder_amp['enable-amp-call-numberfield']);?>"></a>
                        </div>
                    <?php } ?> 
                    <?php do_action('ampforwp_header_elements') ?>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    <?php do_action('ampforwp_header_bottom_design4'); ?>
</header>
<?php } ?>
<?php if($header_type == '2'){
    do_action('ampforwp_reading_progress_bar'); ?>
<header class="header-2 h_m h_m_1">
    <?php do_action('ampforwp_header_top_design4'); ?>
    <input type="checkbox" id="offcanvas-menu"  on="change:AMP.setState({ offcanvas_menu: (event.checked ? true : false) })"  [checked] = "offcanvas_menu"  class="tg" />
    <div class="hamb-mnu">
        <aside class="m-ctr">
            <div class="m-scrl">

                <div class="menu-heading clearfix">
                    <label for="offcanvas-menu" class="c-btn"></label>
                </div><!--end menu-heading-->
                <?php if ( amp_menu(false) ) : ?>
                    <nav class="m-menu">
                       <?php amp_menu();?>
                    </nav><!--end slide-menu -->
                <?php endif; ?>
                <?php do_action('ampforwp_after_amp_menu');?>
                <?php if( true == ampforwp_get_setting('signin-button') && '2' == ampforwp_get_setting('cta-responsive-view')){?>
                    <div class="h-sing cta-res">
                        <a target="_blank" <?php ampforwp_nofollow_cta_header_link(); ?> href="<?php echo esc_url(ampforwp_get_setting('signin-button-link'))?>"><?php echo esc_html__(ampforwp_get_setting('signin-button-text'), 'accelerated-mobile-pages'); ?></a>
                    </div>
                    <?php } ?>
                <?php if ( $redux_builder_amp['menu-search'] ) { ?>
                <div class="m-srch">
                    <?php amp_search();?>
                </div>
                <?php } ?>
                <?php if ( true == $redux_builder_amp['menu-social'] ) { ?>
                <div class="m-s-i">
                    <ul>
                        <?php if($redux_builder_amp['enbl-fb']){?>
                        <li>
                            <a title="facebook" class="s_fb" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-fb-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-tw']){?>
                        <li>
                            <a title="twitter" class="s_tw" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-tw-prfl-url']); ?>">
                            </a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-gol']){?>
                        <li>
                            <a title="google plus" class="s_gp" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-gol-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-lk']){?>
                        <li>
                            <a title="linkedin" class="s_lk" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-lk-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-pt']){?>
                        <li>
                            <a title="pinterest" class="s_pt" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-pt-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-yt']){?>
                        <li>
                            <a title="youtube" class="s_yt" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-yt-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-inst']){?>
                        <li>
                            <a title="instagram" class="s_inst" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-inst-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-vk']){?>
                        <li>
                            <a title="vkontakte" class="s_vk" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-vk-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-rd']){?>
                        <li>
                            <a title="reddit" class="s_rd" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-rd-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-tbl']){?>
                        <li>
                            <a title="tumblr" class="s_tbl" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-tbl-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                          <?php if(ampforwp_get_setting('enbl-telegram')){?>
                        <li>
                            <a title="telegram" class="s_telegram" target="_blank" <?php ampforwp_rel_attributes_social_links(); ?> href="<?php echo esc_url(ampforwp_get_setting('enbl-telegram-prfl-url')); ?>"></a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>
                <?php if( true == $redux_builder_amp['amp-swift-menu-cprt']){?>
                <div class="cp-rgt">
                    <?php amp_non_amp_link(); ?>
                </div>
                <?php } ?>
            </div><!-- /.m-srl -->
        </aside><!--end menu-container-->
        <label for="offcanvas-menu" class="fsc"></label>
        <div class="cntr">
            <div class="head-2 h_m_w">
                <?php  if(ampforwp_get_setting('ampforwp-amp-menu-swift') == true) {?>
                <div class="h-nav">
                   <label for="offcanvas-menu" class="t-btn"></label>
                </div><!-- /.left-nav -->
                <?php } ?>
                <div class="h-logo">
                    <?php amp_logo(); ?>
                </div>
                <div class="h-2">
                   <?php if( ampforwp_get_setting('signin-button-text') && ampforwp_get_setting('signin-button-link') ){?>
                    <div class="h-sing">
                        <a target="_blank" <?php ampforwp_nofollow_cta_header_link(); ?> href="<?php echo esc_url(ampforwp_get_setting('signin-button-link'))?>"><?php echo esc_html__(ampforwp_get_setting('signin-button-text'), 'accelerated-mobile-pages'); ?></a>
                    </div>
                    <?php } ?>
                    <?php if( isset( $redux_builder_amp['amp-swift-cart-btn'] ) && true == $redux_builder_amp['amp-swift-cart-btn'] ) { ?>
                        <div class="h-shop h-ic">
                            <a href="<?php echo ampforwp_wc_cart_page_url(); ?>" class="isc"></a>
                        </div>
                    <?php } ?>
                    <?php if ( true == $redux_builder_amp['ampforwp-callnow-button'] ) { ?>
                        <div class="h-call h-ic">
                            <a title="call telephone" href="tel:<?php echo esc_attr($redux_builder_amp['enable-amp-call-numberfield']);?>"></a>
                        </div>
                    <?php } ?>    
                    <?php do_action('ampforwp_header_elements') ?>
                </div>
            </div>
        </div>
    </div>
    <?php do_action('ampforwp_header_bottom_design4'); ?>
</header>
<?php } ?>
<?php if($header_type == '3'){
    do_action('ampforwp_reading_progress_bar'); ?>
<header class="header-3 h_m h_m_1">
    <?php do_action('ampforwp_header_top_design4'); ?>
    <input type="checkbox" id="offcanvas-menu"  on="change:AMP.setState({ offcanvas_menu: (event.checked ? true : false) })"  [checked] = "offcanvas_menu"  class="tg" />
    <div class="hamb-mnu">
        <aside class="m-ctr">
            <div class="m-scrl">
                <div class="menu-heading clearfix">
                    <label for="offcanvas-menu" class="c-btn"></label>
                </div><!--end menu-heading-->
                <?php if ( amp_menu(false) ) : ?>
                    <nav class="m-menu">
                       <?php amp_menu();?>
                    </nav><!--end slide-menu -->
                <?php endif; ?>
                <?php do_action('ampforwp_after_amp_menu');?>
                <?php if ( $redux_builder_amp['menu-search'] ) { ?>
                <div class="m-srch">
                    <?php amp_search();?>
                </div>
                <?php } ?>
                <?php if ( true == $redux_builder_amp['menu-social'] ) { ?>
                <div class="m-s-i">
                    <ul>
                        <?php if($redux_builder_amp['enbl-fb']){?>
                        <li>
                            <a title="facebook" class="s_fb" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-fb-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-tw']){?>
                        <li>
                            <a title="twitter" class="s_tw" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-tw-prfl-url']); ?>">
                            </a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-gol']){?>
                        <li>
                            <a title="google plus" class="s_gp" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-gol-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-lk']){?>
                        <li>
                            <a title="linkedin" class="s_lk" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-lk-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-pt']){?>
                        <li>
                            <a title="pinterest" class="s_pt" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-pt-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-yt']){?>
                        <li>
                            <a title="youtube" class="s_yt" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-yt-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-inst']){?>
                        <li>
                            <a title="instagram" class="s_inst" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-inst-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-vk']){?>
                        <li>
                            <a title="vkontakte" class="s_vk" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-vk-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-rd']){?>
                        <li>
                            <a title="reddit" class="s_rd" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-rd-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if($redux_builder_amp['enbl-tbl']){?>
                        <li>
                            <a title="tumblr" class="s_tbl" target="_blank" href="<?php echo esc_url($redux_builder_amp['enbl-tbl-prfl-url']); ?>"></a>
                        </li>
                        <?php } ?>
                        <?php if(ampforwp_get_setting('enbl-telegram')){?>
                        <li>
                            <a title="telegram" class="s_telegram" target="_blank" <?php ampforwp_rel_attributes_social_links(); ?> href="<?php echo esc_url(ampforwp_get_setting('enbl-telegram-prfl-url')); ?>"></a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>
                <?php if( true == $redux_builder_amp['amp-swift-menu-cprt']){?>
                <div class="cp-rgt">
                    <?php amp_non_amp_link(); ?>
                </div>
                <?php } ?>
            </div><!-- /.m-srl -->
        </aside><!--end menu-container-->
        <label for="offcanvas-menu" class="fsc"></label>
        <div class="cntr">
            <div class="head-3 h_m_w">
                <div class="h-logo">
                    <?php amp_logo(); ?>
                </div>
                <div class="h-3">
                    <?php if( true == $redux_builder_amp['amp-swift-search-feature'] ){ ?>
                        <div class="h-srch h-ic">
                            <a class="lb icon-src" href="#search"></a>
                            <div class="lb-btn"> 
                                <div class="lb-t" id="search">
                                   <?php amp_search();?>
                                   <a class="lb-x" href="#"></a>
                                </div> 
                            </div>
                        </div><!-- /.search -->
                    <?php } ?>
                    <?php if( isset( $redux_builder_amp['amp-swift-cart-btn'] ) && true == $redux_builder_amp['amp-swift-cart-btn'] ) { ?>
                        <div class="h-shop h-ic">
                            <a href="<?php echo ampforwp_wc_cart_page_url(); ?>" class="isc"></a>
                        </div>
                    <?php } ?>
                    <?php if ( true == $redux_builder_amp['ampforwp-callnow-button'] ) { ?>
                        <div class="h-call h-ic">
                            <a href="tel:<?php echo esc_attr($redux_builder_amp['enable-amp-call-numberfield']);?>"></a>
                        </div>
                    <?php } ?>
                    <?php do_action('ampforwp_header_elements') ?>
                    <?php  if(ampforwp_get_setting('ampforwp-amp-menu-swift') == true) {?>
                    <div class="h-nav">
                       <label for="offcanvas-menu" class="t-btn"></label>
                    </div><!-- /.left-nav --> 
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <?php do_action('ampforwp_header_bottom_design4'); ?>
</header>
<?php }
do_action("ampforwp_advance_header_layout_options");
}
 ?>
<div class="content-wrapper">
<?php
if(!ampforwp_levelup_compatibility('hf_builder_head') ){
 if($redux_builder_amp['primary-menu']){?>
<div class="p-m-fl">
<?php if ( amp_alter_menu(false) ) : ?>
  <div class="p-menu">
    <?php amp_alter_menu(true); ?>
  </div>
  <?php endif; ?>
 <?php do_action('ampforwp_after_primary_menu');  ?>
</div>
<?php } 
}?>

