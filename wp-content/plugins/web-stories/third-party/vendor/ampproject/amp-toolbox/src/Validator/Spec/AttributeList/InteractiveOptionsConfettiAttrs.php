<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
/**
 * Attribute list class InteractiveOptionsConfettiAttrs.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $option1Confetti
 * @property-read array $option2Confetti
 * @property-read array $option3Confetti
 * @property-read array $option4Confetti
 */
final class InteractiveOptionsConfettiAttrs extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'interactive-options-confetti-attrs';
    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [Attribute::OPTION_1_CONFETTI => [], Attribute::OPTION_2_CONFETTI => [], Attribute::OPTION_3_CONFETTI => [], Attribute::OPTION_4_CONFETTI => []];
}
