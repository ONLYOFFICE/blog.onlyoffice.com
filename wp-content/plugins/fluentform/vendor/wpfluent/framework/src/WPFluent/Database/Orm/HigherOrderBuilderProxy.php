<?php

namespace FluentForm\Framework\Database\Orm;

/**
 * @mixin \FluentForm\Framework\Database\Orm\Builder
 */
class HigherOrderBuilderProxy
{
    /**
     * The collection being operated on.
     *
     * @var \FluentForm\Framework\Database\Orm\Builder
     */
    protected $builder;

    /**
     * The method being proxied.
     *
     * @var string
     */
    protected $method;

    /**
     * Create a new proxy instance.
     *
     * @param  \FluentForm\Framework\Database\Orm\Builder  $builder
     * @param  string  $method
     * @return void
     */
    public function __construct(Builder $builder, $method)
    {
        $this->method = $method;
        $this->builder = $builder;
    }

    /**
     * Proxy a scope call onto the query builder.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->builder->{$this->method}(function ($value) use ($method, $parameters) {
            return $value->{$method}(...$parameters);
        });
    }
}
