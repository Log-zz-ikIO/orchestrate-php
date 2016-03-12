<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Contracts\RefsInterface;

class Refs extends AbstractList implements RefsInterface
{
    use Properties\CollectionTrait;
    use Properties\KeyTrait;
    use Properties\ItemClassTrait;

    public function __construct($collection = null, $key = null)
    {
        $this->setCollection($collection);
        $this->setKey($key);
    }

    public function reset()
    {
        parent::reset();
        $this->_collection = null;
        $this->_key = null;
    }

    public function init(array $data)
    {
        if (!empty($data)) {

            if (isset($data['itemClass'])) {
                $this->setItemClass($data['itemClass']);
            }
            if (isset($data['collection'])) {
                $this->setCollection($data['collection']);
            }
            if (isset($data['key'])) {
                $this->setKey($data['key']);
            }

            parent::init($data);
        }
        return $this;
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['kind'] = static::KIND;
        $data['collection'] = $this->_collection;
        $data['key'] = $this->_key;

        return $data;
    }

    public function get($limit = 10, $offset = 0, $values = false)
    {
        // define request options
        $path = [
            $this->getCollection(true),
            $this->getKey(true),
            'refs',
        ];

        $parameters = ['limit' => $limit];

        if ($offset) {
            $parameters['offset'] = $offset;
        }

        if ($values) {
            $parameters['values'] = 'true';
        }

        // request
        $this->request('GET', $path, ['query' => $parameters]);

        if ($this->isSuccess()) {
            $this->setResponseValues();
        }
        return $this->isSuccess();
    }

    /**
     * @param array $itemValues
     */
    protected function createInstance(array $itemValues)
    {
        if (!empty($itemValues['path']['kind'])) {
            $kind = $itemValues['path']['kind'];

            if ($kind === KeyValue::KIND) {
                $class = $this->getItemClass();

            } else {
                return null;
            }

            $item = $class->newInstance()->init($itemValues);

            if ($client = $this->getHttpClient()) {
                $item->setHttpClient($client);
            }
            return $item;
        }
        return null;
    }
}
