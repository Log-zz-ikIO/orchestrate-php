<?php
namespace andrefelipe\Orchestrate\Objects;

/**
 * 
 */
interface ListInterface
{
    /**
     * Get the results of the list operation.
     *
     * @return ObjectArray
     */
    public function getResults();

    /**
     * Recursively merge one list results into another.
     * 
     * @param ListInterface $list
     */
    public function mergeResults(ListInterface $list);

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