<?php
namespace andrefelipe\Orchestrate\Contracts;

use andrefelipe\Orchestrate\Query\PatchBuilder;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Define the KeyValue minimum required interface.
 */
interface KeyValueInterface extends ItemInterface
{
    const KIND = 'item';

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
     * @return boolean
     */
    public function isTombstone();

    /**
     * @param string $ref
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-get
     */
    public function get($ref = null);

    /**
     * @param string $ref
     *
     * @return PromiseInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-get
     */
    public function getAsync($ref = null);

    /**
     * @param array $value
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-put
     */
    public function put(array $value = null);

    /**
     * @param array $value
     *
     * @return PromiseInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-put
     */
    public function putAsync(array $value = null);

    /**
     * @param string $ref
     * @param array $value
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-put-conditional
     */
    public function putIf($ref = true, array $value = null);

    /**
     * @param string $ref
     * @param array $value
     *
     * @return PromiseInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-put-conditional
     */
    public function putIfAsync($ref = true, array $value = null);

    /**
     * @param array $value
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-put-conditional
     */
    public function putIfNone(array $value = null);

    /**
     * @param array $value
     *
     * @return PromiseInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-put-conditional
     */
    public function putIfNoneAsync(array $value = null);

    /**
     * @param array $value
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-post
     */
    public function post(array $value = null);

    /**
     * @param array $value
     *
     * @return PromiseInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-post
     */
    public function postAsync(array $value = null);

    /**
     * @param PatchBuilder $operations
     * @param boolean $reload
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch
     */
    public function patch(PatchBuilder $operations, $reload = false);

    /**
     * @param PatchBuilder $operations
     * @param boolean $reload
     *
     * @return PromiseInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch
     */
    public function patchAsync(PatchBuilder $operations, $reload = false);

    /**
     * @param string $ref
     * @param PatchBuilder $operations
     * @param boolean $reload
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch-conditional
     */
    public function patchIf($ref, PatchBuilder $operations, $reload = false);

    /**
     * @param string $ref
     * @param PatchBuilder $operations
     * @param boolean $reload
     *
     * @return PromiseInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch-conditional
     */
    public function patchIfAsync($ref, PatchBuilder $operations, $reload = false);

    /**
     * @param array $value
     * @param boolean $reload
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch-merge
     */
    public function patchMerge(array $value, $reload = false);

    /**
     * @param array $value
     * @param boolean $reload
     *
     * @return PromiseInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch-merge
     */
    public function patchMergeAsync(array $value, $reload = false);

    /**
     *
     * @param string $ref
     * @param array $value
     * @param boolean $reload
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch-merge-conditional
     */
    public function patchMergeIf($ref = true, array $value, $reload = false);

    /**
     *
     * @param string $ref
     * @param array $value
     * @param boolean $reload
     *
     * @return PromiseInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch-merge-conditional
     */
    public function patchMergeIfAsync($ref = true, array $value, $reload = false);

    /**
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function delete();

    /**
     *
     * @return PromiseInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function deleteAsync();

    /**
     * @param string $ref The specific ref to delete.
     * Pass true to read the ref value from the current object, via $item->getRef().
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function deleteIf($ref = true);

    /**
     * @param string $ref The specific ref to delete.
     * Pass true to read the ref value from the current object, via $item->getRef().
     *
     * @return PromiseInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function deleteIfAsync($ref = true);

    /**
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function purge();

    /**
     * @return PromiseInterface
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function purgeAsync();

    /**
     * @return Refs
     */
    public function refs();

    /**
     * @return Events
     */
    public function events($type = null);

    /**
     * @return Event
     */
    public function event($type = null, $timestamp = null, $ordinal = null);

    /**
     * @return Relationships
     */
    public function relationships($kind);

    /**
     * @return Relationship
     */
    public function relationship($kind, KeyValueInterface $destination);
}
