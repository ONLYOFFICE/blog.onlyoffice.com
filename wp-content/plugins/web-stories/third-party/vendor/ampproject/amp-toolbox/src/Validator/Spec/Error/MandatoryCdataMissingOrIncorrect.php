<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;

use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Error class MandatoryCdataMissingOrIncorrect.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class MandatoryCdataMissingOrIncorrect extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'MANDATORY_CDATA_MISSING_OR_INCORRECT';
    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [SpecRule::FORMAT => 'The mandatory text inside tag \'%1\' is missing or incorrect.', SpecRule::SPECIFICITY => 1];
}
