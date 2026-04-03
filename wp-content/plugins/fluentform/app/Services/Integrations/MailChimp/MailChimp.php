<?php

namespace FluentForm\App\Services\Integrations\MailChimp;

/**
 * Super-simple, minimum abstraction MailChimp API v3 wrapper
 * MailChimp API v3: http://developer.mailchimp.com
 * This wrapper: https://github.com/drewm/mailchimp-api
 *
 * @author Drew McLellan <drew.mclellan@gmail.com>
 *
 * @version 2.4
 */
class MailChimp
{
    private $api_key;
    private $api_endpoint = 'https://<dc>.api.mailchimp.com/3.0';

    public const TIMEOUT = 10;

    /*  SSL Verification
        Read before disabling:
        http://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/
    */
    public $verify_ssl = true;

    private $request_successful = false;
    private $last_error = '';
    private $last_response = [];
    private $last_request = [];

    /**
     * Create a new instance
     *
     * @param string $api_key      Your MailChimp API key
     * @param string $api_endpoint Optional custom API endpoint
     *
     * @throws \Exception
     */
    public function __construct($api_key, $api_endpoint = null)
    {
        $this->api_key = $api_key;

        if (null === $api_endpoint) {
            if (false === strpos($this->api_key, '-')) {
                throw new \Exception(sprintf('Invalid Mailchimp API key `%s` supplied.', esc_html($api_key)));
            }
            list(, $data_center) = explode('-', $this->api_key);
            $this->api_endpoint = str_replace('<dc>', $data_center, $this->api_endpoint);
        } else {
            $this->api_endpoint = $api_endpoint;
        }

        $this->last_response = ['headers' => null, 'body' => null];
    }

    /**
     * @return string The url to the API endpoint
     */
    public function getApiEndpoint()
    {
        return $this->api_endpoint;
    }

    /**
     * Convert an email address into a 'subscriber hash' for identifying the subscriber in a method URL
     *
     * @param string $email The subscriber's email address
     *
     * @return string Hashed version of the input
     */
    public function subscriberHash($email)
    {
        return md5(strtolower($email));
    }

    /**
     * Was the last request successful?
     *
     * @return bool True for success, false for failure
     */
    public function success()
    {
        return $this->request_successful;
    }

    /**
     * Get the last error returned by either the network transport, or by the API.
     * If something didn't work, this should contain the string describing the problem.
     *
     * @return string|false describing the error
     */
    public function getLastError()
    {
        return $this->last_error ?: false;
    }

    /**
     * Get an array containing the HTTP headers and the body of the API response.
     *
     * @return array Assoc array with keys 'headers' and 'body'
     */
    public function getLastResponse()
    {
        return $this->last_response;
    }

    /**
     * Get an array containing the HTTP headers and the body of the API request.
     *
     * @return array Assoc array
     */
    public function getLastRequest()
    {
        return $this->last_request;
    }

    /**
     * Make an HTTP DELETE request - for deleting data
     *
     * @param string $method  URL of the API request method
     * @param array  $args    Assoc array of arguments (if any)
     * @param int    $timeout Timeout limit for request in seconds
     *
     * @return array|false Assoc array of API response, decoded from JSON
     */
    public function delete($method, $args = [], $timeout = self::TIMEOUT)
    {
        return $this->makeRequest('delete', $method, $args, $timeout);
    }

    /**
     * Make an HTTP GET request - for retrieving data
     *
     * @param string $method  URL of the API request method
     * @param array  $args    Assoc array of arguments (usually your data)
     * @param int    $timeout Timeout limit for request in seconds
     *
     * @return array|false Assoc array of API response, decoded from JSON
     */
    public function get($method, $args = [], $timeout = self::TIMEOUT)
    {
        return $this->makeRequest('get', $method, $args, $timeout);
    }

    /**
     * Make an HTTP PATCH request - for performing partial updates
     *
     * @param string $method  URL of the API request method
     * @param array  $args    Assoc array of arguments (usually your data)
     * @param int    $timeout Timeout limit for request in seconds
     *
     * @return array|false Assoc array of API response, decoded from JSON
     */
    public function patch($method, $args = [], $timeout = self::TIMEOUT)
    {
        return $this->makeRequest('patch', $method, $args, $timeout);
    }

    /**
     * Make an HTTP POST request - for creating and updating items
     *
     * @param string $method  URL of the API request method
     * @param array  $args    Assoc array of arguments (usually your data)
     * @param int    $timeout Timeout limit for request in seconds
     *
     * @return array|false Assoc array of API response, decoded from JSON
     */
    public function post($method, $args = [], $timeout = self::TIMEOUT)
    {
        return $this->makeRequest('post', $method, $args, $timeout);
    }

    /**
     * Make an HTTP PUT request - for creating new items
     *
     * @param string $method  URL of the API request method
     * @param array  $args    Assoc array of arguments (usually your data)
     * @param int    $timeout Timeout limit for request in seconds
     *
     * @return array|false Assoc array of API response, decoded from JSON
     *
     * @throws \Exception
     */
    public function put($method, $args = [], $timeout = self::TIMEOUT)
    {
        return $this->makeRequest('put', $method, $args, $timeout);
    }

    /**
     * Performs the underlying HTTP request. Not very exciting.
     *
     * @param string $http_verb The HTTP verb to use: get, post, put, patch, delete
     * @param string $method    The API method to be called
     * @param array  $args      Assoc array of parameters to be passed
     * @param int    $timeout
     *
     * @return array|false Assoc array of decoded result
     *
     * @throws \Exception
     */
    private function makeRequest($http_verb, $method, $args = [], $timeout = self::TIMEOUT)
    {
        $timeout = apply_filters('fluentform/mailchimp_api_timeout', $timeout);

        $url = $this->api_endpoint . '/' . $method;

        $response = $this->prepareStateForRequest($http_verb, $method, $url, $timeout);

        $headers = [
            'Accept'        => 'application/vnd.api+json',
            'Content-Type'  => 'application/vnd.api+json',
            'Authorization' => 'apikey ' . $this->api_key,
            'User-Agent'    => 'DrewM/MailChimp-API/3.0 (github.com/drewm/mailchimp-api)',
        ];

        if (isset($args['language'])) {
            $headers['Accept-Language'] = $args['language'];
            unset($args['language']);
        }

        $request_args = [
            'method'      => strtoupper($http_verb),
            'timeout'     => $timeout,
            'headers'     => $headers,
            'sslverify'   => $this->verify_ssl,
            'httpversion' => '1.0',
        ];

        // Handle GET requests - append query string to URL
        if ('get' === $http_verb) {
            if (! empty($args)) {
                $query = http_build_query($args, '', '&');
                $url = $url . '?' . $query;
            }
        } else {
            // For POST, PUT, PATCH, DELETE - encode body as JSON
            $encoded = json_encode($args);
            $this->last_request['body'] = $encoded;
            $request_args['body'] = $encoded;
        }

        $wp_response = wp_remote_request($url, $request_args);

        if (is_wp_error($wp_response)) {
            $this->last_error = $wp_response->get_error_message();
            $response['headers'] = null;
            $response['body'] = null;
            $this->last_response = $response;
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($wp_response);
        $response_body = wp_remote_retrieve_body($wp_response);
        $response_headers = wp_remote_retrieve_headers($wp_response);

        // Build response structure similar to cURL format for compatibility
        $response['headers'] = [
            'http_code'    => $response_code,
            'content_type' => wp_remote_retrieve_header($wp_response, 'content-type'),
            'total_time'   => isset($wp_response['headers']['total_time']) ? $wp_response['headers']['total_time'] : 0,
        ];

        $response['httpHeaders'] = $this->convertWpHeadersToArray($response_headers);
        $response['body'] = $response_body;

        // Store request headers for debugging
        $this->last_request['headers'] = $headers;

        $formattedResponse = $this->formatResponse($response);

        $this->determineSuccess($response, $formattedResponse, $timeout);

        return $formattedResponse;
    }

    /**
     * @param string  $http_verb
     * @param string  $method
     * @param string  $url
     * @param integer $timeout
     */
    private function prepareStateForRequest($http_verb, $method, $url, $timeout)
    {
        $this->last_error = '';

        $this->request_successful = false;

        $this->last_response = [
            'headers'     => null, // array of details from wp_remote_retrieve_response_code()
            'httpHeaders' => null, // array of HTTP headers
            'body'        => null, // content of the response
        ];

        $this->last_request = [
            'method'  => $http_verb,
            'path'    => $method,
            'url'     => $url,
            'body'    => '',
            'timeout' => $timeout,
        ];

        return $this->last_response;
    }

    /**
     * Get the HTTP headers as an array of header-name => header-value pairs.
     *
     * The "Link" header is parsed into an associative array based on the
     * rel names it contains. The original value is available under
     * the "_raw" key.
     *
     * @param string $headersAsString
     *
     * @return array
     */
    private function getHeadersAsArray($headersAsString)
    {
        $headers = [];

        foreach (explode("\r\n", $headersAsString) as $i => $line) {
            if (0 === $i) { // HTTP code
                continue;
            }

            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            list($key, $value) = explode(': ', $line);

            if ('Link' == $key) {
                $value = array_merge(
                    ['_raw' => $value],
                    $this->getLinkHeaderAsArray($value)
                );
            }

            $headers[$key] = $value;
        }

        return $headers;
    }

    /**
     * Extract all rel => URL pairs from the provided Link header value
     *
     * Mailchimp only implements the URI reference and relation type from
     * RFC 5988, so the value of the header is something like this:
     *
     * 'https://us13.api.mailchimp.com/schema/3.0/Lists/Instance.json; rel="describedBy", <https://us13.admin.mailchimp.com/lists/members/?id=XXXX>; rel="dashboard"'
     *
     * @param string $linkHeaderAsString
     *
     * @return array
     */
    private function getLinkHeaderAsArray($linkHeaderAsString)
    {
        $urls = [];

        if (preg_match_all('/<(.*?)>\s*;\s*rel="(.*?)"\s*/', $linkHeaderAsString, $matches)) {
            foreach ($matches[2] as $i => $relName) {
                $urls[$relName] = $matches[1][$i];
            }
        }

        return $urls;
    }

    /**
     * Convert WordPress headers object to array format
     *
     * @param \WP_HTTP_Requests_Response|\Requests_Utility_CaseInsensitiveDictionary $wp_headers WordPress headers object
     *
     * @return array Associative array of headers
     */
    private function convertWpHeadersToArray($wp_headers)
    {
        $headers = [];

        if (is_object($wp_headers) && method_exists($wp_headers, 'getAll')) {
            $wp_headers = $wp_headers->getAll();
        }

        if (is_array($wp_headers)) {
            foreach ($wp_headers as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $headers[$key] = $value;

                // Handle Link header parsing
                if ('Link' === $key || 'link' === strtolower($key)) {
                    $headers[$key] = array_merge(
                        ['_raw' => $value],
                        $this->getLinkHeaderAsArray($value)
                    );
                }
            }
        }

        return $headers;
    }

    /**
     * Decode the response and format any error messages for debugging
     *
     * @param array $response The response from the WordPress HTTP request
     *
     * @return array|false The JSON decoded into an array
     */
    private function formatResponse($response)
    {
        $this->last_response = $response;

        if (! empty($response['body'])) {
            return json_decode($response['body'], true);
        }

        return false;
    }


    /**
     * Check if the response was successful or a failure. If it failed, store the error.
     *
     * @param array       $response          The response from the WordPress HTTP request
     * @param array|false $formattedResponse The response body payload from the WordPress HTTP request
     * @param int         $timeout           The timeout supplied to the WordPress HTTP request.
     *
     * @return bool If the request was successful
     */
    private function determineSuccess($response, $formattedResponse, $timeout)
    {
        $status = $this->findHTTPStatus($response, $formattedResponse);

        if ($status >= 200 && $status <= 299) {
            $this->request_successful = true;
            return true;
        }

        if (isset($formattedResponse['detail'])) {
            $this->last_error = sprintf('%d: %s', $formattedResponse['status'], $formattedResponse['detail']);
            return false;
        }

        if ($timeout > 0 && $response['headers'] && isset($response['headers']['total_time']) && $response['headers']['total_time'] >= $timeout) {
            $this->last_error = sprintf('Request timed out after %f seconds.', $response['headers']['total_time']);
            return false;
        }

        $this->last_error = 'Unknown error, call getLastResponse() to find out what happened.';
        return false;
    }

    /**
     * Find the HTTP status code from the headers or API response body
     *
     * @param array       $response          The response from the WordPress HTTP request
     * @param array|false $formattedResponse The response body payload from the WordPress HTTP request
     *
     * @return int HTTP status code
     */
    private function findHTTPStatus($response, $formattedResponse)
    {
        if (! empty($response['headers']) && isset($response['headers']['http_code'])) {
            return (int) $response['headers']['http_code'];
        }

        if (! empty($response['body']) && isset($formattedResponse['status'])) {
            return (int) $formattedResponse['status'];
        }

        return 418;
    }
}
