<?php
namespace andrefelipe\Orchestrate\Common;

/**
 * Trait implementing ToArrayInterface, \ArrayAccess, \Countable,
 * \IteratorAggregate, and some path style methods.
 */
trait ArrayAdapterTrait
{
    /** @var array */
    protected $data = [];

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function toArray()
    {
        return $this->data;
    }

    public function count()
    {
        return count($this->data);
    }
}
