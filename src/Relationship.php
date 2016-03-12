<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Contracts\KeyValueInterface;
use andrefelipe\Orchestrate\Contracts\RelationshipInterface;

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
        // define request options
        $path = $this->formRelationPath();

        // Orchestrate doesn't support relationship history (refs) yet

        // request
        $this->request('GET', $path);

        // set values
        if ($this->isSuccess()) {
            $this->setValue($this->getBody());
            $this->setRefFromETag();
        }
        return $this->isSuccess();
    }

    public function put(array $value = null)
    {
        return $this->_put($value);
    }

    public function putIf($ref = true, array $value = null)
    {
        return $this->_put($value, $this->getValidRef($ref));
    }

    public function putIfNone(array $value = null)
    {
        return $this->_put($value, false);
    }

    private function _put(array $value = null, $ref = null)
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
        $this->request('PUT', $path, $options);

        // set values
        if ($this->isSuccess()) {
            $this->setRefFromETag();

            if ($value !== null) {
                $this->resetValue();
                $this->setValue($newValue);
            }
        }
        return $this->isSuccess();
    }

    public function putBoth(array $value = null)
    {
        return $this->_putBoth($value);
    }

    public function putBothIf($ref = true, array $value = null)
    {
        return $this->_putBoth($value, $this->getValidRef($ref));
    }

    public function putBothIfNone(array $value = null)
    {
        return $this->_putBoth($value, false);
    }

    private function _putBoth(array $value = null, $ref = null)
    {
        $success = $this->_put($value, $ref);

        if ($success) {
            $path = $this->formRelationPath(false, true);
            $value = parent::toArray();
            $options = ['json' => empty($value) ? null : $value];

            $this->request('PUT', $path, $options);
        }

        return $this->isSuccess();
    }

    public function delete()
    {
        $options = ['query' => ['purge' => 'true']];

        $this->request('DELETE', $this->formRelationPath(), $options);

        if ($this->isSuccess()) {
            $this->_score = null;
            $this->_distance = null;
            $this->_ref = null;
            $this->_reftime = null;
            $this->resetValue();
        }

        return $this->isSuccess();
    }

    public function deleteBoth()
    {
        $success = $this->delete();

        if ($success) {
            $path = $this->formRelationPath(false, true);
            $options = ['query' => ['purge' => 'true']];
            $this->request('DELETE', $path, $options);
        }

        return $this->isSuccess();
    }

}
