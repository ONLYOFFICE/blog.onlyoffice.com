<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;

use Google\Web_Stories_Dependencies\AmpProject\Extension;
use Google\Web_Stories_Dependencies\AmpProject\Format;
use Google\Web_Stories_Dependencies\AmpProject\Tag as Element;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class H5AmpNestedMenu.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<string> $attrLists
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 */
final class H5AmpNestedMenu extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'h5 amp-nested-menu';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::H5, SpecRule::SPEC_NAME => 'h5 amp-nested-menu', SpecRule::ATTR_LISTS => [AttributeList\AmpNestedMenuActions::ID], SpecRule::MANDATORY_ANCESTOR => Extension::NESTED_MENU, SpecRule::HTML_FORMAT => [Format::AMP]];
}
