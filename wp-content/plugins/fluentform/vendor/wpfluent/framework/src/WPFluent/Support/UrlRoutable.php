<?php

namespace FluentForm\Framework\Support;

interface UrlRoutable
{
    /**
     * Get the value of the model's route key.
     *
     * @return mixed
     */
    public function getRouteKey();

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName();

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \FluentForm\Framework\Database\Orm\Model|null
     */
    public function resolveRouteBinding($value, $field = null);
}
