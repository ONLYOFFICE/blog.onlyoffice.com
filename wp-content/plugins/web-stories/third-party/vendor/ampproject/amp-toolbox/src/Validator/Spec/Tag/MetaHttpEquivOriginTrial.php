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
 * Tag class MetaHttpEquivOriginTrial.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read string $specUrl
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 */
final class MetaHttpEquivOriginTrial extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'meta http-equiv=origin-trial';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::META, SpecRule::SPEC_NAME => 'meta http-equiv=origin-trial', SpecRule::ATTRS => [Attribute::CONTENT => [SpecRule::MANDATORY => \true], Attribute::HTTP_EQUIV => [SpecRule::MANDATORY => \true, SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH', SpecRule::VALUE_CASEI => ['origin-trial']]], SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#html-tags', SpecRule::MANDATORY_ANCESTOR => Element::HEAD, SpecRule::HTML_FORMAT => [Format::AMP, Format::AMP4ADS]];
}
