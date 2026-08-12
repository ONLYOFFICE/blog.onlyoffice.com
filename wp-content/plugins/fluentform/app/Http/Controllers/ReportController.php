<?php

namespace FluentForm\App\Http\Controllers;

use Exception;
use FluentForm\App\Modules\Acl\Acl;
use FluentForm\App\Services\Report\ReportService;

class ReportController extends Controller
{
    private function sanitizeReportAttributes($formId = null)
    {
        $sanitizeMap = [
            'period'   => 'sanitize_text_field',
            'group_by' => 'sanitize_text_field',
            'status'   => 'sanitize_text_field',
        ];

        $attributes = fluentform_backend_sanitizer($this->request->all(), $sanitizeMap);
        $requestFormId = $this->request->get('form_id');

        if (isset($attributes['date_range']) && is_array($attributes['date_range'])) {
            $attributes['date_range'] = array_map('sanitize_text_field', $attributes['date_range']);
        }

        $resolvedFormId = Acl::normalizeFormId($formId);

        if (!$resolvedFormId) {
            $resolvedFormId = Acl::normalizeFormId($requestFormId);
        }

        if ($resolvedFormId) {
            $attributes['form_id'] = $resolvedFormId;
        } else {
            unset($attributes['form_id']);
        }

        return $attributes;
    }

    public function form(ReportService $reportService, $formId)
    {
        try {
            return $this->sendSuccess(
                $reportService->form($this->sanitizeReportAttributes($formId))
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get Submission Report
     * @return \WP_REST_Response
     */
    public function submissions(ReportService $reportService)
    {
        try {
            return $this->sendSuccess(
                $reportService->submissions($this->sanitizeReportAttributes())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    
    /**
     * Get Forms for Dropdown
     * @return \WP_REST_Response
     */
    public function getFormsDropdown(ReportService $reportService)
    {
        try {
            return $this->sendSuccess(
                $reportService->getFormsDropdown()
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get payment revenue grouped by different criteria
     * @return \WP_REST_Response
     */
    public function netRevenue(ReportService $reportService)
    {
        try {
            $data = apply_filters('fluentform/reports/revenue_analysis', [], $this->sanitizeReportAttributes());
            return $this->sendSuccess($data);
            
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get submission analysis grouped by different criteria
     * @return \WP_REST_Response
     */
    public function submissionsAnalysis()
    {
        try {
            $data = apply_filters('fluentform/reports/submissions_analysis', [], $this->sanitizeReportAttributes());
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get Overview Chart Data
     * @return \WP_REST_Response
     */
    public function getOverviewChart(ReportService $reportService)
    {
        try {
            return $this->sendSuccess(
                $reportService->getOverviewChart($this->sanitizeReportAttributes())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get Revenue Chart Data
     * @return \WP_REST_Response
     */
    public function getRevenueChart(ReportService $reportService)
    {
        try {
            return $this->sendSuccess(
                $reportService->getRevenueChart($this->sanitizeReportAttributes())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get Completion Rate Data
     * @return \WP_REST_Response
     */
    public function getCompletionRate()
    {
        try {
            $data = apply_filters('fluentform/reports/completion_rate', [], $this->sanitizeReportAttributes());
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get Form Stats Data
     * @return \WP_REST_Response
     */
    public function getFormStats(ReportService $reportService)
    {
        try {
            return $this->sendSuccess(
                $reportService->getFormStats($this->sanitizeReportAttributes())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get Heatmap Data
     * @return \WP_REST_Response
     */
    public function getHeatmapData()
    {
        try {
            $data = apply_filters('fluentform/reports/heatmap_data', [], $this->sanitizeReportAttributes());
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get Country Heatmap Data
     * @return \WP_REST_Response
     */
    public function getCountryHeatmap(ReportService $reportService)
    {
        try {
            $data = apply_filters('fluentform/reports/country_heatmap', [], $this->sanitizeReportAttributes());
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get API Logs Data
     * @return \WP_REST_Response
     */
    public function getApiLogs(ReportService $reportService)
    {
        try {
            return $this->sendSuccess(
                $reportService->getApiLogs($this->sanitizeReportAttributes())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get Top Performing Forms Data
     * @return \WP_REST_Response
     */
    public function getTopPerformingForms(ReportService $reportService)
    {
        try {
            return $this->sendSuccess(
                $reportService->getTopPerformingForms($this->sanitizeReportAttributes())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get Subscriptions Data
     * @return \WP_REST_Response
     */
    public function getSubscriptions()
    {
        try {
            $data = apply_filters('fluentform/reports/subscriptions', [], $this->sanitizeReportAttributes());
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get Payment Types Data
     * @return \WP_REST_Response
     */
    public function getPaymentTypes(ReportService $reportService)
    {
        try {
            return $this->sendSuccess(
                $reportService->getPaymentTypes($this->sanitizeReportAttributes())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
}
