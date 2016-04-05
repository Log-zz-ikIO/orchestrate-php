<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Contracts\KeyValueInterface;
use andrefelipe\Orchestrate\Query\PatchBuilder;

class KeyValue extends AbstractItem implements KeyValueInterface
{
    use Properties\CollectionTrait;
    use Properties\KeyTrait;

    /**
     * @var boolean
     */
    private $_tombstone = false;

    /**
     * @param string $collection
     * @param string $key
     * @param string $ref
     */
    public function __construct($collection = null, $key = null, $ref = null)
    {
        $this->setCollection($collection);
        $this->setKey($key);
        $this->setRef($ref);
    }

    public function isTombstone()
    {
        return $this->_tombstone;
    }

    public function reset()
    {
        parent::reset();
        $this->_collection = null;
        $this->_key = null;
        $this->_tombstone = false;
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
                } elseif ($key === 'tombstone') {
                    $this->_tombstone = (boolean) $value;
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

        if ($this->_tombstone) {
            $data['path']['tombstone'] = $this->_tombstone;
        }

        return $data;
    }

    public function get($ref = null)
    {
        $this->getAsync($ref);
        $this->settlePromise();
        return $this->isSuccess();
    }

    public function getAsync($ref = null)
    {
        return $this->requestAsync(
            // method
            'GET',
            // uri
            static function ($self) use ($ref) {
                $uri = [
                    $self->getCollection(true),
                    $self->getKey(true),
                ];
                if ($ref) {
                    $uri[] = 'refs';
                    $uri[] = $self->getValidRef($ref);
                }
                return $uri;
            },
            // options
            null,
            // onFulfilled
            static function ($self) {
                $self->setValue($self->getBodyArray());
                $self->setRefFromETag();
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

    public function putIfNone(array $value = null)
    {
        return $this->_put($value, false);
    }

    public function putIfNoneAsync(array $value = null)
    {
        return $this->_putAsync($value, false);
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
                } elseif ($ref === false) {
                    $options['headers'] = ['If-None-Match' => '"*"'];
                }
                return $options;
            },
            // onFulfilled
            static function ($self) use ($value) {
                if ($value !== null) {
                    $self->resetValue();
                    $self->setValue($value);
                }
                $self->setRefFromETag();
                return $self;
            }
        );
    }

    public function post(array $value = null)
    {
        $this->postAsync($value);
        $this->settlePromise();
        return $this->isSuccess();
    }

    public function postAsync(array $value = null)
    {
        return $this->requestAsync(
            // method
            'POST',
            // uri
            static function ($self) {
                $uri = [
                    $self->getCollection(true),
                ];
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
                if ($value !== null) {
                    $self->resetValue();
                    $self->setValue($value);
                }
                $self->setKeyRefFromLocation();
                return $self;
            }
        );
    }

    public function patch(PatchBuilder $operations, $reload = false)
    {
        return $this->_patch($operations, null, $reload);
    }

    public function patchAsync(PatchBuilder $operations, $reload = false)
    {
        return $this->_patchAsync($operations, null, $reload);
    }

    public function patchIf($ref = true, PatchBuilder $operations, $reload = false)
    {
        return $this->_patch($operations, $ref, $reload);
    }

    public function patchIfAsync($ref = true, PatchBuilder $operations, $reload = false)
    {
        return $this->_patchAsync($operations, $ref, $reload);
    }

    private function _patch(PatchBuilder $operations, $ref = null, $reload = false)
    {
        $this->_patchAsync($operations, $ref, $reload);
        $this->settlePromise();
        return $this->isSuccess();
    }

    private function _patchAsync(PatchBuilder $operations, $ref = null, $reload = false)
    {
        return $this->requestAsync(
            // method
            'PATCH',
            // uri
            static function ($self) {
                $uri = [
                    $self->getCollection(true),
                    $self->getKey(true),
                ];
                return $uri;
            },
            // options
            static function ($self) use ($operations, $ref) {
                $options = [
                    'json' => $operations->toArray(),
                ];
                if ($ref) {
                    $ref = $self->getValidRef($ref);
                    $options['headers'] = ['If-Match' => '"'.$ref.'"'];
                }
                return $options;
            },
            // onFulfilled
            static function ($self) use ($reload) {
                $self->setRefFromETag();

                // reload the Value from API
                if ($reload) {
                    $self->get($self->getRef());
                }
                return $self;
            }
        );
    }

    public function patchMerge(array $value, $reload = false)
    {
        return $this->_patchMerge($value, $reload);
    }

    public function patchMergeAsync(array $value, $reload = false)
    {
        return $this->_patchMergeAsync($value, $reload);
    }

    public function patchMergeIf($ref, array $value, $reload = false)
    {
        return $this->_patchMerge($value, $ref, $reload);
    }

    public function patchMergeIfAsync($ref, array $value, $reload = false)
    {
        return $this->_patchMergeAsync($value, $ref, $reload);
    }

    private function _patchMerge(array $value, $ref = null, $reload = false)
    {
        $this->_patchMergeAsync($value, $ref, $reload);
        $this->settlePromise();
        return $this->isSuccess();
    }

    private function _patchMergeAsync(array $value, $ref = null, $reload = false)
    {
        return $this->requestAsync(
            // method
            'PATCH',
            // uri
            static function ($self) {
                $uri = [
                    $self->getCollection(true),
                    $self->getKey(true),
                ];
                return $uri;
            },
            // options
            static function ($self) use ($value, $ref) {
                $options = ['json' => $value];

                if ($ref) {
                    $ref = $self->getValidRef($ref);
                    $options['headers'] = ['If-Match' => '"'.$ref.'"'];
                }
                return $options;
            },
            // onFulfilled
            static function ($self) use ($reload) {
                $self->setRefFromETag();

                // reload the Value from API
                if ($reload) {
                    $self->get($self->getRef());
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
                ];
                return $uri;
            },
            // options
            static function ($self) use ($ref) {
                $options = [];

                if ($ref) {
                    $ref = $self->getValidRef($ref);
                    $options['headers'] = ['If-Match' => '"'.$ref.'"'];
                }
                return $options;
            },
            // onFulfilled
            static function ($self) {
                $self->_score = null;
                $self->_distance = null;
                $self->_reftime = null;
                $self->_tombstone = true;
                $self->resetValue();

                return $self;
            }
        );
    }

    public function purge()
    {
        $this->purgeAsync();
        $this->settlePromise();
        return $this->isSuccess();
    }

    public function purgeAsync()
    {
        return $this->requestAsync(
            // method
            'DELETE',
            // uri
            static function ($self) {
                $uri = [
                    $self->getCollection(true),
                    $self->getKey(true),
                ];
                return $uri;
            },
            // options
            static function ($self) {
                $options = ['query' => ['purge' => 'true']];
                return $options;
            },
            // onFulfilled
            static function ($self) {
                $self->_key = null;
                $self->_ref = null;
                $self->_score = null;
                $self->_distance = null;
                $self->_reftime = null;
                $self->_tombstone = false;
                $self->resetValue();

                return $self;
            }
        );
    }

    public function refs()
    {
        return (new Refs($this->getCollection(true), $this->getKey(true)))
            ->setHttpClient($this->getHttpClient())
            ->setItemClass(new \ReflectionClass($this));
    }

    public function events($type = null)
    {
        return (new Events($this->getCollection(true), $this->getKey(true), $type))
            ->setHttpClient($this->getHttpClient());
    }

    public function event($type = null, $timestamp = null, $ordinal = null)
    {
        return (new Event(
            $this->getCollection(true),
            $this->getKey(true),
            $type,
            $timestamp,
            $ordinal
        ))->setHttpClient($this->getHttpClient());
    }

    public function relationships($kind)
    {
        return (new Relationships($this->getCollection(true), $this->getKey(true), $kind))
            ->setHttpClient($this->getHttpClient());
    }

    public function relationship($kind, KeyValueInterface $destination)
    {
        return (new Relationship($this, $kind, $destination))
            ->setHttpClient($this->getHttpClient());
    }

    /**
     * Helper to set the Key and Ref from a Orchestrate Location HTTP header.
     * For example: Location: /v0/collection/key/refs/ad39c0f8f807bf40
     *
     * Should be used when the request was succesful.
     */
    private function setKeyRefFromLocation()
    {
        $location = $this->getResponse()->getHeader('Location');
        if (empty($location)) {
            $location = $this->getResponse()->getHeader('Content-Location');
        }
        if (empty($location)) {
            return;
        }

        $location = explode('/', trim($location[0], '/'));
        if (count($location) > 4) {
            $this->_key = $location[2];
            $this->_ref = $location[4];
        } else {
            $this->_key = null;
            $this->_ref = null;
        }
    }
}
