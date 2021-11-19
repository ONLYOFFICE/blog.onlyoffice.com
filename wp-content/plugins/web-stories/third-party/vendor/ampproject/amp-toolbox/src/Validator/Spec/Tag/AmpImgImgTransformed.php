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
 * Tag class AmpImgImgTransformed.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $enabledBy
 */
final class AmpImgImgTransformed extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-img > img (transformed)';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::IMG, SpecRule::SPEC_NAME => 'amp-img > img (transformed)', SpecRule::MANDATORY_PARENT => 'amp-img (transformed)', SpecRule::ATTRS => [Attribute::ALT => [], Attribute::ATTRIBUTION => [], Attribute::HEIGHT => [], Attribute::OBJECT_FIT => [], Attribute::OBJECT_POSITION => [], Attribute::REFERRERPOLICY => [], Attribute::SIZES => [], Attribute::TITLE => [], Attribute::WIDTH => [], Attribute::CLASS_ => [SpecRule::MANDATORY => \true, SpecRule::VALUE_REGEX => 'i-amphtml-fill-content\\s+i-amphtml-replaced-content|i-amphtml-replaced-content\\s+i-amphtml-fill-content'], Attribute::DECODING => [SpecRule::MANDATORY => \true, SpecRule::VALUE => ['async']], Attribute::LOADING => [SpecRule::VALUE => ['lazy', 'eager']]], SpecRule::ATTR_LISTS => [AttributeList\MandatorySrcOrSrcset::ID], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::ENABLED_BY => [Attribute::TRANSFORMED]];
}
