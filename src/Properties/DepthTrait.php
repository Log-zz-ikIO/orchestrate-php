<?php
namespace andrefelipe\Orchestrate\Properties;

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
     * @throws \BadMethodCallException if 'relation depth' is required but not set yet.
     */
    public function getDepth($required = false)
    {
        if ($required && empty($this->_depth)) {
            throw new \BadMethodCallException('There is no relation depth set yet. Do so through setDepth() method.');
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
        $this->_depth = (array) $kind;

        return $this;
    }
}
