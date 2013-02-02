<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework\Event;

use Search\Framework\SearchCollectionAbstract;
use Search\Framework\SearchCollectionQueue;
use Search\Framework\SearchServiceAbstract;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for collection related events.
 */
class SearchCollectionEvent extends Event
{
    /**
     * The search service that is indexing the collection.
     *
     * @var SearchServiceAbstract
     */
    protected $_service;

    /**
     * The collection being indexed by the search service.
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
     * @param SearchServiceAbstract $service
     *   The search service that is indexing the collection.
     * @param SearchCollectionAbstract $collection
     *   The collection being indexed by the search service.
     * @param SearchCollectionQueue $queue
     *   The queue containing the items that are enqueued for indexing.
     */
    public function __construct(SearchServiceAbstract $service, SearchCollectionAbstract $collection, SearchCollectionQueue $queue)
    {
        $this->_service = $service;
        $this->_collection = $collection;
        $this->_queue = $queue;
    }

    /**
     * Returns the search service that is indexing the collection.
     *
     * @return SearchServiceAbstract
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * Returns the collection being indexed by the search service.
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
