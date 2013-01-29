<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Server;

use Search\Collection\SearchCollectionAbstract;
use Search\Collection\SearchCollectionQueue;

/**
 *
 */
abstract class SearchServerAbstract
{
    /**
     * An array of SearchCollectionAbstract objects keyed by machine name.
     *
     * @var array
     */
    protected $_collections = array();

    /**
     * The limit
     *
     * @var array
     */
    protected $_limit = SearchCollectionQueue::NO_LIMIT;

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
     * @param int $limit
     *   The maximum number
     */
    public function index()
    {
        foreach ($this->_collections as $name => $collection) {
            $collection->index($this, $limit);
        }
    }
}
