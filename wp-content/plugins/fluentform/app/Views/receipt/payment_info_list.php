<?php

defined('ABSPATH') or die;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables in view files
?>
<ul class="ffp_payment_info_table">
    <li>
        <b><?php esc_html_e('Amount:', 'fluentform');?></b> <?php echo fluentform_sanitize_html($orderTotal); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></b>
    </li>
    <?php
    $paymentMethod = $submission->payment_method;
    if($paymentMethod): ?>
        <li>
            <b><?php esc_html_e('Payment Method:', 'fluentform');?></b> <?php
            $paymentMethod = apply_filters_deprecated(
                'fluentform_payment_method_public_name_' . $paymentMethod,
                [
                    $paymentMethod
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/payment_method_public_name_' . $paymentMethod,
                'Use fluentform/payment_method_public_name_' . $paymentMethod . ' instead of fluentform_payment_method_public_name_' . $paymentMethod
            );
            echo esc_html(ucfirst(
                apply_filters(
                    'fluentform/payment_method_public_name_' . $paymentMethod,
                    $paymentMethod
                )
            )); ?></b>
        </li>
    <?php endif; ?>
    <?php
    if ($submission->payment_status):
        $allStatus = \FluentForm\App\Modules\Payments\PaymentHelper::getPaymentStatuses();
        if (isset($allStatus[$submission->payment_status])) {
            $submission->payment_status = $allStatus[$submission->payment_status];
        }
        ?>
        <li>
            <b><?php esc_html_e('Payment Status:', 'fluentform');?></b> <?php echo esc_html($submission->payment_status); ?></b>
        </li>
    <?php endif; ?>
</ul>
