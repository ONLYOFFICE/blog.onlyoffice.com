<?php

namespace FluentForm\App\Modules\Payments\Classes;

use FluentForm\App\Modules\Payments\PaymentHelper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class PaymentManagement
{
    public function cancelSubscription($subscription)
    {
        $validStatuses = [
            'active',
            'trialling',
            'failing'
        ];

        if (!in_array($subscription->status, $validStatuses)) {
            return new \WP_Error('wrong_status', 'Sorry, You can not cancel this subscription');
        }
        $oldStatus = $subscription->status;
        $newStatus = 'cancelled';
        $submission = fluentFormApi('submissions')->find($subscription->submission_id);

        // Now let's try to cancel this subscription
        $handler = apply_filters_deprecated(
            'fluentform_payment_manager_class_' . $submission->payment_method,
            [
                false
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_manager_class_' . $submission->payment_method,
            'Use fluentform/payment_manager_class_' . $submission->payment_method . ' instead of fluentform_payment_manager_class_' . $submission->payment_method
        );
        $handler = apply_filters('fluentform/payment_manager_class_' . $submission->payment_method, $handler);

        $message = 'Subscription has been marked as cancelled';

        if($handler) { // we have handler so the subscription cancellation will be managed by them
            $response = $handler->cancelSubscription($subscription, 'admin', $submission);
            if(is_wp_error($response)) {
                return $response;
            }
        } else {
            PaymentHelper::recordSubscriptionCancelled($subscription, false, [
                'parent_source_id' => $submission->form_id,
                'source_type'      => 'submission_item',
                'source_id'        => $submission->id,
                'component'        => 'General',
                'status'           => 'info',
                'title'            => __('Subscription has been cancelled by admin', 'fluentform'),
                'description'      => __('Subscription has been cancelled locally. Subscription may not cancelled at ', 'fluentform') . $submission->payment_method
            ]);
        }

        do_action_deprecated(
            'fluentform_payment_subscription_status_to_cancelled',
            [
                $subscription,
                $submission,
                $oldStatus
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_subscription_status_to_cancelled',
            'Use fluentform/payment_subscription_status_to_cancelled instead of fluentform_payment_subscription_status_to_cancelled.'
        );

        do_action('fluentform/payment_subscription_status_to_cancelled', $subscription, $submission, $oldStatus);

        do_action_deprecated(
            'fluentform_payment_subscription_status_' . $submission->payment_method . '_to_' . $newStatus,
            [
                $subscription,
                $submission,
                $oldStatus
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_subscription_status_' . $submission->payment_method . '_to_' . $newStatus,
            'Use fluentform/payment_subscription_status_' . $submission->payment_method . '_to_' . $newStatus . ' instead of fluentform_payment_subscription_status_' . $submission->payment_method . '_to_' . $newStatus
        );

        do_action('fluentform/payment_subscription_status_' . $submission->payment_method . '_to_' . $newStatus, $subscription, $submission, $oldStatus);


        return $message;
    }
}
