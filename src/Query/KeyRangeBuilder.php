<?php
namespace andrefelipe\Orchestrate\Query;

use andrefelipe\Orchestrate\Contracts\ToArrayInterface;
use andrefelipe\Orchestrate\Contracts\KeyValueInterface;

/**
 *
 * @link https://orchestrate.io/docs/apiref#keyvalue-list
 */
class KeyRangeBuilder implements ToArrayInterface
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
     * The start of the key range to paginate from.
     * Include or exclude the start key, if it exists, using the 'inclusive' parameter.
     *
     * @param string|KeyValueInterface $key The start Key to paginate from.
     * @param boolean $inclusive Include the specified key, it it exists. Defaults to true.
     *
     * @return KeyRangeBuilder self
     */
    public function from($key, $inclusive = true)
    {
        // cleanup - both start ranges can not coexist
        unset($this->range['startKey']);
        unset($this->range['afterKey']);

        // set
        if ($key instanceof KeyValueInterface) {
            $key = $key->getKey(true);
        }
        $this->range[($inclusive ? 'start' : 'after').'Key'] = (string) $key;

        return $this;
    }

    /**
     * The end of the key range to paginate to.
     * Include or exclude the end key, if it exists, using the 'inclusive' parameter.
     *
     * @param string|KeyValueInterface $key The end Key to paginate to.
     * @param boolean $inclusive Include the specified key, it it exists. Defaults to true.
     *
     * @return KeyRangeBuilder self
     */
    public function to($key, $inclusive = true)
    {
        // cleanup - both end ranges can not coexist
        unset($this->range['endKey']);
        unset($this->range['beforeKey']);

        // set
        if ($key instanceof KeyValueInterface) {
            $key = $key->getKey(true);
        }
        $this->range[($inclusive ? 'end' : 'before').'Key'] = (string) $key;

        return $this;
    }

    /**
     * Wraps both 'from' and 'to' methods in a single call.
     * Include or exclude the range keys, if they exist, using the 'inclusive' parameter.
     *
     * @param string|KeyValueInterface $fromKey The start Key to paginate from.
     * @param string|KeyValueInterface $toKey The end Key to paginate to.
     * @param boolean $inclusive Include the specified keys, it they exist. Defaults to true.
     *
     * @return KeyRangeBuilder self
     */
    public function between($fromKey, $toKey, $inclusive = true)
    {
        $this->from($fromKey, $inclusive);
        $this->to($toKey, $inclusive);

        return $this;
    }
}
