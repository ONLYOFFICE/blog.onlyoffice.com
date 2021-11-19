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
 * Tag class AmpMegaMenuItemContent.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<array> $attrs
 * @property-read array<string> $htmlFormat
 * @property-read string $descriptiveName
 */
final class AmpMegaMenuItemContent extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-MEGA-MENU item-content';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => '$REFERENCE_POINT', SpecRule::SPEC_NAME => 'AMP-MEGA-MENU item-content', SpecRule::ATTRS => [Attribute::ROLE => [SpecRule::MANDATORY => \true, SpecRule::VALUE => ['dialog']]], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::DESCRIPTIVE_NAME => 'amp-mega-menu item-content'];
}
