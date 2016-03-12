<?php
namespace andrefelipe\Orchestrate\Properties;

/**
 * Trait that implements the JSON serialization methods.
 * Implementation should also add the ToJsonInterface.
 *
 * @internal
 */
trait ToJsonTrait
{
    public function toJson($options = 0, $depth = 512)
    {
        return json_encode($this, $options, $depth);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
