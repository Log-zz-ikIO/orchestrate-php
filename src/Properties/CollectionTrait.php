<?php
namespace andrefelipe\Orchestrate\Properties;

/**
 * Trait that implements the Collection methods.
 *
 * @internal
 */
trait CollectionTrait
{
    /**
     * @var string
     */
    private $_collection = null;

    /**
     * Get collection name.
     *
     * @param boolean $required
     *
     * @return null|string
     * @throws \BadMethodCallException if 'collection' is required but not set yet.
     */
    public function getCollection($required = false)
    {
        if ($required && !$this->_collection) {
            throw new \BadMethodCallException('There is no collection set yet. Do so through setCollection() method.');
        }

        return $this->_collection;
    }

    /**
     * Set collection name.
     *
     * @param null|string $collection
     *
     * @return self
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection ? (string) $collection : null;

        return $this;
    }
}
