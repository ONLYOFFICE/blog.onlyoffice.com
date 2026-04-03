<?php

namespace FluentForm\App\Modules\Form;

defined('ABSPATH') or die;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;

class DefaultStyleApplicator
{
    public function __construct()
    {
        add_action('fluentform/inserted_new_form', [$this, 'applyDefaultStyles'], 10, 2);
    }
    
    /**
     * Apply default style template to newly created forms
     *
     * @param int $formId     The ID of the newly created form
     * @param array $formData The form data
     *
     * @return void
     */
    public function applyDefaultStyles($formId, $formData)
    {
        $defaultTemplate = get_option('_fluentform_default_style_template');
        
        if (!$defaultTemplate || !is_array($defaultTemplate) || Arr::get($defaultTemplate, 'enabled') !== 'yes') {
            return;
        }
        
        $customCss = Arr::get($defaultTemplate, 'custom_css', '');
        if ($customCss) {
            // Replace FF_ID and {form_id} placeholders with actual form ID
            $customCss = str_replace('{form_id}', $formId, $customCss);
            $customCss = str_replace('FF_ID', $formId, $customCss);
            Helper::setFormMeta($formId, '_custom_form_css', $customCss);
        }
        
        $stylerEnabled = Arr::get($defaultTemplate, 'styler_enabled', 'no');
        $stylerTheme = Arr::get($defaultTemplate, 'styler_theme', '');
        if ($stylerTheme) {
            Helper::setFormMeta($formId, '_ff_selected_style', $stylerTheme);
        }
        
        if ($stylerEnabled === 'yes') {
            if ($stylerTheme) {
                Helper::setFormMeta($formId, '_ff_selected_style', $stylerTheme);
            }
            
            $stylerStyles = Arr::get($defaultTemplate, 'styler_styles', []);
            if ($stylerStyles && is_array($stylerStyles) && !empty($stylerStyles)) {
                Helper::setFormMeta($formId, '_ff_form_styles', $stylerStyles);
            } elseif ($stylerTheme) {
                $presets = [];
                if (class_exists('\FluentFormPro\classes\FormStyler')) {
                    $formStyler = new \FluentFormPro\classes\FormStyler();
                    $presets = $formStyler->getPresets();
                } else {
                    $presets = [
                        'ffs_default'       => [
                            'label' => __('Default', 'fluentform'),
                            'style' => '[]',
                        ],
                        'ffs_inherit_theme' => [
                            'label' => __('Inherit Theme Style', 'fluentform'),
                            'style' => '{}',
                        ],
                    ];
                }
                
                if (isset($presets[$stylerTheme])) {
                    $styles = json_decode($presets[$stylerTheme]['style'], true);
                    if ($styles) {
                        Helper::setFormMeta($formId, '_ff_form_styles', $styles);
                    }
                }
            }
        }
    }
}

