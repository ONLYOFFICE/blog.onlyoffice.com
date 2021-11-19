<?php

namespace Google\Web_Stories_Dependencies\AmpProject\Optimizer;

use Google\Web_Stories_Dependencies\AmpProject\Optimizer\Exception\UnknownConfigurationKey;
use Google\Web_Stories_Dependencies\AmpProject\Optimizer\Transformer\AmpBoilerplate;
use Google\Web_Stories_Dependencies\AmpProject\Optimizer\Transformer\AmpBoilerplateErrorHandler;
use Google\Web_Stories_Dependencies\AmpProject\Optimizer\Transformer\AmpRuntimeCss;
use Google\Web_Stories_Dependencies\AmpProject\Optimizer\Transformer\AmpRuntimePreloads;
use Google\Web_Stories_Dependencies\AmpProject\Optimizer\Transformer\GoogleFontsPreconnect;
use Google\Web_Stories_Dependencies\AmpProject\Optimizer\Transformer\OptimizeAmpBind;
use Google\Web_Stories_Dependencies\AmpProject\Optimizer\Transformer\OptimizeHeroImages;
use Google\Web_Stories_Dependencies\AmpProject\Optimizer\Transformer\PreloadHeroImage;
use Google\Web_Stories_Dependencies\AmpProject\Optimizer\Transformer\ReorderHead;
use Google\Web_Stories_Dependencies\AmpProject\Optimizer\Transformer\RewriteAmpUrls;
use Google\Web_Stories_Dependencies\AmpProject\Optimizer\Transformer\ServerSideRendering;
use Google\Web_Stories_Dependencies\AmpProject\Optimizer\Transformer\TransformedIdentifier;
/**
 * Interface for a configuration object that validates and stores configuration settings.
 *
 * @package ampproject/amp-toolbox
 */
interface Configuration
{
    /**
     * Key to use for managing the array of active transformers.
     *
     * @var string
     */
    const KEY_TRANSFORMERS = 'transformers';
    /**
     * Array of known configuration keys and their default values.
     *
     * @var string[]
     */
    const DEFAULTS = [self::KEY_TRANSFORMERS => self::DEFAULT_TRANSFORMERS];
    /**
     * Array of FQCNs of transformers to use for the default setup.
     *
     * @var string[]
     */
    const DEFAULT_TRANSFORMERS = [TransformedIdentifier::class, AmpBoilerplate::class, OptimizeHeroImages::class, ServerSideRendering::class, AmpRuntimeCss::class, AmpRuntimePreloads::class, AmpBoilerplateErrorHandler::class, GoogleFontsPreconnect::class, RewriteAmpUrls::class, ReorderHead::class, OptimizeAmpBind::class];
    /**
     * Register a new configuration class to use for a given transformer.
     *
     * @param string $transformerClass   FQCN of the transformer to register a configuration class for.
     * @param string $configurationClass FQCN of the configuration to use.
     */
    public function registerConfigurationClass($transformerClass, $configurationClass);
    /**
     * Check whether the configuration has a given setting.
     *
     * @param string $key Configuration key to look for.
     * @return bool Whether the requested configuration key was found or not.
     */
    public function has($key);
    /**
     * Get the value for a given key from the configuration.
     *
     * @param string $key Configuration key to get the value for.
     * @return mixed Configuration value for the requested key.
     * @throws UnknownConfigurationKey If the key was not found.
     */
    public function get($key);
    /**
     * Get the transformer-specific configuration for the requested transformer.
     *
     * @param string $transformer FQCN of the transformer to get the configuration for.
     * @return TransformerConfiguration Transformer-specific configuration.
     */
    public function getTransformerConfiguration($transformer);
}
