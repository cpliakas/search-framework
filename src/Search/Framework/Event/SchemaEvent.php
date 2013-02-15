<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework\Event;

use Search\Framework\CollectionAbstract;
use Search\Framework\CollectionAgentAbstract;
use Search\Framework\Schema;

/**
 * Event object for config related events.
 */
class SchemaEvent extends SearchEvent
{
    /**
     *
     * @var CollectionAbstract
     */
    protected $_collection;

    /**
     *
     * @var Schema
     */
    protected $_schema = array();

    /**
     * Constructs a SearchConfigEvent object.
     *
     * @param CollectionAgentAbstract $agent
     *
     * @param CollectionAbstract $collection
     *
     */
    public function __construct(CollectionAgentAbstract $agent, CollectionAbstract $collection, Schema $schema)
    {
        $this->_agent = $agent;
        $this->_collection = $collection;
        $this->_schema = $schema;
    }

    /**
     *
     *
     * @return CollectionAbstract
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     *
     *
     * @return Schema
     */
    public function getSchema()
    {
        return $this->_schema;
    }
}
