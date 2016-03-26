<?php
namespace andrefelipe\Orchestrate\Exception;

use andrefelipe\Orchestrate\Contracts\ConnectionInterface;
use GuzzleHttp\Promise\RejectionException;

/**
 * Exception thrown when request promise is rejected. Extends default
 * RejectionException to include the target object responsible for the request.
 */
class RejectedPromiseException extends RejectionException
{
    /**
     * @var ConnectionInterface
     */
    private $_target;

    public function __construct($reason, ConnectionInterface $target)
    {
        $this->_target = $target;
        parent::__construct($reason);
    }

    /**
     * Gets the target object responsible for the request.
     *
     * @return ConnectionInterface
     */
    public function getTarget()
    {
        return $this->_target;
    }
}
