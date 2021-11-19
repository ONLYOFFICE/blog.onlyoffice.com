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
 * Tag class CryptokeysJsonScript.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read bool $unique
 * @property-read string $mandatoryParent
 * @property-read array $attrs
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class CryptokeysJsonScript extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'cryptokeys .json script';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::SCRIPT, SpecRule::SPEC_NAME => 'cryptokeys .json script', SpecRule::UNIQUE => \true, SpecRule::MANDATORY_PARENT => Element::HEAD, SpecRule::ATTRS => [Attribute::CRYPTOKEYS => [SpecRule::MANDATORY => \true, SpecRule::VALUE => [''], SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH'], Attribute::SHA_256_HASH => [SpecRule::MANDATORY => \true], Attribute::TYPE => [SpecRule::MANDATORY => \true, SpecRule::VALUE_CASEI => ['application/json']]], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::REQUIRES_EXTENSION => [Extension::SUBSCRIPTIONS]];
}
