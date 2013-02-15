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
abstract class QueueWorkerAbstract implements \Iterator, \Countable
{
    /**
     * The collection agent performing the queuing operations.
     *
     * @var CollectionAgentAbstract
     */
    protected $_agent;

    /**
     * The counter used to determine how many items have been processed.
     *
     * @var int
     */
    protected $_count = 0;

    /**
     * The Unix timestamp at which the operation is considered to have timed
     * out.
     *
     * @var int
     */
    protected $_expiry = 0;

    /**
     * The current message fetched from the queue.
     *
     * @var QueueMessage
     */
    protected $_currentMessage;

    /**
     * Returns the collection agent that is performing the queuing operation.
     *
     * @return CollectionAgent
     */
    public function getCollectionAgent()
    {
        return $this->_agent;
    }

    /**
     * Checks whether the operation has timed out.
     *
     * NOTE: `time() >= $this->_expiry` trips me up every time, but this method
     * returns true if the operation has timed out. Therefore it will correctly
     * return true when `time()` is greater than `$this->_expiry`.
     *
     * @return boolean
     */
    public function timedOut()
    {
        return time() >= $this->_expiry;
    }

    /**
     * Implements \Iterator::rewind().
     *
     * Resets the counter, timeout, and limit.
     */
    public function rewind()
    {
        $this->_count = 0;

        $timeout = $this->_agent->getTimeout();
        if ($timeout != CollectionAgentAbstract::NO_LIMIT) {
            $this->_expiry = $timeout + time();
        } else {
            // Pay homage to the Unix time apocalypse.
            // @see http://en.wikipedia.org/wiki/Year_2038_problem
            $this->_expiry = 2147483647;
        }
    }

    /**
     * Implements \Iterator::valid().
     *
     * Checks whether we are within the limit and timeout thresholds set in the
     * collection agent and either fetches an item scheduled for indexing or
     * consumes it from the queue.
     *
     * If an item is being fetched from the collection, it must be converted
     * into an QueueMessage object and set as the
     * QueueWorkerAbstract::_currentMessage property.
     */
    abstract public function valid();

    /**
     * Implements \Iterator::current().
     *
     * @return QueueMessage
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
     *
     * Increments the counter and unsets the current queue message.
     */
    public function next()
    {
        ++$this->_count;
        unset($this->_currentMessage);
    }

    /**
     * Implements \Countable::count().
     *
     * This method returns the number of items published to the queue, NOT the
     * number of items scheduled for indexing. For example, if there are 50
     * items scheduled for indexing and the object is counted before it is
     * iterated over, it will return 0.
     */
    public function count()
    {
        return $this->_count;
    }
}
