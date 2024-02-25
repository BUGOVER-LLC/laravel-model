<?php

declare(strict_types=1);

namespace Service\Models\Custom;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

use function call_user_func;

class Custom extends Relation
{
    /**
     * Create new belongs to relationship instance.
     *
     * @param Builder $query
     * @param Model $parent
     * @param Closure $baseConstraints
     * @param Closure $eagerConstraints
     * @param Closure $eagerMatcher
     * @return void
     */
    public function __construct(
        Builder $query,
        Model $parent,
        protected Closure $baseConstraints,
        protected Closure $eagerConstraints,
        protected Closure $eagerMatcher
    )
    {
        parent::__construct($query, $parent);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        call_user_func($this->baseConstraints, $this);
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        call_user_func($this->eagerConstraints, $this, $models);
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param array $models
     * @param string $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param array $models
     * @param Collection $results
     * @param string $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation): array
    {
        return ($this->eagerMatcher)($models, $results, $relation, $this);
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults(): mixed
    {
        return $this->get();
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param array $columns
     * @return Collection
     */
    public function get($columns = ['*']): Collection
    {
        // First we'll add the proper select columns onto the query so it is run with
        // the proper columns. Then, we will get the results and hydrate out pivot
        // models with the result of those columns as a separate model relation.
        $columns = $this->query->getQuery()->columns ? [] : $columns;

        if (['*'] === $columns) {
            $columns = [$this->related->getTable() . '.*'];
        }

        $builder = $this->query->applyScopes();

        $models = $builder->addSelect($columns)->getModels();

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded. This will solve the
        // n + 1 query problem for the developer and also increase performance.
        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $this->related->newCollection($models);
    }
}
