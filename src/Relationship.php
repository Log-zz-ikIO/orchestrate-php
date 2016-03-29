<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Contracts\KeyValueInterface;
use andrefelipe\Orchestrate\Contracts\RelationshipInterface;
use GuzzleHttp\Promise;

class Relationship extends AbstractItem implements RelationshipInterface
{
    use Properties\RelationTrait;
    use Properties\RelationshipTrait;
    use Properties\ItemClassTrait;

    /**
     * @param KeyValueInterface $source
     * @param string $kind
     * @param KeyValueInterface $destination
     */
    public function __construct(
        KeyValueInterface $source = null,
                          $kind = null,
        KeyValueInterface $destination = null
    ) {
        if ($source) {
            $this->setSource($source);
        }
        if ($kind) {
            $this->setRelation($kind);
        }
        if ($destination) {
            $this->setDestination($destination);
        }
    }

    public function reset()
    {
        parent::reset();
        $this->_source = null;
        $this->_relation = null;
        $this->_destination = null;
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
                if ($key === 'source') {
                    if (is_array($value)) {
                        $item = $this->getItemClass()->newInstance()->init($value);
                        $this->setSource($item);
                    } elseif ($value instanceof KeyValueInterface) {
                        $this->setSource($value);
                    }
                } elseif ($key === 'destination') {
                    if (is_array($value)) {
                        $item = $this->getItemClass()->newInstance()->init($value);
                        $this->setDestination($item);
                    } elseif ($value instanceof KeyValueInterface) {
                        $this->setDestination($value);
                    }
                } elseif ($key === 'relation') {
                    $this->setRelation($value);
                }
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();

        $data['path']['relation'] = $this->_relation;

        $source = $this->getSource();
        if ($source) {
            $data['path']['source'] = [
                'kind' => 'item',
                'collection' => $source->getCollection(),
                'key' => $source->getKey(),
            ];
        } else {
            $data['path']['source'] = null;
        }

        $destination = $this->getDestination();
        if ($destination) {
            $data['path']['destination'] = [
                'kind' => 'item',
                'collection' => $destination->getCollection(),
                'key' => $destination->getKey(),
            ];
        } else {
            $data['path']['destination'] = null;
        }

        return $data;
    }

    public function get()
    {
        $this->getAsync();
        $this->settlePromise();
        return $this->isSuccess();
    }

    public function getAsync()
    {
        // define request options
        $path = $this->formRelationPath();

        // Orchestrate doesn't support relationship history (refs) yet

        // request
        $promise = $this->requestAsync('GET', $path);

        // chain promise
        $this->_promise = $promise->then(
            static function ($self) {

                $self->setValue($self->getBodyArray());
                $self->setRefFromETag();
                return $self;
            }
        );

        return $this->_promise;
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
        $this->settlePromise();
        return $this->isSuccess();
    }

    private function _putAsync(array $value = null, $ref = null)
    {
        $newValue = $value === null ? $this->getValue() : $value;

        // define request options
        $path = $this->formRelationPath();
        $options = ['json' => empty($newValue) ? null : $newValue];

        if ($ref) {
            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        } elseif ($ref === false) {
            $options['headers'] = ['If-None-Match' => '"*"'];
        }

        // request
        $promise = $this->requestAsync('PUT', $path, $options);

        // chain promise
        $this->_promise = $promise->then(
            static function ($self) use ($value) {

                if ($value !== null) {
                    $self->resetValue();
                    $self->setValue($value);
                }
                $self->setRefFromETag();
                return $self;
            }
        );

        return $this->_promise;
    }

    public function putBoth(array $value = null)
    {
        $promise = $this->putBothAsync($value);
        $promise->wait(false);
        return $this->isSuccess();
    }

    public function putBothAsync(array $value = null)
    {   
        $promises = [];
        $promises[] = $this->putAsync($value);

        $opposite = (new Relationship())
            ->setSource($this->getDestination())
            ->setDestination($this->getSource())
            ->setRelation($this->getRelation())
            ->setHttpClient($this->getHttpClient());

        $promises[] = $opposite->putAsync($value);

        return Promise\each($promises);
    }

    public function delete()
    {
        $this->deleteAsync();
        $this->settlePromise();
        return $this->isSuccess();
    }

    public function deleteAsync()
    {
        $options = ['query' => ['purge' => 'true']];
        $path = $this->formRelationPath();

        // request
        $promise = $this->requestAsync('DELETE', $path, $options);

        $this->_promise = $promise->then(
            static function ($self) {

                $self->_score = null;
                $self->_distance = null;
                $this->_ref = null;
                $self->_reftime = null;
                $self->resetValue();

                return $self;
            }
        );

        return $this->_promise;
    }

    public function deleteBoth(array $value = null)
    {
        $promise = $this->deleteBothAsync($value);
        $promise->wait(false);
        return $this->isSuccess();
    }

    public function deleteBothAsync(array $value = null)
    {   
        $promises = [];
        $promises[] = $this->deleteAsync($value);

        $opposite = (new Relationship())
            ->setSource($this->getDestination())
            ->setDestination($this->getSource())
            ->setRelation($this->getRelation())
            ->setHttpClient($this->getHttpClient());

        $promises[] = $opposite->deleteAsync($value);

        return Promise\each($promises);
    }
}