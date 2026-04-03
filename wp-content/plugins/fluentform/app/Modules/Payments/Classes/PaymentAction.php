<?php

namespace FluentForm\App\Modules\Payments\Classes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\OrderItem;
use FluentForm\App\Models\Submission;
use FluentForm\App\Models\Subscription;
use FluentForm\App\Models\SubmissionMeta;
use FluentForm\App\Models\Transaction;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Services\ConditionAssesor;
use FluentForm\App\Services\Form\SubmissionHandlerService;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Payments\PaymentHelper;

class PaymentAction
{
    private $form;

    private $data;

    private $submissionData;

    private $submissionId = null;

    private $orderItems = [];

    private $hookedOrderItems = [];

    private $subscriptionItems = [];

    private $quantityItems = [];

    public $selectedPaymentMethod = '';

    public $methodSettings = [];

    protected $paymentInputs = null;

    protected $subscriptionInputs = null;

    protected $currency = null;

    protected $methodField = null;

    protected $discountCodes = [];

    protected $couponField = [];

    public function __construct($form, $insertData, $data)
    {
        $this->form = $form;
        $this->data = $data;
        $this->setSubmissionData($insertData);
        $this->setupData();
    }

    private function setSubmissionData($insertData)
    {
        $insertData = (array)$insertData;
        $insertData['response'] = json_decode($insertData['response'], true);
        $this->submissionData = $insertData;
    }

    private function setupData()
    {
        $formFields = FormFieldsParser::getPaymentFields($this->form, ['admin_label', 'attributes', 'settings']);

        $paymentInputElements = ['custom_payment_component', 'multi_payment_component'];
        $quantityItems = [];
        $paymentInputs = [];
        $subscriptionInputs = [];
        $paymentMethod = false;
        $couponField = false;
        foreach ($formFields as $fieldKey => $field) {
            $element = ArrayHelper::get($field, 'element');
            if (in_array($element, $paymentInputElements)) {
                $paymentInputs[$fieldKey] = $field;
            } else if ($element == 'item_quantity_component' || $element == 'rangeslider') {
                if ('rangeslider' == $element && 'yes' != ArrayHelper::get($field, 'settings.enable_target_product')) {
                    continue;
                }
                if ($targetProductName = ArrayHelper::get($field, 'settings.target_product')) {
                    $quantityItems[$targetProductName] = ArrayHelper::get($field, 'attributes.name');
                }
            } else if ($element == 'payment_method') {
                $paymentMethod = $field;
            } else if ($element == 'payment_coupon' && Helper::hasPro()) {
                $couponField = $field;
            } else if ($element === 'subscription_payment_component') {
                $subscriptionInputs[$fieldKey] = $field;
            }
        }

        $this->paymentInputs = $paymentInputs;
        $this->quantityItems = $quantityItems;
        $this->subscriptionInputs = $subscriptionInputs;

        if ($paymentMethod) {
            $this->methodField = $paymentMethod;
            if ($this->isConditionPass()) {
                $methodName = ArrayHelper::get($paymentMethod, 'attributes.name');
                $this->selectedPaymentMethod = ArrayHelper::get($this->data, $methodName);
                $this->methodSettings = ArrayHelper::get($paymentMethod, 'settings.payment_methods.' . $this->selectedPaymentMethod);
            }
        }

        if ($couponField) {
            $couponCodes = ArrayHelper::get($this->data, '__ff_all_applied_coupons', '');
            if ($couponCodes) {
                $couponCodes = \json_decode($couponCodes, true);
                if ($couponCodes && class_exists('FluentFormPro\Payments\Classes\CouponModel')) {
                    $couponCodes = array_unique($couponCodes);
                    $this->discountCodes = (new \FluentFormPro\Payments\Classes\CouponModel())->getCouponsByCodes($couponCodes);
                    $this->couponField = $couponField;
                }
            }
        }

        if ($this->subscriptionInputs) {
            // Maybe we have subscription items with bill times = 1
            // Or if we have discount codes then we have to apply the discount codes
            $this->validateSubscriptionInputs();
        }

        $this->applyDiscountCodes();
    }

    public function isConditionPass()
    {
        $conditionSettings = ArrayHelper::get($this->methodField, 'settings.conditional_logics', []);
        if (
            !$conditionSettings ||
            !ArrayHelper::isTrue($conditionSettings, 'status')
        ) {
            return true;
        }

        $conditionFeed = ['conditionals' => $conditionSettings];
        return ConditionAssesor::evaluate($conditionFeed, $this->data);
    }

    public function draftFormEntry()
    {
        // Record Payment Items
        $subscriptionItems = $this->getSubscriptionItems();

        if (count($subscriptionItems) >= 2) {
            // We are not supporting multiple subscription items at this moment
            wp_send_json_error([
                'message' => __('Sorry, multiple subscription item is not supported', 'fluentform')
            ]);
        }

        $items = $this->getOrderItems();

        $existingSubmission = $this->checkForExistingSubmission();

        $formSettings = PaymentHelper::getFormSettings($this->form->id, 'public');
        $submission = $this->submissionData;
        $submission['payment_status'] = 'pending';
        $submission['payment_method'] = $this->selectedPaymentMethod;
        $submission['payment_type'] = $this->getPaymentType();
        $submission['currency'] = $formSettings['currency'];
        $submission['response'] = json_encode($submission['response']);
        $submission['payment_total'] = $this->getCalculatedAmount();
        $submission = apply_filters_deprecated(
            'fluentform_with_payment_submission_data',
            [
                $submission,
                $this->form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_submission_data',
            'Use fluentform/payment_submission_data instead of fluentform_with_payment_submission_data.'
        );
        $submission = apply_filters('fluentform/payment_submission_data', $submission, $this->form);

        if ($existingSubmission) {
            $insertId = $existingSubmission->id;
            Submission::where('id', $insertId)->update($submission);

            // delete the existing transactions here if any
            Transaction::where('submission_id', $insertId)->delete();
        } else {
            $insertId = Submission::create($submission)->id;
            $uidHash = md5(wp_generate_uuid4() . $insertId);
            Helper::setSubmissionMeta($insertId, '_entry_uid_hash', $uidHash, $this->form->id);
            $intermediatePaymentHash = md5('payment_' . wp_generate_uuid4() . '_' . $insertId . '_' . $this->form->id);
            Helper::setSubmissionMeta($insertId, '__entry_intermediate_hash', $intermediatePaymentHash, $this->form->id);
        }

        $submission['id'] = $insertId;
        $this->setSubmissionData($submission);
        $this->submissionId = $insertId;


        $paymentTotal = 0;
        if ($items) {
            foreach ($items as $index => $item) {
                if ($item['type'] == 'discount') {
                    $paymentTotal -= $item['line_total'];
                } else {
                    $paymentTotal += $item['line_total'];
                }
                $items[$index]['submission_id'] = $insertId;
                $items[$index]['form_id'] = $submission['form_id'];
            }
        }

        $this->insertOrderItems($items, $existingSubmission);

        $subsTotal = 0;
        if ($subscriptionItems && $existingSubmission) {
            Subscription::where('submission_id', $existingSubmission->id)->delete();
        }

        foreach ($subscriptionItems as $subscriptionItem) {
            $quantity = isset($subscriptionItem['quantity']) ? $subscriptionItem['quantity'] : 1;
            $linePrice = $subscriptionItem['recurring_amount'] * $quantity;
            $subsTotal += intval($linePrice);
            $subscriptionItem['submission_id'] = $insertId;
            Subscription::create($subscriptionItem);
        }

        do_action('fluentform/notify_on_form_submit', $this->submissionId, $this->submissionData['response'], $this->form);

        $totalPayable = $paymentTotal + $subsTotal;

        // We should make a transaction for subscription

        if ($this->selectedPaymentMethod) {
            Helper::setSubmissionMeta($insertId, '_selected_payment_method', $this->selectedPaymentMethod);
            do_action_deprecated(
                'fluentform_process_payment',
                [
                    $this->submissionId,
                    $this->submissionData,
                    $this->form,
                    $this->methodSettings,
                    !!$subscriptionItems,
                    $totalPayable
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/process_payment',
                'Use fluentform/process_payment instead of fluentform_process_payment.'
            );
            do_action('fluentform/process_payment', $this->submissionId, $this->submissionData, $this->form, $this->methodSettings, !!$subscriptionItems, $totalPayable);

            do_action_deprecated(
                'fluentform_process_payment_' . $this->selectedPaymentMethod,
                [
                    $this->submissionId,
                    $this->submissionData,
                    $this->form,
                    $this->methodSettings,
                    !!$subscriptionItems,
                    $totalPayable
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/process_payment_' . $this->selectedPaymentMethod,
                'Use fluentform/process_payment_' . $this->selectedPaymentMethod . ' instead of fluentform_process_payment_' . $this->selectedPaymentMethod
            );
            do_action('fluentform/process_payment_' . $this->selectedPaymentMethod, $this->submissionId, $this->submissionData, $this->form, $this->methodSettings, !!$subscriptionItems, $totalPayable);
        }

        /*
         * The following code will run only if no payment method catch and process the payment
         * In the payment method, ideally they will send the response. But if no payment method exist then
         * we will handle here
         */
        $submission = Submission::find($insertId);
    
        $returnData = (new SubmissionHandlerService())->processSubmissionData(
            $submission->id, $this->submissionData['response'], $this->form
        );

        wp_send_json_success($returnData, 200);
    }

    public function getOrderItems($forced = false)
    {
        if ($forced) {
            $this->orderItems = [];
        }

        if ($this->orderItems) {
            return $this->orderItems;
        }

        $paymentInputs = $this->paymentInputs;

        if (!$paymentInputs && !$this->hookedOrderItems) {
            return [];
        }

        $data = $this->submissionData['response'];

        foreach ($paymentInputs as $paymentInput) {
            $name = ArrayHelper::get($paymentInput, 'attributes.name');
            if (!$name || !isset($data[$name])) {
                continue;
            }
            $price = 0;
            $inputType = ArrayHelper::get($paymentInput, 'attributes.type');

            if (!$data[$name]) {
                continue;
            }

            if ($inputType == 'number') {
                $price = $data[$name];
            } else if ($inputType == 'single') {
                $price = ArrayHelper::get($paymentInput, 'attributes.value');
                if (ArrayHelper::get($paymentInput, 'settings.dynamic_default_value')) {
                    $price = $data[$name];
                }
            } else if ($inputType == 'radio' || $inputType == 'select') {
                $item = $this->getItemFromVariables($paymentInput, $data[$name]);
                if ($item) {
                    $quantity = $this->getQuantity($item['parent_holder']);
                    if (!$quantity) {
                        continue;
                    }
                    $item['quantity'] = $quantity;
                    $this->pushItem($item);
                }
                continue;
            } else if (ArrayHelper::get($paymentInput, 'attributes.type') == 'checkbox') {
                $selectedItems = $data[$name];
                foreach ($selectedItems as $selectedItem) {
                    $item = $this->getItemFromVariables($paymentInput, $selectedItem);
                    if ($item) {
                        $quantity = $this->getQuantity($item['parent_holder']);
                        if (!$quantity) {
                            continue;
                        }
                        $item['quantity'] = $quantity;
                        $this->pushItem($item);
                    }
                }
                continue;
            }

            if (!is_numeric($price) || !$price) {
                continue;
            }

            $productName = ArrayHelper::get($paymentInput, 'attributes.name');
            $quantity = $this->getQuantity($productName);
            if (!$quantity) {
                continue;
            }

            $this->pushItem([
                'parent_holder' => $productName,
                'item_name'     => ArrayHelper::get($paymentInput, 'admin_label'),
                'item_price'    => $price,
                'quantity'      => $quantity
            ]);
        }

        // We may have initial amount from the subscription
        if ($this->hookedOrderItems) {
            $this->orderItems = array_merge($this->orderItems, $this->hookedOrderItems);
        }
    
        $this->orderItems = apply_filters_deprecated(
            'fluentform_submission_order_items',
            [
                $this->orderItems,
                $this->submissionData,
                $this->form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/submission_order_items',
            'Use fluentform/submission_order_items instead of fluentform_submission_order_items.'
        );

        $this->orderItems = apply_filters('fluentform/submission_order_items', $this->orderItems, $this->submissionData, $this->form, $this->selectedPaymentMethod);

        return $this->orderItems;
    }

    private function getQuantity($productName)
    {
        $quantity = 1;
        if (!$this->quantityItems) {
            return $quantity;
        }
        if (!isset($this->quantityItems[$productName])) {
            return $quantity;
        }
        $inputName = $this->quantityItems[$productName];
        $quantity = ArrayHelper::get($this->submissionData['response'], $inputName);
        if (!$quantity) {
            return 0;
        }
        return intval($quantity);
    }

    private function pushItem($data)
    {
        if (!$data['item_price']) {
            return;
        }
        $data['item_price'] = floatval($data['item_price'] * 100);

        $defaults = [
            'type'       => 'single',
            'form_id'    => $this->form->id,
            'quantity'   => !empty($data['quantity']) ? $data['quantity'] : 1,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];

        $item = wp_parse_args($data, $defaults);

        $item['line_total'] = $item['item_price'] * $item['quantity'];

        if (!$this->orderItems) {
            $this->orderItems = [];
        }

        $this->orderItems[] = $item;
    }

    private function getItemFromVariables($item, $key)
    {
        $elementName = $item['element'];
        $pricingOptions = ArrayHelper::get($item, 'settings.pricing_options');
        $pricingOptions = apply_filters_deprecated(
            'fluentform_payment_field_' . $elementName . '_pricing_options',
            [
                $pricingOptions,
                $item,
                $this->form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_field_' . $elementName . '_pricing_options',
            'Use fluentform/payment_field_' . $elementName . '_pricing_options instead of fluentform_payment_field_' . $elementName . '_pricing_options.'
        );
        $pricingOptions = apply_filters('fluentform/payment_field_' . $elementName . '_pricing_options', $pricingOptions, $item, $this->form);

        $selectedOption = [];
        foreach ($pricingOptions as $priceOption) {
            $label = sanitize_text_field($priceOption['label']);
            $value = sanitize_text_field($priceOption['value']);
            if ($label == $key || $value == $key) {
                $selectedOption = $priceOption;
            }
        }

        if (!$selectedOption || empty($selectedOption['value']) || !is_numeric($selectedOption['value'])) {
            return false;
        }

        return [
            'parent_holder' => ArrayHelper::get($item, 'attributes.name'),
            'item_name'     => $selectedOption['label'],
            'item_price'    => $selectedOption['value']
        ];
    }

    public function getCalculatedAmount()
    {
        $items = $this->getOrderItems();

        $total = 0;
        foreach ($items as $item) {
            if ($item['type'] == 'discount') {
                $total -= $item['line_total'];
            } else {
                $total += $item['line_total'];
            }
        }
        return $total;
    }

    public function getPaymentType()
    {
        return count($this->getSubscriptionItems()) ? 'subscription' : 'product'; // return value product|subscription|donation
    }

    private function getCurrency()
    {
        if ($this->currency !== null) {
            return $this->currency;
        }
        $this->currency = 'usd';

        return $this->currency;
    }

    public function getSubscriptionItems()
    {
        if ($this->subscriptionItems) {
            return $this->subscriptionItems;
        }

        $data = $this->submissionData['response'];
        $subscriptionInputs = $this->subscriptionInputs;

        if (!$subscriptionInputs) {
            return [];
        }

        foreach ($subscriptionInputs as $subscriptionInput) {
            $name = ArrayHelper::get($subscriptionInput, 'attributes.name');
            $quantity = $this->getQuantity($name);

            if (!$name || !isset($data[$name]) || $quantity === 0) {
                continue;
            }

            $label = ArrayHelper::get($subscriptionInput, 'settings.label', $name);

            $subscriptionOptions = ArrayHelper::get($subscriptionInput, 'settings.subscription_options');

            $plan = $subscriptionOptions[$data[$name]];

            if (!$plan) {
                continue;
            }

            if (ArrayHelper::get($plan, 'user_input') === 'yes') {
                $plan['subscription_amount'] = ArrayHelper::get($data, $name . '_custom_' . $data[$name]);
                $plan['subscription_amount'] = $plan['subscription_amount'] ?: 0;
            }

            $noTrial = ArrayHelper::get($plan, 'has_trial_days') === 'no' ||
                       !ArrayHelper::get($plan, 'trial_days');
                       
            if (!$plan['subscription_amount'] && $noTrial) {
                continue;
            }

            if (ArrayHelper::get($plan, 'bill_times') == 1 && ArrayHelper::get($plan, 'has_trial_days') != 'yes') {
                // Since the billing times is 1 and no trial days,
                // the subscription acts like as an one time payment.
                // We'll convert this as a payment item.
                $signupFee = 0;

                if ($plan['has_signup_fee'] === 'yes') {
                    $signupFee = PaymentHelper::convertToCents($plan['signup_fee']);
                }

                $onetimeTotal = $signupFee + PaymentHelper::convertToCents($plan['subscription_amount']);

                $this->pushItem([
                    'parent_holder' => $name,
                    'item_name'     => $label,
                    'quantity'      => $quantity,
                    'item_price'    => $onetimeTotal,
                    'line_total'    => $quantity * $onetimeTotal,
                    'created_at'    => current_time('mysql'),
                    'updated_at'    => current_time('mysql')
                ]);
            } else {
                $billTimes = (isset($plan['bill_times'])) ? $plan['bill_times'] : 0;

                // If end date is set, dynamically calculate bill_times from today
                if (
                    ArrayHelper::get($plan, 'has_end_date') === 'yes'
                    && ($endDateStr = ArrayHelper::get($plan, 'subscription_end_date'))
                ) {
                    $endDate = strtotime($endDateStr . ' +1 day');
                    $now = current_time('timestamp');
                    if (!$endDate || $endDate <= $now) {
                        if (ArrayHelper::get($plan, 'expire_behavior') === 'hide') {
                            continue;
                        }
                        wp_send_json([
                            'errors' => [__('This subscription plan was expired', 'fluentform')]
                        ], 423);
                    }
                    $diffDays = max(1, ceil(($endDate - $now) / 86400));
                    $intervalMap = ['day' => 1, 'week' => 7, 'month' => 30, 'year' => 365];
                    $interval = isset($intervalMap[$plan['billing_interval']]) ? $intervalMap[$plan['billing_interval']] : 30;
                    $billTimes = max(1, ceil($diffDays / $interval));
                }

                $subscription = array(
                    'element_id'       => $name,
                    'item_name'        => $label,
                    'form_id'          => $this->form->id,
                    'plan_name'        => $plan['name'],
                    'billing_interval' => $plan['billing_interval'],
                    'trial_days'       => 0,
                    'recurring_amount' => PaymentHelper::convertToCents($plan['subscription_amount']),
                    'bill_times'       => $billTimes,
                    'initial_amount'   => 0,
                    'status'           => 'pending',
                    'original_plan'    => maybe_serialize($plan),
                    'created_at'       => current_time('mysql'),
                    'updated_at'       => current_time('mysql'),
                );

                if (ArrayHelper::get($plan, 'has_signup_fee') === 'yes' && ArrayHelper::get($plan, 'signup_fee')) {
                    $subscription['initial_amount'] = PaymentHelper::convertToCents($plan['signup_fee']);
                }

                if (ArrayHelper::get($plan, 'has_trial_days') === 'yes' && ArrayHelper::get($plan, 'trial_days')) {
                    $subscription['trial_days'] = $plan['trial_days'];
                    $dateTime = current_datetime();
                    $localtime = $dateTime->getTimestamp() + $dateTime->getOffset();
                    $expirationDate = date('Y-m-d H:i:s', $localtime + absint($plan['trial_days']) * 86400);
                    $subscription['expiration_at'] = $expirationDate;
                }

                if ($quantity > 1) {
                    $subscription['quantity'] = $quantity;
                }

                $this->subscriptionItems[] = $subscription;
            }
        }
        $this->subscriptionItems = apply_filters_deprecated(
            'fluentform_submission_subscription_items',
            [
                $this->subscriptionItems,
                $this->submissionData,
                $this->form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/submission_subscription_items',
            'Use fluentform/submission_subscription_items instead of fluentform_submission_subscription_items.'
        );
        $this->subscriptionItems = apply_filters('fluentform/submission_subscription_items', $this->subscriptionItems, $this->submissionData, $this->form);

        return $this->subscriptionItems;
    }

    private function checkForExistingSubmission()
    {
        $entryUid = ArrayHelper::get($this->submissionData, 'response.__entry_intermediate_hash');

        if (!$entryUid) {
            return false;
        }

        $meta = SubmissionMeta::where('meta_key', '__entry_intermediate_hash')
            ->where('value', $entryUid)
            ->where('form_id', $this->form->id)
            ->first();

        if (!$meta) {
            return false;
        }

        $submission = Submission::find($meta->response_id);

        if ($submission && ($submission->payment_status == 'failed' || $submission->payment_status == 'pending' || $submission->payment_status == 'draft')) {
            return $submission;
        }

        return false;
    }

    private function insertOrderItems($items, $existing = false)
    {
        if (!$existing) {
            foreach ($items as $item) {
                OrderItem::create($item);
            }
            return true;
        }

        if (!$items && $existing) {
            OrderItem::where('submission_id', $existing->id)->delete();
            return true;
        }

        $exitingItems = OrderItem::where('submission_id', $existing->id)->get();

        if (!$exitingItems || count($exitingItems) === 0) {
            foreach ($items as $item) {
                OrderItem::create($item);
            }
            return true;
        }

        $existingHashes = [];
        foreach ($exitingItems as $exitingItem) {
            $hash = md5($exitingItem->type . ':' . $exitingItem->parent_holder . ':' . $exitingItem->item_name . ':' . $exitingItem->quantity . ':' . $exitingItem->item_price);
            $existingHashes[$exitingItem->id] = $hash;
        }

        $verifiedIds = [];
        $newIds = [];
        foreach ($items as $item) {
            $hash = md5($item['type'] . ':' . $item['parent_holder'] . ':' . $item['item_name'] . ':' . $item['quantity'] . ':' . $item['item_price']);
            if (in_array($hash, $existingHashes)) {
                // already exist no need to add
                $verifiedIds[] = array_search($hash, $existingHashes);
            } else {
                $newId = OrderItem::create($item)->id;
                $verifiedIds[] = $newId;
                $newIds[] = $newId;
            }
        }

        if ($verifiedIds) {
            OrderItem::whereNotIn('id', $verifiedIds)->delete();
        }

        return true;
    }

    private function validateSubscriptionInputs()
    {
        $subscriptionInputs = $this->subscriptionInputs;
        if (!$subscriptionInputs) {
            return;
        }
        $data = $this->submissionData['response'];

        $discountCodes = $this->discountCodes;

        foreach ($subscriptionInputs as $inputIndex => $subscriptionInput) {
            $name = ArrayHelper::get($subscriptionInput, 'attributes.name');
            $quantity = $this->getQuantity($name);

            if (!$quantity) {
                continue;
            }

            if (!$name || !isset($data[$name])) {
                continue;
            }

            $subscriptionOptions = ArrayHelper::get($subscriptionInput, 'settings.subscription_options');

            $plan = $subscriptionOptions[$data[$name]];

            if (!$plan) {
                continue;
            }

            if ($discountCodes) {

            }

            if (ArrayHelper::get($plan, 'has_trial_days') == 'yes' && ArrayHelper::get($plan, 'trial_days')) {
                continue; // this is a valid subscription
            }

            if (ArrayHelper::get($plan, 'bill_times') != 1) {
                continue;
            }

            // We have bill times 1 so we have to remove this and push to  hooked inputs and later merged to payment inputs

            if (ArrayHelper::get($plan, 'user_input') === 'yes') {
                $plan['subscription_amount'] = ArrayHelper::get($data, $name . '_custom_' . $data[$name]);
                $plan['subscription_amount'] = $plan['subscription_amount'] ?: 0;
            }

            $amount = PaymentHelper::convertToCents($plan['subscription_amount']);

            if (ArrayHelper::get($plan, 'has_signup_fee') === 'yes' && ArrayHelper::get($plan, 'signup_fee')) {
                $amount += PaymentHelper::convertToCents($plan['signup_fee']);
            }

            $this->hookedOrderItems[] = [
                'type'          => 'single',
                'form_id'       => $this->form->id,
                'parent_holder' => $name,
                'item_name'     => ArrayHelper::get($subscriptionInput, 'admin_label') . ' (' . $plan['name'] . ')',
                'item_price'    => $amount,
                'quantity'      => $quantity,
                'line_total'    => $quantity * $amount,
                'created_at'    => current_time('mysql'),
                'updated_at'    => current_time('mysql'),
            ];

            unset($this->subscriptionInputs[$inputIndex]);
        }

    }

    protected function applyDiscountCodes()
    {
        if (!$this->discountCodes) {
            return false;
        }

        $orderItems = $this->getOrderItems(true);


        $subTotal = array_sum(array_column($orderItems, 'line_total')) / 100;

        $subscriptionItems = $this->getSubscriptionItems();

        $subInitialTotal = 0;
        foreach ($subscriptionItems as $subscriptionItem) {
            if ($subscriptionItem['trial_days']) {
                continue; // it's a trial
            }
            $subInitialTotal += $subscriptionItem['recurring_amount'] + $subscriptionItem['initial_amount'];
        }

        $grandTotal = $subTotal;
        if ($subInitialTotal) {
            $grandTotal += ($subInitialTotal / 100);
        }

        $fixedAmountApplied = 0; // in cents

        if (Helper::hasPro() && class_exists('FluentFormPro\Payments\Classes\CouponModel')) {
            $couponModel = new \FluentFormPro\Payments\Classes\CouponModel();
            $this->discountCodes = $couponModel->getValidCoupons($this->discountCodes, $this->form->id, $grandTotal);
        } else {
            $this->discountCodes = [];
        }

        foreach ($this->discountCodes as $coupon) {
            $discountAmount = $coupon->amount;
            if ($coupon->coupon_type == 'percent') {
                $discountAmount = (floatval($coupon->amount) / 100) * $subTotal;
            } else {
                if ($subTotal >= $discountAmount) {
                    $fixedAmountApplied += $discountAmount;
                } else {
                    $discountAmount = $subTotal;
                    $fixedAmountApplied += $subTotal;
                }
            }

            $this->pushItem([
                'parent_holder' => ArrayHelper::get($this->couponField, 'attributes.name'),
                'item_name'     => $coupon->title,
                'item_price'    => $discountAmount, // this is not cent. We convert to cent at pushItem method
                'quantity'      => 1,
                'type'          => 'discount'
            ]);

            $subTotal = $subTotal - $discountAmount;
        }


        // let's convert to cents now as all subscriptions calculations are on cents
        $fixedAmountApplied = intval($fixedAmountApplied * 100);

        if (!$subscriptionItems) {
            return true;
        }

        $fixedMaxTotal = 0;
        $hasFixedDiscounts = false;
        foreach ($this->discountCodes as $discountCode) {
            if($discountCode->coupon_type == 'fixed') {
                $fixedMaxTotal += intval($coupon->amount * 100);
                $hasFixedDiscounts = true;
            }
        }

        $fixedMaxTotal = $fixedMaxTotal - $fixedAmountApplied;

        foreach ($subscriptionItems as $subIndex => $subscriptionItem) {
            $recurringAmount = $subscriptionItem['recurring_amount'];
            $signupFee = 0;
            if ($subscriptionItem['initial_amount']) {
                $signupFee = $subscriptionItem['initial_amount'];
            }
            // Let's process the percentile discounts first
            foreach ($this->discountCodes as $coupon) {
                $discountAmount = $coupon->amount;
                if ($coupon->coupon_type == 'percent') {
                    $discountRecurringAmount = floatval((floatval($discountAmount) / 100) * $recurringAmount);
                    $recurringAmount -= $discountRecurringAmount;
                    if ($signupFee) {
                        $discountSignupDiscountAmount = floatval((floatval($discountAmount) / 100) * $signupFee);
                        $signupFee -= $discountSignupDiscountAmount;
                    }
                }
            }

            if($hasFixedDiscounts && $fixedMaxTotal > 0) {
                if($fixedMaxTotal >= $subInitialTotal) {
                    $recurringAmount = 0;
                    $signupFee = 0;
                } else {
                    $recurringAmount = $recurringAmount - ($fixedMaxTotal / $subInitialTotal) * $recurringAmount;
                    if($signupFee > 0) {
                        $signupFee = $signupFee - ($fixedMaxTotal / $subInitialTotal) * $signupFee;
                    }
                }
            }

            $subscriptionItems[$subIndex]['recurring_amount'] = intval($recurringAmount);
            $subscriptionItems[$subIndex]['initial_amount'] = intval($signupFee);

            $originalPlan = Helper::safeUnserialize($subscriptionItem['original_plan']);

            $originalPlan['subscription_amount'] = round($recurringAmount / 100, 2);
            $originalPlan['signup_fee'] = round($recurringAmount / 100, 2);
            $subscriptionItems[$subIndex]['original_plan'] = maybe_serialize($originalPlan);
        }

        $this->subscriptionItems = $subscriptionItems;
        return true;
    }
}
