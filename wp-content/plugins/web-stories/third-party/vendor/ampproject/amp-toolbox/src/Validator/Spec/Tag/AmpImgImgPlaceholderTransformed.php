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
 * Tag class AmpImgImgPlaceholderTransformed.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read array<string> $attrLists
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $enabledBy
 */
final class AmpImgImgPlaceholderTransformed extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-img > img[placeholder] (transformed)';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::IMG, SpecRule::SPEC_NAME => 'amp-img > img[placeholder] (transformed)', SpecRule::MANDATORY_PARENT => 'amp-img (transformed)', SpecRule::ATTRS => [Attribute::ALT => [], Attribute::ATTRIBUTION => [], Attribute::CLASS_ => [SpecRule::MANDATORY => \true, SpecRule::VALUE => ['i-amphtml-blurry-placeholder']], Attribute::OBJECT_FIT => [], Attribute::OBJECT_POSITION => [], Attribute::PLACEHOLDER => [SpecRule::MANDATORY => \true, SpecRule::VALUE => [''], SpecRule::DISPATCH_KEY => 'NAME_DISPATCH'], Attribute::REFERRERPOLICY => [], Attribute::SIZES => [], Attribute::TITLE => []], SpecRule::ATTR_LISTS => [AttributeList\MandatorySrcOrSrcset::ID], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::ENABLED_BY => [Attribute::TRANSFORMED]];
}
