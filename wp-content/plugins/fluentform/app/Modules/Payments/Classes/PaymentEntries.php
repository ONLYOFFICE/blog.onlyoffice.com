<?php

namespace FluentForm\App\Modules\Payments\Classes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Models\OrderItem;
use FluentForm\App\Models\Subscription;
use FluentForm\App\Models\Transaction;
use FluentForm\App\Modules\Acl\Acl;
use FluentForm\App\Modules\Payments\PaymentHelper;
use FluentForm\Framework\Helpers\ArrayHelper;

class PaymentEntries
{

    public function init()
    {
        add_action('fluentform/render_payment_entries', array($this, 'loadApp'));
        add_action('wp_ajax_fluentform_get_payments', array($this, 'getPayments'));
        add_action('wp_ajax_fluentform-do_entry_bulk_actions_payment', array($this, 'handleBulkAction'));
        add_action('wp_ajax_fluentform_get_all_payments_entries_filters', array($this, 'getFilters'));

    }

    public function loadApp()
    {
        wp_enqueue_style('ff-payment-entries', fluentFormMix('css/payment_entries.css'), [], FLUENTFORM_VERSION);
        wp_enqueue_script('ff-payment-entries', fluentFormMix('js/payment_entries.js'), ['jquery'], FLUENTFORM_VERSION, true);
        $settingsUrl = admin_url('admin.php?page=fluent_forms_settings#payments/general_settings');
        do_action_deprecated(
            'fluentform_global_menu',
            [
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/global_menu',
            'Use fluentform/global_menu instead of fluentform_global_menu.'
        );
        do_action('fluentform/global_menu');
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $settingsUrl is escaped via esc_url
        echo '<div id="ff_payment_entries"><ff-payment-entries settings_url="'.esc_url($settingsUrl).'"></ff-payment-entries><global-search></global-search></div>';
    }

    public function getPayments()
    {
        $request = wpFluentForm()->request;
        $attributes = $request->all();
        
        // Sanitize request data
        $sanitizeMap = [
            'form_id'          => 'intval',
            'per_page'         => 'intval',
            'payment_statuses' => 'sanitize_text_field',
            'payment_types'    => 'sanitize_text_field',
            'payment_methods'  => 'sanitize_text_field',
        ];
        $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified by Acl::verify() below
        Acl::verify('fluentform_view_payments', ArrayHelper::get($attributes, 'form_id'));
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified by Acl::verify() above
        $perPage = ArrayHelper::get($attributes, 'per_page', 10);
        if(!$perPage) {
            $perPage = 10;
        }
        $paymentsQuery = Transaction::select([
                'fluentform_transactions.id',
                'fluentform_transactions.form_id',
                'fluentform_transactions.submission_id',
                'fluentform_transactions.transaction_type',
                'fluentform_transactions.payment_method',
                'fluentform_transactions.payment_mode',
                'fluentform_transactions.charge_id',
                'fluentform_transactions.card_brand',
                'fluentform_transactions.payment_total',
                'fluentform_transactions.created_at',
                'fluentform_transactions.payer_name',
                'fluentform_transactions.status',
                'fluentform_transactions.currency',
                'fluentform_forms.title'
            ])
            ->join('fluentform_forms', 'fluentform_forms.id', '=', 'fluentform_transactions.form_id')
            ->orderBy('fluentform_transactions.id', 'DESC');

        if ($selectedFormId = ArrayHelper::get($attributes, 'form_id')) {
            $paymentsQuery = $paymentsQuery->where('fluentform_transactions.form_id', $selectedFormId);
        }

        $allowFormIds = apply_filters('fluentform/current_user_allowed_forms', false);
        if ($allowFormIds && is_array($allowFormIds)) {
            $paymentsQuery = $paymentsQuery->whereIn('fluentform_transactions.form_id', $allowFormIds);
        }
        if ($paymentStatus = ArrayHelper::get($attributes, 'payment_statuses')) {
            $paymentsQuery = $paymentsQuery->where('fluentform_transactions.status', $paymentStatus);
        }
        if ($paymentTypes = ArrayHelper::get($attributes, 'payment_types')) {
            $paymentsQuery = $paymentsQuery->where('fluentform_transactions.transaction_type', $paymentTypes);
        }
        if ($paymentMethods = ArrayHelper::get($attributes, 'payment_methods')) {
            $paymentsQuery = $paymentsQuery->where('fluentform_transactions.payment_method', $paymentMethods);
        }
        $paymentsPaginate = $paymentsQuery->paginate($perPage);

        $payments = $paymentsPaginate->items();
        foreach ($payments as $payment) {
            $payment->formatted_payment_total = PaymentHelper::formatMoney($payment->payment_total, $payment->currency);
            $payment->entry_url = admin_url('admin.php?page=fluent_forms&route=entries&form_id='.$payment->form_id.'#/entries/'.$payment->submission_id);
            if($payment->payment_method == 'test') {
                $payment->payment_method = 'offline';
            }
        }
        wp_send_json_success([
            'payments'     => $payments,
            'total'        => $paymentsPaginate->total(),
            'current_page' => $paymentsPaginate->currentPage(),
            'per_page'     => $paymentsPaginate->perPage(),
            'last_page'    => $paymentsPaginate->lastPage()
        ]);

    }
    
    public function handleBulkAction()
    {
        Acl::verify('fluentform_forms_manager');

        $request = wpFluentForm()->request;
        $attributes = $request->all();
        
        $sanitizeMap = [
            'entries' => function($value) {
                if (is_array($value)) {
                    return array_map('intval', $value);
                }
                return [];
            },
            'action_type' => 'sanitize_text_field',
        ];
        $attributes = fluentform_backend_sanitizer($attributes, $sanitizeMap);
        
        $entries = ArrayHelper::get($attributes, 'entries', []);
        $actionType = ArrayHelper::get($attributes, 'action_type');
        if (!$actionType || !count($entries)) {
            wp_send_json_error([
                'message' => __('Please select entries & action first', 'fluentform')
            ], 400);
        }
        
        $message = __("Invalid action", 'fluentform');
        $statusCode = 400;
        // permanently delete payment entries from transactions
        if ($actionType == 'delete_items') {
    
            
            // get submission ids to delete order items
            $transactionData = Transaction::select(['form_id', 'submission_id'])
                ->whereIn('id', $entries)
                ->get();

            $submission_ids = [];

            foreach ($transactionData as $transactionDatum) {
                $submission_ids[] = $transactionDatum->submission_id;
            }

            try {
                if (!$submission_ids || count($transactionData) === 0 ) {
                    throw new \Exception(__('Invalid transaction id', 'fluentform'));
                }
                do_action_deprecated(
                    'fluentform_before_entry_payment_deleted',
                    [
                        $entries,
                        $transactionData
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/before_entry_payment_deleted',
                    'Use fluentform/before_entry_payment_deleted instead of fluentform_before_entry_payment_deleted.'
                );
                do_action('fluentform/before_entry_payment_deleted', $entries, $transactionData);
    
                //delete data from transaction table
                Transaction::whereIn('id', $entries)->delete();

                //delete data from order table
                OrderItem::whereIn('submission_id', $submission_ids)->delete();

                // delete data from subscriptions table
                Subscription::whereIn('submission_id', $submission_ids)->delete();
                
                //add log in each form that payment record has been deleted
                foreach ($transactionData as $data){
                    $logData = [
                        'parent_source_id' => $data->form_id,
                        'source_type'      => 'submission_item',
                        'source_id'        => $data->submission_id,
                        'component'        => 'payment',
                        'status'           => 'info',
                        'title'            => __('Payment data successfully deleted', 'fluentform'),
                        'description'      => __('Payment record cleared from transaction history and order items', 'fluentform'),
                    ];
                    do_action('fluentform/log_data', $logData);
                }
                do_action_deprecated(
                    'fluentform_after_entry_payment_deleted',
                    [
                        $entries,
                        $transactionData
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/after_entry_payment_deleted',
                    'Use fluentform/after_entry_payment_deleted instead of fluentform_after_entry_payment_deleted.'
                );
                do_action('fluentform/after_entry_payment_deleted', $entries, $transactionData);
                $message = __('Selected entries successfully deleted', 'fluentform');
                $statusCode = 200;
        
            } catch (\Exception $exception) {
                $message = $exception->getMessage();
                $statusCode = 400;
            }
        }
        
        wp_send_json_success([
            'message' => $message
        ], $statusCode);
    }

    public function getFilters()
    {
        $transactionTypes = Transaction::select('transaction_type')
            ->groupBy('transaction_type')
            ->get();
        // Define transaction type labels
        $transactionTypeLabels = [
            'onetime'      => __('Charge', 'fluentform'),
            'refund'       => __('Refund', 'fluentform'),
            'subscription' => __('Subscription', 'fluentform'),
            'renewal'      => __('Renewal', 'fluentform')
        ];
        
        $formattedTransactionTypes = [];
        foreach ($transactionTypes as $type) {
            $label = ArrayHelper::get($transactionTypeLabels, $type->transaction_type, ucfirst($type->transaction_type));
            $formattedTransactionTypes[] = [
                'value' => $label,
                'key' => $type->transaction_type
            ];
        }
        
        $statuses = Transaction::select('status')
            ->groupBy('status')
            ->get();
        $statusTypes = PaymentHelper::getPaymentStatuses();
        $formattedStatuses = [];
        foreach ($statuses as $status) {
            $formattedStatuses[] = ArrayHelper::get($statusTypes, $status->status, $status->status);
        }
        $allowFormIds = apply_filters('fluentform/current_user_allowed_forms', false);
        $forms = Transaction::select('fluentform_transactions.form_id', 'fluentform_forms.title')
            ->when($allowFormIds && is_array($allowFormIds), function ($q) use ($allowFormIds){
                return $q->whereIn('fluentform_transactions.form_id', $allowFormIds);
            })
            ->groupBy('fluentform_transactions.form_id')
            ->orderBy('fluentform_transactions.form_id', 'DESC')
            ->join('fluentform_forms', 'fluentform_forms.id', '=', 'fluentform_transactions.form_id')
            ->get();

        $formattedForms = [];
        foreach ($forms as $form) {
            $formattedForms[] = [
                'form_id' => $form->form_id,
                'title'   => $form->title
            ];
        }

        $paymentMethods = Transaction::select('payment_method')
            ->groupBy('payment_method')
            ->get();

        $formattedMethods = [];
        foreach ($paymentMethods as $method) {
            if(!$method->payment_method){
                continue;
            }
            if ($method->payment_method == 'test') {
                $formattedMethods[] = ['value' => __('Offline', 'fluentform'), 'key' => $method->payment_method];
            } else {
                $formattedMethods[] = ['value' => ucfirst($method->payment_method), 'key' => $method->payment_method];
            }
        }
        
        wp_send_json_success([
            'available_payment_types' => $formattedTransactionTypes,
            'available_statuses'      => $formattedStatuses,
            'available_forms'         => $formattedForms,
            'available_methods'       => array_filter($formattedMethods),
        ]);

    }
}
