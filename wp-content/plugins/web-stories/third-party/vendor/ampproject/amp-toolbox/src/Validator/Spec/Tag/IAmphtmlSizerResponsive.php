<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Format;
use Google\Web_Stories_Dependencies\AmpProject\Internal;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class IAmphtmlSizerResponsive.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read array<string> $htmlFormat
 * @property-read bool $explicitAttrsOnly
 * @property-read array<string> $enabledBy
 */
final class IAmphtmlSizerResponsive extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'I-AMPHTML-SIZER-RESPONSIVE';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Internal::SIZER, SpecRule::SPEC_NAME => 'I-AMPHTML-SIZER-RESPONSIVE', SpecRule::ATTRS => [Attribute::STYLE => [SpecRule::MANDATORY => \true, SpecRule::DISALLOWED_VALUE_REGEX => '!\\s*important', SpecRule::DISPATCH_KEY => 'NAME_DISPATCH', SpecRule::CSS_DECLARATION => [[SpecRule::NAME => Attribute::DISPLAY, SpecRule::VALUE_CASEI => ['block']], [SpecRule::NAME => Attribute::PADDING_TOP]]], Attribute::I_AMPHTML_DISABLE_AR => [SpecRule::VALUE => ['']]], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::EXPLICIT_ATTRS_ONLY => \true, SpecRule::ENABLED_BY => [Attribute::TRANSFORMED]];
}
