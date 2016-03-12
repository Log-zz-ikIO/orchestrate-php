<?php
namespace andrefelipe\Orchestrate\Properties;

/**
 * Trait for Relationship factory.
 *
 * @internal
 */
trait RelationshipClassTrait
{
    /**
     * @var \ReflectionClass
     */
    private $_relationshipClass = null;

    /**
     * @var \ReflectionClass
     */
    private static $defaultRelationshipClass = null;

    /**
     * @var string
     */
    private static $defaultRelationshipClassName = 'andrefelipe\Orchestrate\Relationship';

    /**
     * @var string
     */
    private static $minimumRelationshipInterface = 'andrefelipe\Orchestrate\Contracts\RelationshipInterface';

    /**
     * Get the ReflectionClass that is being used to instantiate this list's relationships.
     *
     * @return \ReflectionClass
     */
    public function getRelationshipClass()
    {
        if (!isset($this->_relationshipClass)) {

            if (!isset(self::$defaultRelationshipClass)) {
                self::$defaultRelationshipClass = new \ReflectionClass(self::$defaultRelationshipClassName);
            }

            return self::$defaultRelationshipClass;
        }

        return $this->_relationshipClass;
    }

    /**
     * Set which class should be used to instantiate this list's relationships.
     * Pass null to revert back to the default class: Relationship.
     *
     * @param null|string|\ReflectionClass $class Fully-qualified class name or ReflectionClass.
     *
     * @return AbstractList self
     * @throws \RuntimeException If class does not implement minimum interface.
     */
    public function setRelationshipClass($class)
    {
        if (!$class) {
            $this->_relationshipClass = null;
            return $this;
        }

        if ($class instanceof \ReflectionClass) {
            $this->_relationshipClass = $class;
        } else {
            $this->_relationshipClass = new \ReflectionClass($class);
        }

        if (!$this->_relationshipClass->implementsInterface(self::$minimumRelationshipInterface)) {
            throw new \RuntimeException('Relationship classes must implement '.self::$minimumRelationshipInterface);
        }

        return $this;
    }
}
