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
use Google\Web_Stories_Dependencies\AmpProject\Protocol;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class AmpIframe.
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
final class AmpIframe extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-IFRAME';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Extension::IFRAME, SpecRule::ATTRS => [Attribute::ALLOW => [], Attribute::ALLOWFULLSCREEN => [SpecRule::VALUE => ['']], Attribute::ALLOWPAYMENTREQUEST => [SpecRule::VALUE => ['']], Attribute::ALLOWTRANSPARENCY => [SpecRule::VALUE => ['']], Attribute::FRAMEBORDER => [SpecRule::VALUE => ['0', '1']], Attribute::REFERRERPOLICY => [], Attribute::RESIZABLE => [SpecRule::VALUE => ['']], Attribute::SANDBOX => [], Attribute::SCROLLING => [SpecRule::VALUE => ['auto', 'no', 'yes']], Attribute::TABINDEX => [SpecRule::VALUE_REGEX => '-?\\d+'], Attribute::SRC => [SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin', SpecRule::MANDATORY_ONEOF => [Attribute::SRC, Attribute::SRCDOC], SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::DATA, Protocol::HTTPS], SpecRule::ALLOW_RELATIVE => \true]], Attribute::SRCDOC => [SpecRule::MANDATORY_ONEOF => [Attribute::SRC, Attribute::SRCDOC]], '[src]' => [SpecRule::TRIGGER => [SpecRule::ALSO_REQUIRES_ATTR => [Attribute::SRC]]]], SpecRule::ATTR_LISTS => [AttributeList\ExtendedAmpGlobal::ID], SpecRule::AMP_LAYOUT => [SpecRule::SUPPORTED_LAYOUTS => [Layout::FILL, Layout::FIXED, Layout::FIXED_HEIGHT, Layout::FLEX_ITEM, Layout::INTRINSIC, Layout::NODISPLAY, Layout::RESPONSIVE]], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::REQUIRES_EXTENSION => [Extension::IFRAME]];
}
