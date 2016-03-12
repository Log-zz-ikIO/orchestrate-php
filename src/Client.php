<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Query\KeyRangeBuilder;
use andrefelipe\Orchestrate\Query\PatchBuilder;
use andrefelipe\Orchestrate\Query\TimeRangeBuilder;
use GuzzleHttp\ClientInterface;

/**
 * Client interface for Orchestrate API.
 *
 * @link https://orchestrate.io/docs/apiref
 */
class Client
{
    /**
     * If you provide any parameters if will instantiate a HTTP client on construction.
     * Otherwise it will create one when required.
     *
     * @param string $apiKey Orchestrate API key. If not set gets from env 'ORCHESTRATE_API_KEY'.
     * @param string $host Orchestrate API host. Defaults to 'https://api.orchestrate.io'
     * @param string $version Orchestrate API version. Defaults to 'v0'
     */
    public function __construct($apiKey = null, $host = null, $version = null)
    {
        // lazily instantiante
        if ($apiKey || $host || $version) {
            $client = default_http_client($apiKey, $host, $version);
            $this->setHttpClient($client);
        }
    }

    /**
     * @var ClientInterface
     */
    private $_httpClient;

    public function getHttpClient()
    {
        if (!$this->_httpClient) {
            $this->_httpClient = default_http_client();
        }

        return $this->_httpClient;
    }

    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->_httpClient = $httpClient;

        return $this;
    }

    /**
     * @return boolean
     * @link https://orchestrate.io/docs/apiref#authentication-ping
     */
    public function ping()
    {
        // TODO add try catch Exception for timeouts
        return $this->getHttpClient()->request('HEAD')->getStatusCode() === 200;
    }

    // Collection

    /**
     * Deletes a collection. Warning this will permanently erase all data within
     * this collection and cannot be reversed!
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#collections-delete
     */
    public function deleteCollection($collection)
    {
        // TODO add try catch Exception
        $response = $this->getHttpClient()->request(
            'DELETE',
            $collection,
            ['query' => ['force' => 'true']]
        );

        return $response->getStatusCode() === 204;
    }

    /**
     * @param string $collection
     * @param int $limit
     * @param KeyRangeBuilder $range
     *
     * @return Collection
     * @link https://orchestrate.io/docs/apiref#keyvalue-list
     */
    public function listCollection(
                        $collection,
                        $limit = 10,
        KeyRangeBuilder $range = null
    ) {
        $list = (new Collection($collection))
            ->setHttpClient($this->getHttpClient());

        $list->get($limit, $range);
        return $list;
    }

    /**
     * @param string $collection
     * @param string $query
     * @param string|array $sort
     * @param string|array $aggregate
     * @param int $limit
     * @param int $offset
     *
     * @return Collection
     * @link https://orchestrate.io/docs/apiref#search-collection
     */
    public function search(
        $collection,
        $query,
        $sort = null,
        $aggregate = null,
        $limit = 10,
        $offset = 0
    ) {
        $list = (new Collection($collection))
            ->setHttpClient($this->getHttpClient());

        $list->search($query, $sort, $aggregate, $limit, $offset);
        return $list;
    }

    /**
     * @param string $query
     * @param string|array $sort
     * @param string|array $aggregate
     * @param int $limit
     * @param int $offset
     *
     * @return Application
     * @link https://orchestrate.io/docs/apiref#search-root
     */
    public function rootSearch(
        $query,
        $sort = null,
        $aggregate = null,
        $limit = 10,
        $offset = 0
    ) {
        $list = (new Application())
            ->setHttpClient($this->getHttpClient());

        $list->search($query, $sort, $aggregate, $limit, $offset);
        return $list;
    }

    // Key/Value

    /**
     * @param string $collection
     * @param string $key
     * @param string $ref
     *
     * @return KeyValueInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-get
     */
    public function get($collection, $key, $ref = null)
    {
        $item = $this->newItem($collection, $key);

        $item->get($ref);
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param array $value
     * @param string $ref
     *
     * @return KeyValueInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-put
     */
    public function put($collection, $key, array $value, $ref = null)
    {
        $item = $this->newItem($collection, $key);

        if ($ref) {
            $item->putIf($ref, $value);
        } elseif ($ref === false) {
            $item->putIfNone($value);
        } else {
            $item->put($value);
        }

        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param PatchBuilder $operations
     * @param string $ref
     * @param boolean $reload
     *
     * @return KeyValueInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch
     */
    public function patch(
                     $collection,
                     $key,
        PatchBuilder $operations,
                     $ref = null,
                     $reload = false
    ) {
        $item = $this->newItem($collection, $key);

        if ($ref) {
            $item->patchIf($ref, $operations, $reload);
        } else {
            $item->patch($operations, $reload);
        }

        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param array $value
     * @param string $ref
     * @param boolean $reload
     *
     * @return KeyValueInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch-merge
     */
    public function patchMerge(
              $collection,
              $key,
        array $value,
              $ref = null,
              $reload = false
    ) {
        $item = $this->newItem($collection, $key);

        if ($ref) {
            $item->patchMergeIf($ref, $value, $reload);
        } else {
            $item->patchMerge($value, $reload);
        }

        return $item;
    }

    /**
     * @param string $collection
     * @param array $value
     *
     * @return KeyValueInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-post
     */
    public function post($collection, array $value)
    {
        $item = $this->newItem($collection);

        $item->post($value);
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $ref
     *
     * @return KeyValueInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function delete($collection, $key, $ref = null)
    {
        $item = $this->newItem($collection, $key);

        if ($ref) {
            $item->deleteIf($ref);
        } else {
            $item->delete();
        }

        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     *
     * @return KeyValueInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function purge($collection, $key)
    {
        $item = $this->newItem($collection, $key);

        $item->purge();
        return $item;
    }

    // Refs

    /**
     * @param string $collection
     * @param string $key
     * @param int $limit
     * @param int $offset
     * @param boolean $values
     *
     * @return Refs
     * @link https://orchestrate.io/docs/apiref#refs-list
     */
    public function listRefs(
        $collection,
        $key,
        $limit = 10,
        $offset = 0,
        $values = false
    ) {
        $list = (new Refs($collection, $key))
            ->setHttpClient($this->getHttpClient());

        $list->get($limit, $offset, $values);
        return $list;
    }

    // Events

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     *
     * @return EventInterface
     * @link https://orchestrate.io/docs/apiref#events-get
     */
    public function getEvent($collection, $key, $type, $timestamp, $ordinal)
    {
        $item = newEvent($collection, $key, $type, $timestamp, $ordinal);

        $item->get();
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     * @param array $value
     * @param string $ref
     *
     * @return EventInterface
     * @link https://orchestrate.io/docs/apiref#events-put
     */
    public function putEvent(
              $collection,
              $key,
              $type,
              $timestamp,
              $ordinal,
        array $value,
              $ref = null
    ) {
        $item = newEvent($collection, $key, $type, $timestamp, $ordinal);

        if ($ref) {
            $item->putIf($ref, $value);
        } else {
            $item->put($value);
        }

        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param array $value
     * @param int $timestamp
     *
     * @return EventInterface
     * @link https://orchestrate.io/docs/apiref#events-post
     */
    public function postEvent(
              $collection,
              $key,
              $type,
        array $value,
              $timestamp = null
    ) {
        $item = newEvent($collection, $key, $type);

        $item->post($value, $timestamp);
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     * @param string $ref
     *
     * @return EventInterface
     * @link https://orchestrate.io/docs/apiref#events-delete
     */
    public function deleteEvent(
        $collection,
        $key,
        $type,
        $timestamp,
        $ordinal,
        $ref = null
    ) {
        $item = newEvent($collection, $key, $type, $timestamp, $ordinal);

        if ($ref) {
            $item->deleteIf($ref, $value);
        } else {
            $item->delete($value);
        }

        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     *
     * @return EventInterface
     * @link https://orchestrate.io/docs/apiref#events-delete
     */
    public function purgeEvent($collection, $key, $type, $timestamp, $ordinal)
    {
        $item = newEvent($collection, $key, $type, $timestamp, $ordinal);

        $item->purge();
        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $limit
     * @param TimeRangeBuilder $range
     *
     * @return Events
     * @link https://orchestrate.io/docs/apiref#events-list
     */
    public function listEvents(
                         $collection,
                         $key,
                         $type,
                         $limit = 10,
        TimeRangeBuilder $range = null
    ) {
        $events = (new Events($collection))
            ->setKey($key)
            ->setType($type)
            ->setHttpClient($this->getHttpClient());

        $events->get($limit, $range);
        return $events;
    }

    // Graph

    /**
     * @param string $collection
     * @param string $key
     * @param string $kind
     * @param string $toCollection
     * @param string $toKey
     *
     * @return Relationship
     * @link https://orchestrate.io/docs/apiref#graph-get
     */
    public function getRelationship(
        $collection,
        $key,
        $kind,
        $toCollection,
        $toKey
    ) {
        $source = $this->newItem($collection, $key);
        $destination = $this->newItem($toCollection, $toKey);

        $relation = new Relationship($source, $kind, $destination);
        $relation->get();

        return $relation;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $kind
     * @param string $toCollection
     * @param string $toKey
     * @param array $value
     * @param string $ref
     * @param boolean $both_ways
     *
     * @return Relationship
     * @link https://orchestrate.io/docs/apiref#graph-put
     */
    public function putRelationship(
              $collection,
              $key,
              $kind,
              $toCollection,
              $toKey,
        array $value = null,
              $ref = null,
              $both_ways = false
    ) {
        $source = $this->newItem($collection, $key);
        $destination = $this->newItem($toCollection, $toKey);

        $item = new Relationship($source, $kind, $destination);

        if ($ref) {
            if ($both_ways) {
                $item->putIfBoth($value);
            } else {
                $item->putIf($value);
            }
        } elseif ($ref === false) {
            if ($both_ways) {
                $item->putIfNoneBoth($value);
            } else {
                $item->putIfNone($value);
            }
        } else {
            if ($both_ways) {
                $item->putBoth($value);
            } else {
                $item->put($value);
            }
        }

        return $item;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string $kind
     * @param string $toCollection
     * @param string $toKey
     * @param boolean $both_ways
     *
     * @return Relationship
     * @link https://orchestrate.io/docs/apiref#graph-delete
     */
    public function deleteRelationship(
        $collection,
        $key,
        $kind,
        $toCollection,
        $toKey,
        $both_ways = false
    ) {
        $source = $this->newItem($collection, $key);
        $destination = $this->newItem($toCollection, $toKey);

        $relation = new Relationship($source, $kind, $destination);

        if ($both_ways) {
            $relation->deleteBoth();
        } else {
            $relation->delete();
        }

        return $relation;
    }

    /**
     * @param string $collection
     * @param string $key
     * @param string|array $kind
     * @param int $limit
     * @param int $offset
     *
     * @return Relationships
     * @link https://orchestrate.io/docs/apiref#graph-get
     */
    public function listRelationships(
        $collection,
        $key,
        $kind,
        $limit = 10,
        $offset = 0
    ) {
        $list = (new Relationships($collection, $key, $kind))
            ->setHttpClient($this->getHttpClient());

        $list->get($limit, $offset);
        return $list;
    }

    /**
     * Helper to create KeyValue instances.
     *
     * @param string $collection
     * @param string $key
     * @param string $ref
     *
     * @return KeyValueInterface
     */
    private function newItem($collection = null, $key = null, $ref = null)
    {
        return (new KeyValue())
            ->setCollection($collection)
            ->setKey($key)
            ->setRef($ref)
            ->setHttpClient($this->getHttpClient());
    }

    /**
     * Helper to create Event instances.
     *
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     *
     * @return EventInterface
     */
    private function newEvent(
        $collection = null,
        $key = null,
        $type = null,
        $timestamp = null,
        $ordinal = null
    ) {
        return (new Event())
            ->setCollection($collection)
            ->setKey($key)
            ->setType($type)
            ->setTimestamp($timestamp)
            ->setOrdinal($ordinal)
            ->setHttpClient($this->getHttpClient());
    }
}
