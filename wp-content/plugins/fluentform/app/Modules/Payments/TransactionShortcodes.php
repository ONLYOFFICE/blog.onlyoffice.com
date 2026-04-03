<?php

namespace FluentForm\App\Modules\Payments   ;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\Subscription;
use FluentForm\App\Models\Transaction;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Payments\Classes\PaymentReceipt;
use FluentForm\App\Modules\Payments\Orders\OrderData;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class TransactionShortcodes
{
    public function init()
    {
        add_shortcode('fluentform_payments', array($this, 'registerPaymentsShortcode'));
        add_shortcode('fluentform_payment_view', array($this, 'registerReceiptShortcode'));
        add_action('wp_ajax_fluentform_user_payment_endpoints', array($this, 'routeAjaxEndpoints'));
        add_action('fluentform/payment_view_payment', array($this, 'renderPaymentReceiptPage'));
    }

    public function registerPaymentsShortcode($atts, $content = '')
    {
        $userId = get_current_user_id();
        if (!$userId) {
            if (!$content) {
                return '';
            }
            return '<div class="ff_not_login">' . $content . '</div>';
        }

        $atts = shortcode_atts([
            'type'                  => 'all',
            'payment_statuses'      => '',
            'subscription_statuses' => '',
        ], $atts);

        if ($atts['payment_statuses']) {
            $pay_statuses_array = array_filter(explode(',', $atts['payment_statuses']));
            $atts['payment_statuses'] = $pay_statuses_array;
        }

        if ($atts['subscription_statuses']) {
            $sub_statuses_array = array_filter(explode(',', $atts['subscription_statuses']));
            $atts['subscription_statuses'] = $sub_statuses_array;
        }

        $viewConfig = $this->getViewConfig();

        $html = '';

        if ($atts['type'] == 'all' || $atts['type'] == 'subscriptions') {
            $subscriptionsHtml = $this->getSubscriptionsHtml($userId, $viewConfig, $atts);
            if ($subscriptionsHtml) {
                wp_enqueue_script('fluentform_transactions', fluentFormMix('js/fluentform_transactions_ui.js'), ['jquery'], FLUENTFORM_VERSION, true);

                wp_enqueue_style(
                    'fluentform_transactions',
                    fluentFormMix('css/fluentform_transactions.css'),
                    [],
                    FLUENTFORM_VERSION
                );

                wp_localize_script('fluentform_transactions', 'ff_transactions_vars', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce'    => wp_create_nonce('fluentform_transactions')
                ]);

                if ($atts['type'] == 'subscriptions') {
                    $html .= $subscriptionsHtml;
                    return $html;
                } else {
                    $withTitleHtml = '<div class="ff_transactions_wrapper">';
                    if (!empty($viewConfig['transactions_title'])) {
                        $withTitleHtml .= '<h3>' . $viewConfig['subscriptions_title'] . '</h3>';
                    }
                    $html .= $withTitleHtml . $subscriptionsHtml . '</div>';
                }
            } else if ($atts['type'] == 'subscriptions') {
                return '<p class="ff_no_sub">' . __('No subscription payments found', 'fluentform') . '</p>';
            }
        }

        $html .= $this->getTransactionsHtml($userId, $viewConfig, $atts);

        return $html;
    }

    public function registerReceiptShortcode($atts, $content = '')
    {
       // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is a public shortcode for displaying receipt
       $data = [
           'transaction' => isset($_REQUEST['transaction']) ? sanitize_text_field(wp_unslash($_REQUEST['transaction'])) : '',
       ];
       return $this->renderPaymentReceiptPage($data, false);
    }

    public function renderPaymentReceiptPage($data, $echo = true)
    {
        $transactionHash = ArrayHelper::get($data, 'transaction');

        $transaction = fluentFormApi('submissions')->transaction($transactionHash, 'transaction_hash');

        if(!$transaction) {
            if($echo) {
                status_header(200);
                echo esc_html__('Sorry no transaction found', 'fluentform');
                exit(200);
            }
            return '';
        }

        $submission = fluentFormApi('submissions')->find($transaction->submission_id);

        if ($transaction->transaction_type == 'subscription') {
            $transaction->subscription = fluentFormApi('submissions')->getSubscription($transaction->subscription_id);
        }

        $transaction->form = fluentFormApi('forms')->find($transaction->form_id);
        $transaction->submission = $submission;

        $transactionHtml = $this->getTransactionHtml($transaction);

        if(!$echo) {
            return $transactionHtml;
        }

        add_action('wp_enqueue_scripts', function () {
            wp_enqueue_style('fluent-form-landing', fluentFormMix('css/frameless.css'), [], FLUENTFORM_VERSION);
        });

        $form = fluentFormApi('forms')->find($transaction->form_id);

        $data = [
            'form' => $form,
            'status' => $transaction->status,
            'title' => 'Transaction #'.$transaction->id,
            'message' => $transactionHtml
        ];

        add_filter('pre_get_document_title', function ($title) use ($data) {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- document_title_separator is a WordPress core hook
            return $data['title'] . ' ' . apply_filters('document_title_separator', '-') . ' ' . $data['form']->title;
        });

        status_header(200);
        $file = FLUENTFORM_DIR_PATH . 'app/Views/frameless/frameless_page_view.php';
        extract($data);
        ob_start();
        include($file);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Complete HTML template output with pre-sanitized content from view file
        echo ob_get_clean();
        exit(200);
    }

    public function routeAjaxEndpoints()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified by verifyNonce() method below
        $route = sanitize_text_field(ArrayHelper::get($_REQUEST, 'route'));
        $this->verifyNonce();
        if ($route == 'get_subscription_transactions') {
            $this->sendSubscriptionPayments();
        } else if ($route == 'cancel_transaction') {
            $this->cancelSubscriptionAjax();
        }
    }

    public function getSubscriptionsHtml($userId, $viewConfig = [], $atts = [])
    {
        if (!$viewConfig) {
            $viewConfig = $this->getViewConfig();
        }

        $subscriptions = fluentFormApi('submissions')->subscriptionsByUserId($userId, [
            'form_title' => true,
            'statuses'   => ArrayHelper::get($atts, 'subscription_statuses', [])
        ]);

        if (!count($subscriptions)) {
            return '';
        }

        foreach ($subscriptions as $subscription) {
            $subscription->formatted_recurring_amount = PaymentHelper::formatMoney($subscription->recurring_amount, $subscription->currency);
            $billingText = '';

            if ($subscription->status == 'active') {
                if ($subscription->bill_times) {
                    /* translators: 1: number of payments */
                    $billingText = sprintf(esc_html__('Will be cancelled after %d payments', 'fluentform'), $subscription->bill_times);
                } else {
                    $billingText = __('will be billed until cancelled', 'fluentform');
                }
            }

            $subscription->billing_text = $billingText;
            $subscription->starting_date_formated = date_i18n($viewConfig['date_format'], strtotime($subscription->created_at));
            $subscription->can_cancel = $this->canCancelSubscription($subscription);
            $subscription->status = ArrayHelper::get(PaymentHelper::getSubscriptionStatuses(), $subscription->status, $subscription->status);
            $subscription->billing_interval =  ArrayHelper::get(PaymentHelper::getBillingIntervals(), $subscription->billing_interval, $subscription->billing_interval);
        }

        return PaymentHelper::loadView('user_subscriptions_table', [
            'subscriptions' => $subscriptions,
            'config'        => $viewConfig
        ]);
    }

    public function getTransactionsHtml($userId, $viewConfig = [], $atts = [])
    {
        if (!$viewConfig) {
            $viewConfig = $this->getViewConfig();
        }

        $transactions = fluentFormApi('submissions')->transactionsByUserId($userId, [
            'transaction_types' => ['onetime'],
            'statuses'          => ArrayHelper::get($atts, 'payment_statuses', [])
        ]);

        if (!count($transactions)) {
            return '';
        }


        foreach ($transactions as $transaction) {
            $transaction->formatted_amount = PaymentHelper::formatMoney($transaction->payment_total, $transaction->currency);
            $transaction->formatted_date = date_i18n($viewConfig['date_time_format'], strtotime($transaction->created_at));

            if (!$transaction->transaction_hash) {
                $hash = md5(wp_generate_uuid4() . wp_rand(0, 1000));
                Transaction::where('id', $transaction->id)
                    ->update(['transaction_hash' => $hash]);
                $transaction->transaction_hash = $hash;
            }

            $transaction->view_url = $viewConfig['base_url'] . 'transaction=' . $transaction->transaction_hash . '&payment_method=' . $transaction->payment_method;
            $transaction->status = ArrayHelper::get(PaymentHelper::getPaymentStatuses(), $transaction->status, $transaction->status);

        }

        $transactionsHtml = PaymentHelper::loadView('transactions_table', [
            'transactions' => $transactions,
            'config'       => $viewConfig
        ]);

        $html = '<div class="ff_transactions_wrapper">';
        if (!empty($viewConfig['transactions_title'])) {
            $html .= '<h3>' . $viewConfig['transactions_title'] . '</h3>';
        }

        return $html . $transactionsHtml . '</div>';
    }

    public function sendSubscriptionPayments()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified by verifyNonce() in routeAjaxEndpoints()
        $subscriptionId = intval(ArrayHelper::get($_REQUEST, 'subscription_id'));

        if (!$subscriptionId) {
            wp_send_json_error([
                'message' => __('Invalid Subscription ID', 'fluentform'),
            ], 423);
        }

        $userId = get_current_user_id();

        $subscription = Subscription::select(['fluentform_subscriptions.*', 'fluentform_submissions.user_id'])
            ->where('fluentform_submissions.user_id', $userId)
            ->where('fluentform_subscriptions.id', $subscriptionId)
            ->join('fluentform_submissions', 'fluentform_submissions.id', '=', 'fluentform_subscriptions.submission_id')
            ->first();

        if (!$subscription) {
            wp_send_json_error([
                'message' => __('Invalid Subscription ID', 'fluentform'),
            ], 423);
        }

        $transactions = fluentFormApi('submissions')->transactionsBySubscriptionId($subscription->id);

        if (!count($transactions)) {
            wp_send_json_error([
                'message' => __('Sorry, no related payments found', 'fluentform'),
            ], 423);
        }

        $viewConfig = $this->getViewConfig();

        foreach ($transactions as $transaction) {
            if (!$transaction->transaction_hash) {
                $hash = md5(wp_generate_uuid4() . wp_rand(0, 1000));
                Transaction::where('id', $transaction->id)
                    ->update(['transaction_hash' => $hash]);
                $transaction->transaction_hash = $hash;
            }

            $transaction->formatted_amount = PaymentHelper::formatMoney($transaction->payment_total, $transaction->currency);
            $transaction->formatted_date = date_i18n($viewConfig['date_time_format'], strtotime($transaction->created_at));
            $transaction->view_url = false; //$viewConfig['base_url'] . 'transaction=' . $transaction->transaction_hash . '&payment_method=' . $transaction->payment_method;
            $transaction->status = ArrayHelper::get(PaymentHelper::getPaymentStatuses(), $transaction->status, $transaction->status);

        }

        $viewConfig['has_view_action'] = false;

        $html = PaymentHelper::loadView('user_subscription_payments', [
            'transactions' => $transactions,
            'config'       => $viewConfig
        ]);

        $html = '<h4>' . __('Related Payments', 'fluentform') . '</h4>' . $html;

        wp_send_json_success([
            'html' => $html
        ]);

    }

    private function getViewConfig()
    {
        $paymentSettings = PaymentHelper::getPaymentSettings();
        $pageId = ArrayHelper::get($paymentSettings, 'receipt_page_id');

        $urlBase = false;
        if($pageId) {
            $urlBase = get_permalink($pageId);
        }

        if(!$urlBase) {
            $urlBase = add_query_arg([
                'fluentform_payment' => 'view',
                'route'              => 'payment'
            ], Helper::getFrontendFacingUrl('index.php'));
        }
    
        $urlBase = apply_filters_deprecated(
            'fluentform_transaction_view_url',
            [
                $urlBase
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/transaction_view_url',
            'Use fluentform/transaction_view_url instead of fluentform_transaction_view_url.'
        );

        $urlBase = apply_filters('fluentform/transaction_view_url', $urlBase);

        if (!strpos($urlBase, '?')) {
            $urlBase .= '?';
        } else {
            $urlBase .= '&';
        }

        $wpDateTimeFormat = get_option('date_format') . ' ' . get_option('time_format');

        $config = [
            'new_tab'                    => false,
            'view_text'                  => __('View', 'fluentform'),
            'base_url'                   => $urlBase,
            'date_format'                => get_option('date_format'),
            'date_time_format'           => $wpDateTimeFormat,
            'transactions_title'         => __('Payments', 'fluentform'),
            'subscriptions_title'        => __('Subscriptions', 'fluentform'),
            'sub_cancel_confirm_heading' => __('Are you sure you want to cancel this subscription?', 'fluentform'),
            'sub_cancel_confirm_btn'     => __('Yes, cancel this subscription', 'fluentform'),
            'sub_cancel_close'           => __('Close', 'fluentform')
        ];

        $config = apply_filters_deprecated(
            'fluentform_payment_view_config',
            [
                $config
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_view_config',
            'Use fluentform/payment_view_config instead of fluentform_payment_view_config.'
        );

        return apply_filters('fluentform/payment_view_config', $config);

    }

    private function verifyNonce()
    {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce verification happens immediately below
        $nonce = isset($_REQUEST['_nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['_nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'fluentform_transactions')) {
            wp_send_json_error([
                'message' => __('Security validation failed. Please try again', 'fluentform')
            ], 423);
        }
    }

    private function canCancelSubscription($subscription)
    {
        $validStatuses = [
            'active',
            'trialling',
            'failing'
        ];

        if (!in_array($subscription->status, $validStatuses)) {
            return false;
        }

        $paymentSettings = PaymentHelper::getPaymentSettings();
        if ($paymentSettings['user_can_manage_subscription'] != 'yes') {
            return false;
        }

        $submission = fluentFormApi('submissions')->find($subscription->submission_id);

        if (!$submission) {
            return false;
        }

        $method = $submission->payment_method;

        $hasCancel = apply_filters_deprecated(
            'fluentform_pay_method_has_sub_cancel_' . $method,
            [
                false
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/pay_method_has_sub_cancel_' . $method,
            'Use fluentform/pay_method_has_sub_cancel_' . $method . ' instead of fluentform_pay_method_has_sub_cancel_' . $method
        );

        return $method == 'stripe' || apply_filters('fluentform/pay_method_has_sub_cancel_' . $method, $hasCancel);
    }

    public function cancelSubscriptionAjax()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified by verifyNonce() in routeAjaxEndpoints()
        $subscriptionId = ArrayHelper::get($_REQUEST, 'subscription_id');

        if (!$subscriptionId) {
            $this->sendError(__('Invalid Request', 'fluentform'));
        }

        $subscription = fluentFormApi('submissions')->getSubscription($subscriptionId);

        if (!$subscription) {
            $this->sendError(__('No subscription found', 'fluentform'));
        }

        // validate the subscription
        $userid = get_current_user_id();
        $submission = fluentFormApi('submissions')->find($subscription->submission_id);

        if (!$submission || ($submission->user_id != $userid && !$this->canCancelSubscription($subscription))) {
            $this->sendError(__('Sorry, you can not cancel this subscription at this moment', 'fluentform'));
        }
    
        $handler = apply_filters_deprecated(
            'fluentform_payment_manager_class_' . $submission->payment_method,
            [
                false
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_manager_class_' . $submission->payment_method,
            'Use fluentform/payment_manager_class_' . $submission->payment_method . ' instead of fluentform_payment_manager_class_' . $submission->payment_method
        );

        // Now let's try to cancel this subscription
        $handler = apply_filters('fluentform/payment_manager_class_' . $submission->payment_method, $handler);

        if (!$handler || !method_exists($handler, 'cancelSubscription')) {
            $this->sendError(__('Sorry, you can not cancel this subscription at this moment', 'fluentform'));
        }

        $response = $handler->cancelSubscription($subscription, 'user', $submission);

        if (is_wp_error($response)) {
            $this->sendError($response->get_error_code() . ' - ' . $response->get_error_message());
        }

        wp_send_json_success([
            'message' => __('Your subscription has been cancelled. Refreshing the page...', 'fluentform')
        ], 200);

    }

    private function sendError($message)
    {
        wp_send_json_error([
            'message' => $message
        ], 423);
    }

    public function getTransactionHtml($transaction, $withHeader = false)
    {
        $orderItems = [];
        $discountItems = [];

        $transactionTotal = $subTotal = $orderTotal = PaymentHelper::formatMoney($transaction->payment_total, $transaction->currency);

        if ($transaction->transaction_type == 'subscription') {
            $total = PaymentHelper::formatMoney($transaction->payment_total, $transaction->currency);
            $orderItems[] = (object) [
                'formatted_item_price' => $total,
                'formatted_line_total' => $total,
                'item_name' => $transaction->subscription->plan_name.' ('.$transaction->subscription->item_name.')',
                'quantity' => 1
            ];
        } else {
            $receiptClass = new PaymentReceipt($transaction->submission);
            $orderItems = $receiptClass->getOrderItems();
            $discountItems = $receiptClass->getDiscountItems();
            $subTotal = OrderData::calculateOrderItemsTotal($orderItems, true, $transaction->currency);
            $orderTotal = OrderData::calculateOrderItemsTotal($orderItems, true, $transaction->currency, $discountItems);
        }

        return PaymentHelper::loadView('transaction_details', [
            'transaction'      => $transaction,
            'transactionTotal' => $transactionTotal,
            'discountItems'    => $discountItems,
            'items'            => $orderItems,
            'subTotal'         => $subTotal,
            'orderTotal'       => $orderTotal,
        ]);
    }
}
