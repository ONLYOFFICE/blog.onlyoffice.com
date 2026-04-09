<?php

namespace FluentForm\App\Modules\Payments\Components;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Services\FormBuilder\BaseFieldManager;

class PaymentMethods extends BaseFieldManager
{

    public function __construct()
    {
        parent::__construct(
            'payment_method',
            'Payment Method',
            ['payment methods', 'payment', 'methods', 'stripe', 'paypal'],
            'payments'
        );

        add_filter(
            'fluentform/editor_init_element_payment_method',
            [$this, 'recheckEditorComponent']
        );

        add_filter('fluentform/response_render_' . $this->key, function ($value) {
            if ($value == 'test') {
                return 'Offline';
            }
            return $value;
        });

    }

    function getComponent()
    {
        $available_methods = apply_filters_deprecated(
            'fluentformpro_available_payment_methods',
            [
                []
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/available_payment_methods',
            'Use fluentform/available_payment_methods instead of fluentformpro_available_payment_methods.'
        );
        $available_methods = apply_filters('fluentform/available_payment_methods', $available_methods);

        if (!$available_methods) {
            return;
        }

        return [
            'index'          => 10,
            'element'        => $this->key,
            'attributes'     => [
                'name'  => $this->key,
                'type'  => 'radio',
                'value' => ''
            ],
            'settings'       => [
                'container_class'    => '',
                'label'              => $this->title,
                'default_method'     => '',
                'label_placement'    => '',
                'help_message'       => '',
                'payment_methods'    => $available_methods,
                'validation_rules' => array(
                    'required' => array(
                        'value'          => false,
                        'global'         => true,
                        'message'        => Helper::getGlobalDefaultMessage('required'),
                        'global_message' => Helper::getGlobalDefaultMessage('required'),
                    )
                ),
                'admin_field_label'  => '',
                'conditional_logics' => []
            ],
            'editor_options' => [
                'title'      => $this->title,
                'icon_class' => 'ff-edit-credit-card',
                'template'   => 'inputPaymentMethods'
            ],
        ];
    }

    public function recheckEditorComponent($component)
    {
        if (!isset($component['settings']['validation_rules'])) {
            $component['settings']['validation_rules'] = array(
                'required' => array(
                    'value'          => false,
                    'global'         => true,
                    'message'        => Helper::getGlobalDefaultMessage('required'),
                    'global_message' => Helper::getGlobalDefaultMessage('required'),
                ),
            );
        }
        $available_methods = apply_filters_deprecated(
            'fluentformpro_available_payment_methods',
            [
                []
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/available_payment_methods',
            'Use fluentform/available_payment_methods instead of fluentformpro_available_payment_methods.'
        );

        $available_methods = apply_filters('fluentform/available_payment_methods', $available_methods);

        if (!$available_methods) {
            $component['settings']['payment_methods'] = $available_methods;
            return $component;
        }

        $existingMethods = ArrayHelper::get($component, 'settings.payment_methods', []);

        $updatedMethods = [];

        foreach ($available_methods as $methodName => $method) {
            if (isset($existingMethods[$methodName])) {
                $method['settings'] = wp_parse_args($existingMethods[$methodName]['settings'], $method['settings']);
                $method['enabled'] = $existingMethods[$methodName]['enabled'];
            } else {
                $method['enabled'] = 'no';
            }
            $updatedMethods[$methodName] = $method;
        }

        $component['settings']['payment_methods'] = $updatedMethods;

        return $component;
    }

    public function getEditorCustomizationSettings()
    {
        return [
            'payment_methods' => array(
                'template'  => 'paymentMethodsConfig',
                'label'     => __('Payment Methods', 'fluentform'),
                'help_text' => __('Please Select and Configure the payment methods for this form, At least 1 method is required. If you select only one it will not show on the form for selection but will process that payment with the selected method.', 'fluentform')
            )
        ];
    }

    public function getGeneralEditorElements()
    {
        return [
            'label',
            'admin_field_label',
            'label_placement',
            'payment_methods',
            'value',
            'validation_rules'
        ];
    }

    function render($data, $form)
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

        $data['attributes']['class'] = trim(
            'ff-el-form-check-input ' .
            ArrayHelper::get($data, 'attributes.class')
        );

        $paymentMethods = ArrayHelper::get($data, 'settings.payment_methods');
    
        $available_methods = apply_filters_deprecated(
            'fluentformpro_available_payment_methods',
            [
                []
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/available_payment_methods',
            'Use fluentform/available_payment_methods instead of fluentformpro_available_payment_methods.'
        );

        $available_methods = apply_filters('fluentform/available_payment_methods', $available_methods);
        $activatedMethods = [];

        foreach ($paymentMethods as $methodName => $paymentMethod) {
            $enabled = ArrayHelper::get($paymentMethod, 'enabled');
            if ($enabled == 'yes' && isset($available_methods[$methodName])) {
                $activatedMethods[$methodName] = $paymentMethod;
            }
        }

        if (!$activatedMethods) {
            echo wp_sprintf(
                '<p class="ff-error ff-payment-method-error">%s</p>',
                esc_html__('No activated payment method found. If you are an admin please check the payment settings', 'fluentform')
            );
            return;
        }

        foreach ($activatedMethods as $methodName => $activatedMethod) {
            do_action_deprecated(
                'fluentform_rendering_payment_method_' . $methodName,
                [
                    $activatedMethod,
                    $data,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/rendering_payment_method_' . $methodName,
                'Use fluentform/rendering_payment_method_' . $methodName . ' instead of fluentform_rendering_payment_method_' . $methodName
            );
            do_action('fluentform/rendering_payment_method_' . $methodName, $activatedMethod, $data, $form);
        }


        $data['attributes']['class'] = $data['attributes']['class'] . ' ff_payment_method';

        if (count($activatedMethods) == 1) {
            $methodKeys = array_keys($activatedMethods);
            $methodName = $methodKeys[0];
            $methodElement = $activatedMethods[$methodName];
            $data['attributes']['type'] = 'hidden';
            $data['attributes']['value'] = $methodName;
            $data['attributes']['class'] .= ' ff_selected_payment_method';
            $elMarkup = $html = "<input " . $this->buildAttributes($data['attributes'], $form) . ">";

            $method = $activatedMethods[$methodName];
            $method['is_default'] = true;
            $selectedMarkups = apply_filters_deprecated(
                'fluentform_payment_method_contents_' . $methodName,
                [
                    '',
                    $method,
                    $data,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/payment_method_contents_' . $methodName,
                'Use fluentform/payment_method_contents_' . $methodName . ' instead of fluentform_payment_method_contents_' . $methodName
            );
            $selectedMarkups = apply_filters('fluentform/payment_method_contents_' . $methodName, $selectedMarkups, $method, $data, $form);

            if ($selectedMarkups) {
                $elMarkup .= $selectedMarkups;
                $data['settings']['label'] = '';
                $html = $this->buildElementMarkup($elMarkup, $data, $form);
            }

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

            do_action_deprecated(
                'fluentform_payment_method_render_' . $methodName,
                [
                    $methodElement,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/payment_method_render_' . $methodName,
                'Use fluentform/payment_method_render_' . $methodName . ' instead of fluentform_payment_method_render_' . $methodName
            );
            do_action('fluentform/payment_method_render_' . $methodName, $methodElement, $form);
            return;
        }

        $elMarkup = '';

        $hasImageOption = false;

        $defaultValue = $this->extractValueFromAttributes($data);

        if ($defaultValue && !isset($activatedMethods[$defaultValue])) {
            $defaultValue = key($activatedMethods);
        }

        if ($hasImageOption) {
            $data['settings']['container_class'] .= '';
            $elMarkup .= '<div class="ff_el_checkable_photo_holders">';
        }

        $firstTabIndex = \FluentForm\App\Helpers\Helper::getNextTabIndex();

        $selectedMarkups = '';

        foreach ($activatedMethods as $methodName => $method) {
            $parentClass = "ff-el-form-check ff-el-payment-method-label";
            if ($methodName == $defaultValue) {
                $method['is_default'] = true;
                $data['attributes']['checked'] = true;
                $parentClass .= ' ff_item_selected';
            } else {
                $data['attributes']['checked'] = false;
                $method['is_default'] = false;
            }

            if ($firstTabIndex) {
                $data['attributes']['tabindex'] = $firstTabIndex;
                $firstTabIndex = '-1';
            }

            $data['attributes']['value'] = $methodName;
            $data['attributes']['type'] = 'radio';

            $methodLabel = ArrayHelper::get($method, 'settings.option_label.value');

            if ($methodLabel) {
                $method['title'] = $methodLabel;
            }

            $atts = $this->buildAttributes($data['attributes']);

            $id = $this->getUniqueid(str_replace(['[', ']'], ['', ''], $data['attributes']['name']));

            if ($hasImageOption) {
                $parentClass .= ' ff-el-image-holder';
            }

            $elMarkup .= "<div class='{$parentClass}'>";

            $elMarkup .= "<label class='ff-el-form-check-label' for={$id}><input {$atts} id='{$id}'> <span>{$method['title']}</span></label>";
            $elMarkup .= "</div>";
    
            $selectedMarkups .= apply_filters_deprecated(
                'fluentform_payment_method_contents_' . $methodName,
                [
                    '',
                    $method,
                    $data,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/payment_method_contents_' . $methodName,
                'Use fluentform/payment_method_contents_' . $methodName . ' instead of fluentform_payment_method_contents_' . $methodName
            );
            
            $selectedMarkups = apply_filters('fluentform/payment_method_contents_' . $methodName, $selectedMarkups, $method, $data, $form);
        }

        if ($hasImageOption) {
            $elMarkup .= '</div>';
        }

        $elMarkup .= $selectedMarkups;

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
}
