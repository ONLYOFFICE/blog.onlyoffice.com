<?php

namespace FluentForm\App\Modules\Payments\Components;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Services\FormBuilder\BaseFieldManager;
use FluentForm\App\Services\FormBuilder\Components\Text;
use FluentForm\Framework\Helpers\ArrayHelper;

class ItemQuantity extends BaseFieldManager
{
    public function __construct(
        $key = 'item_quantity_component',
        $title = 'Quantity',
        $tags = ['custom', 'payment', 'quantity'],
        $position = 'payments'
    )
    {
        parent::__construct(
            $key,
            $title,
            $tags,
            $position
        );
    }

    function getComponent()
    {
        return array(
            'index' => 6,
            'element' => $this->key,
            'attributes' => array(
                'type' => 'number',
                'name' => 'item-quantity',
                'value' => '',
                'id' => '',
                'class' => '',
                'placeholder' => '',
                'data-quantity_item' => 'yes'
            ),
            'settings' => array(
                'container_class' => '',
                'is_payment_field' => 'yes',
                'label' => __('Quantity', 'fluentform'),
                'admin_field_label' => '',
                'label_placement' => '',
                'help_message' => '',
                'number_step' => '',
                'prefix_label' => '',
                'suffix_label' => '',
                'target_product' => '',
                'validation_rules' => array(
                    'required' => array(
                        'value'          => false,
                        'global'         => true,
                        'message'        => Helper::getGlobalDefaultMessage('required'),
                        'global_message' => Helper::getGlobalDefaultMessage('required'),
                    ),
                    'numeric'  => array(
                        'value'          => true,
                        'global'         => true,
                        'message'        => Helper::getGlobalDefaultMessage('numeric'),
                        'global_message' => Helper::getGlobalDefaultMessage('numeric'),
                    ),
                    'min'      => array(
                        'value'          => '',
                        'global'         => true,
                        'message'        => Helper::getGlobalDefaultMessage('min'),
                        'global_message' => Helper::getGlobalDefaultMessage('min'),
                    ),
                    'max'      => array(
                        'value'          => '',
                        'global'         => true,
                        'message'        => Helper::getGlobalDefaultMessage('max'),
                        'global_message' => Helper::getGlobalDefaultMessage('max'),
                    ),
                ),
                'conditional_logics' => array()
            ),
            'editor_options' => array(
                'title' => __('Item Quantity', 'fluentform'),
                'icon_class' => 'ff-edit-keyboard-o',
                'template' => 'inputText'
            ),
        );
    }

    public function getGeneralEditorElements()
    {
        return [
            'label',
            'label_placement',
            'admin_field_label',
            'placeholder',
            'target_product',
            'validation_rules'
        ];
    }

    public function getAdvancedEditorElements()
    {
        return [
            'value',
            'container_class',
            'class',
            'help_message',
            'number_step',
            'prefix_label',
            'suffix_label',
            'name',
            'conditional_logics',
            'calculation_settings'
        ];
    }

    public function getEditorCustomizationSettings()
    {
        return [
            'target_product' => array(
                'template'  => 'targetProduct',
                'label' => __('Product Field Mapping', 'fluentform'),
                'help_text' => __('Select which Product this field is tied to', 'fluentform'),
            )
        ];
    }

    function render($data, $form)
    {
        $data['attributes']['class'] .= ' ff_quantity_item';
        $data['attributes']['data-target_product'] = ArrayHelper::get($data, 'settings.target_product');

        if (ArrayHelper::get($data, 'settings.validation_rules.min.value') == '') {
            ArrayHelper::set($data, 'settings.validation_rules.min.value', 0);
        }

        ArrayHelper::set($data, 'attributes.min', ArrayHelper::get($data, 'settings.validation_rules.min.value'));

        if (ArrayHelper::get($data, 'settings.validation_rules.max.value')) {
            ArrayHelper::set($data, 'attributes.max', ArrayHelper::get($data, 'settings.validation_rules.max.value'));
        }

        return (new Text())->compile($data, $form);
    }
}
