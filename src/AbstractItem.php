<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Contracts\ItemInterface;
use JmesPath\Env as JmesPath;

/**
 * Implements the ItemInterface logic.
 */
abstract class AbstractItem extends AbstractConnection implements ItemInterface
{
    use Properties\KindTrait;
    use Properties\RefTrait;
    use Properties\ReftimeTrait;
    use Properties\ScoreTrait;
    use Properties\DistanceTrait;
    use Properties\ToJsonTrait;

    /**
     * @var array Storage for user-defined properties mapped to getter/setters.
     */
    private $_propertyMap = [];

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $this->settlePromise();

        if (isset($this->_propertyMap[$name])) {
            if (isset($this->_propertyMap[$name][0])) {
                return $this->_propertyMap[$name][0]();
            } else {
                return null;
            }
        }
        return isset($this->{$name}) ? $this->{$name} : null;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->settlePromise();

        if (isset($this->_propertyMap[$name])) {
            if (isset($this->_propertyMap[$name][1])) {
                $this->_propertyMap[$name][1]($value);
            }
        } else {
            $this->{$name} = $value;
        }
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        $this->settlePromise();

        $this->{$name} = null;
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $this->settlePromise();

        return $this->{$offset};
    }

    /**
     * @param string $offset
     * @param mixed $value
     *
     * @throws \RuntimeException if trying to set values as indexed arrays at
     * root level, i.e., $item[0] = 'myvalue';
     */
    public function offsetSet($offset, $value)
    {
        $this->settlePromise();

        if (is_null($offset) || is_numeric($offset)) {
            throw new \RuntimeException('Indexed arrays not allowed at the root of '.get_class($this).' objects.');
        }

        $this->{(string) $offset} = $value;
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $this->settlePromise();

        $this->{$offset} = null;
    }

    /**
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        $this->settlePromise();

        return isset($this->{$offset});
    }

    public function reset()
    {
        $this->clearResponse();
        $this->_ref = null;
        $this->_reftime = null;
        $this->_score = null;
        $this->_distance = null;
        $this->resetValue();
    }

    public function init(array $data)
    {
        $this->settlePromise();

        if (!empty($data)) {

            if (!empty($data['path'])) {
                $data = array_merge($data, $data['path']);
            }

            foreach ($data as $key => $value) {
                if ($key === 'ref') {
                    $this->setRef($value);
                } elseif ($key === 'reftime') {
                    $this->setReftime($value);
                } elseif ($key === 'value') {
                    $this->setValue((array) $value);
                } elseif ($key === 'score') {
                    $this->setScore($value);
                } elseif ($key === 'distance') {
                    $this->setDistance($value);
                }
            }
        }
        return $this;
    }

    public function getPath()
    {
        $this->settlePromise();
                
        return [
            'kind' => static::KIND,
            'ref' => $this->_ref,
            'reftime' => $this->_reftime,
        ];
    }

    public function toArray()
    {
        $this->settlePromise();

        $data = [
            'kind' => static::KIND,
            'path' => $this->getPath(),
            'value' => array_merge($this->getMappedValues(true), object_to_array($this)),
        ];

        // search properties
        if ($this->_score !== null) {
            $data['score'] = $this->_score;
        }
        if ($this->_distance !== null) {
            $data['distance'] = $this->_distance;
        }

        return $data;
    }

    public function extract($expression)
    {
        return JmesPath::search($expression, $this->toArray());
    }

    public function extractValue($expression)
    {
        return JmesPath::search($expression, $this->getValue());
    }

    public function getValue()
    {
        $this->settlePromise();

        return array_merge($this->getMappedValues(), object_to_array($this));
    }

    public function setValue(array $values)
    {
        $this->settlePromise();

        if (!empty($values)) {
            foreach ($values as $key => $value) {
                $this->{(string) $key} = $value;
            }
        }
        return $this;
    }

    public function mergeValue(ItemInterface $item)
    {
        $this->settlePromise();

        merge_object($item->getValue(), $this);
        return $this;
    }

    public function resetValue()
    {
        $this->settlePromise();

        foreach (get_public_properties($this) as $key) {
            $this->{$key} = null;
        }
        foreach ($this->_propertyMap as $key => $methods) {
            if (isset($methods[1])) {
                $methods[1](null);
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        $this->settlePromise();

        return serialize($this->toArray());
    }

    /**
     * @param string $serialized
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function unserialize($serialized)
    {
        if (is_string($serialized)) {
            $data = unserialize($serialized);

            if (is_array($data)) {

                $this->init($data);
                return;
            }
        }
        throw new \InvalidArgumentException('Invalid serialized data type.');
    }

    /**
     * @param string $name The property name to map methods to.
     * @param boolean|string $getter The getter method name. Method must exist in current object.
     *                                   Defaults to true, which will automatically try to find a
     *                                   method named after your property with camelCase, for example 'getName'.
     * @param boolean|string $setter The setter method name. Method must exist in current object.
     *                                   Defaults to true, which will automatically try to find a
     *                                   method named after your property with camelCase, for example 'setName'.
     *
     * @throws \InvalidArgumentException If a matching getter or setter
     * could not be found
     */
    protected function mapProperty($name, $getter = true, $setter = true)
    {
        $this->_propertyMap[$name] = [];

        if ($getter === true || $setter === true) {
            $capitalized = str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $name)));

            if ($getter === true) {
                $getter = 'get'.$capitalized;
            }
            if ($setter === true) {
                $setter = 'set'.$capitalized;
            }
        }

        if ($getter) {
            if (method_exists($this, $getter)) {
                $this->_propertyMap[$name][0] = [$this, $getter];
            } else {
                throw new \InvalidArgumentException('A matching getter method could not be found, tried: '.$getter);
            }
        }

        if ($setter) {
            if (method_exists($this, $setter)) {
                $this->_propertyMap[$name][1] = [$this, $setter];
            } else {
                throw new \InvalidArgumentException('A matching setter method could not be found, tried: '.$setter);
            }
        }
    }

    /**
     * Helper to get the mapped properties to getters.
     *
     * @return array
     */
    private function getMappedValues($skipNull = false)
    {
        $result = [];
        foreach ($this->_propertyMap as $key => $methods) {
            if (isset($methods[0])) {

                $value = $methods[0]();

                if (!$skipNull || $value !== null) {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }
}
