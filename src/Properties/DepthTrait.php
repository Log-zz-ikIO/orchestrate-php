<?php
namespace andrefelipe\Orchestrate\Properties;

use andrefelipe\Orchestrate\Exception\MissingPropertyException;

/**
 * Trait that implements the Relations' Depth methods.
 *
 * @internal
 */
trait DepthTrait
{
    /**
     * @var array
     */
    private $_depth = null;

    /**
     * @param boolean $required
     *
     * @return array
     * @throws MissingPropertyException if 'relation depth' is required but not set yet.
     */
    public function getDepth($required = false)
    {
        $this->settlePromise();

        if ($required && empty($this->_depth)) {
            throw new MissingPropertyException('relation depth', 'setDepth');
        }

        return $this->_depth;
    }

    /**
     * @param string|array $kind
     *
     * @return self
     */
    public function setDepth($kind)
    {
        $this->settlePromise();

        $this->_depth = (array) $kind;

        return $this;
    }
}
