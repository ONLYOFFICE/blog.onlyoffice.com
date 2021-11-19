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
 * Tag class AmpTimeago.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpTimeago extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-TIMEAGO';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Extension::TIMEAGO, SpecRule::ATTRS => [Attribute::CUTOFF => [SpecRule::VALUE_REGEX => '\\d+'], Attribute::DATETIME => [SpecRule::MANDATORY => \true, SpecRule::VALUE_REGEX => '\\d{4}-[01]\\d-[0-3]\\dT[0-2]\\d:[0-5]\\d(:[0-5]\\d(\\.\\d+)?)?(Z|[+-][0-1][0-9]:[0-5][0-9])'], Attribute::LOCALE => [], '[datetime]' => [], '[title]' => []], SpecRule::ATTR_LISTS => [AttributeList\ExtendedAmpGlobal::ID], SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-timeago/', SpecRule::AMP_LAYOUT => [SpecRule::SUPPORTED_LAYOUTS => [Layout::FIXED, Layout::FIXED_HEIGHT, Layout::RESPONSIVE]], SpecRule::HTML_FORMAT => [Format::AMP, Format::AMP4EMAIL], SpecRule::REQUIRES_EXTENSION => [Extension::TIMEAGO]];
}
