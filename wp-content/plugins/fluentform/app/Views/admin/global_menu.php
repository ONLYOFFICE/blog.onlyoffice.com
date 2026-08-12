<?php

defined('ABSPATH') or die;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables in view files

use FluentForm\App\Modules\Acl\Acl;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Admin menu display, no nonce needed
$page = sanitize_text_field($_GET['page']);
?>
<div class="ff_header">
    <div class="ff_header_group">
        <span class="plugin-name">
            <img src="<?php echo esc_url($logo); ?>"/>
        </span>
        <span class="ff_menu_toggle">
            <i class="ff-icon ff-icon-menu"></i>
        </span>
    </div>
    <ul class="ff_menu">
        <li class="<?php echo ($page == 'fluent_forms') ? 'active' : '' ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms')); ?>" class="ff_menu_link">
                <?php esc_html_e('Forms', 'fluentform'); ?>
            </a>
        </li>
        <li class="<?php echo ($page == 'fluent_forms_all_entries') ? 'active' : '' ?>">
            <?php if (Acl::hasPermission('fluentform_entries_viewer')): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_all_entries'));?>" class="ff_menu_link">
                    <?php esc_html_e('Entries', 'fluentform'); ?>
                </a>
            <?php endif; ?>
        </li>
        <li class="<?php echo ($page == 'fluent_forms_reports') ? 'active' : '' ?>">
            <?php if (Acl::hasPermission('fluentform_entries_viewer')): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_reports'));?>" class="ff_menu_link">
                    <?php esc_html_e('Reports', 'fluentform'); ?>
                </a>
            <?php endif; ?>
        </li>
        <li class="<?php echo ($page == 'fluent_forms_payment_entries') ? 'active' : '' ?>">
            <?php if ($show_payment_entries && Acl::hasPermission('fluentform_view_payments')): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_payment_entries')); ?>" class="ff_menu_link">
                    <?php esc_html_e('Payments', 'fluentform'); ?>
                </a>
            <?php endif; ?>
        </li>
        <?php if (Acl::hasPermission('fluentform_settings_manager')): ?>
            <li class="<?php echo ($page == 'fluent_forms_settings') ? 'active' : '' ?>">
                <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_settings')); ?>" class="ff_menu_link">
                    <?php esc_html_e('Global Settings', 'fluentform'); ?>
                </a>
            </li>
            <li class="<?php echo ($page == 'fluent_forms_transfer') ? 'active' : '' ?>">
                <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_transfer')); ?>" class="ff_menu_link">
                    <?php esc_html_e('Tools', 'fluentform'); ?>
                </a>
            </li>
            <li class="<?php echo ($page == 'fluent_forms_add_ons') ? 'active' : '' ?>">
                <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_add_ons')); ?>" class="ff_menu_link">
                    <?php esc_html_e('Integrations', 'fluentform'); ?>
                </a>
            </li>
        <?php endif; ?>
        <li>
            <?php if ($show_payment && Acl::hasPermission('fluentform_view_payments')): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_settings#payments/general_settings')); ?>" class="ff_menu_link">
                    <?php esc_html_e('Payments', 'fluentform'); ?><span class="ff_new_badge">new</span>
                </a>
            <?php endif; ?>
        </li>

        <li class="<?php echo ($page == 'fluent_forms_docs') ? 'active' : '' ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_docs')); ?>" class="ff_menu_link">
                <?php esc_html_e('Support', 'fluentform'); ?>
            </a>
        </li>
        <?php if(!defined('FLUENTFORMPRO')): ?>
            <li>
                <a target="_blank" rel="noopener" href="<?php echo esc_url(fluentform_upgrade_url('top_bar')); ?>" class="ff_menu_link ff_menu_link_buy">
                    <?php esc_html_e('Upgrade to Pro', 'fluentform'); ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <?php
        $globalSearchActive = apply_filters('fluentform/global_search_active', 'yes');
        $globalSearchButtonClickScript = "";
        if ($globalSearchActive == 'yes') {
            $globalSearchButtonClickScript = "jQuery('.global-search-menu-button').on('click', function() {
                    document.dispatchEvent(new CustomEvent('global-search-menu-button-click'));
                });";
            $user_agent = getenv("HTTP_USER_AGENT");
            if (!empty($user_agent) && strpos($user_agent, "Win") !== FALSE) {
                $key = "Ctrl ";
            } else {
                $key = "⌘";
            }
        }
    ?>
    <?php if($globalSearchActive == 'yes'):?>
        <button class="global-search-menu-button">
            <span class="el-icon-search"></span> <span class="shortcut"><?php echo esc_html($key)?>K </span>
        </button>
    <?php endif; ?>
    <?php

    do_action_deprecated(
        'fluentform_after_global_menu',
        [
        ],
        FLUENTFORM_FRAMEWORK_UPGRADE,
        'fluentform/after_global_menu',
        'Use fluentform/after_global_menu instead of fluentform_after_global_menu.'
    );
    do_action('fluentform/after_global_menu');
    
    wp_add_inline_script('fluent_forms_global', "
        //for mobile nav
        let globalHeaderMenuElem = jQuery('.ff_menu');
        jQuery('.ff_menu_toggle').on('click', function() {
            globalHeaderMenuElem.toggleClass('ff_menu_active');
        });

        $globalSearchButtonClickScript

        // for setting sidebar
        let globalSettingSidebarElem = jQuery('#ff_global_settings_sidebar');
        let globalOverlayElem = jQuery('#global-overlay');
        let globalSettingSidebarStateKey = 'fluentform_global_settings_sidebar_collapsed';
        let globalSettingSidebarToggle = globalSettingSidebarElem.find('.ff_sidebar_toggle');
        let isGlobalSettingDesktop = function() {
            return window.matchMedia('(min-width: 769px)').matches;
        };
        let syncGlobalSettingSidebarMode = function() {
            if (isGlobalSettingDesktop() && window.localStorage.getItem(globalSettingSidebarStateKey) === 'yes') {
                globalSettingSidebarElem.addClass('ff_settings_sidebar_collapsed');
            } else if (!isGlobalSettingDesktop()) {
                globalSettingSidebarElem.removeClass('ff_settings_sidebar_collapsed');
            }
        };
        let updateGlobalSettingToggleState = function() {
            let collapseLabel = globalSettingSidebarToggle.data('collapse-label') || 'Collapse settings menu';
            let expandLabel = globalSettingSidebarToggle.data('expand-label') || 'Expand settings menu';
            let isExpanded = isGlobalSettingDesktop()
                ? !globalSettingSidebarElem.hasClass('ff_settings_sidebar_collapsed')
                : globalSettingSidebarElem.hasClass('active');

            globalSettingSidebarToggle
                .attr('aria-expanded', isExpanded ? 'true' : 'false')
                .attr('aria-label', isExpanded ? collapseLabel : expandLabel)
                .attr('title', isExpanded ? collapseLabel : expandLabel);
        };

        syncGlobalSettingSidebarMode();

        globalSettingSidebarToggle.on('click', function() {
            if (isGlobalSettingDesktop()) {
                globalSettingSidebarElem.toggleClass('ff_settings_sidebar_collapsed');
                window.localStorage.setItem(
                    globalSettingSidebarStateKey,
                    globalSettingSidebarElem.hasClass('ff_settings_sidebar_collapsed') ? 'yes' : 'no'
                );
                updateGlobalSettingToggleState();
                return;
            }

            jQuery(globalSettingSidebarElem).add(globalOverlayElem).toggleClass('active');
            updateGlobalSettingToggleState();
        });

        jQuery(globalOverlayElem).on('click', function() {
            jQuery(globalOverlayElem).add(globalSettingSidebarElem).removeClass('active');
            updateGlobalSettingToggleState();
        });

        jQuery(window).on('resize', function() {
            syncGlobalSettingSidebarMode();
            updateGlobalSettingToggleState();
        });
        updateGlobalSettingToggleState();

    ");
    ?>
</div>
<?php
do_action('fluentform/after_global_menu_render',$page);
?>
