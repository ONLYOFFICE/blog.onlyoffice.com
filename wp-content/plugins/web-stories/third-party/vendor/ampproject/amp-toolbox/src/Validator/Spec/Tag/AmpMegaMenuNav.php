<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;

use Google\Web_Stories_Dependencies\AmpProject\Format;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class AmpMegaMenuNav.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $childTags
 * @property-read array<array<string>> $referencePoints
 * @property-read array<string> $htmlFormat
 * @property-read bool $siblingsDisallowed
 * @property-read string $descriptiveName
 */
final class AmpMegaMenuNav extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-MEGA-MENU > NAV';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => '$REFERENCE_POINT', SpecRule::SPEC_NAME => 'AMP-MEGA-MENU > NAV', SpecRule::CHILD_TAGS => [SpecRule::MANDATORY_NUM_CHILD_TAGS => 1, SpecRule::CHILD_TAG_NAME_ONEOF => ['OL', 'UL']], SpecRule::REFERENCE_POINTS => [[SpecRule::TAG_SPEC_NAME => 'AMP-MEGA-MENU NAV > UL/OL']], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::SIBLINGS_DISALLOWED => \true, SpecRule::DESCRIPTIVE_NAME => 'amp-mega-menu > nav'];
}
