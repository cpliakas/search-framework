<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * Used to iterate over collections of data that are published to or consumed
 * from a queue.
 *
 * This iterator passes the item to appropriate message building method in the
 * SearchCollectionAbstract instance which converts the items to queue message
 * objects.
 *
 * The main benefit of this iterator is that it checks whether the operation is
 * within the thresholds set in the constructor. This means that a developer
 * can iterate over this collection in a foreach loop without having to
 * implement any logic to check whether the operation has exceeded the limit
 * or timeout thresholds.
 */
abstract class SearchQueueIteratorAbstract implements \Iterator
{
    /**
     *
     * @var SearchCollectionAbstract
     */
    protected $_collection;
    
    /**
     * The queue object set for the collection.
     *
     * @var SearchQueueAbstract
     */
    protected $_queue;

    /**
     *
     * @var int
     */
    protected $_timeout;

    /**
     *
     * @var int
     */
    protected $_limit;

    /**
     *
     * @var int
     */
    protected $_count;

    /**
     * The current message fetched from the queue.
     *
     * @var SearchQueueMessage
     */
    protected $_currentMessage;

    /**
     * Constructs a SearchQueueIteratorAbstract object.
     *
     * @param SearchQueueCollection $collection
     *   The queue object that is consuming an indexing queue.
     */
    public function __construct(SearchCollectionAbstract $collection)
    {
        $this->_collection = $collection;
        $this->_queue = $collection->getQueue();
    }

    /**
     * Checks whether the operation has timed out.
     *
     * NOTE: `time() >= $this->_timeout` trips me up every time, but this method
     * returns true if the operation has timed out. Therefore it will correctly
     * return true when `time()` is greater than `$this->_timeout`.
     *
     * @return boolean
     */
    public function timedOut()
    {
        return (SearchCollectionAbstract::NO_LIMIT != $this->_timeout && time() >= $this->_timeout);
    }

    /**
     * Checks whether the operation has timed out.
     *
     * @return boolean
     */
    public function limitExceeded()
    {
        return (SearchCollectionAbstract::NO_LIMIT != $this->_limit && $this->_count > $this->_limit);
    }

    /**
     * Implements \Iterator::rewind().
     *
     * Resets the counter, timeout, and limit.
     */
    public function rewind()
    {
        $this->_count = 0;

        $this->_timeout = $this->_collection->getTimeout();
        if ($this->_timeout != SearchCollectionAbstract::NO_LIMIT) {
            $this->_timeout += time();
        }

        $this->_limit = $this->_collection->getLimit();
    }

    /**
     * Implements \Iterator::valid().
     *
     * Checks whether we are within the thresholds set in the collection and
     * either fetches an item from the index scheduler or consumes it from the
     * queue. This method also invokes the appropriate
     */
    abstract public function valid();

    /**
     * Implements \Iterator::current().
     *
     * @return SearchQueueMessage
     */
    public function current()
    {
        return $this->_currentMessage;
    }

    /**
     * Implements \Iterator::key().
     *
     * Returns the current value of the counter.
     */
    public function key()
    {
        return $this->_count;
    }

    /**
     * Implements \Iterator::next().
     */
    public function next()
    {
        ++$this->_count;
        unset($this->_currentMessage);
    }
}
