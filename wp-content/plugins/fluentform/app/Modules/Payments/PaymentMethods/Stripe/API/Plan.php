<?php

namespace FluentForm\App\Modules\Payments\PaymentMethods\Stripe\API;

if (!defined('ABSPATH')) {
    exit;
}

use FluentForm\App\Modules\Payments\PaymentHelper;
use FluentForm\App\Modules\Payments\PaymentMethods\Stripe\StripeSettings;

/**
 * Handle Payment Charge Via Stripe
 * @since 1.2.0
 */
class Plan
{
    use RequestProcessor;

    public static function retrieve($planId, $formId)
    {
        try {
            $secretKey = apply_filters_deprecated(
                'fluentform-payment_stripe_secret_key',
                [
                    StripeSettings::getSecretKey($formId),
                    $formId
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/payment_stripe_secret_key',
                'Use fluentform/payment_stripe_secret_key instead of fluentform-payment_stripe_secret_key.'
            );

            $secretKey = apply_filters('fluentform/payment_stripe_secret_key', $secretKey, $formId);

            ApiRequest::set_secret_key($secretKey);

            $response = ApiRequest::request([], 'plans/' . $planId, 'GET');

            return static::processResponse($response);
        } catch (\Exception $e) {
            // Something else happened, completely unrelated to Stripe
            return static::errorHandler('non_stripe', esc_html__('General Error', 'fluentform') . ': ' . $e->getMessage());
        }
    }

    public static function create($plan, $formId)
    {
        $secretKey = apply_filters_deprecated(
            'fluentform-payment_stripe_secret_key',
            [
                StripeSettings::getSecretKey($formId),
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_stripe_secret_key',
            'Use fluentform/payment_stripe_secret_key instead of fluentform-payment_stripe_secret_key.'
        );

        $secretKey = apply_filters('fluentform/payment_stripe_secret_key', $secretKey, $formId);

        ApiRequest::set_secret_key($secretKey);

        $response = ApiRequest::request($plan, 'plans', 'POST');

        return static::processResponse($response);
    }

    public static function getPriceIdsFromSubscriptionTransaction($subscription, $subscriptionTransaction)
    {
        if ($subscriptionTransaction->transaction_type != 'subscription') {
            return false;
        }

        $planItems = [];
        $subscriptionPlan = self::getSubscriptionPlanBySubscription($subscription, $subscriptionTransaction->currency);

        if(is_wp_error($subscriptionPlan)) {
            return $subscriptionPlan;
        }

        $planItems[] = [
            'price'    => $subscriptionPlan->id,
            'quantity' => $subscription->quantity ? $subscription->quantity : 1
        ];

        // Maybe we have signup fee and other single payment amount
        $transactionTotal = $subscriptionTransaction->payment_total;

        $subscriptionFirstTotal = 0;

        if (!$subscription->trial_days) {
            $subscriptionFirstTotal += $subscription->recurring_amount;
        }

        $signupFee = 0;
        if ($transactionTotal > $subscriptionFirstTotal) {
            $signupFee = $transactionTotal - $subscriptionFirstTotal;
        }

        return [
            'items' => $planItems,
            'signup_fee' => $signupFee
        ];
    }

    public static function getOrCreate($subscription, $submission)
    {
        if (PaymentHelper::isZeroDecimal($submission->currency)) {
            $subscription->recurring_amount = intval($subscription->recurring_amount / 100);
        }

        // Generate The subscription ID Here
        $subscriptionId = static::getGeneratedSubscriptionId($subscription, $submission->currency);

        $stripePlan = static::retrieve($subscriptionId, $subscription->form_id);

        if ($stripePlan && !is_wp_error($stripePlan)) {
            return $stripePlan;
        }

        // We don't have this plan yet. Now we have to create the plan from subscription
        $plan = array(
            'id'                => $subscriptionId,
            'currency'          => $submission->currency,
            'interval'          => $subscription->billing_interval,
            'amount'            => $subscription->recurring_amount,
            'trial_period_days' => $subscription->trial_days,
            'product'           => [
                'id'   => $subscriptionId,
                'name' => $subscription->item_name . ' (' . $subscription->plan_name . ')',
                'type' => 'service'
            ],
            'metadata'          => [
                'form_id'    => $subscription->form_id,
                'element_id' => $subscription->element_id,
                'wp_plugin'  => 'Fluent Forms Pro'
            ]
        );

        return static::create($plan, $subscription->form_id);
    }

    public static function getGeneratedSubscriptionId($subscription, $currency = 'USD')
    {
        $subscriptionId = 'fluentform_' . $subscription->element_id . '_' . $subscription->recurring_amount . '_' . $subscription->billing_interval . '_' . $subscription->trial_days . '_' . $currency;;

        $subscriptionId = apply_filters_deprecated(
            'fluentform_stripe_plan_name_generated',
            [
                $subscriptionId,
                $subscription,
                $currency
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/stripe_plan_name_generated',
            'Use fluentform/stripe_plan_name_generated instead of fluentform_stripe_plan_name_generated.'
        );

        return apply_filters('fluentform/stripe_plan_name_generated', $subscriptionId, $subscription, $currency);
    }

    public static function subscribe($subscriptionArgs, $formId)
    {
        $secretKey = apply_filters_deprecated(
            'fluentform-payment_stripe_secret_key',
            [
                StripeSettings::getSecretKey($formId),
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_stripe_secret_key',
            'Use fluentform/payment_stripe_secret_key instead of fluentform-payment_stripe_secret_key.'
        );

        $secretKey = apply_filters('fluentform/payment_stripe_secret_key', $secretKey, $formId);

        ApiRequest::set_secret_key($secretKey);

        return ApiRequest::request($subscriptionArgs, 'subscriptions', 'POST');
    }

    public static function getCancelledAtTimestamp($subscription)
    {
        if(!$subscription->bill_times) {
            return false; // bill times is unlimited
        }

        $billTimes = $subscription->bill_times;
        $billPeriod = $subscription->billing_interval;

        $dateString = $billTimes.' '.$billPeriod;

        if($subscription->expiration_at) {
            return strtotime($subscription->expiration_at) - time() +  strtotime($dateString); // after 6 hours
        }

        return strtotime($dateString);

    }

    public static function getSubscriptionPlanBySubscription($subscription, $currency)
    {
        $recurringAmount = $subscription->recurring_amount;
        if (PaymentHelper::isZeroDecimal($currency)) {
            $recurringAmount = intval($subscription->recurring_amount / 100);
        }

        $planNameArgs = [
            'fluentform',
            $subscription->form_id,
            $recurringAmount,
            $subscription->billing_interval,
            $currency
        ];

        $planName = apply_filters_deprecated(
            'fluentform_stripe_plan_name',
            [
                implode('_', $planNameArgs),
                $subscription
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/stripe_plan_name',
            'Use fluentform/stripe_plan_name instead of fluentform_stripe_plan_name.'
        );

        $planName = apply_filters('fluentform/stripe_plan_name', $planName, $subscription);

        $subscriptionPlan = static::retrieve($planName, $subscription->form_id);

        if (!is_wp_error($subscriptionPlan) && $subscriptionPlan) {
            return $subscriptionPlan;
        }

        $plan = array(
            'id'                => $planName,
            'currency'          => $currency,
            'interval'          => $subscription->billing_interval,
            'amount'            => $recurringAmount,
            'trial_period_days' => $subscription->trial_days,
            'product'           => [
                'id'   => $subscription->id,
                'name' => $subscription->plan_name,
                'type' => 'service'
            ],
            'metadata'          => [
                'form_id'    => $subscription->form_id,
                'element_id' => $subscription->element_id,
                'wp_plugin'  => 'Fluent Forms Pro'
            ],
            'nickname'          => $subscription->item_name . ' - ' . $subscription->plan_name
        );

        return static::create($plan, $subscription->form_id);
    }

    public static function maybeSetCancelAt($subscription, $stripeSub)
    {
        if($stripeSub->cancel_at) {
            return;
        }

        if(!$subscription->bill_times) {
            return;
        }

        $trialOffset = 0;

        $dateStr = '+'.$subscription->bill_times.' '.$subscription->billing_interval;

        if($subscription->trial_days) {
            $trialOffset = $subscription->trial_days * 86400;
        }

        $startingTimestamp = $stripeSub->created;

        $cancelAt = $startingTimestamp + $trialOffset + (strtotime($dateStr) - time());

        $secretKey = apply_filters_deprecated(
            'fluentform-payment_stripe_secret_key',
            [
                StripeSettings::getSecretKey($subscription->form_id),
                $subscription->form_id
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_stripe_secret_key',
            'Use fluentform/payment_stripe_secret_key instead of fluentform-payment_stripe_secret_key.'
        );
        $secretKey = apply_filters('fluentform/payment_stripe_secret_key', $secretKey, $subscription->form_id);

        ApiRequest::set_secret_key($secretKey);

        return ApiRequest::request([
            'cancel_at' => $cancelAt
        ], 'subscriptions/' . $stripeSub->id, 'POST');

    }

}
