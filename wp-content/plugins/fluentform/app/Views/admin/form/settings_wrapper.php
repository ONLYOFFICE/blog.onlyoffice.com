<?php

defined('ABSPATH') or die;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables in view files
use FluentForm\Framework\Helpers\ArrayHelper;

$settings_menu_icons = [
	'form_settings'              => 'ff-icon-setting',
	'email_notifications'        => 'ff-icon-email',
	'conditional_confirmations'  => 'ff-icon-checkmark-square',
	'all_integrations'           => 'ff-icon-puzzle',
	'slack'                      => 'ff-icon-promotion',
	'custom_css_js'              => 'ff-icon-code',
	'landing_pages'              => 'ff-icon-web-development',
	'quiz_settings'              => 'ff-icon-task',
	'pdf_feeds'                  => 'ff-icon-document',
	'post_feeds'                 => 'ff-icon-file-add',
	'payment_settings'           => 'ff-icon-payment',
];
?>

<div class="ff_settings_wrapper ff_layout_section">
	<div id="ff_form_settings_sidebar" class="ff_settings_sidebar_wrap">
		<div class="ff_settings_sidebar ff_layout_section_sidebar" role="navigation" aria-label="<?php echo esc_attr(__('Form settings menu', 'fluentform')); ?>">
			<ul class="ff_settings_list ff_list_button">
				<?php
					$settings_base_url = admin_url('admin.php?page=fluent_forms&form_id='.$form_id.'&route=settings&sub_route=form_settings');
					$form_settings_route = $settings_menus['form_settings']['route'];
				?>
				<li class="ff_list_button_item has_sub_menu">
					<a
						class="ff_list_button_link ff-page-scroll"
						data-route_key="<?php echo esc_attr($form_settings_route); ?>"
						aria-label="<?php echo esc_attr(__('Settings', 'fluentform')); ?>"
						title="<?php echo esc_attr(__('Settings', 'fluentform')); ?>"
						href="#confirmation-settings">
						<i class="ff_settings_menu_icon ff-icon <?php echo esc_attr($settings_menu_icons['form_settings']); ?>" aria-hidden="true"></i>
						<span class="ff_settings_menu_label"><?php echo esc_html(__('Settings', 'fluentform')); ?></span>
					</a>
					<ul class="ff_list_submenu">
						<li>
							<a class="ff-page-scroll" href="#confirmation-settings">
								<?php echo esc_html(__('Confirmation Settings', 'fluentform')); ?>
							</a>
						</li>
						<?php if(defined('FLUENTFORMPRO') && $has_double_opt_in): ?>
							<li>
								<a class="ff-page-scroll"
									href="#double-optin-confirmation">
									<?php echo esc_html(__('Double Opt-in Confirmation', 'fluentform')); ?>
								</a>
							</li>
						<?php endif?>
                        <?php if (defined('FLUENTFORMPRO') && \FluentForm\App\Helpers\IntegrationManagerHelper::isIntegrationEnabled('admin_approval')): ?>
                            <li>
                                <a class="ff-page-scroll" href="#admin_approval">
                                    <?php echo esc_html(__('Admin Approval', 'fluentform')); ?>
                                </a>
                            </li>
                        <?php endif ?>
						<li>
							<a class="ff-page-scroll"
								href="#form-layout">
								<?php echo esc_html(__('Form Layout', 'fluentform')); ?>
							</a>
						</li>
						<li>
							<a class="ff-page-scroll"
								href="#scheduling-and-restrictions">
								<?php echo esc_html(__('Scheduling & Restrictions', 'fluentform')); ?>
							</a>
						</li>
						<li>
							<a class="ff-page-scroll"
								href="#advanced-form-validation">
								<?php echo esc_html(__('Advanced Form Validation', 'fluentform')); ?>
							</a>
						</li>
						<li>
							<a class="ff-page-scroll"
								href="#survey-result">
								<?php echo esc_html(__('Survey Result', 'fluentform')); ?>
							</a>
						</li>
						<li>
							<a class="ff-page-scroll"
								href="#compliance-settings">
								<?php echo esc_html(__('Compliance Settings', 'fluentform')); ?>
							</a>
						</li>
						<li>
							<a class="ff-page-scroll"
								href="#other">
								<?php echo esc_html(__('Other', 'fluentform')); ?>
							</a>
						</li>
					</ul>
				</li>
				
				<?php foreach ($settings_menus as $settings_menu_key => $settings_menu): ?>
					<?php if (ArrayHelper::get($settings_menu, 'hash') != 'basic_settings') : ?>

					<li class="ff_list_button_item">
						<?php if(isset($settings_menu['route'])): ?>
							<?php
								$route = $settings_menu['route'];
							?>
							<a class="ff_list_button_link" data-route_key="<?php echo esc_attr($route); ?>" aria-label="<?php echo esc_attr($settings_menu['title']); ?>" title="<?php echo esc_attr($settings_menu['title']); ?>" href="<?php echo esc_url($settings_base_url); ?>#<?php echo esc_attr($route); ?>">
								<i class="ff_settings_menu_icon ff-icon <?php echo esc_attr(ArrayHelper::get($settings_menu_icons, $settings_menu_key, 'ff-icon-setting')); ?>" aria-hidden="true"></i>
								<span class="ff_settings_menu_label"><?php echo esc_html($settings_menu['title']); ?></span>
							</a>
						<?php else: ?>
						<a
							class="ff_list_button_link <?php if(isset($settings_menu['class'])) { echo esc_attr($settings_menu['class']); } ?>"
							data-settings_key="<?php echo (isset($settings_menu['settings_key'])) ? esc_attr($settings_menu['settings_key']) : '';?>"
							data-component="<?php echo (isset($settings_menu['component'])) ? esc_attr($settings_menu['component']) : '';?>"
							data-hash="<?php echo (isset($settings_menu['hash'])) ? esc_attr($settings_menu['hash']) : '';?>"
							aria-label="<?php echo esc_attr($settings_menu['title']); ?>"
							title="<?php echo esc_attr($settings_menu['title']); ?>"
							href="<?php echo esc_url($settings_base_url).'&sub_route='. esc_attr($settings_menu['slug']); ?><?php if(isset($settings_menu['hash'])) { echo '#'. esc_attr($settings_menu['hash']); } ?>">
							<i class="ff_settings_menu_icon ff-icon <?php echo esc_attr(ArrayHelper::get($settings_menu_icons, $settings_menu_key, 'ff-icon-setting')); ?>" aria-hidden="true"></i>
							<span class="ff_settings_menu_label"><?php echo esc_html($settings_menu['title']); ?></span>
						</a>
						<?php endif; ?>
					</li>

					<?php endif;?>
				<?php endforeach; ?>
			</ul>
		</div>
		<button type="button" class="ff_sidebar_toggle" title="<?php echo esc_attr(__('Collapse settings menu', 'fluentform')); ?>" aria-label="<?php echo esc_attr(__('Collapse settings menu', 'fluentform')); ?>" aria-controls="ff_form_settings_sidebar" aria-expanded="true" data-collapse-label="<?php echo esc_attr(__('Collapse settings menu', 'fluentform')); ?>" data-expand-label="<?php echo esc_attr(__('Expand settings menu', 'fluentform')); ?>">
			<i class="ff-icon ff-icon-arrow-right" aria-hidden="true"></i>
			<span class="ff_sidebar_toggle_text"><?php echo esc_html(__('Collapse menu', 'fluentform')); ?></span>
		</button>
	</div><!-- .ff_settings_sidebar_wrap -->

	<div class="ff_settings_container ff_layout_section_container">
		<?php
        do_action_deprecated(
            'fluentform_form_settings_container_' . $current_sub_route,
            [
                $form_id
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/form_settings_container_' . $current_sub_route,
            'Use fluentform/form_settings_container_' . $current_sub_route . ' instead of fluentform_form_settings_container_' . $current_sub_route
        );
            do_action('fluentform/form_settings_container_' . $current_sub_route, $form_id);
        ?>
	</div>
</div>
