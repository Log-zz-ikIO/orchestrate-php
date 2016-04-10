<?php
namespace andrefelipe\Orchestrate\Properties;

use andrefelipe\Orchestrate\Exception\MissingPropertyException;

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
     * @throws MissingPropertyException if 'key' is required but not set yet.
     */
    public function getKey($required = false)
    {
        $this->settlePromise();

        if ($required && !$this->_key) {
            throw new MissingPropertyException('key', 'setKey');
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
        $this->settlePromise();

        $this->_key = (string) $key;

        return $this;
    }
}
