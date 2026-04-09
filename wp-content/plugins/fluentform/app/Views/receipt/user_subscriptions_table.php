<?php

defined('ABSPATH') or die;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables in view files
 /** @var Array $subscriptions */
 /** @var Array $config */
?>
<div class="ff_subscriptions">
    <?php
    foreach ($subscriptions as $subscription): ?>
        <div class="ff_subscription">
            <div class="ff_subscription_header">
                <div class="ff_pay_info">
                    <div class="ff_form_name"><span class="ff_sub_id">#<?php echo esc_html($subscription->id); ?></span> <?php echo fluentform_sanitize_html($subscription->title); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                    <div class="ff_sub_name"><?php echo esc_html($subscription->plan_name); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <span class="ff_sub_input_name">(<?php echo fluentform_sanitize_html($subscription->item_name); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>)</span>
                    </div>
                    <div class="head_payment_amount">
                        <span class="pay_amount"><?php echo fluentform_sanitize_html($subscription->formatted_recurring_amount); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Formatted amount contains decoded currency symbols ?></span>
                        <span>/<?php echo esc_html($subscription->billing_interval); ?></span>
                        <span class="ff_pay_status_badge ff_pay_status_<?php echo esc_attr($subscription->status); ?>">
                            <?php echo esc_html($subscription->status); ?>
                        </span>
                        <?php if ($subscription->billing_text): ?>
                            <p class="ff_billing_text"><?php echo fluentform_sanitize_html($subscription->billing_text); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="ff_plan_info">
                    <p class="ff_billing_dates">
                        <span class="ff_sub_start"><?php esc_html_e('Started:', 'fluentform') ?> <?php echo esc_html($subscription->starting_date_formated); ?></span>
                    </p>
                    <div class="ff_payment_count_btn">
                        <button data-subscription_id="<?php echo esc_attr($subscription->id); ?>" class="ff_show_payments"><?php esc_html_e('View Payments', 'fluentform'); ?>
                        </button>
                        <?php if($subscription->can_cancel): ?>
                        <button data-submission_id="<?php echo esc_attr($subscription->submission_id); ?>" data-subscription_id="<?php echo esc_attr($subscription->id); ?>" class="ff_cancel_subscription"><?php esc_html_e('Cancel', 'fluentform'); ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if($subscription->can_cancel): ?>
            <div style="display: none;" class="ff_sub_cancel_confirmation">
                <h4><?php echo esc_html($config['sub_cancel_confirm_heading']); ?></h4>
                <div class="sub_cancel_btns">
                    <button class="ff_confirm_subscription_cancel"><?php echo esc_html($config['sub_cancel_confirm_btn']); ?></button>
                    <button class="ff_cancel_close"><?php echo esc_html($config['sub_cancel_close']); ?></button>
                    <p class="ff_sub_message_notices"></p>
                </div>
            </div>
            <?php endif; ?>
            <div class="ff_subscription_payments"></div>
        </div>
    <?php endforeach; ?>
</div>
