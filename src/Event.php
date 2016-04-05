<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Contracts\EventInterface;

class Event extends AbstractItem implements EventInterface
{
    use Properties\CollectionTrait;
    use Properties\KeyTrait;
    use Properties\TypeTrait;
    use Properties\TimestampTrait;
    use Properties\OrdinalTrait;

    /**
     * @param string $collection
     * @param string $key
     * @param string $type
     * @param int $timestamp
     * @param int $ordinal
     */
    public function __construct(
        $collection = null,
        $key = null,
        $type = null,
        $timestamp = null,
        $ordinal = null
    ) {
        $this->setCollection($collection);
        $this->setKey($key);
        $this->setType($type);
        $this->setTimestamp($timestamp);
        $this->setOrdinal($ordinal);
    }

    public function reset()
    {
        parent::reset();
        $this->_collection = null;
        $this->_key = null;
        $this->_type = null;
        $this->_timestamp = null;
        $this->_ordinal = null;
        $this->_ordinalStr = null;
    }

    public function init(array $data)
    {
        if (!empty($data)) {

            if (!empty($data['path'])) {
                $data = array_merge($data, $data['path']);
                unset($data['path']);
            }

            parent::init($data);

            foreach ($data as $key => $value) {
                if ($key === 'collection') {
                    $this->setCollection($value);
                } elseif ($key === 'key') {
                    $this->setKey($value);
                } elseif ($key === 'type') {
                    $this->setType($value);
                } elseif ($key === 'timestamp') {
                    $this->setTimestamp($value);
                } elseif ($key === 'ordinal') {
                    $this->setOrdinal($value);
                } elseif ($key === 'ordinal_str') {
                    $this->setOrdinalStr($value);
                }
            }
        }
        return $this;
    }

    public function toArray()
    {
        $data = parent::toArray();

        $data['path']['collection'] = $this->_collection;
        $data['path']['key'] = $this->_key;
        $data['path']['type'] = $this->_type;
        $data['path']['timestamp'] = $this->_timestamp;
        $data['path']['ordinal'] = $this->_ordinal;
        $data['path']['ordinal_str'] = $this->_ordinalStr;

        return $data;
    }

    public function get()
    {
        $this->getAsync($ref);
        $this->settlePromise();
        return $this->isSuccess();
    }

    public function getAsync()
    {
        return $this->requestAsync(
            // method
            'GET',
            // uri
            static function ($self) {
                $uri = [
                    $self->getCollection(true),
                    $self->getKey(true),
                    'events',
                    $self->getType(true),
                    $self->getTimestamp(true),
                    $self->getOrdinal(true),
                ];
                return $uri;
            },
            // options
            null,
            // onFulfilled
            static function ($self) {

                $self->init($self->getBodyArray());
                return $self;
            }
        );
    }

    public function put(array $value = null)
    {
        return $this->_put($value);
    }

    public function putAsync(array $value = null)
    {
        return $this->_putAsync($value);
    }

    public function putIf($ref = true, array $value = null)
    {
        return $this->_put($value, $ref);
    }

    public function putIfAsync($ref = true, array $value = null)
    {
        return $this->_putAsync($value, $ref);
    }

    private function _put(array $value = null, $ref = null)
    {
        $this->_putAsync($value, $ref);
        $this->settlePromise();
        return $this->isSuccess();
    }

    private function _putAsync(array $value = null, $ref = null)
    {
        return $this->requestAsync(
            // method
            'PUT',
            // uri
            static function ($self) {
                $uri = [
                    $self->getCollection(true),
                    $self->getKey(true),
                    'events',
                    $self->getType(true),
                    $self->getTimestamp(true),
                    $self->getOrdinal(true),
                ];
                return $uri;
            },
            // options
            static function ($self) use ($ref, $value) {
                $options = [
                    'json' => $value === null ? $self->getValue() : $value,
                ];
                if ($ref) {
                    $ref = $self->getValidRef($ref);
                    $options['headers'] = ['If-Match' => '"'.$ref.'"'];
                }
                return $options;
            },
            // onFulfilled
            static function ($self) use ($value) {

                $self->_reftime = null;
                $self->_ordinalStr = null;
                $self->setRefFromETag();
                $self->setTimestampAndOrdinalFromLocation();

                if ($value !== null) {
                    $self->resetValue();
                    $self->setValue($value);
                }
                return $self;
            }
        );
    }

    public function post(array $value = null, $timestamp = null)
    {
        $this->postAsync($value);
        $this->settlePromise();
        return $this->isSuccess();
    }

    public function postAsync(array $value = null, $timestamp = null)
    {
        return $this->requestAsync(
            // method
            'POST',
            // uri
            static function ($self) use ($timestamp) {
                $uri = [
                    $self->getCollection(true),
                    $self->getKey(true),
                    'events',
                    $self->getType(true),
                ];
                if ($timestamp === true) {
                    $timestamp = $self->getTimestamp(true);
                }
                if ($timestamp) {
                    $uri[] = $timestamp;
                }
                return $uri;
            },
            // options
            static function ($self) use ($value) {
                $options = [
                    'json' => $value === null ? $self->getValue() : $value,
                ];
                return $options;
            },
            // onFulfilled
            static function ($self) use ($value) {
                $self->_reftime = null;
                $self->_ordinalStr = null;
                $self->setRefFromETag();
                $self->setTimestampAndOrdinalFromLocation();

                if ($value !== null) {
                    $self->resetValue();
                    $self->setValue($value);
                }
                return $self;
            }
        );
    }

    public function delete()
    {
        return $this->_delete();
    }

    public function deleteAsync()
    {
        return $this->_deleteAsync();
    }

    public function deleteIf($ref = true)
    {
        return $this->_delete($ref);
    }

    public function deleteIfAsync($ref = true)
    {
        return $this->_deleteAsync($ref);
    }

    private function _delete($ref = null)
    {
        $this->_deleteAsync($ref);
        $this->settlePromise();
        return $this->isSuccess();
    }

    private function _deleteAsync($ref = null)
    {
        return $this->requestAsync(
            // method
            'DELETE',
            // uri
            static function ($self) {
                $uri = [
                    $self->getCollection(true),
                    $self->getKey(true),
                    'events',
                    $self->getType(true),
                    $self->getTimestamp(true),
                    $self->getOrdinal(true),
                ];
                return $uri;
            },
            // options
            static function ($self) use ($ref) {
                $options = [
                    'query' => ['purge' => 'true'],
                    // currently required by Orchestrate
                ];
                if ($ref) {
                    $ref = $self->getValidRef($ref);
                    $options['headers'] = ['If-Match' => '"'.$ref.'"'];
                }
                return $options;
            },
            // onFulfilled
            static function ($self) {
                $self->_ref = null;
                $self->_reftime = null;
                $self->_ordinalStr = null;
                $self->_score = null;
                $self->_distance = null;
                $self->resetValue();

                return $self;
            }
        );
    }

    private function setTimestampAndOrdinalFromLocation()
    {
        // Location: /v0/collection/key/events/type/1398286518286/6

        $location = $this->getResponse()->getHeader('Location');
        if (empty($location)) {
            $location = $this->getResponse()->getHeader('Content-Location');
        }
        if (empty($location)) {
            return;
        }

        $location = explode('/', trim($location[0], '/'));

        if (isset($location[5])) {
            $this->setTimestamp($location[5]);
        } else {
            $this->_timestamp = null;
        }

        if (isset($location[6])) {
            $this->setOrdinal($location[6]);
        } else {
            $this->_ordinal = null;
        }
    }
}
