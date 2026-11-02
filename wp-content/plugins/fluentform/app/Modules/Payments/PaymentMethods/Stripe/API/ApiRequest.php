<?php

namespace FluentForm\App\Modules\Payments\PaymentMethods\Stripe\API;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\Framework\Helpers\ArrayHelper;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC_Stripe_API class.
 *
 * Communicates with Stripe API.
 */
class ApiRequest
{
    /**
     * Stripe API Endpoint
     */
    private static $ENDPOINT = 'https://api.stripe.com/v1/';
    const STRIPE_API_VERSION = '2020-08-27';
    /**
     * Secret API Key.
     * @var string
     */
    private static $secret_key = '';

    /**
     * Set secret API Key.
     * @param string $secret_key
     */
    public static function set_secret_key($secret_key)
    {
        self::$secret_key = $secret_key;
    }

    /**
     * Set secret API Key.
     * @param string $secret_key
     */
    public static function set_end_point($endpont)
    {
        self::$ENDPOINT = $endpont;
    }

    /**
     * Get secret key.
     * @return string
     */
    public static function get_secret_key()
    {
        return self::$secret_key;
    }

    /**
     * Generates the user agent we use to pass to API request so
     * Stripe can identify our application.
     *
     * @since 4.0.0
     * @version 4.0.0
     */
    public static function get_user_agent()
    {
        $app_info = array(
            'name' => 'Fluent Forms',
            'version' => FLUENTFORM_VERSION,
            'url' => site_url(),
            'partner_id' => 'pp_partner_FN62GfRLM2Kx5d'
        );
        return array(
            'lang' => 'php',
            'lang_version' => phpversion(),
            'publisher' => 'wpmanageninja',
            'uname' => function_exists('php_uname') ? php_uname() : PHP_OS,
            'application' => $app_info,
        );
    }

    /**
     * Generates the headers to pass to API request.
     *
     * @since 4.0.0
     * @version 4.0.0
     */
    public static function get_headers()
    {
        $user_agent = self::get_user_agent();
        $app_info = $user_agent['application'];
        $headers = [
            'Authorization' => 'Basic ' . base64_encode(self::get_secret_key() . ':'),
            'Stripe-Version' => self::STRIPE_API_VERSION,
            'User-Agent' => $app_info['name'] . '/' . $app_info['version'] . ' (' . $app_info['url'] . ')',
            'X-Stripe-Client-User-Agent' => json_encode($user_agent),
        ];

        $headers = apply_filters_deprecated(
            'fluentform_stripe_request_headers',
            [
                $headers
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/stripe_request_headers',
            'Use fluentform/stripe_request_headers instead of fluentform_stripe_request_headers.'
        );
        return apply_filters('fluentform/stripe_request_headers', $headers);
    }

    /**
     * Send the request to Stripe's API
     *
     * @param array $request
     * @param string $api
     * @param string $method
     * @return object|\WP_Error
     * @since 3.1.0
     * @version 4.0.6
     */
    public static function request($request, $api = 'charges', $method = 'POST')
    {
        $headers = self::get_headers();
        if ('charges' === $api && 'POST' === $method) {
            $customer = !empty($request['customer']) ? $request['customer'] : '';
            $source = !empty($request['source']) ? $request['source'] : $customer;
            $key = ArrayHelper::get($request, 'metadata.fluentform_tid') . '-' . $source . '-' . $api;
            $key = apply_filters_deprecated(
                'fluentform_stripe_idempotency_key',
                [
                    $key,
                    $request
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/stripe_idempotency_key',
                'Use fluentform/stripe_idempotency_key instead of fluentform_stripe_idempotency_key.'
            );
            $idempotency_key = apply_filters('fluentform/stripe_idempotency_key', $key, $request);
            $headers['Idempotency-Key'] = $idempotency_key;
        }

        $request = apply_filters_deprecated(
            'fluentform_stripe_request_body',
            [
                $request,
                $api
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/stripe_request_body',
            'Use fluentform/stripe_request_body instead of fluentform_stripe_request_body.'
        );

        $response = wp_safe_remote_post(
            self::$ENDPOINT . $api,
            array(
                'method' => $method,
                'headers' => $headers,
                'body' => apply_filters('fluentform/stripe_request_body', $request, $api),
                'timeout' => 50,
            )
        );
        if (is_wp_error($response) || empty($response['body'])) {
            return new \WP_Error('stripe_error', __('There was a problem connecting to the Stripe API endpoint.', 'fluentform'));
        }

        $body = json_decode(wp_remote_retrieve_body($response));
        // check if it's a stripe error or not
        $responseCode = wp_remote_retrieve_response_code($response);

        if($responseCode > 299) {
            $code = __('general_error', 'fluentform');
            if(!empty($body->error->code)) {
                $code = $body->error->code;
            }
            $message = __('Stripe General Error', 'fluentform');
            if(!empty($body->error->message)) {
                $message = $body->error->message;
            }

            return new \WP_Error($code, $message, $body);
        }

        return $body;
    }

    /**
     * Retrieve API endpoint.
     *
     * @param string $api
     * @return mixed|\WP_Error|null
     * @since 4.0.0
     * @version 4.0.0
     */
    public static function retrieve($api)
    {
        $response = wp_safe_remote_get(
            self::$ENDPOINT . $api,
            array(
                'method' => 'GET',
                'headers' => self::get_headers(),
                'timeout' => 70,
            )
        );
        if (is_wp_error($response) || empty($response['body'])) {
            return new \WP_Error('stripe_error', __('There was a problem connecting to the Stripe API endpoint.', 'fluentform'));
        }
        return json_decode($response['body']);
    }
}
