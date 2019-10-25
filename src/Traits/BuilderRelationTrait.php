<?php

namespace Swoft\Orm\Traits;

use Swoft\Db\Query\Builder;
use Swoft\Orm\Relation;
use Closure;
use Swoft\Orm\Exception\RelationException;
use Swoft\Stdlib\Collection;
use Swoft\Stdlib\Helper\Str;

trait BuilderRelationTrait
{
    /**
     * @var array $eagerLoad
     */
    protected $eagerLoad = [];

    /**
     * Applied global scopes.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * Removed global scopes.
     *
     * @var array
     */
    protected $removedScopes = [];
    /**
     * Eager load the relationships for the models.
     *
     * @param  array  $models
     * @return array
     */
    public function eagerLoadRelations(array $models)
    {
        foreach ($this->eagerLoad as $name => $constraints) {
            // For nested eager loads we'll skip loading them here and they will be set as an
            // eager load on the query to retrieve the relation so that they will be eager
            // loaded on that query, because that is where they get hydrated as models.
            if (strpos($name, '.') === false) {
                $models = $this->eagerLoadRelation($models, $name, $constraints);
            }
        }
        return $models;
    }

    /**
     * Eagerly load the relationship on a set of models.
     *
     * @param  array  $models mdoels array
     * @param  string  $name
     * @param  \Closure  $constraints
     * @return array
     */
    protected function eagerLoadRelation(array $models, $name, Closure $constraints)
    {
        // First we will "back up" the existing where conditions on the query so we can
        // add our eager constraints. Then we will merge the wheres that were on the
        // query back to it in order that any where conditions might be specified.
        /** @var Relation $relation */
        $relation = $this->getRelation($name);
        //add sql and get result
        $relation->addEagerConstraints($models);
        $constraints($relation->getQuery());
        $results = $relation->getEager();
        // Once we have the results, we just match those back up to their parent models
        // using the relationship instance. Then we just return the finished arrays
        // of models which have been eagerly hydrated and are readied for return.
        $models = $relation->initRelation($models, $name);
        return $relation->match($models, $results, $name);
    }

    /**
     * Get the relation instance for the given relation name.
     *
     * @param  string  $name
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function getRelation($name)
    {
        // We want to run a relationship query without any constrains so that we will
        // not have to remove these where clauses manually which gets really hacky
        // and error prone. We don't want constraints because we add eager ones.
        /** @var Relation $relation */
        $relation = Relation::noConstraints(function () use ($name) {
            return $this->getModel()->newInstance()->getAttributeRelationMethod($name);
        });

        $nested = $this->relationsNestedUnder($name);
        // If there are nested relationships set on the query, we will put those onto
        // the query instances so that they can be handled after this relationship
        // is loaded. In this way they will all trickle down as they are loaded.
        if (count($nested) > 0) {
            $relation->getQuery()->with($nested);
        }
        return $relation;
    }

    /**
     * Get the deeply nested relations for a given top-level relation.
     *
     * @param  string  $relation
     * @return array
     */
    protected function relationsNestedUnder($relation)
    {
        $nested = [];

        // We are basically looking for any relationships that are nested deeper than
        // the given top-level relationship. We will just check for any relations
        // that start with the given top relations and adds them to our arrays.
        foreach ($this->eagerLoad as $name => $constraints) {
            if ($this->isNestedUnder($relation, $name)) {
                $nested[substr($name, strlen($relation.'.'))] = $constraints;
            }
        }

        return $nested;
    }
    /**
     * Determine if the relationship is nested.
     *
     * @param  string  $relation
     * @param  string  $name
     * @return bool
     */
    protected function isNestedUnder($relation, $name)
    {
        return Str::contains($name, '.') && Str::startsWith($name, $relation.'.');
    }
    /**
     * Register a new global scope.
     *
     * @param  string  $identifier
     * @param  \Illuminate\Database\Eloquent\Scope|\Closure  $scope
     * @return $this
     */
    public function withGlobalScope($identifier, $scope)
    {
        $this->scopes[$identifier] = $scope;

        if (method_exists($scope, 'extend')) {
            $scope->extend($this);
        }

        return $this;
    }

    /**
     * Remove a registered global scope.
     *
     * @param  \Illuminate\Database\Eloquent\Scope|string  $scope
     * @return $this
     */
    public function withoutGlobalScope($scope)
    {
        if (! is_string($scope)) {
            $scope = get_class($scope);
        }

        unset($this->scopes[$scope]);

        $this->removedScopes[] = $scope;

        return $this;
    }

    /**
     * Remove all or passed registered global scopes.
     *
     * @param  array|null  $scopes
     * @return $this
     */
    public function withoutGlobalScopes(array $scopes = null)
    {
        if (! is_array($scopes)) {
            $scopes = array_keys($this->scopes);
        }

        foreach ($scopes as $scope) {
            $this->withoutGlobalScope($scope);
        }

        return $this;
    }

    /**
     * Get an array of global scopes that were removed from the query.
     *
     * @return array
     */
    public function removedScopes()
    {
        return $this->removedScopes;
    }

    /**
     * Set the relationships that should be eager loaded.
     *
     * @param  mixed  $relations
     * @return $this
     */
    public function with($relations)
    {
        $eagerLoad = $this->parseWithRelations(is_string($relations) ? func_get_args() : $relations);
        $this->eagerLoad = array_merge($this->eagerLoad, $eagerLoad);

        return $this;
    }

    /**
     * Prevent the specified relations from being eager loaded.
     *
     * @param  mixed  $relations
     * @return $this
     */
    public function without($relations)
    {
        $this->eagerLoad = array_diff_key($this->eagerLoad, array_flip(
            is_string($relations) ? func_get_args() : $relations
        ));

        return $this;
    }
    /**
     * Create a new instance of the model being queried.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function newModelInstance($attributes = [])
    {
        return $this->model->newInstance($attributes)->setConnection(
            $this->query->getConnection()->getName()
        );
    }

    /**
     * Parse a list of relations into individuals.
     *
     * @param  array  $relations
     * @return array
     */
    protected function parseWithRelations(array $relations)
    {
        $results = [];
        foreach ($relations as $name => $constraints) {
            // If the "name" value is a numeric key, we can assume that no
            // constraints have been specified. We'll just put an empty
            // Closure there, so that we can treat them all the same.
            if (is_numeric($name)) {
                $name = $constraints;

                [$name, $constraints] = Str::contains($name, ':')
                    ? $this->createSelectWithConstraint($name)
                    : [$name, function () {
                        //
                    }];
            }

            // We need to separate out any nested includes, which allows the developers
            // to load deep relationships using "dots" without stating each level of
            // the relationship with its own key in the array of eager-load names.
            $results = $this->addNestedWiths($name, $results);

            $results[$name] = $constraints;
        }

        return $results;
    }

    /**
     * Create a constraint to select the given columns for the relation.
     *
     * @param  string  $name
     * @return array
     */
    protected function createSelectWithConstraint($name)
    {
        return [explode(':', $name)[0], function ($query) use ($name) {
            $query->select(explode(',', explode(':', $name)[1]));
        }];
    }

    /**
     * Parse the nested relationships in a relation.
     *
     * @param  string  $name
     * @param  array  $results
     * @return array
     */
    protected function addNestedWiths($name, $results)
    {
        $progress = [];

        // If the relation has already been set on the result array, we will not set it
        // again, since that would override any constraints that were already placed
        // on the relationships. We will only set the ones that are not specified.
        foreach (explode('.', $name) as $segment) {
            $progress[] = $segment;

            if (! isset($results[$last = implode('.', $progress)])) {
                $results[$last] = function () {
                    //
                };
            }
        }

        return $results;
    }

    /**
     * Apply the given scope on the current builder instance.
     *
     * @param  callable  $scope
     * @param  array  $parameters
     * @return mixed
     */
    protected function callScope(callable $scope, $parameters = [])
    {
        array_unshift($parameters, $this);

        $query = $this->getQuery();

        // We will keep track of how many wheres are on the query before running the
        // scope so that we can properly group the added scope constraints in the
        // query as their own isolated nested where statement and avoid issues.
        $originalWhereCount = is_null($query->wheres)
            ? 0 : count($query->wheres);

        $result = $scope(...array_values($parameters)) ?? $this;

        if (count((array) $query->wheres) > $originalWhereCount) {
            $this->addNewWheresWithinGroup($query, $originalWhereCount);
        }

        return $result;
    }

    /**
     * Nest where conditions by slicing them at the given where count.
     *
     * @param  Builder  $query
     * @param  int  $originalWhereCount
     * @return void
     */
    protected function addNewWheresWithinGroup(Builder $query, $originalWhereCount)
    {
        // Here, we totally remove all of the where clauses since we are going to
        // rebuild them as nested queries by slicing the groups of wheres into
        // their own sections. This is to prevent any confusing logic order.
        $allWheres = $query->wheres;

        $query->wheres = [];

        $this->groupWhereSliceForScope(
            $query, array_slice($allWheres, 0, $originalWhereCount)
        );

        $this->groupWhereSliceForScope(
            $query, array_slice($allWheres, $originalWhereCount)
        );
    }

    /**
     * Slice where conditions at the given offset and add them to the query as a nested condition.
     *
     * @param  Builder  $query
     * @param  array  $whereSlice
     * @return void
     */
    protected function groupWhereSliceForScope(Builder $query, $whereSlice)
    {
        $whereBooleans = Collection::make($whereSlice)->pluck('boolean');

        // Here we'll check if the given subset of where clauses contains any "or"
        // booleans and in this case create a nested where expression. That way
        // we don't add any unnecessary nesting thus keeping the query clean.
        if ($whereBooleans->contains('or')) {
            $query->wheres[] = $this->createNestedWhere(
                $whereSlice, $whereBooleans->first()
            );
        } else {
            $query->wheres = array_merge($query->wheres, $whereSlice);
        }
    }

    /**
     * Create a where array with nested where conditions.
     *
     * @param  array  $whereSlice
     * @param  string  $boolean
     * @return array
     */
    protected function createNestedWhere($whereSlice, $boolean = 'and')
    {
        $whereGroup = $this->getQuery()->forNestedWhere();

        $whereGroup->wheres = $whereSlice;

        return ['type' => 'Nested', 'query' => $whereGroup, 'boolean' => $boolean];
    }
}
