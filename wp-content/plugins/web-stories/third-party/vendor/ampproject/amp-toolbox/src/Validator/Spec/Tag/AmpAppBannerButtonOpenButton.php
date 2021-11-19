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
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class AmpAppBannerButtonOpenButton.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $satisfies
 */
final class AmpAppBannerButtonOpenButton extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-app-banner button[open-button]';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::BUTTON, SpecRule::SPEC_NAME => 'amp-app-banner button[open-button]', SpecRule::ATTRS => [Attribute::OPEN_BUTTON => [SpecRule::VALUE => ['']], Attribute::ROLE => [SpecRule::IMPLICIT => \true], Attribute::TABINDEX => [SpecRule::IMPLICIT => \true], Attribute::TYPE => [], Attribute::VALUE => []], SpecRule::ATTR_LISTS => [AttributeList\NameAttr::ID], SpecRule::MANDATORY_ANCESTOR => Extension::APP_BANNER, SpecRule::HTML_FORMAT => [Format::AMP, Format::AMP4ADS], SpecRule::SATISFIES => ['amp-app-banner button[open-button]']];
}
