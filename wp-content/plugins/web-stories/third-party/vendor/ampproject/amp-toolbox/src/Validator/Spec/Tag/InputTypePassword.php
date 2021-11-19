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
 * Tag class InputTypePassword.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 */
final class InputTypePassword extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'INPUT [type=password]';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::INPUT, SpecRule::SPEC_NAME => 'INPUT [type=password]', SpecRule::ATTRS => [Attribute::TYPE => [SpecRule::MANDATORY => \true, SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH', SpecRule::VALUE_CASEI => ['password']], '[type]' => [SpecRule::DISABLED_BY => [Attribute::AMP4EMAIL]]], SpecRule::ATTR_LISTS => [AttributeList\InputCommonAttr::ID, AttributeList\NameAttr::ID], SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-form/', SpecRule::MANDATORY_ANCESTOR => 'FORM [method=POST]', SpecRule::HTML_FORMAT => [Format::AMP]];
}
