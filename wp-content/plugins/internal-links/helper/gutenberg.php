<?php
namespace ILJ\Helper;

/**
 * Gutenberg toolset
 *
 * Methods for the gutenberg editor
 *
 * @package ILJ\Helper
 * @since   1.1.4
 */
class Gutenberg
{
    /**
     * Checks if the gutenberg editor is active
     *
     * @since  1.1.4
     * @return boolean
     */
    public static function isActive()
    {
        $gutenberg    = false;
        $block_editor = false;

        if (has_filter('replace_editor', 'gutenberg_init')) {
            // Gutenberg is installed and activated.
            $gutenberg = true;
        }

        if (version_compare($GLOBALS['wp_version'], '5.0-beta', '>')) {
            // Block editor.
            $block_editor = true;
        }

        if (!$gutenberg && !$block_editor) {
            return false;
        }

        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        if (!is_plugin_active('classic-editor/classic-editor.php')) {
            return true;
        }

        $use_block_editor = (get_option('classic-editor-replace') === 'no-replace');

        return $use_block_editor;
    }
}
