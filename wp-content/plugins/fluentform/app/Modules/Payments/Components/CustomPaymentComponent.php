<?php

namespace FluentForm\App\Modules\Payments\Components;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Services\FormBuilder\BaseFieldManager;
use FluentForm\App\Services\FormBuilder\Components\Text;
use FluentForm\App\Modules\Payments\PaymentHelper;

class CustomPaymentComponent extends BaseFieldManager
{
    public function __construct(
        $key = 'custom_payment_component',
        $title = 'Custom Payment',
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

        add_filter('fluentform/editor_init_element_custom_payment_component', function ($item) {
            if (!isset($item['settings']['numeric_formatter'])) {
                $item['settings']['numeric_formatter'] = '';
            }
            return $item;
        }, 10, 2);

        add_filter('fluentform/response_render_' . $this->key, function($amount, $field, $form_id, $isHtml = false) {
            if (!$isHtml) {
                return $amount;
            }
            if ($currency = PaymentHelper::getFormCurrency($form_id)) {
                $amount = PaymentHelper::formatMoney(PaymentHelper::convertToCents($amount), $currency);
            }
            return $amount;
        }, 10, 4);
    }

    function getComponent()
    {
        return array(
            'index'          => 6,
            'element'        => $this->key,
            'attributes'     => array(
                'type'              => 'number',
                'name'              => 'custom-payment-amount',
                'value'             => '',
                'id'                => '',
                'class'             => '',
                'placeholder'       => '',
                'data-payment_item' => 'yes'
            ),
            'settings'       => array(
                'container_class'      => '',
                'is_payment_field'     => 'yes',
                'label'                => __('Custom Payment Amount', 'fluentform'),
                'admin_field_label'    => '',
                'label_placement'      => '',
                'help_message'         => '',
                'number_step'          => '',
                'prefix_label'         => '',
                'suffix_label'         => '',
                'validation_rules'     => array(
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
                'conditional_logics'   => array(),
                'calculation_settings' => array(
                    'status'  => false,
                    'formula' => ''
                )
            ),
            'editor_options' => array(
                'title'      => __('Custom Payment Amount', 'fluentform'),
                'icon_class' => 'ff-edit-keyboard-o',
                'template'   => 'inputText'
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
            'validation_rules',
            'numeric_formatter'
        ];
    }

    public function getAdvancedEditorElements()
    {
        return [
            'value',
            'container_class',
            'class',
            'help_message',
            'prefix_label',
            'suffix_label',
            'name',
            'conditional_logics',
            'calculation_settings'
        ];
    }

    function render($data, $form)
    {
        $data['attributes']['class'] .= ' ff_payment_item';
        $data['attributes']['inputmode'] = 'decimal';
        
        return (new Text())->compile($data, $form);
    }
}
