<?php

namespace FluentForm\Framework\Database\Concerns;

use FluentForm\Framework\Support\Collection;

trait ExplainsQueries
{
    /**
     * Explains the query.
     *
     * @return \FluentForm\Framework\Support\Collection
     */
    public function explain()
    {
        $sql = $this->toSql();

        $bindings = $this->getBindings();

        $explanation = $this->getConnection()->select('EXPLAIN '.$sql, $bindings);

        return new Collection($explanation);
    }
}
