<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Collection;

/**
 * In this instance, a queue is simply a collection that can be iterated over
 * using `foreach()`.
 */
class SearchCollectionQueue
{
    /**
     * The items queued for indexing.
     *
     * @var \Traversable|array
     */
    protected $_items;

    /**
     * Constructs a SearchCollectionQueue object.
     *
     * @param \Traversable|array $items
     *   The items enqueued for indexing.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($items)
    {
        if (!is_array($items) && !$items instanceof \Traversable) {
            throw new \InvalidArgumentException('Items must be an array or traversable object.');
        }
        $this->_items = $items;
    }

    /**
     * Returns the items enqueued for indexing.
     *
     * @return \Traversable|array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Proceses the items queued for indexing.
     *
     * @param SearchServerAbstract $server
     *   The search server that is indexing the datasource.
     * @param SearchCollectionAbstract $collection
     *   The collection containing the datasource being indexed.
     */
    public function processQueue(SearchServerAbstract $server, SearchCollectionAbstract $collection)
    {
        foreach ($this->_items as $item) {
            $document = $collection->loadDocument($item);
            $server->indexDocument($document);
        }
    }
}
