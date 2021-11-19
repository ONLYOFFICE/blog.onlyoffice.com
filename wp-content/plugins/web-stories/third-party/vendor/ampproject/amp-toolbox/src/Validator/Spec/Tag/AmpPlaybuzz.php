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
 * Tag class AmpPlaybuzz.
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
final class AmpPlaybuzz extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-PLAYBUZZ';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Extension::PLAYBUZZ, SpecRule::ATTRS => [Attribute::DATA_COMMENTS => [SpecRule::VALUE_CASEI => ['false', 'true']], Attribute::DATA_ITEM => [SpecRule::MANDATORY_ONEOF => [Attribute::DATA_ITEM, Attribute::SRC]], Attribute::DATA_ITEM_INFO => [SpecRule::VALUE_CASEI => ['false', 'true']], Attribute::DATA_SHARE_BUTTONS => [SpecRule::VALUE_CASEI => ['false', 'true']], Attribute::SRC => [SpecRule::MANDATORY_ONEOF => [Attribute::DATA_ITEM, Attribute::SRC]]], SpecRule::ATTR_LISTS => [AttributeList\ExtendedAmpGlobal::ID], SpecRule::AMP_LAYOUT => [SpecRule::SUPPORTED_LAYOUTS => [Layout::RESPONSIVE, Layout::FIXED_HEIGHT]], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::REQUIRES_EXTENSION => [Extension::PLAYBUZZ]];
}
