<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Format;
use Google\Web_Stories_Dependencies\AmpProject\Protocol;
use Google\Web_Stories_Dependencies\AmpProject\Tag as Element;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class AAmp4email.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read array<string> $htmlFormat
 */
final class AAmp4email extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'A (AMP4EMAIL)';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::A, SpecRule::SPEC_NAME => 'A (AMP4EMAIL)', SpecRule::ATTRS => [Attribute::BORDER => [], Attribute::HREF => [SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin|(.|\\s){{|}}(.|\\s)|^{{.*[^}][^}]$|^[^{][^{].*}}$|^}}|{{$|{{#|{{/|{{\\^', SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTP, Protocol::HTTPS, Protocol::MAILTO, Protocol::TEL], SpecRule::ALLOW_RELATIVE => \false]], Attribute::HREFLANG => [], Attribute::MEDIA => [], Attribute::ROLE => [SpecRule::IMPLICIT => \true], Attribute::TABINDEX => [SpecRule::IMPLICIT => \true], Attribute::TARGET => [SpecRule::VALUE => ['_blank']], Attribute::TYPE => [SpecRule::VALUE_CASEI => ['text/html']]], SpecRule::HTML_FORMAT => [Format::AMP4EMAIL]];
}
