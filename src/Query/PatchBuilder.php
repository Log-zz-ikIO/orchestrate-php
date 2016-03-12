<?php
namespace andrefelipe\Orchestrate\Query;

use andrefelipe\Orchestrate\Contracts\ToArrayInterface;

/**
 *
 * @link https://orchestrate.io/docs/apiref#keyvalue-patch-operations
 */
class PatchBuilder implements ToArrayInterface
{
    /**
     * @var array
     */
    protected $operations = [];
    
    /**
     * @return array
     */
    public function toArray()
    {
        return $this->operations;
    }

    /**
     * Depending on the specified path, creates a field with that value, replaces an existing field with the specified value, or adds the value to an array.
     *
     * @param  string       $path
     * @param  string       $value
     * @return PatchBuilder self
     */
    public function add($path, $value)
    {
        return $this->appendOperation('add', $path, $value);
    }

    /**
     * Removes the field at a specified path.
     *
     * @param  string       $path
     * @return PatchBuilder self
     */
    public function remove($path)
    {
        return $this->appendOperation('remove', $path);
    }

    /**
     * Replaces an existing value with the given value at the specified path.
     *
     * @param  string       $path
     * @param  string       $value
     * @return PatchBuilder self
     */
    public function replace($path, $value)
    {
        return $this->appendOperation('replace', $path, $value);
    }

    /**
     * Moves a value from one path to another, removing the original path.
     *
     * @param  string       $fromPath
     * @param  string       $toPath
     * @return PatchBuilder self
     */
    public function move($fromPath, $toPath)
    {
        return $this->appendOperation('move', $toPath, null, $fromPath);
    }

    /**
     * Copies the value at one path to another.
     *
     * @param  string       $fromPath
     * @param  string       $toPath
     * @return PatchBuilder self
     */
    public function copy($fromPath, $toPath)
    {
        return $this->appendOperation('copy', $toPath, null, $fromPath);
    }

    /**
     * Tests equality of the value at a particular path to a specified value, the entire request fails if the test fails.
     *
     * @param  string       $path
     * @param  string       $value
     * @return PatchBuilder self
     */
    public function test($path, $value)
    {
        return $this->appendOperation('test', $path, $value);
    }

    /**
     * Increments the numeric value at a specified field by the given numeric value, decrements if given numeric value is negative.
     *
     * @param  string       $path
     * @param  string       $value
     * @return PatchBuilder self
     */
    public function inc($path, $value = 1)
    {
        return $this->appendOperation('inc', $path, $value);
    }

    /**
     * @param  string       $operation
     * @param  string       $path
     * @param  string       $value
     * @param  string       $fromPath
     * @return PatchBuilder self
     */
    protected function appendOperation(
        $operation,
        $path,
        $value = null,
        $fromPath = null
    ) {
        $op = [
            'op' => $operation,
            'path' => $path,
        ];

        if ($value !== null) {
            $op['value'] = $value;
        }

        if ($fromPath) {
            $op['from'] = $fromPath;
        }

        $this->operations[] = $op;

        return $this;
    }
}
