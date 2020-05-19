<?php
namespace ILJ\Core;

use ILJ\Backend\MenuPage\Settings;
use ILJ\Core\Options\OptionInterface;
use ILJ\Helper\Options as OptionsHelper;

/**
 * Options Wrapper
 *
 * Holds all the options, which can be configured by the site administrator
 * as well as system related settings
 *
 * @package ILJ\Core
 * @since   1.0.0
 */

class Options
{
    const KEY = 'ilj_options';

    /**
     * Prefixes
     */
    const ILJ_OPTION_PREFIX_PAGE = 'ilj_settings_section_';
    const ILJ_OPTION_PREFIX_ID   = 'ilj_settings_';

    /**
     * Option sections
     */
    const ILJ_OPTION_SECTION_GENERAL = 'general';
    const ILJ_OPTION_SECTION_CONTENT = 'content';
    const ILJ_OPTION_SECTION_LINKS   = 'links';

    /**
     * Other (internal) options
     */
    const ILJ_OPTION_KEY_ENVIRONMENT  = 'ilj_environment';
    const ILJ_OPTION_KEY_INDEX_NOTIFY = 'ilj_option_index_notify';

    private static $instance;

    /**
     * @var   array
     * @since 1.1.3
     */
    private $sections = [];

    /**
     * @var   array
     * @since 1.1.3
     */
    private $keys = [];

    protected static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function __construct()
    {
        $this->sections = [
            self::ILJ_OPTION_SECTION_GENERAL => [
                'options'     => [new Options\KeepSettings(), new Options\EditorRole(), new Options\IndexGeneration()]
            ],
            self::ILJ_OPTION_SECTION_CONTENT => [
                'options'     => [new Options\Whitelist(), new Options\TaxonomyWhitelist(), new Options\Blacklist(), new Options\TermBlacklist(), new Options\KeywordOrder(), new Options\LinksPerPage(), new Options\LinksPerTarget(), new Options\MultipleKeywords(), new Options\NoLinkTags(), new Options\RespectExistingLinks()]
            ],
            self::ILJ_OPTION_SECTION_LINKS   => [
                'options'     => [new Options\LinkOutputInternal(), new Options\InternalNofollow(), new Options\LinkOutputCustom()]
            ]
        ];

        return $this;
    }

    public static function init()
    {
        $options = self::getInstance();

        $options->addSettingsSections()
            ->addOptions();
    }

    /**
     * Retrieves the internal option value with different defaults
     *
     * @since  1.0.0
     * @param  string $option The option value which should be returned
     * @return mixed
     */
    public static function getOption($key)
    {
        $available_keys = self::getInstance()->getKeys();

        switch ($key) {
        case Options\Whitelist::getKey():
        case Options\Blacklist::getKey():
        case Options\NoLinkTags::getKey():
            $value = get_option($key, []);
            if (!is_array($value)) {
                return [];
            }
            return $value;
                break;
        case in_array($key, $available_keys):
            return get_option($key, '');
        default:
            return get_option($key, false);
                break;
        }
    }

    /**
     * Retrieve section data
     *
     * @param  string $section_title The title of the section
     * @since  1.1.3
     * @return array|null
     */
    public static function getSection($section_title)
    {
        foreach(self::getInstance()->sections as $section => $data) {
            if ($section == $section_title) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Sets the value of a plugin option
     *
     * @since  1.0.0
     * @param  string $option The option name
     * @param  string $value  The option value
     * @return bool
     */
    public static function setOption($key, $value)
    {
        $available_keys = self::getInstance()->getKeys();

        if (!in_array($key, $available_keys)) {
            return false;
        }

        return update_option($key, $value);
    }

    /**
     * Sets the default options
     *
     * @since  1.1.0
     * @return void
     */
    public static function setOptionsDefault()
    {
        $options  = self::getInstance();
        $defaults = $options->getDefaults();

        foreach ($defaults as $option => $default) {
            if ("ilj_settings_field_link_output_internal" == $option) {
                $test = null;
            }

            if (is_string($default)) {
                $default = esc_html($default);
            }

            $existant_option = get_option($option, false);

            if (!$existant_option) {
                add_option($option, $default);
            }
        }
    }

    /**
     * Remove all options of the plugin from db
     *
     * @since  1.1.3
     * @return void
     */
    public static function removeAllOptions()
    {
        $options  = self::getInstance();
        foreach ($options->getKeys() as $key) {
            delete_option($key);
        }

        delete_option(self::ILJ_OPTION_KEY_INDEX_NOTIFY);
        delete_option(self::ILJ_OPTION_KEY_ENVIRONMENT);
    }

    /**
     * Get key value pairs of the default for each option
     *
     * @since  1.1.3
     * @return array
     */
    protected function getDefaults()
    {
        $defaults = [];
        foreach ($this->sections as $section) {
            foreach ($section['options'] as $option) {
                global $ilj_fs;

                if ($option->isPro() && (!$ilj_fs->is__premium_only() || !$ilj_fs->can_use_premium_code())) {
                    continue;
                }

                $defaults[$option::getKey()] = $option::getDefault();
            }
        }
        return $defaults;
    }

    /**
     * Returns all option keys
     *
     * @since  1.1.3
     * @return array
     */
    protected function getKeys()
    {
        if (!count($this->keys)) {
            foreach ($this->sections as $section) {
                foreach ($section['options'] as $option) {
                    $this->keys[] = $option->getKey();
                }
            }
            $this->keys = array_merge($this->keys, [self::ILJ_OPTION_KEY_ENVIRONMENT, self::ILJ_OPTION_KEY_INDEX_NOTIFY]);
            $this->keys = array_unique($this->keys);
        }

        return $this->keys;
    }

    /**
     * Responsible for the registration of settings sections
     *
     * @since  1.0.0
     * @return void
     */
    protected function addSettingsSections()
    {
        $sections = array_merge(
            $this->sections, [
            self::ILJ_OPTION_SECTION_GENERAL => [
            'title'       => __('General Settings Section', 'ILJ'),
            'description' => __('All settings related to the use of the plugin.', 'ILJ')
            ],
            self::ILJ_OPTION_SECTION_CONTENT => [
            'title'       => __('Content Settings Section', 'ILJ'),
            'description' => __('Configure how the plugin should behave regarding the internal linking.', 'ILJ')
            ],
            self::ILJ_OPTION_SECTION_LINKS   => [
            'title'       => __('Links Settings Section', 'ILJ'),
            'description' => __('Setting options for the output of the generated links.', 'ILJ')
            ]
            ]
        );

        foreach ($sections as $section => $section_data) {
            add_settings_section(
                self::ILJ_OPTION_PREFIX_ID . $section,
                $section_data['title'],
                (function () use ($section_data) {
                    echo '<p class="section-description">' . $section_data['description'] . '</p>';
                }),
                self::ILJ_OPTION_PREFIX_PAGE . $section
            );
        }

        return $this;
    }

    /**
     * Initiates the options
     *
     * @since  1.1.3
     * @return $this
     */
    protected function addOptions()
    {
        foreach ($this->sections as $section => $section_data) {
            foreach ($section_data['options'] as $option) {
                if (!$option instanceof Options\AbstractOption || $option::getKey() == "") {
                    continue;
                }

                add_settings_field(
                    $option::getKey(),
                    OptionsHelper::getTitle($option),
                    (function () use ($option) {
                        OptionsHelper::renderFieldComplete($option, self::getOption($option::getKey()));
                    }),
                    self::ILJ_OPTION_PREFIX_PAGE . $section,
                    self::ILJ_OPTION_PREFIX_ID . $section
                );

                $option->register(self::ILJ_OPTION_PREFIX_PAGE . $section);
            }
        }

        return $this;
    }

    private function __clone()
    {
    }
    private function __wakeup()
    {
    }

}
