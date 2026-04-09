<?php

namespace FluentForm\App\Models;

class Subscription extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fluentform_subscriptions';

    /**
     * A subscription belongs to a form.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id', 'id');
    }

    /**
     * A subscription belongs to a submission.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id', 'id');
    }

    /**
     * A subscription has many transactions.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'subscription_id', 'id');
    }

    public function getOriginalPlanAttribute($value)
    {
        return maybe_unserialize($value);
    }

    public function getVendorResponseAttribute($value)
    {
        return maybe_unserialize($value);
    }

    public function scopeBySubmission($query, $submissionId)
    {
        return $query->where('submission_id', $submissionId);
    }

    public function scopeByVendorSubscriptionId($query, $vendorId)
    {
        return $query->where('vendor_subscription_id', $vendorId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
