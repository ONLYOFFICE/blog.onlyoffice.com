<?php

namespace FluentForm\App\Modules\Payments\Components;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Payments\PaymentHelper;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Services\FormBuilder\BaseFieldManager;

class Subscription extends BaseFieldManager
{
    public function __construct(
        $key = 'subscription_payment_component',
        $title = 'Subscription Field',
        $tags = ['custom', 'payment', 'donation', 'subscription'],
        $position = 'payments'
    )
    {
        parent::__construct(
            $key,
            $title,
            $tags,
            $position
        );

        add_filter('fluentform/editor_init_element_subscription_payment_component', function ($element) {
            if (!isset($element['settings']['layout_class'])) {
                $element['settings']['layout_class'] = '';
            }
            // Backward compatibility: ensure existing plans have end date fields
            if (!empty($element['settings']['subscription_options'])) {
                foreach ($element['settings']['subscription_options'] as &$option) {
                    if (!isset($option['has_end_date'])) {
                        $option['has_end_date'] = 'no';
                    }
                    if (!isset($option['subscription_end_date'])) {
                        $option['subscription_end_date'] = '';
                    }
                    if (!isset($option['expire_behavior'])) {
                        $option['expire_behavior'] = 'show_error';
                    }
                }
                unset($option);
            }
            return $element;
        });
        add_filter('fluentform/response_render_' . $this->key, function ($response, $field, $form_id, $isHtml = false) {
            if (!$isHtml) {
                return $response;
            }
            return ArrayHelper::get($field, 'raw.settings.subscription_options.' . $response . '.name', $response);
        }, 10, 4);
        add_filter('fluentform/white_listed_fields', [$this, 'addWhiteListedFields'], 10, 2);
    }

    function getComponent()
    {
        return array(
            'index'          => 8,
            'element'        => $this->key,
            'attributes'     => array(
                'type'  => 'single', // single|multiple
                'name'  => 'payment_input',
                'value' => '10'
            ),
            'settings'       => array(
                'container_class'      => '',
                'label'                => __('Subscription Item', 'fluentform'),
                'admin_field_label'    => '',
                'label_placement'      => '',
                'display_type'         => '',
                'help_message'         => '',
                'is_payment_field'     => 'yes',
                'selection_type'       => 'radio',
                'subscription_options' => [
                    [
                        "bill_times"            => 0,
                        "billing_interval"      => "month",
                        "has_signup_fee"        => "no",
                        "has_trial_days"        => "no",
                        "is_default"            => "yes",
                        "name"                  => __("Monthly Plan", 'fluentform'),
                        "plan_features"         => [],
                        "signup_fee"            => 0,
                        "subscription_amount"   => 9.99,
                        "trial_days"            => 0,
                        "has_end_date"          => "no",
                        "subscription_end_date" => "",
                        "expire_behavior"       => "show_error",
                    ]
                ],
                'price_label'          => __('Price:', 'fluentform'),
                'enable_quantity'      => false,
                'enable_image_input'   => false,
                'is_element_lock'      => false,
                'layout_class'         => 'ff_list_buttons',
                'validation_rules'     => array(
                    'required' => array(
                        'value'          => false,
                        'global'         => true,
                        'message'        => Helper::getGlobalDefaultMessage('required'),
                        'global_message' => Helper::getGlobalDefaultMessage('required'),
                    ),
                ),
                'conditional_logics'   => array(),
            ),
            'editor_options' => array(
                'title'      => __('Subscription', 'fluentform'),
                'icon_class' => 'ff-edit-shopping-cart',
                'element'    => 'input-radio',
                'template'   => 'inputSubscriptionPayment'
            )
        );
    }

    public function getGeneralEditorElements()
    {
        return [
            'label',
            'label_placement',
            'admin_field_label',
            'placeholder',
            'subscription_options',
            'validation_rules',
        ];
    }

    public function getAdvancedEditorElements()
    {
        return [
            'container_class',
            'help_message',
            'name',
            'layout_class',
            'conditional_logics'
        ];
    }

    function render($data, $form)
    {
        $type = ArrayHelper::get($data, 'attributes.type');

        if ($type == 'single') {
            $this->renderSingleItem($data, $form);
            return '';
        }

        $this->renderMultiProduct($data, $form);
    }

    private function isPlanExpiredAndHidden($plan)
    {
        return PaymentHelper::isPlanExpiredAndHidden($plan);
    }

    public function renderSingleItem($data, $form)
    {
        $elementName = $data['element'];
        $data = apply_filters_deprecated(
            'fluentform_rendering_field_data_' . $elementName,
            [
                $data,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/rendering_field_data_' . $elementName,
            'Use fluentform/rendering_field_data_' . $elementName . ' instead of fluentform_rendering_field_data_' . $elementName
        );

        $data = apply_filters('fluentform/rendering_field_data_' . $elementName, $data, $form);
        $plan = ArrayHelper::get($data, 'settings.subscription_options.0', []);
        $plan['index'] = 0;

        // Hide the entire field if the single plan is expired and set to hide
        if ($this->isPlanExpiredAndHidden($plan)) {
            return;
        }

        $isCustomAmount = ArrayHelper::get($plan, 'user_input') === 'yes';
        if ($isCustomAmount) {
            $plan['subscription_amount'] = ArrayHelper::get($plan, 'user_input_default_value');
        }

        $inputAttributes = [
            'type'  => 'hidden',
            'name'  => $data['attributes']['name'],
            'value' => '0',
            'class' => 'ff_payment_item ff_subscription_item',
        ];

        $inputAttributes['id'] = $this->makeElementId($data, $form);
        $currency = PaymentHelper::getFormCurrency($form->id);

        $billingAttributes = $this->getPlanInputAttributes($plan);

        $itemInputAttributes = wp_parse_args($billingAttributes, $inputAttributes);

        $elMarkup = "<input " . $this->buildAttributes($itemInputAttributes, $form) . ">";

        if ($isCustomAmount) {
            $plan['user_input_label'] = ArrayHelper::get($plan, 'user_input_label');
            $plan['user_input_label'] = $plan['user_input_label'] ?: ArrayHelper::get($data, 'settings.label');

            $elMarkup = $this->makeCustomInputHtml($data, $plan, 'hidden', $elMarkup);

            $data['settings']['label'] = ArrayHelper::get($plan, 'user_input_label', $data['settings']['label']);
            $data['attributes']['id'] = ArrayHelper::get($data, 'attributes.name') . '_custom_' . $plan['index'];
        }

        $paymentSummary = $this->getPaymentSummaryText($plan, $form, $currency);
        $elMarkup .= $paymentSummary;

        $html = $this->buildElementMarkup($elMarkup, $data, $form);

        $html = apply_filters_deprecated(
            'fluentform_rendering_field_html_' . $elementName,
            [
                $html,
                $data,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/rendering_field_html_' . $elementName,
            'Use fluentform/rendering_field_html_' . $elementName . ' instead of fluentform_rendering_field_html_' . $elementName
        );

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is escaped by the filter
        echo apply_filters('fluentform/rendering_field_html_' . $elementName, $html, $data, $form);
    }

    public function renderMultiProduct($data, $form)
    {
        $elementName = $data['element'];
    
        $data = apply_filters_deprecated(
            'fluentform_rendering_field_data_' . $elementName,
            [
                $data,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/rendering_field_data_' . $elementName,
            'Use fluentform/rendering_field_data_' . $elementName . ' instead of fluentform_rendering_field_data_' . $elementName
        );

        $data = apply_filters('fluentform/rendering_field_data_' . $elementName, $data, $form);

        // If all plans are expired and hidden, hide the entire field
        $pricingPlans = ArrayHelper::get($data, 'settings.subscription_options', []);
        $visiblePlans = array_filter($pricingPlans, function ($plan) {
            return !$this->isPlanExpiredAndHidden($plan);
        });

        if (empty($visiblePlans)) {
            return;
        }

        $currency = PaymentHelper::getFormCurrency($form->id);

        $type = ArrayHelper::get($data, 'settings.selection_type', 'radio');
        $data['attributes']['type'] = $type;

        $data['settings']['container_class'] .= ' ff_subs_selections';

        $isSmartUi = false;
        if ($type == 'radio') {
            $layoutClass = ArrayHelper::get($data, 'settings.layout_class');

            if ($layoutClass == 'ff_list_buttons') {
                $isSmartUi = true;
            }

            if ($layoutClass) {
                $data['settings']['container_class'] .= ' ff_sub_smart_ui ' . $layoutClass;
            }
        }

        $data['attributes']['class'] = trim(
            'ff-el-form-check-input ' .
            'ff-el-form-check-' . $data['attributes']['type'] . ' ' .
            ArrayHelper::get($data, 'attributes.class')
        );

        $elMarkup = '';
        $customAmountMarkup = '';
        $firstTabIndex = \FluentForm\App\Helpers\Helper::getNextTabIndex();

        if ($type === 'select') {
            $selectAtts = [
                'name'                   => $data['attributes']['name'],
                'class'                  => 'ff-el-form-control ff_subscription_item ff_payment_item',
                'data-subscription_item' => 'yes',
                'data-name'              => $data['attributes']['name'],
                'id'                     => $this->getUniqueid(str_replace(['[', ']'], ['', ''], $data['attributes']['name'])),
                'data-parent_input_name' => $data['attributes']['name'],
                'data-parent_input_type' => $type,
            ];

            if ($firstTabIndex) {
                $selectAtts['tabindex'] = $firstTabIndex;
            }

            $elMarkup .= '<select ' . $this->buildAttributes($selectAtts, $form) . '>';

            $placeholder = ArrayHelper::get($data, 'settings.placeholder', __('--Select Plan--', 'fluentform'));

            $elMarkup .= '<option value>' . $placeholder . '</option>';
        }

        $groupId = $this->makeElementId($data, $form);

        foreach ($pricingPlans as $index => $pricingPlan) {
            $pricingPlan['index'] = $index;

            // Skip expired plans that are set to hide
            if ($this->isPlanExpiredAndHidden($pricingPlan)) {
                continue;
            }

            $isDefaultPlan = ArrayHelper::get($pricingPlan, 'is_default') === 'yes';

            $isCustomAmount = ArrayHelper::get($pricingPlan, 'user_input') === 'yes';

            if ($isCustomAmount) {
                $pricingPlan['subscription_amount'] = ArrayHelper::get($pricingPlan, 'user_input_default_value');
            }

            $billingAttributes = $this->getPlanInputAttributes($pricingPlan);

            if ($type === 'select') {
                $optionAtts = array_merge($billingAttributes, [
                    'value'    => $index,
                    'selected' => $isDefaultPlan
                ]);
                $optionAtts = $this->buildAttributes($optionAtts, $form);

                $elMarkup .= '<option ' . $optionAtts . '>' . $pricingPlan['name'] . '</option>';
            } else {
                $displayType = isset($data['settings']['display_type']) ? ' ff-el-form-check-' . $data['settings']['display_type'] : '';
                $parentClass = "ff-el-form-check{$displayType}";
                $atts = $data['attributes'];
                $atts['checked'] = $isDefaultPlan;

                if ($isDefaultPlan) {
                    $parentClass .= ' ff_item_selected';
                }

                if ($firstTabIndex) {
                    $atts['tabindex'] = $firstTabIndex;
                    $firstTabIndex = '-1';
                }

                $atts['class'] .= ' ff_subscription_item ff_payment_item';
                $atts['value'] = $index;
                $atts['data-group_id'] = $groupId;

                $id = $this->getUniqueid(str_replace(['[', ']'], ['', ''], $atts['name']));
                $atts = $this->buildAttributes(array_merge($billingAttributes, $atts), $form);

                $labelHtml = "<span class='ff_plan_name'>{$pricingPlan['name']}</span>";

                if ($isSmartUi) {
                    $paymentSummary = $this->getPaymentSummaryText($pricingPlan, $form->id, $currency);
                    $summaryHtml = '<div class="ff_sub_desc">' . $paymentSummary . '</div>';
                    $labelHtml = "<span class='ff_plan_holder'><span class='ff_plan_title'>{$pricingPlan['name']}</span>" . $summaryHtml . "</span>";
                }

                $elMarkup .= "<div class='{$parentClass}'>";
                $elMarkup .= "<label class='ff-el-form-check-label' for={$id}><input {$atts} id='{$id}'>" . $labelHtml . "</label>";
                $elMarkup .= "</div>";
            }

            if ($isCustomAmount) {
                $customAmountMarkup = $this->makeCustomInputHtml($data, $pricingPlan, $type, $customAmountMarkup);
            }

            if (!$isSmartUi) {
                $paymentSummary = $this->getPaymentSummaryText($pricingPlan, $form, $currency);
                $customAmountMarkup .= $paymentSummary;
            }
        }

        if ($type === 'select') {
            $elMarkup .= '</select>';
        }

        if ($customAmountMarkup) {
            $elMarkup .= $customAmountMarkup;
        }

        $html = $this->buildElementMarkup($elMarkup, $data, $form);

        $html = apply_filters_deprecated(
            'fluentform_rendering_field_html_' . $elementName,
            [
                $html,
                $data,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/rendering_field_html_' . $elementName,
            'Use fluentform/rendering_field_html_' . $elementName . ' instead of fluentform_rendering_field_html_' . $elementName
        );

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is escaped by the filter
        echo apply_filters('fluentform/rendering_field_html_' . $elementName, $html, $data, $form);
    }

    private function getPaymentSummaryText($plan, $formId, $currency)
    {
        return PaymentHelper::getPaymentSummaryText($plan, $formId, $currency, true);
    }

    private function getPlanInputAttributes($plan)
    {
        $subscriptionAmount = $plan['subscription_amount'];
        $currentBillableAmount = $subscriptionAmount;
        $initialAmount = 0;

        $signupFee = 0;
        if ($this->hasSignupFee($plan)) {
            $signupFee = $plan['signup_fee'];
        }

        $trialDays = false;
        if ($this->hasTrial($plan)) {
            $currentBillableAmount = 0;
            $trialDays = ArrayHelper::get($plan, 'trial_days');
        }

        return [
            'data-subscription_amount' => $subscriptionAmount ?: 0,
            'data-billing_interval'    => $plan['billing_interval'],
            'data-price'               => $currentBillableAmount ?: 0,
            'data-payment_value'       => $currentBillableAmount ?: 0,
            'data-initial_amount'      => $initialAmount ?: 0,
            'data-signup_fee'          => $signupFee,
            'data-trial_days'          => $trialDays,
            'data-plan_name' => esc_attr($plan['name'])
        ];
    }

    private function hasTrial($plan)
    {
        $hasTrial = ArrayHelper::get($plan, 'has_trial_days') == 'yes';
        $trialDays = ArrayHelper::get($plan, 'trial_days');

        return $hasTrial && $trialDays;
    }

    private function hasSignupFee($plan)
    {
        $hasSignup = ArrayHelper::get($plan, 'has_signup_fee') == 'yes';
        $signUpFee = ArrayHelper::get($plan, 'signup_fee');

        return $hasSignup && $signUpFee;
    }

    private function makeCustomInputHtml($field, $plan, $parentInputType, $markup)
    {
        $htmlID = ArrayHelper::get($field, 'attributes.name') . '_custom_' . $plan['index'];
        $isDefault = ArrayHelper::get($plan, 'is_default') === 'yes';

        $customAmountInputAttributes = $this->buildAttributes([
            'name'                   => $htmlID,
            'type'                   => 'number',
            'value'                  => ArrayHelper::get($plan, 'user_input_default_value'),
            'min'                    => $isDefault ? ArrayHelper::get($plan, 'user_input_min_value', 0) : '0',
            'data-min'               => ArrayHelper::get($plan, 'user_input_min_value', 0),
            'step'                   => 'any',
            'placeholder'            => ArrayHelper::get($plan, 'user_input_label'),
            'class'                  => 'ff-el-form-control ff-custom-user-input ' . ($isDefault ? 'is-default' : ''),
            'id'                     => $htmlID,
            'data-parent_input_name' => $field['attributes']['name'],
            'data-parent_input_type' => $parentInputType,
            'data-plan_index'        => $plan['index'],
            'data-plan_trial_days'   => ArrayHelper::get($plan, 'trial_days', 0)
        ]);

        $class = $isDefault ? '' : 'hidden_field';
        $style = $parentInputType === 'hidden' ? '' : "style='margin-top: 5px'";
        $markup .= "<div class='ff-custom-user-input-wrapper ff-custom-user-input-wrapper-{$plan['index']} {$class}' {$style}>";

        if ($parentInputType !== 'hidden') {
            $markup .= "<label class='ff-el-form-check-label' for='{$htmlID}'>" . ArrayHelper::get($plan, 'user_input_label') . '</label>';
        }

        $markup .= "<input {$customAmountInputAttributes}>";
        $markup .= '</div>';

        return $markup;
    }

    public function addWhiteListedFields($whiteListedFields, $formId)
    {
        $form = \FluentForm\App\Models\Form::find($formId);
        if (!$form->has_payment) {
          return $whiteListedFields;
        }
        $inputs = FormFieldsParser::getInputsByElementTypes($form, ['subscription_payment_component'], ['settings', 'attributes']);
        $customInputNames = [];
        foreach ($inputs as $subscriptionInput) {
            $index = 0;
            $subscriptionOptions = ArrayHelper::get($subscriptionInput, 'settings.subscription_options');
            foreach ($subscriptionOptions as $plan) {
                if (ArrayHelper::get($plan, 'user_input') === 'yes') {
                    $customInputNames[] = ArrayHelper::get($subscriptionInput, 'attributes.name') . '_custom_' . $index;
                }
                $index++;
            }
        }
        return array_merge($whiteListedFields, $customInputNames);
    }
}
