<?php

namespace FluentForm\App\Modules\MCP\Support;

defined('ABSPATH') || exit;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\App\Modules\Payments\Orders\OrderData;
use FluentForm\App\Modules\Payments\PaymentHelper;

/**
 * Default payment provider for the MCP submission seams.
 *
 * Listens on fluentform/mcp_submission_data (entry) and
 * fluentform/mcp_submission_rows (list) to attach a compact payment block from
 * the core payment tables. The entry tools authorize on entry-view permission,
 * so THIS listener owns the payments boundary: nothing is injected unless the
 * user holds fluentform_view_payments for the entry's form. Fail-closed — any
 * error returns the payload unchanged. An addon that already populated
 * 'payment' wins; this provider never overwrites.
 */
class PaymentDataProvider
{
    public static function register()
    {
        add_filter('fluentform/mcp_submission_data', [__CLASS__, 'addEntryPayment'], 10, 2);
        add_filter('fluentform/mcp_submission_rows', [__CLASS__, 'addRowPayments'], 10, 3);
    }

    public static function addEntryPayment($data, $submission)
    {
        if (isset($data['payment'])) {
            return $data;
        }

        try {
            $formId = isset($submission->form_id) ? (int) $submission->form_id : 0;
            if (!$formId || !Acl::hasPermission('fluentform_view_payments', $formId)) {
                return $data;
            }

            $payment = self::buildPayment($submission);
            if ($payment) {
                $data['payment'] = $payment;
            }
        } catch (\Throwable $e) {
            return $data;
        }

        return $data;
    }

    public static function addRowPayments($rows, $items, $formId)
    {
        try {
            $formId = (int) $formId;
            if (!$formId || !Acl::hasPermission('fluentform_view_payments', $formId)) {
                return $rows;
            }

            // The seam passes the page's Submission models (payment columns
            // included), so the per-row summary needs no query of its own.
            $map = [];
            foreach ($items as $submission) {
                if (!isset($submission->payment_status) || !$submission->payment_status) {
                    continue;
                }
                $map[(int) $submission->id] = [
                    'status' => $submission->payment_status,
                    'total'  => isset($submission->payment_total)
                        ? PaymentHelper::formatMoney($submission->payment_total, isset($submission->currency) ? $submission->currency : '')
                        : null,
                ];
            }

            if (!$map) {
                return $rows;
            }

            foreach ($rows as &$row) {
                $id = isset($row['id']) ? (int) $row['id'] : 0;
                if ($id && isset($map[$id]) && !isset($row['payment'])) {
                    $row['payment'] = $map[$id];
                }
            }
            unset($row);
        } catch (\Throwable $e) {
            return $rows;
        }

        return $rows;
    }

    /**
     * Compact, agent-sized block: status, formatted total, currency, method,
     * transaction count, and subscription status/interval. Never raw cents,
     * payment_note, vendor_response, or original_plan.
     */
    private static function buildPayment($submission)
    {
        // The submission row already carries the payment columns; when none is
        // set this is a non-payment entry — skip both transaction queries.
        $hasPaymentColumns = (isset($submission->payment_status) && $submission->payment_status)
            || isset($submission->payment_total)
            || (isset($submission->payment_type) && $submission->payment_type);
        if (!$hasPaymentColumns) {
            return null;
        }

        $transactions = OrderData::getTransactions($submission->id);
        list($subscriptions) = OrderData::getSubscriptionsAndPaymentTotal($submission);

        // count(), not empty(): ->get() returns a Collection and
        // empty(Collection) is always false — an empty() guard would fabricate
        // a payment block on every non-payment entry.
        $hasStatus = isset($submission->payment_status) && $submission->payment_status;
        if (!$hasStatus && 0 === count($transactions) && 0 === count($subscriptions)) {
            return null;
        }

        $currency = isset($submission->currency) ? $submission->currency : '';

        $payment = [
            'status'            => $hasStatus ? $submission->payment_status : null,
            'total'             => isset($submission->payment_total) ? PaymentHelper::formatMoney($submission->payment_total, $currency) : null,
            'currency'          => $currency,
            'payment_method'    => isset($submission->payment_method) ? $submission->payment_method : null,
            'transaction_count' => count($transactions),
        ];

        if (count($subscriptions)) {
            $subscription = $subscriptions[0];
            $payment['subscription'] = [
                'status'           => isset($subscription->status) ? $subscription->status : null,
                'billing_interval' => isset($subscription->billing_interval) ? $subscription->billing_interval : null,
            ];
        }

        return $payment;
    }
}
