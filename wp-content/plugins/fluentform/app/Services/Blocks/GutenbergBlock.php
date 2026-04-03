<?php

namespace FluentForm\App\Services\Blocks;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Support\Arr;

/**
 * GutenbergBlock class for handling Fluent Forms Gutenberg block functionality
 * @since 1.0.0
 */
class GutenbergBlock
{
    /**
     * Register the Gutenberg block
     * @return void
     */
    public static function register()
    {
        if (!function_exists('register_block_type')) {
            return;
        }

        register_block_type('fluentfom/guten-block', [
            'render_callback' => [self::class, 'render'],
            'attributes'      => BlockAttributes::getAttributes(),
            'api_version'     => 3,
        ]);
    }

    /**
     * Render the Gutenberg block
     *
     * @param array $atts Block attributes
     * @return string Rendered block HTML
     */
    public static function render($atts)
    {
        $formId = (int)Arr::get($atts, 'formId', 0);

        if (!$formId) {
            return '<div class="fluentform-no-form-selected"><p>' . __('Please select a form', 'fluentform') . '</p></div>';
        }

        $className = sanitize_text_field(Arr::get($atts, 'className', ''));
        $themeStyle = sanitize_text_field(Arr::get($atts, 'themeStyle', ''));
        $type = Helper::isConversionForm($formId) ? 'conversational' : '';

        // Custom CSS for block styling
        
        $inlineStyle = '';
        $customCss = Arr::get($atts, 'customCss', '');
        
        $customCss = json_decode($customCss, true);
        $customCss = is_string($customCss) ? $customCss : '';
        
        if ($customCss && $customCss = fluentformSanitizeCSS($customCss)) {
            $styleId = 'fluentform-block-custom-styles-' . $formId;
            $inlineStyle = '<style id="' . esc_attr($styleId) . '">' . $customCss . '</style>';
            if (wp_style_is('fluent-form-styles', 'enqueued') || wp_style_is('fluent-form-styles', 'registered')) {
                wp_add_inline_style('fluent-form-styles', $customCss);
            }
            if (wp_style_is('fluentform-public-default', 'enqueued') || wp_style_is('fluentform-public-default', 'registered')) {
                wp_add_inline_style('fluentform-public-default', $customCss);
            }
        }

        // Return the form with inline styles
        $formOutput = do_shortcode('[fluentform theme="' . $themeStyle . '" css_classes="' . $className . ' ff_guten_block ff_guten_block-' . $formId . '" id="' . $formId . '"  type="' . $type . '"]');

        if ($formOutput) {
            $allStyles = '';
            if ($inlineStyle) {
                $allStyles .= $inlineStyle;
            }
//            if (!empty($individualStyleTag)) {
//                $allStyles .= $individualStyleTag;
//            }
            
            if ($allStyles) {
                return $allStyles . $formOutput;
            }
            return $formOutput;
        }

        return '';
    }
}
