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
 * Tag class AmpConsentType.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read bool $unique
 * @property-read array<array<bool>> $attrs
 * @property-read array<string> $attrLists
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $satisfies
 * @property-read array<string> $requires
 * @property-read array<string> $requiresExtension
 */
final class AmpConsentType extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-consent [type]';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Extension::CONSENT, SpecRule::SPEC_NAME => 'amp-consent [type]', SpecRule::UNIQUE => \true, SpecRule::ATTRS => [Attribute::TYPE => [SpecRule::MANDATORY => \true]], SpecRule::ATTR_LISTS => [AttributeList\ExtendedAmpGlobal::ID], SpecRule::AMP_LAYOUT => [SpecRule::SUPPORTED_LAYOUTS => [Layout::NODISPLAY]], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::SATISFIES => ['amp-consent [type]'], SpecRule::REQUIRES => ['meta name=amp-consent-blocking', 'amp-consent extension .json script'], SpecRule::REQUIRES_EXTENSION => [Extension::CONSENT]];
}
