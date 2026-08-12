<?php

namespace FluentForm\App\Api;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\FormMeta;
use FluentForm\App\Models\Submission;
use FluentForm\App\Services\Manager\FormManagerService;
use FluentForm\Framework\Helpers\ArrayHelper;

class Form
{
    public function forms($atts = [], $withFields = false)
    {
        $defaultAtts = [
            'search'      => '',
            'status'      => 'all',
            'sort_column' => 'id',
            'filter_by'   => 'all',
            'date_range'  => [],
            'sort_by'     => 'DESC',
            'per_page'    => 10,
            'page'        => 1,
        ];

        $atts = wp_parse_args($atts, $defaultAtts);

        $perPage = (int) ArrayHelper::get($atts, 'per_page', 10);
        $search = sanitize_text_field(ArrayHelper::get($atts, 'search', ''));
        $status = sanitize_text_field(ArrayHelper::get($atts, 'status', 'all'));
        $filter_by = sanitize_text_field(ArrayHelper::get($atts, 'filter_by', 'all'));
        $dateRange = ArrayHelper::get($atts, 'date_range', []);
        $is_filter_by_conv_or_step_form = $filter_by && ('conv_form' == $filter_by || 'step_form' == $filter_by);

        $shortColumn = sanitize_sql_orderby(ArrayHelper::get($atts, 'sort_column', 'id'));
        $shortColumn = $shortColumn !== false && $shortColumn !== '' ? $shortColumn : 'id';
        $sortBy = Helper::sanitizeOrderValue(ArrayHelper::get($atts, 'sort_by', 'DESC'));

        $query = \FluentForm\App\Models\Form::orderBy($shortColumn, $sortBy)->getQuery();

        if ($status && 'all' != $status) {
            $query->where('status', $status);
        }

        if (false !== ($allowIds = FormManagerService::getUserAllowedFormsScope())) {
            $query->whereIn('id', $allowIds ?: [0]);
        }

        if ($filter_by && !$is_filter_by_conv_or_step_form) {
            switch ($filter_by) {
                case 'published':
                    $query->where('status', 'published');
                    break;
                case 'unpublished':
                    $query->where('status', 'unpublished');
                    break;
                case 'post':
                    $query->where('type', 'post');
                    break;
                case 'is_payment':
                    $query->where('has_payment', 1);
                    break;
                default:
                    break;
            }
        }

        if ($dateRange) {
            $query->where('created_at', '>=', sanitize_text_field($dateRange[0] . ' 00:00:01'));
            $query->where('created_at', '<=', sanitize_text_field($dateRange[1] . ' 23:59:59'));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', '%' . $search . '%');
                $q->orWhere('title', 'LIKE', '%' . $search . '%');
            });
        }

        $currentPage = intval(ArrayHelper::get($atts, 'page', 1));
        $total = $query->count();
        $skip = $perPage * ($currentPage - 1);

        if ($is_filter_by_conv_or_step_form) {
            $data = $query->select('*')->get()->all();
        } else {
            if ($total < $skip) {
                $skip = 0;
            }
            $data = $query->select('*')->limit($perPage)->offset($skip)->get()->all();
        }

        // Fetch all counts in bulk before the loop
        $formIds = array_column($data, 'id');

        // Get all submission counts in one query using Submission model
        $submissionCounts = Submission::select([
                'form_id',
                wpFluent()->raw('COUNT(*) as total_submissions'),
                wpFluent()->raw("SUM(CASE WHEN status = 'unread' THEN 1 ELSE 0 END) as unread_count")
            ])
            ->whereIn('form_id', $formIds)
            ->where('status', '!=', 'trashed')
            ->groupBy('form_id')
            ->get();

        // Map counts by form_id for quick lookup
        $countsMap = [];
        foreach ($submissionCounts as $count) {
            $countsMap[$count->form_id] = $count;
        }

        // Get all view counts in one query using FormMeta model
        $viewCounts = FormMeta::select(['form_id', 'value'])
            ->where('meta_key', '_total_views')
            ->whereIn('form_id', $formIds)
            ->get();

        // Map view counts by form_id for quick lookup
        $viewsMap = [];
        foreach ($viewCounts as $view) {
            $viewsMap[$view->form_id] = intval($view->value);
        }

        $conversationOrStepForms = [];
        foreach ($data as $form) {
            $is_conv_form = Helper::isConversionForm($form->id);

            //  skip form if filter by conversation form but form is not conversational form
            if ('conv_form' == $filter_by && !$is_conv_form) {
                continue;
            }
            //  skip form if filter by step form but form is not step form
            if ('step_form' == $filter_by && !Helper::isMultiStepForm($form)) {
                continue;
            }
            $formInstance = $this->form($form);
            $form->preview_url = Helper::getPreviewUrl($form->id, 'classic');
            $form->edit_url = Helper::getFormAdminPermalink('editor', $form);
            $form->settings_url = Helper::getFormSettingsUrl($form);
            $form->entries_url = Helper::getFormAdminPermalink('entries', $form);
            $form->analytics_url = Helper::getFormAdminPermalink('analytics', $form);

            // Use pre-fetched counts instead of individual queries
            $form->total_views = $viewsMap[$form->id] ?? 0;
            $counts = $countsMap[$form->id] ?? null;
            $form->total_Submissions = $counts ? intval($counts->total_submissions) : 0;
            $form->unread_count = $counts ? intval($counts->unread_count) : 0;

            // Calculate conversion rate (same logic as FormProperties::conversionRate())
            if ($form->total_Submissions && $form->total_views) {
                $form->conversion = ceil(($form->total_Submissions / $form->total_views) * 100);
            } else {
                $form->conversion = 0;
            }

            if ($is_conv_form) {
                $form->conversion_preview = Helper::getPreviewUrl($form->id, 'conversational');
            }

            if (!$withFields) {
                unset($form->form_fields);
            }
            if ($is_filter_by_conv_or_step_form) {
                $conversationOrStepForms[] = $form;
            }
        }
        if ($is_filter_by_conv_or_step_form) {
            $total = count($conversationOrStepForms);
            if ($total < $skip) {
                $skip = 0;
            }
            $conversationOrStepForms = array_slice($conversationOrStepForms, $skip, $perPage);
            $dataCount = count($conversationOrStepForms);
        } else {
            $dataCount = count($data);
        }

        $from = $dataCount > 0 ? ($currentPage - 1) * $perPage + 1 : null;

        $to = $dataCount > 0 ? $from + $dataCount - 1 : null;
        $lastPage = (int) ceil($total / $perPage);
        return [
            'current_page' => $currentPage,
            'per_page'     => $perPage,
            'from'         => $from,
            'to'           => $to,
            'last_page'    => $lastPage,
            'total'        => $total,
            'data'         => $is_filter_by_conv_or_step_form ? $conversationOrStepForms : $data,
        ];
    }

    public function find($formId)
    {
        return \FluentForm\App\Models\Form::where('id', $formId)->first();
    }

    /**
     * Get Form Properties instance
     *
     * @param int|object $form
     *
     * @return \FluentForm\App\Api\FormProperties
     */
    public function form($form)
    {
        if (is_numeric($form)) {
            $form = $this->find($form);
        }

        return (new FormProperties($form));
    }

    public function entryInstance($form)
    {
        if (is_numeric($form)) {
            $form = $this->find($form);
        }

        return (new Entry($form));
    }
}
