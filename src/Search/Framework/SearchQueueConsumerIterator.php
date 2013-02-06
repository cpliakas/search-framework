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
