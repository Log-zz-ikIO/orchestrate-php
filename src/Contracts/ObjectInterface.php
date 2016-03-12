<?php
namespace andrefelipe\Orchestrate\Contracts;

/**
 * Defines the basis for all our objects. They should be:
 * - Accessible: Data can be acessed via object or array syntax, and easily
 * output via toArray and toJson and extracted with JmesPath.
 * - Serializable: Via PHP's or JSON formats.
 * - Reusable: We can reset and init the object without creating a new instance.
 */
interface ObjectInterface extends
\ArrayAccess,
\Serializable,
\JsonSerializable,
ToArrayInterface,
ToJsonInterface,
ConnectionInterface
{
    /**
     * Gets the kind of the current object. Value is immutable, it's the same as:
     * <code>
     * echo $my_object::KIND;
     * </code>
     *
     * This value matches the Orchestrate kind property for singular items
     * (KeyValue, Event and Relationship) and adds our list classes for internal
     * control (Application, Collection, Refs, Events and Relationships).
     *
     * @return string
     */
    public function getKind();

    /**
     * Resets the current instance to its initial state.
     */
    public function reset();

    /**
     * Single entry point to initialize the current instance and its properties.
     * Should be compatible with the toArray output.
     *
     * @param array $data
     *
     * @return self
     */
    public function init(array $data);

    /**
     * Use a JMESPath expression to model the data you need.
     *
     * @param string $expression
     *
     * @return array|null
     */
    public function extract($expression);
}
