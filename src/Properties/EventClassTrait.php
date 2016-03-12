<?php
namespace andrefelipe\Orchestrate\Properties;

/**
 * Trait for Event factory.
 *
 * @internal
 */
trait EventClassTrait
{
    /**
     * @var \ReflectionClass
     */
    private $_eventClass = null;

    /**
     * @var \ReflectionClass
     */
    private static $defaultEventClass = null;

    /**
     * @var string
     */
    private static $defaultEventClassName = 'andrefelipe\Orchestrate\Event';

    /**
     * @var string
     */
    private static $minimumEventInterface = 'andrefelipe\Orchestrate\Contracts\EventInterface';

    /**
     * Get the ReflectionClass that is being used to instantiate this list's events.
     *
     * @return \ReflectionClass
     */
    public function getEventClass()
    {
        if (!isset($this->_eventClass)) {

            if (!isset(self::$defaultEventClass)) {
                self::$defaultEventClass = new \ReflectionClass(self::$defaultEventClassName);
            }

            return self::$defaultEventClass;
        }

        return $this->_eventClass;
    }

    /**
     * Set which class should be used to instantiate this list's events.
     * Pass null to revert back to the default class: Event.
     *
     * @param null|string|\ReflectionClass $class Fully-qualified class name or ReflectionClass.
     *
     * @return AbstractList self
     * @throws \RuntimeException If class does not implement minimum interface.
     */
    public function setEventClass($class)
    {
        if (!$class) {
            $this->_eventClass = null;
            return $this;
        }

        if ($class instanceof \ReflectionClass) {
            $this->_eventClass = $class;
        } else {
            $this->_eventClass = new \ReflectionClass($class);
        }

        if (!$this->_eventClass->implementsInterface(self::$minimumEventInterface)) {
            throw new \RuntimeException('Event classes must implement '.self::$minimumEventInterface);
        }

        return $this;
    }
}
