<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Format;
use Google\Web_Stories_Dependencies\AmpProject\Protocol;
use Google\Web_Stories_Dependencies\AmpProject\Tag as Element;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class LinkRelPreload.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<array> $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<string> $disallowedAncestor
 * @property-read array<string> $htmlFormat
 * @property-read string $descriptiveName
 */
final class LinkRelPreload extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'link rel=preload';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::LINK, SpecRule::SPEC_NAME => 'link rel=preload', SpecRule::ATTRS => [Attribute::AS_ => [], Attribute::HREF => [], Attribute::REL => [SpecRule::MANDATORY => \true, SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH', SpecRule::VALUE_CASEI => ['preload']], Attribute::IMAGESRCSET => [SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin', SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTP, Protocol::HTTPS]]], Attribute::IMAGESIZES => []], SpecRule::ATTR_LISTS => [AttributeList\CommonLinkAttrs::ID], SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#html-tags', SpecRule::DISALLOWED_ANCESTOR => ['TEMPLATE'], SpecRule::HTML_FORMAT => [Format::AMP, Format::AMP4ADS], SpecRule::DESCRIPTIVE_NAME => 'link rel=preload'];
}
