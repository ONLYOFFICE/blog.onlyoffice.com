<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;

use Google\Web_Stories_Dependencies\AmpProject\Extension;
use Google\Web_Stories_Dependencies\AmpProject\Format;
use Google\Web_Stories_Dependencies\AmpProject\Layout;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class AmpStoryConsent.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $mandatoryParent
 * @property-read array<string> $attrLists
 * @property-read array<array<string>> $ampLayout
 * @property-read array $childTags
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requires
 * @property-read array<string> $requiresExtension
 */
final class AmpStoryConsent extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-STORY-CONSENT';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Extension::STORY_CONSENT, SpecRule::MANDATORY_PARENT => Extension::CONSENT, SpecRule::ATTR_LISTS => [AttributeList\MandatoryIdAttr::ID], SpecRule::AMP_LAYOUT => [SpecRule::SUPPORTED_LAYOUTS => [Layout::NODISPLAY]], SpecRule::CHILD_TAGS => [SpecRule::MANDATORY_NUM_CHILD_TAGS => 1, SpecRule::CHILD_TAG_NAME_ONEOF => ['SCRIPT']], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::REQUIRES => ['amp-story-consent extension .json script'], SpecRule::REQUIRES_EXTENSION => [Extension::CONSENT, Extension::STORY]];
}
