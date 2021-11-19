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
 * Tag class AmpListDivFetchError.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 */
final class AmpListDivFetchError extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-LIST DIV [fetch-error]';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::DIV, SpecRule::SPEC_NAME => 'AMP-LIST DIV [fetch-error]', SpecRule::ATTRS => [Attribute::ALIGN => [], Attribute::FETCH_ERROR => [SpecRule::MANDATORY => \true]], SpecRule::MANDATORY_ANCESTOR => Extension::LIST_, SpecRule::HTML_FORMAT => [Format::AMP, Format::AMP4ADS, Format::AMP4EMAIL]];
}
