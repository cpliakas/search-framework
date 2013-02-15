<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * Publishes items scheduled for indexing to the indexing queue.
 */
class QueueProducer extends QueueWorkerAbstract
{
    /**
     * The collection that fetches the items scheduled for indexing.
     *
     * @var CollectionAbstract
     */
    protected $_collection;

    /**
     * An iterator containing the items that are scheduled for indexing.
     *
     * This iterator is instantiated in the QueueProducerIterator::rewind()
     * method via the CollectionAbstract::fetchScheduledItems() method. This
     * architecture allows us to fetch a new batch of items by iterating over
     * the collection again.
     *
     * @var \Iterator
     */
    protected $_scheduledItems;

    /**
     * Constructs a QueueProducer object.
     *
     * @param CollectionAgentAbstract $agent
     *   The agent that is performing the queuing operations.
     * @param CollectionAbstract $collection
     *   The collection that fetches the items scheduled for indexing.
     */
    public function __construct(CollectionAgentAbstract $agent, CollectionAbstract $collection)
    {
        $this->_agent = $agent;
        $this->_collection = $collection;
    }

    /**
     * Returns the collection that fetches the items scheduled for indexing.
     *
     * @return CollectionAbstract
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Overrides SearchQueueIteratorAbstract::rewind().
     *
     * Fetches a new batch of items that are scheduled for indexing.
     *
     * @throws \RuntimeException
     *   Thrown when the collection has not been set.
     */
    public function rewind()
    {
        parent::rewind();
        $limit = $this->_agent->getLimit();
        $this->_scheduledItems = $this->_collection->fetchScheduledItems($limit);
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
        $log = $this->_agent->getLogger();
        $context = array('collection' => $this->_collection->getId());

        if (!$this->timedOut()) {
            list(, $item) = each($this->_scheduledItems);
            if ($item !== null) {

                // Build queue message and store it in a class property.
                $message = $this->_agent->getQueue()->newMessage();
                $message->setCollection($this->_collection);
                $this->_collection->buildQueueMessage($message, $item);
                $this->_currentMessage = $message;

                // Log a debug message.
                $context['item'] = $message->getBody();
                $log->debug('Fetched item scheduled for indexing', $context);

                return true;
            }

        } else {
            $context['timeout'] = $this->_agent->getTimeout();
            $log->info('Fetching operation timed out', $context);
        }

        return false;
    }
}
