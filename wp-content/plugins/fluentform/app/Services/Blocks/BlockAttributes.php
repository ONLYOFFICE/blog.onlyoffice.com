<?php

namespace FluentForm\App\Services\Blocks;

/**
 * BlockAttributes class for defining Gutenberg block attributes
 * @since 1.0.0
 */
class BlockAttributes
{
    /**
     * Get all attributes for the Fluent Forms Gutenberg block
     *
     * @return array Block attributes configuration
     */
    public static function getAttributes()
    {
        return array_merge(
            self::getFormAttributes(),
            self::getStylesAttribute()
        );
    }

    /**
     * Get form specific attributes
     *
     * @return array Form specific attributes
     */
    public static function getFormAttributes()
    {
        return [
            'formId' => [
                'type' => 'string',
                'default' => '',
            ],
            'className' => [
                'type' => 'string',
                'default' => '',
            ],
            'themeStyle' => [
                'type' => 'string',
                'default' => '',
            ],
            'isConversationalForm' => [
                'type' => 'boolean',
                'default' => false,
            ],
            'customCssClass' => [
                'type' => 'string',
                'default' => '',
            ],
            // Generated block custom styles
            'customCss' => [
                'type' => 'string',
                'default' => '',
            ]
        ];
    }

    /**
     * Get the consolidated styles attribute
     *
     * @return array Styles attribute configuration
     */
    public static function getStylesAttribute()
    {
        return [
            'styles' => [
                'type' => 'object',
                'default' => array_merge(
                    self::getTypographyAttributes(),
                    self::getColorAttributes(),
                    self::getBorderAttributes(),
                    self::getBoxShadowAttributes(),
                    self::getBackgroundAttributes(),
                    self::getSpacingAttributes(),
                    self::getButtonAttributes(),
                    self::getMessageAttributes(),
                    self::getAdvancedAttributes()
                ),
            ],
        ];
    }

    public static function getBoxShadowAttributes() {
        return [
            // Input box shadow
            'inputBoxShadow' => [
                'enable' => false,
                'color' => '',
                'position' => 'outline',
                'horizontal' => ['value' => '0', 'unit' => 'px'],
                'vertical' => ['value' => '0', 'unit' => 'px'],
                'blur' => ['value' => '5', 'unit' => 'px'],
                'spread' => ['value' => '0', 'unit' => 'px'],
            ],
            // Input box shadow focus
            'inputBoxShadowFocus' => [
                'enable' => false,
                'color' => '',
                'position' => 'outline',
                'horizontal' => ['value' => '0', 'unit' => 'px'],
                'vertical' => ['value' => '0', 'unit' => 'px'],
                'blur' => ['value' => '5', 'unit' => 'px'],
                'spread' => ['value' => '0', 'unit' => 'px'],
            ],
            // Container box shadow
            'containerBoxShadow' => [
                'enable' => false,
                'color' => '',
                'position' => 'outline',
                'horizontal' => ['value' => '0', 'unit' => 'px'],
                'vertical' => ['value' => '0', 'unit' => 'px'],
                'blur' => ['value' => '5', 'unit' => 'px'],
                'spread' => ['value' => '0', 'unit' => 'px'],
            ],
            // Button box shadow
            'buttonBoxShadow' => [
                'enable' => false,
                'color' => '',
                'position' => 'outline',
                'horizontal' => ['value' => '0', 'unit' => 'px'],
                'vertical' => ['value' => '0', 'unit' => 'px'],
                'blur' => ['value' => '5', 'unit' => 'px'],
                'spread' => ['value' => '0', 'unit' => 'px'],
            ],
            // Button hover box shadow
            'buttonHoverBoxShadow' => [
                'enable' => false,
                'color' => '',
                'position' => 'outline',
                'horizontal' => ['value' => '0', 'unit' => 'px'],
                'vertical' => ['value' => '0', 'unit' => 'px'],
                'blur' => ['value' => '5', 'unit' => 'px'],
                'spread' => ['value' => '0', 'unit' => 'px'],
            ],
        ];
    }

    /**
     * Get typography related attributes
     *
     * @return array Typography related attributes
     */
    public static function getTypographyAttributes()
    {
        return [
            'labelTypography' => [],
            'inputTypography' => [],
            'buttonTypography' => [],
            'placeholderTypography' => [],
            'radioCheckboxTypography' => [],
        ];
    }

    /**
     * Get color related attributes
     *
     * @return array Color related attributes
     */
    public static function getColorAttributes()
    {
        return [
            'backgroundColor' => '',
            'labelColor' => '',
            'inputTextColor' => '',
            'inputBackgroundColor' => '',
            'placeholderColor' => '',
            'placeholderFocusColor' => '',
            'buttonColor' => '',
            'buttonBGColor' => '',
            'buttonHoverColor' => '',
            'buttonHoverBGColor' => '',
            'radioCheckboxItemsColor' => '',
            'radioCheckboxLabelColor' => '',
            'checkboxSize' => '',
            'checkboxBorderColor' => '',
            'checkboxBgColor' => '',
            'checkboxCheckedColor' => '',
            'radioSize' => '',
            'radioBorderColor' => '',
            'radioBgColor' => '',
            'radioCheckedColor' => '',
            'errorMessageColor' => '',
            'errorMessageBgColor' => '',
            'successMessageColor' => '',
            'successMessageBgColor' => '',
            'submitErrorMessageColor' => '',
            'submitErrorMessageBgColor' => '',
            'gradientColor1' => '',
            'gradientColor2' => '',
            'backgroundOverlayColor' => '',
            'boxShadowColor' => '',
            'asteriskColor' => '',
            // Focus state colors
            'inputTextFocusColor' => '',
            'inputBackgroundFocusColor' => '',
            'inputFocusSpacing' => [],
        ];
    }

    /**
     * Get border related attributes
     *
     * @return array Border related attributes
     */
    public static function getBorderAttributes()
    {
        return [
            // Input border
            'inputBorder' => [
                'enable' => false,
                'type' => 'solid',
                'color' => '',
                'width' => [],
                'radius' => [],
            ],
            // Input focus border
            'inputBorderFocus' => [
                'enable' => false,
                'type' => 'solid',
                'color' => '',
                'width' => [],
                'radius' => [],
            ],
            // Form container border
            'formBorder' => [
                'enable' => false,
                'type' => 'solid',
                'color' => '',
                'width' => [],
                'radius' => [],
            ],
            // Button border
            'buttonBorder' => [
                'enable' => false,
                'type' => 'solid',
                'color' => '',
                'width' => [],
                'radius' => [],
            ],
            // Button hover border
            'buttonHoverBorder' => [
                'enable' => false,
                'type' => 'solid',
                'color' => '',
                'width' => [],
                'radius' => [],
            ],
        ];
    }

    /**
     * Get background related attributes
     *
     * @return array Background related attributes
     */
    public static function getBackgroundAttributes()
    {
        return [
            'backgroundType' => 'classic',
            'backgroundImage' => '',
            'backgroundImageId' => 0,
            'backgroundSize' => 'cover',
            'backgroundPosition' => 'center center',
            'backgroundRepeat' => 'no-repeat',
            'backgroundAttachment' => 'scroll',
            'gradientType' => 'linear',
            'gradientAngle' => 90,
            'backgroundOverlayOpacity' => 0.5,
        ];
    }

    /**
     * Get spacing related attributes
     *
     * @return array Spacing related attributes
     */
    public static function getSpacingAttributes()
    {
        return [
            'containerPadding' => [],
            'containerMargin' => [],
            'inputSpacing' => [],
            'inputSpacingHover' => [],
            'buttonPadding' => [],
            'buttonMargin' => [],
            'formWidth' => '',
            'formAlignment' => '',
        ];
    }

    /**
     * Get button related attributes
     *
     * @return array Button related attributes
     */
    public static function getButtonAttributes()
    {
        return [
            // Common button attributes
            'buttonWidth' => '',
            'buttonAlignment' => 'left',

            // Normal state button attributes
            'buttonTypography' => [],
            'buttonPadding' => [],
            'buttonMargin' => [],
            'buttonBorderType' => 'solid',
            'buttonBorderColor' => '',
            'buttonBorderWidth' => [],
            'buttonBorderRadius' => [],

            // Hover state button attributes
            'buttonHoverTypography' => [],
            'buttonHoverPadding' => [],
            'buttonHoverMargin' => [],
            'enableButtonHoverBorder' => false,
            'buttonHoverBorderType' => 'solid',
            'buttonHoverBorderColor' => '',
            'buttonHoverBorderWidth' => [],
            'buttonHoverBorderRadius' => [],
        ];
    }

    /**
     * Get message related attributes
     *
     * @return array Message related attributes
     */
    public static function getMessageAttributes()
    {
        return [
            'errorMessageAlignment' => '',
            'successMessageAlignment' => '',
            'submitErrorMessageAlignment' => '',
        ];
    }

    /**
     * Get advanced attributes
     *
     * @return array Advanced attributes
     */
    public static function getAdvancedAttributes()
    {
        return [
            'radioCheckboxItemsSize' => '',
            'customCss' => '',
            'hideOnDesktop' => false,
            'hideOnTablet' => false,
            'hideOnMobile' => false,
            'zIndex' => '',
        ];
    }
}
