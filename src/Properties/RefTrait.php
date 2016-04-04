<?php
namespace andrefelipe\Orchestrate\Properties;

use andrefelipe\Orchestrate\Exception\MissingPropertyException;

/**
 * Trait that implements the Ref methods.
 *
 * @internal
 */
trait RefTrait
{
    /**
     * @var string
     */
    private $_ref = null;

    /**
     * @param boolean $required
     *
     * @return string
     * @throws MissingPropertyException if 'ref' is required but not set yet.
     */
    public function getRef($required = false)
    {
        if ($required && !$this->_ref) {
            throw new MissingPropertyException('ref', 'setRef');
        }

        return $this->_ref;
    }

    /**
     * @param string $ref
     *
     * @return self
     */
    public function setRef($ref)
    {
        $this->_ref = (string) $ref;

        return $this;
    }

    protected function setRefFromETag()
    {
        $etag = $this->getResponse()->getHeader('ETag');
        $this->_ref = !empty($etag) ? trim($etag[0], '"') : null;
    }

    /**
     * @param mixed $ref
     *
     * @return string
     * @throws MissingPropertyException if 'ref' is not set yet.
     */
    protected function getValidRef($ref = true)
    {
        if ($ref === true) {
            $ref = $this->getRef(true);
        }
        if (empty($ref) || !is_string($ref)) {
            throw new MissingPropertyException('ref');
        }
        return $ref;
    }
}
