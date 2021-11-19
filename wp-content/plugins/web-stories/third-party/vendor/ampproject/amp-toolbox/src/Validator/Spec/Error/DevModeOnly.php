<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;

use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Error class DevModeOnly.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class DevModeOnly extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'DEV_MODE_ONLY';
    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [SpecRule::FORMAT => 'Tag \'html\' marked with attribute \'data-ampdevmode\'. Validator will suppress errors regarding any other tag with this attribute.', SpecRule::SPECIFICITY => 1000];
}
