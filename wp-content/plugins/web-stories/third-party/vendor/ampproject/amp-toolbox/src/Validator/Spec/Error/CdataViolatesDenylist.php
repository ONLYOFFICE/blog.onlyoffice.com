<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;

use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Error class CdataViolatesDenylist.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class CdataViolatesDenylist extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'CDATA_VIOLATES_DENYLIST';
    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [SpecRule::FORMAT => 'The text inside tag \'%1\' contains \'%2\', which is disallowed.', SpecRule::SPECIFICITY => 2];
}
