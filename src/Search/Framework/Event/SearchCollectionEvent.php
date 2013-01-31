<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework\Event;

use Search\Framework\SearchCollectionAbstract;
use Search\Framework\SearchServerAbstract;
use Search\Framework\SearchCollectionQueue;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for collection related events.
 */
class SearchCollectionEvent extends Event
{
    /**
     * The search server that is indexing the collection.
     *
     * @var SearchServerAbstract
     */
    protected $_server;

    /**
     * The collection being indexed by the search server.
     *
     * @var SearchCollectionAbstract
     */
    protected $_collection;

    /**
     * The queue containing the items that are enqueued for indexing.
     *
     * @var SearchCollectionQueue
     */
    protected $_queue;

    /**
     * Constructs a SearchCollectionEvent object.
     *
     * @param SearchServerAbstract $server
     *   The search server that is indexing the collection.
     * @param SearchCollectionAbstract $collection
     *   The collection being indexed by the search server.
     * @param SearchCollectionQueue $queue
     *   The queue containing the items that are enqueued for indexing.
     */
    public function __construct(SearchServerAbstract $server, SearchCollectionAbstract $collection, SearchCollectionQueue $queue)
    {
        $this->_server = $server;
        $this->_collection = $collection;
        $this->_queue = $queue;
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
     * Returns the queue containing the items that are enqueued for indexing.
     *
     * @return SearchCollectionQueue
     */
    public function getQueue()
    {
        return $this->_queue;
    }
}
