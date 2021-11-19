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
 * Attribute list class SvgConditionalProcessingAttributes.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $requiredextensions
 * @property-read array $requiredfeatures
 * @property-read array $systemlanguage
 */
final class SvgConditionalProcessingAttributes extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'svg-conditional-processing-attributes';
    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [Attribute::REQUIREDEXTENSIONS => [], Attribute::REQUIREDFEATURES => [], Attribute::SYSTEMLANGUAGE => []];
}
