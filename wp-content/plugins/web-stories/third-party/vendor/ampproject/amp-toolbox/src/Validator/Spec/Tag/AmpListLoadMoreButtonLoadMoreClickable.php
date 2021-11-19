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
 * Tag class AmpListLoadMoreButtonLoadMoreClickable.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpListLoadMoreButtonLoadMoreClickable extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-list-load-more button[load-more-clickable]';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::BUTTON, SpecRule::SPEC_NAME => 'amp-list-load-more button[load-more-clickable]', SpecRule::MANDATORY_PARENT => Extension::LIST_LOAD_MORE, SpecRule::ATTRS => [Attribute::DISABLED => [SpecRule::VALUE => ['']], Attribute::LOAD_MORE_CLICKABLE => [SpecRule::MANDATORY => \true, SpecRule::VALUE => [''], SpecRule::DISPATCH_KEY => 'NAME_DISPATCH'], Attribute::ROLE => [SpecRule::IMPLICIT => \true], Attribute::TABINDEX => [SpecRule::IMPLICIT => \true], Attribute::TYPE => [], Attribute::VALUE => []], SpecRule::ATTR_LISTS => [AttributeList\NameAttr::ID], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::REQUIRES_EXTENSION => [Extension::LIST_]];
}
