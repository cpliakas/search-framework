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
class SearchQueueConsumerIterator extends SearchQueueIteratorAbstract
{
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
    protected $_limit;

    /**
     * Constructs a SearchQueueConsumerIterator object.
     *
     * @param SearchQueueAbstract $queue
     *   The queue object that is consuming the queue.
     */
    public function __construct(SearchQueueAbstract $queue)
    {
        $this->_queue = $queue;
        $this->_limit = $queue->getLimit();
        $this->_timeout = $queue->getLimit();
    }

    /**
     * Checks whether the operation has timed out.
     *
     * @return boolean
     */
    public function limitExceeded()
    {
        return $this->_count > $this->_limit;
    }

    /**
     * Implements \Iterator::valid().
     *
     * Fetch a message from the queue if we are still within the timeout and
     * limit thresholds specified in the SearchCollectionAbstract instance.
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
        $this->_queue->attachConsumedMessage($this->_currentMessage);
        return $this->_currentMessage;
    }
}
