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
 * Attribute list class AmpDatePickerRangeTypeAttributes.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $endDate
 * @property-read array $endInputSelector
 * @property-read array<string> $maximumNights
 * @property-read array<string> $minimumNights
 * @property-read array $startDate
 * @property-read array $startInputSelector
 */
final class AmpDatePickerRangeTypeAttributes extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'amp-date-picker-range-type-attributes';
    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [Attribute::END_DATE => [], Attribute::END_INPUT_SELECTOR => [], Attribute::MAXIMUM_NIGHTS => [SpecRule::VALUE_REGEX => '[0-9]+'], Attribute::MINIMUM_NIGHTS => [SpecRule::VALUE_REGEX => '[0-9]+'], Attribute::START_DATE => [], Attribute::START_INPUT_SELECTOR => []];
}
