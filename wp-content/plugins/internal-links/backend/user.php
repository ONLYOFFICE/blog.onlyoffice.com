<?php
namespace ILJ\Backend;

/**
 * User Environment
 *
 * Singleton, that has information about the current users meta data regarding the plugin
 *
 * @package ILJ\Backend
 * @since   1.1.2
 */
class User
{
    const ILJ_META_USER = 'ilj_user';

    /**
     * @var   User
     * @since 1.1.2
     */
    private static $instance;

    /**
     * @var   int
     * @since 1.1.2
     */
    private $user_id;

    /**
     * @var   array
     * @since 1.1.2
     */
    private $user_data;

    protected function __construct()
    {
        $user_data_default = [
            'hide_promo' => false
        ];

        $user_id   = get_current_user_id();
        $user_data = get_user_meta($user_id, self::ILJ_META_USER, true);

        $this->user_id   = $user_id;
        $this->user_data = wp_parse_args($user_data, $user_data_default);
    }

    /**
     * Get data
     *
     * @since  1.1.2
     * @param  string $key The key
     * @return string|bool
     */
    public static function get($key)
    {
        self::init();
        $user_data = self::$instance->user_data;
        if (array_key_exists($key, $user_data)) {
            return $user_data[$key];
        }
        return false;
    }

    /**
     * Update data
     *
     * @since  1.1.2
     * @param  string $key   The key
     * @param  mixed  $value The value
     * @return void
     */
    public static function update($key, $value)
    {
        self::init();
        $user_data       = self::$instance->user_data;
        $user_data[$key] = $value;
        update_user_meta(self::$instance->user_id, self::ILJ_META_USER, $user_data);
    }

    /**
     * Init User class
     *
     * @since  1.1.2
     * @return void
     */
    private static function init()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
    }

    protected function __clone()
    {
    }

    protected function __wakeup()
    {
    }

}
