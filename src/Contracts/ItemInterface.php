<?php
namespace andrefelipe\Orchestrate\Contracts;

use andrefelipe\Orchestrate\Exception\MissingPropertyException;

/**
 * Defines the basis for all the singular items (KeyValue, Event and Relationship):
 * - Value storage: They have a value body to store any ammount of properties.
 * - Accessible: Value can be easily be managed with get/set/merge/resetValue,
 * and through JMESPath with extractValue.
 * - Searchable: They can be part of search results.
 * - Uniquely identifiable: through Ref and Reftime.
 */
interface ItemInterface extends ObjectInterface
{
    /**
     * @param boolean $required
     *
     * @return string
     * @throws MissingPropertyException if 'ref' is required but not set yet.
     */
    public function getRef($required = false);

    /**
     * @param string $ref
     *
     * @return self
     */
    public function setRef($ref);

    /**
     * @return int
     */
    public function getReftime();

    /**
     * @return float
     */
    public function getScore();

    /**
     * @return float
     */
    public function getDistance();

    /**
     * Get item Path.
     *
     * @return array
     */
    public function getPath();

    /**
     * Get item Value.
     *
     * @return array
     */
    public function getValue();

    /**
     * Set item Value. Overwrites properties if already set.
     *
     * @param array $values
     */
    public function setValue(array $values);

    /**
     * Recursively merge one item Value into another.
     *
     * @param ItemInterface $object
     */
    public function mergeValue(ItemInterface $object);

    /**
     * Sets all public properties to null.
     */
    public function resetValue();

    /**
     * Use a JMESPath expression to model the data you need.
     *
     * @param string $expression
     *
     * @return array|null
     */
    public function extractValue($expression);

}
