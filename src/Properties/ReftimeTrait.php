<?php
namespace andrefelipe\Orchestrate\Properties;

/**
 * Trait that implements the Reftime methods.
 *
 * @internal
 */
trait ReftimeTrait
{
    /**
     * @var int
     */
    private $_reftime = null;

    /**
     * @return int
     */
    public function getReftime()
    {
        return $this->_reftime;
    }

    /**
     * @param int $value
     *
     * @return self
     */
    private function setReftime($value)
    {
        $this->_reftime = (int) $value;

        return $this;
    }
}
