<?php declare(strict_types=1);
/**
 * Define an inverse one-to-one  relationship.
 */

namespace Swoft\Orm\Annotation\Mapping;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * Class HasOne
 *
 * @Annotation
 * @Target("PROPERTY")
 * @Attributes({
 *     @Attribute("entity", type="string"),
 *     @Attribute("foreign", type="string"),
 *     @Attribute("owner", type="string")
 * })
 *
 * @since 2.0
 */
final class HasOne
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
}
