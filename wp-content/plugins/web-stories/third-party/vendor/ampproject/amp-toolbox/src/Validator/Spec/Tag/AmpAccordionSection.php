<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Extension;
use Google\Web_Stories_Dependencies\AmpProject\Format;
use Google\Web_Stories_Dependencies\AmpProject\Tag as Element;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class AmpAccordionSection.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array $attrs
 * @property-read array $childTags
 * @property-read array<string> $htmlFormat
 * @property-read string $descriptiveName
 */
final class AmpAccordionSection extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-accordion > section';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::SECTION, SpecRule::SPEC_NAME => 'amp-accordion > section', SpecRule::MANDATORY_PARENT => Extension::ACCORDION, SpecRule::ATTRS => [Attribute::ACCESS_HIDE => [SpecRule::VALUE => [''], SpecRule::DISABLED_BY => [Attribute::AMP4EMAIL]], Attribute::EXPANDED => [SpecRule::VALUE => ['']], '[data-expand]' => [], '[expanded]' => [SpecRule::DISABLED_BY => [Attribute::AMP4EMAIL]]], SpecRule::CHILD_TAGS => [SpecRule::MANDATORY_NUM_CHILD_TAGS => 2, SpecRule::FIRST_CHILD_TAG_NAME_ONEOF => ['H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'HEADER']], SpecRule::HTML_FORMAT => [Format::AMP, Format::AMP4ADS, Format::AMP4EMAIL], SpecRule::DESCRIPTIVE_NAME => 'amp-accordion > section'];
}
