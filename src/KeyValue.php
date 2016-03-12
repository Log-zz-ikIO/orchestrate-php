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
        $this->wait();
        return $this->isSuccess();
    }

    public function getAsync($ref = null)
    {
        // define request options
        $path = [
            $this->getCollection(true),
            $this->getKey(true),
        ];
        if ($ref) {
            $path[] = 'refs';
            $path[] = trim($ref, '"');
        }

        // request
        $promise = $this->requestAsync('GET', $path);

        $promise = $promise->then(
            static function ($self) {
                $self->setValue($self->getBody());
                $self->setRefFromETag();
                return $self;
            }
            // ,
            // static function ($self) {
            //     return new \GuzzleHttp\Promise\RejectedPromise($self);
            // }

        );

        return $promise;
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
        return $this->_put($value, $this->getValidRef($ref));
    }

    public function putIfAsync($ref = true, array $value = null)
    {
        return $this->_putAsync($value, $this->getValidRef($ref));
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
        $this->wait();
        return $this->isSuccess();
    }

    private function _putAsync(array $value = null, $ref = null)
    {
        $newValue = $value === null ? $this->getValue() : $value;

        // define request options
        $path = [
            $this->getCollection(true),
            $this->getKey(true),
        ];
        $options = ['json' => $newValue];

        if ($ref) {
            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        } elseif ($ref === false) {
            $options['headers'] = ['If-None-Match' => '"*"'];
        }

        // request
        $promise = $this->requestAsync('PUT', $path, $options);

        $promise = $promise->then(
            static function ($self) use ($value, $newValue) {

                if ($value !== null) {
                    $self->resetValue();
                    $self->setValue($newValue);
                }
                $self->setRefFromETag();
                return $self;
            }
        );

        return $promise;
    }

    public function post(array $value = null)
    {
        $this->postAsync($value);
        $this->wait();
        return $this->isSuccess();
    }

    public function postAsync(array $value = null)
    {
        $newValue = $value === null ? $this->getValue() : $value;

        // request
        $path = [$this->getCollection(true)];
        $promise = $this->requestAsync('POST', $path, ['json' => $newValue]);

        $promise = $promise->then(
            static function ($self) use ($value, $newValue) {

                if ($value !== null) {
                    $self->resetValue();
                    $self->setValue($newValue);
                }
                $self->setKeyRefFromLocation();
                return $self;
            }
        );

        return $promise;
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
        return $this->_patch($operations, $this->getValidRef($ref), $reload);
    }

    public function patchIfAsync($ref = true, PatchBuilder $operations, $reload = false)
    {
        return $this->_patchAsync($operations, $this->getValidRef($ref), $reload);
    }

    private function _patch(PatchBuilder $operations, $ref = null, $reload = false)
    {
        $this->_patchAsync($operations, $ref, $reload);
        $this->wait();
        return $this->isSuccess();
    }

    private function _patchAsync(PatchBuilder $operations, $ref = null, $reload = false)
    {
        // define request options
        $path = [
            $this->getCollection(true),
            $this->getKey(true),
        ];
        $options = ['json' => $operations->toArray()];

        if ($ref) {
            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $promise = $this->requestAsync('PATCH', $path, $options);

        $promise = $promise->then(
            static function ($self) use ($reload) {

                $self->setRefFromETag();

                // reload the Value from API
                if ($reload) {
                    $self->get($self->getRef());
                }

                return $self;
            }
        );

        return $promise;
    }

    public function patchMerge(array $value, $reload = false)
    {
        return $this->_patchMerge($value, $reload);
    }

    public function patchMergeIf($ref, array $value, $reload = false)
    {
        return $this->_patchMerge($value, $this->getValidRef($ref), $reload);
    }

    private function _patchMerge(array $value, $ref = null, $reload = false)
    {
        $this->_patchMergeAsync($value, $ref, $reload);
        $this->wait();
        return $this->isSuccess();
    }

    private function _patchMergeAsync(array $value, $ref = null, $reload = false)
    {
        // define request options
        $path = [
            $this->getCollection(true),
            $this->getKey(true),
        ];
        $options = ['json' => $value];

        if ($ref) {
            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $promise = $this->requestAsync('PATCH', $path, $options);

        $promise = $promise->then(
            static function ($self) use ($reload) {

                $self->setRefFromETag();

                // reload the Value from API
                if ($reload) {
                    $self->get($self->getRef());
                }

                return $self;
            }
        );

        return $promise;
    }

    public function delete()
    {
        return $this->_delete();
    }

    public function deleteIf($ref = true)
    {
        return $this->_delete($this->getValidRef($ref));
    }

    private function _delete($ref = null)
    {
        $this->_deleteAsync($ref);
        $this->wait();
        return $this->isSuccess();
    }

    private function _deleteAsync($ref = null)
    {
        // define request options
        $path = [
            $this->getCollection(true),
            $this->getKey(true),
        ];
        $options = [];

        if ($ref) {
            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $promise = $this->requestAsync('DELETE', $path, $options);

        $promise = $promise->then(
            static function ($self) {

                $self->_score = null;
                $self->_distance = null;
                $self->_reftime = null;
                $self->_tombstone = true;
                $self->resetValue();

                return $self;
            }
        );

        return $promise;
    }

    public function purge()
    {
        $this->purgeAsync();
        $this->wait();
        return $this->isSuccess();
    }

    public function purgeAsync()
    {
        // define request options
        $path = [
            $this->getCollection(true),
            $this->getKey(true),
        ];
        $options = ['query' => ['purge' => 'true']];

        // request
        $promise = $this->requestAsync('DELETE', $path, $options);

        $promise = $promise->then(
            static function ($self) {

                $this->_key = null;
                $this->_ref = null;
                $this->_score = null;
                $this->_distance = null;
                $this->_reftime = null;
                $this->_tombstone = false;
                $this->resetValue();

                return $self;
            }
        );

        return $promise;
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
