<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate as Orchestrate;
use andrefelipe\Orchestrate\Contracts\ItemInterface;
use andrefelipe\Orchestrate\Contracts\ListInterface;
use GuzzleHttp\ClientInterface;
use JmesPath\Env as JmesPath;

abstract class AbstractList extends AbstractConnection implements ListInterface
{
    use Properties\KindTrait;
    use Properties\ToJsonTrait;

    /**
     * @var array
     */
    protected $_results = null;

    /**
     * @var int
     */
    protected $_totalCount = null;

    /**
     * @var string
     */
    protected $_nextUrl = '';

    /**
     * @var string
     */
    protected $_prevUrl = '';

    /**
     * Set the client which this object, and all of its children,
     * will use to make Orchestrate API requests.
     *
     * @param ClientInterface $httpClient
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        parent::setHttpClient($httpClient);

        foreach ($this->getResults() as $item) {
            if ($item instanceof ConnectionInterface) {
                $item->setHttpClient($httpClient);
            }
        }
        return $this;
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getResults()[$offset];
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->getResults()[$offset] = $value;
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $this->getResults()[$offset] = null;
    }

    /**
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->getResults()[$offset]);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getResults());
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->getResults());
    }

    public function reset()
    {
        parent::reset();
        $this->_totalCount = null;
        $this->_nextUrl = '';
        $this->_prevUrl = '';
        $this->_results = null;
    }

    public function init(array $data)
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {

                if ($key === 'total_count') {
                    $this->_totalCount = (int) $value;

                } elseif ($key === 'prev') {
                    $this->_prevUrl = $value;

                } elseif ($key === 'next') {
                    $this->_nextUrl = $value;

                } elseif ($key === 'results') {
                    $this->_results = array_map(
                        [$this, 'createInstance'],
                        $value
                    );
                }
            }
        }
        return $this;
    }

    public function toArray()
    {
        $data = [
            'kind' => 'list',
            'count' => count($this),
            'results' => $this->getResults(),
        ];

        if ($this->_totalCount !== null) {
            $data['total_count'] = $this->_totalCount;
        }
        if ($this->_nextUrl) {
            $data['next'] = $this->_nextUrl;
        }
        if ($this->_prevUrl) {
            $data['prev'] = $this->_prevUrl;
        }

        return $data;
    }

    public function extract($expression)
    {
        return JmesPath::search($expression, $this->toArray());
    }

    public function extractValues($expression)
    {
        return JmesPath::search($expression, $this->getValues());
    }

    public function getValues()
    {
        $values = [];
        foreach ($this->getResults() as $item) {
            if ($item instanceof ItemInterface) {
                $values[] = $item->getValue();
            }
        }
        return $values;
    }

    public function getResults()
    {
        if (!$this->_results) {
            $this->_results = [];
        }
        return $this->_results;
    }

    public function mergeResults(ListInterface $list)
    {
        Orchestrate\merge_object($list->getResults(), $this->_results);
        return $this;
    }

    /**
     * @return string
     */
    public function serialize()
    {
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

    public function getTotalCount()
    {
        return $this->_totalCount;
    }

    public function getNextUrl()
    {
        return $this->_nextUrl;
    }

    public function getPrevUrl()
    {
        return $this->_prevUrl;
    }

    public function nextPage()
    {
        if ($this->_nextUrl) {
            $this->request('GET', $this->_nextUrl);

            if ($this->isSuccess()) {
                $this->setResponseValues();
            }
            return $this->isSuccess();
        }
        return false;
    }

    public function prevPage()
    {
        if ($this->_prevUrl) {
            $this->request('GET', $this->_prevUrl);

            if ($this->isSuccess()) {
                $this->setResponseValues();
            }
            return $this->isSuccess();
        }
        return false;
    }

    /**
     * Helper method to set instance values according to current response.
     */
    protected function setResponseValues()
    {
        // reset local properties
        $this->_results = null;
        $this->_nextUrl = '';
        $this->_prevUrl = '';

        // set properties
        if ($body = $this->getBody()) {
            if (!empty($body['results'])) {
                $this->_results = array_map(
                    [$this, 'createInstance'],
                    $body['results']
                );
            }
            if (isset($body['total_count'])) {
                $this->_totalCount = (int) $body['total_count'];
            }
            if (!empty($body['next'])) {
                $this->_nextUrl = $body['next'];
            }
            if (!empty($body['prev'])) {
                $this->_prevUrl = $body['prev'];
            }
        }
    }
}
