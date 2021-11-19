<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;

use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Error class DisallowedManufacturedBody.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class DisallowedManufacturedBody extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'DISALLOWED_MANUFACTURED_BODY';
    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [SpecRule::FORMAT => 'Tag or text which is only allowed inside the body section found outside of the body section.', SpecRule::SPECIFICITY => 106];
}
