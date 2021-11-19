<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;

use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Error class AttrMissingRequiredExtension.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class AttrMissingRequiredExtension extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'ATTR_MISSING_REQUIRED_EXTENSION';
    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [SpecRule::FORMAT => 'The attribute \'%1\' requires including the \'%2\' extension JavaScript.', SpecRule::SPECIFICITY => 13];
}
