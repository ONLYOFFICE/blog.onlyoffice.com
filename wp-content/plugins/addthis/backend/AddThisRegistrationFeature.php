<?php
/**
 * +--------------------------------------------------------------------------+
 * | Copyright (c) 2008-2017 AddThis, LLC                                     |
 * +--------------------------------------------------------------------------+
 * | This program is free software; you can redistribute it and/or modify     |
 * | it under the terms of the GNU General Public License as published by     |
 * | the Free Software Foundation; either version 2 of the License, or        |
 * | (at your option) any later version.                                      |
 * |                                                                          |
 * | This program is distributed in the hope that it will be useful,          |
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
 * | GNU General Public License for more details.                             |
 * |                                                                          |
 * | You should have received a copy of the GNU General Public License        |
 * | along with this program; if not, write to the Free Software              |
 * | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA |
 * +--------------------------------------------------------------------------+
 */

require_once 'AddThisFeature.php';

if (!class_exists('AddThisRegistrationFeature')) {
    /**
     * Class for adding AddThis global options to WordPress, such as whether to
     * load addthis_widget.js asyncronously, in the header or footer, custom
     * settings to addthis_config and addthis_share, etc.
     *
     * @category   FollowButtons
     * @package    AddThisWordPress
     * @subpackage Features
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisRegistrationFeature extends AddThisFeature
    {
        protected $settingsPageId = 'addthis_registration';
        protected $name = 'Home';
        public $globalEnabledField = 'registration_feature_enabled';
        protected $settingLinkText = 'Home';
        protected $requestTimeout = 30;

        /**
         * Determines if this feature is enabled by any plugin, not necessarily
         * the plugin that boostrapped this object. The registration feature is
         * always enabled.
         *
         * @return boolean
         */
        public function isEnabled()
        {
            return true;
        }

        /**
         * Registering AJAX endpoints with WordPress
         *
         * @return null
         */
        protected function registerAjaxEndpoints()
        {
            add_action('wp_ajax_addthis_check_recommended_content', array($this, 'printCheckRecommendedContentProxy'));
            add_action('wp_ajax_addthis_check_api_key', array($this, 'printCheckApiKeyProxy'));
            add_action('wp_ajax_addthis_check_login', array($this, 'printCheckLoginProxy'));
            add_action('wp_ajax_addthis_check_old_plugins', array($this, 'printOldPluginsCheck'));
            add_action('wp_ajax_addthis_get_profiles', array($this, 'printGetProfilesProxy'));
            add_action('wp_ajax_addthis_get_profile_configs', array($this, 'printGetBoostConfigProxy'));
            add_action('wp_ajax_addthis_create_account', array($this, 'printCreateAccountProxy'));
            add_action('wp_ajax_addthis_create_profile', array($this, 'printCreateProfileProxy'));
            add_action('wp_ajax_addthis_create_api_key', array($this, 'printCreateApiKeyProxy'));
            add_action('wp_ajax_addthis_change_profile_type', array($this, 'printChangeProfileTypeProxy'));
            add_action('wp_ajax_addthis_change_old_plugin_profile_id', array($this, 'changeOldConfigsProfileId'));
            add_action('wp_ajax_addthis_boost_compatibility', array($this, 'checkBoostCompatibilityProxy'));
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Prints out a JSON payload with settings for a profile ID. Requires
         * the version of the plugin and pco of the plugin (can't auto add this
         * as the plugin object isn't available from here).
         *
         * @return null
         */
        public function printGetBoostConfigProxy()
        {
            $required = array('plugin_version', 'plugin_pco');
            $input = $this->jsonSetup($required);
            $this->checkForEditPermissions();

            $configs = $this->globalOptionsObject->getConfigs();
            $profileId = urlencode($configs['addthis_profile']);
            $version = urlencode($input['plugin_version']);
            $pco = urlencode($input['plugin_pco']);

            $result = array();

            $url = $this->globalOptionsObject->getDarkseidBaseUrl()
                . 'plugins/' . $pco
                . '/v/' . $version
                . '/site/' . $profileId;

            $args = array(
                'headers' => array(
                    'Accept'       => 'application/json',
                    'Authorization' => $configs['api_key'],
                ),
                'timeout' => $this->requestTimeout,
            );

            $response = wp_remote_get($url, $args);
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $result['message'] = 'Something went wrong: ' . $error_message;
            } else {
                if (isset($response['body'])) {
                    try {
                        $result['data'] = json_decode($response['body'], true);
                    } catch (Exception $e) {
                        $result['message'] = $e->getMessage();
                    }
                }

                if (isset($response['response']['code'])
                    && $response['response']['code'] == 200
                ) {
                    $result['success'] = true;
                } elseif (isset($response['response']['code'])
                          && $response['response']['code'] == 404
                ) {
                    $result['message'] = 'Unknown Profile ID';
                } elseif (isset($result['data']['error'])) {
                    $result['message'] = $result['data']['error'];
                }
            }

            if (empty($result['success'])) {
                $result['success'] = false;
                if (empty($result['message'])) {
                    $result['message'] = 'Something went wrong';
                }
            }

            $this->printJsonResults($result);
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Prints out a JSON payload to tell you if (and how much) recommended
         * content AddThis has for this site. Requires nothing. Uses the saved
         * profile id from the settings.
         *
         * TODO use a WordPress specific darkseid endpoint for looking up if we have recommended content for a profile id and domain name combo
         *
         * @return null
         */
        public function printCheckRecommendedContentProxy()
        {
            // todo does this belong in the recommended content feature?
            $input = $this->jsonSetup();
            $profileId = $this->globalOptionsObject->getUsableProfileId();
            $domain = $this->getSiteDomain();
            $result = array('success' => false);

            if ($domain) {
                $url = 'http://q.addthis.com/feeds/1.0/views2.json?pubid=' . $profileId
                    . '&domain=' . $domain
                    . '&limit=25';

                $args = array('timeout' => $this->requestTimeout);
                $response = wp_remote_get($url, $args);

                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    $result['message'] = 'Something went wrong: ' . $error_message;
                } else {
                    $json = $response['body'];
                    $recommendedContent = json_decode($json, true);
                }

                if (!empty($recommendedContent)) {
                    $result['success'] = true;
                }
            } else {
                header('X-PHP-Response-Code: 400', true, 400);
            }

            $this->printJsonResults($result);
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Prints out a JSON payload to tell you if an API key has admin
         * privilages on a profile. Requires a profileId and API Key.
         *
         * @return null
         */
        public function printCheckApiKeyProxy()
        {
            $required = array('profileId', 'apiKey');
            $input = $this->jsonSetup($required);
            $this->checkForEditPermissions();

            $result = array();

            preg_match('/(ra-)?(.*)/', $input['profileId'], $matches);
            $cuidish = $matches[2];

            $url = $this->globalOptionsObject->getDarkseidBaseUrl()
                . 'publisher/' . $cuidish
                . '/application';

            $args = array(
                'headers' => array(
                    'Accept'       => 'application/json',
                    'Authorization' => $input['apiKey'],
                ),
                'timeout' => $this->requestTimeout,
            );
            $response = wp_remote_get($url, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $result['message'] = 'Something went wrong: ' . $error_message;
            } else {
                if (isset($response['body'])) {
                    try {
                        $result['data'] = json_decode($response['body'], true);
                    } catch (Exception $e) {
                        $result['message'] = $e->getMessage();
                    }
                }

                if (isset($response['response']['code'])
                          && $response['response']['code'] == 200
                ) {
                    foreach ($result['data'] as $apiKeyInfo) {
                        if ($apiKeyInfo['cuid'] == $input['apiKey']) {
                            $result['success'] = true;
                        }
                    }
                } elseif (isset($response['response']['code'])
                          && $response['response']['code'] == 404
                ) {
                    $result['message'] = 'This API key is not valid for the provided Profile ID';
                } elseif (isset($result['data']['error'])) {
                    $result['message'] = $result['data']['error'];
                }
            }

            if (empty($result['success'])) {
                $result['success'] = false;
                if (empty($result['message'])) {
                    $result['message'] = 'Something went wrong';
                }
            }

            $this->printJsonResults($result);
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Prints out a JSON payload with of all the profiles on an account and
         * information about them. Requires an addthis account email and
         * password.
         *
         * @return null
         */
        public function printGetProfilesProxy()
        {
            $required = array('email', 'password');
            $input = $this->jsonSetup($required);
            $this->checkForEditPermissions();

            $result = array();

            $url = $this->globalOptionsObject->getDarkseidBaseUrl() . 'publisher';
            $credentials = $input['email'] . ':' . $input['password'];

            $args = array(
                'headers' => array(
                    'Accept'        => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($credentials),
                ),
                'timeout' => $this->requestTimeout,
            );
            $response = wp_remote_get($url, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $result['message'] = 'Something went wrong: ' . $error_message;
            } else {
                if (isset($response['body'])) {
                    try {
                        $result['data'] = json_decode($response['body'], true);
                    } catch (Exception $e) {
                        $result['message'] = $e->getMessage();
                    }
                }

                if (isset($response['response']['code'])
                          && $response['response']['code'] == 200
                ) {
                    $result['success'] = true;
                } elseif (isset($response['response']['code'])
                          && $response['response']['code'] == 401
                ) {
                    $result['message'] = 'Unknown email address or password';
                } elseif (isset($result['data']['error'])) {
                    $result['message'] = $result['data']['error'];
                }
            }

            if (empty($result['success'])) {
                $result['success'] = false;
                if (empty($result['message'])) {
                    $result['message'] = 'Something went wrong';
                }
            }

            $this->printJsonResults($result);
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Creates a new API key for a profile and prints out a JSON payload
         * with the newly created API key for the profile. Requires an addthis
         * account email, password, and profile id.
         *
         * @return null
         */
        public function printCreateApiKeyProxy()
        {
            $required = array('email', 'password', 'profileId');
            $input = $this->jsonSetup($required);
            $this->checkForEditPermissions();

            $result = array();

            preg_match('/(ra-)?(.*)/', $input['profileId'], $matches);
            $cuidish = $matches[2];

            $url = $this->globalOptionsObject->getDarkseidBaseUrl()
                . 'publisher/' . $cuidish
                . '/application';
            $credentials = $input['email'] . ':' . $input['password'];

            $date = date('Y F jS gia s');
            $body = array(
                'name' => 'WordPress Plugin (created ' . $date . ')',
            );

            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($credentials),
                ),
                'body' => json_encode($body),
                'timeout' => $this->requestTimeout,
            );
            $response = wp_remote_post($url, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $result['message'] = 'Something went wrong: ' . $error_message;
            } else {
                if (isset($response['body'])) {
                    try {
                        $result['data'] = json_decode($response['body'], true);
                    } catch (Exception $e) {
                        $result['message'] = $e->getMessage();
                    }
                }

                if (isset($response['response']['code'])
                    && $response['response']['code'] == 200
                ) {
                    foreach ($result['data'] as $apiKeyInfo) {
                        $result['apiKey'] = $apiKeyInfo['cuid'];
                        $result['success'] = true;
                    }
                }

                if (isset($result['data']['error'])) {
                    $result['message'] = $result['data']['error'];
                }
            }

            if (empty($result['success'])) {
                $result['success'] = false;
                if (empty($result['message'])) {
                    $result['message'] = 'Something went wrong';
                }
            }

            $this->printJsonResults($result);
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Creates a new AddThis account and prints out a JSON payload whether
         * the account was created successfully. Requires an account email,
         * password and a boolean for whether the user is
         * subscribing to our newsletter.
         *
         * @return null
         */
        public function printCreateAccountProxy()
        {
            $required = array('email', 'password', 'country', 'newsletter');
            $input = $this->jsonSetup($required);
            $this->checkForEditPermissions();

            $result = array();

            $body = array(
                'username'               => $input['email'],
                'email'                  => $input['email'],
                'plainPassword'          => $input['password'],
                'country'                => $input['country'],
                'subscribedToNewsletter' => (int)$input['newsletter'],
                'profileType'            => 'wp',
                'source'                 => 'wpwt',
            );

            $url = $this->globalOptionsObject->getDarkseidBaseUrl()
                . 'account/register-user';

            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ),
                'body' => json_encode($body),
                'timeout' => $this->requestTimeout,
            );
            $response = wp_remote_post($url, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $result['message'] = 'Something went wrong: ' . $error_message;
            } else {
                if (isset($response['body'])) {
                    try {
                        $result['data'] = json_decode($response['body'], true);
                    } catch (Exception $e) {
                        $result['message'] = $e->getMessage();
                    }
                }

                if (isset($result['data']['id'])) {
                    $result['success'] = true;
                    $this->kissMetricsAlias($input['email']);
                }

                if (isset($result['data']['error'])) {
                    $result['message'] = $result['data']['error'];
                }
            }

            if (empty($result['success'])) {
                $result['success'] = false;
                if (empty($result['message'])) {
                    $result['message'] = 'Something went wrong';
                }
            }

            $this->printJsonResults($result);
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Checks if the username and password passed are valid
         *
         * @return null
         */
        public function printCheckLoginProxy()
        {
            $required = array('email', 'password');
            $input = $this->jsonSetup($required);
            $this->checkForEditPermissions();
            $result = array();

            $credentials = $input['email'] . ':' . $input['password'];
            $url = $this->globalOptionsObject->getDarkseidBaseUrl() . 'user';

            $args = array(
                'headers' => array(
                    'Accept'        => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($credentials),
                ),
                'timeout' => $this->requestTimeout,
            );
            $response = wp_remote_get($url, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $result['message'] = 'Something went wrong: ' . $error_message;
            } else {
                if (isset($response['body'])) {
                    try {
                        $result['data'] = json_decode($response['body'], true);
                    } catch (Exception $e) {
                        $result['message'] = $e->getMessage();
                    }
                }

                if (isset($result['data']['email']) == $input['email']) {
                    $result['success'] = true;
                    $this->kissMetricsAlias($input['email']);
                }

                if (isset($result['data']['error'])) {
                    $result['message'] = $result['data']['error'];
                }
            }

            if (empty($result['success'])) {
                $result['success'] = false;
                if (empty($result['message'])) {
                    $result['message'] = 'Something went wrong';
                }
            }

            $this->printJsonResults($result);
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Creates a new site profile on an account and prints out a JSON
         * payload with the profile ID of the newly created profile. Requires
         * an addthis account email, password and a name for the new profile.
         *
         * @return null
         */
        public function printCreateProfileProxy()
        {
            $required = array('email', 'password', 'name');
            $input = $this->jsonSetup($required);
            $this->checkForEditPermissions();
            $result = array();

            $credentials = $input['email'] . ':' . $input['password'];
            $url = $this->globalOptionsObject->getDarkseidBaseUrl()
                . 'publisher';

            $body = array(
                'type' => 'wp',
                'name' => $input['name'],
            );

            $args = array(
                'headers' => array(
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($credentials),
                ),
                'body' => json_encode($body),
                'timeout' => $this->requestTimeout,
            );

            $response = wp_remote_post($url, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $result['message'] = 'Something went wrong: ' . $error_message;
            } else {
                if (isset($response['body'])) {
                    try {
                        $result['data'] = json_decode($response['body'], true);
                    } catch (Exception $e) {
                        $result['message'] = $e->getMessage();
                    }
                }

                if (isset($result['data']['pubId'])) {
                    $result['success'] = true;
                    $result['profileId'] = $result['data']['pubId'];
                }

                if (isset($result['data']['error'])) {
                    $result['message'] = $result['data']['error'];
                }
            }

            if (empty($result['success'])) {
                $result['success'] = false;
                if (empty($result['message'])) {
                    $result['message'] = 'Something went wrong';
                }
            }

            $this->printJsonResults($result);
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Changes a profile type to wp (WordPress) and prints out a JSON payload
         * to indicate whether it was successful. Requires a profileID and API Key.
         *
         * @return null
         */
        public function printChangeProfileTypeProxy()
        {
            $required = array('apiKey', 'profileId');
            $input = $this->jsonSetup($required);
            $this->checkForEditPermissions();

            $url = $this->globalOptionsObject->getDarkseidBaseUrl()
                . 'publisher/' . $input['profileId']
                . '/profile-type';

            $body = array(
                'type' => 'wp',
            );

            $args = array(
                'method'  => 'PUT',
                'headers' => array(
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => $input['apiKey'],
                ),
                'body' => json_encode($body),
                'timeout' => $this->requestTimeout,
            );
            $response = wp_remote_request($url, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $result['message'] = 'Something went wrong: ' . $error_message;
            } else {
                if (isset($response['body'])) {
                    try {
                        $result['data'] = json_decode($response['body'], true);
                    } catch (Exception $e) {
                        $result['message'] = $e->getMessage();
                    }
                }

                if (isset($result['data']['type'])
                        && $result['data']['type'] == 'wp'
                ) {
                    $result['success'] = true;
                }

                if (isset($result['data']['error'])) {
                    $result['message'] = $result['data']['error'];
                }
            }

            if (empty($result['success'])) {
                $result['success'] = false;
                if (empty($result['message'])) {
                    $result['message'] = 'Something went wrong';
                }
            }

            $this->printJsonResults($result);
        }

        /**
         * Checks what other AddThis plugins look to be active and returns the
         * profile ID they're using
         *
         * @return array
         */
        public function oldPluginsCheck()
        {
            $oldSharedSettings = get_option('addthis_settings');

            $results = array();
            if (get_option('addthis_bar_activated') === '1') {
                $tmp = array();
                $tmp['name'] = 'AddThis Welcome Bar';
                if (isset($oldSharedSettings['profile'])) {
                    $tmp['profileId'] = $oldSharedSettings['profile'];
                }
                $tmp['source'] = 'addthis_settings';
                $results['wb'] = $tmp;
            }

            if (get_option('smart_layer_activated') === '1') {
                $tmp = array();
                $tmp['name'] = 'AddThis Smart Layers';
                $tmp['profileId'] = get_option('smart_layer_profile');
                $tmp['source'] = 'smart_layer_profile';
                $results['sl'] = $tmp;
            }

            // neither of these methods for determining the follow button plugin
            // is enabled are perfect
            if (get_option('addthis_follow_settings') !== false
                || get_option('widget_addthis-follow-widget') !== false
            ) {
                $tmp = array();
                $tmp['name'] = 'AddThis Follow Buttons';
                if (isset($oldSharedSettings['profile'])) {
                    $tmp['profileId'] = $oldSharedSettings['profile'];
                }
                $tmp['source'] = 'addthis_settings';
                $results['fb'] = $tmp;
            }

            if (get_option('addthis_run_once')) {
                $tmp = array();
                $tmp['name'] = 'AddThis Sharing Buttons';
                $tmp['profileId'] = $this->globalOptionsObject->getProfileId();
                $results['sb'] = $tmp;
            }

            foreach ($results as $key => $plugin) {
                if (empty($plugin['profileId'])) {
                    $results[$key]['profileId'] = '';
                }
            }
            return $results;
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Prints out a JSON payload of other installed plugins
         *
         * @return null
         */
        public function printOldPluginsCheck()
        {
            $this->jsonSetup();
            $oldConfigs = $this->oldPluginsCheck();
            $this->printJsonResults($oldConfigs);
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Updates the profile id in the specified source to match the one in
         * this plugin and then prints out a JSON payload of other installed
         * plugins
         *
         * @return null
         */
        public function changeOldConfigsProfileId()
        {
            $required = array('source', 'nonce');
            $input = $this->jsonSetup($required);
            $this->checkForEditPermissions();

            $profileId = $this->globalOptionsObject->getProfileId();
            $source = $input['source'];

            if ($source == 'smart_layer_profile') {
                update_option('smart_layer_profile', $profileId);
            } elseif ($source == 'addthis_settings') {
                $configs = get_option('addthis_settings');
                $configs['profile'] = $profileId;
                update_option('addthis_settings', $configs);
            }

            $this->printOldPluginsCheck();
        }

        /**
         * This must be public as it's used in a callback for add_action
         *
         * Check to see if this plugin version can still update boost directly, or if boost has changed too much
         *
         * @return null
         */
        public function checkBoostCompatibilityProxy()
        {
            $required = array('nonce', 'plugin_version', 'plugin_pco');
            $input = $this->jsonSetup($required);

            $version = urlencode($input['plugin_version']);
            $plugin_pco = urlencode($input['plugin_pco']);

            $result = array();

            $url = $this->globalOptionsObject->getDarkseidBaseUrl()
                . 'plugins/' . $plugin_pco
                . '/v/' . $version
                . '/check';

            $args = array(
                'method'  => 'GET',
                'timeout' => $this->requestTimeout,
            );
            $response = wp_remote_request($url, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $result['message'] = 'Something went wrong: ' . $error_message;
            } else {
                $result['success'] = true;
                if (isset($response['response']['code'])
                    && $response['response']['code'] == 204
                ) {
                    $result['compatible'] = true;
                } else {
                    $result['compatible'] = false;
                }
            }

            if (empty($result['success'])) {
                $result['success'] = false;
                if (empty($result['message'])) {
                    $result['message'] = 'Something went wrong';
                }
            }

            $this->printJsonResults($result);
        }

        /**
         * KissMetrics aliasing for the pubId and user email
         *
         * @return null
         */
        private function kissMetricsAlias($email)
        {
            if (isset($_COOKIE["km_ai"])) {
                $url = $this->globalOptionsObject->getDarkseidBaseUrl() . 'wordpress/aliasing';
                $body = array(
                    'email' => $email,
                    'kmId' => $_COOKIE['km_ai']
                );
                $args = array(
                    'headers' => array(
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ),
                    'body' => json_encode($body),
                    'timeout' => $this->requestTimeout,
                );
                $response = wp_remote_post($url, $args);
            }
        }
    }
}
