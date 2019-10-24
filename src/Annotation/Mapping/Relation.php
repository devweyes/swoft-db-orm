<?php

namespace Swoft\Orm\Annotation\Mapping;

class Relation
{
    public const TYPE_HAS_ONE = 'hasOne';

    public const TYPE_HAS_MANY = 'hasMany';

    public const TYPE_BELONGS_TO = 'belongsTo';

    public const TYPE_BELONGS_TO_MANY = 'belongsToMany';
}
