<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;

use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Error class StylesheetAndInlineStyleTooLong.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class StylesheetAndInlineStyleTooLong extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'STYLESHEET_AND_INLINE_STYLE_TOO_LONG';
    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [SpecRule::FORMAT => 'The author stylesheet specified in tag \'style amp-custom\' and the combined inline styles is too large - document contains %1 bytes whereas the limit is %2 bytes.', SpecRule::SPECIFICITY => 34];
}
