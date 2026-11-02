<?php

namespace FluentForm\App\Models;

use Exception;
use FluentForm\App\Modules\Payments\PaymentHelper;
use FluentForm\App\Services\Manager\FormManagerService;
use FluentForm\Framework\Support\Arr;

/**
 * Column properties resolved at runtime via the ORM's magic __get; declared
 * here so static analysis can verify attribute access against the real schema
 * (database/Migrations/Submissions.php).
 *
 * @property int $id
 * @property int|null $form_id
 * @property int|null $serial_number
 * @property string|null $response
 * @property string|null $source_url
 * @property int|null $user_id
 * @property string|null $status
 * @property int $is_favourite
 * @property string|null $browser
 * @property string|null $device
 * @property string|null $ip
 * @property string|null $city
 * @property string|null $country
 * @property string|null $payment_status
 * @property string|null $payment_method
 * @property string|null $payment_type
 * @property string|null $currency
 * @property float|null $payment_total
 * @property float|null $total_paid
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Submission extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fluentform_submissions';

    /**
     * A submission is owned by a User.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    /**
     * A submission is owned by a form.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id', 'id');
    }

    /**
     * A submission has many meta.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function submissionMeta()
    {
        return $this->hasMany(SubmissionMeta::class, 'response_id', 'id');
    }

    /**
     * A submission has many logs.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany(Log::class, 'source_id', 'id');
    }

    /**
     * A submission has many entry details.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function entryDetails()
    {
        return $this->hasMany(EntryDetails::class, 'submission_id', 'id');
    }

    /**
     * A submission has many transactions.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'submission_id', 'id');
    }

    /**
     * A submission has many subscriptions.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'submission_id', 'id');
    }

    /**
     * A submission has many order items.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'submission_id', 'id');
    }

    /**
     * Returns the column the entries listing should be sorted by.
     * Defaults to created_at so imported entries respect their original
     * submission date. Site owners can switch back to legacy id-based
     * ordering via the fluentform/entries_default_sort_column filter.
     */
    public static function getSortColumn()
    {
        $column = apply_filters('fluentform/entries_default_sort_column', 'created_at');
        $allowed = ['id', 'created_at'];

        return in_array($column, $allowed, true) ? $column : 'created_at';
    }

    public function customQuery($attributes = [], $searchExtender = null)
    {
        $entryType = Arr::get($attributes, 'entry_type');
        $dateRange = Arr::get($attributes, 'date_range');
        $isFavourite = false;
        $status = $entryType;

        // We have to handle favorites separately because status and favorites are different
        if ('favorites' === $entryType) {
            $isFavourite = true;
            $status = false;
        }

        $formId = Arr::get($attributes, 'form_id');
        $startDate = Arr::get($dateRange, 0);
        $endDate = Arr::get($dateRange, 1);
        $search = Arr::get($attributes, 'search');
        $sortBy = \FluentForm\App\Helpers\Helper::sanitizeOrderValue(Arr::get($attributes, 'sort_by', 'DESC'));

        $wheres = [];
        $paymentStatuses = Arr::get($attributes, 'payment_statuses');

        if ($paymentStatuses && is_array($paymentStatuses)) {
            $wheres[] = ['payment_status', $paymentStatuses];
        }

        // Sort by submission date so imported entries (which get new auto-increment ids
        // but carry their original created_at) interleave correctly with native ones.
        // Tie-break on id to keep pagination stable for rows sharing a timestamp.
        // Filter lets site owners revert to legacy id-based ordering if needed.
        $sortColumn = self::getSortColumn();
        $query = $this->orderBy('fluentform_submissions.' . $sortColumn, $sortBy);
        if ('id' !== $sortColumn) {
            $query = $query->orderBy('fluentform_submissions.id', $sortBy);
        }
        $query = $query
            ->when($formId, function ($q) use ($formId) {
                return $q->where('fluentform_submissions.form_id', $formId);
            })
            ->when($isFavourite, function ($q) {
                return $q->where('is_favourite', true);
            })
            ->where(function ($q) use ($status) {
                $operator = '=';

                if (!$status) {
                    $operator = '!=';
                    $status = 'trashed';
                }

                return $q->where('fluentform_submissions.status', $operator, $status);
            })
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $endDate .= ' 23:59:59';

                return $q->where('fluentform_submissions.created_at', '>=', $startDate)
                    ->where('fluentform_submissions.created_at', '<=', $endDate);
            })
            ->when($search, function ($q) use ($search, $searchExtender) {
                global $wpdb;
                $escaped = $wpdb->esc_like($search);
                return $q->where(function ($q) use ($escaped, $searchExtender) {
                    $q->where('fluentform_submissions.id', 'LIKE', "%{$escaped}%")
                        ->orWhere('response', 'LIKE', "%{$escaped}%")
                        ->orWhere('fluentform_submissions.status', 'LIKE', "%{$escaped}%")
                        ->orWhere('fluentform_submissions.created_at', 'LIKE', "%{$escaped}%");
                    if ($searchExtender) {
                        $searchExtender($q, $escaped);
                    }
                });
            })
            ->when($wheres, function ($q) use ($wheres) {
                foreach ($wheres as $where) {
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
                            return $q->whereIn($column, $value);
                        } else {
                            return $q->where($column, $operator, $value);
                        }
                    }
                }
            });

        return $query;
    }

    public function paginateEntries($attributes = [])
    {
        $formId = Arr::get($attributes, 'form_id');
        $allowFormIds = FormManagerService::getUserAllowedFormsScope();
        $query = $this->customQuery($attributes);
        $query = $query->when(false !== $allowFormIds, function ($q) use ($allowFormIds) {
            return $q->whereIn('fluentform_submissions.form_id', $allowFormIds ?: [0]);
        });
        if (Arr::get($attributes, 'advanced_filter')) {
            $query = apply_filters('fluentform/apply_entries_advance_filter', $query, $attributes);
        }
        $response = $query->paginate();
        $response = apply_filters_deprecated(
            'fluentform_get_raw_responses',
            [
                $response,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/get_raw_responses',
            'Use fluentform/get_raw_responses instead of fluentform_get_raw_responses.'
        );

        return apply_filters('fluentform/get_raw_responses', $response, $formId);
    }

    public function findAdjacentSubmission($attributes = [])
    {
        $sortBy = Arr::get($attributes, 'sort_by', 'DESC');

        $direction = Arr::get($attributes, 'direction', 'next');

        $operator = 'ASC' === $sortBy && 'previous' === $direction ? '>' : '<';

        if ('previous' === $direction) {
            $operator = 'ASC' === $sortBy ? '>' : '<';
        } else {
            $operator = 'ASC' === $sortBy ? '<' : '>';
            $attributes['sort_by'] = 'ASC' === $sortBy ? 'DESC' : 'ASC';
        }

        $entryId = Arr::get($attributes, 'entry_id');

        $columns = Arr::get($attributes, 'columns', 'id');

        $query = $this->customQuery($attributes);

        // Adjacency must match customQuery's sort order. Compare on the same
        // (sortColumn, id) row-tuple the listing orders by, so Next/Prev walk
        // in display order regardless of which sort column the filter selects.
        // When sortColumn is id the tuple degenerates to a simple id compare.
        $sortColumn = self::getSortColumn();
        // Re-assert whitelist; $sortColumn is interpolated into whereRaw below.
        if (!in_array($sortColumn, ['id', 'created_at'], true)) {
            $sortColumn = 'created_at';
        }
        $current = static::select(['id', $sortColumn])->find($entryId);
        if (!$current) {
            return apply_filters('fluentform/next_submission', null, $entryId, $attributes);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'fluentform_submissions';
        $submission = $query->select($columns)
            ->whereRaw(
                "({$table}.{$sortColumn}, {$table}.id) {$operator} (?, ?)",
                [$current->{$sortColumn}, $entryId]
            )
            ->first();

        return apply_filters('fluentform/next_submission', $submission, $entryId, $attributes);
    }

    public function countByGroup($formId)
    {
        $statuses = $this->selectRaw('status, COUNT(*) as count')
            ->where('form_id', $formId)
            ->groupBy('status')
            ->get();

        $counts = [];

        foreach ($statuses as $status) {
            $counts[$status->status] = (int) $status->count;
        }

        $counts['all'] = array_sum($counts);

        if (isset($counts['trashed'])) {
            $counts['all'] -= $counts['trashed'];
        }

        $favorites = $this->where('form_id', $formId)
            ->where('is_favourite', 1)
            ->where('status', '!=', 'trashed')
            ->count();

        $counts['favorites'] = $favorites;

        return array_merge([
            'unread'  => 0,
            'read'    => 0,
            'spam'    => 0,
            'trashed' => 0,
        ], $counts);
    }

    public function amend($id, $data = [])
    {
        $this->where('id', $id)->update($data);
    }

    public static function remove($submissionIds, $formId = null)
    {
        // Fail-closed scope guard: $formId scopes every delete to its owning form;
        // a missing scope throws rather than ever deleting unscoped.
        if (empty($formId)) {
            throw new \InvalidArgumentException('Submission::remove() requires a form id to scope the deletion.');
        }

        $submissionIds = static::where('form_id', $formId)
            ->whereIn('id', (array) $submissionIds)
            ->pluck('id')
            ->all();

        if (!$submissionIds) {
            return;
        }

        static::whereIn('id', $submissionIds)->delete();

        SubmissionMeta::whereIn('response_id', $submissionIds)->delete();

        Log::whereIn('source_id', $submissionIds)
            ->where('source_type', 'submission_item')
            ->delete();

        EntryDetails::whereIn('submission_id', $submissionIds)->delete();

        try {
            if (PaymentHelper::hasPaymentSettings()) {
                OrderItem::whereIn('submission_id', $submissionIds)->delete();
                Transaction::whereIn('submission_id', $submissionIds)->delete();
                Subscription::whereIn('submission_id', $submissionIds)->delete();
            }

            wpFluent()->table('ff_scheduled_actions')
                ->whereIn('origin_id', $submissionIds)
                ->where('type', 'submission_action')
                ->delete();

        } catch (Exception $exception) {
            // ...
        }
    }

    public function allSubmissions($attributes = []) {
        $searchExtender = function ($q, $escaped) {
            $q->orWhereHas('form', function ($q) use ($escaped) {
                $q->where('title', 'LIKE', "%{$escaped}%");
            });
        };
        $customQuery = $this->customQuery($attributes, $searchExtender);
        $allowFormIds = FormManagerService::getUserAllowedFormsScope();

        $result = $customQuery
            ->with([
                'form' => function ($q) {
                    $q->select(['id', 'title']);
                }
            ])
            ->select(['id', 'form_id', 'status', 'created_at', 'browser', 'currency', 'total_paid'])
            ->when(false !== $allowFormIds, function ($q) use ($allowFormIds){
                return $q->whereIn('form_id', $allowFormIds ?: [0]);
            })
            ->paginate()
            ->toArray();

        $useHumanDate = apply_filters('fluentform/entries_human_date', false);

        foreach ($result['data'] as &$entry) {
            $entry['entry_url'] = admin_url('admin.php?page=fluent_forms&route=entries&form_id=' . $entry['form_id'] . '#/entries/' . $entry['id']);

            if ($useHumanDate) {
                $entry['human_date'] = human_time_diff(strtotime($entry['created_at']), strtotime(current_time('mysql')));
            }
        }

        $result['available_forms'] = $this->availableForms();

        return $result;
    }

    public function availableForms()
    {
        $form = new Form();
        if (false !== ($allowForms = FormManagerService::getUserAllowedFormsScope())) {
            return $form->select('id', 'title')->whereIn('id', $allowForms ?: [0])->get();
        }
        return $form->select('id', 'title')->get();
    }

    public static function report($attributes)
    {
        $from = date('Y-m-d H:i:s', strtotime('-30 days'));
        $to = date('Y-m-d H:i:s', strtotime('+1 days'));
        $formId = Arr::get($attributes, 'form_id');
        $allowFormIds = FormManagerService::getUserAllowedFormsScope();
        $status = Arr::get($attributes, 'entry_status');
        $start = Arr::get($attributes, 'date_range.0', '');
        $end = Arr::get($attributes, 'date_range.1', '');
        $dateRange = Arr::get($attributes, 'date_range');

        if ('all' === $dateRange) {
            $firstItem = self::orderBy('created_at', 'ASC')
                ->when(false !== $allowFormIds, function ($q) use ($allowFormIds) {
                    return $q->whereIn('form_id', $allowFormIds ?: [0]);
                })
                ->when($formId, function ($q) use ($formId) {
                    return $q->where('form_id', $formId);
                })
                ->when($status, function ($q2) use ($status) {
                    return $q2->where('status', $status);
                })
                ->first();

            if ($firstItem && $firstItem->created_at) {
                $from = date('Y-m-d H:i:s', strtotime($firstItem->created_at));
                $to = date('Y-m-d H:i:s');
            }
        }


        if ($start && $startTime = strtotime($start)) {
            $from = date('Y-m-d 00:00:00', $startTime);
        }

        if ($end  && $endTime = strtotime($end)) {
            $to = date('Y-m-d 23:59:59', $endTime);
        }

        $period = new \DatePeriod(new \DateTime($from), new \DateInterval('P1D'), new \DateTime($to));

        $range = [];

        foreach ($period as $date) {
            $range[$date->format('Y-m-d')] = 0;
        }

        $items = self::selectRaw('DATE(created_at) AS date')
            ->selectRaw('COUNT(id) AS count')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->when(false !== $allowFormIds, function ($q) use ($allowFormIds) {
                return $q->whereIn('form_id', $allowFormIds ?: [0]);
            })
            ->when($formId, function ($q) use ($formId) {
                return $q->where('form_id', $formId);
            })
            ->when($status, function ($q2) use ($status) {
                return $q2->where('status', $status);
            })
            ->get();
        

        foreach ($items as $item) {
            $range[$item->date] = $item->count;
        }

        return $range;
    }
}
