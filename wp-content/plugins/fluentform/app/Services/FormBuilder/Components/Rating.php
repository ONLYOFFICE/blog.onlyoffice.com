<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Services\FormBuilder\RatingIcon;
use FluentForm\Framework\Helpers\ArrayHelper;

class Rating extends BaseComponent
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

        $data['attributes']['type'] = 'radio';

        $defaultValues = (array) $this->extractValueFromAttributes($data);

        $iconSettings = RatingIcon::resolveSettings($data);
        $iconSvg = RatingIcon::getResolvedIconSvg($data, [
            'class' => 'ff-rating-icon-svg jss-ff-svg ff-svg',
        ]);
        $ratingStyles = sprintf(
            '--ff-rating-inactive-color: %s; --ff-rating-active-color: %s;',
            esc_attr($iconSettings['inactive_color']),
            esc_attr($iconSettings['active_color'])
        );

        $firstTabIndex = Helper::getNextTabIndex();
        $elMarkup = "<div class='ff-el-ratings jss-ff-el-ratings' role='radiogroup' style='" . $ratingStyles . "' data-rating-icon-source='" . esc_attr($iconSettings['icon_source']) . "' data-rating-icon-type='" . esc_attr($iconSettings['icon_type']) . "' data-base-tabindex='" . esc_attr($firstTabIndex ?: 0) . "'>";
        $ratingText = '';
        $selectedValues = array_filter(array_map('strval', $defaultValues), function ($value) {
            return $value !== '';
        });
        $hasSelectedValue = !empty($selectedValues);
        $optionIndex = 0;

        foreach ((array) ArrayHelper::get($data, 'options', []) as $value => $label) {
            $starred = '';
            $isSelected = in_array((string) $value, $selectedValues, true);

            if ($isSelected) {
                $data['attributes']['checked'] = true;
                $starred = 'active';
            } else {
                $data['attributes']['checked'] = false;
            }

            $data['attributes']['tabindex'] = -1;
            $atts = $this->buildAttributes($data['attributes']);
            $id = esc_attr($this->getUniqueId(str_replace(['[', ']'], ['', ''], $data['attributes']['name'])));

            $ariaRequired = 'false';
            if (ArrayHelper::get($data, 'settings.validation_rules.required.value')) {
                $ariaRequired = 'true';
            }

            $labelTabIndex = -1;
            if ($isSelected || (!$hasSelectedValue && $optionIndex === 0)) {
                $labelTabIndex = $firstTabIndex ?: 0;
            }

            $checked = $isSelected ? 'true' : 'false';
            $elMarkup .= "<label for={$id} class='{$starred}' role='radio' aria-checked='{$checked}' aria-label='" . esc_attr(wp_strip_all_tags($label)) . "' tabindex='" . esc_attr($labelTabIndex) . "'><input {$atts} id={$id} aria-valuenow='" . esc_attr($value) . "' value='" . esc_attr($value) . "' aria-required={$ariaRequired} aria-invalid='false'>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $starred, $atts, $id are escaped before being passed in.
            $elMarkup .= "<span class='ff-rating-icon' aria-hidden='true'>{$iconSvg}</span>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is sanitized before output.
            $elMarkup .= '</label>';

            if ('yes' == ArrayHelper::get($data, 'settings.show_text')) {
                $displayDefaultText = $isSelected ? 'display: inline-block' : 'display: none';
                $ratingText .= "<span style='{$displayDefaultText}' class='ff-el-rating-text' data-id='{$id}'>" . fluentform_sanitize_html($label) . '</span>';
            }

            $optionIndex++;
        };

        $elMarkup .= '</div>' . $ratingText;

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
}
