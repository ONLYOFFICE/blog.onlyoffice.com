<?php

namespace FluentForm\App\Modules\Payments\PaymentMethods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\Form;
use FluentForm\App\Models\OrderItem;
use FluentForm\App\Models\Submission;
use FluentForm\App\Models\Subscription;
use FluentForm\App\Models\SubmissionMeta;
use FluentForm\App\Models\Transaction;
use FluentForm\App\Modules\Payments\PaymentHelper;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Services\Form\SubmissionHandlerService;

abstract class BaseProcessor
{
    protected $method;

    protected $form = null;

    protected $submission = null;

    protected $submissionId = null;

    public function init()
    {
        add_action('fluentform/process_payment_' . $this->method, array($this, 'handlePaymentAction'), 10, 6);
    }

    public abstract function handlePaymentAction($submissionId, $submissionData, $form, $methodSettings, $hasSubscriptions, $totalPayable);

    public function setSubmissionId($submissionId)
    {
        $this->submissionId = $submissionId;
    }

    public function getSubmissionId()
    {
        return $this->submissionId;
    }

    public function insertTransaction($data)
    {
        if (empty($data['transaction_type'])) {
            $data['transaction_type'] = 'onetime';
        }

        $data = wp_parse_args($data, $this->getTransactionDefaults());

        if (empty($data['transaction_hash'])) {
            $data['transaction_hash'] = md5($data['transaction_type'] . '_payment_' . $data['submission_id'] . '-' . $data['form_id'] . '_' . $data['created_at'] . '-' . time() . '-' . wp_rand(100, 999));
        }

        return Transaction::create($data)->id;
    }

    public function insertRefund($data)
    {
        $submission = $this->getSubmission();
        $data['created_at'] = current_time('mysql');
        $data['updated_at'] = current_time('mysql');
        $data['form_id'] = $submission->form_id;
        $data['submission_id'] = $submission->id;
        $data['payment_method'] = $this->method;
        if (empty($data['transaction_type'])) {
            $data['transaction_type'] = 'refund';
        }

        if ($userId = get_current_user_id()) {
            $data['user_id'] = $userId;
        }

        if (empty($data['transaction_hash'])) {
            $data['transaction_hash'] = md5($data['transaction_type'] . '_payment_' . $data['submission_id'] . '-' . $data['form_id'] . '_' . $data['created_at'] . '-' . time() . '-' . wp_rand(100, 999));
        }

        return Transaction::create($data)->id;
    }

    public function getTransaction($transactionId, $column = 'id')
    {
        return Transaction::where($column, $transactionId)->first();
    }

    public function getRefund($refundId, $column = 'id')
    {
        return Transaction::where($column, $refundId)
            ->refunds()
            ->first();
    }

    public function getTransactionByChargeId($chargeId)
    {
        return Transaction::bySubmission($this->submissionId)
            ->where('charge_id', $chargeId)
            ->first();
    }

    public function getLastTransaction($submissionId)
    {
        return Transaction::bySubmission($submissionId)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function changeSubmissionPaymentStatus($newStatus)
    {
        do_action_deprecated(
            'fluentform_before_payment_status_change',
            [
                $newStatus,
                $this->getSubmission()
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/before_payment_status_change',
            'Use fluentform/before_payment_status_change instead of fluentform_before_payment_status_change.'
        );

        do_action('fluentform/before_payment_status_change', $newStatus, $this->getSubmission());

        Submission::where('id', $this->submissionId)
            ->update([
                'payment_status' => $newStatus,
                'updated_at'     => current_time('mysql')
            ]);

        $this->submission = null;

        $logData = [
            'parent_source_id' => $this->getForm()->id,
            'source_type'      => 'submission_item',
            'source_id'        => $this->submissionId,
            'component'        => 'Payment',
            'status'           => 'paid' === $newStatus ? 'success' : $newStatus,
            'title'            => __('Payment Status changed', 'fluentform'),
            'description'      => __('Payment status changed to ', 'fluentform') . $newStatus
        ];

        do_action('fluentform/log_data', $logData);

        do_action_deprecated(
            'fluentform_after_payment_status_change',
            [
                $newStatus,
                $this->getSubmission()
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/after_payment_status_change',
            'Use fluentform/after_payment_status_change instead of fluentform_after_payment_status_change.'
        );

        do_action('fluentform/after_payment_status_change', $newStatus, $this->getSubmission());

        return true;
    }

    public function recalculatePaidTotal()
    {
        $transactions = Transaction::bySubmission($this->submissionId)
            ->paid()
            ->get();

        $total = 0;
        $subscriptionId = false;
        $subBillCount = 0;

        foreach ($transactions as $transaction) {
            $total += $transaction->payment_total;
            if($transaction->subscription_id) {
                $subscriptionId = $transaction->subscription_id;
                $subBillCount += 1;
            }
        }

        $refunds = $this->getRefundTotal();
        if ($refunds) {
            $total = $total - $refunds;
        }

        Submission::where('id', $this->submissionId)
            ->update([
                'total_paid' => $total,
                'updated_at' => current_time('mysql')
            ]);

        if($subscriptionId && $subBillCount) {
            $this->updateSubscription($subscriptionId, [
                'bill_count' => $subBillCount
            ]);
        }
    }

    public function getRefundTotal()
    {
        $refunds = Transaction::bySubmission($this->submissionId)
            ->refunds()
            ->get();

        $total = 0;
        foreach ($refunds as $refund) {
            $total += $refund->payment_total;
        }

        return $total;
    }

    public function changeTransactionStatus($transactionId, $newStatus)
    {
        do_action_deprecated(
            'fluentform_before_transaction_status_change',
            [
                $newStatus,
                $this->getSubmission(),
                $transactionId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/before_transaction_status_change',
            'Use fluentform/before_transaction_status_change instead of fluentform_before_transaction_status_change.'
        );

        do_action(
            'fluentform/before_transaction_status_change',
            $newStatus,
            $this->getSubmission(),
            $transactionId
        );

        Transaction::where('id', $transactionId)
            ->update([
                'status'     => $newStatus,
                'updated_at' => current_time('mysql')
            ]);

        do_action_deprecated(
            'fluentform_after_transaction_status_change',
            [
                $newStatus,
                $this->getSubmission(),
                $transactionId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/after_transaction_status_change',
            'Use fluentform/after_transaction_status_change instead of fluentform_after_transaction_status_change.'
        );

        do_action(
            'fluentform/after_transaction_status_change',
            $newStatus,
            $this->getSubmission(),
            $transactionId
        );

        return true;
    }

    public function updateTransaction($transactionId, $data)
    {
        $data['updated_at'] = current_time('mysql');

        return Transaction::where('id', $transactionId)->update($data);
    }

    public function completePaymentSubmission($isAjax = true)
    {
        $returnData = $this->getReturnData();
        if ($isAjax) {
            wp_send_json_success($returnData, 200);
        }
        return $returnData;
    }

    public function getReturnData()
    {
        $submission = $this->getSubmission();
        try {
            $submissionService = new SubmissionHandlerService();
            if ($this->getMetaData('is_form_action_fired') == 'yes') {
                $data = $submissionService->getReturnData($submission->id, $this->getForm(),
                    $submission->response);

                $returnData = [
                    'insert_id' => $submission->id,
                    'result'    => $data,
                    'error'     => ''
                ];
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is checking for payment gateway webhook notification
                if (!isset($_REQUEST['fluentform_payment_api_notify'])) {
                    // now we have to check if we need this user as auto login
                    if ($loginId = $this->getMetaData('_make_auto_login')) {
                        $this->maybeAutoLogin($loginId, $submission);
                    }
                }
            } else {
                $returnData = $submissionService->processSubmissionData(
                    $this->submissionId, $submission->response, $this->getForm()
                );
                $this->setMetaData('is_form_action_fired', 'yes');
            }
            return $returnData;

        } catch (\Exception $e) {
            return [
                'insert_id' => $submission->id,
                'result'    => '',
                'error'     => $e->getMessage(),
            ];
        }

    }

    public function getSubmission()
    {
        if (!is_null($this->submission)) {
            return $this->submission;
        }

        $submission = Submission::where('id', $this->submissionId)->first();

        if (!$submission) {
            return false;
        }

        $submission->response = json_decode($submission->response, true);

        $this->submission = $submission;

        return $this->submission;

    }

    public function getForm()
    {
        if (!is_null($this->form)) {
            return $this->form;
        }

        $submission = $this->getSubmission();

        $this->form = Form::where('id', $submission->form_id)->first();

        return $this->form;
    }

    public function getOrderItems()
    {
        return OrderItem::bySubmission($this->submissionId)
            ->products()
            ->get();
    }

    public function getDiscountItems()
    {
        return OrderItem::bySubmission($this->submissionId)
            ->discounts()
            ->get();
    }

    public function setMetaData($name, $value)
    {
        $value = maybe_serialize($value);

        return SubmissionMeta::create([
            'response_id' => $this->getSubmissionId(),
            'form_id'     => $this->getForm()->id,
            'meta_key'    => $name,
            'value'       => $value,
            'created_at'  => current_time('mysql'),
            'updated_at'  => current_time('mysql')
        ])->id;
    }

    public function deleteMetaData($name)
    {
        return SubmissionMeta::where('meta_key', $name)
            ->where('response_id', $this->getSubmissionId())
            ->delete();
    }

    public function getMetaData($metaKey)
    {
        $meta = SubmissionMeta::where('response_id', $this->getSubmissionId())
            ->where('meta_key', $metaKey)
            ->first();

        if ($meta && $meta->value) {
            return Helper::safeUnserialize($meta->value);
        }

        return false;
    }

    public function showPaymentView($returnData)
    {
        $redirectUrl = ArrayHelper::get($returnData, 'result.redirectUrl');
        if ($redirectUrl) {
            wp_redirect($redirectUrl);
            exit();
        }

        $form = $this->getForm();

        if (!empty($returnData['title'])) {
            $title = $returnData['title'];
        } else if ($returnData['type'] == 'success') {
            $title = __('Payment Success', 'fluentform');
            $title = apply_filters('fluentform/payment_success_title', $title, $this->getSubmission(), $form);
        } else {
            $title = __('Payment Failed', 'fluentform');
            $title = apply_filters('fluentform/payment_failed_title', $title, $this->getSubmission(), $form);
        }

        $message = $returnData['error'];
        if (!$message) {
            $message = $returnData['result']['message'];
        }

        // Apply payment message filter
        if ($message) {
            $message = apply_filters('fluentform/payment_confirmation_message', $message, $this->getSubmission(), $form);
        }

        $data = [
            'status'     => $returnData['type'],
            'form'       => $form,
            'title'      => $title,
            'submission' => $this->getSubmission(),
            'message'    => $message,
            'is_new'     => $returnData['is_new'],
            'data'       => $returnData
        ];

        $data = apply_filters_deprecated(
            'fluentform_frameless_page_data',
            [
                $data
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/frameless_page_data',
            'Use fluentform/frameless_page_data instead of fluentform_frameless_page_data.'
        );

        $data = apply_filters('fluentform/frameless_page_data', $data);

        add_filter('pre_get_document_title', function ($title) use ($data) {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- document_title_separator is a WordPress core hook
            return $data['title'] . ' ' . apply_filters('document_title_separator', '-') . ' ' . $data['form']->title;
        });

        add_action('wp_enqueue_scripts', function () {
            wp_enqueue_style('fluent-form-landing', fluentFormMix('css/frameless.css'), [], FLUENTFORM_VERSION);
        });

        status_header(200);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- loadView() returns complete HTML template with pre-sanitized content
        echo $this->loadView('frameless/frameless_page_view', $data);
        exit(200);
    }

    public function loadView($view, $data = [])
    {
        $file = FLUENTFORM_DIR_PATH . 'app/Views/' . $view . '.php';
        extract($data);
        ob_start();
        include($file);
        return ob_get_clean();
    }

    public function refund($refund_amount, $transaction, $submission, $method = '', $refundId = '', $refundNote = 'Refunded')
    {
        $this->setSubmissionId($submission->id);
        $status = 'refunded';

        $alreadyRefunded = $this->getRefundTotal();
        $totalRefund = intval($refund_amount + $alreadyRefunded);

        if ($totalRefund < $transaction->payment_total) {
            $status = 'partially-refunded';
        }

        $this->changeTransactionStatus($transaction->id, $status);
        $this->changeSubmissionPaymentStatus($status);
        $uniqueHash = md5('refund_' . $submission->id . '-' . $submission->form_id . '-' . time() . '-' . wp_rand(100, 999));

        $refundData = [
            'form_id'          => $submission->form_id,
            'submission_id'    => $submission->id,
            'transaction_hash' => $uniqueHash,
            'payment_method'   => $transaction->payment_method,
            'charge_id'        => $refundId,
            'payment_note'     => $refundNote,
            'payment_total'    => $refund_amount,
            'currency'         => $transaction->currency,
            'payment_mode'     => $transaction->payment_mode,
            'created_at'       => current_time('mysql'),
            'updated_at'       => current_time('mysql'),
            'status'           => 'refunded',
            'transaction_type' => 'refund'
        ];

        $refundId = $this->insertRefund($refundData);

        $logData = [
            'parent_source_id' => $submission->form_id,
            'source_type'      => 'submission_item',
            'source_id'        => $submission->id,
            'component'        => 'Payment',
            'status'           => 'info',
            'title'            => __('Refund issued', 'fluentform'),
            'description'      => __('Refund issued and refund amount: ', 'fluentform') . number_format($refund_amount / 100, 2)
        ];

        do_action('fluentform/log_data', $logData);

        $this->recalculatePaidTotal();
        $refund = $this->getRefund($refundId);

        do_action_deprecated(
            'fluentform_payment_' . $status . '_' . $method,
            [
                $refund,
                $transaction,
                $submission
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_' . $status . '_' . $method,
            'Use fluentform/payment_' . $status . '_' . $method . ' instead of fluentform_payment_' . $status . '_' . $method
        );

        do_action('fluentform/payment_' . $status . '_' . $method, $refund, $transaction, $submission);

        do_action_deprecated(
            'fluentform_payment_' . $status,
            [
                $refund,
                $transaction,
                $submission
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_' . $status,
            'Use fluentform/payment_' . $status . ' instead of fluentform_payment_' . $status
        );
        do_action('fluentform/payment_' . $status, $refund, $transaction, $submission);
    }

    public function updateRefund($totalRefund, $transaction, $submission, $method = '', $refundId = '', $refundNote = 'Refunded')
    {
        if(!$totalRefund) {
            return;
        }

        $this->setSubmissionId($submission->id);
        $existingRefund = Transaction::bySubmission($submission->id)
            ->refunds()
            ->first();

        if ($existingRefund) {

            if ($existingRefund->payment_total == $totalRefund) {
                return;
            }

            $status = 'refunded';
            if ($totalRefund < $transaction->payment_total) {
                $status = 'partially-refunded';
            }
            $updateData = [
                'form_id'          => $submission->form_id,
                'submission_id'    => $submission->id,
                'payment_method'   => $transaction->payment_method,
                'charge_id'        => $refundId,
                'payment_note'     => $refundNote,
                'payment_total'    => $totalRefund,
                'payment_mode'     => $transaction->payment_mode,
                'created_at'       => current_time('mysql'),
                'updated_at'       => current_time('mysql'),
                'status'           => 'refunded',
                'transaction_type' => 'refund'
            ];

            Transaction::where('id', $existingRefund->id)->update($updateData);

            $existingRefund = Transaction::bySubmission($submission->id)
                ->refunds()
                ->first();

            if ($transaction->status != $status) {
                $this->changeTransactionStatus($transaction->id, $status);
            }

            do_action_deprecated(
                'fluentform_payment_refund_updated_' . $method,
                [
                    $existingRefund,
                    $existingRefund->form_id
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/payment_refund_updated_' . $method,
                'Use fluentform/payment_refund_updated_' . $method . ' instead of fluentform_payment_refund_updated_' . $method
            );

            do_action('fluentform/payment_refund_updated_' . $method, $existingRefund, $existingRefund->form_id);

            do_action_deprecated(
                'fluentform_payment_refund_updated',
                [
                    $existingRefund,
                    $existingRefund->form_id
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/payment_refund_updated',
                'Use fluentform/payment_refund_updated instead of fluentform_payment_refund_updated.'
            );

            do_action('fluentform/payment_refund_updated', $existingRefund, $existingRefund->form_id);

        } else {
            $this->refund($totalRefund, $transaction, $submission, $method, $refundId, $refundNote);
        }
    }

    private function maybeAutoLogin($loginId, $submission)
    {
        if (is_user_logged_in() || !$loginId) {
            return;
        }
        if ($loginId != $submission->user_id) {
            return;
        }

        wp_clear_auth_cookie();
        wp_set_current_user($loginId);
        wp_set_auth_cookie($loginId);
        $this->deleteMetaData('_make_auto_login');
    }

    public function getAmountTotal()
    {
        $orderItems = $this->getOrderItems();

        $amountTotal = 0;
        foreach ($orderItems as $item) {
            $amountTotal += $item->line_total;
        }

        $discountItems = $this->getDiscountItems();
        foreach ($discountItems as $discountItem) {
            $amountTotal -= $discountItem->line_total;
        }

        return $amountTotal;
    }

    public function handleSessionRedirectBack($data)
    {
        $submissionId = intval($data['fluentform_payment']);
        $this->setSubmissionId($submissionId);

        $submission = $this->getSubmission();

        $transactionHash = sanitize_text_field($data['transaction_hash']);
        $transaction = $this->getTransaction($transactionHash, 'transaction_hash');

        if (!$transaction || !$submission) {
            return;
        }

        $type = $transaction->status;
        $form = $this->getForm();

        if ($type == 'paid') {
            $returnData = $this->getReturnData();
        } else {
            $pendingTitle = __('Payment was not marked as paid', 'fluentform');
            $pendingMessage = __('Looks like you have is still on pending status', 'fluentform');
            $returnData = [
                'insert_id' => $submission->id,
                'title'     => apply_filters('fluentform/payment_pending_title', $pendingTitle, $submission, $form),
                'result'    => false,
                'error'     => apply_filters('fluentform/payment_pending_message', $pendingMessage, $submission, $form)
            ];
        }

        $returnData['type'] = 'success';
        $returnData['is_new'] = false;

        $this->showPaymentView($returnData);
    }

    public function getSubscriptions($status = false)
    {
        $subscriptions = Subscription::bySubmission($this->submissionId)
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->get();

        foreach ($subscriptions as $subscription) {
            $subscription->original_plan = Helper::safeUnserialize($subscription->original_plan);
            $subscription->vendor_response = Helper::safeUnserialize($subscription->vendor_response);
        }

        return $subscriptions;
    }

    public function updateSubscription($id, $data)
    {
        $data['updated_at'] = current_time('mysql');

        return Subscription::where('id', $id)->update($data);
    }

    public function maybeInsertSubscriptionCharge($item)
    {
        $exists = Transaction::subscriptionType()
            ->where('submission_id', $item['submission_id'])
            ->where('subscription_id', $item['subscription_id'])
            ->where('charge_id', $item['charge_id'])
            ->where('payment_method', $item['payment_method'])
            ->first();

        $isNew = false;

        if ($exists) {
            // We don't want to update the address and payer email that we already have here
            if ($exists->billing_address) {
                unset($item['billing_address']);
            }
            if ($exists->payer_email) {
                unset($item['payer_email']);
            }

            unset($item['transaction_hash']);
            unset($item['created_at']);

            Transaction::where('id', $exists->id)->update($item);

            $id = $exists->id;
        } else {
            if (empty($item['created_at'])) {
                $item['created_at'] = current_time('mysql');
                $item['updated_at'] = current_time('mysql');
            }

            if (empty($item['transaction_hash'])) {
                $uniqueHash = md5('subscription_payment_' . $item['submission_id'] . '-' . $item['charge_id'] . '-' . time() . '-' . wp_rand(100, 999));
                $item['transaction_hash'] = $uniqueHash;
            }

            $id = Transaction::create($item)->id;
            $isNew = true;
        }


        $transaction = fluentFormApi('submissions')->transaction($id);

        // We want to update the total amount here
        $parentSubscription = Subscription::where('id', $transaction->subscription_id)->first();

        // Let's count the total subscription payment
        if ($parentSubscription) {
            list($billCount, $paymentTotal) = $this->getPaymentCountsAndTotal($parentSubscription->id);

            Subscription::where('id', $parentSubscription->id)
                ->update([
                    'bill_count'    => $billCount,
                    'payment_total' => $paymentTotal,
                    'updated_at'    => current_time('mysql')
                ]);

            Submission::where('id', $parentSubscription->submission_id)
                ->update([
                    'payment_total' => $paymentTotal,
                    'total_paid'    => $paymentTotal,
                ]);

            $subscription = Subscription::where('id', $parentSubscription->id)->first();

            if($isNew) {
                $submission = $this->getSubmission();
                do_action_deprecated(
                    'fluentform_subscription_received_payment',
                    [
                        $subscription,
                        $submission
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/subscription_received_payment',
                    'Use fluentform/subscription_received_payment instead of fluentform_subscription_received_payment.'
                );
                do_action('fluentform/subscription_received_payment', $subscription, $submission);

                do_action_deprecated(
                    'fluentform_subscription_received_payment_' . $submission->payment_method,
                    [
                        $subscription,
                        $submission
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/subscription_received_payment_' . $submission->payment_method,
                    'Use fluentform/subscription_received_payment_' . $submission->payment_method . ' instead of fluentform_subscription_received_payment_' . $submission->payment_method
                );
                do_action('fluentform/subscription_received_payment_' . $submission->payment_method, $subscription, $submission);
            }

            if($subscription->bill_times >= $subscription->bill_count) {
                // We have to mark the subscription as completed
                $this->updateSubscriptionStatus($subscription, 'completed');
            }
        }

        return $id;
    }

    public function getPaymentCountsAndTotal($subscriptionId, $paymentMethod = false)
    {
        $payments = Transaction::select(['id', 'payment_total'])
            ->subscriptionType()
            ->where('subscription_id', $subscriptionId)
            ->when($paymentMethod, function ($q) use ($paymentMethod) {
                $q->where('payment_method', $paymentMethod);
            })
            ->get();

        $paymentTotal = 0;

        foreach ($payments as $payment) {
            $paymentTotal += $payment->payment_total;
        }

        return [count($payments), $paymentTotal];
    }

    protected function getCancelAtTimeStamp($subscription)
    {
        if (!$subscription->bill_times) {
            return false;
        }

        $dateTime = current_datetime();
        $localtime = $dateTime->getTimestamp() + $dateTime->getOffset();

        $billingStartDate = $localtime;

        if ($subscription->expiration_at) {
            $billingStartDate = strtotime($subscription->expiration_at);
        }

        $billTimes = $subscription->bill_times;

        $interval = $subscription->billing_interval;

        $interValMaps = [
            'day'   => 'days',
            'week'  => 'weeks',
            'month' => 'months',
            'year'  => 'years'
        ];

        if (isset($interValMaps[$interval]) && $billTimes > 1) {
            $interval = $interValMaps[$interval];
        }

        return strtotime('+ ' . $billTimes . ' ' . $interval, $billingStartDate);
    }

    public function updateSubmission($id, $data)
    {
        $data['updated_at'] = current_time('mysql');

        return Submission::where('id', $id)->update($data);
    }

    public function limitLength($string, $limit = 127)
    {
        $str_limit = $limit - 3;
        if (function_exists('mb_strimwidth')) {
            if (mb_strlen($string) > $limit) {
                $string = mb_strimwidth($string, 0, $str_limit) . '...';
            }
        } else {
            if (strlen($string) > $limit) {
                $string = substr($string, 0, $str_limit) . '...';
            }
        }
        return $string;
    }

    public function getTransactionDefaults()
    {
        $submission = $this->getSubmission();
        if (!$submission) {
            return [];
        }

        $data = [];

        if ($customerEmail = PaymentHelper::getCustomerEmail($submission, $this->getForm())) {
            $data['payer_email'] = $customerEmail;
        }

        if ($customerName = PaymentHelper::getCustomerName($submission, $this->getForm())) {
            $data['payer_name'] = $customerName;
        }

        if ($submission->user_id) {
            $data['user_id'] = $submission->user_id;
        } else if ($user = get_user_by('ID', get_current_user_id())) {
            $data['user_id'] = $user->ID;
        }

        if (!$submission->user_id && !empty($data['payer_email'])) {
            $email = $data['payer_email'];
            $maybeUser = get_user_by('email', $email);
            if ($maybeUser) {

                $this->updateSubmission($submission->id, [
                    'user_id' => $maybeUser->ID
                ]);

                if (empty($data['user_id'])) {
                    $data['user_id'] = $maybeUser->ID;
                }
            }
        }

        $address = PaymentHelper::getCustomerAddress($submission);
        if (!$address) {
            $address = ArrayHelper::get($submission->response, 'address_1');
        }
        if ($address) {
            $address = array_filter($address);
            if ($address) {
                $data['billing_address'] = implode(', ', $address);
            }
        }

        $data['created_at'] = current_time('mysql');
        $data['updated_at'] = current_time('mysql');
        $data['form_id'] = $submission->form_id;
        $data['submission_id'] = $submission->id;
        $data['payment_method'] = $this->method;

        return $data;
    }

    public function createInitialPendingTransaction($submission = false, $hasSubscriptions = false)
    {
        if (!$submission) {
            $submission = $this->getSubmission();
        }

        $form = $this->getForm();

        $uniqueHash = md5($submission->id . '-' . $form->id . '-' . time() . '-' . wp_rand(100, 999));

        $transactionData = [
            'transaction_type' => 'onetime',
            'transaction_hash' => $uniqueHash,
            'payment_total'    => $this->getAmountTotal(),
            'status'           => 'pending',
            'currency'         => strtoupper($submission->currency),
            'payment_mode'     => $this->getPaymentMode()
        ];
        if ($hasSubscriptions) {
            $subscriptions = $this->getSubscriptions();
            if ($subscriptions) {
                $subscriptionInitialTotal = 0;
                foreach ($subscriptions as $subscription) {
                    if (!$subscription->trial_days) {
                        $subscriptionInitialTotal += $subscription->recurring_amount;
                    }
                    if ($subscription->initial_amount) {
                        $subscriptionInitialTotal += $subscription->initial_amount;
                    }
                    $transactionData['subscription_id'] = $subscription->id;
                }
                $transactionData['payment_total'] += $subscriptionInitialTotal;
                $transactionData['transaction_type'] = 'subscription';
            }
        }

        $transactionId = $this->insertTransaction($transactionData);

        $this->updateSubmission($submission->id, [
            'payment_total' => $transactionData['payment_total']
        ]);

        return $this->getTransaction($transactionId);

    }

    /**
     * @param object $subscription
     * @param string $newStatus
     * @param string $note
     * @return object
     */
    public function updateSubscriptionStatus($subscription, $newStatus, $note = '')
    {
        if (!$note) {
            $note = sprintf(
                /* translators: 1: new status, 2: old status */
                __('Subscription status has been changed to %1$s from %2$s', 'fluentform'),
                $newStatus,
                $subscription->status
            );
        }

        $oldStatus = $subscription->status;

        if($oldStatus == $newStatus) {
            return $subscription;
        }

        Subscription::where('id', $subscription->id)
            ->update([
                'status' => $newStatus,
                'updated_at' => current_time('mysql')
            ]);

        $logData = [
            'parent_source_id' => $this->getForm()->id,
            'source_type'      => 'submission_item',
            'source_id'        => $this->submissionId,
            'component'        => 'Payment',
            'status'           => 'info',
            'title'            => __('Subscription Status changed to ', 'fluentform') . $newStatus,
            'description'      => $note
        ];

        do_action('fluentform/log_data', $logData);

        $subscription->status = $newStatus;

        $submission = $this->getSubmission();

        do_action_deprecated(
            'fluentform_subscription_payment_' . $newStatus,
            [
                $subscription,
                $submission,
                false
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/subscription_payment_' . $newStatus,
            'Use fluentform/subscription_payment_' . $newStatus . ' instead of fluentform_subscription_payment_' . $newStatus
        );

        do_action('fluentform/subscription_payment_' . $newStatus, $subscription, $submission, false);

        do_action_deprecated(
            'fluentform_subscription_payment_' . $newStatus . '_' . $submission->payment_method,
            [
                $subscription,
                $submission,
                false
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/subscription_payment_' . $newStatus . '_' . $submission->payment_method,
            'Use fluentform/subscription_payment_' . $newStatus . '_' . $submission->payment_method . ' instead of fluentform_subscription_payment_' . $newStatus . '_' . $submission->payment_method
        );
        do_action('fluentform/subscription_payment_' . $newStatus . '_' . $submission->payment_method, $subscription, $submission, false);

        return $subscription;
    }
}
