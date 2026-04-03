<?php

namespace FluentForm\App\Services\Report;

use Exception;
use FluentForm\App\Models\Form;
use FluentForm\App\Models\Submission;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentForm\Framework\Support\Sanitizer;


class ReportService
{
    /**
     * Get Form Report
     * @param array $attr
     * @return array|mixed $response
     * @throws Exception
     */
    public function form($attr = [])
    {
        $formId = (int) Arr::get($attr, 'form_id');
        ReportHelper::maybeMigrateData($formId);
        try {
            $form = Form::findOrFail($formId);
        } catch (Exception $e) {
            throw new \Exception("The form couldn't be found.");
        }

        $statuses = Arr::get($attr, 'statuses', ['read', 'unread', 'unapproved', 'approved', 'declined', 'unconfirmed', 'confirmed']);
        return ReportHelper::generateReport($form, $statuses);
    }

    /**
     * Get Submissions Report
     *
     * @throws Exception
     */
    public function submissions($args)
    {
        try {
            return Submission::report($args);
        } catch (Exception $e) {
            throw new Exception(esc_html($e->getMessage()));
        }
    }

    /**
     * Get forms for dropdown
     *
     * @return array
     */
    public function getFormsDropdown()
    {
        $forms = Form::select(['id', 'title', 'has_payment'])
                     ->orderBy('id', 'DESC')
                     ->get();

        return [
            'forms' => $forms
        ];
    }

    /**
     * Get Overview Chart Data
     *
     * @param array $data
     * @return array
     */
    public function getOverviewChart($data)
    {
        $data = $this->sanitizeCommonParams($data);
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $formId = $data['form_id'];

        return [
            'overview_chart' => ReportHelper::getOverviewChartData($startDate, $endDate, $formId, 'activity')
        ];
    }

    /**
     * Get Revenue Chart Data
     *
     * @param array $data
     * @return array
     */
    public function getRevenueChart($data)
    {
        $data = $this->sanitizeCommonParams($data);
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $formId = $data['form_id'];

        return [
            'revenue_chart' => ReportHelper::getOverviewChartData($startDate, $endDate, $formId, 'revenue')
        ];
    }


    /**
     * Get Form Stats Data
     *
     * @param array $data
     * @return array
     */
    public function getFormStats($data)
    {
        $data = $this->sanitizeCommonParams($data);
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $formId = $data['form_id'];

        return [
            'form_stats' => ReportHelper::getFormStats($startDate, $endDate, $formId)
        ];
    }

    
    /**
     * Get API Logs Data
     *
     * @param array $data
     * @return array
     */
    public function getApiLogs($data)
    {
        $data = $this->sanitizeCommonParams($data);
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $formId = $data['form_id'];

        return [
            'api_logs' => ReportHelper::getApiLogs($startDate, $endDate, $formId)
        ];
    }

    /**
     * Get Top Performing Forms Data
     *
     * @param array $data
     * @return array
     */
    public function getTopPerformingForms($data)
    {
        $data = $this->sanitizeCommonParams($data);
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $metric = Arr::get($data, 'metric', 'entries');

        if (!in_array($metric, ['entries', 'payments', 'views'])) {
            $metric = 'entries';
        }

        $result = ReportHelper::getTopPerformingForms($startDate, $endDate, $metric);
        return [
            'top_performing_forms' => Arr::get($result, 'data', []),
            'disable_message'      => Arr::get($result, 'disable_message', '')
        ];
    }
    
    
    public function getPaymentTypes($data)
    {
        $data = $this->sanitizeCommonParams($data);
        
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $formId = $data['form_id'];
        $data['payment_types'] = [
            'subscription' => ReportHelper::getPaymentsByType($startDate, $endDate, 'subscription', $formId),
            'onetime'      => ReportHelper::getPaymentsByType($startDate, $endDate, 'onetime', $formId),
        ];
        return $data;
    }
   
    /**
     * Sanitize and prepare common parameters
     *
     * @param array $data
     * @return array
     */
    public function sanitizeCommonParams($data)
    {
        $data = Sanitizer::sanitize($data, [
            'start_date' => 'sanitizeTextField',
            'end_date' => 'sanitizeTextField',
            'metric' => 'sanitizeTextField',
        ]);

        $startDate = Arr::get($data, 'start_date');
        $endDate = Arr::get($data, 'end_date');
        $formId = intval(Arr::get($data, 'form_id'));

        // Set default date range if not provided
        if (!$startDate || !$endDate) {
            $now = new \DateTime();
            $endDate = $now->format('Y-m-d 23:59:59');

            $thirtyDaysAgo = new \DateTime();
            $thirtyDaysAgo->modify('-30 days');
            $startDate = $thirtyDaysAgo->format('Y-m-d 00:00:00');
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'form_id' => $formId,
            'metric' => Arr::get($data, 'metric', 'entries')
        ];
    }
}
