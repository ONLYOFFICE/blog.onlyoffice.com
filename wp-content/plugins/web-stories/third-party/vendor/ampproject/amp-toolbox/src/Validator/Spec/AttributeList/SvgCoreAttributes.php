<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
/**
 * Attribute list class SvgCoreAttributes.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $xml:lang
 * @property-read array $xml:space
 * @property-read array $xmlns
 * @property-read array $xmlns:xlink
 */
final class SvgCoreAttributes extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'svg-core-attributes';
    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [Attribute::XML_LANG => [], Attribute::XML_SPACE => [], Attribute::XMLNS => [], Attribute::XMLNS_XLINK => []];
}
