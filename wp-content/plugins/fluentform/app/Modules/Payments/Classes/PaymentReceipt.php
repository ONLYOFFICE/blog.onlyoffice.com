<?php

namespace FluentForm\App\Modules\Payments\Classes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Modules\Payments\Orders\OrderData;
use FluentForm\App\Modules\Payments\PaymentHelper;

class PaymentReceipt
{

    private $entry;

    private $orderItems = null;

    private $subscriptions = null;
    private $subscriptionTotal = null;

    private $discountItems = null;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }

    public function getItem($property)
    {
        $methodMaps = [
            'receipt' => 'renderReceipt',
            'summary' => 'paymentInfo',
            'summary_list' => 'paymentInfoTable',
            'order_items' => 'itemDetails',
	        'subscription_items' => 'subscriptionDetails'
        ];

        $submissionMaps = [
            'payment_status',
            'payment_total',
            'payment_method'
        ];

        if(isset($methodMaps[$property])) {
            $html = $this->{$methodMaps[$property]}();
            $html .= $this->loadCss();
            return $html;
        }

        if(in_array($property, $submissionMaps)) {
            return $this->getSubmissionValue($property);
        }

        return '';

    }

    public function getSubmissionValue($property)
    {
        if($property == 'payment_total') {
            return OrderData::getTotalPaid($this->entry);
        }

        $value = '';
        if(property_exists($this->entry, $property)) {
            $value = $this->entry->{$property};
        }

        if($property == 'payment_method' && $value == 'test') {
            return __('Offline', 'fluentform');
        }

        return ucfirst($value);
    }

    public function getOrderItems()
    {
        if (!is_null($this->orderItems)) {
            return $this->orderItems;
        }

        $this->orderItems = OrderData::getOrderItems($this->entry);
        return $this->orderItems;
    }

	private function getSubscriptions()
	{
		if (!is_null($this->subscriptions)) {
			return $this->subscriptions;
		}

		list($subscriptions, $total) = OrderData::getSubscriptionsAndPaymentTotal($this->entry);

		$this->subscriptions = $subscriptions;
		$this->subscriptionTotal = PaymentHelper::formatMoney($total, $this->entry->currency);

		return $this->subscriptions;
    }

    public function getDiscountItems()
    {
        if (!is_null($this->discountItems)) {
            return $this->discountItems;
        }

        $this->discountItems = OrderData::getDiscounts($this->entry);
        return $this->discountItems;
    }

    public function renderReceipt()
    {
        $submission = $this->entry;

        if (!$submission) {
            return '<p class="ff_invalid_receipt">' . __('Invalid submission. No receipt found', 'fluentform') . '</p>';
        }

        $html = $this->beforePaymentReceipt();

        $html .= $this->paymentInfo();

        if (count($this->getOrderItems())) {
	        $html .= '<h4>' . __('Order Details', 'fluentform') . '</h4>';
	        $html .= $this->itemDetails();
        }

        if (count($this->getSubscriptions())) {
        	$html .= '<h4>' . __('Subscriptions', 'fluentform') . '</h4>';
        	$html .= $this->subscriptionDetails();
        }

        $html .= $this->customerDetails();
        $html .= $this->afterPaymentReceipt();
        $html .= $this->loadCss();
        return $html;
    }

    private function beforePaymentReceipt()
    {
        ob_start();
        echo '<div class="ff_payment_receipt">';
        do_action_deprecated(
            'fluentform_payment_receipt_before_content',
            [
                $this->entry
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_receipt_before_content',
            'Use fluentform/payment_receipt_before_content instead of fluentform_partial_submission_step_completed.'
        );
        do_action('fluentform/payment_receipt_before_content', $this->entry);
        return ob_get_clean();
    }

    private function afterPaymentReceipt()
    {
        ob_start();
        do_action_deprecated(
            'fluentform_payment_receipt_after_content',
            [
                $this->entry
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_receipt_after_content',
            'Use fluentform/payment_receipt_after_content instead of fluentform_payment_receipt_after_content.'
        );
        do_action('fluentform/payment_receipt_after_content', $this->entry);
        echo '</div>';
        return ob_get_clean();
    }


    private function paymentInfo()
    {
        $preRender = apply_filters_deprecated(
            'fluentform_payment_receipt_pre_render_payment_info',
            [
                '',
                $this->entry
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_receipt_pre_render_payment_info',
            'Use fluentform/payment_receipt_pre_render_payment_info instead of fluentform_payment_receipt_pre_render_payment_info.'
        );
        $preRender = apply_filters('fluentform/payment_receipt_pre_render_payment_info', $preRender, $this->entry);
        if ($preRender) {
            return $preRender;
        }

        $orderItems = $this->getOrderItems();
        $subscriptions = $this->getSubscriptions();

        if (!count($orderItems) && !count($subscriptions)) {
            return;
        }

        $submission = $this->entry;

        if($submission->payment_method == 'test') {
            $submission->payment_method = __('Offline', 'fluentform');
        }

        $discountItems = $this->getDiscountItems();

        return $this->loadView('payment_info', array(
            'submission' => $submission,
            'items' => $orderItems,
            'discount_items' => $discountItems,
	        'totalPaid' => OrderData::getTotalPaid($submission)
        ));
    }

    private function paymentInfoTable()
    {
        $preRender = apply_filters_deprecated(
            'fluentform_payment_receipt_pre_render_payment_info_list',
            [
                '',
                $this->entry
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_receipt_pre_render_payment_info_list',
            'Use fluentform/payment_receipt_pre_render_payment_info_list instead of fluentform_payment_receipt_pre_render_payment_info_list.'
        );
        $preRender = apply_filters('fluentform/payment_receipt_pre_render_payment_info_list', $preRender, $this->entry);
        if ($preRender) {
            return $preRender;
        }

        $orderItems = $this->getOrderItems();

        if (!count($orderItems)) {
            return '';
        }

        $discountItems = $this->getDiscountItems();

        return $this->loadView('payment_info_list', array(
            'submission' => $this->entry,
            'items' => $orderItems,
            'orderTotal' => OrderData::calculateOrderItemsTotal($orderItems, true, $this->entry->currency, $discountItems)
        ));
    }


    private function itemDetails()
    {
        $orderItems = $this->getOrderItems();
        $preRender = apply_filters_deprecated(
            'fluentform_payment_receipt_pre_render_item_details',
            [
                '',
                $this->entry,
                $orderItems
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_receipt_pre_render_item_details',
            'Use fluentform/payment_receipt_pre_render_item_details instead of fluentform_payment_receipt_pre_render_item_details.'
        );
        $preRender = apply_filters('fluentform/payment_receipt_pre_render_item_details', $preRender, $this->entry, $orderItems);
        if ($preRender) {
            return $preRender;
        }

        if (!count($orderItems)) {
            return '';
        }

        $discountItems = $this->getDiscountItems();

        return $this->loadView('order_items_table', array(
            'submission' => $this->entry,
            'items' => $orderItems,
            'discount_items' => $discountItems,
            'subTotal' => OrderData::calculateOrderItemsTotal($orderItems, true, $this->entry->currency),
            'orderTotal' => OrderData::calculateOrderItemsTotal($orderItems, true, $this->entry->currency, $discountItems)
        ));
    }

    private function customerDetails()
    {
        $preRender = apply_filters_deprecated(
            'fluentform_payment_receipt_pre_render_submission_details',
            [
                '',
                $this->entry
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_receipt_pre_render_submission_details',
            'Use fluentform/payment_receipt_pre_render_submission_details instead of fluentform_payment_receipt_pre_render_submission_details.'
        );
        $preRender = apply_filters('fluentform/payment_receipt_pre_render_submission_details', $preRender, $this->entry);
        if ($preRender) {
            return $preRender;
        }

        $transactions = OrderData::getTransactions($this->entry->id);
        if (!$transactions || empty($transactions[0])) {
            return;
        }

        $transaction = $transactions[0];

        return $this->loadView('customer_details', array(
            'submission' => $this->entry,
            'transaction' => $transaction
        ));
    }

    private function loadCss()
    {
        return $this->loadView('custom_css', array('submission' => $this->entry));
    }

    public function loadView($fileName, $data)
    {
        return PaymentHelper::loadView($fileName, $data);
    }

	private function subscriptionDetails()
	{
		$subscriptions = $this->getSubscriptions();
        $preRender = apply_filters_deprecated(
            'fluentform_payment_receipt_pre_render_subscription_details',
            [
                '',
                $this->entry,
                $subscriptions
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_receipt_pre_render_subscription_details',
            'Use fluentform/payment_receipt_pre_render_subscription_details instead of fluentform_payment_receipt_pre_render_subscription_details.'
        );
		$preRender = apply_filters('fluentform/payment_receipt_pre_render_subscription_details', $preRender, $this->entry, $subscriptions);

		if ($preRender) {
			return $preRender;
		}

		return $this->loadView('subscriptions_table', array(
			'submission' => $this->entry,
			'subscriptions' => $subscriptions,
			'orderTotal' => $this->subscriptionTotal
		));
    }
}
