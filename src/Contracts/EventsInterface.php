<?php
namespace andrefelipe\Orchestrate\Contracts;

use andrefelipe\Orchestrate\Query\TimeRangeBuilder;

/**
 * Define the Events minimum required interface.
 */
interface EventsInterface extends ListInterface
{
    const KIND = 'events';

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
     * @return string
     * @throws \BadMethodCallException if 'type' is required but not set yet.
     */
    public function getType($required = false);

    /**
     * @param string $type
     *
     * @return self
     */
    public function setType($type);

    /**
     * @return array
     */
    public function getAggregates();

    /**
     * Gets a list of events in reverse chronological order,
     * specified by the limit and time range parameters.
     *
     * If there are more results available, the pagination URL can be checked with
     * getNextUrl/getPrevUrl, and queried with nextPage/prevPage methods.
     *
     * @param int $limit The limit of items to return. Defaults to 10 and max to 100.
     * @param TimeRangeBuilder $range
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#events-list
     */
    public function get($limit = 10, TimeRangeBuilder $range = null);

    /**
     * Search events. IMPORTANT this search is limited to events only, even if
     * you manage to inject other item kinds with the init method, still this
     * class will skip all items expect 'event'. Also the current 'type' and 'key'
     * will be added to the query clause as well, if they are set.
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
