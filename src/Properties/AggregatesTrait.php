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
    private $_aggregates = [];

    /**
     * @return array
     */
    public function getAggregates()
    {
        $this->settlePromise();
        
        return $this->_aggregates;
    }
}
