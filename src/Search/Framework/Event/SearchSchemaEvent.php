<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework\Event;

use Search\Framework\SearchCollectionAbstract;
use Search\Framework\SearchCollectionSchema;
use Search\Framework\SearchServerAbstract;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for schema related events.
 */
class SearchSchemaEvent extends Event
{
    /**
     * The search server that is indexing the collection.
     *
     * @var SearchServerAbstract
     */
    protected $_server;

    /**
     * The collection that the schema is associated with.
     *
     * @var SearchCollectionAbstract
     */
    protected $_collection;

    /**
     * The collection's schema.
     *
     * @var SearchCollectionSchema
     */
    protected $_schema;

    /**
     * Constructs a SearchSchemaEvent object.
     *
     * @param SearchServerAbstract $server
     *   The search server that is indexing the collection.
     * @param SearchCollectionAbstract $collection
     *   The collection that the schema is associated with.
     * @param SearchCollectionSchema $schema
     *   The collection's schema.
     */
    public function __construct(SearchServerAbstract $server, SearchCollectionAbstract $collection, SearchCollectionSchema $schema)
    {
        $this->_server = $server;
        $this->_collection = $collection;
        $this->_schema = $schema;
    }

    /**
     * Returns the search server that is indexing the collection.
     *
     * @return SearchServerAbstract
     */
    public function getServer()
    {
        return $this->_server;
    }

    /**
     * Returns the collection being indexed by the search server.
     *
     * @return SearchCollectionAbstract
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Returns the collection's schema.
     *
     * @return SearchCollectionSchema
     */
    public function getSchema()
    {
        return $this->_server;
    }
}
