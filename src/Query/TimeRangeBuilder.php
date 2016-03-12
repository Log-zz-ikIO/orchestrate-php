<?php
namespace andrefelipe\Orchestrate\Query;

use andrefelipe\Orchestrate\Contracts\ToArrayInterface;
use andrefelipe\Orchestrate\Contracts\EventInterface;

/**
 *
 * @link https://orchestrate.io/docs/apiref#events-list
 */
class TimeRangeBuilder implements ToArrayInterface
{
    /**
     * @var array
     */
    protected $range = [];

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->range;
    }

    /**
     * The start of the time range to paginate from.
     * Include or exclude events of the specified start time, if it exists,
     * using the 'inclusive' parameter.
     *
     * Note that events are queried in reverse chronological order
     * (from newest to oldest)
     *
     * @param string|EventInterface $event The start 'event' to paginate from. Value must be either:
     *                                  (1) A correctly formatted timestamp per Orchestrate specification;
     *                                  (2) An Event object;
     * @param boolean $inclusive Include event of the specified time, it it exists. Defaults to true.
     *
     * @return TimeRangeBuilder self
     * @link https://orchestrate.io/docs/apiref#events-timestamps
     */
    public function from($event, $inclusive = true)
    {
        // cleanup - both start ranges can not coexist
        unset($this->range['startEvent']);
        unset($this->range['afterEvent']);

        // set
        if ($event instanceof EventInterface) {
            $event = $event->getTimestamp(true).($event->getOrdinal() ? '/'.$event->getOrdinal() : '');
        }

        $this->range[($inclusive ? 'start' : 'after').'Event'] = $event;

        return $this;
    }

    /**
     * The start of the time range to paginate from.
     * Include or exclude events of the specified start time, if it exists,
     * using the 'inclusive' parameter.
     *
     * Note that events are queried in reverse chronological order
     * (from newest to oldest)
     *
     * @param string|int|DateTime $date The start date to paginate from. Value must be either:
     *                                  (1) A valid format that strtotime understands;
     *                                  (2) A integer, that will be considered as seconds since epoch;
     *                                  (3) A DateTime object;
     * @param boolean $inclusive Include event of specified time, it it exists. Defaults to true.
     *
     * @return TimeRangeBuilder self
     * @link http://php.net/manual/en/datetime.formats.php
     */
    public function fromDate($date, $inclusive = true)
    {
        // cleanup - both start ranges can not coexist
        unset($this->range['startEvent']);
        unset($this->range['afterEvent']);

        // set
        $this->range[($inclusive ? 'start' : 'after').'Event'] = $this->toEventTime($date);

        return $this;
    }

    /**
     * The end of the time range to paginate to.
     * Include or exclude events of the specified end time, if it exists,
     * using the 'inclusive' parameter.
     *
     * Note that events are queried in reverse chronological order
     * (from newest to oldest)
     *
     * @param string|EventInterface $event The end 'event' to paginate to. Value must be either:
     *                                  (1) A correctly formatted timestamp per Orchestrate specification;
     *                                  (2) An Event object;
     * @param boolean $inclusive Include event of specified time, it it exists. Defaults to true.
     *
     * @return TimeRangeBuilder self
     * @link https://orchestrate.io/docs/apiref#events-timestamps
     */
    public function to($event, $inclusive = true)
    {
        // cleanup - both end ranges can not coexist
        unset($this->range['endEvent']);
        unset($this->range['beforeEvent']);

        // set
        if ($event instanceof EventInterface) {
            $event = $event->getTimestamp(true).($event->getOrdinal() ? '/'.$event->getOrdinal() : '');
        }
        $this->range[($inclusive ? 'end' : 'before').'Event'] = $event;

        return $this;
    }

    /**
     * The end of the time range to paginate to.
     * Include or exclude events of the specified end time, if it exists,
     * using the 'inclusive' parameter.
     *
     * Note that events are queried in reverse chronological order
     * (from newest to oldest)
     *
     * @param string|int|DateTime $date The end date to paginate to. Value must be either:
     *                                  (1) A valid format that strtotime understands;
     *                                  (2) A integer, that will be considered as seconds since epoch;
     *                                  (3) A DateTime object;
     * @param boolean $inclusive Include event of specified time, it it exists. Defaults to true.
     *
     * @return TimeRangeBuilder self
     * @link http://php.net/manual/en/datetime.formats.php
     */
    public function toDate($date, $inclusive = true)
    {
        // cleanup - both end ranges can not coexist
        unset($this->range['endEvent']);
        unset($this->range['beforeEvent']);

        // set
        $this->range[($inclusive ? 'end' : 'before').'Event'] = $this->toEventTime($date);

        return $this;
    }

    /**
     * Wraps both 'from' and 'to' methods in a single call.
     * Include or exclude events at the edges of the time range specified,
     * if they exist, using the 'inclusive' parameter.
     *
     * @param string|EventInterface $fromEvent The start event to paginate from.
     * @param string|EventInterface $toEvent The end event to paginate to.
     * @param boolean $inclusive Include the specified keys, it they exist. Defaults to true.
     *
     * @return TimeRangeBuilder self
     * @link https://orchestrate.io/docs/apiref#events-timestamps
     */
    public function between($fromEvent, $toEvent, $inclusive = true)
    {
        $this->from($fromEvent, $inclusive);
        $this->to($toEvent, $inclusive);

        return $this;
    }

    /**
     * Wraps both 'from' and 'to' methods in a single call.
     * Include or exclude events at the edges of the time range specified,
     * if they exist, using the 'inclusive' parameter.
     *
     * @param string|EventInterface $fromEvent The start date to paginate from.
     * @param string|EventInterface $toEvent The end date to paginate to.
     * @param boolean $inclusive Include the specified keys, it they exist. Defaults to true.
     *
     * @return TimeRangeBuilder self
     * @link https://orchestrate.io/docs/apiref#events-timestamps
     */
    public function betweenDates($fromDate, $toDate, $inclusive = true)
    {
        $this->fromDate($fromDate, $inclusive);
        $this->toDate($toDate, $inclusive);

        return $this;
    }

    /**
     * Helper to convert PHP date to miliseconds since epoch.
     * Of course, without the milisecond precision.
     */
    private function toEventTime($value)
    {
        if ($value instanceof \DateTime) {
            $seconds = $value->getTimestamp();

        } elseif (is_numeric($value)) {
            $seconds = (int) $value;

        } else {
            $seconds = strtotime((string) $value);
        }

        return $seconds * 1000;
    }
}
