<?php declare(strict_types=1);

namespace Swoft\Orm\Register;

/**
 * Class RelationRegister
 *
 * @since 2.0
 */
class RelationRegister
{
    /**
     * Relation array
     *
     * @var array
     *
     * @example
     * [
     *     'entityClassName' => [
     *          'property' => [
     *              'property' => 'property',
     *              'foreignEntity' => 'foreignEntity',
     *              'type' => 'hasOne',
     *              'key' => [
     *                  'foreign' => 'foreign',
     *                  'owner' => 'owner',
     *                  'foreignPivot' => 'foreignPivot',   //only in BelongsToMany
     *                  'ownerPivot' => 'ownerPivot',        //only in BelongsToMany
     *                  'table' => 'table',                 //only in BelongsToMany
     *                  'relation' => 'relation',           //only in BelongsTo and BelongsToMany
     *              ]
     *          ]
     *     ]
     * ]
     */
    private static $relation = [];

    /**
     * Register relation
     * @param string $className
     * @param string $propertyName
     * @param string $type
     * @param string $foreignEntity
     * @param array $keys
     */
    public static function register(
        string $className,
        string $propertyName,
        string $type,
        string $foreignEntity,
        array $keys
    ): void {
        self::$relation[$className][$propertyName] = [
            'property' => $propertyName,
            'type' => $type,
            'foreignEntity' => $foreignEntity,
            'key' => $keys
        ];
    }

    /**
     * has relation
     * @param string $className
     * @param string $propertyName
     * @return bool
     */
    public static function has(string $className, string $propertyName = null)
    {
        if ($propertyName) {
            return !empty(self::$relation[$className][$propertyName]);
        }
        if (!$propertyName) {
            return !empty(self::$relation[$className]);
        }
    }

    /**
     * get relation
     * @return array
     */
    public static function get(string $className, string $propertyName = null)
    {
        if ($propertyName) {
            return self::$relation[$className][$propertyName] ?? [];
        }
        if (!$propertyName) {
            return self::$relation[$className] ?? [];
        }
    }
}
