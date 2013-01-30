<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework\Server;

use Search\Framework\Collection\SearchCollectionAbstract;
use Search\Framework\Collection\SearchCollectionQueue;
use Search\Framework\Index\SearchIndexDocument;
use Search\Framework\Index\SearchIndexField;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Abstract class extended by search backend libraries.
 */
abstract class SearchServerAbstract
{
    /**
     * The event dispatcher used by this collection to throw events.
     *
     * @var EventDispatcher
     */
    protected $_dispatcher;

    /**
     * An array of collections indexed by this server.
     *
     * @var array
     */
    protected $_collections = array();

    /**
     * Processes a document for indexing.
     *
     * @param SearchIndexDocument $document
     *   The document being indexed.
     */
    abstract public function indexDocument(SearchIndexDocument $document);

    /**
     * Returns a search index document object for this backend.
     *
     * @return SearchIndexDocument
     */
    public function getDocument()
    {
        return new SearchIndexDocument($this);
    }

    /**
     * Returns a search index field object for this backend.
     *
     * @return SearchIndexField
     */
    public function getField()
    {
        return new SearchIndexField();
    }

    /**
     * Sets the event dispatcher used by this collection to throw events.
     *
     * @param EventDispatcher $dispatcher
     *   The event dispatcher.
     *
     * @return SearchServerAbstract
     */
    public function setDispatcher(EventDispatcher $dispatcher)
    {
        $this->_dispatcher = $dispatcher;
        return $this;
    }

    /**
     * Sets the event dispatcher used by this collection to throw events.
     *
     * If no event dispatcher is set, then one is instantiated automatically.
     *
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        if (!isset($this->_dispatcher)) {
            $this->_dispatcher = new EventDispatcher();
        }
        return $this->_dispatcher;
    }

    /**
     * Associates a collection with this server.
     *
     * @param SearchCollectionAbstract $collection
     *   The collection being associated with this server.
     *
     * @return SearchServerAbstract
     */
    public function addCollection(SearchCollectionAbstract $collection)
    {
        $this->_collections[] = $collection;
        return $this;
    }

    /**
     * Returns all collections indexed by this server.
     *
     * @return array
     */
    public function getCollections()
    {
        return $this->_collections;
    }

    /**
     * Indexes all items enqueued for indexing in all colections associated with
     * this server.
     *
     * @param int|null $limit
     *   The maximum number of items to process, defaults to null which uses the
     *   default setting.
     */
    public function index($limit = SearchCollectionQueue::NO_LIMIT)
    {
        foreach ($this->_collections as $collection) {
            $collection->index($this, $limit);
        }
    }
}
