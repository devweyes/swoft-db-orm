<?php declare(strict_types=1);
/**
 * Define an inverse one-to-many relationship parser.
 */

namespace Jcsp\Orm\Annotation\Parser;

use Swoft\Annotation\Annotation\Mapping\AnnotationParser;
use Swoft\Annotation\Annotation\Parser\Parser;
use Swoft\Annotation\Exception\AnnotationException;
use Swoft\Bean\Annotation\Mapping\Bean;
use Jcsp\Orm\Annotation\Mapping\HasMany;
use Jcsp\Orm\Annotation\Mapping\Relation;
use App\EntityRegister;
use Jcsp\Orm\Register\RelationRegister;

/**
 * Class EntityParser
 *
 * @AnnotationParser(HasMany::class)
 * @since 2.0
 * @package App\Relation\Parser
 */
class HasManyParser extends Parser
{
    /**
     * @param int $type
     * @param HasMany $annotationObject
     *
     * @return array
     */
    public function parse(int $type, $annotationObject): array
    {
        if ($type !== self::TYPE_PROPERTY) {
            throw new AnnotationException('Annotation HasMany shoud on property!');
        }
        $params = [
            'foreign' => $annotationObject->getForeign(),
            'owner' => $annotationObject->getOwner()
        ];

        RelationRegister::register(
            $this->className,
            $this->propertyName,
            Relation::TYPE_HAS_MANY,
            $annotationObject->getEntity(),
            $params
        );
        return [];
    }
}
