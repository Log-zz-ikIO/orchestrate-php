<?php
namespace andrefelipe\Orchestrate\Contracts;

/**
 * Define the Relationship list minimum required interface.
 */
interface RelationshipsInterface extends ListInterface
{
    const KIND = 'relationships';

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
     * @param boolean $required
     *
     * @return array
     * @throws \BadMethodCallException if 'relation depth' is required but not set yet.
     */
    public function getDepth($required = false);

    /**
     * @param string|array $kind
     *
     * @return self
     */
    public function setDepth($kind);

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-get
     */
    public function get($limit = 10, $offset = 0)

    /**
     * @return array
     */
    public function getAggregates();

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-get
     */
    public function get($limit = 10, $offset = 0);

    /**
     * Search relationships. IMPORTANT this search is limited to relationships
     * only, even if you manage to inject other item kinds with the init method,
     * still this class will skip all items expect 'relationship'. Also the
     * current 'key' and 'relation' paths will be added to the query clause
     * as well, if they are set.
     *
     * @param string $query
     * @param string|array $sort
     * @param string|array $aggregate
     * @param int $limit
     * @param int $offset
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#search-events
     */
    public function search($query, $sort = null, $aggregate = null, $limit = 10, $offset = 0);
}
