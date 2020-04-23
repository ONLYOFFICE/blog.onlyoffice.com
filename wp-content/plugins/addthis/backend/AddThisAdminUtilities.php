<?php

require_once 'AddThisFeature.php';

if (!class_exists('AddThisAdminUtilities')) {
    class AddThisAdminUtilities {
        const CONFIG_KEY = 'addthis_admin_utilities_config';
        const NOTICE_KEY = 'addthis_admin_notice';

        protected static $globalOptionsObject = null;
        protected static $notices  = null;

        /**
         * Callback for admin_enqueue_scripts, enqueues admin utilities javascript
         *
         * @return null
         */
        public static function adminEnqueueScripts() {
            if (self::$globalOptionsObject) {
                $root = self::$globalOptionsObject->getSettingsUiBaseUrl();
                wp_enqueue_script('addthis_wordpress_admin', $root . 'build/addthis_wordpress_admin.min.js');
            }
        }

        /**
         * Helper function for queuing admin notices
         *
         * @param string $noticeKey unique notice key
         * @param callable $function callback function for displaying notice when requested by WP
         *
         * @return null
         */
        public static function enqueueNotice($noticeKey, $function) {
            if (!isset(self::$notices[$noticeKey])) {
                self::$notices[$noticeKey] = array(
                    'dismissed' => false
                );
            }

            if (is_callable($function) && !self::isNoticeDismissed($noticeKey)) {
                add_action('admin_notices', $function);
            }
        }

        /**
         * Initialization routines for static singleton object
         *
         * @return null
         */
        public static function init() {
            self::loadUtilityConfig();

            add_action('admin_enqueue_scripts', array(__CLASS__, 'adminEnqueueScripts'), 'jquery');
            add_action('wp_ajax_addthis_admin_notice_dismiss', array(__CLASS__, 'noticeAjaxCallback'));
        }

        /**
         * Determines if currently viewing the WP admin interface
         *
         * @return boolean
         */
        public static function isAdminInterface() {
            return is_admin();
        }

        /**
         * Determines if currently running the AddThis plugin in anonymous WP mode
         *
         * @return boolean
         */
        public static function isAnonMode() {
            if (self::$globalOptionsObject) {
                return self::$globalOptionsObject->inAnonymousMode();
            }

            return false;
        }

        /**
         * Determines if a notice, by key, has been dismissed
         *
         * @param string $noticeKey unique notice key
         *
         * @return boolean
         */
        protected static function isNoticeDismissed($noticeKey) {
            return (self::$notices[$noticeKey]['dismissed'] === true);
        }

        /**
         * Loads the configuration object from WP
         *
         * @return array
         */
        protected static function loadUtilityConfig() {
            $options = get_option(AddThisAdminUtilities::CONFIG_KEY);

            if (isset($options['notices'])) {
                self::$notices = $options['notices'];
            } else {
                self::$notices = array();
            }

            return $options;
        }

        /**
         * AJAX callback for dismissing notices from the front-end
         *
         * @return null
         */
        public static function noticeAjaxCallback() {
            $nonceKey = AddThisAdminUtilities::NOTICE_KEY;
            $noticeKey = sanitize_key($_POST['noticekey']);

            if (empty($noticeKey)) {
                wp_send_json_error(array('error' => 'notice key invalid'), 400);
            }

            $nonceKey .= '_' . $noticeKey;
            if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], $nonceKey)) {
                if (isset(self::$notices[$noticeKey])) {
                    self::$notices[$noticeKey]['dismissed'] = true;
                    self::saveUtilityConfig();

                    wp_send_json(array('success' => true));
                    return;
                }
            }

            wp_send_json_error(array('error' => 'invalid nonce'), 400);
        }

        /**
         * Saves the configuration object in WP
         *
         * @return null
         */
        protected static function saveUtilityConfig() {
            $options = array();

            if (isset(self::$notices) && count(self::$notices)) {
                $options['notices'] = self::$notices;
            }

            update_option(AddThisAdminUtilities::CONFIG_KEY, $options);
        }

        /**
         * Set an AddThis Global Options Object from an AddThis tools plugin
         *
         * @return null
         */
        public static function setGlobalOptions($globalOptionsObject) {
            if (self::$globalOptionsObject === null) {
                self::$globalOptionsObject = $globalOptionsObject;
            }
        }

        /**
         * Helper function to output the markup that will contain the admin notice
         *
         * @param string $message the message that should be displayed
         * @param string $noticeKey unique key for the message, to make dismissable
         * @parem string $class style class for WordPress admin notice
         *
         * @return null
         */
        public static function showNotice($message, $noticeKey = null, $link = null, $class = 'notice-info') {
            $nonceKey = AddThisAdminUtilities::NOTICE_KEY;
            $classes = array('notice');

            if (!empty($noticeKey)) {
                $classes[] = 'is-dismissible';
                $nonceKey .= '_' . $noticeKey;
            }

            if (!empty($class)) {
                $classes[] = $class;
            }

            ?>
            <div class="<?php echo esc_attr(implode(' ', $classes)); ?>" data-atnoticekey="<?php echo esc_attr($noticeKey); ?>">
                <p>
                    <?php esc_html_e($message, AddThisFeature::$l10n_domain); ?>
                    <?php if (!empty($link)) : ?><?php echo $link; ?><?php endif; ?>
                </p>
                <?php wp_nonce_field($nonceKey); ?>
            </div>
            <?php
        }

        /**
         * Verify user has appropriate WordPress capabilities
         *
         * @return boolean
         */
        public static function userHasCapabilities() {
            if (self::$globalOptionsObject) {
                return current_user_can(self::$globalOptionsObject->getEditOptionsCapability());
            }

            return false;
        }
    }

    if (AddThisAdminUtilities::isAdminInterface()) {
        add_action('admin_init', array('AddThisAdminUtilities', 'init'));
    }
}
