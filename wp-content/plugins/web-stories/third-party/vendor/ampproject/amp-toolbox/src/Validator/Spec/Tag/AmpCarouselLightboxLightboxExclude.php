<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Format;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class AmpCarouselLightboxLightboxExclude.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<array<bool>> $attrs
 * @property-read array<string> $htmlFormat
 */
final class AmpCarouselLightboxLightboxExclude extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-CAROUSEL lightbox [lightbox-exclude]';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => '$REFERENCE_POINT', SpecRule::SPEC_NAME => 'AMP-CAROUSEL lightbox [lightbox-exclude]', SpecRule::ATTRS => [Attribute::LIGHTBOX_EXCLUDE => [SpecRule::MANDATORY => \true]], SpecRule::HTML_FORMAT => [Format::AMP]];
}
