<?php

namespace FluentForm\App\Modules\Ai;

defined('ABSPATH') or die;

/**
 *  Handling AI Api.
 * @since 6.0.0
 */
class FluentFormAIAPI
{
    private $url = 'https://ai.fluentforms.com/';
    
    public function makeRequest($requestData = [])
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];
    
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- http_request_timeout is a WordPress core hook
        $originalTimeout = apply_filters('http_request_timeout', 5);
    
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- http_request_timeout is a WordPress core hook
        add_filter('http_request_timeout', function ($timeout) {
            return 60;
        });
    
        $response = wp_remote_post($this->url, [
            'headers' => $headers,
            'body'    => json_encode($requestData),
        ]);
    
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- http_request_timeout is a WordPress core hook
        add_filter('http_request_timeout', function ($timeout) use ($originalTimeout) {
            return $originalTimeout;
        });
    
        if (is_wp_error($response)) {
            return new \WP_Error(423, $response->get_error_message());
        }
    
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $code = wp_remote_retrieve_response_code($response);
    
        $error_message = __('Something went wrong.', 'fluentform');
        if (isset($body['error']['message'])) {
            $error_message = $body['error']['message'];
        }
    
        if ($code !== 200) {
            return new \WP_Error(423, $error_message);
        }
    
        return $body;
    }
}
