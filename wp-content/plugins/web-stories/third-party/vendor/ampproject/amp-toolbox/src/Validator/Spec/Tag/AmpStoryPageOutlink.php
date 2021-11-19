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
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class AmpStoryPageOutlink.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read string $mandatoryAncestor
 * @property-read array $childTags
 * @property-read array<string> $htmlFormat
 * @property-read bool $mandatoryLastChild
 */
final class AmpStoryPageOutlink extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-story-page-outlink';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Extension::STORY_PAGE_OUTLINK, SpecRule::SPEC_NAME => 'amp-story-page-outlink', SpecRule::ATTRS => [Attribute::CTA_ACCENT_COLOR => [], Attribute::CTA_ACCENT_ELEMENT => [SpecRule::VALUE => ['background', 'text']], Attribute::CTA_IMAGE => [SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTP, Protocol::HTTPS]]], Attribute::LAYOUT => [SpecRule::MANDATORY => \true, SpecRule::VALUE => ['nodisplay']], Attribute::THEME => [SpecRule::VALUE => ['custom', 'dark', 'light']]], SpecRule::MANDATORY_ANCESTOR => Extension::STORY_PAGE, SpecRule::CHILD_TAGS => [SpecRule::MANDATORY_NUM_CHILD_TAGS => 1, SpecRule::CHILD_TAG_NAME_ONEOF => ['A']], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::MANDATORY_LAST_CHILD => \true];
}
