<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Format;
use Google\Web_Stories_Dependencies\AmpProject\Tag as Element;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class MetaNameAmpAdDoubleclickSra.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read array<string> $htmlFormat
 */
final class MetaNameAmpAdDoubleclickSra extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'meta name=amp-ad-doubleclick-sra';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::META, SpecRule::SPEC_NAME => 'meta name=amp-ad-doubleclick-sra', SpecRule::MANDATORY_PARENT => Element::HEAD, SpecRule::ATTRS => [Attribute::NAME => [SpecRule::MANDATORY => \true, SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH', SpecRule::VALUE_CASEI => ['amp-ad-doubleclick-sra']]], SpecRule::HTML_FORMAT => [Format::AMP]];
}
