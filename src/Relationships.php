<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Contracts\RelationshipsInterface;

class Relationships extends AbstractSearchList implements RelationshipsInterface
{
    use Properties\CollectionTrait;
    use Properties\KeyTrait;
    use Properties\DepthTrait;
    use Properties\RelationshipClassTrait;

    /**
     * @param string $collection
     * @param string $key
     * @param string|array $kind
     */
    public function __construct($collection = null, $key = null, $kind = null)
    {
        $this->setCollection($collection);
        $this->setKey($key);
        $this->setDepth($kind);
    }

    public function reset()
    {
        parent::reset();
        $this->_collection = null;
        $this->_key = null;
        $this->_depth = null;
    }

    public function init(array $data)
    {
        if (!empty($data)) {

            if (isset($data['relationshipClass'])) {
                $this->setRelationshipClass($data['relationshipClass']);
            }
            if (isset($data['collection'])) {
                $this->setCollection($data['collection']);
            }
            if (isset($data['key'])) {
                $this->setKey($data['key']);
            }
            if (isset($data['depth'])) {
                $this->setDepth($data['depth']);
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
        $data['depth'] = $this->_depth;

        return $data;
    }

    public function get($limit = 10, $offset = 0)
    {
        // define request options
        $path = [
            $this->getCollection(true),
            $this->getKey(true),
            'relations',
        ];
        $path = array_merge($path, $this->getDepth(true));

        $parameters = ['limit' => $limit];

        if ($offset) {
            $parameters['offset'] = $offset;
        }

        // request
        $this->request('GET', $path, ['query' => $parameters]);

        if ($this->isSuccess()) {
            $this->setResponseValues();
        }
        return $this->isSuccess();
    }

    public function search($query, $sort = null, $aggregate = null, $limit = 10, $offset = 0)
    {
        // define request options
        $queryParts = ['@path.kind:'.Relationship::KIND];
        if (!empty($this->_key)) {
            $queryParts[] = '@path.key:'.$this->_key;
        }
        if (!empty($this->_depth)) {
            $queryParts[] = '@path.relation:'.implode('/', $this->_depth);
        }
        if ($query) {
            $queryParts[] = $query;
        }

        $parameters = [
            'query' => implode(' AND ', $queryParts),
            'limit' => $limit,
        ];
        if (!empty($sort)) {
            $parameters['sort'] = implode(',', (array) $sort);
        }
        if (!empty($aggregate)) {
            $parameters['aggregate'] = implode(',', (array) $aggregate);
        }
        if ($offset) {
            $parameters['offset'] = $offset;
        }

        // request
        $this->request('GET', [$this->_collection], ['query' => $parameters]);

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

            if ($kind === Relationship::KIND) {
                $class = $this->getRelationshipClass();

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
