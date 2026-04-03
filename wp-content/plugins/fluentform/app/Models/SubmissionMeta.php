<?php

namespace FluentForm\App\Models;

use FluentForm\App\Helpers\Helper;

// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- This class uses custom meta table (fluentform_submission_meta), not WordPress postmeta
class SubmissionMeta extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fluentform_submission_meta';

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
     * A formMeta is owned by a submission.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function submission()
    {
        return $this->belongsTo(Submission::class, 'response_id', 'id');
    }


    public static function retrieve($key, $submissionId = null, $default  = null)
    {
        $meta = static::when($submissionId, function ($q) use ($submissionId) {
            return $q->where('response_id', $submissionId);
        })
            ->where('meta_key', $key)
            ->first();

        if ($meta && isset($meta->value)) {
            return Helper::safeUnserialize($meta->value);
        }

        return $default;
    }

    public static function persist($submissionId, $metaKey, $metaValue, $formId = null)
    {
        $metaValue = maybe_serialize($metaValue);
        if (!$formId) {
            $formId = Submission::select('form_id')->where('id', $submissionId)->value('form_id');
        }
        return static::updateOrCreate(
            ['response_id' => $submissionId, 'meta_key' => $metaKey],
            [
                'value' => $metaValue,
                'form_id'     => $formId,
            ]
        );
    }

    public static function persistArray($submissionId, $metaKey, $metaValue, $formId = null)
    {
        if (!$formId) {
            $formId = Submission::select('form_id')->where('id', $submissionId)->value('form_id');
        }

        // Try to fetch an existing record.
        $record = static::where('response_id', $submissionId)
                        ->where('meta_key', $metaKey)
                        ->first();

        if ($record) {
            $values = json_decode($record->value);
            $values[] = $metaValue;
            $record->value = json_encode($values);
            $record->save();
        } else {
            $metaValue = json_encode([$metaValue]);

            $record = static::create([
                'response_id' => $submissionId,
                'meta_key' => $metaKey,
                'value' => $metaValue,
                'form_id' => $formId,
            ]);
        }

        return $record;
    }
}
