<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\KeyTrait;
use andrefelipe\Orchestrate\Common\KindTrait;

class Graph extends AbstractList
{
    use KeyTrait;
    use KindTrait; // TODO review other clients, 'kind' feels a bad naming, 'type' also

    public function __construct($collection, $key = null, $kind = null)
    {
        parent::__construct($collection);
        $this->setKey($key);
        $this->setKind($kind);
    }

    /**
     * @param int $limit
     * @param int $offset
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-get
     */
    public function listRelations($limit = 10, $offset = 0)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true)
            .'/relations/'.implode('/', $this->getKind(true));

        $parameters = ['limit' => $limit];
        
        if ($offset) {
            $parameters['offset'] = $offset;
        }            
       
        // request
        $this->request('GET', $path, ['query' => $parameters]);

        return $this->isSuccess();
    }
}
