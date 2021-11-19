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
use Google\Web_Stories_Dependencies\AmpProject\Protocol;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class AmpScript.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $disallowedAncestor
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpScript extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-SCRIPT';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Extension::SCRIPT, SpecRule::ATTRS => [Attribute::DATA_AMPDEVMODE => [SpecRule::VALUE => ['false'], SpecRule::DISALLOWED_VALUE_REGEX => 'false'], Attribute::MAX_AGE => [SpecRule::VALUE_REGEX => '[0-9]+', SpecRule::TRIGGER => [SpecRule::ALSO_REQUIRES_ATTR => [Attribute::SCRIPT]]], Attribute::NODOM => [SpecRule::VALUE => ['']], Attribute::SANDBOXED => [SpecRule::VALUE => ['']], Attribute::SANDBOX => [], Attribute::SCRIPT => [SpecRule::MANDATORY_ONEOF => [Attribute::SCRIPT, Attribute::SRC], SpecRule::VALUE_ONEOF_SET => 'AMP_SCRIPT_IDS'], Attribute::SRC => [SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin', SpecRule::MANDATORY_ONEOF => [Attribute::SCRIPT, Attribute::SRC], SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTPS], SpecRule::ALLOW_RELATIVE => \false]]], SpecRule::ATTR_LISTS => [AttributeList\ExtendedAmpGlobal::ID], SpecRule::AMP_LAYOUT => [SpecRule::SUPPORTED_LAYOUTS => [Layout::CONTAINER, Layout::FILL, Layout::FIXED, Layout::FIXED_HEIGHT, Layout::FLEX_ITEM, Layout::INTRINSIC, Layout::NODISPLAY, Layout::RESPONSIVE]], SpecRule::DISALLOWED_ANCESTOR => ['AMP-SCRIPT'], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::REQUIRES_EXTENSION => [Extension::SCRIPT]];
}
