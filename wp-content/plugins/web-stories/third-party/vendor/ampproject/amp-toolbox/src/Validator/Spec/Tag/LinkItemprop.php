<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Format;
use Google\Web_Stories_Dependencies\AmpProject\Tag as Element;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class LinkItemprop.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<array<bool>> $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<string> $htmlFormat
 * @property-read string $descriptiveName
 */
final class LinkItemprop extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'link itemprop=';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::LINK, SpecRule::SPEC_NAME => 'link itemprop=', SpecRule::ATTRS => [Attribute::HREF => [SpecRule::MANDATORY => \true], Attribute::ITEMPROP => [SpecRule::MANDATORY => \true]], SpecRule::ATTR_LISTS => [AttributeList\CommonLinkAttrs::ID], SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#html-tags', SpecRule::HTML_FORMAT => [Format::AMP, Format::AMP4ADS], SpecRule::DESCRIPTIVE_NAME => 'link itemprop='];
}
