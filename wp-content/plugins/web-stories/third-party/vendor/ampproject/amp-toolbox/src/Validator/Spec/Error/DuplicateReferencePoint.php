<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;

use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Error class DuplicateReferencePoint.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class DuplicateReferencePoint extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'DUPLICATE_REFERENCE_POINT';
    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [SpecRule::FORMAT => 'The reference point \'%1\' for \'%2\' must be unique but a duplicate was encountered.', SpecRule::SPECIFICITY => 80];
}
