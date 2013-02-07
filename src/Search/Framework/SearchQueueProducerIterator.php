<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * Iterates over the items that are scheduled for indexing and converts the
 * items to messages objects that can be published to the queue.
 */
class SearchQueueProducerIterator extends SearchQueueIteratorAbstract
{
    /**
     * The queue object set for the collection.
     *
     * @var SearchQueueAbstract
     */
    protected $_queue;

    /**
     *
     * @var SearchCollectionAbstract
     */
    protected $_collection;

    /**
     * An iterator containing the items that are scheduled for indexing.
     *
     * @var \Iterator
     */
    protected $_scheduledItems;

    /**
     * Constructs a SearchQueueIteratorAbstract object.
     *
     * @param SearchQueueAbstract $queue
     *   The queue object interacting with the broker.
     * @param SearchQueueCollection $collection
     *   The queue object that is consuming an indexing queue.
     */
    public function __construct(SearchQueueAbstract $queue, SearchCollectionAbstract $collection)
    {
        $this->_queue = $queue;
        $this->_collection = $collection;

        $this->_timeout = $collection->getTimeout();
    }

    /**
     * Overrides SearchQueueIteratorAbstract::rewind().
     *
     * Resets the counter, timeout, and limit.
     */
    public function rewind()
    {
        parent::rewind();
        $this->_scheduledItems = $this->_collection->fetchScheduledItems();
    }

    /**
     * Implements SearchQueueIteratorAbstract::valid().
     *
     * Fetches an item that is scheduled for indexing, populates a message
     * object that can be sent to the index queue.
     *
     * We only have to check the timeout because the document limit should have
     * been taken care of in the SearchCollectionAbstract::fetchScheduledItems()
     * method.
     */
    public function valid()
    {
        if (!$this->timedOut()) {
            list(, $item) = each($this->_scheduledItems);
            if ($item !== null) {
                $message = $this->_queue->newMessage();
                $message->setCollection($this->_collection);
                $this->_collection->buildQueueMessage($message, $item);
                $this->_currentMessage = $message;
                return true;
            }
        }
        return false;
    }
}
