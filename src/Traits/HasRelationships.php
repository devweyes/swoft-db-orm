<?php

namespace Swoft\Orm\Traits;

use Swoft\Bean\BeanFactory;
use Swoft\Stdlib\Helper\Arr;
use Swoft\Stdlib\Helper\Str;
use Swoft\Db\Eloquent\Model;
use Swoft\Db\Eloquent\Builder;
use Swoft\Stdlib\Collection;
use Swoft\Orm\Relation\HasMany;
use Swoft\Orm\Relation\HasOne;
use Swoft\Orm\Relation\BelongsTo;
use Swoft\Orm\Relation\BelongsToMany;

trait HasRelationships
{
    /**
     * The loaded relationships for the model.
     *
     * @var array
     */
    protected $relations = [];

    /**
     * The relationships that should be touched on save.
     *
     * @var array
     */
    protected $touches = [];

    /**
     * The many to many relationship methods.
     *
     * @var array
     */
    public static $manyMethods = [
        'belongsToMany'
    ];

    /**
     * Define a one-to-one relationship.
     *
     * @param string $related
     * @param string $foreignKey
     * @param string $localKey
     * @return \Swoft\Orm\HasOne
     */
    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return $this->newHasOne($instance->newQuery(), $this, $instance->getTable() . '.' . $foreignKey, $localKey);
    }

    /**
     * Instantiate a new HasOne relationship.
     *
     * @param \Swoft\Db\Eloquent\Builder $query
     * @param \Swoft\Db\Eloquent\Model $parent
     * @param string $foreignKey
     * @param string $localKey
     * @return \Swoft\Orm\HasOne
     */
    protected function newHasOne(Builder $query, Model $parent, $foreignKey, $localKey)
    {
        return new HasOne($query, $parent, $foreignKey, $localKey);
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @param string $related
     * @param string $foreignKey
     * @param string $ownerKey
     * @param string $relation
     * @return \Swoft\Orm\BelongsTo
     */
    public function belongsTo($related, $relation, $foreignKey = null, $ownerKey = null)
    {
        $instance = $this->newRelatedInstance($related);
        // If no foreign key was supplied, we can use a backtrace to guess the proper
        // foreign key name by using the name of the relationship function, which
        // when combined with an "_id" should conventionally match the columns.

        $foreignKey = $foreignKey ?: Str::snake($relation) . '_' . $instance->getKeyName();

        // Once we have the foreign key names, we'll just create a new Eloquent query
        // for the related models and returns the relationship instance which will
        // actually be responsible for retrieving and hydrating every relations.
        $ownerKey = $ownerKey ?: $instance->getKeyName();
        return $this->newBelongsTo(
            $instance->query(),
            $this,
            $foreignKey,
            $ownerKey,
            $relation
        );
    }

    /**
     * Instantiate a new BelongsTo relationship.
     *
     * @param \Swoft\Db\Eloquent\Builder $query
     * @param \Swoft\Db\Eloquent\Model $child
     * @param string $foreignKey
     * @param string $ownerKey
     * @param string $relation
     * @return \Swoft\Orm\BelongsTo
     */
    protected function newBelongsTo(Builder $query, Model $child, $foreignKey, $ownerKey, $relation)
    {
        return new BelongsTo($query, $child, $foreignKey, $ownerKey, $relation);
    }

    /**
     * Define a one-to-many relationship.
     *
     * @param string $related
     * @param string $foreignKey
     * @param string $localKey
     * @return \Swoft\Orm\HasMany
     */
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return $this->newHasMany(
            $instance->newQuery(),
            $this,
            $instance->getTable() . '.' . $foreignKey,
            $localKey
        );
    }

    /**
     * Instantiate a new HasMany relationship.
     *
     * @param \Swoft\Db\Eloquent\Builder $query
     * @param \Swoft\Db\Eloquent\Model $parent
     * @param string $foreignKey
     * @param string $localKey
     * @return \Swoft\Orm\HasMany
     */
    protected function newHasMany(Builder $query, Model $parent, $foreignKey, $localKey)
    {
        return new HasMany($query, $parent, $foreignKey, $localKey);
    }

    /**
     * Define a many-to-many relationship.
     *
     * @param string $related
     * @param string $pointEntity
     * @param string $foreignPivotKey
     * @param string $relatedPivotKey
     * @param string $parentKey
     * @param string $relatedKey
     * @param string $relation
     * @return \Swoft\Orm\BelongsToMany
     */
    public function belongsToMany(
        $related,
        $pointEntity,
        $relation,
        $foreignPivotKey = null,
        $relatedPivotKey = null,
        $parentKey = null,
        $relatedKey = null
    )
    {
        // First, we'll need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we'll make the query
        // instances as well as the relationship instances we need for this.
        $instance = $this->newRelatedInstance($related);

        $pointEntity = $this->newRelatedInstance($pointEntity);

        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();

        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();


        return $this->newBelongsToMany(
            $instance->query(),
            $this,
            $pointEntity,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey ?: $this->getKeyName(),
            $relatedKey ?: $instance->getKeyName(),
            $relation
        );
    }

    /**
     * Instantiate a new BelongsToMany relationship.
     *
     * @param \Swoft\Db\Eloquent\Builder $query
     * @param \Swoft\Db\Eloquent\Model $parent
     * @param string $pointEntity
     * @param string $foreignPivotKey
     * @param string $relatedPivotKey
     * @param string $parentKey
     * @param string $relatedKey
     * @param string $relationName
     * @return \Swoft\Orm\BelongsToMany
     */
    protected function newBelongsToMany(
        Builder $query,
        Model $parent,
        Model $pointEntity,
        $foreignPivotKey,
        $relatedPivotKey,
        $parentKey,
        $relatedKey,
        $relationName = null
    )
    {
        return new BelongsToMany(
            $query,
            $parent,
            $pointEntity,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relationName
        );
    }

    /**
     * Get the joining table name for a many-to-many relation.
     *
     * @param string $related
     * @param \Swoft\Db\Eloquent\Model|null $instance
     * @return string
     */
    public function joiningTable($related, $instance = null)
    {
        // The joining table name, by convention, is simply the snake cased models
        // sorted alphabetically and concatenated with an underscore, so we can
        // just sort the models and join them together to get the table name.
        $segments = [
            $instance ? $instance->joiningTableSegment()
                : Str::snake(class_basename($related)),
            $this->joiningTableSegment(),
        ];

        // Now that we have the model names in an array we can just sort them and
        // use the implode function to join them together with an underscores,
        // which is typically used by convention within the database system.
        sort($segments);

        return strtolower(implode('_', $segments));
    }

    /**
     * Get this model's half of the intermediate table name for belongsToMany relationships.
     *
     * @return string
     */
    public function joiningTableSegment()
    {
        return Str::snake(class_basename($this));
    }

    /**
     * Determine if the model touches a given relation.
     *
     * @param string $relation
     * @return bool
     */
    public function touches($relation)
    {
        return in_array($relation, $this->touches);
    }

    /**
     * Touch the owning relations of the model.
     *
     * @return void
     */
    public function touchOwners()
    {
        foreach ($this->touches as $relation) {
            $this->$relation()->touch();

            if ($this->$relation instanceof self) {
                $this->$relation->fireModelEvent('saved', false);

                $this->$relation->touchOwners();
            } elseif ($this->$relation instanceof Collection) {
                $this->$relation->each(function (Model $relation) {
                    $relation->touchOwners();
                });
            }
        }
    }

    /**
     * Create a new model instance for a related model.
     *
     * @param string $class
     * @return Model
     */
    protected function newRelatedInstance($class)
    {
        return BeanFactory::getBean($class);
    }

    /**
     * Get all the loaded relations for the instance.
     *
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Get a specified relationship.
     *
     * @param string $relation
     * @return mixed
     */
    public function getRelation($relation)
    {
        return $this->relations[$relation];
    }

    /**
     * Determine if the given relation is loaded.
     *
     * @param string $key
     * @return bool
     */
    public function hasRelation($key)
    {
        return array_key_exists($key, $this->relations);
    }

    /**
     * Set the given relationship on the model.
     *
     * @param string $relation
     * @param mixed $value
     * @return $this
     */
    public function setRelation($relation, $value)
    {
        $this->relations[$relation] = $value;

        return $this;
    }

    /**
     * Unset a loaded relationship.
     *
     * @param string $relation
     * @return $this
     */
    public function unsetRelation($relation)
    {
        unset($this->relations[$relation]);

        return $this;
    }

    /**
     * Set the entire relations array on the model.
     *
     * @param array $relations
     * @return $this
     */
    public function setRelations(array $relations)
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * Get the relationships that are touched on save.
     *
     * @return array
     */
    public function getTouchedRelations()
    {
        return $this->touches;
    }

    /**
     * Set the relationships that are touched on save.
     *
     * @param array $touches
     * @return $this
     */
    public function setTouchedRelations(array $touches)
    {
        $this->touches = $touches;

        return $this;
    }
}
