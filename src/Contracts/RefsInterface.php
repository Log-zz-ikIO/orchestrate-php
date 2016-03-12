<?php
namespace andrefelipe\Orchestrate\Contracts;

/**
 * Define the Refs minimum required interface.
 */
interface RefsInterface extends ListInterface
{
    const KIND = 'refs';

    /**
     * @param boolean $required
     *
     * @return string
     */
    public function getCollection($required = false);

    /**
     * @param string $collection
     *
     * @return self
     */
    public function setCollection($collection);

    /**
     * @param boolean $required
     *
     * @return string
     */
    public function getKey($required = false);

    /**
     * @param string $key
     *
     * @return self
     */
    public function setKey($key);

    /**
     * @param int $limit
     * @param int $offset
     * @param boolean $values
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#refs-list
     */
    public function get($limit = 10, $offset = 0, $values = false);

}
