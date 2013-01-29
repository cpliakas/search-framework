<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Server;

use Search\Collection\SearchCollectionAbstract;
use Search\Collection\SearchCollectionQueue;
use Search\Index\SearchIndexDocument;
use Search\Index\SearchIndexField;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 *
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
     * An array of SearchCollectionAbstract objects keyed by machine name.
     *
     * @var array
     */
    protected $_collections = array();

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
     * Add or replace a collection.
     *
     * @param string $name
     *   The machine name of the collection.
     * @param SearchCollectionAbstract $collection
     *   The collection being associated with this server.
     *
     * @return SearchServerAbstract
     */
    public function addCollection($name, SearchCollectionAbstract $collection)
    {
        $this->_collections[$name] = $collection;
        return $this;
    }

    /**
     * Disassociates a collection form this server.
     *
     * @param string $name
     *   The machine name of the collection.
     *
     * @return SearchServerAbstract
     */
    public function removeCollection($name)
    {
        unset($this->_collections[$name]);
        return $this;
    }

    /**
     * Returns a collection given its machine name.
     *
     * @param string $name
     *   The machine name of the collection.
     *
     * @return SearchCollectionAbstract
     *
     * @throws \InvalidArgumentException
     */
    public function getCollection($name)
    {
        if (!isset($this->_collections[$name])) {
            throw new \InvalidArgumentException('Collection "' . $name . '" is not associated with this server.');
        }
        return $this->_collections[$name];
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
        foreach ($this->_collections as $name => $collection) {
            $collection->index($this, $limit);
        }
    }
}
