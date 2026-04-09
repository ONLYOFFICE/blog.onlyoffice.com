<?php

namespace FluentForm\App\Modules\Payments\Orders;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\OrderItem;
use FluentForm\App\Models\Subscription;
use FluentForm\App\Models\Transaction;
use FluentForm\App\Modules\Payments\PaymentHelper;

class OrderData
{
    public static function getSummary($submission, $form)
    {
        $orderItems = static::getOrderItems($submission);
        $discountItems = static::getDiscounts($submission);

        list($subscriptions, $subscriptionPaymentTotal) = static::getSubscriptionsAndPaymentTotal($submission);

        return [
	        'order_items'                => $orderItems,
	        'discount_items'             => $discountItems,
	        'transactions'               => static::getTransactions($submission->id),
	        'refunds'                    => static::getRefunds($submission->id),
	        'order_items_subtotal'       => static::calculateOrderItemsTotal($orderItems, false, false),
	        'order_items_total'          => static::calculateOrderItemsTotal($orderItems, false, false, $discountItems),
	        'subscriptions'              => $subscriptions,
	        'subscription_payment_total' => $subscriptionPaymentTotal
        ];
    }

    public static function getOrderItems($submission)
    {
        $items = OrderItem::bySubmission($submission->id)
            ->products()
            ->get();

        foreach ($items as $item) {
            $item->formatted_item_price = PaymentHelper::formatMoney($item->item_price, $submission->currency);
            $item->formatted_line_total = PaymentHelper::formatMoney($item->line_total, $submission->currency);
        }

        return $items;
    }

    public static function getDiscounts($submission)
    {
        $items = OrderItem::bySubmission($submission->id)
            ->discounts()
            ->get();

        foreach ($items as $item) {
            $item->formatted_item_price = PaymentHelper::formatMoney($item->item_price, $submission->currency);
            $item->formatted_line_total = PaymentHelper::formatMoney($item->line_total, $submission->currency);
        }

        return $items;
    }

    public static function getTransactions($submissionId)
    {
        $transactions = Transaction::bySubmission($submissionId)
            ->whereIn('transaction_type', ['onetime', 'subscription'])
            ->orderBy('id', 'ASC')
            ->get();

        $formattedTransactions = [];
        foreach ($transactions as $transaction) {
            $transaction->payment_note = Helper::safeUnserialize($transaction->payment_note);
    
            $transaction = apply_filters_deprecated(
                'fluentform_transaction_data_' . $transaction->payment_method,
                [
                    $transaction
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/transaction_data_' . $transaction->payment_method,
                'Use fluentform/transaction_data_' . $transaction->payment_method . ' instead of fluentform_transaction_data_' . $transaction->payment_method
            );

            $transaction = apply_filters('fluentform/transaction_data_' . $transaction->payment_method, $transaction);
            if($transaction) {
                $formattedTransactions[] = $transaction;
            }
        }

        return $formattedTransactions;
    }

    public static function getRefunds($submissionId)
    {
        $transactions = Transaction::bySubmission($submissionId)
            ->refunds()
            ->orderBy('id', 'ASC')
            ->get();

        $formattedTransactions = [];
        foreach ($transactions as $transaction) {
            $transaction->payment_note = Helper::safeUnserialize($transaction->payment_note);
    
            $transaction = apply_filters_deprecated(
                'fluentform_transaction_data_' . $transaction->payment_method,
                [
                    $transaction
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/transaction_data_' . $transaction->payment_method,
                'Use fluentform/transaction_data_' . $transaction->payment_method . ' instead of fluentform_transaction_data_' . $transaction->payment_method
            );

            $transaction = apply_filters('fluentform/transaction_data_' . $transaction->payment_method, $transaction);
            if($transaction) {
                $formattedTransactions[] = $transaction;
            }
        }

        return $formattedTransactions;
    }

    public static function calculateOrderItemsTotal($orderItems, $formatted = false, $currency = false, $discountItems = []) {
        $total = 0;
        foreach ($orderItems as $item) {
            $total += $item->line_total;
        }

        if(count($discountItems)) {
            foreach ($discountItems as $discountItem) {
                $total -= $discountItem->line_total;
            }
        }

        if($formatted) {
            return PaymentHelper::formatMoney($total, $currency);
        }

        return $total;
    }

	public static function getSubscriptionsAndPaymentTotal($submission)
	{
		$subscriptions = Subscription::bySubmission($submission->id)
			->get();

		$subscriptionPaymentTotal = 0;

		foreach ($subscriptions as $subscription) {
			$subscription->original_plan = Helper::safeUnserialize($subscription->original_plan);
			$subscription->vendor_response = Helper::safeUnserialize($subscription->vendor_response);

			$subscription->initial_amount_formatted = PaymentHelper::formatMoney($subscription->initial_amount, $submission->currency);
			$subscription->recurring_amount_formatted = PaymentHelper::formatMoney($subscription->recurring_amount, $submission->currency);
		}

		return [$subscriptions, $subscriptionPaymentTotal];
	}

	public static function getSubscriptionTransactions($subscriptionId)
	{
		$transactions = Transaction::where('subscription_id', $subscriptionId)->get();

		foreach ($transactions as $transaction) {
			$transaction->payment_note = Helper::safeUnserialize($transaction->payment_note);
            
            $transaction->items = apply_filters_deprecated(
                'fluentform_subscription_items_' . $transaction->payment_method,
                [
                    [],
                    $transaction
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/subscription_items_' . $transaction->payment_method,
                'Use fluentform/subscription_items_' . $transaction->payment_method . ' instead of fluentform_subscription_items_' . $transaction->payment_method
            );

			$transaction->items = apply_filters('fluentform/subscription_items_'.$transaction->payment_method, [], $transaction);
		}
        
        $transactions = apply_filters_deprecated(
            'fluentform_subscription_transactions',
            [
                $transactions,
                $subscriptionId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/subscription_transactions',
            'Use fluentform/subscription_transactions instead of fluentform_subscription_transactions.'
        );

		return apply_filters('fluentform/subscription_transactions', $transactions, $subscriptionId);
	}

	public static function getTotalPaid($submission)
	{
		return PaymentHelper::formatMoney($submission->payment_total, $submission->currency);
	}
}
