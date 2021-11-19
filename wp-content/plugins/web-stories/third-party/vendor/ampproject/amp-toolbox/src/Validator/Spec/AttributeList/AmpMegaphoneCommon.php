<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Attribute list class AmpMegaphoneCommon.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<array<string>> $dataLight
 * @property-read array<array<string>> $dataSharing
 */
final class AmpMegaphoneCommon extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'amp-megaphone-common';
    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [Attribute::DATA_LIGHT => [SpecRule::VALUE => ['']], Attribute::DATA_SHARING => [SpecRule::VALUE => ['']]];
}
