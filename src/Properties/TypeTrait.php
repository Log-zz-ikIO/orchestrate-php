<?php
namespace andrefelipe\Orchestrate\Properties;

/**
 * Trait that implements the Type methods.
 *
 * @internal
 */
trait TypeTrait
{
    /**
     * @var string
     */
    private $_type = null;

    /**
     * @param boolean $required
     *
     * @return string
     * @throws \BadMethodCallException if 'type' is required but not set yet.
     */
    public function getType($required = false)
    {
        if ($required && !$this->_type) {
            throw new \BadMethodCallException('There is no type set yet. Do so through setType() method.');
        }

        return $this->_type;
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->_type = (string) $type;

        return $this;
    }
}
