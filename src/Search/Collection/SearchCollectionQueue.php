<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Collection;

use Search\Server\SearchServerAbstract;

/**
 * In this instance, a queue is simply a collection that can be iterated over
 * using `foreach()`.
 */
class SearchCollectionQueue
{
    /**
     * Flags that there is no limit of the number of documents processed when
     * running the queue.
     */
    const NO_LIMIT = -1;

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
        $dispatcher = $server->getDispatcher();
        foreach ($this->_items as $item) {
            $document = $server->getDocument();

            // $dispatcher->dispatch(); search.document.initialize

            $collection->buildDocument($document, $item);

            // $dispatcher->dispatch(); search.document.preIndex

            $server->indexDocument($document);

            // $dispatcher->dispatch(); search.document.postIndex
        }
    }
}
