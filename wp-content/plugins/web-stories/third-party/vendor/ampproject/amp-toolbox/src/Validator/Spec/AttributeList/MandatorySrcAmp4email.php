<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Protocol;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
/**
 * Attribute list class MandatorySrcAmp4email.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $src
 */
final class MandatorySrcAmp4email extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'mandatory-src-amp4email';
    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [Attribute::SRC => [SpecRule::MANDATORY => \true, SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin|(.|\\s){{|}}(.|\\s)|^{{.*[^}][^}]$|^[^{][^{].*}}$|^}}|{{$|{{#|{{/|{{\\^', SpecRule::VALUE_URL => [SpecRule::PROTOCOL => [Protocol::HTTPS], SpecRule::ALLOW_RELATIVE => \false]]];
}
