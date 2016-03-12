<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate as Orchestrate;
use andrefelipe\Orchestrate\Contracts\ApplicationInterface;

/**
 *
 * @link https://orchestrate.io/docs/apiref
 */
class Application extends AbstractSearchList implements ApplicationInterface
{
    /**
     * If you provide any parameters if will instantiate a HTTP client on construction.
     * Otherwise it will create one when required.
     *
     * @param string $apiKey Orchestrate API key. If not set gets from env 'ORCHESTRATE_API_KEY'.
     * @param string $host Orchestrate API host. Defaults to 'https://api.orchestrate.io'
     * @param string $version Orchestrate API version. Defaults to 'v0'
     */
    public function __construct($apiKey = null, $host = null, $version = null)
    {
        // lazily instantiante
        if ($apiKey || $host || $version) {
            $client = Orchestrate\default_http_client($apiKey, $host, $version);
            $this->setHttpClient($client);
        }
    }

    public function ping()
    {
        return $this->getHttpClient()->request('HEAD')->getStatusCode() === 200;
    }

    /**
     * Creates a new application instance configured with the same Http client.
     * Useful to create different instances to populate with different search results.
     *
     * @return Application
     */
    public function application()
    {
        return (new Application())
            ->setHttpClient($this->getHttpClient());
    }

    public function collection($name)
    {
        return (new Collection())
            ->setCollection($name)
            ->setHttpClient($this->getHttpClient());
    }

    public function getTotalItems()
    {
        return $this->getItemCount(null, KeyValue::KIND);
    }

    public function getTotalEvents($type = null)
    {
        return $this->getItemCount(null, Event::KIND, $type);
    }

    public function getTotalRelationships($type = null)
    {
        return $this->getItemCount(
            null,
            Relationship::KIND,
            null,
            $type
        );
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['kind'] = static::KIND;

        return $data;
    }

    public function search($query, $sort = null, $aggregate = null, $limit = 10, $offset = 0)
    {
        // define request options
        $parameters = [
            'query' => $query,
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
        $this->request('GET', null, ['query' => $parameters]);

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
                $item = (new KeyValue())->init($itemValues);

            } elseif ($kind === Event::KIND) {
                $item = (new Event())->init($itemValues);

            } elseif ($kind === Relationship::KIND) {
                $item = (new Relationship())->init($itemValues);

            } else {
                return null;
            }

            if ($client = $this->getHttpClient()) {
                $item->setHttpClient($client);
            }
            return $item;
        }
        return null;
    }
}
