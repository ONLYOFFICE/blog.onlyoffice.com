<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;

use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Error class CssSyntaxMalformedMediaQuery.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class CssSyntaxMalformedMediaQuery extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'CSS_SYNTAX_MALFORMED_MEDIA_QUERY';
    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [SpecRule::FORMAT => 'CSS syntax error in tag \'%1\' - malformed media query.', SpecRule::SPECIFICITY => 117];
}
