<?php
namespace andrefelipe\Orchestrate\Properties;

use andrefelipe\Orchestrate\Exception\MissingPropertyException;

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
     * @throws MissingPropertyException if 'collection' is required but not set yet.
     */
    public function getCollection($required = false)
    {
        $this->settlePromise();

        if ($required && !$this->_collection) {
            throw new MissingPropertyException('collection', 'setCollection');
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
        $this->settlePromise();
        
        $this->_collection = $collection ? (string) $collection : null;

        return $this;
    }
}
