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
 * Tag class AmpYoutube.
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
final class AmpYoutube extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-YOUTUBE';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Extension::YOUTUBE, SpecRule::ATTRS => [Attribute::AUTOPLAY => [], Attribute::LOOP => [], Attribute::CREDENTIALS => [SpecRule::VALUE_CASEI => ['include', 'omit']], Attribute::DATA_LIVE_CHANNELID => [SpecRule::MANDATORY_ONEOF => [Attribute::DATA_LIVE_CHANNELID, Attribute::DATA_VIDEOID], SpecRule::VALUE_REGEX => '[^=/?:]+'], Attribute::DATA_VIDEOID => [SpecRule::MANDATORY_ONEOF => [Attribute::DATA_LIVE_CHANNELID, Attribute::DATA_VIDEOID], SpecRule::VALUE_REGEX => '[^=/?:]+'], Attribute::DOCK => [SpecRule::REQUIRES_EXTENSION => [Extension::VIDEO_DOCKING]], '[data-videoid]' => []], SpecRule::ATTR_LISTS => [AttributeList\ExtendedAmpGlobal::ID, AttributeList\LightboxableElements::ID], SpecRule::AMP_LAYOUT => [SpecRule::SUPPORTED_LAYOUTS => [Layout::FILL, Layout::FIXED, Layout::FIXED_HEIGHT, Layout::FLEX_ITEM, Layout::NODISPLAY, Layout::RESPONSIVE]], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::REQUIRES_EXTENSION => [Extension::YOUTUBE]];
}
