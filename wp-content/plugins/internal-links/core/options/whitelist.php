<?php
namespace ILJ\Core\Options;

use ILJ\Helper\Help;

/**
 * Option: Whitelist
 *
 * @since   1.1.3
 * @package ILJ\Core\Options
 */
class Whitelist extends AbstractOption
{
    /**
     * @inheritdoc
     */
    public static function getKey()
    {
        return self::ILJ_OPTIONS_PREFIX . 'whitelist';
    }

    /**
     * @inheritdoc
     */
    public static function getDefault()
    {
        return ['page', 'post'];
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return __('Whitelist of post types, that should be used for linking', 'ILJ');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return __('All posts within the allowed post types can link to other posts automatically.', 'ILJ');
    }

    /**
     * @inheritdoc
     */
    public function renderField($value)
    {
        if ($value == "") {
            $value = [];
        }

        $post_types_public = get_post_types(
            [
                'public'   => true,
                '_builtin' => false
            ], 'objects', 'or'
        );

        $post_types_with_editor = get_post_types_by_support(
            ['editor']
        );

        if (count($post_types_public)) {
            echo '<select name="' . self::getKey() . '[]" id="' . self::getKey() . '" multiple="multiple">';
            foreach ($post_types_public as $post_type) {
                if (in_array($post_type->name, $post_types_with_editor)) {
                    echo '<option value="' . $post_type->name . '"' . (in_array($post_type->name, $value) ? ' selected' : '') . '>' . $post_type->label . '</option>';
                }
            }
            echo '</select> ' . Help::getOptionsLink('whitelist-blacklist/', 'whitelist', 'whitelist');
        }
    }
}
