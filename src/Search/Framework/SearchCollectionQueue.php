<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * Contains a list of items enqueued for indexing.
 *
 * In this instance, a queue is simply a collection that can be iterated over
 * using `foreach()`. It is not a true queue in that items cannot be claimed,
 * released, or deleted. However, the queue can be extended to act as a worker
 * class that interacts with a traditional queue.
 */
class SearchCollectionQueue implements \IteratorAggregate
{
    /**
     * The items enqueued for indexing.
     *
     * @var mixed
     */
    protected $_items;

    /**
     * Constructs a SearchCollectionQueue object.
     *
     * @param mixed
     *   The items enqueued for indexing. When passing this value to the default
     *   queue object, it should be an array, an iterator, or traversable.
     */
    public function __construct($items)
    {
        $this->_items = $items;
    }

    /**
     * Implements IteratorAggregate::getIterator().
     *
     * @throws \InvalidArgumentException
     *   Thrown when the items cannot be converted to an iterator.
     */
    public function getIterator()
    {
        $items = $this->getItems();
        switch (true) {
          case is_array($items):
            return new \ArrayIterator($items);

          case $items instanceof \Iterator:
            return new $items;

          case $items instanceof \Traversable:
            return new \IteratorIterator($items);

          default:
            $message = 'Enqueued items must be an array, an iterator, or traversable.';
            throw new \InvalidArgumentException($message);
        }
    }

    /**
     * Returns the items enqueued for indexing.
     *
     * If the items are not already an array, an iterator, or traversable, then
     * this is the queue's chance to ensure the items are one of the expected
     * data types.
     *
     * @return \Iterator|\Traversable|array
     */
    public function getItems()
    {
        return $this->_items;
    }
}
