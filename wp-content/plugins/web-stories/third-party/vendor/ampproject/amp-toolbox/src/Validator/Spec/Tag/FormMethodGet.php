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
 * Tag class FormMethodGet.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read array<string> $disallowedAncestor
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class FormMethodGet extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'FORM [method=GET]';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::FORM, SpecRule::SPEC_NAME => 'FORM [method=GET]', SpecRule::ATTRS => [Attribute::ACCEPT => [], Attribute::ACCEPT_CHARSET => [], Attribute::ACTION => [SpecRule::MANDATORY => \true, SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin', SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTPS]]], Attribute::ACTION_XHR => [SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin', SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTPS]]], Attribute::AUTOCOMPLETE => [], Attribute::CUSTOM_VALIDATION_REPORTING => [SpecRule::VALUE => ['as-you-go', 'interact-and-submit', 'show-all-on-submit', 'show-first-on-submit']], Attribute::ENCTYPE => [], Attribute::METHOD => [SpecRule::VALUE_CASEI => ['get']], Attribute::NOVALIDATE => [], Attribute::TARGET => [SpecRule::MANDATORY => \true, SpecRule::VALUE_CASEI => ['_blank', '_top']], Attribute::VERIFY_XHR => [SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin', SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTPS]]]], SpecRule::ATTR_LISTS => [AttributeList\FormNameAttr::ID], SpecRule::DISALLOWED_ANCESTOR => ['AMP-APP-BANNER'], SpecRule::HTML_FORMAT => [Format::AMP, Format::AMP4ADS], SpecRule::REQUIRES_EXTENSION => [Extension::FORM]];
}
