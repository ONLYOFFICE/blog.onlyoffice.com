<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Protocol;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Attribute list class TrackAttrsSubtitles.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<array<string>> $default
 * @property-read array $kind
 * @property-read array $label
 * @property-read array $src
 * @property-read array<bool> $srclang
 */
final class TrackAttrsSubtitles extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'track-attrs-subtitles';
    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [Attribute::DEFAULT_ => [SpecRule::VALUE => ['']], Attribute::KIND => [SpecRule::MANDATORY => \true, SpecRule::VALUE_CASEI => ['subtitles']], Attribute::LABEL => [], Attribute::SRC => [SpecRule::MANDATORY => \true, SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin', SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTPS], SpecRule::ALLOW_RELATIVE => \false]], Attribute::SRCLANG => [SpecRule::MANDATORY => \true]];
}
