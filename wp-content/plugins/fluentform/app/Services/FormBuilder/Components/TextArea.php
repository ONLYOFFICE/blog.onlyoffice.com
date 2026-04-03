<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;

class TextArea extends BaseComponent
{
    /**
     * Compile and echo the html element
     *
     * @param array     $data [element data]
     * @param \stdClass $form [Form Object]
     *
     * @return void
     */
    public function compile($data, $form)
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

        $textareaValue = $this->extractValueFromAttributes($data);

        $data['attributes']['class'] = trim('ff-el-form-control ' . $data['attributes']['class']);
        $data['attributes']['id'] = $this->makeElementId($data, $form);

        if ($tabIndex = Helper::getNextTabIndex()) {
            $data['attributes']['tabindex'] = $tabIndex;
        }

        $ariaRequired = 'false';
        if (ArrayHelper::get($data, 'settings.validation_rules.required.value')) {
            $ariaRequired = 'true';
        }

        $ariaLabelledBy = 'label_' . ArrayHelper::get($data, 'attributes.id');
        $elMarkup = '<textarea aria-required="' . $ariaRequired . '" aria-labelledby="' . $ariaLabelledBy . '" %s>%s</textarea>';

        $atts = $this->buildAttributes($data['attributes']);

        $elMarkup = sprintf(
            $elMarkup,
            $atts,
            esc_attr($textareaValue)
        ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $atts is escaped before being passed in.

        $elMarkup = $this->buildInputGroup($elMarkup, $data);

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

        $this->printContent('fluentform/rendering_field_html_' . $elementName, $html, $data, $form);
    }

    /**
     * Build input group with prefix/suffix
     *
     * @param string $textarea The textarea element HTML
     * @param array  $data     The field data
     *
     * @return string
     */
    private function buildInputGroup($textarea, $data)
    {
        $prefix = ArrayHelper::get($data, 'settings.prefix_label');
        $suffix = ArrayHelper::get($data, 'settings.suffix_label');

        if ($prefix || $suffix) {
            $wrapperClass = 'ff_input-group';
           
            $wrapper = '<div class="' . $wrapperClass . '">';
            if ($prefix) {
                $wrapper .= '<div class="ff_input-group-prepend"><span class="ff_input-group-text">' . fluentform_sanitize_html($prefix) . '</span></div>';
            }
            $wrapper .= $textarea;
            if ($suffix) {
                $wrapper .= '<div class="ff_input-group-append"><span class="ff_input-group-text">' . fluentform_sanitize_html($suffix) . '</span></div>';
            }
            $wrapper .= '</div>';
            return $wrapper;
        }

        return $textarea;
    }
}
