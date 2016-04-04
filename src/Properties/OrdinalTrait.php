<?php
namespace andrefelipe\Orchestrate\Properties;

use andrefelipe\Orchestrate\Exception\MissingPropertyException;

/**
 * Trait that implements the Ordinal methods.
 *
 * @internal
 */
trait OrdinalTrait
{
    /**
     * @var int
     */
    private $_ordinal = null;

    /**
     * @var string
     */
    private $_ordinalStr = null;

    /**
     * @param boolean $required
     *
     * @return int
     * @throws MissingPropertyException if 'ordinal' is required but not set yet.
     */
    public function getOrdinal($required = false)
    {
        if ($required && !$this->_ordinal) {
            throw new MissingPropertyException('ordinal', 'setOrdinal');
        }

        return $this->_ordinal;
    }

    /**
     * @param int $ordinal
     *
     * @return self
     */
    public function setOrdinal($ordinal)
    {
        $this->_ordinal = (int) $ordinal;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrdinalStr()
    {
        return $this->_ordinalStr;
    }

    /**
     * @param string $value
     *
     * @return self
     */
    private function setOrdinalStr($value)
    {
        $this->_ordinalStr = (string) $value;

        return $this;
    }
}
