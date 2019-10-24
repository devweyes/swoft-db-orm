<?php declare(strict_types=1);
/**
 * Define an inverse many-to-one relationship parser.
 */

namespace Swoft\Orm\Annotation\Parser;

use Swoft\Annotation\Annotation\Mapping\AnnotationParser;
use Swoft\Annotation\Annotation\Parser\Parser;
use Swoft\Annotation\Exception\AnnotationException;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Orm\Annotation\Mapping\BelongsTo;
use Swoft\Orm\Annotation\Mapping\Relation;
use Swoft\Orm\Register\RelationRegister;

/**
 * Class EntityParser
 *
 * @AnnotationParser(BelongsTo::class)
 * @since 2.0
 * @package App\Relation\Parser
 */
class BelongsToParser extends Parser
{
    /**
     * @param int $type
     * @param BelongsTo $annotationObject
     *
     * @return array
     */
    public function parse(int $type, $annotationObject): array
    {
        if ($type !== self::TYPE_PROPERTY) {
            throw new AnnotationException('Annotation BelongsTo shoud on property!');
        }
        $params = [
            'foreign' => $annotationObject->getForeign(),
            'owner' => $annotationObject->getOwner(),
            'relation' => $annotationObject->getRelation() ?: $this->propertyName
        ];

        RelationRegister::register(
            $this->className,
            $this->propertyName,
            Relation::TYPE_BELONGS_TO,
            $annotationObject->getEntity(),
            $params
        );
        return [];
    }
}
