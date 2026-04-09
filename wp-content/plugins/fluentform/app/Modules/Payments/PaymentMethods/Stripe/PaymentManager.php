<?php

namespace FluentForm\App\Modules\Payments\PaymentMethods\Stripe;

use FluentForm\App\Models\Transaction;
use FluentForm\App\Modules\Payments\PaymentHelper;
use FluentForm\App\Modules\Payments\PaymentMethods\Stripe\API\ApiRequest;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class PaymentManager
{
    public function cancelSubscription($subscription, $scope = 'admin', $submission = false)
    {
        if(!$submission) {
            $submission = fluentFormApi('submissions')->find($subscription->submission_id);
        }

        if(!$submission || $submission->payment_method != 'stripe') {
            return new \WP_Error('method_mismatch', __('Failed to cancel this subscription', 'fluentform'));
        }

        // Get the first transaction to determine the payment mode
        $lastTransaction = Transaction::where('subscription_id', $subscription->id)
            ->where('payment_method', 'stripe')
            ->orderBy('id', 'DESC')
            ->first();

        if(!$lastTransaction) {
            return new \WP_Error('transaction_mismatch', __('Failed to cancel this subscription', 'fluentform'));
        }

        $vendorSubscriptionId = $subscription->vendor_subscription_id;

        if(!$vendorSubscriptionId) {
            return new \WP_Error('no_vendor_subscription_found', __('Failed to cancel this subscription', 'fluentform'));
        }

        $secretKey = StripeSettings::getSecretKey($submission->form_id);
        ApiRequest::set_secret_key($secretKey);
        $response = ApiRequest::request([], 'subscriptions/'.$vendorSubscriptionId, 'DELETE');

        if(is_wp_error($response)) {
            return $response;
        }

        PaymentHelper::recordSubscriptionCancelled($subscription, $response, [
            'parent_source_id' => $submission->form_id,
            'source_type'      => 'submission_item',
            'source_id'        => $submission->id,
            'component'        => 'General',
            'status'           => 'info',
            'title'            => __('Subscription has been cancelled by ', 'fluentform') . $scope,
            'description'      => __('Subscription has been cancelled from ', 'fluentform') . $submission->payment_method
        ]);

        // It's a success so let's send a valid response
        return $response;
    }

    public function refundTransaction($transaction, $scope = 'admin')
    {

    }

    private function retrieveVendorSubscription($subscription)
    {

    }

    private function retrieveVendorTransaction($transaction)
    {

    }
}
