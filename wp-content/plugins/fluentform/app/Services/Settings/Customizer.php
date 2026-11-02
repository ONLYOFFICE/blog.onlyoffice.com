<?php

namespace FluentForm\App\Services\Settings;

use Exception;
use FluentForm\App\Models\FormMeta;
use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Support\Arr;

class Customizer
{
    public function get($formId, $metaKeys = ['_custom_form_css', '_custom_form_js'])
    {
        $result = [];
        
        foreach ($metaKeys as $metaKey) {
            $value = Helper::getFormMeta($formId, $metaKey, '');
            
            // If the meta doesn't exist or is empty, skip it
            if ($value === '' || $value === null) {
                continue;
            }

            if ($metaKey === '_custom_form_css') {
                $result['css'] = $value;
            } elseif ($metaKey === '_ff_selected_style') {
                $result['styler_theme'] = $value;
            } elseif ($metaKey === '_ff_form_styles') {
                $result['styler_styles'] = $value;
            } elseif ($metaKey === '_custom_form_js') {
                $result['js'] = $value;
            } else {
                $result[$metaKey] = $value;
            }
        }
        
        return $result;
    }

    public function store($attributes = [])
    {
        if (!fluentformCanUnfilteredHTML()) {
            throw new Exception(
                esc_html__('You need unfiltered_html permission to save Custom CSS & JS', 'fluentform')
            );
        }

        $formId = absint(Arr::get($attributes, 'form_id'));

        $css = fluentformSanitizeCSS(Arr::get($attributes, 'css'));
        $js = fluentform_kses_js(Arr::get($attributes, 'js'));

        FormMeta::persist($formId, '_custom_form_css', $css);
        FormMeta::persist($formId, '_custom_form_js', $js);
    }
}
