<?php

/**
 * Search Framework
 *
 * @author    Chris Pliakas <opensource@chrispliakas.com>
 * @copyright 2013 Chris Pliakas
 * @license   http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License (LGPL)
 * @link      https://github.com/cpliakas/search-framework
 */

namespace Search\Framework;

/**
 * Fetches items from the queue that are scheduled for indexing.
 */
class QueueConsumer extends QueueWorkerAbstract
{
    /**
     * Constructs a QueueConsumer object.
     *
     * @param CollectionAgentAbstract $agent
     *   The agent that is performing the queuing operations.
     */
    public function __construct(CollectionAgentAbstract $agent)
    {
        $this->_agent = $agent;
    }

    /**
     * Checks whether the operation has timed out.
     *
     * @return boolean
     *
     * @todo Store limit as a class property to eliminate method calls?
     */
    public function limitExceeded()
    {
        $limit = $this->_agent->getLimit();
        return $limit != CollectionAgentAbstract::NO_LIMIT && $this->_count > $this->_agent->getLimit();
    }

    /**
     * Implements \Iterator::valid().
     *
     * Fetch a message from the queue if we are still within the timeout and
     * limit thresholds specified in the SearchCollectionAbstract instance.
     *
     * @todo Store queue as a class property to eliminate method calls?
     */
    public function valid()
    {
        if ($this->timedOut() || $this->limitExceeded()) {
            return false;
        }
        $this->_currentMessage = $this->_agent->getQueue()->consume();
        return $this->_currentMessage !== false;
    }

    /**
     * Implements \Iterator::current().
     *
     * @return SearchQueueMessage
     */
    public function current()
    {
        $this->_agent->getQueue()->attachConsumedMessage($this->_currentMessage);
        return $this->_currentMessage;
    }
}
