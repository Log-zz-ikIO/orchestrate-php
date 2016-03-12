<?php
namespace andrefelipe\Orchestrate\Properties;

/**
 * Trait for KeyValue factory.
 *
 * @internal
 */
trait ItemClassTrait
{
    /**
     * @var \ReflectionClass
     */
    private $_itemClass = null;

    /**
     * @var \ReflectionClass
     */
    private static $defaultItemClass = null;

    /**
     * @var string
     */
    private static $defaultItemClassName = 'andrefelipe\Orchestrate\KeyValue';

    /**
     * @var string
     */
    private static $minimumItemInterface = 'andrefelipe\Orchestrate\Contracts\KeyValueInterface';

    /**
     * Get the ReflectionClass that is being used to instantiate this list's items (KeyValue).
     *
     * @return \ReflectionClass
     */
    public function getItemClass()
    {
        if (!isset($this->_itemClass)) {

            if (!isset(self::$defaultItemClass)) {
                self::$defaultItemClass = new \ReflectionClass(self::$defaultItemClassName);
            }

            return self::$defaultItemClass;
        }

        return $this->_itemClass;
    }

    /**
     * Set which class should be used to instantiate this list's items.
     * Pass null to revert back to the default class: KeyValue.
     *
     * @param null|string|\ReflectionClass $class Fully-qualified class name or ReflectionClass.
     *
     * @return AbstractList self
     * @throws \RuntimeException If class does not implement minimum interface.
     */
    public function setItemClass($class)
    {
        if (!$class) {
            $this->_itemClass = null;
            return $this;
        }

        if ($class instanceof \ReflectionClass) {
            $this->_itemClass = $class;
        } else {
            $this->_itemClass = new \ReflectionClass($class);
        }

        if (!$this->_itemClass->implementsInterface(self::$minimumItemInterface)) {
            throw new \RuntimeException('Item classes must implement '.self::$minimumItemInterface);
        }

        return $this;
    }
}
