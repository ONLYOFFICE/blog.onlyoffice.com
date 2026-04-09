<?php

namespace FluentForm\App\Modules\Payments;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Acl\Acl;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Modules\Payments\Orders\OrderData;
use FluentForm\App\Services\FormBuilder\ShortCodeParser;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Payments\Classes\PaymentAction;
use FluentForm\App\Modules\Payments\Classes\PaymentEntries;
use FluentForm\App\Modules\Payments\Classes\PaymentReceipt;
use FluentForm\App\Modules\Payments\Components\CustomPaymentComponent;
use FluentForm\App\Modules\Payments\Components\ItemQuantity;
use FluentForm\App\Modules\Payments\Components\MultiPaymentComponent;
use FluentForm\App\Modules\Payments\Components\PaymentMethods;
use FluentForm\App\Modules\Payments\Components\PaymentSummaryComponent;
use FluentForm\App\Modules\Payments\Components\Subscription;
use FluentForm\App\Modules\Payments\PaymentMethods\Stripe\Components\StripeInline;
use FluentForm\App\Modules\Payments\PaymentMethods\Stripe\ConnectConfig;
use FluentForm\App\Modules\Payments\PaymentMethods\Stripe\StripeHandler;
use FluentForm\App\Modules\Payments\PaymentMethods\Stripe\StripeSettings;

class PaymentHandler
{
    public function init()
    {
        
        add_filter('fluentform/global_settings_components', [$this, 'pushGlobalSettings'], 1, 1);

        add_filter('fluentform/global_settings_component_settings_data', [$this, 'getGlobalSettingsPaymentVars']);

        add_action('wp_ajax_fluentform_handle_payment_ajax_endpoint', [$this, 'handleAjaxEndpoints']);
        
        if (!$this->isEnabled()) {
            return;
        }
        
        add_filter('fluentform/show_payment_entries', '__return_true');
        
        add_filter('fluentform/form_settings_menu', array($this, 'maybeAddPaymentSettings'), 10, 2);
        // Let's load Payment Methods here
        (new StripeHandler())->init();

        // Let's load the payment method component here
        new MultiPaymentComponent();
        new Subscription();
        new CustomPaymentComponent();
        new ItemQuantity();
        new PaymentMethods();
        new PaymentSummaryComponent();

        add_filter('fluentform/editor_components', function ($components) {
            if (!Helper::hasPro()) {
                $components['payments'][] = [
                    'index'          => 6,
                    'element'        => 'payment_coupon',
                    'attributes'     => [],
                    'settings'       => [],
                    'editor_options' => [
                        'title'      => __('Coupon', 'fluentform'),
                        'icon_class' => 'el-icon-postcard',
                        'template'   => 'inputText',
                    ],
                ];
            }
            return $components;
        }, 11);

        new StripeInline();
        
        add_action('fluentform/before_insert_payment_form', array($this, 'maybeHandlePayment'), 10, 3);
        
        add_filter('fluentform/submission_order_data', function ($data, $submission) {
            return OrderData::getSummary($submission, $submission->form);
        }, 10, 2);
        
        add_filter('fluentform/entries_vars', function ($vars, $form) {
            if ($form->has_payment) {
                $vars['has_payment'] = $form->has_payment;
                $vars['currency_config'] = PaymentHelper::getCurrencyConfig($form->id);
                $vars['currency_symbols'] = PaymentHelper::getCurrencySymbols();
                $vars['payment_statuses'] = PaymentHelper::getPaymentStatuses();
            }
            return $vars;
        }, 10, 2);
        
        add_filter(
            'fluentform/submission_labels',
            [$this, 'modifySingleEntryLabels'],
            10,
            3
        );
        
        
        add_filter('fluentform/all_entry_labels_with_payment', array($this, 'modifySingleEntryLabels'), 10, 3);
        
        add_action('fluentform/rendering_payment_form', function ($form) {
            if (Helper::isElementorEditor()) {
                return;
            }

            $src = fluentFormMix('js/payment_handler.js');
            $version = FLUENTFORM_VERSION;

            // If pro is installed and script is compatible, load script from pro
            if (Helper::isProPaymentScriptCompatible()) {
                $src = FLUENTFORMPRO_DIR_URL . 'public/js/payment_handler_pro.js';
                $version = FLUENTFORMPRO_VERSION;
            }

            wp_enqueue_script('fluentform-payment-handler',
                $src,
                array('jquery'),
                $version,
                true
            );
            
            wp_enqueue_style(
                'fluentform-payment-skin',
                fluentFormMix('css/payment_skin.css'),
                array(),
                FLUENTFORM_VERSION
            );
            
            wp_localize_script('fluentform-payment-handler', 'fluentform_payment_config', [
                'i18n' => [
                    'item'                     => __('Item', 'fluentform'),
                    'price'                    => __('Price', 'fluentform'),
                    'qty'                      => __('Qty', 'fluentform'),
                    'line_total'               => __('Line Total', 'fluentform'),
                    'total'                    => __('Total', 'fluentform'),
                    'not_found'                => __('No payment item selected yet', 'fluentform'),
                    'discount:'                => __('Discount:', 'fluentform'),
                    'processing_text'          => __('Processing payment. Please wait...', 'fluentform'),
                    'confirming_text'           => __('Confirming payment. Please wait...', 'fluentform'),
                    /* translators: %s is the item name */
                    'Signup Fee for %s'        => __('Signup Fee for %s', 'fluentform'),
                    /* translators: 1: item name, 2: plan name */
                    'Signup Fee for %1$s - %2$s' => __('Signup Fee for %1$s - %2$s', 'fluentform'),
                ]
            ]);
            
            $secretKey = apply_filters_deprecated(
                'fluentform-payment_stripe_publishable_key',
                [
                    StripeSettings::getPublishableKey($form->id),
                    $form->id
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/payment_stripe_publishable_key',
                'Use fluentform/payment_stripe_publishable_key instead of fluentform-payment_stripe_publishable_key.'
            );
            
            $publishableKey = apply_filters('fluentform/payment_stripe_publishable_key', $secretKey, $form->id);
            
            $stripeCustomCss = [
                'styles' => [
                    'base' => [
                        'backgroundColor' => 'white',
                        'color'           => '#32325d',
                        'fontFamily'      => "-apple-system, \"system-ui\", \"Segoe UI\", Roboto, Oxygen-Sans, Ubuntu, Cantarell, \"Helvetica Neue\", sans-serif",
                        'fontSize'        => '14px',
                        'fontSmoothing'   => 'antialiased',
                        'iconColor'       => '#32325d',
                        'textDecoration'  => 'none',
                        '::placeholder'   => [
                            'color'=> "#aab7c4"
                        ],
                        ":focus" => [
                            'backgroundColor' => 'white',
                            'color'           => '#32325d',
                            'fontFamily'      => "-apple-system, \"system-ui\", \"Segoe UI\", Roboto, Oxygen-Sans, Ubuntu, Cantarell, \"Helvetica Neue\", sans-serif",
                            'fontSize'        => '14px',
                            'fontSmoothing'   => 'antialiased',
                            'iconColor'       => '#32325d',
                            'textDecoration'  => 'none',
                        ],
                    ],
                    'invalid' => [
                        'color'     => "#fa755a",
                        'iconColor' => "#fa755a"
                    ]
                ]
            ];

            $paymentConfig = [
                'currency_settings' => PaymentHelper::getCurrencyConfig($form->id),
                'stripe'            => [
                    'publishable_key' => $publishableKey,
                    'inlineConfig'    => PaymentHelper::getStripeInlineConfig($form->id),
                    'custom_style'    => apply_filters(
                        'fluentform/stripe_inline_custom_css',
                        $stripeCustomCss,
                        $form->id
                    ),
                    'locale'          => 'en'
                ],
                'stripe_app_info'   => [
                    'name'       => 'Fluent Forms',
                    'version'    => FLUENTFORM_VERSION,
                    'url'        => site_url(),
                    'partner_id' => 'pp_partner_FN62GfRLM2Kx5d'
                ],
            ];
            $paymentConfig = apply_filters('fluentform/payment_config', $paymentConfig, $form->id);
            
            wp_localize_script('fluentform-payment-handler', 'fluentform_payment_config_' . $form->id, $paymentConfig);
            
        }, 11, 1);
        
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Public payment callback from gateway, nonce not applicable, verified by transaction_hash in validateFrameLessPage()
        if (isset($_GET['fluentform_payment']) && isset($_GET['payment_method'])) {
            add_action('wp', function () {
                $data = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Data sanitized below and verified by validateFrameLessPage()

                $type = isset($_GET['fluentform_payment']) ? sanitize_text_field(wp_unslash($_GET['fluentform_payment'])) : '';

                if ($type == 'view' && $route = ArrayHelper::get($data, 'route')) {
                    do_action_deprecated(
                        'fluent_payment_view_' . $route,
                        [
                            $data
                        ],
                        FLUENTFORM_FRAMEWORK_UPGRADE,
                        'fluentform/payment_view_' . $route,
                        'Use fluentform/payment_view_' . $route . ' instead of fluent_payment_view_' . $route
                    );
                    do_action('fluentform/payment_view_' . $route, $data);
                }

                $this->validateFrameLessPage($data);
                $paymentMethod = isset($_GET['payment_method']) ? sanitize_text_field(wp_unslash($_GET['payment_method'])) : '';
                do_action_deprecated(
                    'fluent_payment_frameless_' . $paymentMethod,
                    [
                        $data
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/payment_frameless_' . $paymentMethod,
                    'Use fluentform/payment_frameless_' . $paymentMethod . ' instead of fluent_payment_frameless_' . $paymentMethod
                );
                do_action('fluentform/payment_frameless_' . $paymentMethod, $data);
            });
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended -- End of payment callback processing

        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- IPN callback from payment gateway, verified by gateway signature
        if (isset($_REQUEST['fluentform_payment_api_notify'])) {
            add_action('wp', function () {
                $paymentMethod = isset($_REQUEST['payment_method']) ? sanitize_text_field(wp_unslash($_REQUEST['payment_method'])) : '';
                do_action_deprecated(
                    'fluentform_ipn_endpoint_' . $paymentMethod,
                    [
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/ipn_endpoint_' . $paymentMethod,
                    'Use fluentform/ipn_endpoint_' . $paymentMethod . ' instead of fluentform_ipn_endpoint_' . $paymentMethod
                );
                do_action('fluentform/ipn_endpoint_' . $paymentMethod);
            });
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended -- End of IPN webhook processing

        add_filter('fluentform/editor_vars', function ($vars) {
            $settings = PaymentHelper::getCurrencyConfig($vars['form_id']);
            $vars['payment_settings'] = $settings;
            $vars['has_payment_features'] = !!$settings;
            return $vars;
        });
        
        add_filter('fluentform/admin_i18n', array($this, 'paymentTranslations'), 10, 1);
        
        add_filter('fluentform/payment_smartcode', array($this, 'paymentReceiptView'), 10, 3);
        
        add_action('user_register', array($this, 'maybeAssignTransactions'), 99, 1);
        
        (new PaymentEntries())->init();
        
        /*
         * Transactions and subscriptions Shortcode
         */
        (new TransactionShortcodes())->init();
        
        add_filter(
            'fluentform/validate_input_item_subscription_payment_component',
            [$this, 'validateSubscriptionInputs'],
            10,
            3
        );
        
        add_filter(
            'fluentform/validate_input_item_multi_payment_component',
            [$this, 'validatePaymentInputs'],
            10,
            3
        );
        
        add_filter(
            'fluentform/validate_input_item_payment_method',
            [$this, 'validatePaymentMethod'],
            10,
            5
        );
    }
    
    public function pushGlobalSettings($components)
    {
        $subMenuItems = [
            [
                'title'     => 'Settings',
                'hash'      => 'payments/general_settings',
            ],
            [
                'title'     => 'Payment Methods',
                'hash'      => 'payments/payment_methods',
            ]
        ];
        $subMenuItems = apply_filters('fluentform/global_settings_payment_sub_menu_items', $subMenuItems);

        $components['payment_settings'] = [
            'title' => __('Payment Settings', 'fluentform'),
            'sub_menu'=> $subMenuItems
        ];
        return $components;
    }
    
    public function getGlobalSettingsPaymentVars($globalSettingVars)
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Admin page, nonce verified by WordPress admin
        if (isset($_GET['ff_stripe_connect'])) {
            $data = ArrayHelper::only($_GET, ['ff_stripe_connect', 'mode', 'state', 'code']);
            ConnectConfig::verifyAuthorizeSuccess($data);
        }

        $paymentSettings = PaymentHelper::getPaymentSettings();
        $isSettingsAvailable = PaymentHelper::hasPaymentSettings();

        $nav = 'general';

        if (isset($_REQUEST['nav'])) {
            $nav = sanitize_text_field(wp_unslash($_REQUEST['nav']));
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended -- End of admin page GET parameter processing

        $paymentMethods = apply_filters_deprecated(
            'fluentformpro_available_payment_methods',
            [
                []
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/available_payment_methods',
            'Use fluentform/available_payment_methods instead of fluentformpro_available_payment_methods.'
        );
        
        $globalSettings = apply_filters_deprecated(
            'fluentformpro_payment_methods_global_settings',
            [
                []
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_methods_global_settings',
            'Use fluentform/payment_methods_global_settings instead of fluentformpro_payment_methods_global_settings.'
        );

        $paymentVars = [
            'is_setup'                  => $isSettingsAvailable,
            'general'                   => $paymentSettings,
            'payment_methods'           => apply_filters('fluentform/available_payment_methods', $paymentMethods),
            'available_payment_methods' => apply_filters('fluentform/payment_methods_global_settings', $globalSettings),
            'currencies'                => PaymentHelper::getCurrencies(),
            'active_nav'                => $nav,
            'stripe_webhook_url'        => add_query_arg([
                'fluentform_payment_api_notify' => '1',
                'payment_method'                => 'stripe'
            ], site_url('index.php')),
            'paypal_webhook_url'        => add_query_arg([
                'fluentform_payment_api_notify' => '1',
                'payment_method'                => 'paypal'
            ], site_url('index.php'))
        ];

        // Enqueue payment global settings css
        wp_enqueue_style('ff-payment-settings', fluentFormMix('css/payment_settings.css'), [], FLUENTFORM_VERSION);

        $globalSettingVars['payment_vars'] = apply_filters('fluentform/global_settings_component_payment_vars', $paymentVars);
        return $globalSettingVars;
    }
    
    public function handleAjaxEndpoints()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce verified by Acl::verify()
        if (isset($_REQUEST['form_id'])) {
            Acl::verify('fluentform_forms_manager');
        } else {
            Acl::verify('fluentform_settings_manager');
        }

        $route = isset($_REQUEST['route']) ? sanitize_text_field(wp_unslash($_REQUEST['route'])) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Recommended -- End of AJAX endpoint nonce verification by Acl::verify()

        (new AjaxEndpoints())->handleEndpoint($route);
    }
    
    public function maybeHandlePayment($insertData, $data, $form)
    {
        // Let's get selected Payment Method
        if (!FormFieldsParser::hasPaymentFields($form)) {
            return;
        }
        
        $paymentAction = new PaymentAction($form, $insertData, $data);
        
        if (!$paymentAction->getSubscriptionItems() && !$paymentAction->getCalculatedAmount()) {
            return;
        }
        
        /*
         * We have to check if
         * 1. has payment method
         * 2. if user selected payment method
         * 3. or maybe has a conditional logic on it
         */
        if ($paymentAction->isConditionPass()) {
            if (FormFieldsParser::hasElement($form, 'payment_method') &&
                !$paymentAction->selectedPaymentMethod
            ) {
                wp_send_json([
                    'errors' => [__('Sorry! No selected payment method found. Please select a valid payment method', 'fluentform')]
                ], 423);
            }
        }
        
        /*
         * Some Payment Gateway like Razorpay, Square not supported $subscriptionItems.
         * So we are providing filter hook to validate payment fields.
         */
        $errors = apply_filters(
            'fluentform/validate_payment_items_' . $paymentAction->selectedPaymentMethod,
            [],
            $paymentAction->getOrderItems(),
            $paymentAction->getSubscriptionItems(),
            $form
        );
        
        if ($errors) {
            wp_send_json([
                'errors' => $errors
            ], 423);
        }
        
        $paymentAction->draftFormEntry();
    }
    
    public function isEnabled()
    {
        $paymentSettings = PaymentHelper::getPaymentSettings();
        return $paymentSettings['status'] == 'yes';
    }
    
    public function modifySingleEntryLabels($labels, $submission, $form)
    {
        $formFields = FormFieldsParser::getPaymentFields($form);
        if ($formFields && is_array($formFields)) {
            $labels = ArrayHelper::except($labels, array_keys($formFields));
        }
        return $labels;
    }
    
    public function maybeAddPaymentSettings($menus, $formId)
    {
        $form = \FluentForm\App\Models\Form::find($formId);
        if ($form->has_payment) {
            $menus = array_merge(array_slice($menus, 0, 1), array(
                'payment_settings' => [
                    'title' => __('Payment Settings', 'fluentform'),
                    'slug'  => 'form_settings',
                    'hash'  => 'payment_settings',
                    'route' => '/payment-settings',
                ]
            ), array_slice($menus, 1));
        }
        return $menus;
    }
    
    
    /**
     * @param $html     string
     * @param $property string
     * @param $instance ShortCodeParser
     * @return false|string
     */
    public function paymentReceiptView($html, $property, $instance)
    {
        $entry = $instance::getEntry();
        $receiptClass = new PaymentReceipt($entry);
        return $receiptClass->getItem($property);
    }
    
    private function validateFrameLessPage($data)
    {
        // We should verify the transaction hash from the URL
        $transactionHash = sanitize_text_field(ArrayHelper::get($data, 'transaction_hash'));
        $submissionId = intval(ArrayHelper::get($data, 'fluentform_payment'));
        if (!$submissionId) {
            die('Validation Failed');
        }
        
        if ($transactionHash) {
            $transaction = \FluentForm\App\Models\Transaction::bySubmission($submissionId)
                ->where('transaction_hash', $transactionHash)
                ->first();
            if ($transaction) {
                return true;
            }
            
            die('Transaction hash is invalid');
        }
        
        $uid = sanitize_text_field(ArrayHelper::get($data, 'entry_uid'));
        if (!$uid) {
            die('Validation Failed');
        }
        
        $originalUid = Helper::getSubmissionMeta($submissionId, '_entry_uid_hash');

        if ($originalUid != $uid) {
            die(esc_html__('Transaction UID is invalid', 'fluentform'));
        }
        
        return true;
    }
    
    public function maybeAssignTransactions($userId)
    {
        $user = get_user_by('ID', $userId);
        if (!$user) {
            return false;
        }
        $userEmail = $user->user_email;
        
        $transactions = \FluentForm\App\Models\Transaction::where('payer_email', $userEmail)
            ->where(function ($query) {
                $query->whereNull('user_id')
                    ->orWhere('user_id', '');
            })
            ->get();
        
        if (count($transactions) === 0) {
            return false;
        }
        
        $submissionIds = [];
        $transactionIds = [];
        foreach ($transactions as $transaction) {
            $submissionIds[] = $transaction->submission_id;
            $transactionIds[] = $transaction->id;
        }
        
        $submissionIds = array_unique($submissionIds);
        $transactionIds = array_unique($transactionIds);
        
        \FluentForm\App\Models\Submission::whereIn('id', $submissionIds)
            ->update([
                'user_id'    => $userId,
                'updated_at' => current_time('mysql')
            ]);

        \FluentForm\App\Models\Transaction::whereIn('id', $transactionIds)
            ->update([
                'user_id'    => $userId,
                'updated_at' => current_time('mysql')
            ]);
        
        return true;
    }
    
    public function paymentTranslations($i18n)
    {
        $paymentI18n = array(
            'Order Details' => __('Order Details', 'fluentform'),
            'Product' => __('Product', 'fluentform'),
            'Qty' => __('Qty', 'fluentform'),
            'Unit Price' => __('Unit Price', 'fluentform'),
            'Total' => __('Total', 'fluentform'),
            'Sub-Total' => __('Sub-Total', 'fluentform'),
            'Discount' => __('Discount', 'fluentform'),
            'Price' => __('Price', 'fluentform'),
            'Payment Details' => __('Payment Details', 'fluentform'),
            'From Subscriptions' => __('From Subscriptions', 'fluentform'),
            'Card Last 4' => __('Card Last 4', 'fluentform'),
            'Payment Total' => __('Payment Total', 'fluentform'),
            'Payment Status' => __('Payment Status', 'fluentform'),
            'Transaction ID' => __('Transaction ID', 'fluentform'),
            'Payment Method' => __('Payment Method', 'fluentform'),
            'Transaction' => __('Transaction', 'fluentform'),
            'Refunds' => __('Refunds', 'fluentform'),
            'Refund' => __('Refund', 'fluentform'),
            'at' => __('at', 'fluentform'),
            'View' => __('View', 'fluentform'),
            'has been refunded via' => __('has been refunded via', 'fluentform'),
            'Note' => __('Note', 'fluentform'),
            'Edit Transaction' => __('Edit Transaction', 'fluentform'),
            'Billing Name' => __('Billing Name', 'fluentform'),
            'Billing Email' => __('Billing Email', 'fluentform'),
            'Billing Address' => __('Billing Address', 'fluentform'),
            'Shipping Address' => __('Shipping Address', 'fluentform'),
            'Reference ID' => __('Reference ID', 'fluentform'),
            'refunds-to-be-handled-from-provider-text' => __('Please note that, Actual Refund needs to be handled in your Payment Service Provider.', 'fluentform'),
            'Please Provide new refund amount only.' => __('Please Provide new refund amount only.', 'fluentform'),
            'Refund Note' => __('Refund Note', 'fluentform'),
            'Cancel' => __('Cancel', 'fluentform'),
            'Confirm' => __('Confirm', 'fluentform'),
        );
        return array_merge($i18n,$paymentI18n);
    }
    
    public function validateSubscriptionInputs($error, $field, $formData)
    {
        if (isset($formData[$field['name']])) {
            $subscriptionOptions = ArrayHelper::get($field, 'raw.settings.subscription_options', []);
            $selectedPlanIndex = $formData[$field['name']];
            $acceptedSubscriptionPlan = is_numeric($selectedPlanIndex) && in_array($selectedPlanIndex, array_keys($subscriptionOptions));
            if (!$acceptedSubscriptionPlan) {
                $error = __('This subscription plan is invalid', 'fluentform');
            }
            $selectedPlan = ArrayHelper::get($subscriptionOptions, $selectedPlanIndex, []);
            if ('yes' === ArrayHelper::get($selectedPlan, 'user_input')) {
                $userGivenValue = ArrayHelper::get($formData, "{$field['name']}_custom_$selectedPlanIndex");
                $userGivenValue = $userGivenValue ?: 0;
                $planMinValue = ArrayHelper::get($selectedPlan, 'user_input_min_value');
                if (!is_numeric($userGivenValue) || ($planMinValue && $userGivenValue < $planMinValue)) {
                    $error = __('This subscription plan value is invalid', 'fluentform');
                }
            }
        }
        
        return $error;
    }
    
    public function validatePaymentInputs($error, $field, $formData)
    {
        if (ArrayHelper::get($formData, $field['name'])) {
            $fieldType = ArrayHelper::get($field, 'raw.attributes.type');
            
            if (in_array($fieldType, ['radio', 'select', 'checkbox'])) {
                $pricingOptions = array_column(
                    ArrayHelper::get($field, 'raw.settings.pricing_options', []),
                    'label'
                );
                
                $pricingOptions = array_map('sanitize_text_field', $pricingOptions);
                
                if (in_array($fieldType, ['radio', 'select'])) {
                    $acceptedPaymentPlan = in_array($formData[$field['name']], $pricingOptions);
                } else {
                    $acceptedPaymentPlan = array_diff($formData[$field['name']], $pricingOptions);
                    
                    $acceptedPaymentPlan = empty($acceptedPaymentPlan);
                }
                
                if (!$acceptedPaymentPlan) {
                    $error = __('This payment item is invalid', 'fluentform');
                }
            }
        }
        
        return $error;
    }
    
    public function validatePaymentMethod($error, $field, $formData, $fields, $form)
    {
        if ($selectedMethod = ArrayHelper::get($formData, $field['name'])) {
            $activeMethods = array_keys(PaymentHelper::getFormPaymentMethods($form->id));
            if (!in_array($selectedMethod, $activeMethods)) {
                $error = __('This payment method is invalid', 'fluentform');
            }
        }
        return $error;
    }
}
