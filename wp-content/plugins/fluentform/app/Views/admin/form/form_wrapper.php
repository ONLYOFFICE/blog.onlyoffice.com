<?php

defined('ABSPATH') or die;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables in view files
?>
<div class="ff_form_wrap ff_screen_<?php echo esc_attr($route); ?>">
    <div class="global-overlay" id="form-setting-overlay"></div>
	<?php
        do_action_deprecated(
            'fluentform_before_form_screen_wrapper',
            [
                $form_id,
                $route
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/before_form_screen_wrapper',
            'Use fluentform/before_form_screen_wrapper instead of fluentform_before_form_screen_wrapper.'
        );
        do_action('fluentform/before_form_screen_wrapper', $form_id, $route);
    ?>
	
	<div class="form_internal_menu">
        <?php
        if ( is_array($menu_items) && count($menu_items) < 5){
            if (isset($_SERVER['HTTP_REFERER'])): ?>
                <div class="ff_menu_back">
                    <?php // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- sanitize_url() handles unslashing ?>
                    <a class="ff_menu_link" href="<?php echo esc_url(sanitize_url($_SERVER['HTTP_REFERER'])) ;?>">
                        <span class="el-icon-arrow-left"></span>
                    </a>
                </div>
            <?php endif;
        }
        ?>
        <div title="<?php echo esc_html($form->title); ?>" class="ff_form_name" id="js-ff-nav-title">
			<div class="ff_form_name_inner">
                <?php echo esc_html($form->title); ?>
            </div>
		</div>
        <?php
            $extra_menu_class = 'normal_form_editor';
            if (\FluentForm\App\Helpers\Helper::hasPartialEntries($form->id)) $extra_menu_class = "partial_entries_form_editor";
        ?>

        <div class="form_internal_menu_inner">
            <ul class="ff_menu <?php echo esc_attr($extra_menu_class)?>">
                <?php foreach ($menu_items as $menu_index => $menu_item): ?>
                    <li class="<?php if ($route == $menu_item['slug']) echo "active"; ?>">
                        <a class="ff_menu_link" href="<?php echo esc_url($menu_item['url']); ?><?php if (isset($menu_item['hash'])) echo "#". esc_attr($menu_item['hash']); ?>">
                            <?php echo esc_html($menu_item['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <div class="ff-navigation-right">
                <?php
                    do_action_deprecated(
                        'fluentform_after_form_navigation',
                        [
                            $form_id,
                            $route
                        ],
                        FLUENTFORM_FRAMEWORK_UPGRADE,
                        'fluentform/after_form_navigation',
                        'Use fluentform/after_form_navigation instead of fluentform_after_form_navigation.'
                    );
                    do_action('fluentform/after_form_navigation', $form_id, $route);
                ?>
                <?php
                do_action_deprecated(
                    'fluentform_after_form_navigation_' . $route,
                    [
                        $form_id
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/after_form_navigation_' . $route,
                    'Use fluentform/after_form_navigation_' . $route . ' instead of fluentform_after_form_navigation_' . $route
                );
                    do_action('fluentform/after_form_navigation_' . $route, $form_id);
                ?>

                <div id="more-menu">
                    <more-menu />
                </div>
            </div>
        </div>
        <span class="ff_menu_toggle">
            <i class="ff-icon ff-icon-menu"></i>
        </span>
	</div>
    
    <?php
        do_action('fluentform/after_form_menu');

        wp_add_inline_script('fluent_forms_global', "
            //for mobile nav
            let formHeaderMenuElem = jQuery('.form_internal_menu_inner');
            jQuery('.ff_menu_toggle').on('click', function() {
                formHeaderMenuElem.toggleClass('active');
            });

            // for setting sidebar
            let formSettingSidebarElem = jQuery('.ff_settings_sidebar_wrap');
            let formSettingOverlayElem = jQuery('#form-setting-overlay');
            let formSettingSidebarStateKey = 'fluentform_settings_sidebar_collapsed';
            let formSettingSidebarToggle = jQuery('.ff_settings_wrapper .ff_sidebar_toggle');
            let isFormSettingDesktop = function() {
                return window.matchMedia('(min-width: 769px)').matches;
            };
            let syncFormSettingSidebarMode = function() {
                if (isFormSettingDesktop() && window.localStorage.getItem(formSettingSidebarStateKey) === 'yes') {
                    formSettingSidebarElem.addClass('ff_settings_sidebar_collapsed');
                } else if (!isFormSettingDesktop()) {
                    formSettingSidebarElem.removeClass('ff_settings_sidebar_collapsed');
                }
            };
            let updateFormSettingToggleState = function() {
                let collapseLabel = formSettingSidebarToggle.data('collapse-label') || 'Collapse settings menu';
                let expandLabel = formSettingSidebarToggle.data('expand-label') || 'Expand settings menu';
                let isExpanded = isFormSettingDesktop()
                    ? !formSettingSidebarElem.hasClass('ff_settings_sidebar_collapsed')
                    : formSettingSidebarElem.hasClass('active');

                formSettingSidebarToggle
                    .attr('aria-expanded', isExpanded ? 'true' : 'false')
                    .attr('aria-label', isExpanded ? collapseLabel : expandLabel)
                    .attr('title', isExpanded ? collapseLabel : expandLabel);
            };

            syncFormSettingSidebarMode();

            formSettingSidebarToggle.on('click', function() {
                if (isFormSettingDesktop()) {
                    formSettingSidebarElem.toggleClass('ff_settings_sidebar_collapsed');
                    window.localStorage.setItem(
                        formSettingSidebarStateKey,
                        formSettingSidebarElem.hasClass('ff_settings_sidebar_collapsed') ? 'yes' : 'no'
                    );
                    updateFormSettingToggleState();
                    return;
                }

                jQuery(formSettingSidebarElem).add(formSettingOverlayElem).toggleClass('active');
                updateFormSettingToggleState();
            });
            
            jQuery(formSettingOverlayElem).on('click', function() {
                jQuery(formSettingOverlayElem).add(formSettingSidebarElem).removeClass('active');
                updateFormSettingToggleState();
            });

            jQuery(window).on('resize', function() {
                syncFormSettingSidebarMode();
                updateFormSettingToggleState();
            });
            updateFormSettingToggleState();

        ");
    ?>

	<div class="ff_form_application_container">
		<?php
            do_action_deprecated(
                'ff_fluentform_form_application_view_' . $route,
                [
                    $form_id
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/form_application_view_' . $route,
                'Use fluentform/form_application_view_' . $route . ' instead of ff_fluentform_form_application_view_' . $route
            );
            do_action('fluentform/form_application_view_' . $route, $form_id);
        ?>
	</div>
	
	<?php
        do_action_deprecated(
            'fluentform_after_form_screen_wrapper',
            [
                $form_id,
                $route
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/after_form_screen_wrapper',
            'Use fluentform/after_form_screen_wrapper instead of fluentform_after_form_screen_wrapper.'
        );
        do_action('fluentform/after_form_screen_wrapper', $form_id, $route);
    ?>
</div>
