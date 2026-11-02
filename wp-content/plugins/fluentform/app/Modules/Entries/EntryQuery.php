<?php

namespace FluentForm\App\Modules\Entries;

use FluentForm\App\Helpers\Helper;

/**
 * @deprecated Use SubmissionService or direct wpFluent() queries instead.
 * @todo Remove in next major version.
 */
class EntryQuery
{
    protected $request;
    protected $formModel;
    protected $responseModel;

    protected $formId = false;
    protected $per_page = 10;
    protected $page_number = 1;
    protected $status = false;
    protected $is_favourite = null;
    protected $sort_by = 'ASC';
    protected $search = false;
    protected $wheres = [];
    protected $startDate;
    protected $endDate;

    public function __construct()
    {
        _deprecated_function(__CLASS__, '6.2.0', 'FluentForm\App\Models\Submission model or wpFluent() queries');
        $this->request = wpFluentForm('request');
        $this->formModel = wpFluent()->table('fluentform_forms');
        $this->responseModel = wpFluent()->table('fluentform_submissions');
    }

    public function getResponses()
    {
        $query = $this->responseModel
            ->where('form_id', $this->formId)
            ->orderBy('id', Helper::sanitizeOrderValue($this->sort_by));

        if ($this->per_page > 0) {
            $query = $query->limit($this->per_page);
        }

        if ($this->page_number > 0) {
            $query = $query->offset(($this->page_number - 1) * $this->per_page);
        }

        if ($this->is_favourite) {
            $query->where('is_favourite', $this->is_favourite);
            $query->where('status', '!=', 'trashed');
        } else {
            if (!$this->status) {
                $query->where('status', '!=', 'trashed');
            } else {
                $query->where('status', $this->status);
            }
        }

        if ($this->startDate && $this->endDate) {
            $endDate = $this->endDate . ' 23:59:59';
            $query->where('created_at', '>=', $this->startDate);
            $query->where('created_at', '<=', $endDate);
        }

        if ($this->search) {
            $searchString = $this->search;
            $query->where(function ($q) use ($searchString) {
                $q->where('id', 'LIKE', "%{$searchString}%")
                    ->orWhere('response', 'LIKE', "%{$searchString}%")
                    ->orWhere('status', 'LIKE', "%{$searchString}%")
                    ->orWhere('created_at', 'LIKE', "%{$searchString}%");
            });
        }

        if ($this->wheres) {
            foreach ($this->wheres as $where) {
                if (is_array($where) && count($where) > 1) {
                    if (count($where) > 2) {
                        $column = $where[0];
                        $operator = $where[1];
                        $value = $where[2];
                    } else {
                        $column = $where[0];
                        $operator = '=';
                        $value = $where[1];
                    }
                    if (is_array($value)) {
                        $query->whereIn($column, $value);
                    } else {
                        $query->where($column, $operator, $value);
                    }
                }
            }
        }

        $total = $query->count();
        $responses = $query->get();

        $responses = apply_filters('fluentform/get_raw_responses', $responses, $this->formId);

        return [
            'data'     => $responses,
            'paginate' => [
                'total'        => $total,
                'per_page'     => $this->per_page,
                'current_page' => $this->page_number,
                'last_page'    => ceil($total / $this->per_page),
            ],
        ];
    }
}
