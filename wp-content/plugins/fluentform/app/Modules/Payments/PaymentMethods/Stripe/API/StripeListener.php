<?php

namespace FluentForm\App\Modules\Payments\PaymentMethods\Stripe\API;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\Submission;
use FluentForm\App\Models\Subscription;
use FluentForm\App\Models\Transaction;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Payments\PaymentHelper;
use FluentForm\App\Modules\Payments\PaymentMethods\BaseProcessor;
use FluentForm\App\Modules\Payments\PaymentMethods\Stripe\StripeProcessor;
use FluentForm\App\Modules\Payments\PaymentMethods\Stripe\StripeSettings;

class StripeListener
{

    public function verifyIPN()
    {
        // $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'];

        // retrieve the request's body and parse it as JSON
        $body = @file_get_contents('php://input');

        $event = json_decode($body);

        if (!$event || empty($event->id)) {
            return;
        }

        $eventId = $event->id;

        if ($eventId) {
            status_header(200);
            try {
                $formId = StripeSettings::guessFormIdFromEvent($event);

                $event = $this->retrive($eventId, $formId);

                if ($event && !is_wp_error($event)) {
                    $eventType = $event->type;

                    if ($eventType == 'charge.succeeded' || $eventType == 'charge.captured') {
                        $this->handleChargeSucceeded($event);
                    } else if ($eventType == 'invoice.payment_succeeded') {
                        $this->maybeHandleSubscriptionPayment($event);
                    } else if ($eventType == 'charge.refunded') {
                        $this->handleChargeRefund($event);
                    } else if ($eventType == 'customer.subscription.deleted') {
                        $this->handleSubscriptionCancelled($event);
                    } else if ($eventType == 'checkout.session.completed') {
                        $this->handleCheckoutSessionCompleted($event);
                    } else if($eventType == 'customer.subscription.updated') {
                        // maybe we have to handle the
                    }
                }
            } catch (\Exception $e) {
                return; // No event found for this account
            }
        } else {
            status_header(500);
            die('-1'); // Failed
        }
        die('1');
    }

    // This is an onetime payment success
    private function handleChargeSucceeded($event)
    {
        $charge = $event->data->object;

        $meta = (array) $charge->metadata;

        $transactionId = ArrayHelper::get($meta, 'transaction_id');

        if(!$transactionId) {
            $transaction = Transaction::byChargeId($charge->payment_intent)->first();
            if(!$transaction) {
                return;
            }
        } else {
            $submissionId = ArrayHelper::get($meta, 'submission_id');
            $transaction = Transaction::bySubmission($submissionId)
                ->where('id', $transactionId)
                ->where('payment_method', 'stripe')
                ->first();
        }

        if (!$transaction) {
            return;
        }

        if($transaction->status == 'paid') {
            return; // Already paid we don't have to do anything here
        }

        // We have the transaction so we have to update some fields
        $updateData = array(
            'status' => 'paid'
        );

        if (!$transaction->card_last_4) {
            if (!empty($charge->source->last4)) {
                $updateData['card_last_4'] = $charge->source->last4;
            } else if (!empty($charge->payment_method_details->card->last4)) {
                $updateData['card_last_4'] = $charge->payment_method_details->card->last4;
            }
        }
        if (!$transaction->card_brand) {
            if (!empty($charge->source->brand)) {
                $updateData['card_brand'] = $charge->source->brand;
            } else if (!empty($charge->payment_method_details->card->network)) {
                $updateData['card_brand'] = $charge->payment_method_details->card->network;
            }
        }

        if(!$transaction->charge_id) {
            $updateData['charge_id'] = $charge->payment_intent;
        }

        Transaction::where('id', $transaction->id)->update($updateData);

        // We have to fire transaction paid hook here

    }

    /*
     * Handle Subscription Payment IPN
     * Refactored in version 2.0
     */
    private function maybeHandleSubscriptionPayment($event)
    {
        $data = $event->data->object;

        $subscriptionId = false;
        if (property_exists($data, 'subscription')) {
            $subscriptionId = $data->subscription;
        }

        if (!$subscriptionId) {
            return;
        }

        $subscription = Subscription::byVendorSubscriptionId($subscriptionId)
            ->where('vendor_customer_id', $data->customer)
            ->first();

        if (!$subscription) {
            return;
        }

        $submission = Submission::find($subscription->submission_id);

        if (!$submission) {
            return;
        }

        $transactionData = $this->createSubsTransactionDataFromInvoice($data, $subscription, $submission);

        // We may have an already exist session charge that we have to update
        $pendingTransaction = Transaction::bySubmission($submission->id)
            ->whereNull('charge_id')
            ->where('status', 'pending')
            ->first();

        if($pendingTransaction) {
            unset($transactionData['transaction_hash']);
            unset($transactionData['created_at']);

            Transaction::where('id', $pendingTransaction->id)->update($transactionData);
        } else {
            (new StripeProcessor())->recordSubscriptionCharge($subscription, $transactionData);
        }
    }

    /*
     * Refactored at version 2.0
     * We are logging refunds now for both subscription and
     * One time payments
     */
    private function handleChargeRefund($event)
    {
        (new StripeProcessor())->handleRefund($event);
    }

    /*
     * Handle Subscription Canceled
     */
    private function handleSubscriptionCancelled($event)
    {
        $data = $event->data->object;
        $subscriptionId = $data->id;

        $subscription = Subscription::byVendorSubscriptionId($subscriptionId)
            ->where('status', '!=', 'completed')
            ->first();

        if (!$subscription || $subscription->status == 'cancelled') {
            return;
        }

        do_action_deprecated(
            'fluentform_form_submission_activity_start',
            [
                $subscription->form_id
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/form_submission_activity_start',
            'Use fluentform/form_submission_activity_start instead of fluentform_form_submission_activity_start.'
        );

        do_action('fluentform/form_submission_activity_start', $subscription->form_id);

        PaymentHelper::recordSubscriptionCancelled($subscription, $data);
    }


    private function handleCheckoutSessionCompleted($event)
    {
        $data = $event->data->object;

        $metaData = (array)$data->metadata;

        $formId = ArrayHelper::get($metaData, 'form_id');

        $session = CheckoutSession::retrieve($data->id, [
            'expand' => [
                'subscription.latest_invoice.payment_intent',
                'payment_intent'
            ]
        ], $formId);

        if (!$session || is_wp_error($session)) {
            return;
        }

        $submissionId = intval($session->client_reference_id);
        if (!$session || !$submissionId) {
            return;
        }

        if (Helper::getSubmissionMeta($submissionId, 'is_form_action_fired') == 'yes') {
            return;
        }

        // let's get the pending submission
        $submission = Submission::find($submissionId);

        if (!$submission) {
            return;
        }

        $transactionId = ArrayHelper::get($metaData, 'transaction_id');

        if(!$transactionId) {
            return;
        }

        $transaction = Transaction::bySubmission($submission->id)
            ->where('form_id', $submission->form_id)
            ->where('id', $transactionId)
            ->first();

        if(!$transaction) {
            return; // not our transaction or transaction_status already paid
        }

        $returnData = (new StripeProcessor())->processStripeSession($session, $submission, $transaction);
    }

    /*
     *
     */
    public function retrive($eventId, $formId = false)
    {
        $api = new ApiRequest();
        $api::set_secret_key(StripeSettings::getSecretKey($formId));
        return $api::request([], 'events/' . $eventId, 'GET');
    }

    public function verifySignature($payload, $signature)
    {
        // Extract timestamp and signatures from header
        $timestamp = self::getTimestamp($signature);
        $signatures = self::getSignatures($signature);

        if (-1 === $timestamp) {
            return false;
        }
        if (empty($signatures)) {
            return false;
        }

        $signedPayload = "{$timestamp}.{$payload}";

        if (!function_exists('hash_hmac')) {
            return false;
        }

        $secret = 'whsec_NsNZNMSnWVPLt8GErz3SVZ97pWu8eb6D';

        $expectedSignature = \hash_hmac('sha256', $payload, $secret);

        // Reject events older than 5 minutes to prevent replay attacks
        if (abs(time() - $timestamp) > 300) {
            return false;
        }

        foreach ($signatures as $signature) {
            if ($this->secureCompare($signature, $expectedSignature)) {
                return true;
            }
        }

        return false;
    }

    protected function getTimeStamp($signature)
    {
        $items = \explode(',', $signature);

        foreach ($items as $item) {
            $itemParts = \explode('=', $item, 2);
            if ('t' === $itemParts[0]) {
                if (!\is_numeric($itemParts[1])) {
                    return -1;
                }

                return (int)($itemParts[1]);
            }
        }

        return -1;
    }

    private function getSignatures($header, $scheme = 'v1')
    {
        $signatures = [];
        $items = \explode(',', $header);

        foreach ($items as $item) {
            $itemParts = \explode('=', $item, 2);
            if (\trim($itemParts[0]) === $scheme) {
                $signatures[] = $itemParts[1];
            }
        }

        return $signatures;
    }

    protected function secureCompare($a, $b)
    {
        if (function_exists('hash_equals')) {
            return \hash_equals($a, $b);
        }

        if (\strlen($a) !== \strlen($b)) {
            return false;
        }

        $result = 0;
        for ($i = 0; $i < \strlen($a); ++$i) {
            $result |= \ord($a[$i]) ^ \ord($b[$i]);
        }

        return 0 === $result;
    }

    protected function createSubsTransactionDataFromInvoice($invoice, $subscription, $submission)
    {
        $paymentIntent = false;
        if(!is_object($invoice->payment_intent)) {
            ApiRequest::set_secret_key(StripeSettings::getSecretKey($subscription->form_id));
            $paymentIntent = ApiRequest::request([], 'payment_intents/'.$invoice->payment_intent, 'GET');
            if(is_wp_error($paymentIntent)) {
                $paymentIntent = false;
            }
            $chargeId = $invoice->payment_intent;
        } else {
            $chargeId = $invoice->payment_intent->id;
            $paymentIntent = $invoice->payment_intent;
        }

        $paymentTotal = $invoice->amount_paid;
        if(PaymentHelper::isZeroDecimal($invoice->currency)) {
            $paymentTotal = $paymentTotal * 100;
        }

        $data = [
            'subscription_id' => $subscription->id,
            'form_id' => $subscription->form_id,
            'transaction_hash' => md5('subscription_trans_'.wp_generate_uuid4().time()),
            'user_id' => $submission->user_id,
            'submission_id' => $submission->id,
            'transaction_type' => 'subscription',
            'payment_method' => 'stripe',
            'card_last_4' => '',
            'card_brand' => '',
            'payer_name' => $invoice->customer_name,
            'payer_email' => $invoice->customer_email,
            'charge_id' => $chargeId,
            'payment_total' => $paymentTotal,
            'status' => 'paid',
            'currency' => strtoupper($invoice->currency),
            'payment_mode' => ($invoice->livemode) ? 'live' : 'test',
            'payment_note' => maybe_serialize($invoice),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ];

        if($paymentIntent && !empty($paymentIntent->charges->data[0]->payment_method_details->card)) {
            $card = $paymentIntent->charges->data[0]->payment_method_details->card;
            $data['card_brand'] = $card->brand;
            $data['card_last_4'] = $card->last4;
        }

        return $data;
    }
}
