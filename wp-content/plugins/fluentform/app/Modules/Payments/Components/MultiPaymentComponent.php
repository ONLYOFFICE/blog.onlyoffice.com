<?php

namespace FluentForm\App\Modules\Payments\Components;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Services\FormBuilder\BaseFieldManager;
use FluentForm\App\Services\FormBuilder\Components\Text;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Payments\PaymentHelper;

class MultiPaymentComponent extends BaseFieldManager
{
    public function __construct(
        $key = 'multi_payment_component',
        $title = 'Multi Payment Item',
        $tags = ['custom', 'payment', 'donation'],
        $position = 'payments'
    )
    {
        parent::__construct(
            $key,
            $title,
            $tags,
            $position
        );

        add_filter('fluentform/editor_init_element_multi_payment_component', function ($element) {
            if(!isset($element['settings']['layout_class'])) {
                $element['settings']['layout_class'] = '';
            }
            return $element;
        });

    }

    function getComponent()
    {
        return array(
            'index' => 8,
            'element' => $this->key,
            'attributes' => array(
                'type' => 'single', // single|radio|checkbox|select
                'name' => 'payment_input',
                'value' => '10',
            ),
            'settings' => array(
                'container_class' => '',
                'label' => __('Payment Item', 'fluentform'),
                'admin_field_label' => '',
                'label_placement' => '',
                'display_type' => '',
                'help_message' => '',
                'is_payment_field' => 'yes',
                'pricing_options' => array(
                    [
                        'label' => 'Payment Item 1',
                        'value' => 10,
                        'image' => ''
                    ]
                ),
                'dynamic_default_value' => '',
                'price_label' => __('Price:', 'fluentform'),
                'enable_quantity' => false,
                'enable_image_input' => false,
                'is_element_lock' => false,
                'layout_class' => '',
                'validation_rules' => array(
                    'required' => array(
                        'value'          => false,
                        'global'         => true,
                        'message'        => Helper::getGlobalDefaultMessage('required'),
                        'global_message' => Helper::getGlobalDefaultMessage('required'),
                    ),
                ),
                'conditional_logics' => array(),
            ),
            'editor_options' => array(
                'title' => __('Payment Item', 'fluentform'),
                'icon_class' => 'ff-edit-shopping-cart',
                'element' => 'input-radio',
                'template' => 'inputMultiPayment'
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
            'pricing_options',
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
            'dynamic_default_value',
            'conditional_logics'
        ];
    }

    function render($data, $form)
    {
        $type = ArrayHelper::get($data, 'attributes.type');

        if(!$this->app){
            $this->app = wpFluentForm();
        }
        if ($type == 'single') {
            $this->renderSingleItem($data, $form);
            return '';
        }

        $this->renderMultiProduct($data, $form);
    }

    public function renderSingleItem($data, $form)
    {
        $elementName = $data['element'];
        $label = ArrayHelper::get($data, 'settings.label');
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

        $priceLabel = ArrayHelper::get($data, 'settings.price_label');
        $productPrice = ArrayHelper::get($data, 'attributes.value');
    
        if ($dynamicValues = $this->extractDynamicValues($data, $form)) {
            $productPrice = ArrayHelper::get($dynamicValues, '0');
        }
        if (!$productPrice || !is_numeric($productPrice)) {
            $productPrice = apply_filters('fluentform/single_payment_item_fallback_value', 0, $data, $form);
        }
        $inputAttributes = [
            'type'               => 'hidden',
            'id'                 => $this->makeElementId($data, $form),
            'name'               => $data['attributes']['name'],
            'value'              => $productPrice,
            'class'              => 'ff_payment_item',
            'data-payment_item'  => 'yes',
            'data-payment_value' => $productPrice,
            'data-payment_label' => $label,
            'data-quantity_remaining' => ArrayHelper::get($data, 'settings.quantity_remaining', false)
        ];
        $money = PaymentHelper::formatMoney(
            $productPrice * 100, PaymentHelper::getFormCurrency($form->id)
        );

        $elMarkup = $input = "<input " . $this->buildAttributes($inputAttributes, $form) . ">";

        $elMarkup .= '<span class="ff_item_price_wrapper"><span class="ff_product_price_label">' . $priceLabel . '</span>';
        $elMarkup .= ' <span class="ff_product_price">' . $money . '</span></span>';

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

        $data['attributes']['class'] = trim(
            'ff-el-form-check-input ' .
            'ff-el-form-check-'.$data['attributes']['type'].' '.
            ArrayHelper::get($data, 'attributes.class')
        );

        if ($data['attributes']['type'] == 'checkbox') {
            $data['attributes']['name'] = $data['attributes']['name'] . '[]';
        }

        $defaultValues = (array)$this->extractValueFromAttributes($data);
        if ($dynamicValues = $this->extractDynamicValues($data, $form)) {
            $defaultValues = $dynamicValues;
        }

        $elMarkup = '';

        $firstTabIndex = \FluentForm\App\Helpers\Helper::getNextTabIndex();

        $formattedOptions = ArrayHelper::get($data, 'settings.pricing_options');

        $formattedOptions = apply_filters_deprecated(
            'fluentform_payment_field_' . $elementName . '_pricing_options',
            [
                $formattedOptions,
                $data,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/payment_field_' . $elementName . '_pricing_options',
            'Use fluentform/payment_field_' . $elementName . '_pricing_options instead of fluentform_payment_field_' . $elementName . '_pricing_options'
        );

        $formattedOptions = apply_filters('fluentform/payment_field_' . $elementName . '_pricing_options', $formattedOptions, $data, $form);

        $hasImageOption = ArrayHelper::get($data, 'settings.enable_image_input');

        $type = ArrayHelper::get($data, 'attributes.type');


        if ($type == 'select') {
            $selectAtts = [
                'name' => $data['attributes']['name'],
                'class' => 'ff-el-form-control ff_payment_item',
                'data-payment_item' => 'yes',
                'data-name' => $data['attributes']['name'],
                'id' => $this->getUniqueid(str_replace(['[', ']'], ['', ''], $data['attributes']['name']))
            ];

            if ($firstTabIndex) {
                $selectAtts['tabindex'] = $firstTabIndex;
            }

            $elMarkup .= '<select ' . $this->buildAttributes($selectAtts, $form) . '>';

            if($placeholder = ArrayHelper::get($data, 'settings.placeholder')) {
                $optionAtts = '';
                foreach ([
                             'value' => "",
                             'data-payment_value' => '',
                             'data-calc_value' => ''
                         ] as $key => $value) {
                    $optionAtts .= $key . '="' . $value . '" ';
                }
                $elMarkup .= '<option '.$optionAtts.'>'.$placeholder.'</option>';
            }

        } else if ($hasImageOption) {
            $data['settings']['container_class'] .= '';
            $elMarkup .= '<div class="ff_el_checkable_photo_holders">';
        }
        $groupId = $this->makeElementId($data, $form);

        foreach ($formattedOptions as $index => $option) {
            $quantityLabel = ArrayHelper::get($option,'quantiy_label');
            if ($type == 'select') {
                if (!$defaultValues && $index == 0) {
                    $checked = true;
                } else {
                    $checked = in_array($option['value'], $defaultValues);
                }
                $optionAtts = $this->buildAttributes([
                    'value' => $option['label'],
                    'data-payment_value' => $option['value'],
                    'data-calc_value' => $option['value'],
                    'selected' => $checked,
                    'disabled' => ArrayHelper::get($option, 'disabled') ? 'disabled' : '',
                    'data-quantity_remaining' => ArrayHelper::get($option, 'quantity_remaining', false)
                ], $form);
                $elMarkup .= '<option ' . $optionAtts . '>' . $option['label'] . $quantityLabel.'</option>';
                continue;
            }

            $displayType = isset($data['settings']['display_type']) ? ' ff-el-form-check-' . $data['settings']['display_type'] : '';
            $parentClass = "ff-el-form-check{$displayType}";

            if (in_array($option['value'], $defaultValues)) {
                $data['attributes']['checked'] = true;
                $parentClass .= ' ff_item_selected';
            } else {
                $data['attributes']['checked'] = false;
            }

            if ($firstTabIndex) {
                $data['attributes']['tabindex'] = $firstTabIndex;
                $firstTabIndex = '-1';
            }

            $data['attributes']['class'] .= ' ff_payment_item';

            $data['attributes']['value'] = $option['label'];
            $data['attributes']['data-payment_value'] = $option['value'];
            $data['attributes']['data-calc_value'] = ArrayHelper::get($option, 'value');
            $data['attributes']['data-group_id'] = $groupId;
            $data['attributes']['disabled'] = ArrayHelper::get($option, 'disabled') ? 'disabled' : '';
            $data['attributes']['data-quantity_remaining'] = ArrayHelper::get($option, 'quantity_remaining', false);

            
            $atts = $this->buildAttributes($data['attributes'], $form);
            $id = $this->getUniqueid(str_replace(['[', ']'], ['', ''], $data['attributes']['name']));

            if ($hasImageOption && !empty($option['image'])) {
                $parentClass .= ' ff-el-image-holder';
            }

            $elMarkup .= "<div class='{$parentClass}'>";
            // Here we can push the visual items
            if ($hasImageOption && !empty($option['image'])) {
                $elMarkup .= "<label style='background-image: url({$option['image']})' class='ff-el-image-input-src' for={$id}></label>";
            }

            $elMarkup .= "<label class='ff-el-form-check-label' for={$id}><input {$atts} id='{$id}'> <span class='ff_plan_title'>{$option['label']}{$quantityLabel}</span></label>";
            $elMarkup .= "</div>";
        }

        if ($type == 'select') {
            $elMarkup .= '</select>';
        } else if ($hasImageOption) {
            $elMarkup .= '</div> ';
        }

        if($layoutClass = ArrayHelper::get($data, 'settings.layout_class')) {
            $data['settings']['container_class'] = $data['settings']['container_class'].' '.$layoutClass;
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
}
