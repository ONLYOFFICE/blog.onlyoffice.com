<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Extension;
use Google\Web_Stories_Dependencies\AmpProject\Format;
use Google\Web_Stories_Dependencies\AmpProject\Protocol;
use Google\Web_Stories_Dependencies\AmpProject\Tag as Element;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class NoscriptImg.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read string $mandatoryAncestor
 * @property-read string $mandatoryAncestorSuggestedAlternative
 * @property-read array<string> $htmlFormat
 * @property-read string $descriptiveName
 */
final class NoscriptImg extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'noscript > img';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::IMG, SpecRule::SPEC_NAME => 'noscript > img', SpecRule::ATTRS => [Attribute::ALT => [], Attribute::ATTRIBUTION => [], Attribute::BORDER => [], Attribute::DECODING => [SpecRule::VALUE => ['async', 'auto', 'sync']], Attribute::HEIGHT => [], Attribute::IMPORTANCE => [], Attribute::ISMAP => [], Attribute::INTRINSICSIZE => [], Attribute::LOADING => [], Attribute::LONGDESC => [SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin', SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTP, Protocol::HTTPS]]], Attribute::SIZES => [], Attribute::WIDTH => []], SpecRule::ATTR_LISTS => [AttributeList\MandatorySrcOrSrcset::ID], SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-img/', SpecRule::MANDATORY_ANCESTOR => Element::NOSCRIPT, SpecRule::MANDATORY_ANCESTOR_SUGGESTED_ALTERNATIVE => Extension::IMG, SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::DESCRIPTIVE_NAME => 'img'];
}
