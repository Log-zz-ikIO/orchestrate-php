<?php
namespace andrefelipe\Orchestrate\Properties;

/**
 * Trait that implements the Key methods.
 *
 * @internal
 */
trait KeyTrait
{
    /**
     * @var string
     */
    private $_key = null;

    /**
     * @param boolean $required
     *
     * @return string
     * @throws \BadMethodCallException if 'key' is required but not set yet.
     */
    public function getKey($required = false)
    {
        if ($required && !$this->_key) {
            throw new \BadMethodCallException('There is no key set yet. Do so through setKey() method.');
        }

        return $this->_key;
    }

    /**
     * @param string $key
     *
     * @return self
     */
    public function setKey($key)
    {
        $this->_key = (string) $key;

        return $this;
    }
}
