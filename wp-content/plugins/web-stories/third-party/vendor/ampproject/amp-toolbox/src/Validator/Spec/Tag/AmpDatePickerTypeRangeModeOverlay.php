<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Extension;
use Google\Web_Stories_Dependencies\AmpProject\Format;
use Google\Web_Stories_Dependencies\AmpProject\Layout;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class AmpDatePickerTypeRangeModeOverlay.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<array> $attrs
 * @property-read array<string> $attrLists
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpDatePickerTypeRangeModeOverlay extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-date-picker[type=range][mode=overlay]';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Extension::DATE_PICKER, SpecRule::SPEC_NAME => 'amp-date-picker[type=range][mode=overlay]', SpecRule::ATTRS => [Attribute::MODE => [SpecRule::MANDATORY => \true, SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH', SpecRule::VALUE_CASEI => ['overlay']], Attribute::TYPE => [SpecRule::MANDATORY => \true, SpecRule::VALUE_CASEI => ['range']]], SpecRule::ATTR_LISTS => [AttributeList\AmpDatePickerCommonAttributes::ID, AttributeList\AmpDatePickerOverlayModeAttributes::ID, AttributeList\AmpDatePickerRangeTypeAttributes::ID, AttributeList\ExtendedAmpGlobal::ID], SpecRule::AMP_LAYOUT => [SpecRule::SUPPORTED_LAYOUTS => [Layout::CONTAINER, Layout::NODISPLAY]], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::REQUIRES_EXTENSION => [Extension::DATE_PICKER]];
}
