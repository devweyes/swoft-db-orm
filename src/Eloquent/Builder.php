<?php declare(strict_types=1);

namespace Jcsp\Orm\Eloquent;

use Swoft\Db\Eloquent\Collection;
use Swoft\Db\Exception\DbException;
use Jcsp\Orm\Traits\BuilderRelationTrait;

class Builder extends \Swoft\Db\Eloquent\Builder
{
    //ORM builder relation trait
    use BuilderRelationTrait;
    /**
     * 重写swoft get方法用于ORM
     * Execute the query as a "select" statement.
     *
     * @param array $columns
     *
     * @return Collection
     * @throws DbException
     */
    public function get(array $columns = ['*']): Collection
    {
        $builder = $this;
        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded, which will solve the
        // n+1 query issue for the developers to avoid running a lot of queries.
        if (count($models = $builder->getModels($columns)) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }
        return $builder->getModel()->newCollection($models);
    }
}
