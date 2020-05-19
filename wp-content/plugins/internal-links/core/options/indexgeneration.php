<?php
namespace ILJ\Core\Options;

use ILJ\Helper\Help;
use ILJ\Enumeration\IndexMode;
use ILJ\Helper\Options as OptionsHelper;

/**
 * Option: Index generation mode
 *
 * @since   1.1.3
 * @package ILJ\Core\Options
 */
class IndexGeneration extends AbstractOption
{
    /**
     * @inheritdoc
     */
    public static function getKey()
    {
        return self::ILJ_OPTIONS_PREFIX . 'index_generation';
    }

    /**
     * @inheritdoc
     */
    public static function getDefault()
    {
        return IndexMode::AUTOMATIC;
    }

    /**
     * @inheritdoc
     */
    public static function isPro()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return __('Index generation mode', 'ILJ');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return __('Choose your preferred approach for generating the index.', 'ILJ');
    }

    /**
     * @inheritdoc
     */
    public function getHint()
    {
        return '<ul class="description">'
        . '<li><p class="description"><code>' . __('None', 'ILJ') . '</code>: ' . __('The index is not created by the plugin (you should set up a cronjob). Read more in our', 'ILJ') . ' <a href="' . Help::getLinkUrl('index-generation-mode/', 'mode-none', 'index generation mode', 'settings') . '" target="_blank" rel="noopener">' . __('manual', 'ILJ') . '</a>.</p></li>'
        . '<li><p class="description"><code>' . __('Manually', 'ILJ') . '</code>: ' . __('You are notified when changes are made in connection with the index and can decide when the index should get updated.', 'ILJ') . '</p></li>'
        . '<li><p class="description"><code>' . __('Automatic', 'ILJ') . '</code>: ' . __('Any change affecting the index automatically updates the index.', 'ILJ') . '</p></li>'
            . '</ul>';
    }

    /**
     * @inheritdoc
     */
    public function renderField($value)
    {
        global $ilj_fs;

        if (!$ilj_fs->is__premium_only() || !$ilj_fs->can_use_premium_code()) {
            $value = self::getDefault();
        }

        echo '<select name="' . self::getKey() . '" id="' . self::getKey() . '"' . OptionsHelper::getDisabler($this) . '>';
        echo '<option value="' . IndexMode::NONE . '" ' . selected($value, IndexMode::NONE) . '>' . __('None', 'ILJ') . '</option>';
        echo '<option value="' . IndexMode::MANUALLY . '" ' . selected($value, IndexMode::MANUALLY) . '>' . __('Manually', 'ILJ') . '</option>';
        echo '<option value="' . IndexMode::AUTOMATIC . '" ' . selected($value, IndexMode::AUTOMATIC) . '>' . __('Automatic', 'ILJ') . '</option>';
        echo '</select> ' . Help::getOptionsLink('index-generation-mode/', '', 'index generation mode');
    }
}
