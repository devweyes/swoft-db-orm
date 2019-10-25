<?php

namespace Swoft\Orm\Traits;

use Swoft\Db\Eloquent\Model;
use Swoft\Orm\Register\RelationRegister;
use Swoft\Orm\Relation\Relation;
use Swoft\Stdlib\Helper\Str;
use Swoft\Orm\Exception\RelationException;

trait HasAttributesRelation
{
    /**
     * BelongstoMany relations point
     * @var Model|null $pointAttr
     */
    protected $pointAttr;
    /**
     * get relation result
     * @param string $key
     * @return mixed
     * @throws RelationException
     */
    public function getRelationResults(string $key)
    {
        $relationCLass = $this->getAttributeRelationMethod($key);
        $relationCLass->addConstraints();
        return $relationCLass->getResults();
    }

    /**
     * set relation result
     * @param string $key
     * @return void
     * @throws RelationException
     */
    public function setRelationResults(string $key)
    {
        $relationCLass = $this->getAttributeRelationMethod($key);
        $relationCLass->addConstraints();
        $this->setRelationValue($key, $relationCLass->getResults());
    }

    /**
     * get relation class
     * @param string $key
     * @param array|null $relation
     * @return Relation
     * @throws RelationException
     */
    public function getAttributeRelationMethod(string $key, array $relation = null): Relation
    {
        if (!$relation) {
            $relation = $this->getAttributeRelationValue($key);
        }
        if (empty($relation)) {
            throw new RelationException(sprintf(
                '%s::%s undefined a relationship.',
                $this->getClassName(),
                $key
            ));
        }

        $args = $this->sortByRelationArgs($relation);
        $relationClass = $this->{$relation['type']}(...$args);
        if (!$relationClass instanceof Relation) {
            throw new RelationException(sprintf(
                '%s::%s must annotation relationship instance.',
                $this->getClassName(),
                $key
            ));
        }
        return $relationClass;
    }

    /**
     * set relation value
     * @param $key
     * @param $value
     * @throws RelationException
     */
    public function setRelationValue($key, $value)
    {
        $setter = sprintf('set%s', ucfirst(Str::camel($key)));
        if (!method_exists($this, $setter)) {
            throw new RelationException(sprintf(
                '%s::%s undefined method.',
                $this->getClassName(),
                $setter
            ));
        }
        $this->{$setter}($value);
    }

    /**
     * get relation value
     * @param $key
     * @return mixed
     * @throws RelationException
     */
    public function getRelationValue($key)
    {
        $getter = sprintf('get%s', ucfirst(Str::camel($key)));
        if (!method_exists($this, $getter)) {
            throw new RelationException(sprintf(
                '%s::%s undefined method.',
                $this->getClassName(),
                $setter
            ));
        }
        return $this->{$getter}();
    }

    /**
     * get relation register value
     * @param string $key
     * @return array
     */
    protected function getAttributeRelationValue(string $key): array
    {
        return RelationRegister::get($this->getClassName(), $key);
    }

    /*
     * get relation args
     * @param array $relation
     * @return array
     */
    protected function sortByRelationArgs(array $relation): array
    {
        if ($relation['type'] === 'hasOne') {
            return [
                $relation['foreignEntity'],
                $relation['key']['foreign'],
                $relation['key']['owner']
            ];
        }
        if ($relation['type'] === 'hasMany') {
            return [
                $relation['foreignEntity'],
                $relation['key']['foreign'],
                $relation['key']['owner']
            ];
        }
        if ($relation['type'] === 'belongsTo') {
            return [
                $relation['foreignEntity'],
                $relation['key']['relation'],
                $relation['key']['foreign'],
                $relation['key']['owner']
            ];
        }
        if ($relation['type'] === 'belongsToMany') {
            return [
                $relation['foreignEntity'],
                $relation['key']['pointEntity'],
                $relation['key']['relation'],
                $relation['key']['foreignPivot'],
                $relation['key']['ownerPivot'],
                $relation['key']['foreign'],
                $relation['key']['owner']
            ];
        }
        return null;
    }

    /**
     * Get an attribute from the model.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttributeAble($key)
    {
        if (!$key) {
            return null;
        }
        // If the attribute exists in the attribute array or has a "get" mutator we will
        // get the attribute's value. Otherwise, we will proceed as if the developers
        // are asking for a relationship's value. This covers both types of values.
        if (array_key_exists($key, $this->attributesToArray())) {
            return $this->getAttributeValue($key);
        }
        return null;
        // Here we will determine if the model base class itself contains this given key
        // since we don't want to treat any of those methods as relationships because
        // they are all intended as helper methods and none of these are relations.
        if (method_exists(self::class, $key)) {
            return null;
        }
        return $this->getRelationValueAble($key);
    }

    /**
     * Get a relationship.
     *
     * @param string $key
     * @return mixed
     */
    public function getRelationValueAble($key)
    {
        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->hasRelation($key)) {
            return $this->relations[$key];
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        return $this->getRelationshipFromMethod($key);
    }

    /**
     * Get a relationship value from a method.
     *
     * @param string $method
     * @return mixed
     *
     * @throws \LogicException
     */
    protected function getRelationshipFromMethod($key)
    {
        $relation = $this->getAttributeRelationMethod($key);
        return tap($relation->getResults(), function ($results) use ($key) {
            $this->setRelation($key, $results);
        });
    }

    /**
     * @return Model|null
     */
    public function getPointAttr()
    {
        return $this->pointAttr;
    }

    /**
     * @param Model $point
     */
    public function setPointAttr(Model $point)
    {
        $this->pointAttr = $point;
    }
    /**
     * Get the default foreign key name for the model.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return Str::snake($this->getClassName()).'_'.$this->getKeyName();
    }
}
