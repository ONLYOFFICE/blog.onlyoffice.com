<?php

defined('ABSPATH') or die;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables in view files

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;

?>
<?php
    do_action_deprecated(
        'fluentform_global_menu',
        [
        ],
        FLUENTFORM_FRAMEWORK_UPGRADE,
        'fluentform/global_menu',
        'Use fluentform/global_menu instead of fluentform_global_menu.'
    );
    do_action('fluentform/global_menu');
?>

<div class="ff_form_wrap ff_global_setting_wrap">
    <div class="global-overlay" id="global-overlay"></div>
    <div class="ff_form_wrap_area">
        <div class="ff_settings_wrapper ff_layout_section">
            <?php
                do_action_deprecated(
                    'fluentform_before_global_settings_wrapper',
                    [
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/before_global_settings_wrapper',
                    'Use fluentform/before_global_settings_wrapper instead of fluentform_before_global_settings_wrapper.'
                );
                do_action('fluentform/before_global_settings_wrapper');
            ?>
            <div class="ff_settings_sidebar_wrap" id="ff_global_settings_sidebar">
                <div class="ff_settings_sidebar ff_layout_section_sidebar">
                    <ul class="ff_settings_list ff_list_button">
                        <li class="ff_list_button_item has_sub_menu ">
                            <a
                                class="ff_list_button_link"
                                title="<?php echo esc_attr(__('General', 'fluentform')); ?>"
                                aria-label="<?php echo esc_attr(__('General', 'fluentform')); ?>"
                                href="#">
                                <i class="ff_settings_menu_icon ff-icon ff-icon-setting" aria-hidden="true"></i>
                                <span class="ff_settings_menu_label"><?php esc_html_e('General','fluentform'); ?></span>
                            </a>
                            <ul class="ff_list_submenu" >
                                <li >
                                    <a class="ff-page-scroll"
                                        data-section-id="#settings"
                                        data-hash="settings"
                                        href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings', [
                                        'hash' => 'settings'
                                        ])); ?>">
                                        <?php esc_html_e('Layout', 'fluentform'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="ff-page-scroll"
                                       data-section-id="#email-summaries"
                                       data-hash="settings"
                                       href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings', [
                                           'hash' => 'setting'
                                       ])); ?>">
                                        <?php esc_html_e('Email Summaries', 'fluentform'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="ff-page-scroll"
                                       data-section-id="#integration-failure-notification"
                                       data-hash="settings"
                                       href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings', [
                                           'hash' => 'setting'
                                       ])); ?>">
                                        <?php esc_html_e('Integration Failure', 'fluentform'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="ff-page-scroll"
                                       data-section-id="#default-messages"
                                       data-hash="settings"
                                       href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings', [
                                           'hash' => 'settings'
                                       ])); ?>">
                                        <?php esc_html_e('Validation Messages', 'fluentform'); ?>
                                    </a>
                                </li>
                                <li>
                                <a class="ff-page-scroll"
                                   data-section-id="#default-style-template"
                                   data-hash="settings"
                                   href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings', [
                                     'hash' => 'settings'
                                   ])); ?>">
                                  <?php esc_html_e('Default Style Template', 'fluentform'); ?>
                                </a>
                              </li>
                                <li>
                                    <a class="ff-page-scroll"
                                       data-section-id="#miscellaneous"
                                       data-hash="settings"
                                       href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings', [
                                           'hash' => 'setting'
                                       ])); ?>">
                                        <?php esc_html_e('Miscellaneous', 'fluentform'); ?>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <?php if (ArrayHelper::exists($components, 'payment_settings')) : ?>
                            <?php if (Helper::isPaymentCompatible()) : ?>
                                <li class="ff_list_button_item has_sub_menu">
                                    <a class="ff_list_button_link"
                                        title="<?php echo esc_attr(ArrayHelper::get($components, 'payment_settings.title', '')); ?>"
                                        aria-label="<?php echo esc_attr(ArrayHelper::get($components, 'payment_settings.title', '')); ?>"
                                        href="#">
                                        <i class="ff_settings_menu_icon ff-icon ff-icon-payment" aria-hidden="true"></i>
                                        <span class="ff_settings_menu_label"><?php echo esc_html(ArrayHelper::get($components, 'payment_settings.title', '')); ?></span>
                                    </a>
                                    <?php if (ArrayHelper::get($components, 'payment_settings.sub_menu')) : ?>
                                        <ul class="ff_list_submenu">
                                            <?php $subMenus = ArrayHelper::get($components, 'payment_settings.sub_menu');
                                            foreach ($subMenus as $subMenu): ?>
                                                <li class="<?php echo esc_attr(ArrayHelper::get($subMenu, 'class','')); ?> ff_list_button_item">
                                                    <a
                                                            data-settings_key="payment_component"
                                                            data-component="payment_component"
                                                            data-hash="<?php echo esc_attr(ArrayHelper::get($subMenu, 'hash')) ?>"
                                                            href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings', [
                                                                'hash' => esc_attr(ArrayHelper::get($subMenu, 'hash')),
                                                            ])); ?>"
                                                    >
                                                        <?php echo esc_attr(ArrayHelper::get($subMenu, 'title', '')); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif ?>
                                </li>
                            <?php else : ?>
                                <li class="<?php echo esc_attr(Helper::getHtmlElementClass('payment_settings', $currentComponent)); ?> ff_list_button_item has_sub_menu">
                                    <a
                                            class="ff_list_button_link ff-payment-settings-root"
                                            data-hash="payment_settings"
                                            title="<?php echo esc_attr(__('Payment', 'fluentform')); ?>"
                                            aria-label="<?php echo esc_attr(__('Payment', 'fluentform')); ?>"
                                            href="#">
                                        <i class="ff_settings_menu_icon ff-icon ff-icon-payment" aria-hidden="true"></i>
                                        <span class="ff_settings_menu_label"><?php esc_html_e('Payment', 'fluentform'); ?></span>
                                    </a>
                                    <?php if (ArrayHelper::get($components, 'payment_settings.sub_menu')) : ?>
                                        <ul class="ff_list_submenu">
                                            <?php
                                            $subMenus = ArrayHelper::get($components, 'payment_settings.sub_menu');
                                            foreach ($subMenus as $subMenu):
                                                $baseUrl = Helper::makeMenuUrl('fluent_forms_settings', $subMenu);
                                                $baseUrl .= ArrayHelper::get($subMenu, 'path');
                                                ?>
                                                <li>
                                                    <a data-settings_key="<?php echo esc_attr(ArrayHelper::get($subMenu,
                                                        'path')); ?>"
                                                       data-component="<?php echo esc_attr(ArrayHelper::get($subMenu, 'path',
                                                           '')); ?>"
                                                       data-hash="<?php echo esc_attr(ArrayHelper::get($subMenu, 'path',
                                                           '')); ?>"
                                                       href="<?php echo esc_url($baseUrl); ?>"
                                                    >
                                                        <?php echo esc_attr($subMenu['name']); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif ?>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <li class="ff_list_button_item has_sub_menu">
                            <a class="ff_list_button_link"
                                title="<?php echo esc_attr(__('Security', 'fluentform')); ?>"
                                aria-label="<?php echo esc_attr(__('Security', 'fluentform')); ?>"
                                href="#">
                                <i class="ff_settings_menu_icon ff-icon ff-icon-lock" aria-hidden="true"></i>
                                <span class="ff_settings_menu_label"><?php esc_html_e('Security', 'fluentform'); ?></span>
                            </a>
                            <ul class="ff_list_submenu">
                                <?php foreach ($components as $componentName => $component): ?>
                                    <?php if (ArrayHelper::get($component, 'hash') == 're_captcha'
                                        || ArrayHelper::get($component, 'hash') == 'h_captcha'
                                        || ArrayHelper::get($component, 'hash') == 'turnstile'
                                        || ArrayHelper::get($component, 'hash') == 'cleantalk'
                                    ) : ?>

                                        <li class="<?php echo esc_attr(Helper::getHtmlElementClass($component['hash'],
                                            $currentComponent)); ?> ff_item_<?php echo esc_attr($componentName); ?>">
                                            <a data-settings_key="<?php echo esc_attr(ArrayHelper::get($component,
                                                'settings_key')); ?>"
                                               data-component="<?php echo esc_attr(ArrayHelper::get($component, 'component',
                                                   '')); ?>"
                                               data-hash="<?php echo esc_attr(ArrayHelper::get($component, 'hash', '')); ?>"
                                               href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings',
                                                   $component)); ?>"
                                            >
                                                <?php echo esc_attr($component['title']); ?>
                                            </a>
                                        </li>
                                    <?php endif ?>
                                <?php endforeach; ?>
                            </ul>
                        </li>

                        <li class="<?php echo esc_attr(Helper::getHtmlElementClass('managers', $currentComponent)); ?> ff_list_button_item">
                            <a
                                    class="ff_list_button_link"
                                    data-hash="managers"
                                    title="<?php echo esc_attr(__('Permissions', 'fluentform')); ?>"
                                    aria-label="<?php echo esc_attr(__('Permissions', 'fluentform')); ?>"
                                    href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings', [
                                        'hash' => 'permissions'
                                    ])); ?>">
                                <i class="ff_settings_menu_icon ff-icon ff-icon-user" aria-hidden="true"></i>
                                <span class="ff_settings_menu_label"><?php esc_html_e('Permissions','fluentform'); ?></span>
                            </a>
                        </li>
                        <?php if ( ArrayHelper::get($components, 'admin_approval')) :?>

                            <li class="<?php echo esc_attr(Helper::getHtmlElementClass('admin_approval', $currentComponent)); ?> ff_list_button_item">
                                <a
                                        class="ff_list_button_link"
                                        data-hash="admin_approval"
                                        data-settings_key="ff_admin_approval"
                                        data-component="general-integration-settings"
                                        title="<?php echo esc_attr(__('Admin approval', 'fluentform')); ?>"
                                        aria-label="<?php echo esc_attr(__('Admin approval', 'fluentform')); ?>"
                                        href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings', [
                                            'hash' => 'admin_approval'
                                        ])); ?>">
                                    <i class="ff_settings_menu_icon ff-icon ff-icon-checkmark-square" aria-hidden="true"></i>
                                    <span class="ff_settings_menu_label"><?php esc_html_e('Admin approval', 'fluentform'); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="<?php echo esc_attr(Helper::getHtmlElementClass('double_optin_settings', $currentComponent)); ?> ff_list_button_item">
                            <a
                                    class="ff_list_button_link"
                                    data-hash="double_optin_settings"
                                    title="<?php echo esc_attr(__('Double Opt-in', 'fluentform')); ?>"
                                    aria-label="<?php echo esc_attr(__('Double Opt-in', 'fluentform')); ?>"
                                    href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings', [
                                        'hash' => 'double_optin_settings'
                                    ])); ?>">
                                <i class="ff_settings_menu_icon ff-icon ff-icon-email" aria-hidden="true"></i>
                                <span class="ff_settings_menu_label"><?php esc_html_e('Double Opt-in', 'fluentform'); ?></span>
                            </a>
                        </li>
                        <?php
                        // Every control on this card (the enable toggle, the
                        // adapter install) requires manage_options, so showing it
                        // to a settings manager would only offer them a switch
                        // that always errors.
                        if (current_user_can('manage_options')) { ?>
                        <li class="<?php echo esc_attr(Helper::getHtmlElementClass('mcp_settings', $currentComponent)); ?> ff_list_button_item">
                            <a
                                    class="ff_list_button_link"
                                    data-hash="mcp_settings"
                                    title="<?php echo esc_attr(__('MCP for AI Agents', 'fluentform')); ?>"
                                    aria-label="<?php echo esc_attr(__('MCP for AI Agents', 'fluentform')); ?>"
                                    href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings', [
                                        'hash' => 'mcp_settings'
                                    ])); ?>">
                                <i class="ff_settings_menu_icon ff-icon ff-icon-code" aria-hidden="true"></i>
                                <span class="ff_settings_menu_label"><?php esc_html_e('MCP for AI Agents', 'fluentform'); ?></span>
                            </a>
                        </li>
                        <?php } ?>
                        <?php
                        if (ArrayHelper::exists($components, 'InventoryManager')) { ?>
                            <li class="<?php echo esc_attr(Helper::getHtmlElementClass('inventory', $currentComponent)); ?> ff_list_button_item">
                                <a
                                        class="ff_list_button_link"
                                        data-hash="inventory_manager"
                                        title="<?php echo esc_attr(__('Inventory Manager', 'fluentform')); ?>"
                                        aria-label="<?php echo esc_attr(__('Inventory Manager', 'fluentform')); ?>"
                                        href="<?php
                                        echo esc_url(Helper::makeMenuUrl('fluent_forms_settings', [
                                            'hash' => 'inventory_manager'
                                        ])); ?>">
                                    <i class="ff_settings_menu_icon ff-icon ff-icon-layers" aria-hidden="true"></i>
                                    <span class="ff_settings_menu_label"><?php esc_html_e('Inventory Manager', 'fluentform'); ?></span>
                                </a>
                            </li>
                        <?php } ?>

                        <li class="ff_list_button_item has_sub_menu">
                            <a class="ff_list_button_link"
                                title="<?php echo esc_attr(__('Configure Integrations', 'fluentform')); ?>"
                                aria-label="<?php echo esc_attr(__('Configure Integrations', 'fluentform')); ?>"
                                href="#">
                                <i class="ff_settings_menu_icon ff-icon ff-icon-puzzle" aria-hidden="true"></i>
                                <span class="ff_settings_menu_label"><?php esc_html_e('Configure Integrations', 'fluentform'); ?></span>
                            </a>
                            <ul class="ff_list_submenu">
                                <?php foreach ($components as $componentName => $component): ?>
                                    <?php
                                        if (ArrayHelper::get($component, 'hash') != 're_captcha'
                                            && ArrayHelper::get($component, 'hash') != 'h_captcha'
                                            && ArrayHelper::get($component, 'hash') != 'turnstile'
                                            && ArrayHelper::get($component, 'hash') != 'cleantalk'
                                            && $componentName != 'payment_settings'
                                            && $componentName != 'license_page'
                                            && ArrayHelper::get($component, 'hash') != 'admin_approval'
                                            && ArrayHelper::get($component, 'hash') != 'inventory_manager'
                                        )
                                    : ?>

                                        <li class="<?php echo esc_attr(Helper::getHtmlElementClass(ArrayHelper::get($component, 'hash'),
                                            $currentComponent)); ?> ff_item_<?php echo esc_attr($componentName); ?>">
                                            <a data-settings_key="<?php echo esc_attr(ArrayHelper::get($component,
                                                'settings_key')); ?>"
                                               data-component="<?php echo esc_attr(ArrayHelper::get($component, 'component',
                                                   '')); ?>"
                                               data-hash="<?php echo esc_attr(ArrayHelper::get($component, 'hash', '')); ?>"
                                               href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings',
                                                   $component)); ?>"
                                            >
                                                <?php echo esc_attr($component['title']); ?>
                                            </a>
                                        </li>
                                    <?php endif ?>
                                <?php endforeach; ?>
                            </ul>
                        </li>

                        <?php if ($licensePage = ArrayHelper::get($components, 'license_page', '')) : ?>
                            <li class="<?php echo esc_attr(Helper::getHtmlElementClass('license_page', $currentComponent)); ?> ff_list_button_item">
                                <a
                                        class="ff_list_button_link"
                                        data-component="<?php echo esc_attr(ArrayHelper::get($licensePage, 'query.component', '')); ?>"
                                        title="<?php echo esc_attr($licensePage['title']); ?>"
                                        aria-label="<?php echo esc_attr($licensePage['title']); ?>"
                                        href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings', $licensePage)); ?>"
                                >
                                    <i class="ff_settings_menu_icon ff-icon ff-icon-handshake" aria-hidden="true"></i>
                                    <span class="ff_settings_menu_label"><?php echo esc_attr($licensePage['title']); ?></span>
                                </a>
                            </li>
                        <?php endif ?>

                        <!-- Render Custom links -->
                        <?php if ($customLinks) : ?>
                            <?php foreach ($customLinks as $customLink): ?>
                                <?php if($subLinks = ArrayHelper::get($customLink, 'sub_links')) :?>
                                    <li class="ff_list_button_item has_sub_menu">
                                        <a class="ff_list_button_link"
                                            title="<?php echo esc_attr(ArrayHelper::get($customLink, 'title', '')); ?>"
                                            aria-label="<?php echo esc_attr(ArrayHelper::get($customLink, 'title', '')); ?>"
                                            href="#">
                                            <i class="ff_settings_menu_icon ff-icon <?php echo esc_attr(ArrayHelper::get($customLink, 'icon', 'ff-icon-link')); ?>" aria-hidden="true"></i>
                                            <span class="ff_settings_menu_label"><?php echo esc_html(ArrayHelper::get($customLink, 'title', '')); ?></span>
                                        </a>
                                        <ul class="ff_list_submenu">
                                            <?php foreach ($subLinks as $customSubLink): ?>
                                                <li
                                                        class="<?php echo esc_attr(ArrayHelper::get($customSubLink, 'class', '')); ?> ff_list_button_item"
                                                >
                                                    <a
                                                            data-hash="<?php echo esc_attr(ArrayHelper::get($customSubLink, 'hash', 'custom_component')) ?>"
                                                            data-settings_key="custom_component"
                                                            data-component="custom_component"
                                                            data-component_name="<?php echo esc_attr(ArrayHelper::get($customSubLink, 'component', '')); ?>"
                                                            href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings', [
                                                                'hash' => esc_attr(ArrayHelper::get($customSubLink, 'hash', 'custom_component')),
                                                            ])); ?>"
                                                    >
                                                        <?php echo esc_attr(ArrayHelper::get($customSubLink, 'title', '')); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </li>
                                <?php else: ?>
                                    <li
                                            class="<?php echo esc_attr(ArrayHelper::get($customLink, 'class', '')); ?> ff_list_button_item"
                                    >
                                        <a
                                                class="ff_list_button_link"
                                                data-hash="<?php echo esc_attr(ArrayHelper::get($customLink, 'hash', 'custom_component')) ?>"
                                                data-settings_key="custom_component"
                                                data-component="custom_component"
                                                data-component_name="<?php echo esc_attr(ArrayHelper::get($customLink, 'component', '')); ?>"
                                                title="<?php echo esc_attr(ArrayHelper::get($customLink, 'title', '')); ?>"
                                                aria-label="<?php echo esc_attr(ArrayHelper::get($customLink, 'title', '')); ?>"
                                                href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_settings', [
                                                    'hash' => esc_attr(ArrayHelper::get($customLink, 'hash', 'custom_component')),
                                                ])); ?>"
                                        >
                                            <i class="ff_settings_menu_icon ff-icon <?php echo esc_attr(ArrayHelper::get($customLink, 'icon', 'ff-icon-link')); ?>" aria-hidden="true"></i>
                                            <span class="ff_settings_menu_label"><?php echo esc_attr(ArrayHelper::get($customLink, 'title', '')); ?></span>
                                        </a>
                                    </li>
                                <?php endif ?>
                            <?php endforeach; ?>
                            <?php do_action('fluentform/global_settings_custom_component'); ?>
                        <?php endif ?>

                    </ul>
                </div>
                <button type="button" class="ff_sidebar_toggle" title="<?php echo esc_attr(__('Collapse settings menu', 'fluentform')); ?>" aria-label="<?php echo esc_attr(__('Collapse settings menu', 'fluentform')); ?>" aria-controls="ff_global_settings_sidebar" aria-expanded="true" data-collapse-label="<?php echo esc_attr(__('Collapse settings menu', 'fluentform')); ?>" data-expand-label="<?php echo esc_attr(__('Expand settings menu', 'fluentform')); ?>">
                    <i class="ff-icon ff-icon-arrow-right" aria-hidden="true"></i>
                    <span class="ff_sidebar_toggle_text"><?php echo esc_html(__('Collapse menu', 'fluentform')); ?></span>
                </button>
            </div>

            <div class="ff_settings_container ff_layout_section_container" id="ff_settings_container">
                <?php
                do_action('fluentform/global_settings_component_' . $currentComponent);
                ?>
            </div>
            <?php
            do_action_deprecated(
                'fluentform_after_global_settings_wrapper',
                [
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/after_global_settings_wrapper',
                'Use fluentform/after_global_settings_wrapper instead of fluentform_after_global_settings_wrapper.'
            );
            do_action('fluentform/after_global_settings_wrapper');
            ?>
        </div>
    </div>
</div>
