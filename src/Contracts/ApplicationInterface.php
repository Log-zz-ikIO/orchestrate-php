<?php
namespace andrefelipe\Orchestrate\Contracts;

/**
 * Define the Application minimum required interface.
 */
interface ApplicationInterface extends ListInterface
{
    const KIND = 'application';

    /**
     * @return boolean
     * @link https://orchestrate.io/docs/apiref#authentication-ping
     */
    public function ping();

    /**
     * @param string $name Collection name
     *
     * @return Collection
     */
    public function collection($name);

    /**
     * Gets total item count of the entire Application.
     *
     * @return int|null Null on failure, item count on success.
     */
    public function getTotalItems();

    /**
     * Gets total event count of the entire Application.
     *
     * @param string $type Event type.
     *
     * @return int|null Null on failure, event count on success.
     */
    public function getTotalEvents($type = null);

    /**
     * Gets total relationship count of the entire Application.
     *
     * @param string $type Relation type.
     *
     * @return int|null Null on failure, item count on success.
     */
    public function getTotalRelationships($type = null);

    /**
     * Search!
     *
     * @param string $query
     * @param string|array $sort
     * @param string|array $aggregate
     * @param int $limit
     * @param int $offset
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#search-root
     */
    public function search($query, $sort = null, $aggregate = null, $limit = 10, $offset = 0);

    /**
     * @return array
     */
    public function getAggregates();
}
