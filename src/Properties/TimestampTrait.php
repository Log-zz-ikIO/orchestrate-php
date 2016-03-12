<?php
namespace andrefelipe\Orchestrate\Properties;

/**
 * Trait that implements the Timestamp methods.
 *
 * @internal
 */
trait TimestampTrait
{
    /**
     * @var string|int
     */
    private $_timestamp = null;

    /**
     * Gets the event timestamp.
     *
     * Note that Orchestrate timestamps comes in milliseconds, so to convert
     * to a PHP date divide by 1000, for example:
     * date('Y-m-d', $event->getTimestamp()/1000)
     *
     * @param boolean $required
     *
     * @return string|int
     * @throws \BadMethodCallException if 'timestamp' is required but not set yet.
     */
    public function getTimestamp($required = false)
    {
        if ($required && !$this->_timestamp) {
            throw new \BadMethodCallException('There is no timestamp set yet. Do so through setTimestamp() method.');
        }

        return $this->_timestamp;
    }

    /**
     * The timestamp can be in any of the following formats:
     * https://orchestrate.io/docs/apiref#events-timestamps
     *
     * But for consistency, always set with the milliseconds since epoch.
     *
     * @param string|int $timestamp
     *
     * @return self
     * @link https://orchestrate.io/docs/apiref#events-timestamps
     */
    public function setTimestamp($timestamp)
    {
        $this->_timestamp = $timestamp;

        return $this;
    }

    /**
     * Helper method to set the timestamp without millisecond precision.
     *
     * @param string|int|DateTime $date Value must be either:
     *                                  (1) A valid format that strtotime understands;
     *                                  (2) A integer, that will be considered as seconds since epoch;
     *                                  (3) A DateTime object;
     *
     * @return self
     * @link http://php.net/manual/en/datetime.formats.php
     */
    public function setTimestampDate($date)
    {
        if ($date instanceof \DateTime) {
            $seconds = $date->getTimestamp();

        } elseif (is_numeric($date)) {
            $seconds = (int) $date;

        } else {
            $seconds = strtotime((string) $date);
        }

        $this->setTimestamp($seconds * 1000);

        return $this;
    }
}
