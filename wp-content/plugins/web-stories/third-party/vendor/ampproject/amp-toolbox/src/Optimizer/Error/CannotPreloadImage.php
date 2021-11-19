<?php

namespace Google\Web_Stories_Dependencies\AmpProject\Optimizer\Error;

use Google\Web_Stories_Dependencies\AmpProject\Dom\Element;
use Google\Web_Stories_Dependencies\AmpProject\Dom\ElementDump;
use Google\Web_Stories_Dependencies\AmpProject\Optimizer\Error;
/**
 * Optimizer error object for when a hero image cannot be preloaded.
 *
 * @package ampproject/amp-toolbox
 */
final class CannotPreloadImage implements Error
{
    use ErrorProperties;
    const SRCSET_STRING = 'Not preloading the hero image because of the presence of a "srcset" attribute, which ' . 'can currently only be preloaded by Chromium-based browsers ' . '(see https://web.dev/preload-responsive-images/).';
    /**
     * Instantiate a CannotPreloadImage object for an image with a srcset attribute.
     *
     * @param Element|null $element Optional. Image element that has the srcset attribute, or null if no element.
     * @return self
     */
    public static function fromImageWithSrcsetAttribute(Element $element = null)
    {
        $message = self::SRCSET_STRING;
        if ($element !== null) {
            $message .= "\n" . new ElementDump($element);
        }
        return new self($message);
    }
}
