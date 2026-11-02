<?php

namespace FluentForm\App\Services\Logger;

use FluentForm\App\Models\Log;
use FluentForm\App\Models\Form;
use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\Scheduler;
use FluentForm\App\Services\Manager\FormManagerService;
use FluentForm\Framework\Support\Arr;
use FluentForm\Framework\Support\Collection;
use FluentForm\Framework\Validator\ValidationException;

class Logger
{
    public function get($attributes = [])
    {
        $statuses = Arr::get($attributes, 'status');
        $formIds = $this->normalizeFormScope(Arr::get($attributes, 'form_id'));
        $formIds = $this->resolveVisibleFormScope($formIds);
        $components = Arr::get($attributes, 'component');
        $sortBy = \FluentForm\App\Helpers\Helper::sanitizeOrderValue(Arr::get($attributes, 'sort_by', 'DESC'));
        $type = Arr::get($attributes, 'type', 'log');
        $dateRange = Arr::get($attributes, 'date_range', []);
        $startDate = Arr::get($dateRange, 0);
        $endDate = Arr::get($dateRange, 1);
        [$table, $model, $columns, $join, $componentColumn, $dateColumn] = $this->getBases($type);

        $logsQuery = $model->select($columns)
            ->leftJoin('fluentform_forms', 'fluentform_forms.id', '=', $join)
            ->orderBy($table . '.id', $sortBy)
            ->when(false !== $formIds && [] !== $formIds, function ($q) use ($formIds) {
                return $q->whereIn('fluentform_forms.id', array_map('intval', $formIds));
            })
            ->when([] === $formIds, function ($q) {
                return $q->whereIn('fluentform_forms.id', [0]);
            })
            ->when($statuses, function ($q) use ($statuses, $table) {
                return $q->whereIn($table . '.status', array_map('sanitize_text_field', $statuses));
            })
            ->when($components, function ($q) use ($components, $componentColumn) {
                return $q->whereIn($componentColumn, array_map('sanitize_text_field', $components));
            })
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate, $dateColumn) {
                // Concatenate time if not time included on start/end date string
                if (date('Y-m-d H:i:s', strtotime($startDate)) != $startDate) { // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- comparing a local-time string to its own roundtrip; UTC would be wrong here
                    $startDate .= ' 00:00:01';
                }
                if (date('Y-m-d H:i:s', strtotime($endDate)) != $endDate) { // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- comparing a local-time string to its own roundtrip; UTC would be wrong here
                    $endDate .= ' 23:59:59';
                }
                return $q->where($dateColumn, '>=', $startDate)
                    ->where($dateColumn, '<=', $endDate);
            });

        $logs = $logsQuery->paginate();

        $logItems = $logs->items();

        foreach ($logItems as $log) {
            $hasUrl = ('api' === $type) || (
                'submission_item' == $log->source_type && $log->submission_id
            );

            if ($hasUrl) {
                $log->submission_url = admin_url(
                    'admin.php?page=fluent_forms&route=entries&form_id=' . $log->form_id . '#/entries/' . $log->submission_id
                );
            }

            $log->component = Helper::getLogInitiator($log->component, $type);
            $log->integration_enabled = false;

            $notificationKeys = apply_filters('fluentform/global_notification_active_types', [], $log->form_id);

            unset($notificationKeys['user_registration_feeds']);
            unset($notificationKeys['notifications']);

            $notificationKeys = array_flip($notificationKeys);

            $actionName = $log->getOriginal('component');
            if ($actionName) {
                $actionName = str_replace(['fluentform_integration_notify_', 'fluentform/integration_notify_'], '', $actionName);

                if (in_array($actionName, $notificationKeys)) {
                    $log->integration_enabled = true;
                }
            }
        }

        $logItems = apply_filters_deprecated(
            'fluentform_all_logs',
            [
                $logItems,
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/get_logs',
            'Use fluentform/get_logs instead of fluentform_all_logs'
        );

        $logs->setCollection(Collection::make($logItems));

        $logs = apply_filters('fluentform/get_logs', $logs);

        foreach ($logs->items() as $log) {
            if ('api' === $type && isset($log->note)) {
                $log->note = static::sanitizeLogHtml($log->note);
            } elseif (isset($log->description)) {
                $log->description = static::sanitizeLogHtml($log->description);
            }
        }

        return $logs;
    }

    protected function normalizeFormScope($formIds)
    {
        if (null === $formIds || false === $formIds || '' === $formIds || [] === $formIds) {
            return false;
        }

        $normalized = array_values(array_filter(array_map('intval', (array) $formIds)));

        return $normalized ?: [];
    }

    protected function resolveVisibleFormScope($requestedFormIds)
    {
        $allowedForms = FormManagerService::getUserAllowedFormsScope();

        if (false === $allowedForms) {
            return $requestedFormIds;
        }

        if (false === $requestedFormIds) {
            return $allowedForms;
        }

        return array_values(array_intersect($requestedFormIds, $allowedForms));
    }

    protected function getBases($type)
    {
        if ('log' === $type) {
            $table = 'fluentform_logs';
            $model = Log::query();
            $columns = [
                'fluentform_logs.*',
                'fluentform_forms.title as form_title',
                'fluentform_logs.source_id as submission_id',
                'fluentform_logs.parent_source_id as form_id',
            ];
            $join = 'fluentform_logs.parent_source_id';
            $componentColumn = 'fluentform_logs.component';
            $dateColumn = 'fluentform_logs.created_at';
        } else {
            $table = 'ff_scheduled_actions';
            $model = Scheduler::query();
            $columns = [
                'ff_scheduled_actions.id',
                'ff_scheduled_actions.action as component',
                'ff_scheduled_actions.form_id',
                'ff_scheduled_actions.origin_id as submission_id',
                'ff_scheduled_actions.status',
                'ff_scheduled_actions.note',
                'ff_scheduled_actions.updated_at',
                'ff_scheduled_actions.feed_id',
                'fluentform_forms.title as form_title',
            ];
            $join = 'ff_scheduled_actions.form_id';
            $componentColumn = 'ff_scheduled_actions.action';
            $dateColumn = 'ff_scheduled_actions.updated_at';
        }

        return [$table, $model, $columns, $join, $componentColumn, $dateColumn];
    }

    public function getFilters($attributes = [])
    {
        $type = Arr::get($attributes, 'type', 'log');
        $allowedForms = FormManagerService::getUserAllowedFormsScope();

        if ('log' === $type) {
            $statusQuery = Log::select('status')->distinct();
            $componentQuery = Log::select('component')->distinct();
            $formIdQuery = Log::select('parent_source_id as form_id')->distinct();
            $scopeColumn = 'parent_source_id';
        } else {
            $statusQuery = Scheduler::select('status')->distinct();
            $componentQuery = Scheduler::select('action as component')->distinct();
            $formIdQuery = Scheduler::select('form_id')->distinct();
            $scopeColumn = 'form_id';
        }

        $statusRows = $this->scopeFilterQuery($statusQuery, $scopeColumn, $allowedForms)->get();
        $componentRows = $this->scopeFilterQuery($componentQuery, $scopeColumn, $allowedForms)->get();
        $formIdRows = $this->scopeFilterQuery($formIdQuery, $scopeColumn, $allowedForms)->get();

        $statuses = $statusRows->pluck('status')->filter()->map(function ($item) {
            return [
                'label' => ucwords($item),
                'value' => $item,
            ];
        })->values();

        $components = $componentRows->pluck('component')->filter()->map(function ($item) use ($type) {
            return [
                'label' => Helper::getLogInitiator($item, $type),
                'value' => $item,
            ];
        })->values();

        $formIds = $formIdRows->pluck('form_id')->filter()->toArray();
        if (false !== ($allowForms = FormManagerService::getUserAllowedFormsScope())) {
            $formIds = array_filter($formIds, function ($value) use ($allowForms) {
                return in_array($value, $allowForms);
            });
        }

        $forms = Form::select('id', 'title')->whereIn('id', $formIds ?: [0])->get();

        return apply_filters('fluentform/get_log_filters', [
            'statuses'   => $statuses,
            'components' => $components,
            'forms'      => $forms,
        ]);
    }

    protected function scopeFilterQuery($query, $formColumn, $allowedForms)
    {
        if (false !== $allowedForms) {
            // phpcs:ignore Universal.Operators.DisallowShortTernary.Found -- `?: [0]` is the delegated-scope regression contract (detect_resource_authorization)
            $query->whereIn($formColumn, $allowedForms ?: [0]);
        }

        return $query;
    }

    public function getSubmissionLogs($submissionId, $attributes = [])
    {
        $logType = Arr::get($attributes, 'log_type', 'logs');

        $sourceType = Arr::get($attributes, 'source_type', 'submission_item');

        if ('logs' === $logType) {
            $logs = Log::where('source_id', $submissionId)
                ->where('source_type', $sourceType)
                ->orderBy('id', 'DESC')
                ->get();

            $logs = apply_filters_deprecated(
                'fluentform_entry_logs',
                [
                    $logs,
                    $submissionId,
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/submission_logs',
                'Use fluentform/submission_logs instead of fluentform_entry_logs.'
            );

            $logs = apply_filters('fluentform/submission_logs', $logs, $submissionId);

            $entryLogs = [];

            foreach ($logs as $log) {
                if (isset($log->component) && $log->component === 'slack') {
                    continue;
                }
                $entryLogs[] = [
                    'id'          => $log->id,
                    'status'      => esc_attr($log->status),
                    'title'       => esc_html($log->component . ' (' . $log->title . ')'),
                    'description' => $log->description,
                    'created_at'  => (string) $log->created_at,
                ];
            }
        } else {
            $columns = [
                'id',
                'action',
                'status',
                'note',
                'created_at',
                'form_id',
                'feed_id',
                'origin_id',
            ];

            $logs = Scheduler::select($columns)
                ->where('origin_id', $submissionId)
                ->orderBy('id', 'DESC')
                ->get();

            $logs = apply_filters_deprecated(
                'fluentform_entry_api_logs',
                [
                    $logs,
                    $submissionId,
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/submission_api_logs',
                'Use fluentform/submission_api_logs instead of fluentform_entry_api_logs.'
            );
            $logs = apply_filters('fluentform/submission_api_logs', $logs, $submissionId);

            $entryLogs = [];

            foreach ($logs as $log) {
                $entryLog = [
                    'id'                  => $log->id,
                    'status'              => esc_attr($log->status),
                    'title'               => 'n/a',
                    'description'         => $log->note,
                    'created_at'          => (string) $log->created_at,
                    'form_id'             => $log->form_id,
                    'feed_id'             => $log->feed_id,
                    'submission_id'       => $log->origin_id,
                    'integration_enabled' => false,
                ];

                $notificationKeys = apply_filters('fluentform/global_notification_active_types', [], $log->form_id);

                unset($notificationKeys['user_registration_feeds']);
                unset($notificationKeys['notifications']);

                $notificationKeys = array_flip($notificationKeys);

                $actionName = Helper::getLogInitiator($log->action);
                if ($actionName) {
                    $actionName = str_replace(['Fluentform_integration_notify_', 'Fluentform/integration_notify_'], '', $actionName);

                    if (in_array($actionName, $notificationKeys)) {
                        $entryLog['integration_enabled'] = true;
                    }
                }

                if ($log->action) {
                    $entryLog['title'] = esc_html(Helper::getLogInitiator($log->action, $logType));
                }

                $entryLogs[] = $entryLog;
            }
        }

        $entryLogs = apply_filters('fluentform/submission_logs', $entryLogs, $submissionId);

        foreach ($entryLogs as &$entryLog) {
            if (isset($entryLog['description'])) {
                $entryLog['description'] = static::sanitizeLogHtml($entryLog['description']);
            }
        }
        unset($entryLog);

        return $entryLogs;
    }

    public static function sanitizeLogHtml($value)
    {
        if (!is_scalar($value)) {
            return '';
        }

        return wp_kses((string) $value, [
            'br'     => [],
            'b'      => [],
            'strong' => [],
            'i'      => [],
            'em'     => [],
            'code'   => [],
            'p'      => [],
            'a'      => [
                'href'  => [],
                'title' => [],
                'rel'   => [],
            ],
        ]);
    }

    public function remove($attributes = [])
    {
        $ids = $this->normalizeLogIds($attributes);

        if (!$ids) {
            throw new ValidationException(
               // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception message, not output
                __('No selections found', 'fluentform')
            );
        }

        $logType = Arr::get($attributes, 'type', Arr::get($attributes, 'log_type', 'logs'));
        $entryId = intval(Arr::get($attributes, 'entry_id'));
        $targetLogs = $this->getLogsForDeletion($ids, $logType);

        if (!$targetLogs->count()) {
            throw new ValidationException(
               // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception message, not output
                __('No selections found', 'fluentform')
            );
        }

        $this->assertDeletePermission($targetLogs, $entryId);
        $this->getDeleteQuery($logType)
            ->whereIn('id', $targetLogs->pluck('id')->all())
            ->delete();

        return [
            'message' => __('Selected log(s) successfully deleted', 'fluentform'),
        ];
    }

    protected function normalizeLogIds($attributes)
    {
        $ids = Arr::get($attributes, 'log_ids', []);

        if (!is_array($ids)) {
            $ids = [];
        }

        $singleLogId = intval(Arr::get($attributes, 'log_id'));
        if ($singleLogId) {
            $ids[] = $singleLogId;
        }

        $ids = array_map('intval', $ids);
        $ids = array_filter($ids);

        return array_values(array_unique($ids));
    }

    protected function getLogsForDeletion($ids, $logType)
    {
        if ('logs' === $logType) {
            return Log::select([
                'id',
                'parent_source_id as form_id',
                'source_id as submission_id',
            ])->whereIn('id', $ids)->get();
        }

        return Scheduler::select([
            'id',
            'form_id',
            'origin_id as submission_id',
        ])->whereIn('id', $ids)->get();
    }

    protected function getDeleteQuery($logType)
    {
        return 'logs' === $logType ? Log::query() : Scheduler::query();
    }

    protected function assertDeletePermission($targetLogs, $entryId = 0)
    {
        if ($entryId) {
            foreach ($targetLogs as $targetLog) {
                if (intval($targetLog->submission_id) !== $entryId) {
                    $this->throwDeletePermissionError();
                }
            }
        }

        $allowedForms = FormManagerService::getUserAllowedFormsScope();
        if (false === $allowedForms) {
            return;
        }

        foreach ($targetLogs as $targetLog) {
            $formId = intval($targetLog->form_id);

            if (!$formId || !in_array($formId, $allowedForms, true)) {
                $this->throwDeletePermissionError();
            }
        }
    }

    protected function throwDeletePermissionError()
    {
        throw new ValidationException(
           // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception message, not output
            __('You do not have permission to delete the selected logs', 'fluentform')
        );
    }
}
