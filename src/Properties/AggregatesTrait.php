<?php
namespace andrefelipe\Orchestrate\Properties;

/**
 * Trait that implements the Aggregates methods.
 *
 * @internal
 */
trait AggregatesTrait
{
    /**
     * @var array
     */
    private $_aggregates;

    /**
     * @return array
     */
    public function getAggregates()
    {
        if (!$this->_aggregates) {
            $this->_aggregates = [];
        }
        return $this->_aggregates;
    }
}
