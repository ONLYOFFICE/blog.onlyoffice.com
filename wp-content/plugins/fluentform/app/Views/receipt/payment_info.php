<?php

defined('ABSPATH') or die;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables in view files
?>
<div class="ffp_payment_info">
    <div class="ffp_payment_info_item ffp_payment_info_item_order_id">
        <div class="ffp_item_heading"><?php esc_html_e('Order ID:', 'fluentform');?></div>
        <div class="ffp_item_value">#<?php echo esc_html($submission->id); ?></div>
    </div>
    <div class="ffp_payment_info_item ffp_payment_info_item_date">
        <div class="ffp_item_heading"><?php esc_html_e('Date:' ,'fluentform');?></div>
        <div class="ffp_item_value"><?php echo esc_html(date(get_option( 'date_format' ), strtotime($submission->created_at))); ?></div>
    </div>
    <div class="ffp_payment_info_item ffp_payment_info_item_total">
        <div class="ffp_item_heading"><?php esc_html_e('Total:','fluentform');?></div>
        <div class="ffp_item_value"><?php echo fluentform_sanitize_html($totalPaid); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
    </div>
    <?php
    $paymentMethod = $submission->payment_method;
    if($paymentMethod): ?>
        <div class="ffp_payment_info_item ffp_payment_info_item_payment_method">
            <div class="ffp_item_heading"><?php esc_html_e('Payment Method:','fluentform');?></div>
            <div class="ffp_item_value"><?php
                $paymentMethod = apply_filters_deprecated(
                    'fluentform_payment_method_public_name_' . $paymentMethod,
                    [
                        $paymentMethod
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/payment_method_public_name_' . $paymentMethod,
                    'Use fluentform/payment_method_public_name_' . $paymentMethod. ' instead of fluentform_payment_method_public_name_' . $paymentMethod
                );
                echo esc_html(ucfirst(
                    apply_filters(
                        'fluentform/payment_method_public_name_' . $paymentMethod,
                        $paymentMethod
                    )
                )); ?></div>
        </div>
    <?php endif; ?>
    <?php
    if ($submission->payment_status):
        $allStatus = \FluentForm\App\Modules\Payments\PaymentHelper::getPaymentStatuses();
        if (isset($allStatus[$submission->payment_status])) {
            $submission->payment_status = $allStatus[$submission->payment_status];
        }
        ?>
        <div class="ffp_payment_info_item ffp_payment_info_item_payment_status">
            <div class="ffp_item_heading"><?php esc_html_e('Payment Status:','fluentform');?></div>
            <div class="ffp_item_value"><?php echo esc_html($submission->payment_status); ?></div>
        </div>
    <?php endif; ?>
</div>
