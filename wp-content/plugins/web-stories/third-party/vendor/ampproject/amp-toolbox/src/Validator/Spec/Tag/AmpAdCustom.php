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
 * Tag class AmpAdCustom.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array<array> $attrs
 * @property-read array<string> $attrLists
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $disallowedAncestor
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpAdCustom extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-AD-CUSTOM';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Extension::AD_CUSTOM, SpecRule::ATTRS => [Attribute::SRC => [SpecRule::MANDATORY => \true, SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin', SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTPS], SpecRule::ALLOW_RELATIVE => \false]]], SpecRule::ATTR_LISTS => [AttributeList\ExtendedAmpGlobal::ID], SpecRule::AMP_LAYOUT => [SpecRule::SUPPORTED_LAYOUTS => [Layout::FILL, Layout::FIXED, Layout::FIXED_HEIGHT, Layout::FLEX_ITEM, Layout::FLUID, Layout::INTRINSIC, Layout::NODISPLAY, Layout::RESPONSIVE]], SpecRule::DISALLOWED_ANCESTOR => ['AMP-APP-BANNER'], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::REQUIRES_EXTENSION => [Extension::AD_CUSTOM]];
}
