<?php
namespace andrefelipe\Orchestrate\Properties;

use andrefelipe\Orchestrate\Exception\MissingPropertyException;

/**
 * Trait that implements the relation kind methods.
 *
 * @internal
 */
trait RelationTrait
{
    /**
     * @var string
     */
    private $_relation = null;

    /**
     * Get the relation kind between the objects.
     *
     * @param boolean $required
     *
     * @return string
     * @throws MissingPropertyException if 'relation' is required but not set yet.
     */
    public function getRelation($required = false)
    {
        $this->settlePromise();

        if ($required && !$this->_relation) {
            throw new MissingPropertyException('relation', 'setRelation');
        }

        return $this->_relation;
    }

    /**
     * @param string $kind
     *
     * @return Relation self
     * @throws \InvalidArgumentException if 'kind' is array. Only one relation can be handled per time.
     */
    public function setRelation($kind)
    {
        $this->settlePromise();

        if (is_array($kind)) {
            throw new \InvalidArgumentException('The "kind" parameter can not be Array. Only one relation can be handled per time.');
        }

        $this->_relation = (string) $kind;

        return $this;
    }
}
