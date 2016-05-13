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
        $this->settlePromise();

        return $this->_reftime;
    }

    /**
     * @param int $value
     *
     * @return self
     */
    protected function setReftime($value)
    {
        $this->settlePromise();

        $this->_reftime = (int) $value;

        return $this;
    }
}
