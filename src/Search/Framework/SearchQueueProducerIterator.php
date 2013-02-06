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
     * An iterator containing the items that are scheduled for indexing.
     *
     * @var \Iterator
     */
    protected $_scheduledItems;

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
                $this->_collection->buildQueueMessage($message, $item);
                $this->_currentMessage = $message;
                return true;
            }
        }
        return false;
    }
}
