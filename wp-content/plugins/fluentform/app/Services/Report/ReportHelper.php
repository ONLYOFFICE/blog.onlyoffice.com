<?php

namespace FluentForm\App\Services\Report;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\EntryDetails;
use FluentForm\App\Models\Form;
use FluentForm\App\Models\FormAnalytics;
use FluentForm\App\Models\Log;
use FluentForm\App\Models\Submission;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Modules\Payments\PaymentHelper;
use FluentForm\App\Services\Submission\SubmissionService;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;

class ReportHelper
{
    public static function generateReport($form, $statuses = ['read', 'unread', 'unapproved', 'approved', 'declined', 'unconfirmed', 'confirmed'])
    {
        $formInputs = FormFieldsParser::getEntryInputs($form, ['admin_label', 'element', 'options']);
        $inputLabels = FormFieldsParser::getAdminLabels($form, $formInputs);

        $elements = [];
        foreach ($formInputs as $inputName => $input) {
            $elements[$inputName] = $input['element'];
            if ('select_country' == $input['element']) {
                $formInputs[$inputName]['options'] = getFluentFormCountryList();
            }
        }

        $reportableInputs = Helper::getReportableInputs();
        $formReportableInputs = array_intersect($reportableInputs, array_values($elements));
        $reportableInputs = Helper::getSubFieldReportableInputs();
        $formSubFieldInputs = array_intersect($reportableInputs, array_values($elements));


        if (!$formReportableInputs && !$formSubFieldInputs) {
            return [
                'report_items'  => (object)[],
                'total_entries' => static::getEntryCounts($form->id, $statuses),
                'browsers'      => static::getBrowserCounts($form->id, $statuses),
                'devices'       => static::getDeviceCounts($form->id, $statuses),
            ];
        }

        $inputs = [];
        $subfieldInputs = [];
        foreach ($elements as $elementKey => $element) {
            if (in_array($element, $formReportableInputs)) {
                $inputs[$elementKey] = $element;
            }
            if (in_array($element, $formSubFieldInputs)) {
                $subfieldInputs[$elementKey] = $element;
            }
        }

        $reports = static::getInputReport($form->id, array_keys($inputs), $statuses);

        $subFieldReports = static::getSubFieldInputReport($form->id, array_keys($subfieldInputs), $statuses);
        $reports = array_merge($reports, $subFieldReports);
        foreach ($reports as $reportKey => $report) {
            $reports[$reportKey]['label'] = $inputLabels[$reportKey];
            $reports[$reportKey]['element'] = Arr::get($inputs, $reportKey, []);
            $reports[$reportKey]['options'] = $formInputs[$reportKey]['options'];
        }

        return [
            'report_items'  => $reports,
            'total_entries' => static::getEntryCounts($form->id, $statuses),
            'browsers'      => static::getBrowserCounts($form->id, $statuses),
            'devices'       => static::getDeviceCounts($form->id, $statuses),
        ];
    }

    public static function getInputReport(
        $formId,
        $fieldNames,
        $statuses = ['read', 'unread', 'unapproved', 'approved', 'declined', 'unconfirmed', 'confirmed']
    ) {
        if (!$fieldNames) {
            return [];
        }

        $reports = EntryDetails::select(['field_name', 'sub_field_name', 'field_value'])
            ->where('form_id', $formId)
            ->whereIn('field_name', $fieldNames)
            ->when(
                is_array($statuses) && (count($statuses) > 0),
                function ($q) use ($statuses) {
                    return $q->whereHas('submission', function ($q) use ($statuses) {
                        return $q->whereIn('status', $statuses);
                    });
                })
            ->selectRaw('COUNT(field_name) AS total_count')
            ->groupBy(['field_name', 'field_value'])
            ->get();

        $formattedReports = [];
        foreach ($reports as $report) {
            $formattedReports[$report->field_name]['reports'][] = [
                'value'     => Helper::safeUnserialize($report->field_value),
                'count'     => $report->total_count,
                'sub_field' => $report->sub_field_name,
            ];

            $formattedReports[$report->field_name]['total_entry'] = static::getEntryTotal($report->field_name, $formId,
                $statuses);
        }
        if ($formattedReports) {
            //sync with form field order
            $formattedReports = array_replace(array_intersect_key(array_flip($fieldNames), $formattedReports),
                $formattedReports);
        }
        return $formattedReports;
    }

    public static function getSubFieldInputReport($formId, $fieldNames, $statuses)
    {
        if (!$fieldNames) {
            return [];
        }

        $reports = EntryDetails::select(['field_name', 'sub_field_name', 'field_value'])
            ->selectRaw('COUNT(field_name) AS total_count')
            ->where('form_id', $formId)
            ->whereIn('field_name', $fieldNames)
            ->when(
                is_array($statuses) && (count($statuses) > 0),
                function ($q) use ($statuses) {
                    return $q->whereHas('submission', function ($q) use ($statuses) {
                        return $q->whereIn('status', $statuses);
                    });
                })
            ->groupBy(['field_name', 'field_value', 'sub_field_name'])
            ->get()->toArray();
        return static::getFormattedReportsForSubInputs($reports, $formId, $statuses);
    }

    protected static function getFormattedReportsForSubInputs($reports, $formId, $statuses)
    {
        if (!count($reports)) {
            return [];
        }
        $formattedReports = [];
        foreach ($reports as $report) {
            static::setReportForSubInput((array)$report, $formattedReports);
        }
        foreach ($formattedReports as $fieldName => $val) {
            $formattedReports[$fieldName]['total_entry'] = static::getEntryTotal(
                Arr::get($report, 'field_name'),
                $formId,
                $statuses
            );
            $formattedReports[$fieldName]['reports'] = array_values(
                $formattedReports[$fieldName]['reports']
            );
        }
        return $formattedReports;
    }

    protected static function setReportForSubInput($report, &$formattedReports)
    {
        $filedValue = Helper::safeUnserialize(Arr::get($report, 'field_value'));

        if (is_array($filedValue)) {
            foreach ($filedValue as $fVal) {
                static::setReportForSubInput(
                    array_merge($report, ['field_value' => $fVal]),
                    $formattedReports
                );
            }
        } else {
            $value = Arr::get($report, 'sub_field_name') . ' : ' . $filedValue;
            $count = Arr::get($formattedReports, $report['field_name'] . '.reports.' . $value . '.count');
            $count = $count ? $count + Arr::get($report, 'total_count') : Arr::get($report, 'total_count');

            $formattedReports[$report['field_name']]['reports'][$value] = [
                'value'     => $value,
                'count'     => $count,
                'sub_field' => $report['sub_field_name'],
            ];
        }
    }

    public static function getEntryTotal($fieldName, $formId, $statuses = false)
    {
        return EntryDetails::select('id')->where('form_id', $formId)
            ->where('field_name', $fieldName)
            ->when(
                is_array($statuses) && (count($statuses) > 0),
                function ($q) use ($statuses) {
                    return $q->whereHas('submission', function ($q) use ($statuses) {
                        return $q->whereIn('status', $statuses);
                    });
                }
            )
            ->distinct(['field_name', 'submission_id'])
            ->count();
    }

    private static function getEntryCounts($formId, $statuses = false)
    {
        return Submission::where('form_id', $formId)
            ->when(
                is_array($statuses) && (count($statuses) > 0),
                function ($q) use ($statuses) {
                    return $q->whereIn('status', $statuses);
                })
            ->when(!$statuses, function ($q) {
                return $q->where('status', '!=', 'trashed');
            })->count();
    }

    public static function getBrowserCounts($formId, $statuses = false)
    {
        return static::getCounts($formId, 'browser', $statuses);
    }

    public static function getDeviceCounts($formId, $statuses = false)
    {
        return static::getCounts($formId, 'device', $statuses);
    }

    private static function getCounts($formId, $for, $statuses)
    {
        $deviceCounts = Submission::select([
            "$for",
        ])
            ->selectRaw('COUNT(id) as total_count')
            ->where('form_id', $formId)
            ->when(
                is_array($statuses) && (count($statuses) > 0),
                function ($q) use ($statuses) {
                    return $q->whereIn('status', $statuses);
                })
            ->when(!$statuses, function ($q) {
                return $q->where('status', '!=', 'trashed');
            })
            ->groupBy("$for")->get();

        $formattedData = [];
        foreach ($deviceCounts as $deviceCount) {
            $formattedData[$deviceCount->{$for}] = $deviceCount->total_count;
        }
        return $formattedData;
    }

    public static function maybeMigrateData($formId)
    {
        if ('yes' == Helper::getFormMeta($formId, 'report_data_migrated')) {
            return true;
        }

        // Check if migration is already in progress (with 5-minute staleness timeout)
        $migrationStatus = Helper::getFormMeta($formId, 'report_data_migration_status');
        if ($migrationStatus && strpos($migrationStatus, 'processing:') === 0) {
            $startedAt = (int) substr($migrationStatus, 11);
            if ((time() - $startedAt) < 300) {
                return true;
            }
        }

        Helper::setFormMeta($formId, 'report_data_migration_status', 'processing:' . time());

        // Dispatch a non-blocking background request
        $args = [
            'timeout'  => 0.1,
            'blocking' => false,
            'body'     => [
                'action'  => 'fluentform_report_data_migrate',
                'form_id' => $formId,
                'nonce'   => wp_create_nonce('fluentform_report_data_migrate'),
            ],
            'cookies'  => is_user_logged_in() ? wp_unslash($_COOKIE) : [],
            'sslverify' => apply_filters('fluentform/https_local_ssl_verify', false),
        ];

        wp_remote_post(esc_url_raw(Helper::getAjaxUrl()), $args);

        return true;
    }

    /**
     * Ensure submission_id index exists on entry_details table for efficient anti-join queries.
     */
    private static $indexEnsured = false;

    private static function ensureEntryDetailsIndex()
    {
        if (static::$indexEnsured) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'fluentform_entry_details';

        // Check if index already exists
        $indexExists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(1) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND INDEX_NAME = 'idx_submission_id'",
            $table
        ));

        if (!$indexExists) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $table is hardcoded prefix + table name, not user input
            $wpdb->query("ALTER TABLE `{$table}` ADD INDEX `idx_submission_id` (`submission_id`)");
        }

        static::$indexEnsured = true;
    }

    public static function runMigrationBatch($formId)
    {
        if ('yes' == Helper::getFormMeta($formId, 'report_data_migrated')) {
            return;
        }

        // Acquire a MySQL advisory lock to prevent concurrent batch execution per form.
        // GET_LOCK returns 1 if acquired, 0 if timed out, NULL on error.
        global $wpdb;
        $lockName = 'ff_report_migrate_' . intval($formId);
        $gotLock = $wpdb->get_var($wpdb->prepare("SELECT GET_LOCK(%s, 0)", $lockName));

        if (!$gotLock) {
            return; // Another worker holds the lock for this form
        }

        try {
            // Ensure index exists for efficient doesntHave('entryDetails') queries
            static::ensureEntryDetailsIndex();

            $batchSize = apply_filters('fluentform/report_migration_batch_size', 200);
            $maxBatches = apply_filters('fluentform/report_migration_max_batches', 50);
            $submissionService = new SubmissionService();
            $lastId = (int) Helper::getFormMeta($formId, 'report_data_migration_last_id');

            for ($batch = 0; $batch < $maxBatches; $batch++) {
                $unmigratedData = Submission::select(['id', 'response'])
                    ->where('form_id', $formId)
                    ->where('id', '>', $lastId)
                    ->doesntHave('entryDetails')
                    ->orderBy('id', 'ASC')
                    ->limit($batchSize)
                    ->get();

                if (!$unmigratedData || count($unmigratedData) === 0) {
                    break;
                }

                foreach ($unmigratedData as $datum) {
                    $lastId = $datum->id;
                    $value = json_decode($datum->response, true);
                    if (is_array($value) && !empty($value)) {
                        $submissionService->recordEntryDetails($datum->id, $formId, $value);
                    }

                    // If no entry_details were created (empty/invalid response or all values filtered),
                    // insert a sentinel so doesntHave('entryDetails') excludes this row
                    $hasDetails = EntryDetails::where('submission_id', $datum->id)->exists();
                    if (!$hasDetails) {
                        EntryDetails::insert([
                            'form_id'        => $formId,
                            'submission_id'  => $datum->id,
                            'field_name'     => '_migration_skipped',
                            'sub_field_name' => '',
                            'field_value'    => '',
                        ]);
                    }
                }

                Helper::setFormMeta($formId, 'report_data_migration_last_id', $lastId);

                if ($unmigratedData->count() < $batchSize) {
                    break;
                }
            }

            // Only mark complete if no unmigrated rows remain
            $remaining = Submission::where('form_id', $formId)
                ->doesntHave('entryDetails')
                ->count();

            if ($remaining === 0) {
                Helper::setFormMeta($formId, 'report_data_migrated', 'yes');
                Helper::setFormMeta($formId, 'report_data_migration_last_id', 0);
            }

            Helper::setFormMeta($formId, 'report_data_migration_status', 'completed');
        } finally {
            $wpdb->query($wpdb->prepare("SELECT RELEASE_LOCK(%s)", $lockName));
        }
    }

    /**
     * Get overview chart data
     */
    public static function getOverviewChartData($startDate, $endDate, $formId, $view)
    {
        // Process and fix date ranges if needed
        list($startDate, $endDate) = self::processDateRange($startDate, $endDate);

        // Calculate date difference to determine grouping
        $startDateTime = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);
        $interval = $startDateTime->diff($endDateTime);
        $daysInterval = $interval->days + 1;

        // Determine grouping mode based on date range
        $groupingMode = self::getGroupingMode($daysInterval);
        $data = self::getAggregatedData($startDate, $endDate, $groupingMode, $view, $formId);
        // Get date labels based on grouping mode
        $dateLabels = self::getDateLabels($startDateTime, $endDateTime, $groupingMode);
        // Format the data for the chart
        $chartData = self::formatDataForChart($dateLabels, $data, $formId);
        // Get views data based on the ip
        $views = self::getFormViews($startDate, $endDate, $groupingMode, $formId);
        if ($views) {
            $chartData['values']['views'] = array_values(self::fillMissingData($dateLabels['dates'], $views));
        }
        return $chartData;
    }





    public static function getFormStats($startDate, $endDate, $formId)
    {
        // Process and fix date ranges if needed
        list($startDate, $endDate) = self::processDateRange($startDate, $endDate);

        // Calculate the date range duration to determine previous period
        $startDateTime = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);
        $interval = $startDateTime->diff($endDateTime);
        $daysDifference = $interval->days;

        // Calculate previous period dates (same duration, shifted back)
        $previousEndDateTime = clone $startDateTime;
        $previousEndDateTime->modify('-1 day');
        $previousStartDateTime = clone $previousEndDateTime;
        $previousStartDateTime->modify("-{$daysDifference} days");

        $previousStartDate = $previousStartDateTime->format('Y-m-d H:i:s');
        $previousEndDate = $previousEndDateTime->format('Y-m-d H:i:s');

        // Get submission counts
        $periodSubmissions = Submission::whereBetween('created_at', [$startDate, $endDate])
            ->when($formId, function ($q) use ($formId) {
                return $q->where('form_id', $formId);
            })->count();
        $previousPeriodSubmissions = Submission::whereBetween('created_at',
            [$previousStartDate, $previousEndDate])
            ->when($formId, function ($q) use ($formId) {
                return $q->where('form_id', $formId);
            })->count();

        // Get submission status counts (grouped)
        $statusCounts = Submission::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->when($formId, function ($q) use ($formId) {
                return $q->where('form_id', $formId);
            })
            ->groupBy('status')
            ->pluck('count', 'status');

        $unreadSubmissions = intval(Arr::get($statusCounts, 'unread', 0));
        $readSubmissions = intval(Arr::get($statusCounts, 'read', 0));
        $periodSpamSubmissions = intval(Arr::get($statusCounts, 'spam', 0));


        $previousStatusCounts = Submission::whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->selectRaw('status, COUNT(*) as count')
            ->when($formId, function ($q) use ($formId) {
                return $q->where('form_id', $formId);
            })
            ->groupBy('status')
            ->pluck('count', 'status');

        $previousSpamSubmissions = intval(Arr::get($previousStatusCounts, 'spam', 0));

        // Get active integrations count from wp_options
        $modulesStatus = get_option('fluentform_global_modules_status');
        $activeIntegrations = count(array_filter($modulesStatus, function ($status) {
            return $status === 'yes' || $status == 1 || $status == 'true';
        }));

        // Calculate period growth percentage
        $growthPercentage = 0;
        if ($previousPeriodSubmissions > 0) {
            $growthPercentage = round((($periodSubmissions - $previousPeriodSubmissions) / $previousPeriodSubmissions) * 100,
                1);
        } elseif ($periodSubmissions > 0) {
            $growthPercentage = 100;
        }

        $growthText = $growthPercentage > 0 ? '+' . $growthPercentage . '%' : $growthPercentage . '%';
        $growthType = $growthPercentage > 0 ? 'up' : ($growthPercentage < 0 ? 'down' : 'neutral');

        // calculate spam percentage
        $spamPercentage = 0;
        if ($previousSpamSubmissions > 0) {
            $spamPercentage = round((($periodSpamSubmissions - $previousSpamSubmissions) / $previousSpamSubmissions) * 100,
                1);
        } elseif ($periodSpamSubmissions > 0) {
            $spamPercentage = 100;
        }
        $spamText = $spamPercentage > 0 ? '+' . $spamPercentage . '%' : $spamPercentage . '%';
        $spamType = $spamPercentage > 0 ? 'down' : ($spamPercentage < 0 ? 'up' : 'neutral'); // Refunds going up is bad

        // Active forms
        $periodActiveFormsCount = Form::where('status', 'published')->whereBetween('created_at',
            [$startDate, $endDate])->count();
        $previousActiveFormsCount = Form::where('status', 'published')->whereBetween('created_at',
            [$previousStartDate, $previousEndDate])->count();
        $activeFormsPercentage = 0;
        if ($previousActiveFormsCount > 0) {
            $activeFormsPercentage = round((($periodActiveFormsCount - $previousActiveFormsCount) / $previousActiveFormsCount) * 100,
                1);
        } elseif ($periodActiveFormsCount > 0) {
            $activeFormsPercentage = 100;
        }
        $activeFormsText = $activeFormsPercentage > 0 ? '+' . $activeFormsPercentage . '%' : $activeFormsPercentage . '%';
        $activeFormsType = $activeFormsPercentage > 0 ? 'up' : ($activeFormsPercentage < 0 ? 'down' : 'neutral');

        $readRate = $periodSubmissions > 0 ? round(($readSubmissions / $periodSubmissions) * 100, 1) : 0;

        $stats = [
            'period'               => $daysDifference . ' days',
            'total_submissions'    => [
                'value'        => $periodSubmissions,
                'period_value' => $periodSubmissions,
                'change'       => $growthText,
                'change_type'  => $growthType
            ],
            'spam_submissions'     => [
                'value'        => $periodSpamSubmissions,
                'period_value' => $previousSpamSubmissions,
                'change'       => $spamText,
                'change_type'  => $spamType
            ],
            'active_integrations'  => [
                'value' => $activeIntegrations,
            ],
            'unread_submissions'   => [
                'value' => $unreadSubmissions,
            ],
            'read_submissions'     => [
                'value' => $readSubmissions,
            ],
            'active_forms'         => [
                'value'       => $periodActiveFormsCount,
                'change'      => $activeFormsText,
                'change_type' => $activeFormsType
            ],
            'read_submission_rate' => [
                'value' => $readRate,
            ]
        ];

        // Add payment statistics if payment module is enabled
        $paymentSettings = get_option('__fluentform_payment_module_settings');
        if ($paymentSettings && Arr::get($paymentSettings, 'status') === 'yes') {
            // Get payment statistics
            $paymentStats = self::getPaymentStats($startDate, $endDate, $previousStartDate, $previousEndDate, $formId);
            $stats = array_merge($stats, $paymentStats);
        }

        return $stats;
    }


    /**
     * Initialize heatmap data structure based on aggregation type
     */
    protected static function initializeHeatmapData($aggregationType)
    {
        if ($aggregationType === 'day_of_week') {
            $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            $heatmapData = [];
            foreach ($dayNames as $day) {
                $heatmapData[$day] = array_fill(0, 24, 0); // 24 time slots (0-23 hours), all initialized to 0
            }
            return $heatmapData;
        }

        return [];
    }

    /**
     * Get submission data for heatmap with appropriate grouping
     * Optimized version with better query performance
     */
    protected static function getHeatmapSubmissionData($startDate, $endDate, $formId, $aggregationType)
    {
        if ($aggregationType === 'day_of_week') {
            $query = Submission::selectRaw('
                DAYOFWEEK(created_at) as day_of_week,
                HOUR(created_at) as submission_hour,
                COUNT(*) as count
            ')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->when($formId, function ($q) use ($formId) {
                    return $q->where('form_id', $formId);
                })
                ->whereNotIn('status', ['trashed', 'spam'])
                ->groupBy('day_of_week', 'submission_hour')
                ->orderBy('day_of_week')
                ->orderBy('submission_hour');

            return $query->get();
        }

        return collect([]);
    }


    public static function getApiLogs($startDate, $endDate, $formId = null)
    {
        // Process date range
        list($startDate, $endDate) = self::processDateRange($startDate, $endDate);

        // Calculate date difference to determine grouping
        $startDateTime = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);
        $interval = $startDateTime->diff($endDateTime);
        $daysInterval = $interval->days + 1;

        // Determine grouping mode based on date range
        $groupingMode = self::getGroupingMode($daysInterval);

        // Define the date format based on grouping mode
        if ($groupingMode === 'day') {
            $dateFormat = "DATE(created_at)";
        } elseif ($groupingMode === '3days') {
            $dateFormat = "DATE(created_at)";
        } elseif ($groupingMode === 'week') {
            $dateFormat = "DATE(DATE_ADD(created_at, INTERVAL(-WEEKDAY(created_at)) DAY))";
        } else { // month
            $dateFormat = "DATE_FORMAT(created_at, '%Y-%m-01')";
        }

        // Components to exclude
        $excludedComponents = [
            'postFeeds',
            'AdminApproval',
            'Payment',
            'EntryEditor',
            'DoubleOptin',
            'Subscription',
            'UserRegistration',
            'Akismet Integration',
            'CleanTalk API Integration'
        ];

        // Get logs grouped by date and status using Eloquent, excluding specific components
        $logsQuery = Log::whereBetween('created_at', [$startDate, $endDate]);

        // Exclude components - handle both NULL and specific values
        $logsQuery->where(function ($query) use ($excludedComponents) {
            $query->whereNull('component')
                ->orWhereNotIn('component', $excludedComponents);
        });

        if ($formId) {
            $logsQuery->where('parent_source_id', $formId);
        }

        $results = $logsQuery->selectRaw($dateFormat . ' as log_date')
            ->selectRaw('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('log_date', 'status')
            ->orderBy('log_date')
            ->get();

        // Get total counts by status (also excluding the specific components)
        $totalsQuery = Log::whereBetween('created_at', [$startDate, $endDate]);

        $totalsQuery->where(function ($query) use ($excludedComponents) {
            $query->whereNull('component')
                ->orWhereNotIn('component', $excludedComponents);
        });

        $totalsResults = $totalsQuery->selectRaw('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $totals = [
            'success' => 0,
            'pending' => 0,
            'failed'  => 0
        ];

        foreach ($totalsResults as $total) {
            $status = strtolower($total->status);
            if (isset($totals[$status])) {
                $totals[$status] = (int)$total->count;
            }
        }

        // Get date labels and prepare data
        $dateLabels = self::getDateLabels($startDateTime, $endDateTime, $groupingMode);
        $dates = $dateLabels['dates'];
        $formattedLabels = $dateLabels['labels'];

        // Initialize data structure - always with all dates, even if no data exists
        $seriesData = [
            'success' => array_fill_keys($dates, 0),
            'pending' => array_fill_keys($dates, 0),
            'failed'  => array_fill_keys($dates, 0)
        ];

        // Fill in data from results when available
        foreach ($results as $row) {
            $date = $row->log_date;
            $status = strtolower($row->status);
            $count = (int)$row->count;

            // Map status to our categories
            if ($status === 'success' || $status === 'pending' || $status === 'failed') {
                if (isset($seriesData[$status][$date])) {
                    $seriesData[$status][$date] = $count;
                }
            }
        }

        return [
            'logs_data'  => [
                'categories' => $formattedLabels,
                'series'     => $seriesData
            ],
            'totals'     => $totals,
            'start_date' => $startDate,
            'end_date'   => $endDate
        ];
    }

    /**
     * Get top performing forms by entries, views, or payments
     */
    public static function getTopPerformingForms($startDate, $endDate, $metric = 'entries')
    {
        list($startDate, $endDate) = self::processDateRange($startDate, $endDate);
        global $wpdb;
        $prefix = $wpdb->prefix;
        $formResults = [];
        $disableMessage = '';

        switch ($metric) {
            case 'entries':
                // Use Form model with Submission relationship
                $results = Form::select(['id', 'title'])
                    ->withCount([
                        'submissions' => function ($q) use ($startDate, $endDate) {
                            $q->whereBetween('created_at', [$startDate, $endDate]);
                            $q->whereNotIn('status', ['trashed', 'spam']);
                        }
                    ])
                    ->orderBy('submissions_count', 'DESC')
                    ->limit(5)
                    ->get();

                // Map the results to standard format
                foreach ($results as $form) {
                    $formResults[] = (object)[
                        'id' => $form->id,
                        'title' => $form->title,
                        'value' => $form->submissions_count
                    ];
                }
                break;

            case 'payments':
                // Check if payment module is enabled
                $paymentSettings = get_option('__fluentform_payment_module_settings');
                if ($paymentSettings && Arr::get($paymentSettings, 'status')) {
                    $results = wpFluent()->table('fluentform_forms')
                        ->select([
                            'fluentform_forms.id',
                            'fluentform_forms.title',
                            wpFluent()->raw("COALESCE(SUM({$prefix}fluentform_transactions.payment_total), 0) as raw_value")
                        ])
                        ->leftJoin('fluentform_transactions', 'fluentform_forms.id', '=',
                            'fluentform_transactions.form_id')
                        ->whereBetween('fluentform_transactions.created_at', [$startDate, $endDate])
                        ->where('fluentform_transactions.status', 'paid')
                        ->groupBy('fluentform_forms.id')
                        ->orderBy('raw_value', 'DESC')
                        ->limit(5)
                        ->get();

                    // Convert cents to dollars in PHP for better precision
                    foreach ($results as $form) {
                        $form->value = round((float)$form->raw_value / 100, 2);
                    }

                    $formResults = $results;
                } else {
                    $disableMessage = __('Payment module is disabled. Please enable it to view top performing form by payments.', 'fluentform');
                }
                break;

            case 'views':
                // Count unique views by IP from analytics table if analytics enabled
                if (!apply_filters('fluentform/disabled_analytics', true)) {
                    $results = wpFluent()->table('fluentform_forms')
                        ->select([
                            'fluentform_forms.id',
                            'fluentform_forms.title',
                            wpFluent()->raw("COUNT(DISTINCT {$prefix}fluentform_form_analytics.ip) as value")
                        ])
                        ->leftJoin('fluentform_form_analytics', 'fluentform_forms.id', '=',
                            'fluentform_form_analytics.form_id')
                        ->whereBetween('fluentform_form_analytics.created_at', [$startDate, $endDate])
                        ->groupBy('fluentform_forms.id')
                        ->orderBy('value', 'DESC')
                        ->limit(5)
                        ->get();

                    $formResults = $results;
                } else {
                    $disableMessage = __('Analytics is disabled. Please enable it to view top performing form by views.', 'fluentform');
                }
                break;
        }

        // Common formatting for all results
        $topForms = [];
        foreach ($formResults as $form) {
            if ((float)$form->value > 0) {
                $topForms[] = [
                    'id'    => $form->id,
                    'title' => $form->title ?: 'Untitled Form',
                    'value' => (float)$form->value
                ];
            }
        }
        return [
            'disable_message' => $disableMessage,
            'data' => array_reverse($topForms)
        ];
    }

    /**
     * Get form views date chunks
     */
    private static function getFormViews($startDate, $endDate, $groupingMode, $formId)
    {
        if (apply_filters('fluentform/disabled_analytics', true)) {
            return [];
        }

        // 1. Get UNIQUE VIEWS by IP address
        $viewsQuery = FormAnalytics::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('ip');

        if ($formId) {
            $viewsQuery->where('form_id', $formId);
        }

        // Group by date and IP to count unique visitors
        if ($groupingMode === 'day') {
            $viewsQuery->selectRaw('DATE(created_at) as date_group, COUNT(DISTINCT ip) as unique_count');
        } elseif ($groupingMode === '3days') {
            // Get min date for reference
            $minDateRecord = FormAnalytics::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('MIN(DATE(created_at)) as min_date')
                ->first();

            if ($minDateRecord && $minDateRecord->min_date) {
                $minDate = $minDateRecord->min_date;
                $viewsQuery->selectRaw("FLOOR(DATEDIFF(DATE(created_at), ?) / 3) as group_num", [$minDate])
                    ->selectRaw('MIN(DATE(created_at)) as date_group')
                    ->selectRaw('COUNT(DISTINCT ip) as unique_count')
                    ->groupBy('group_num');
            } else {
                $viewsQuery->selectRaw('DATE(created_at) as date_group, COUNT(DISTINCT ip) as unique_count')
                    ->groupBy('date_group');
            }
        } elseif ($groupingMode === 'week') {
            $viewsQuery->selectRaw("DATE(DATE_ADD(created_at, INTERVAL(-WEEKDAY(created_at)) DAY)) as date_group, COUNT(DISTINCT ip) as unique_count");
        } else { // month
            $viewsQuery->selectRaw("DATE_FORMAT(created_at, '%Y-%m-01') as date_group, COUNT(DISTINCT ip) as unique_count");
        }

        if ($groupingMode !== '3days' || !(isset($minDateRecord) && $minDateRecord->min_date)) {
            $viewsQuery->groupBy('date_group');
        }

        $results = $viewsQuery->orderBy('date_group')->get();
        $views = [];
        foreach ($results as $result) {
            $views[$result->date_group] = $result->unique_count;
        }
        return $views;
    }

    /**
     * Process date range
     */
    public static function processDateRange($startDate, $endDate)
    {
        // Validate date formats
        if (!strtotime($startDate) || !strtotime($endDate)) {
            return [];
        }

        // Sanity check - ensure start date is before end date
        $startDateTime = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);

        // If start date is after end date, swap them
        if ($startDateTime > $endDateTime) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        return [$startDate, $endDate];
    }

    /**
     * Determine grouping mode based on date range
     */
    private static function getGroupingMode($daysInterval)
    {
        if ($daysInterval <= 7) {
            return 'day'; // 1-7 days: group by day
        } elseif ($daysInterval <= 31) {
            return '3days'; // 8-31 days: group by 3 days
        } elseif ($daysInterval <= 92) {
            return 'week'; // Group by week for 1-3 months
        } else {
            return 'month'; // 3+ months: group by month
        }
    }

    /**
     * Get aggregated data based on grouping mode
     */
    private static function getAggregatedData($startDate, $endDate, $groupingMode, $view, $formId)
    {
        $baseQuery = Submission::whereBetween('created_at', [$startDate, $endDate]);

        // Filter by form ID if provided
        if ($formId) {
            $baseQuery->where('form_id', $formId);
        }

        if ($view === 'revenue') {
            // Clone the base query for each payment status
            $paidQuery = clone $baseQuery;
            $pendingQuery = clone $baseQuery;
            $refundedQuery = clone $baseQuery;

            // Get paid payments
            $paidQuery->whereNotNull('payment_total')
                ->where(function ($query) {
                    $query->where('payment_status', 'paid');
                })
                ->selectRaw('ROUND(SUM(payment_total) / 100, 2) as count');

            // Get pending payments
            $pendingQuery->whereNotNull('payment_total')
                ->where('payment_status', 'pending')
                ->selectRaw('ROUND(SUM(payment_total) / 100, 2) as count');

            // Get refunded payments
            $refundedQuery->whereNotNull('payment_total')
                ->where('payment_status', 'refunded')
                ->selectRaw('ROUND(SUM(payment_total) / 100, 2) as count');

            // Apply grouping based on mode to all three queries
            if ($groupingMode === 'day') {
                $paidQuery->selectRaw('DATE(created_at) as date_group')->groupBy('date_group');
                $pendingQuery->selectRaw('DATE(created_at) as date_group')->groupBy('date_group');
                $refundedQuery->selectRaw('DATE(created_at) as date_group')->groupBy('date_group');
            } elseif ($groupingMode === '3days') {
                // Get minimum date for reference
                $minDateRecord = Submission::whereBetween('created_at', [$startDate, $endDate])
                    ->selectRaw('MIN(DATE(created_at)) as min_date')
                    ->first();

                if ($minDateRecord && $minDateRecord->min_date) {
                    $minDate = $minDateRecord->min_date;

                    $paidQuery->selectRaw("MIN(DATE(created_at)) as date_group")
                        ->selectRaw("FLOOR(DATEDIFF(DATE(created_at), ?) / 3) as group_num", [$minDate])
                        ->groupBy('group_num');

                    $pendingQuery->selectRaw("MIN(DATE(created_at)) as date_group")
                        ->selectRaw("FLOOR(DATEDIFF(DATE(created_at), ?) / 3) as group_num", [$minDate])
                        ->groupBy('group_num');

                    $refundedQuery->selectRaw("MIN(DATE(created_at)) as date_group")
                        ->selectRaw("FLOOR(DATEDIFF(DATE(created_at), ?) / 3) as group_num", [$minDate])
                        ->groupBy('group_num');
                } else {
                    $paidQuery->selectRaw('DATE(created_at) as date_group')->groupBy('date_group');
                    $pendingQuery->selectRaw('DATE(created_at) as date_group')->groupBy('date_group');
                    $refundedQuery->selectRaw('DATE(created_at) as date_group')->groupBy('date_group');
                }
            } elseif ($groupingMode === 'week') {
                $paidQuery->selectRaw("DATE(DATE_ADD(created_at, INTERVAL(-WEEKDAY(created_at)) DAY)) as date_group")->groupBy('date_group');
                $pendingQuery->selectRaw("DATE(DATE_ADD(created_at, INTERVAL(-WEEKDAY(created_at)) DAY)) as date_group")->groupBy('date_group');
                $refundedQuery->selectRaw("DATE(DATE_ADD(created_at, INTERVAL(-WEEKDAY(created_at)) DAY)) as date_group")->groupBy('date_group');
            } else {
                $paidQuery->selectRaw("DATE_FORMAT(created_at, '%Y-%m-01') as date_group")->groupBy('date_group');
                $pendingQuery->selectRaw("DATE_FORMAT(created_at, '%Y-%m-01') as date_group")->groupBy('date_group');
                $refundedQuery->selectRaw("DATE_FORMAT(created_at, '%Y-%m-01') as date_group")->groupBy('date_group');
            }

            // Execute the queries
            $paidResults = $paidQuery->orderBy('date_group')->get();
            $pendingResults = $pendingQuery->orderBy('date_group')->get();
            $refundedResults = $refundedQuery->orderBy('date_group')->get();


            // Format the data
            $paidData = $revenuePayments = [];
            foreach ($paidResults as $result) {
                $paidData[$result->date_group] = $result->count;
                $revenuePayments[$result->date_group] = $result->count;
            }

            $pendingData = [];
            foreach ($pendingResults as $result) {
                $pendingData[$result->date_group] = $result->count;
            }

            $refundedData = [];
            foreach ($refundedResults as $result) {
                $refundedData[$result->date_group] = $result->count;
                if (isset($revenuePayments[$result->date_group])) {
                    $revenuePayments[$result->date_group] -= $result->count;
                }
            }

            // Return all three datasets
            return [
                'paid'     => $paidData,
                'pending'  => $pendingData,
                'refunded' => $refundedData,
                'payments' => $revenuePayments
            ];
        } else {
            $query = $baseQuery->selectRaw('COUNT(*) as count')->selectRaw('status');
            $query->groupBy('status');

            // Apply grouping based on mode
            if ($groupingMode === 'day') {
                $query->selectRaw('DATE(created_at) as date_group')
                    ->groupBy('date_group');
            } elseif ($groupingMode === '3days') {
                $minDateRecord = Submission::whereBetween('created_at', [$startDate, $endDate])
                    ->selectRaw('MIN(DATE(created_at)) as min_date')
                    ->first();

                if ($minDateRecord && $minDateRecord->min_date) {
                    $query->selectRaw("MIN(DATE(created_at)) as date_group")
                        ->selectRaw("FLOOR(DATEDIFF(DATE(created_at), ?) / 3) as group_num", [$minDateRecord->min_date])
                        ->groupBy('group_num');
                } else {
                    $query->selectRaw('DATE(created_at) as date_group')
                        ->groupBy('date_group');
                }
            } elseif ($groupingMode === 'week') {
                $query->selectRaw("DATE(DATE_ADD(created_at, INTERVAL(-WEEKDAY(created_at)) DAY)) as date_group")
                    ->groupBy('date_group');
            } else {
                $query->selectRaw("DATE_FORMAT(created_at, '%Y-%m-01') as date_group")
                    ->groupBy('date_group');
            }

            $results = $query->orderBy('date_group')->get();
            $total = $read = $unread = $spam = $trashed = [];
            foreach ($results as $result) {
                if ($result->status === 'read') {
                    $read[$result->date_group] = $result->count;
                }
                if ($result->status === 'unread') {
                    $unread[$result->date_group] = $result->count;
                }
                if ($result->status === 'spam') {
                    $spam[$result->date_group] = $result->count;
                }
                if ($result->status === 'trashed') {
                    $trashed[$result->date_group] = $result->count;
                }
                $total[$result->date_group] = isset($total[$result->date_group]) ? $total[$result->date_group] + $result->count : $result->count;
            }
            // Return all four datasets
            return [
                'submissions' => $total,
                'read'        => $read,
                'unread'      => $unread,
                'spam'        => $spam,
                'trashed'     => $trashed
            ];
        }
    }

    /**
     * Generate date labels based on grouping mode
     */
    private static function getDateLabels(\DateTime $startDate, \DateTime $endDate, $groupingMode)
    {
        $dates = [];
        $labels = [];
        $current = clone $startDate;

        if ($groupingMode === 'day') {
            // Generate daily labels
            while ($current <= $endDate) {
                $dateKey = $current->format('Y-m-d');
                $dates[] = $dateKey;
                $labels[] = $current->format('M d');
                $current->modify('+1 day');
            }
        } elseif ($groupingMode === '3days') {
            // Generate labels for every 3 days
            $dayIndex = 0;
            $groupStartDate = clone $current;

            while ($current <= $endDate) {
                if ($dayIndex % 3 === 0 && $dayIndex > 0) {
                    $previousDate = clone $current;
                    $previousDate->modify('-1 day');

                    $dateKey = $groupStartDate->format('Y-m-d');
                    $dates[] = $dateKey;
                    $labels[] = $groupStartDate->format('M d');

                    $groupStartDate = clone $current;
                }

                $current->modify('+1 day');
                $dayIndex++;
            }

            // Add the last group if needed
            if ($groupStartDate <= $endDate) {
                $dateKey = $groupStartDate->format('Y-m-d');
                $dates[] = $dateKey;
                $labels[] = $groupStartDate->format('M d');
            }
        } elseif ($groupingMode === 'week') {
            // Generate weekly labels
            while ($current <= $endDate) {
                // Use simple approach to get Monday (start of week)
                $dayOfWeek = (int)$current->format('N'); // 1 (Monday) through 7 (Sunday)
                $daysToSubtract = $dayOfWeek - 1;

                $weekStart = clone $current;
                if ($daysToSubtract > 0) {
                    $weekStart->modify("-{$daysToSubtract} days");
                }

                // Calculate end of week (Sunday)
                $weekEnd = clone $weekStart;
                $weekEnd->modify('+6 days');

                // If weekend exceeds the range end, cap it
                if ($weekEnd > $endDate) {
                    $weekEnd = clone $endDate;
                }

                $dateKey = $weekStart->format('Y-m-d');
                $dates[] = $dateKey;
                $labels[] = $weekStart->format('M d');

                // Move to next week
                $current->modify('+7 days');
            }
        } else {
            // Generate monthly labels
            while ($current <= $endDate) {
                $dateKey = $current->format('Y-m-01');
                $dates[] = $dateKey;
                $labels[] = $current->format('M Y');

                // Manually move to first day of next month
                $year = (int)$current->format('Y');
                $month = (int)$current->format('m');

                // Move to next month
                $month++;
                if ($month > 12) {
                    $month = 1;
                    $year++;
                }

                // Set to first day of next month
                $current = new \DateTime("$year-$month-01");
            }
        }

        return ['dates' => $dates, 'labels' => $labels];
    }
    
    public static function getPaymentsByType($startDate, $endDate, $type, $formId = 0)
    {
        $paymentSettings = get_option('__fluentform_payment_module_settings');
        if (!$paymentSettings || !Arr::isTrue($paymentSettings, 'status')) {
            return []; // Return empty if payment module is disabled
        }
        list($startDate, $endDate) = self::processDateRange($startDate, $endDate);
        
        // Base query for transactions
        $query = \FluentForm\App\Models\Transaction::whereBetween('created_at', [$startDate, $endDate]);
        
        // Filter by transaction type if specified
        if ($type === 'subscription') {
            $query->whereIn('transaction_type', ['subscription', 'subscription_signup_fee']);
        } elseif ($type === 'onetime') {
            $query->where('transaction_type', 'onetime');
        }
        
        if ($formId) {
            $query->where('form_id', $formId);
        }
        
        // Get payments grouped by status
        $payments = $query->select('status')
            ->selectRaw('SUM(payment_total) as total_amount')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->get();
        
        // Get the total payment amount
        $totalAmount = 0;
        foreach ($payments as $payment) {
            $totalAmount += $payment->total_amount;
        }
        
        $formattedData = [];
        foreach ($payments as $payment) {
            $status = strtolower($payment->status);
            $amount = $payment->total_amount / 100; // Convert from cents to dollars
            $percentage = $totalAmount > 0 ? round(($payment->total_amount / $totalAmount) * 100, 2) : 0;
            
            $formattedData[$status] = [
                'amount' => $amount,
                'percentage' => $percentage,
                'count' => $payment->count
            ];
        }
        
        // Calculate weekly average paid amount
        $daysInRange = self::getDateDifference($startDate, $endDate);
        $weeksInRange = max(1, round($daysInRange / 7, 1));
        
        $paidAmount = 0;
        foreach ($formattedData as $status => $data) {
            if ($status === 'paid') {
                $paidAmount = $data['amount'];
                break;
            }
        }
        
        $weeklyAverage = $paidAmount / $weeksInRange;
        
        return [
            'currency_symbol' => Arr::get(PaymentHelper::getCurrencyConfig($formId), 'currency_sign', '$'),
            'payment_statuses' => $formattedData,
            'total_amount'     => $totalAmount / 100, // Convert from cents to dollars
            'weekly_average'   => round($weeklyAverage, 2)
        ];
    }
    
    /**
     * Format payment method name for display
     */
    protected static function formatPaymentMethodName($paymentMethod)
    {
        $methodNames = [
            'stripe'   => 'Stripe',
            'paypal'   => 'PayPal',
            'razorpay' => 'Razorpay',
            'paystack' => 'Paystack',
            'mollie'   => 'Mollie',
            'square'   => 'Square',
            'paddle'   => 'Paddle',
            'test'     => 'Offline/Test',
            'offline'  => 'Offline'
        ];

        return $methodNames[$paymentMethod] ?? ucfirst($paymentMethod);
    }

    /**
     * Format data for the chart
     */
    private static function formatDataForChart($dateLabels, $data, $formId)
    {
        $dates = $dateLabels['dates'];
        $labels = $dateLabels['labels'];

        if (is_array($data) && isset($data['paid'])) {
            $paidValues = self::fillMissingData($dates, $data['paid']);
            $pendingValues = self::fillMissingData($dates, $data['pending']);
            $refundedValues = self::fillMissingData($dates, $data['refunded']);
            $paymentsValues = self::fillMissingData($dates, $data['payments']);
            $currencyConfig = PaymentHelper::getCurrencyConfig($formId);

            return [
                'dates'         => $labels,
                'currency_sign' => Arr::get($currencyConfig, 'currency_sign', '$'),
                'currency'      => Arr::get($currencyConfig, 'currency', 'USD'),
                'values'        => [
                    'paid'     => array_values($paidValues),
                    'pending'  => array_values($pendingValues),
                    'refunded' => array_values($refundedValues),
                    'payments' => array_values($paymentsValues)
                ]
            ];
        } else {
            return [
                'dates'  => $labels,
                'values' => [
                    'submissions' => array_values(self::fillMissingData($dates, $data['submissions'])),
                    'read'        => array_values(self::fillMissingData($dates, $data['read'])),
                    'unread'      => array_values(self::fillMissingData($dates, $data['unread'])),
                    'spam'        => array_values(self::fillMissingData($dates, $data['spam'])),
                    'trashed'     => array_values(self::fillMissingData($dates, $data['trashed']))
                ]
            ];
        }
    }

    /**
     * Fill in missing data based on date intervals
     *
     * @param array $allDates Array of interval start dates
     * @param array $data     Associative array of date => value pairs
     *
     * @return array Result with interval start dates mapped to summed values
     */
    private static function fillMissingData($allDates, $data)
    {
        $result = [];

        // Pre-convert dates to timestamps for faster comparison
        $dataTimestamps = [];
        foreach ($data as $date => $value) {
            $dataTimestamps[strtotime($date)] = $value;
        }

        $allDatesCount = count($allDates);
        for ($i = 0; $i < $allDatesCount; $i++) {
            $startTimestamp = strtotime($allDates[$i]);

            // Calculate end timestamp of interval (exclusive)
            $endTimestamp = isset($allDates[$i + 1])
                ? strtotime($allDates[$i + 1])
                : $startTimestamp + (3 * 24 * 60 * 60); // 3 days in seconds

            $sum = 0;
            $hasData = false;

            // Check each timestamp in the data array
            foreach ($dataTimestamps as $timestamp => $value) {
                if ($timestamp >= $startTimestamp && $timestamp < $endTimestamp) {
                    $sum += $value;
                    $hasData = true;
                }
            }

            $result[$allDates[$i]] = $hasData ? $sum : 0;
        }

        return $result;
    }

    private static function getPaymentStats($startDate, $endDate, $previousStartDate, $previousEndDate, $formId)
    {
        // Get total payments (paid status) for current period
        $currentPayments = wpFluent()
            ->table('fluentform_transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->when($formId, function ($q) use ($formId) {
                return $q->where('form_id', $formId);
            })
            ->sum('payment_total');

        // Get total payments for previous period
        $previousPayments = wpFluent()
            ->table('fluentform_transactions')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->where('status', 'paid')
            ->when($formId, function ($q) use ($formId) {
                return $q->where('form_id', $formId);
            })
            ->sum('payment_total');

        // Get pending payments for current period
        $currentPending = wpFluent()
            ->table('fluentform_transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'pending')
            ->when($formId, function ($q) use ($formId) {
                return $q->where('form_id', $formId);
            })
            ->sum('payment_total');

        // Get pending payments for previous period
        $previousPending = wpFluent()
            ->table('fluentform_transactions')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->where('status', 'pending')
            ->when($formId, function ($q) use ($formId) {
                return $q->where('form_id', $formId);
            })
            ->sum('payment_total');

        // Get total refunds for current period
        $currentRefunds = wpFluent()
            ->table('fluentform_transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'refunded')
            ->when($formId, function ($q) use ($formId) {
                return $q->where('form_id', $formId);
            })
            ->sum('payment_total');

        // Get total refunds for previous period
        $previousRefunds = wpFluent()
            ->table('fluentform_transactions')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->where('status', 'refunded')
            ->when($formId, function ($q) use ($formId) {
                return $q->where('form_id', $formId);
            })
            ->sum('payment_total');

        // Convert from cents to dollars
        $currentPayments = $currentPayments ? $currentPayments / 100 : 0;
        $previousPayments = $previousPayments ? $previousPayments / 100 : 0;
        $currentPending = $currentPending ? $currentPending / 100 : 0;
        $previousPending = $previousPending ? $previousPending / 100 : 0;
        $currentRefunds = $currentRefunds ? $currentRefunds / 100 : 0;
        $previousRefunds = $previousRefunds ? $previousRefunds / 100 : 0;

        // Calculate payment growth percentage
        $paymentGrowthPercentage = 0;
        if ($previousPayments > 0) {
            $paymentGrowthPercentage = round((($currentPayments - $previousPayments) / $previousPayments) * 100, 1);
        } elseif ($currentPayments > 0) {
            $paymentGrowthPercentage = 100;
        }

        $paymentGrowthText = $paymentGrowthPercentage > 0 ? '+' . $paymentGrowthPercentage . '%' : $paymentGrowthPercentage . '%';
        $paymentGrowthType = $paymentGrowthPercentage > 0 ? 'up' : ($paymentGrowthPercentage < 0 ? 'down' : 'neutral');

        // Calculate refund growth percentage
        $refundGrowthPercentage = 0;
        if ($previousRefunds > 0) {
            $refundGrowthPercentage = round((($currentRefunds - $previousRefunds) / $previousRefunds) * 100, 1);
        } elseif ($currentRefunds > 0) {
            $refundGrowthPercentage = 100;
        }

        $refundGrowthText = $refundGrowthPercentage > 0 ? '+' . $refundGrowthPercentage . '%' : $refundGrowthPercentage . '%';
        $refundGrowthType = $refundGrowthPercentage > 0 ? 'down' : ($refundGrowthPercentage < 0 ? 'up' : 'neutral'); // Refunds going up is bad

        // Calculate pending growth percentage
        $pendingGrowthPercentage = 0;
        if ($previousPending > 0) {
            $pendingGrowthPercentage = round((($currentPending - $previousPending) / $previousPending) * 100, 1);
        } elseif ($currentPending > 0) {
            $pendingGrowthPercentage = 100;
        }

        $pendingGrowthText = $pendingGrowthPercentage > 0 ? '+' . $pendingGrowthPercentage . '%' : $pendingGrowthPercentage . '%';
        $pendingGrowthType = $pendingGrowthPercentage > 0 ? 'up' : ($pendingGrowthPercentage < 0 ? 'down' : 'neutral');

        // Calculate revenue percentage
        $totalRevenue = $currentPayments - $currentRefunds;
        $previousRevenue = $previousPayments - $previousRefunds;
        $revenuePercentage = 0;
        if ($previousRevenue > 0) {
            $revenuePercentage = round((($totalRevenue - $previousRevenue) / $previousRevenue) * 100, 1);
        } elseif ($totalRevenue > 0) {
            $revenuePercentage = 100;
        }
        $revenueText = $revenuePercentage > 0 ? '+' . $revenuePercentage . '%' : $revenuePercentage . '%';
        $revenueType = $revenuePercentage > 0 ? 'up' : ($revenuePercentage < 0 ? 'down' : 'neutral');

        // Get default currency from payment settings
        $paymentSettings = PaymentHelper::getPaymentSettings();
        $currency = Arr::get($paymentSettings, 'currency', 'USD');
        $currencySymbol = PaymentHelper::getCurrencySymbol($currency);

        return [
            'total_payments'   => [
                'value'           => number_format($currentPayments, 2),
                'raw_value'       => $currentPayments,
                'currency'        => $currency,
                'currency_symbol' => $currencySymbol,
                'change'          => $paymentGrowthText,
                'change_type'     => $paymentGrowthType
            ],
            'pending_payments' => [
                'value'           => number_format($currentPending, 2),
                'raw_value'       => $currentPending,
                'currency'        => $currency,
                'currency_symbol' => $currencySymbol,
                'change'          => $pendingGrowthText,
                'change_type'     => $pendingGrowthType
            ],
            'total_refunds'    => [
                'value'           => number_format($currentRefunds, 2),
                'raw_value'       => $currentRefunds,
                'currency'        => $currency,
                'currency_symbol' => $currencySymbol,
                'change'          => $refundGrowthText,
                'change_type'     => $refundGrowthType
            ],
            'total_revenue'    => [
                'value'           => number_format($totalRevenue, 2),
                'raw_value'       => $totalRevenue,
                'change'          => $revenueText,
                'change_type'     => $revenueType,
                'currency'        => $currency,
                'currency_symbol' => $currencySymbol
            ]
        ];
    }

    
    protected static function getDateDifference($startDate, $endDate)
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $interval = $start->diff($end);
        return $interval->days + 1;
    }
}
