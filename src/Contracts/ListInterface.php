<?php
namespace andrefelipe\Orchestrate\Contracts;

/**
 *
 */
interface ListInterface extends
\IteratorAggregate,
\Countable,
ObjectInterface
{
    /**
     * Get the results of the list operation.
     *
     * @return array
     */
    public function getResults();

    /**
     * Recursively merge one list results into another.
     *
     * @param ListInterface $list
     */
    public function mergeResults(ListInterface $list);

    /**
     * Use a JMESPath expression to model the data you need.
     *
     * @return array|null
     */
    public function extractValues($expression);

    /**
     * @return int
     */
    public function getTotalCount();

    /**
     * @return string
     */
    public function getNextUrl();

    /**
     * @return string
     */
    public function getPrevUrl();

    /**
     * @return boolean Success of operation.
     */
    public function nextPage();

    /**
     * @return boolean Success of operation.
     */
    public function prevPage();
}
