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
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class AmpFont.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpFont extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-FONT';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Extension::FONT, SpecRule::ATTRS => [Attribute::FONT_FAMILY => [SpecRule::MANDATORY => \true], Attribute::FONT_STYLE => [], Attribute::FONT_VARIANT => [], Attribute::FONT_WEIGHT => [], Attribute::ON_ERROR_ADD_CLASS => [], Attribute::ON_ERROR_REMOVE_CLASS => [], Attribute::ON_LOAD_ADD_CLASS => [], Attribute::ON_LOAD_REMOVE_CLASS => [], Attribute::TIMEOUT => [SpecRule::VALUE_REGEX => '[0-9]+']], SpecRule::ATTR_LISTS => [AttributeList\ExtendedAmpGlobal::ID], SpecRule::AMP_LAYOUT => [SpecRule::SUPPORTED_LAYOUTS => [Layout::NODISPLAY]], SpecRule::HTML_FORMAT => [Format::AMP, Format::AMP4ADS], SpecRule::REQUIRES_EXTENSION => [Extension::FONT]];
}
