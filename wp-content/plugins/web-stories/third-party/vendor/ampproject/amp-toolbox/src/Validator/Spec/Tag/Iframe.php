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
 * Tag class Iframe.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read string $mandatoryAncestor
 * @property-read string $mandatoryAncestorSuggestedAlternative
 * @property-read array<string> $htmlFormat
 */
final class Iframe extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'IFRAME';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::IFRAME, SpecRule::ATTRS => [Attribute::FRAMEBORDER => [SpecRule::VALUE => ['0', '1']], Attribute::HEIGHT => [], Attribute::REFERRERPOLICY => [], Attribute::RESIZABLE => [SpecRule::VALUE => ['']], Attribute::SANDBOX => [], Attribute::SCROLLING => [SpecRule::VALUE => ['auto', 'yes', 'no']], Attribute::SRC => [SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin', SpecRule::MANDATORY_ONEOF => [Attribute::SRC, Attribute::SRCDOC], SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::DATA, Protocol::HTTPS], SpecRule::ALLOW_RELATIVE => \false]], Attribute::SRCDOC => [SpecRule::MANDATORY_ONEOF => [Attribute::SRC, Attribute::SRCDOC]], Attribute::WIDTH => []], SpecRule::ATTR_LISTS => [AttributeList\NameAttr::ID], SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-iframe/', SpecRule::MANDATORY_ANCESTOR => Element::NOSCRIPT, SpecRule::MANDATORY_ANCESTOR_SUGGESTED_ALTERNATIVE => Extension::IFRAME, SpecRule::HTML_FORMAT => [Format::AMP]];
}
