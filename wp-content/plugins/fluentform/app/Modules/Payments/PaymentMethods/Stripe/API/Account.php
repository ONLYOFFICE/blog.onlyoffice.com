<?php

namespace FluentForm\App\Modules\Payments\PaymentMethods\Stripe\API;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\Framework\Helpers\ArrayHelper;

if (!defined('ABSPATH')) {
    exit;
}

class Account
{
    use RequestProcessor;
    public static function retrive($accountId, $key)
    {
        ApiRequest::set_secret_key($key);
        if ($accountId) {
            $account = ApiRequest::retrieve('accounts/' . $accountId);
        } else {
            $account = ApiRequest::retrieve('account');
        }
        return self::processResponse($account);
    }
}
