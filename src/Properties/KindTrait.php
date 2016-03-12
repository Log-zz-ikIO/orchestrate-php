<?php
namespace andrefelipe\Orchestrate\Properties;

/**
 * Trait that implements the getKind method.
 *
 * @internal
 */
trait KindTrait
{
    final public function getKind()
    {
        return static::KIND;
    }
}
