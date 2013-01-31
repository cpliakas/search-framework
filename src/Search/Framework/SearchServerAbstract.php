<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Abstract class extended by search backend libraries.
 */
abstract class SearchServerAbstract
{
    /**
     * The event dispatcher used by this search server to throw events.
     *
     * @var EventDispatcher
     */
    protected $_dispatcher;

    /**
     * An array of collections that are associated with this search server.
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
     * Deletes all indexed data on the search server.
     */
    abstract public function delete();

    /**
     * Returns a search index document object specific to the extending backend.
     *
     * @return SearchIndexDocument
     */
    public function newDocument()
    {
        return new SearchIndexDocument($this);
    }

    /**
     * Returns a search index field object specific to the extending backend.
     *
     * @param string $id
     *   The unique identifier of the field that the index name defaults to.
     * @param string|array $value
     *   The field's value extracted form the source text.
     * @param string|null $name
     *   The name of this field as stored in the index, defaults to null which
     *   uses the unique identifier.
     *
     * @return SearchIndexField
     */
    public function newField($id, $value, $name = null)
    {
        return new SearchIndexField($id, $value, $name);
    }

    /**
     * Sets the event dispatcher used by this search server to throw events.
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
     * Sets the event dispatcher used by this search server to throw events.
     *
     * If no event dispatcher is set, one is instantiated automatically.
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
     * Associates a collection with this search server.
     *
     * @param SearchCollectionAbstract $collection
     *   The collection being associated with this search server.
     *
     * @return SearchServerAbstract
     */
    public function addCollection(SearchCollectionAbstract $collection)
    {
        $this->_collections[] = $collection;
        return $this;
    }

    /**
     * Returns all collections associated with this search server.
     *
     * @return array
     */
    public function getCollections()
    {
        return $this->_collections;
    }

    /**
     * Iterates over all collections associated with this search server and
     * processes the items enqueued for indexing.
     *
     * @param int|null $limit
     *   The maximum number of items to process, defaults to null which uses the
     *   default setting.
     *
     * @see SearchCollectionAbstract::index()
     */
    public function index($limit = SearchCollectionQueue::NO_LIMIT)
    {
        foreach ($this->_collections as $collection) {
            $collection->index($this, $limit);
        }
    }
}
