<?php

namespace FluentForm\App\Modules\Payments\Components;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Services\FormBuilder\BaseFieldManager;
use FluentForm\App\Services\FormBuilder\Components\CustomHtml;
use FluentForm\App\Services\FormBuilder\Components\Text;

class PaymentSummaryComponent extends BaseFieldManager
{
    public function __construct(
        $key = 'payment_summary_component',
        $title = 'Payment Summary',
        $tags = ['payment', 'summary', 'cart'],
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


    public function register()
    {
        add_filter('fluentform/editor_components', array($this, 'pushComponent'));
        add_filter('fluentform/editor_element_settings_placement', array($this, 'pushEditorElementPositions'));
        add_filter('fluentform/editor_element_search_tags', array($this, 'pushTags'), 10, 2);
        add_action('fluentform/render_item_' . $this->key, array($this, 'render'), 10, 2);

        add_filter('fluentform/editor_element_customization_settings', function ($settings) {
            if ($customSettings = $this->getEditorCustomizationSettings()) {
                $settings = array_merge($settings, $customSettings);
            }

            return $settings;
        });
        
        add_filter('fluentform/editor_init_element_payment_summary_component', function ($item) {
            if (!isset($item['settings']['show_close_button'])) {
                $item['settings']['show_close_button'] = false;
            }
            return $item;
        });


       // add_filter('fluentform/supported_conditional_fields', array($this, 'pushConditionalSupport'));

    }

    function getComponent()
    {
        return array(
            'index' => 7,
            'element' => $this->key,
            'attributes' => array(),
            'settings' => array(
                'html_codes' => '<p>' . __('Payment Summary will be shown here', 'fluentform') . '</p>',
                'cart_empty_text' => __('No payment items has been selected yet', 'fluentform'),
                'show_close_button' => false,
                'conditional_logics' => array(),
                'container_class' => ''
            ),
            'editor_options' => array(
                'title' => __('Payment Summary', 'fluentform'),
                'icon_class' => 'ff-edit-html',
                'template' => 'customHTML'
            ),
        );
    }

    public function getGeneralEditorElements()
    {
        return [
            'cart_empty_text',
            'show_close_button'
        ];
    }

    public function generalEditorElement()
    {
        return [
            'cart_empty_text' => [
                'template' => 'inputHTML',
                'label' => __('Empty Payment Selected Text', 'fluentform'),
                'help_text' => __('The provided text will show if no payment item is selected yet', 'fluentform'),
                'hide_extra' => 'yes'
            ],
            'show_close_button' => [
                'template' => 'inputCheckbox',
                'options'  => [
                    [
                        'value' => false,
                        'label' => __('Show close button for closing Payment Summary', 'fluentform'),
                    ],
                ]
            ]
        ];
    }

    public function getAdvancedEditorElements()
    {
        return [
            'conditional_logics',
            'container_class'
        ];
    }

    function render($data, $form)
    {
        $fallBack =  $data['settings']['cart_empty_text'];
        $data['settings']['html_codes'] = '<div class="ff_dynamic_value ff_dynamic_payment_summary" data-ref="payment_summary"><div class="ff_payment_summary"></div><div class="ff_payment_summary_fallback">'.$fallBack.'</div></div>';

        add_filter('fluentform/payment_config', function($config, $formId) use ($data) {
            $name = $data['attributes']['name'];

            $config['payment_summary_config'][$name] = [
                'show_close_button' => !empty($data['settings']['show_close_button'])
            ];

            return $config;
        }, 10, 2);
        
        return (new CustomHtml())->compile($data, $form);
    }
}
