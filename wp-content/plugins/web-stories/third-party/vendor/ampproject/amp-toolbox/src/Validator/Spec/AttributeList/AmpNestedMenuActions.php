<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Attribute list class AmpNestedMenuActions.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<string> $ampNestedSubmenuClose
 * @property-read array<string> $ampNestedSubmenuOpen
 */
final class AmpNestedMenuActions extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'amp-nested-menu-actions';
    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [Attribute::AMP_NESTED_SUBMENU_CLOSE => [SpecRule::MANDATORY_ONEOF => [Attribute::AMP_NESTED_SUBMENU_CLOSE, Attribute::AMP_NESTED_SUBMENU_OPEN]], Attribute::AMP_NESTED_SUBMENU_OPEN => [SpecRule::MANDATORY_ONEOF => [Attribute::AMP_NESTED_SUBMENU_CLOSE, Attribute::AMP_NESTED_SUBMENU_OPEN]]];
}
