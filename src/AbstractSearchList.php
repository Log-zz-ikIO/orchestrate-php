<?php
namespace andrefelipe\Orchestrate;

/**
 * Adds the aggregate support to List objects that provides search support.
 */
abstract class AbstractSearchList extends AbstractList
{
    use Properties\AggregatesTrait;

    public function reset()
    {
        parent::reset();
        $this->_aggregates = null;
    }

    public function init(array $data)
    {
        if (!empty($data)) {

            if (!empty($data['aggregates'])) {
                $this->_aggregates = $data['aggregates'];
            }

            parent::init($data);
        }
        return $this;
    }

    public function toArray()
    {
        $data = parent::toArray();

        if ($this->_aggregates) {
            $data['aggregates'] = $this->_aggregates;
        }

        return $data;
    }

    /**
     * Adds aggregates support.
     */
    protected function setResponseValues()
    {
        parent::setResponseValues();

        if ($this->isSuccess()) {
            $body = $this->getBody();
            if (!empty($body['aggregates'])) {
                $this->_aggregates = $body['aggregates'];
            } else {
                $this->_aggregates = null;
            }
        }
    }

    /**
     * Helper method to get item count from Orchestrate,
     *
     * @param string $path Base path, either null or collection name.
     * @param string $kind Item kind.
     * @param string $type Event type.
     * @param string $relation Relation type.
     *
     * @return null|int Null on failure, item count on success.
     */
    protected function getItemCount($path, $kind, $type = null, $relation = null)
    {
        // makes a straight Search query for no results
        $query = '@path.kind:'.$kind;
        if ($type) {
            $query .= ' AND @path.type:'.$type;
        }
        if ($relation) {
            $query .= ' AND @path.relation:'.$relation;
        }

        $parameters = [
            'query' => $query,
            'limit' => 0,
        ];
        $http_query = http_build_query($parameters, null, '&', PHP_QUERY_RFC3986);

        $response = $this->getHttpClient()
            ->request('GET', $path, ['query' => $http_query]);

        $body = json_decode($response->getBody(), true);

        // return value if successful
        if (isset($body['total_count'])) {
            return (int) $body['total_count'];
        }

        return null;
    }
}
