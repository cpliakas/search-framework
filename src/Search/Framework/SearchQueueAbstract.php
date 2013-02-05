<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * Interface for classes that interact with a queue.
 *
 * Iterating over an instance of this class fetches messages from the queue
 * until the thresholds set in the collection are met or there are no more
 * messages left in the queue.
 *
 * @see http://www.rabbitmq.com/tutorials/amqp-concepts.html
 */
abstract class SearchQueueAbstract implements \IteratorAggregate
{
    /**
     * The collection containing the source data being acted on.
     *
     * @var SearchCollectionAbstract
     */
    protected $_collection;

    /**
     * The name of the search index queue.
     *
     * @var string
     */
    protected $_name;

    /**
     * Messages that have been consumed since the last time acknowledgements
     * were sent.
     *
     * @var array
     */
    protected $_consumedMessages = array();

    /**
     * Constructs a SearchQueueAbstract object.
     *
     * @param SearchCollectionAbstract $collection
     *   The collection containing the source data being acted on.
     * @param string $name
     *   The name of the search index queue.
     */
    public function __construct(SearchCollectionAbstract $collection, $name)
    {
        $this->_collection = $collection;
        $this->_name = $name;
    }

    /**
     * Implements \IteratorAggregate::getIterator().
     *
     * Returns an iterator containing a collection of SearchQueueMessage
     * objects fetched from the queue.
     *
     * @return SearchQueueConsumerIterator
     */
    public function getIterator()
    {
        return new SearchQueueConsumerIterator($this);
    }

    /**
     * Returns the collection containing the source data being acted on.
     *
     * @return SearchCollectionAbstract
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Returns the collection containing the source data being acted on.
     *
     * @return SearchCollectionAbstract
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Adds a message that was consumed after the last time acknowledgements
     * were sent to the broker.
     *
     * @param SearchQueueMessage $message
     *   The message that was consumed.
     *
     * @return SearchQueueAbstract
     */
    public function addConsumedMessage(SearchQueueMessage $message)
    {
        $this->_consumedMessage = $message;
        return $this;
    }

    /**
     * Returns the messages that have been consumed since the last time
     * acknowledgements were sent to the broker.
     *
     * @return array
     */
    public function getConsumedMessages()
    {
        return $this->_consumedMessages;
    }

    /**
     * Clears the messages that have been consumed since the last time
     * acknowledgements were sent to the broker.
     *
     * @return SearchQueueAbstract
     */
    public function clearConsumedMessages()
    {
        $this->_consumedMessages = array();
        return $this;
    }

    /**
     * Publishes items scheduled for indexing to the queue.
     *
     * @todo Implement timeout.
     */
    public function publishScheduledItems()
    {
        foreach ($this->_collection->fetchScheduledItems() as $item) {
            $message = $this->newMessage();
            $this->_collection->buildQueueMessage($message, $item);
            $this->publish($message);
        }
    }

    /**
     * Factory function for queue message objects.
     *
     * This method is most often overridden by queue backends that require a
     * message class with backend specific functionality.
     *
     * @return SearchQueueMessage
     */
    public function newMessage()
    {
        return new SearchQueueMessage();
    }

    /**
     * Sends an item that is scheduled to be indexed to the queue.
     *
     * @param SearchQueueMessage $message
     *   The message being sent to the queue.
     */
    abstract public function publish(SearchQueueMessage $message);

    /**
     * Fetches an item that is scheduled to be indexed from the queue.
     *
     * @return SearchQueueMessage|false
     *   The message fetched from the queue, false if there are no more messages
     *   to retrieve.
     */
    abstract public function consume();

    /**
     * Allows the consumer to send acknowledgements to the broker, usually
     * notifying it about which documents were processed.
     *
     * Don't forget to call SearchQueueAbstract::clearConsumedMessages() so that
     * we don't resend acknowledgements.
     *
     * @param boolean $success
     *   Whether the documents were successfully processed, defaults to true.
     */
    abstract public function acknowledge($success = true);
}
