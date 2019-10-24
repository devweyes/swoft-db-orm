<?php declare(strict_types=1);
/**
 * Define an inverse many-to-one  relationship.
 */

namespace Swoft\Orm\Annotation\Mapping;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * Class BelongsTo
 *
 * @Annotation
 * @Target("PROPERTY")
 * @Attributes({
 *     @Attribute("entity", type="string"),
 *     @Attribute("foreign", type="string"),
 *     @Attribute("owner", type="string"),
 *     @Attribute("relation", type="string"),
 * })
 *
 * @since 2.0
 */
final class BelongsTo
{
    /**
     * @var string
     */
    private $entity;
    /**
     * @var string
     */
    private $foreign = '';
    /**
     * @var string
     */
    private $owner = '';
    /**
     * @var string
     */
    private $relation = '';

    /**
     * Entity constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->entity = $values['value'];
        } elseif (isset($values['entity'])) {
            $this->entity = $values['entity'];
        }
        if (isset($values['foreign'])) {
            $this->foreign = $values['foreign'];
        }
        if (isset($values['owner'])) {
            $this->owner = $values['owner'];
        }
        if (isset($values['relation'])) {
            $this->relation = $values['relation'];
        }
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    public function getForeign(): string
    {
        return $this->foreign;
    }

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @return string
     */
    public function getRelation(): string
    {
        return $this->relation;
    }
}
