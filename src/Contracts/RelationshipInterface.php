<?php
namespace andrefelipe\Orchestrate\Contracts;

/**
 * Define the Relationship minimum required interface.
 */
interface RelationshipInterface extends ItemInterface
{
    const KIND = 'relationship';

    /**
     * @param boolean $required
     *
     * @return KeyValueInterface
     */
    public function getSource($required = false);

    /**
     * @param KeyValueInterface $item
     *
     * @return self
     */
    public function setSource(KeyValueInterface $item);

    /**
     * @param boolean $required
     *
     * @return KeyValueInterface
     * @throws \BadMethodCallException if 'destination' is required but not set yet.
     */
    public function getDestination($required = false);

    /**
     * @param KeyValueInterface $item
     *
     * @return self
     */
    public function setDestination(KeyValueInterface $item);

    /**
     * Get the relation kind between the objects.
     *
     * @param boolean $required
     *
     * @return string
     * @throws \BadMethodCallException if 'relation' is required but not set yet.
     */
    public function getRelation($required = false);

    /**
     * @param string $kind
     *
     * @return Relation self
     * @throws \InvalidArgumentException if 'kind' is array. Only one relation can be handled per time.
     */
    public function setRelation($kind);

    /**
     * Get the current relation value.
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-get
     */
    public function get();

    /**
     * Sets the relation between the two objects. This is an one-way
     * operation, only the relation from the source will be set,
     * to go both ways use the 'putBoth' method.
     *
     * @param array $value
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-put
     */
    public function put(array $value = null);

    /**
     * Sets the relation between the two objects if the current relationship on
     * Orchestrate matches this specific ref. This is an one-way
     * operation, only the relation from the source will be set,
     * to go both ways use the 'putBothIf' method.
     *
     * @param string $ref
     * @param array $value
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-put-conditional
     */
    public function putIf($ref = true, array $value = null);

    /**
     * Sets the relation between the two objects if there is no relationship
     * set yet. Mind that the check if the relation exist is made on this
     * current (source) object only. This is an one-way operation, only the
     * relation from the source will be set, to go both ways use the
     * 'putBothIfNone' method.
     *
     * @param array $value
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-put-conditional
     */
    public function putIfNone(array $value = null);

    /**
     * Sets the relation between the two objects, in both ways.
     * Two API calls are made concurrently.
     *
     * @param array $value
     * @param string $ref
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-put
     */
    public function putBoth(array $value = null);

    /**
     * Remove the relation between the two objects. This is an one-way
     * operation, only the relation from the source will be removed,
     * to go both ways use the 'deleteBoth' method.
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-delete
     */
    public function delete();

    /**
     * Remove the relation between the two objects, in both ways.
     * Two API calls are made concurrently.
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-delete
     */
    public function deleteBoth();

}
