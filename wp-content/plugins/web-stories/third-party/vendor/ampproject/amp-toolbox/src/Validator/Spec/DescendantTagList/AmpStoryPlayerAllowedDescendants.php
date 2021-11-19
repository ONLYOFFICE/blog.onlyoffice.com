<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\DescendantTagList;

use Google\Web_Stories_Dependencies\AmpProject\Internal;
use Google\Web_Stories_Dependencies\AmpProject\Tag as Element;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\DescendantTagList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
/**
 * Descendant tag list class AmpStoryPlayerAllowedDescendants.
 *
 * @package ampproject/amp-toolbox.
 */
final class AmpStoryPlayerAllowedDescendants extends DescendantTagList implements Identifiable
{
    /**
     * ID of the descendant tag list.
     *
     * @var string
     */
    const ID = 'amp-story-player-allowed-descendants';
    /**
     * Array of descendant tags.
     *
     * @var array<array>
     */
    const DESCENDANT_TAGS = [Element::A, Element::SPAN, Internal::SIZER, Element::IMG];
}
