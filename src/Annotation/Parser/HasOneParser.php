<?php declare(strict_types=1);
/**
 * Define an inverse one-to-one relationship parser.
 */
namespace Swoft\Orm\Annotation\Parser;

use Swoft\Annotation\Annotation\Mapping\AnnotationParser;
use Swoft\Annotation\Annotation\Parser\Parser;
use Swoft\Annotation\Exception\AnnotationException;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Orm\Annotation\Mapping\HasOne;
use Swoft\Orm\Annotation\Mapping\Relation;
use Swoft\Db\Eloquent\Model;
use Swoft\Orm\Register\RelationRegister;

/**
 * Class HasOneParser
 *
 * @AnnotationParser(HasOne::class)
 * @since 2.0
 * @package App\Relation\Parser
 */
class HasOneParser extends Parser
{
    /**
     * @param int $type
     * @param HasOne $annotationObject
     *
     * @return array
     */
    public function parse(int $type, $annotationObject): array
    {
        if ($type !== self::TYPE_PROPERTY) {
            throw new AnnotationException('Annotation HasOne shoud on property!');
        }
        $params = [
            'foreign' => $annotationObject->getForeign(),
            'owner' => $annotationObject->getOwner()
        ];
        RelationRegister::register(
            $this->className,
            $this->propertyName,
            Relation::TYPE_HAS_ONE,
            $annotationObject->getEntity(),
            $params
        );
        return [];
    }
}
