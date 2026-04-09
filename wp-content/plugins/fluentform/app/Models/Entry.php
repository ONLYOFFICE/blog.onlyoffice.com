<?php

namespace FluentForm\App\Models;

use Exception;
use FluentForm\App\Modules\Payments\PaymentHelper;
use FluentForm\Framework\Support\Arr;

class Entry extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fluentform_submissions';

    /**
     * A formMeta is owned by a form.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id', 'id');
    }

    /**
     * An entry has many meta.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function entryMeta()
    {
        return $this->hasMany(EntryMeta::class, 'response_id', 'id');
    }

    /**
     * An entry has many logs.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany(Log::class, 'source_id', 'id');
    }

    /**
     * An entry has many entry details.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function entryDetails()
    {
        return $this->hasMany(EntryDetails::class, 'submission_id', 'id');
    }

    public function paginateEntries($attributes = [])
    {
        $formId = Arr::get($attributes, 'form_id');
        $isFavourite = Arr::get($attributes, 'is_favourite');
        $status = Arr::get($attributes, 'status');
        $startDate = Arr::get($attributes, 'start_date');
        $endDate = Arr::get($attributes, 'end_date');
        $search = Arr::get($attributes, 'search');
        $wheres = Arr::get($attributes, 'wheres');

        $sortBy = \FluentForm\App\Helpers\Helper::sanitizeOrderValue(Arr::get($attributes, 'sort_by', 'DESC'));
        $query = $this->orderBy('id', $sortBy)
            ->when($formId, function ($q) use ($formId) {
                return $q->where('form_id', $formId);
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

                return $q->where('status', $operator, $status);
            })
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $endDate .= ' 23:59:59';

                return $q->where('created_at', '>=', $startDate)
                    ->where('created_at', '<=', $endDate);
            })
            ->when($search, function ($q) use ($search) {
                global $wpdb;
                $escaped = $wpdb->esc_like($search);
                return $q->where(function ($q) use ($escaped) {
                    return $q->where('id', 'LIKE', "%{$escaped}%")
                        ->orWhere('response', 'LIKE', "%{$escaped}%")
                        ->orWhere('status', 'LIKE', "%{$escaped}%")
                        ->orWhere('created_at', 'LIKE', "%{$escaped}%");
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

    public static function remove($entryIds)
    {
        static::whereIn('id', $entryIds)->delete();

        EntryMeta::whereIn('response_id', $entryIds)->delete();

        Log::whereIn('source_id', $entryIds)
            ->where('source_type', 'submission_item')
            ->delete();

        EntryDetails::whereIn('submission_id', $entryIds)->delete();

        try {
            if (PaymentHelper::hasPaymentSettings()) {
                OrderItem::whereIn('submission_id', $entryIds)->delete();
                Transaction::whereIn('submission_id', $entryIds)->delete();
                Subscription::whereIn('submission_id', $entryIds)->delete();
            }

            wpFluent()->table('ff_scheduled_actions')
                ->whereIn('origin_id', $entryIds)
                ->where('type', 'submission_action')
                ->delete();
        } catch (Exception $exception) {
            // ...
        }
    }
}
