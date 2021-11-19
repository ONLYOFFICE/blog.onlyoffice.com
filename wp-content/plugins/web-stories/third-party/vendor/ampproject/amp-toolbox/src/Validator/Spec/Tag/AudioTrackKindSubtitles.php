<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;

use Google\Web_Stories_Dependencies\AmpProject\Format;
use Google\Web_Stories_Dependencies\AmpProject\Tag as Element;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class AudioTrackKindSubtitles.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array<string> $attrLists
 * @property-read array<string> $htmlFormat
 */
final class AudioTrackKindSubtitles extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'audio > track[kind=subtitles]';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::TRACK, SpecRule::SPEC_NAME => 'audio > track[kind=subtitles]', SpecRule::MANDATORY_PARENT => Element::AUDIO, SpecRule::ATTR_LISTS => [AttributeList\TrackAttrsSubtitles::ID], SpecRule::HTML_FORMAT => [Format::AMP, Format::AMP4ADS]];
}
