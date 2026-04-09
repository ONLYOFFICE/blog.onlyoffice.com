<?php

namespace FluentForm\App\Modules\Form;

use Cleantalk\Antispam\CleantalkRequest;
use FluentForm\Framework\Helpers\ArrayHelper;

class CleanTalkHandler
{
    public function setCleanTalkScript()
    {
        $hasCleanTalk = self::isCleantalkActivated();
        if ($hasCleanTalk) {
            wp_enqueue_script(
                'ct_bot_detector',
                'https://moderate.cleantalk.org/ct-bot-detector-wrapper.js',
                [],
                FLUENTFORM_VERSION,
                [
                    'in_footer' => false,
                    'strategy' => 'defer'
                ]
            );

            echo '<input type="hidden" name="ff_ct_form_load_time" class="ff_ct_form_load_time" value="">';
        }
    }
    
    public static function validate($accessKey)
    {
        $cleanTalkRequest = [
            'method_name' => 'notice_paid_till',
            'auth_key' => $accessKey,
        ];

        $response = wp_remote_post(
            'https://api.cleantalk.org/',
            [
                'body'    => \http_build_query($cleanTalkRequest, true),
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]
        );

        if (is_wp_error($response)) {
            return false;
        }

        $response = json_decode(wp_remote_retrieve_body($response));

        if ($response->data->moderate == 1 && $response->data->valid == 1 && $response->data->product_id == 1) {
            return true;
        } else {
            return false;
        }
    }

    public static function spamSubmissionCheckWithApi($formData, $form)
    {
        global $fluentformCleantalkExecuted;
        
        $accessKey = ArrayHelper::get(get_option('_fluentform_cleantalk_details'), 'accessKey');

        if (!$accessKey) {
            return false;
        }

        $eventToken = ArrayHelper::get($formData, 'ct_bot_detector_event_token');
        $submitTime = isset($formData['ff_ct_form_load_time']) ? time() - (int)$formData['ff_ct_form_load_time'] : null;

        $cleanTalkRequest = [
            'method_name'     => 'check_message',
            'auth_key'        => $accessKey,
            'sender_ip'       => sanitize_text_field(wpFluentForm()->request->getIp()),
            'event_token'     => $eventToken,
            'submit_time'     => $submitTime,
            'sender_info'     => json_encode([
                'REFERRER'   => isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : '',
                'USER_AGENT' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : ''
            ]),
            'js_on'           => 1,
            'sender_nickname' => '',
            'sender_email'    => '',
            'message'         => '',
            'phone'           => '',
            'agent'           => 'wordpress-fluentforms-' . FLUENTFORM_VERSION,
            'post_info'       => [
                'comment_type' => 'fluent_forms_vendor_integration__use_api',
                'post_url'     => isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : ''
            ],
            'all_headers'   => strtolower(json_encode(wpFluentForm()->request->header())),
        ];

        $maps = [
            'input_name'  => 'sender_nickname',
            'input_email' => 'sender_email',
            'textarea'    => 'message',
            'phone'       => 'phone',
        ];
        
        $inputs = FormFieldsParser::getInputs($form, ['attributes']);

        foreach ($inputs as $input) {
            $element = ArrayHelper::get($input, 'element');
            $key = ArrayHelper::get($input, 'attributes.name');
            if (isset($maps[$element]) && !$cleanTalkRequest[$maps[$element]]) {
                $value = ArrayHelper::get($formData, $key);
                if ($value) {
                    if (is_array($value)) {
                        $value = implode(' ', $value);
                    }
                    $cleanTalkRequest[$maps[$element]] = $value;
                }
            }
        }

        $cleanTalkRequest = apply_filters('fluentform/cleantalk_fields', $cleanTalkRequest, $formData, $form);

        $response = wp_remote_post(
            'https://moderate.cleantalk.org/api2.0',
            [
                'body'    => json_encode($cleanTalkRequest),
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        if (is_wp_error($response)) {
            return false;
        }

        $response = json_decode(wp_remote_retrieve_body($response));

        $cleantalkPassed = $response->allow == 1 && $response->spam == 0 && $response->account_status == 1;

        $fluentformCleantalkExecuted = true;

        return !$cleantalkPassed;
    }

    public static function isCleantalkActivated()
    {
        $settings = get_option('_fluentform_cleantalk_details');
        return $settings && ArrayHelper::get($settings, 'status');
    }
    
    public static function isEnabled()
    {
        if (!self::isPluginEnabled()) {
            return false;
        }

        $settings = get_option('_fluentform_global_form_settings');
        return $settings && 'yes' == ArrayHelper::get($settings, 'misc.cleantalk_status');
    }

    public static function isPluginEnabled()
    {
        $exists = method_exists('Cleantalk\Antispam\Cleantalk', 'isAllowMessage');
        if ($exists) {
            global $apbct;
            return !!$apbct->data['key_is_ok'];
        }
        return false;
    }

    public static function isSpamSubmission($formData, $form)
    {
        $cleanTalkRequest = self::getCleanTalkRequest($formData, $form);
        $cleanTalk = new \Cleantalk\Antispam\Cleantalk();
        $cleanTalk->server_url = 'https://moderate.cleantalk.org';
        $response = $cleanTalk->isAllowMessage($cleanTalkRequest);

        return 0 == $response->allow;
    }

    protected static function getCleanTalkRequest($data, $form)
    {
        global $apbct;
        $app = wpFluentForm();
        $ip = sanitize_text_field($app->request->getIp());
        
        $serverName = isset($_SERVER['SERVER_NAME']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : '';
        $requestUri = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';

        $info = [
            'auth_key'             => $apbct->settings['apikey'],
            'sender_ip'            => $ip,
            'contact_form_subject' => $form->title,
            'referrer'             => urlencode($data['_wp_http_referer']),
            'page_url'             => $serverName . $requestUri,
            'submit_time'          => isset($_SESSION['ct_submit_time']) ? time() - (int)$_SESSION['ct_submit_time'] : null, //@todo Improve this
            'agent'                => 'php-api',
            'js_on'                => 1,
            'sender_nickname'      => '',
            'sender_email'         => '',
            'message'              => '',
            'phone'                => ''
        ];

        $maps = [
            'input_name'  => 'sender_nickname',
            'input_email' => 'sender_email',
            'textarea'    => 'message',
            'phone'       => 'phone',
        ];
        $inputs = FormFieldsParser::getInputs($form, ['attributes']);

        foreach ($inputs as $input) {
            $element = ArrayHelper::get($input, 'element');
            $key = ArrayHelper::get($input, 'attributes.name');
            if (isset($maps[$element]) && !$info[$maps[$element]]) {
                $value = ArrayHelper::get($data, $key);
                if ($value) {
                    if (is_array($value)) {
                        $value = implode(' ', $value);
                    }
                    $info[$maps[$element]] = $value;
                }
            }
        }

        $info = apply_filters('fluentform/cleantalk_fields', $info, $data, $form);

        return new CleantalkRequest($info);
    }
}
