<?php declare(strict_types=1);

namespace Swoft\Orm\Eloquent;

use Swoft\Db\Exception\DbException;
use Swoft\Orm\Traits\HasAttributesRelation;
use Swoft\Orm\Traits\HasRelationships;

/**
 * Class Model
 *
 * @since 2.0
 * @method static hasOne($related, $foreignKey = null, $localKey = null)
 * @method static belongsTo($related, $relation, $foreignKey = null, $ownerKey = null)
 * @method static hasMany($related, $foreignKey = null, $localKey = null)
 * @method static belongsToMany($related,$pointEntity,$relation,$foreignPivotKey = null,$relatedPivotKey = null,$parentKey = null,$relatedKey = null)
 * @method static with($relations)
 * @method static withOut($relations)
 * @method static withCount($relations)
 */
class Model extends \Swoft\Db\Eloquent\Model
{
    //ORM relation trait
    use HasRelationships, HasAttributesRelation;
    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param QueryBuilder $query
     *
     * @return Builder
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }
    /**
     * Convert the model instance to an array.
     *
     * @return array
     * @throws DbException
     */
    public function toArray(): array
    {
        return array_merge($this->attributesToArray(), $this->relationsToArray());
    }
}
