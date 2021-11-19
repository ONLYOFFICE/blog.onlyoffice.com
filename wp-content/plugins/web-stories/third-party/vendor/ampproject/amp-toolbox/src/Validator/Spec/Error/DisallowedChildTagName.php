<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;

use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Error;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Error class DisallowedChildTagName.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class DisallowedChildTagName extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'DISALLOWED_CHILD_TAG_NAME';
    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [SpecRule::FORMAT => 'Tag \'%1\' is disallowed as child of tag \'%2\'. Child tag must be one of %3.', SpecRule::SPECIFICITY => 75];
}
