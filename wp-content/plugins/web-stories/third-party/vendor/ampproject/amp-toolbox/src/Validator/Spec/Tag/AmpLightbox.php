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
 * Tag class AmpLightbox.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpLightbox extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-LIGHTBOX';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Extension::LIGHTBOX, SpecRule::ATTRS => [Attribute::ANIMATE_IN => [SpecRule::VALUE_CASEI => ['fade-in', 'fly-in-bottom', 'fly-in-top']], Attribute::ANIMATION => [SpecRule::VALUE_CASEI => ['fade-in', 'fly-in-bottom', 'fly-in-top'], SpecRule::DISABLED_BY => [Attribute::AMP4EMAIL]], Attribute::CONTROLS => [], Attribute::FROM => [], Attribute::SCROLLABLE => [SpecRule::DISABLED_BY => [Attribute::AMP4EMAIL]], '[open]' => []], SpecRule::ATTR_LISTS => [AttributeList\ExtendedAmpGlobal::ID], SpecRule::AMP_LAYOUT => [SpecRule::SUPPORTED_LAYOUTS => [Layout::NODISPLAY]], SpecRule::HTML_FORMAT => [Format::AMP, Format::AMP4EMAIL], SpecRule::REQUIRES_EXTENSION => [Extension::LIGHTBOX]];
}
