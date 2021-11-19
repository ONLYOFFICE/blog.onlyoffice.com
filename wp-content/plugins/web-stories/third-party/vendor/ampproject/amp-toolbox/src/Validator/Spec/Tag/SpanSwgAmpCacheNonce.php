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
 * Tag class SpanSwgAmpCacheNonce.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<array> $attrs
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $satisfies
 * @property-read array<string> $requires
 * @property-read array<string> $requiresExtension
 */
final class SpanSwgAmpCacheNonce extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'span swg_amp_cache_nonce';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::SPAN, SpecRule::SPEC_NAME => 'span swg_amp_cache_nonce', SpecRule::ATTRS => [Attribute::SWG_AMP_CACHE_NONCE => [SpecRule::MANDATORY => \true, SpecRule::DISPATCH_KEY => 'NAME_DISPATCH']], SpecRule::MANDATORY_ANCESTOR => Element::BODY, SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::SATISFIES => ['span swg_amp_cache_nonce'], SpecRule::REQUIRES => ['subscriptions-section content swg_amp_cache_nonce'], SpecRule::REQUIRES_EXTENSION => [Extension::SUBSCRIPTIONS]];
}
