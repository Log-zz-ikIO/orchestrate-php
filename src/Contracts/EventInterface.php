<?php
namespace andrefelipe\Orchestrate\Contracts;

/**
 * Define the Event minimum required interface.
 */
interface EventInterface extends ItemInterface
{
    const KIND = 'event';

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
     */
    public function getType($required = false);

    /**
     * @param string $type
     *
     * @return self
     */
    public function setType($type);

    /**
     * @param boolean $required
     *
     * @return int
     */
    public function getTimestamp($required = false);

    /**
     * @param int $timestamp
     *
     * @return self
     */
    public function setTimestamp($timestamp);

    /**
     * @param boolean $required
     *
     * @return int
     * @throws \BadMethodCallException if 'ordinal' is required but not set yet.
     */
    public function getOrdinal($required = false);

    /**
     * @param int $ordinal
     *
     * @return self
     */
    public function setOrdinal($ordinal);

    /**
     * @return string
     */
    public function getOrdinalStr();

    /**
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#events-get
     */
    public function get();

    /**
     * @param array $value
     * @param string $ref
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#events-put
     */
    public function put(array $value = null);

    /**
     * @param string $ref
     * @param array $value
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#events-put-conditional
     */
    public function putIf($ref = true, array $value = null);

    /**
     * @param array $value
     * @param int $timestamp
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#events-post
     */
    public function post(array $value = null, $timestamp = null);

    /**
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#events-delete
     */
    public function delete();

    /**
     * @param string $ref
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#events-delete-conditional
     */
    public function deleteIf($ref = true);
}
