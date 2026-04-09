<?php

defined('ABSPATH') or die;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables in view files
?>
<table style="width: 100%;border: 1px solid #cbcbcb;margin-top: 0; margin-bottom: 15px;" class="table ffp_order_items_table ffp_table table_bordered">
    <thead>
    <tr>
        <th><?php esc_html_e('Item', 'fluentform'); ?></th>
        <th><?php esc_html_e('Status', 'fluentform'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($subscriptions as $subscription): ?>
        <tr>
            <td>
                <?php echo esc_html($subscription->plan_name . ' (' . $subscription->item_name . ')'); ?>
                <?php if($subscription->original_plan): ?>
                <p style="margin: 5px 0px 0px;font-size: 90%;"><?php echo fluentform_sanitize_html(\FluentForm\App\Modules\Payments\PaymentHelper::getPaymentSummaryText($subscription->original_plan, $subscription->form_id, $submission->currency, false)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
                <?php endif; ?>
            </td>
            <td><span class="ff_trial_badge"><?php echo esc_html(ucfirst($subscription->status)); ?></span></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
