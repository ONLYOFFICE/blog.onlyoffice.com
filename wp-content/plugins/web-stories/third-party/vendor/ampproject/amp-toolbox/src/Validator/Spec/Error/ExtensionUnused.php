<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;

use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Error class ExtensionUnused.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class ExtensionUnused extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'EXTENSION_UNUSED';
    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [SpecRule::FORMAT => 'The extension \'%1\' was found on this page, but is unused. Please remove this extension.', SpecRule::SPECIFICITY => 15];
}
