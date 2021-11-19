<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;

use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Error class NonLtsScriptAfterLts.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class NonLtsScriptAfterLts extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'NON_LTS_SCRIPT_AFTER_LTS';
    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [SpecRule::FORMAT => '\'%1\' must use the LTS version to correspond with the first script in the page, which uses LTS.', SpecRule::SPECIFICITY => 18];
}
