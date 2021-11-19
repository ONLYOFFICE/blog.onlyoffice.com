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
 * Tag class AmpDateDisplay.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpDateDisplay extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-DATE-DISPLAY';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Extension::DATE_DISPLAY, SpecRule::ATTRS => [Attribute::DATETIME => [SpecRule::MANDATORY_ONEOF => [Attribute::DATETIME, Attribute::TIMESTAMP_MS, Attribute::TIMESTAMP_SECONDS], SpecRule::VALUE_REGEX => 'now|(\\d{4}-[01]\\d-[0-3]\\d(T[0-2]\\d:[0-5]\\d(:[0-6]\\d(\\.\\d\\d?\\d?)?)?(Z|[+-][0-1]\\d:[0-5]\\d)?)?)'], Attribute::DISPLAY_IN => [SpecRule::VALUE_CASEI => ['utc']], Attribute::OFFSET_SECONDS => [SpecRule::VALUE_REGEX => '-?\\d+'], Attribute::LOCALE => [], Attribute::TEMPLATE => [SpecRule::VALUE_ONEOF_SET => 'TEMPLATE_IDS'], Attribute::TIMESTAMP_MS => [SpecRule::MANDATORY_ONEOF => [Attribute::DATETIME, Attribute::TIMESTAMP_MS, Attribute::TIMESTAMP_SECONDS], SpecRule::VALUE_REGEX => '\\d+'], Attribute::TIMESTAMP_SECONDS => [SpecRule::MANDATORY_ONEOF => [Attribute::DATETIME, Attribute::TIMESTAMP_MS, Attribute::TIMESTAMP_SECONDS], SpecRule::VALUE_REGEX => '\\d+']], SpecRule::ATTR_LISTS => [AttributeList\ExtendedAmpGlobal::ID], SpecRule::AMP_LAYOUT => [SpecRule::SUPPORTED_LAYOUTS => [Layout::FILL, Layout::FIXED, Layout::FIXED_HEIGHT, Layout::FLEX_ITEM, Layout::NODISPLAY, Layout::RESPONSIVE]], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::REQUIRES_EXTENSION => [Extension::DATE_DISPLAY]];
}
