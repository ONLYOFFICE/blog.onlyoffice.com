<?php

namespace FluentForm\App\Models;

use DateTimeInterface;
use FluentForm\Framework\Database\Orm\Model as BaseModel;

class Model extends BaseModel
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'ID'];

    /**
     * Serialize dates to Y-m-d H:i:s format for backward compatibility.
     *
     * The framework v2 defaults to ISO 8601 (e.g. 2026-03-03T08:54:40+00:00)
     * but existing JS code expects the simple Y-m-d H:i:s format.
     *
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Get the number of models to return per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        $request = wpFluentForm('request');

        return intval(
            $request->get('per_page', $request->get('perPage', $this->perPage))
        );
    }
}
