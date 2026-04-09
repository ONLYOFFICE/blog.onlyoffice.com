<?php

namespace FluentForm\App\Modules\Report;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Registerer\TranslationString;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Payments\PaymentHelper;

class ReportHandler
{
    protected $app;
    
    public function register($app)
    {
        $this->app = $app;
        $app->addAction('fluentform/render_report', [$this, 'renderReport']);
    }

    public function renderReport()
    {
        wp_enqueue_script('fluentform_reports');
        wp_enqueue_style('fluentform_reports');


        // Maybe load intl-tel-input flags
        if (Helper::hasPro() && defined('FLUENTFORMPRO_DIR_URL')) {
            $file = is_rtl() ? 'intlTelInput-rtl.min.css' : 'intlTelInput.min.css';
            wp_enqueue_style(
                'intlTelInput',
                FLUENTFORMPRO_DIR_URL . 'public/libs/intl-tel-input/css/' . $file,
                [],
                '24.2.0'
            );
        }
        

        $hasPayment = false;
        $paymentSettings = get_option('__fluentform_payment_module_settings');
        if ($paymentSettings && ArrayHelper::get($paymentSettings, 'status') === 'yes') {
            $hasPayment = true;
        }

        wp_localize_script('fluentform_reports', 'FluentFormApp', [
            'has_payment'      => $hasPayment,
            'has_pro'          => Helper::hasPro(),
            'has_pdf'          => defined('FLUENTFORM_PDF_VERSION'),
            'reports_i18n'     => TranslationString::getReportsI18n(),
            'payment_statuses' => PaymentHelper::getPaymentStatuses(),
            'payment_methods'  => apply_filters('fluentform/available_payment_methods', [])
        ]);

        $this->app->view->render('admin.reports.index', [
            'logo' => fluentformMix('img/fluentform-logo.svg'),
        ]);
    }
}
