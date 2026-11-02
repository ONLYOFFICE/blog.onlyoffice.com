<?php

namespace FluentForm\App\Models;

class Transaction extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fluentform_transactions';

    /**
     * A transaction belongs to a form.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id', 'id');
    }

    /**
     * A transaction belongs to a submission.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id', 'id');
    }

    /**
     * A transaction may belong to a subscription.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'id');
    }

    public function scopeBySubmission($query, $submissionId)
    {
        return $query->where('submission_id', $submissionId);
    }

    public function scopeByChargeId($query, $chargeId)
    {
        return $query->where('charge_id', $chargeId);
    }

    public function scopeOnetime($query)
    {
        return $query->where('transaction_type', 'onetime');
    }

    public function scopeRefunds($query)
    {
        return $query->where('transaction_type', 'refund');
    }

    public function scopeSubscriptionType($query)
    {
        return $query->where('transaction_type', 'subscription');
    }

    public function scopePaid($query)
    {
        return $query->whereIn('status', [
            'paid', 'requires_capture', 'processing', 'partially-refunded', 'refunded'
        ]);
    }
}
