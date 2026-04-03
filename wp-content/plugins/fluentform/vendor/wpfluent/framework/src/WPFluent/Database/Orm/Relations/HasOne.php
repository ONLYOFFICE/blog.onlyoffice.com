<?php

namespace FluentForm\Framework\Database\Orm\Relations;

use FluentForm\Framework\Database\Orm\Model;
use FluentForm\Framework\Database\Orm\Builder;
use FluentForm\Framework\Database\Orm\Collection;
use FluentForm\Framework\Database\Query\JoinClause;
use FluentForm\Framework\Database\Orm\Relations\Concerns\CanBeOneOfMany;
use FluentForm\Framework\Database\Orm\SupportsPartialRelations;
use FluentForm\Framework\Database\Orm\Relations\Concerns\ComparesRelatedModels;
use FluentForm\Framework\Database\Orm\Relations\Concerns\SupportsDefaultModels;

class HasOne extends HasOneOrMany implements SupportsPartialRelations
{
    use ComparesRelatedModels, CanBeOneOfMany, SupportsDefaultModels;

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        if (is_null($this->getParentKey())) {
            return $this->getDefaultFor($this->parent);
        }

        return $this->query->first() ?: $this->getDefaultFor($this->parent);
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array  $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->getDefaultFor($model));
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array  $models
     * @param  \FluentForm\Framework\Database\Orm\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        return $this->matchOne($models, $results, $relation);
    }

    /**
     * Add the constraints for an internal relationship existence query.
     *
     * Essentially, these queries compare on column names like "whereColumn".
     *
     * @param  \FluentForm\Framework\Database\Orm\Builder  $query
     * @param  \FluentForm\Framework\Database\Orm\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \FluentForm\Framework\Database\Orm\Builder
     */
    public function getRelationExistenceQuery(
        Builder $query,
        Builder $parentQuery,
        $columns = ['*']
    ) {
        if ($this->isOneOfMany()) {
            $this->mergeOneOfManyJoinsTo($query);
        }

        return parent::getRelationExistenceQuery($query, $parentQuery, $columns);
    }

    /**
     * Add constraints for inner join subselect for one of many relationships.
     *
     * @param  \FluentForm\Framework\Database\Orm\Builder  $query
     * @param  string|null  $column
     * @param  string|null  $aggregate
     * @return void
     */
    public function addOneOfManySubQueryConstraints(
        Builder $query, $column = null, $aggregate = null
    ) {
        $query->addSelect($this->foreignKey);
    }

    /**
     * Get the columns that should be selected by the one of many subquery.
     *
     * @return array|string
     */
    public function getOneOfManySubQuerySelectColumns()
    {
        return $this->foreignKey;
    }

    /**
     * Add join query constraints for one of many relationships.
     *
     * @param  \FluentForm\Framework\Database\Query\JoinClause  $join
     * @return void
     */
    public function addOneOfManyJoinSubQueryConstraints(JoinClause $join)
    {
        $join->on(
            $this->qualifySubSelectColumn($this->foreignKey),
            '=',
            $this->qualifyRelatedColumn($this->foreignKey)
        );
    }

    /**
     * Make a new related instance for the given model.
     *
     * @param  \FluentForm\Framework\Database\Orm\Model  $parent
     * @return \FluentForm\Framework\Database\Orm\Model
     */
    public function newRelatedInstanceFor(Model $parent)
    {
        return $this->related->newInstance()->setAttribute(
            $this->getForeignKeyName(), $parent->{$this->localKey}
        );
    }

    /**
     * Get the value of the model's foreign key.
     *
     * @param  \FluentForm\Framework\Database\Orm\Model  $model
     * @return mixed
     */
    protected function getRelatedKeyFrom(Model $model)
    {
        return $model->getAttribute($this->getForeignKeyName());
    }
}
