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
     * The name of the default queue.
     *
     * @var string
     */
    protected static $_defaultQueue = 'default';

    /**
     *
     * @var int
     */
    protected $_limit = 200;

    /**
     *
     * @var int
     */
    protected $_timeout = 30;

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
    public function __construct($name = null)
    {
        $this->_name = (null === $name) ? static::$_defaultQueue : $name;
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
     *
     * @param string $name
     *
     *
     */
    public static function setDefaultQueue($name)
    {
        static::$_defaultQueue = $name;
    }

    /**
     *
     * @return string
     */
    public static function getDefaultQueue()
    {
        return static::$_defaultQueue;
    }

    /**
     * Sets the maximum number of documents that are processed during indexing
     * and queuing operations.
     *
     * @param int $limit
     *   The maximum number of documents to process
     *
     * @return SearchCollectionAbstract
     */
    public function setLimit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * Gets that maximum number of documents that are processed during indexing
     * operation.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->_timeout;
    }

    /**
     * Sets the timeout in seconds for the indexing operations.
     *
     * @param int $timeout
     *   The the maximum amount of time in seconds allowed for the indexing and
     *   queuing operations.
     *
     * @return SearchCollectionAbstract
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
        return $this;
    }

    /**
     * Returns the timeout in seconds for the indexing and queuing operations.
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->_config->getOption('timeout', static::$_defaultTimeout);
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
    public function attachConsumedMessage(SearchQueueMessage $message)
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
     * Factory function for queue message objects.
     *
     * This method is most often overridden by queue backends that require a
     * message class with backend specific functionality.
     *
     * @return SearchQueueMessage
     */
    public function newMessage()
    {
        return new SearchQueueMessage($this);
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
