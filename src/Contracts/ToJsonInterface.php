<?php
namespace andrefelipe\Orchestrate\Contracts;

/**
 * An object that can be represented as a JSON string.
 */
interface ToJsonInterface extends ToArrayInterface, \JsonSerializable
{
    /**
     * Get the JSON representation of an object. Uses the toArray output.
     * Exactly the same as using json_encode($keyvalue_item).
     *
     * @return string
     */
    public function toJson($options = 0, $depth = 512);

    /**
     * Serializes the object for json_encode
     *
     *<code>
     * echo json_encode($keyvalue_item);
     *</code>
     *
     * @return array
     */
    public function jsonSerialize();
}
