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
 * Tag class AmpWebPush.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpWebPush extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-WEB-PUSH';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Extension::WEB_PUSH, SpecRule::ATTRS => [Attribute::HELPER_IFRAME_URL => [SpecRule::MANDATORY => \true, SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTPS], SpecRule::ALLOW_RELATIVE => \false]], Attribute::ID => [SpecRule::MANDATORY => \true, SpecRule::VALUE => ['amp-web-push']], Attribute::PERMISSION_DIALOG_URL => [SpecRule::MANDATORY => \true, SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTPS], SpecRule::ALLOW_RELATIVE => \false]], Attribute::SERVICE_WORKER_URL => [SpecRule::MANDATORY => \true, SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTPS], SpecRule::ALLOW_RELATIVE => \false]], Attribute::SERVICE_WORKER_SCOPE => [SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTPS]]]], SpecRule::ATTR_LISTS => [AttributeList\ExtendedAmpGlobal::ID], SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-web-push/', SpecRule::AMP_LAYOUT => [SpecRule::SUPPORTED_LAYOUTS => [Layout::NODISPLAY]], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::REQUIRES_EXTENSION => [Extension::WEB_PUSH]];
}
