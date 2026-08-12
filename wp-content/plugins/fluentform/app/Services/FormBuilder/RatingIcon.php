<?php

namespace FluentForm\App\Services\FormBuilder;

use FluentForm\Framework\Helpers\ArrayHelper;

class RatingIcon
{
    const DEFAULT_ICON = 'star';
    const DEFAULT_INACTIVE_COLOR = '#d4d4d4';
    const DEFAULT_ACTIVE_COLOR = '#ffb100';

    public static function getPresetOptions()
    {
        return [
            [
                'value' => 'star',
                'label' => __('Star', 'fluentform'),
            ],
            [
                'value' => 'heart',
                'label' => __('Heart', 'fluentform'),
            ],
            [
                'value' => 'thumb',
                'label' => __('Thumb', 'fluentform'),
            ],
            [
                'value' => 'smile',
                'label' => __('Smile', 'fluentform'),
            ],
            [
                'value' => 'bolt',
                'label' => __('Bolt', 'fluentform'),
            ],
        ];
    }

    public static function getPresetSvgs()
    {
        return [
            'star'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 62 58"><path fill="currentColor" d="M31 44.237L12.19 57.889l7.172-22.108L.566 22.111l23.241-.01L31 0l7.193 22.1 23.24.011-18.795 13.67 7.171 22.108z"/></svg>',
            'heart' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 58"><path fill="currentColor" d="M32 55.8 27.36 51.62C11.52 37.4 1 27.92 1 16.28 1 6.8 8.36 0 17.68 0c5.28 0 10.34 2.4 13.66 6.18C34.66 2.4 39.72 0 45 0 54.32 0 61.68 6.8 61.68 16.28c0 11.64-10.52 21.12-26.36 35.34L32 55.8z"/></svg>',
            'thumb' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M14 10V4.5a2.5 2.5 0 0 0-5 0V10m0 0H5.8c-.99 0-1.8.81-1.8 1.8V18a2 2 0 0 0 2 2h7.4a3 3 0 0 0 2.93-2.36l1.2-5.4A1.8 1.8 0 0 0 15.77 10H14ZM9 10v10"/></svg>',
            'smile' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.8"/><path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" d="M9 14c.75 1 1.8 1.5 3 1.5s2.25-.5 3-1.5"/><circle cx="9" cy="10" r="1" fill="currentColor"/><circle cx="15" cy="10" r="1" fill="currentColor"/></svg>',
            'bolt'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 64"><path fill="currentColor" d="M28.56 0 4 36.22h15.18L12.86 64 44 24.86H28.64L28.56 0z"/></svg>',
        ];
    }

    public static function resolveSettings($field)
    {
        $settings = ArrayHelper::get($field, 'settings');

        if (!is_array($settings)) {
            $settings = ArrayHelper::get($field, 'raw.settings', []);
        }

        if (!is_array($settings)) {
            $settings = is_array($field) ? $field : [];
        }

        return [
            'icon_source'     => static::normalizeIconSource(ArrayHelper::get($settings, 'icon_source')),
            'icon_type'       => static::normalizeIconType(ArrayHelper::get($settings, 'icon_type')),
            'custom_icon_svg' => static::sanitizeCustomSvg(ArrayHelper::get($settings, 'custom_icon_svg')),
            'inactive_color'  => static::sanitizeColor(ArrayHelper::get($settings, 'inactive_color'), static::DEFAULT_INACTIVE_COLOR),
            'active_color'    => static::sanitizeColor(ArrayHelper::get($settings, 'active_color'), static::DEFAULT_ACTIVE_COLOR),
        ];
    }

    public static function sanitizeColor($color, $fallback = '')
    {
        $sanitized = sanitize_hex_color((string) $color);

        if ($sanitized) {
            return $sanitized;
        }

        return $fallback ? sanitize_hex_color($fallback) : '';
    }

    public static function normalizeIconSource($iconSource)
    {
        return sanitize_key($iconSource) === 'custom_svg' ? 'custom_svg' : 'preset';
    }

    public static function normalizeIconType($iconType)
    {
        $iconType = sanitize_key($iconType);
        $presetSvgs = static::getPresetSvgs();

        return isset($presetSvgs[$iconType]) ? $iconType : static::DEFAULT_ICON;
    }

    public static function sanitizeCustomSvg($svg)
    {
        if (!is_string($svg)) {
            return '';
        }

        $svg = trim(preg_replace('/<\?xml.*?\?>/i', '', $svg));

        if (!$svg || stripos($svg, '<svg') !== 0) {
            return '';
        }

        $svg = wp_kses($svg, static::getAllowedSvgTags());

        return stripos($svg, '<svg') === 0 ? $svg : '';
    }

    public static function getResolvedIconSvg($field, $attributes = [])
    {
        $settings = static::resolveSettings($field);

        $svg = $settings['custom_icon_svg'];
        if ($settings['icon_source'] !== 'custom_svg' || !$svg) {
            $svg = static::getPresetSvgs()[$settings['icon_type']];
        }

        return static::applySvgAttributes($svg, $attributes);
    }

    public static function applySvgAttributes($svg, $attributes = [])
    {
        if (!$svg || !preg_match('/<svg\b[^>]*>/i', $svg, $match)) {
            return '';
        }

        $existingTag = $match[0];
        $existingClass = '';
        if (preg_match('/\sclass=("|\')(.*?)\1/i', $existingTag, $classMatch)) {
            $existingClass = $classMatch[2];
        }

        if (isset($attributes['class'])) {
            $attributes['class'] = trim($existingClass . ' ' . $attributes['class']);
        } elseif ($existingClass) {
            $attributes['class'] = $existingClass;
        }

        $attributes = array_merge([
            'focusable'           => 'false',
            'aria-hidden'         => 'true',
            'preserveAspectRatio' => 'xMidYMid meet',
            'preserveaspectratio' => 'xMidYMid meet',
        ], $attributes);

        $attributePairs = [];
        foreach ($attributes as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $attributePairs[] = sprintf('%s="%s"', esc_attr($key), esc_attr($value));
        }

        $tag = preg_replace('/\sclass=("|\')(.*?)\1/i', '', $existingTag);
        $tag = rtrim(substr($tag, 0, -1)) . ' ' . implode(' ', $attributePairs) . '>';

        return preg_replace('/<svg\b[^>]*>/i', $tag, $svg, 1);
    }

    protected static function getAllowedSvgTags()
    {
        $attributes = [
            'class'               => true,
            'd'                   => true,
            'cx'                  => true,
            'cy'                  => true,
            'r'                   => true,
            'rx'                  => true,
            'ry'                  => true,
            'x'                   => true,
            'y'                   => true,
            'x1'                  => true,
            'x2'                  => true,
            'y1'                  => true,
            'y2'                  => true,
            'width'               => true,
            'height'              => true,
            'viewbox'             => true,
            'fill'                => true,
            'fill-rule'           => true,
            'clip-rule'           => true,
            'stroke'              => true,
            'stroke-width'        => true,
            'stroke-linecap'      => true,
            'stroke-linejoin'     => true,
            'stroke-miterlimit'   => true,
            'opacity'             => true,
            'points'              => true,
            'transform'           => true,
            'xmlns'               => true,
            'xmlns:xlink'         => true,
            'aria-hidden'         => true,
            'focusable'           => true,
            'role'                => true,
            'preserveaspectratio' => true,
        ];

        return [
            'svg'      => $attributes,
            'g'        => $attributes,
            'path'     => $attributes,
            'circle'   => $attributes,
            'rect'     => $attributes,
            'polygon'  => $attributes,
            'polyline' => $attributes,
            'line'     => $attributes,
            'ellipse'  => $attributes,
            'title'    => [],
            'desc'     => [],
        ];
    }
}
