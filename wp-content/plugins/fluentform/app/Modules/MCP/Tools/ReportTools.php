<?php

namespace FluentForm\App\Modules\MCP\Tools;

defined('ABSPATH') || exit;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\Form;
use FluentForm\App\Models\Submission;
use FluentForm\App\Modules\MCP\Support\ErrorCodes;
use FluentForm\App\Modules\MCP\Support\FormAccess;
use FluentForm\App\Modules\MCP\Support\MCPHelper;
use FluentForm\App\Modules\MCP\Support\PermissionGate;
use FluentForm\App\Services\Report\ReportHelper;
use FluentForm\Framework\Support\Arr;

/**
 * Report tools (read).
 *
 * The get-form-stats tool answers "how is this form doing?" — entry counts by
 * status, total views, and conversion rate. get-submissions-trend gives a daily time
 * series for charting volume over a window. get-submissions-report ranks forms by
 * volume and surfaces the busiest hours and weekdays across a date range.
 * get-payment-summary answers revenue questions with a single server-side SUM
 * over the transactions table, grouped by type and status — the same figures the
 * admin Reports page shows. All form-scoped.
 */
class ReportTools
{
    const TREND_MAX_DAYS = 366;

    const TOP_FORMS = 20;

    public static function definitions()
    {
        return [
            'fluentform/get-form-stats' => [
                'label'       => __('Get Form Stats', 'fluentform'),
                'group'       => __('Reports', 'fluentform'),
                'description' => __('Headline numbers for one form: entry counts by status (unread, read, spam, trashed, favorites, all), total views, and conversion rate (entries ÷ views). Requires form_id.', 'fluentform'),
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'form_id' => ['type' => 'integer'],
                    ],
                    'required' => ['form_id'],
                ],
                'execute_callback'    => [self::class, 'getFormStats'],
                'capability'          => ['fluentform_entries_viewer', 'fluentform_dashboard_access'],
                'annotations' => ['readonly' => true],
            ],

            'fluentform/get-submissions-trend' => [
                'label'       => __('Get Submissions Trend', 'fluentform'),
                'group'       => __('Reports', 'fluentform'),
                'description' => __('Daily entry counts for one form over a date window (defaults to the last 30 days, maximum 366 days). Returns a date→count series for charting submission volume. Requires form_id.', 'fluentform'),
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'form_id'   => ['type' => 'integer'],
                        'date_from' => ['type' => 'string', 'description' => 'YYYY-MM-DD (site timezone). Defaults to 30 days ago. The window may span at most 366 days.'],
                        'date_to'   => ['type' => 'string', 'description' => 'YYYY-MM-DD (site timezone). Defaults to today.'],
                    ],
                    'required' => ['form_id'],
                ],
                'execute_callback'    => [self::class, 'getTrend'],
                'capability'          => ['fluentform_entries_viewer', 'fluentform_dashboard_access'],
                'annotations' => ['readonly' => true],
            ],

            'fluentform/get-payment-summary' => [
                'label'       => __('Get Payment Summary', 'fluentform'),
                'group'       => __('Reports', 'fluentform'),
                'description' => __('Payment totals computed server-side: amount and count per payment status (paid, pending, refunded, …), split into one-time and subscription transactions, and grouped by currency (amounts in different currencies are reported separately, never summed together). Scope to one form with form_id or omit it to aggregate every form you can access. Defaults to the last 30 days; widen date_from/date_to for lifetime totals.', 'fluentform'),
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'form_id'   => ['type' => 'integer', 'description' => 'Optional. Omit to aggregate across all forms in your access scope.'],
                        'date_from' => ['type' => 'string', 'description' => 'YYYY-MM-DD (site timezone). Defaults to 30 days ago.'],
                        'date_to'   => ['type' => 'string', 'description' => 'YYYY-MM-DD (site timezone). Defaults to today.'],
                    ],
                ],
                'execute_callback'    => [self::class, 'getPaymentSummary'],
                'capability'          => 'fluentform_view_payments',
                'annotations' => ['readonly' => true],
            ],

            'fluentform/get-submissions-report' => [
                'label'       => __('Get Submissions Report', 'fluentform'),
                'group'       => __('Reports', 'fluentform'),
                'description' => __('Cross-form submission analytics for a date range: total entries, the forms ranked by submission count (top_forms — answers "which form got the most submissions"), and when they arrive — busiest hours (by_hour 0–23 in site time, plus peak_hour) and busiest days (by_weekday Monday–Sunday, plus peak_weekday). Scope to one form with form_id, or omit it to cover every form you can access. Defaults to the last 30 days (max 366).', 'fluentform'),
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'form_id'   => ['type' => 'integer', 'description' => 'Optional. Omit to cover every form in your access scope.'],
                        'date_from' => ['type' => 'string', 'description' => 'YYYY-MM-DD (site timezone). Defaults to 30 days ago. The window may span at most 366 days.'],
                        'date_to'   => ['type' => 'string', 'description' => 'YYYY-MM-DD (site timezone). Defaults to today.'],
                    ],
                ],
                'execute_callback'    => [self::class, 'getSubmissionsReport'],
                'capability'          => ['fluentform_entries_viewer', 'fluentform_dashboard_access'],
                'annotations' => ['readonly' => true],
            ],
        ];
    }

    public static function getFormStats($params = [])
    {
        $form = FormAccess::resolveForm($params);
        if (is_wp_error($form)) {
            return $form;
        }
        $formId = (int) $form->id;

        $counts = (new Submission())->countByGroup($formId);
        $views  = (int) Helper::getFormMeta($formId, '_total_views', 0);
        $all    = isset($counts['all']) ? (int) $counts['all'] : 0;

        $conversion = $views > 0 ? round(($all / $views) * 100, 2) : null;

        $data = [
            'form_id'             => $formId,
            'counts'              => $counts,
            'total_views'         => $views,
            'conversion_rate_pct' => $conversion,
        ];

        // Entries can exceed tracked views (e.g. a form embedded in a template
        // with view tracking off), which pushes the rate past 100% — flag it
        // rather than emit a bogus number the agent would read as real.
        if (null !== $conversion && $all > $views) {
            $data['views_note'] = __('Entries exceed tracked views, so the conversion rate is unreliable — view tracking may be disabled or unavailable for this form.', 'fluentform');
        }

        return MCPHelper::envelope(
            sprintf(
                /* translators: 1: form title, 2: entry count, 3: view count */
                __('"%1$s": %2$d entries from %3$d views.', 'fluentform'),
                $form->title,
                $all,
                $views
            ),
            $data
        );
    }

    public static function getTrend($params = [])
    {
        $form = FormAccess::resolveForm($params);
        if (is_wp_error($form)) {
            return $form;
        }
        $formId = (int) $form->id;

        $window = self::dateWindow($params, self::TREND_MAX_DAYS);
        if (is_wp_error($window)) {
            return $window;
        }
        list($from, $to) = $window;

        $rows = Submission::query()
            ->where('form_id', $formId)
            ->where('status', '!=', 'trashed')
            ->where('created_at', '>=', $from . ' 00:00:00')
            ->where('created_at', '<=', $to . ' 23:59:59')
            ->selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->get();

        $series = [];
        $total  = 0;
        foreach ($rows as $row) {
            $count    = (int) $row->count;
            $total   += $count;
            $series[] = ['date' => $row->day, 'count' => $count];
        }

        return MCPHelper::envelope(
            sprintf(
                /* translators: 1: total entries, 2: start date, 3: end date */
                __('%1$d entries between %2$s and %3$s.', 'fluentform'),
                $total,
                $from,
                $to
            ),
            [
                'form_id' => $formId,
                'from'    => $from,
                'to'      => $to,
                'total'   => $total,
                'series'  => $series,
            ]
        );
    }

    public static function getPaymentSummary($params = [])
    {
        $formId = 0;
        if (!empty($params['form_id'])) {
            // ReportHelper trusts a non-zero form id without a scope check, so
            // the access check has to happen here, before it's passed down.
            $form = FormAccess::resolveForm($params);
            if (is_wp_error($form)) {
                return $form;
            }
            $formId = (int) $form->id;
        }

        $settings = get_option('__fluentform_payment_module_settings');
        if (!$settings || !Arr::isTrue($settings, 'status')) {
            return MCPHelper::error(ErrorCodes::FEATURE_DISABLED, __('The payment module is disabled, so there is no payment data to summarize.', 'fluentform'));
        }

        // No span clamp: this is a SUM grouped by status (a handful of rows),
        // not per-day buckets, and the admin Reports page runs the same query
        // unbounded — lifetime totals are a legitimate ask.
        $window = self::dateWindow($params);
        if (is_wp_error($window)) {
            return $window;
        }
        list($from, $to) = $window;

        $fromDt = $from . ' 00:00:00';
        $toDt   = $to . ' 23:59:59';

        // One canonical scan for both types (admin Reports reads the same helper), so
        // the agent and the admin page can't drift. by_currency never blends currencies
        // (a cross-currency SUM is meaningless); full-day bounds keep the window
        // inclusive of the last day. Form scoping is identical on both sides — both
        // resolve FormManagerService::getUserAllowedFormsScope().
        $breakdown     = ReportHelper::getPaymentBreakdown($fromDt, $toDt, $formId);
        $subByCurrency = Arr::get($breakdown, 'subscription.by_currency', []);
        $oneByCurrency = Arr::get($breakdown, 'onetime.by_currency', []);

        $emptyBlock = ['currency_symbol' => '', 'payment_statuses' => [], 'total_amount' => 0, 'weekly_average' => 0];

        // Currency-first: each currency carries its own onetime + subscription block.
        $currencies = [];
        foreach (array_unique(array_merge(array_keys($oneByCurrency), array_keys($subByCurrency))) as $currency) {
            $onetime      = Arr::get($oneByCurrency, $currency, $emptyBlock);
            $subscription = Arr::get($subByCurrency, $currency, $emptyBlock);
            $symbol       = '' !== $onetime['currency_symbol'] ? $onetime['currency_symbol'] : $subscription['currency_symbol'];
            // A block empty for this currency still labels itself with the currency's symbol.
            $onetime['currency_symbol']      = $symbol;
            $subscription['currency_symbol'] = $symbol;
            $paid         = (float) Arr::get($onetime, 'payment_statuses.paid.amount', 0)
                          + (float) Arr::get($subscription, 'payment_statuses.paid.amount', 0);

            $currencies[] = [
                'currency'        => $currency,
                'currency_symbol' => $symbol,
                'total_paid'      => round($paid, 2),
                'onetime'         => $onetime,
                'subscription'    => $subscription,
            ];
        }

        // Highest paid total first, so the dominant currency leads.
        usort($currencies, function ($a, $b) {
            return $b['total_paid'] <=> $a['total_paid'];
        });

        $data = [
            'form_id'        => $formId ? $formId : null,
            'from'           => $from,
            'to'             => $to,
            'multi_currency' => count($currencies) > 1,
            'currencies'     => $currencies,
        ];

        if (empty($currencies)) {
            $data['total_paid'] = 0;

            return MCPHelper::envelope(
                sprintf(
                    /* translators: 1: start date, 2: end date */
                    __('No payments between %1$s and %2$s.', 'fluentform'),
                    $from,
                    $to
                ),
                $data
            );
        }

        // One currency (the common case): surface it at the top level too, so the
        // answer is one hop away. Only ever for a single currency — surfacing a
        // top-level total across currencies is exactly the blend this must not do.
        if (1 === count($currencies)) {
            $only                    = $currencies[0];
            $data['currency']        = $only['currency'];
            $data['currency_symbol'] = $only['currency_symbol'];
            $data['total_paid']      = $only['total_paid'];
            $data['onetime']         = $only['onetime'];
            $data['subscription']    = $only['subscription'];

            return MCPHelper::envelope(
                sprintf(
                    /* translators: 1: currency symbol, 2: paid amount, 3: start date, 4: end date */
                    __('%1$s%2$s paid between %3$s and %4$s.', 'fluentform'),
                    $only['currency_symbol'],
                    number_format($only['total_paid'], 2),
                    $from,
                    $to
                ),
                $data
            );
        }

        $parts = [];
        foreach ($currencies as $c) {
            $parts[] = $c['currency_symbol'] . number_format($c['total_paid'], 2) . ' ' . $c['currency'];
        }
        $data['note'] = __('Amounts span multiple currencies and are reported separately under "currencies"; they are not summed.', 'fluentform');

        return MCPHelper::envelope(
            sprintf(
                /* translators: 1: comma-separated per-currency paid totals, 2: start date, 3: end date */
                __('Paid between %2$s and %3$s by currency: %1$s.', 'fluentform'),
                implode('; ', $parts),
                $from,
                $to
            ),
            $data
        );
    }

    public static function getSubmissionsReport($params = [])
    {
        // Resolve the form set: a single authorized form, or every form in scope.
        // null = unrestricted (all forms); [] = restricted to no forms.
        $formIds = null;
        if (!empty($params['form_id'])) {
            $form = FormAccess::resolveForm($params);
            if (is_wp_error($form)) {
                return $form;
            }
            $formIds = [(int) $form->id];
        } else {
            $scope = PermissionGate::formScope();
            if (is_array($scope)) {
                $formIds = array_values(array_map('intval', $scope));
            }
        }

        $window = self::dateWindow($params, self::TREND_MAX_DAYS);
        if (is_wp_error($window)) {
            return $window;
        }
        list($from, $to) = $window;

        if (is_array($formIds) && empty($formIds)) {
            return MCPHelper::envelope(
                __('No forms are within your access scope, so there is nothing to report.', 'fluentform'),
                self::emptyReport($from, $to)
            );
        }

        $fromDt = $from . ' 00:00:00';
        $toDt   = $to . ' 23:59:59';

        // Rebuilt per aggregate — a query builder is consumed by its terminal call.
        $base = function () use ($formIds, $fromDt, $toDt) {
            $q = Submission::query()
                ->where('status', '!=', 'trashed')
                ->where('created_at', '>=', $fromDt)
                ->where('created_at', '<=', $toDt);
            if (null !== $formIds) {
                $q->whereIn('form_id', $formIds);
            }
            return $q;
        };

        // Three bounded aggregates instead of one (form_id, hour, weekday) scan that
        // hydrated forms x 24 x 7 rows into PHP: each groups by a single dimension, so
        // the result set is capped (<=24 hours, <=7 weekdays, <=TOP_FORMS forms) no
        // matter how many forms exist. MySQL DAYOFWEEK: 1=Sunday … 7=Saturday.
        $dowNames  = [1 => 'Sunday', 2 => 'Monday', 3 => 'Tuesday', 4 => 'Wednesday', 5 => 'Thursday', 6 => 'Friday', 7 => 'Saturday'];
        $byHour    = array_fill(0, 24, 0);
        $byWeekday = array_fill_keys(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'], 0);

        foreach ($base()->selectRaw('HOUR(created_at) as h, COUNT(*) as c')->groupBy('h')->get() as $r) {
            $byHour[(int) $r->h] = (int) $r->c;
        }
        foreach ($base()->selectRaw('DAYOFWEEK(created_at) as d, COUNT(*) as c')->groupBy('d')->get() as $r) {
            $name = isset($dowNames[(int) $r->d]) ? $dowNames[(int) $r->d] : '';
            if ('' !== $name) {
                $byWeekday[$name] = (int) $r->c;
            }
        }
        // Every entry falls in exactly one hour bucket, so the hour totals sum to the
        // window total — no separate COUNT scan needed.
        $total = array_sum($byHour);

        $peakHour    = $total > 0 ? self::indexOfMax($byHour) : null;
        $peakWeekday = $total > 0 ? self::keyOfMax($byWeekday) : null;

        // Leaderboard: SQL ranks + caps the per-form totals (<=TOP_FORMS rows), then a
        // single batched title lookup — no full per-form hydration in PHP.
        $rankIds = [];
        $counts  = [];
        foreach ($base()->selectRaw('form_id, COUNT(*) as c')->groupBy('form_id')->orderByRaw('c DESC')->limit(self::TOP_FORMS)->get() as $r) {
            $fid          = (int) $r->form_id;
            $rankIds[]    = $fid;
            $counts[$fid] = (int) $r->c;
        }
        $titles = [];
        if ($rankIds) {
            foreach (Form::query()->whereIn('id', $rankIds)->get(['id', 'title']) as $f) {
                $titles[(int) $f->id] = $f->title;
            }
        }
        $topForms = [];
        foreach ($rankIds as $fid) {
            $topForms[] = [
                'form_id' => $fid,
                'title'   => isset($titles[$fid]) ? $titles[$fid] : null,
                'count'   => $counts[$fid],
            ];
        }

        $summary = ($topForms && null !== $peakHour)
            ? sprintf(
                /* translators: 1: total entries, 2: top form title, 3: peak weekday, 4: peak hour */
                __('%1$d entries. Top form: "%2$s". Busiest: %3$s around %4$02d:00.', 'fluentform'),
                $total,
                (string) $topForms[0]['title'],
                (string) $peakWeekday,
                (int) $peakHour
            )
            : sprintf(
                /* translators: %d: total entries */
                __('%d entries in this window.', 'fluentform'),
                $total
            );

        return MCPHelper::envelope($summary, [
            'form_id'      => (is_array($formIds) && 1 === count($formIds)) ? $formIds[0] : null,
            'from'         => $from,
            'to'           => $to,
            'total'        => $total,
            'top_forms'    => $topForms,
            'by_hour'      => $byHour,
            'peak_hour'    => $peakHour,
            'by_weekday'   => $byWeekday,
            'peak_weekday' => $peakWeekday,
        ]);
    }

    private static function emptyReport($from, $to)
    {
        return [
            'form_id'      => null,
            'from'         => $from,
            'to'           => $to,
            'total'        => 0,
            'top_forms'    => [],
            'by_hour'      => array_fill(0, 24, 0),
            'peak_hour'    => null,
            'by_weekday'   => array_fill_keys(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'], 0),
            'peak_weekday' => null,
        ];
    }

    /** Index of the highest value in a 0-based list (first wins on a tie). */
    private static function indexOfMax(array $vals)
    {
        $max = -1;
        $idx = 0;
        foreach ($vals as $i => $v) {
            if ($v > $max) {
                $max = $v;
                $idx = $i;
            }
        }

        return $idx;
    }

    /** Key of the highest value in a map (first wins on a tie, insertion order). */
    private static function keyOfMax(array $map)
    {
        $max = -1;
        $key = null;
        foreach ($map as $k => $v) {
            if ($v > $max) {
                $max = $v;
                $key = $k;
            }
        }

        return $key;
    }

    /**
     * Validated Y-m-d [from, to] window from tool params. Defaults to the last
     * 30 days ending today; a non-zero $maxDays caps the span.
     *
     * @return array|\WP_Error
     */
    private static function dateWindow($params, $maxDays = 0)
    {
        $to = !empty($params['date_to']) ? sanitize_text_field($params['date_to']) : gmdate('Y-m-d', current_time('timestamp'));
        if (!MCPHelper::isYmd($to)) {
            return MCPHelper::error(ErrorCodes::INVALID_PARAM, __('date_to must be a valid date in YYYY-MM-DD format.', 'fluentform'), ['fields' => ['date_to']]);
        }

        $from = !empty($params['date_from']) ? sanitize_text_field($params['date_from']) : gmdate('Y-m-d', strtotime('-30 days', strtotime($to)));
        if (!MCPHelper::isYmd($from)) {
            return MCPHelper::error(ErrorCodes::INVALID_PARAM, __('date_from must be a valid date in YYYY-MM-DD format.', 'fluentform'), ['fields' => ['date_from']]);
        }

        if ($from > $to) {
            return MCPHelper::error(ErrorCodes::INVALID_PARAM, __('date_from must be on or before date_to.', 'fluentform'), ['fields' => ['date_from', 'date_to']]);
        }

        if ($maxDays) {
            $spanDays = (int) (new \DateTime($from))->diff(new \DateTime($to))->days;
            if ($spanDays > $maxDays) {
                return MCPHelper::error(
                    ErrorCodes::INVALID_PARAM,
                    sprintf(
                        /* translators: %d: maximum allowed days in the date window */
                        __('The date window may span at most %d days. Narrow date_from/date_to and call again.', 'fluentform'),
                        $maxDays
                    ),
                    ['fields' => ['date_from', 'date_to'], 'max_days' => $maxDays]
                );
            }
        }

        return [$from, $to];
    }
}
