<?php

namespace FluentForm\App\Modules\Payments\PaymentMethods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

abstract class BasePaymentMethod
{
    protected $key = '';

    protected $settingsKey = '';

    public function __construct($key)
    {
        $this->key = $key;
        $this->settingsKey = 'fluentform_payment_settings_'.$key;

        add_filter('fluentform/payment_methods_global_settings', function ($methods) {
            $fields = $this->getGlobalFields();
            if($fields) {
                $methods[$this->key] = $fields;
            }
            return $methods;
        });
        add_filter('fluentform/payment_settings_' . $this->key, array($this, 'getGlobalSettings'));
    }

    abstract public function getGlobalFields();

    abstract public function getGlobalSettings();

}
