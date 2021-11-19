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
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class AmpImaVideoSource.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpImaVideoSource extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-ima-video > source';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::SOURCE, SpecRule::SPEC_NAME => 'amp-ima-video > source', SpecRule::MANDATORY_PARENT => Extension::IMA_VIDEO, SpecRule::ATTRS => [Attribute::MEDIA => [], Attribute::SRC => [SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin', SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTPS], SpecRule::ALLOW_RELATIVE => \true]], Attribute::TYPE => [], '[src]' => [], '[type]' => []], SpecRule::HTML_FORMAT => [Format::AMP, Format::AMP4ADS], SpecRule::REQUIRES_EXTENSION => [Extension::IMA_VIDEO]];
}
