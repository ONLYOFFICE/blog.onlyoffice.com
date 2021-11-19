<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;

use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Error class CssSyntaxMissingSelector.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class CssSyntaxMissingSelector extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'CSS_SYNTAX_MISSING_SELECTOR';
    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [SpecRule::FORMAT => 'CSS syntax error in tag \'%1\' - missing selector.', SpecRule::SPECIFICITY => 66];
}
