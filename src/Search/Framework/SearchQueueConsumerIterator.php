<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * Fetches messages from the queue as long as there are messages to fetch and
 * the collection's limit and timeout thresholds haven't been exceeded.
 */
class SearchQueueConsumerIterator implements \Iterator
{
    /**
     * The queue object that is consuming an indexing queue.
     *
     * @var SearchQueueAbstract
     */
    protected $_queue;

    /**
     * The unix timestamp containing the timeout threshold.
     *
     * @var int
     */
    protected $_timeout;

    /**
     * The maximum number of documents that are allowed to be fetched from the
     * queue. This value is set in the collection object that contains the items
     * scheduled for indexing enqueued in this queue.
     *
     * @var int
     */
    protected $_limit;

    /**
     * The counter for the number of items fetched that is used as the key.
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
     * Constructs a SearchQueueConsumerIterator object.
     *
     * @param SearchQueueAbstract $queue
     *   The queue object that is consuming an indexing queue.
     */
    public function __construct(SearchQueueAbstract $queue)
    {
        $this->_queue = $queue;
    }

    /**
     * Checks whether the operation has timed out.
     *
     * @return boolean
     */
    public function timedOut()
    {
        return (SearchCollectionAbstract::NO_LIMIT == $this->_timeout || time() > $this->_timeout);
    }

    /**
     * Checks whether the operation has timed out.
     *
     * @return boolean
     */
    public function limitExceeded()
    {
        return (SearchCollectionAbstract::NO_LIMIT == $this->_limit || $this->_count > $this->_limit);
    }

    /**
     * Implements \Iterator::rewind().
     *
     * Resets the counter, timeout, and limit.
     */
    public function rewind()
    {
        $this->_count = 0;

        $this->_timeout = $this->_queue->getCollection()->getTimeout();
        if ($this->_timeout != SearchCollectionAbstract::NO_LIMIT) {
            $this->_timeout += time();
        }

        $this->_limit = $this->_queue->getCollection()->getLimit();
    }

    /**
     * Implements \Iterator::valid().
     *
     * If we are still within the thresholds specified in the collector, fetch
     * a message from the queue.
     */
    public function valid()
    {
        if ($this->timedOut() || $this->limitExceeded()) {
            return false;
        }
        $this->_currentMessage = $this->_queue->consume();
        return $this->_currentMessage !== false;
    }

    /**
     * Implements \Iterator::current().
     *
     * @return SearchQueueMessage
     */
    public function current()
    {
        $this->_queue->addConsumedMessage($this->_currentMessage);
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
