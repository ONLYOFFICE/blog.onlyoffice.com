<?php

defined('ABSPATH') or die;

if($transaction->payer_name || $transaction->payer_email) : ?>
<div class="ffp_customer_details">
    <h4><?php esc_html_e('Customer Details', 'fluentform'); ?></h4>
    <div  class="ffp_submission_details">
        <ul>
            <?php if($transaction->payer_name): ?>
            <li><?php esc_html_e('Customer Name:', 'fluentform');?> <?php echo esc_html($transaction->payer_name); ?></li>
            <?php endif; ?>
            <?php if($transaction->payer_email): ?>
            <li><?php esc_html_e('Customer Email:', 'fluentform');?> <?php echo esc_html($transaction->payer_email); ?></li>
            <?php endif; ?>
            <?php if($transaction->billing_address && strlen($transaction->billing_address) > 2): ?>
            <li><?php esc_html_e('Billing Address:', 'fluentform');?> <?php echo esc_html($transaction->billing_address); ?></li>
            <?php endif; ?>
            <?php if($transaction->shipping_address && strlen($transaction->shipping_address) > 2): ?>
            <li><?php esc_html_e('Shipping Address:', 'fluentform');?> <?php echo esc_html($transaction->shipping_address); ?></li>
            <?php endif; ?>
        </ul>
    </div>
</div>
<?php endif; ?>
