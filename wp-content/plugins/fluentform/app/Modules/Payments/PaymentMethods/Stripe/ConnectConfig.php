<?php

namespace FluentForm\App\Modules\Payments\PaymentMethods\Stripe;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Payments\PaymentMethods\Stripe\API\Account;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ConnectConfig
{
    private static $connectBase = 'https://apiv2.wpmanageninja.com/fluentform/';

    public static function getConnectConfig()
    {
        $configBase = self::$connectBase . 'stripe-connect';
        $hash = md5(site_url() . wp_generate_uuid4() . time());

        $liveArgs = [
            'url_base' => rawurlencode(admin_url('admin.php?page=fluent_forms_settings')),
            'mode'     => 'live',
            'hash'     => $hash
        ];

        $testArgs = [
            'url_base' => rawurlencode(admin_url('admin.php?page=fluent_forms_settings')),
            'mode'     => 'test',
            'hash'     => $hash
        ];

        $settings = StripeSettings::getSettings();

        $data = [
            'connect_config' => [
                'live_redirect' => add_query_arg($liveArgs, $configBase),
                'test_redirect' => add_query_arg($testArgs, $configBase),
                'image_url'     => fluentformMix('img/payment/stripe-connect.png'),
                'should_apply_application_fee' => !Helper::hasPro(),
            ],
            'test_account'   => self::getAccountInfo($settings, 'test'),
            'live_account'   => self::getAccountInfo($settings, 'live')
        ];

        if ($settings['test_secret_key']) {
            $settings['test_secret_key'] = 'ENCRYPTED_KEY';
        }

        if ($settings['live_secret_key']) {
            $settings['live_secret_key'] = 'ENCRYPTED_KEY';
        }

        $data['settings'] = $settings;

        return $data;
    }

    public static function verifyAuthorizeSuccess($data)
    {
        $response = wp_remote_post(self::$connectBase . 'stripe-verify-code', [
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'sslverify'   => false,
            'blocking'    => true,
            'headers'     => array(),
            'body'        => $data,
            'cookies'     => array()
        ]);

        if (is_wp_error($response)) {
            $message = $response->get_error_message();
            echo '<div class="ff_message ff_message_error">' . esc_html($message) . '</div>';
            return;
        }

        $response = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($response['stripe_user_id'])) {
            $message = ArrayHelper::get($response, 'message');
            if (!$message) {
                $message = __('Invalid Stripe Request. Please configure stripe payment gateway again', 'fluentform');
            }
            echo '<div class="ff_message ff_message_error">' . esc_html($message) . '</div>';
            return;
        }

        $settings = StripeSettings::getSettings();
        $settings['provider'] = 'connect';

        $settings['is_active'] = 'yes';

        if (!empty($response['livemode'])) {
            $settings['payment_mode'] = 'live';
            $settings['live_account_id'] = $response['stripe_user_id'];
            $settings['live_publishable_key'] = $response['stripe_publishable_key'];
            $settings['live_secret_key'] = $response['access_token'];
        } else {
            $settings['payment_mode'] = 'test';
            $settings['test_account_id'] = $response['stripe_user_id'];
            $settings['test_publishable_key'] = $response['stripe_publishable_key'];
            $settings['test_secret_key'] = $response['access_token'];
        }

        StripeSettings::updateSettings($settings);

        ?>
        <script type="text/javascript">
            window.location = "<?php echo esc_url(admin_url('admin.php?page=fluent_forms_settings#payments/payment_methods')); ?>"
        </script>
        <?php

    }

    private static function getAccountInfo($settings, $mode)
    {

        if ($settings['is_active'] != 'yes') {
            return false;
        }

        if ($settings['provider'] != 'connect') {
            return false;
        }

        $apiKey = $settings[$mode . '_secret_key'];

        $accountId = ArrayHelper::get($settings, $mode . '_account_id');

        if (!$accountId) {
            return false;
        }

        $account = Account::retrive($accountId, $apiKey);

        if (is_wp_error($account)) {
            return [
                'error' => $account->get_error_message()
            ];
        }

        // Find the email.
        $email = isset($account->email)
            ? esc_html($account->email)
            : '';

        // Find a Display Name.
        $display_name = isset($account->display_name)
            ? esc_html($account->display_name)
            : '';

        if (
            empty($display_name) &&
            isset($account->settings) &&
            isset($account->settings->dashboard) &&
            isset($account->settings->dashboard->display_name)
        ) {
            $display_name = esc_html($account->settings->dashboard->display_name);
        }

        if (empty($display_name)) {
            return [
                'error' => __('Unable to find connected display name', 'fluentform')
            ];
        }

        return [
            'account_id'   => $accountId,
            'display_name' => $display_name,
            'email'        => $email
        ];

    }

    public static function disconnect($data, $sendResponse = false)
    {
        $mode = ArrayHelper::get($data, 'mode');
        $stripeSettings = StripeSettings::getSettings();

        if($stripeSettings['is_active'] != 'yes') {
            if($sendResponse) {
                wp_send_json_error([
                    'message' => __('Stripe mode is not active', 'fluentform')
                ], 423);
            }
            return false;
        }

        if(empty($stripeSettings[$mode.'_account_id'])) {
            if($sendResponse) {
                wp_send_json_error([
                    'message' => __('Selected Account does not exist', 'fluentform')
                ], 423);
            }
            return false;
        }

        $stripeSettings[$mode.'_account_id'] = '';
        $stripeSettings[$mode.'_publishable_key'] = '';
        $stripeSettings[$mode.'_secret_key'] = '';

        if($mode == 'live') {
            $alternateMode = 'test';
        } else {
            $alternateMode = 'live';
        }

        if(empty($stripeSettings[$alternateMode.'_account_id'])) {
            $stripeSettings['is_active'] = 'no';
            $stripeSettings['payment_mode'] = 'test';
        } else {
            $stripeSettings['payment_mode'] = $alternateMode;
        }

        StripeSettings::updateSettings($stripeSettings);

        if($sendResponse) {
            wp_send_json_success([
                'message' => __('Stripe settings has been disconnected', 'fluentform'),
                'settings' => $stripeSettings
            ], 200);
        }

        return true;
    }
}
