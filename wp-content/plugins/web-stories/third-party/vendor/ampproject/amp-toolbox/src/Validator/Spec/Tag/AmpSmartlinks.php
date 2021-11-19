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
 * Tag class AmpSmartlinks.
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
final class AmpSmartlinks extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-SMARTLINKS';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Extension::SMARTLINKS, SpecRule::ATTRS => [Attribute::EXCLUSIVE_LINKS => [SpecRule::VALUE => ['']], Attribute::LINK_ATTRIBUTE => [], Attribute::LINK_SELECTOR => [], Attribute::LINKMATE => [SpecRule::VALUE => ['']], Attribute::NRTV_ACCOUNT_NAME => [SpecRule::MANDATORY => \true]], SpecRule::ATTR_LISTS => [AttributeList\ExtendedAmpGlobal::ID], SpecRule::AMP_LAYOUT => [SpecRule::SUPPORTED_LAYOUTS => [Layout::NODISPLAY]], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::REQUIRES_EXTENSION => [Extension::SMARTLINKS]];
}
