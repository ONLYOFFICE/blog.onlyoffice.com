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
 * Tag class AmpMegaMenuNavUlOlLi.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $childTags
 * @property-read array<array> $referencePoints
 * @property-read array<string> $htmlFormat
 * @property-read string $descriptiveName
 */
final class AmpMegaMenuNavUlOlLi extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-MEGA-MENU NAV > UL/OL > LI';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => '$REFERENCE_POINT', SpecRule::SPEC_NAME => 'AMP-MEGA-MENU NAV > UL/OL > LI', SpecRule::CHILD_TAGS => [SpecRule::CHILD_TAG_NAME_ONEOF => ['A', 'BUTTON', 'DIV', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'SPAN'], SpecRule::MANDATORY_MIN_NUM_CHILD_TAGS => 1], SpecRule::REFERENCE_POINTS => [[SpecRule::TAG_SPEC_NAME => 'AMP-MEGA-MENU item-content', SpecRule::UNIQUE => \true], [SpecRule::TAG_SPEC_NAME => 'AMP-MEGA-MENU item-heading', SpecRule::MANDATORY => \true, SpecRule::UNIQUE => \true]], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::DESCRIPTIVE_NAME => 'amp-mega-menu nav > ul/ol > li'];
}
