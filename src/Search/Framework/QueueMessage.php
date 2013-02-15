<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * A message sent to or fetched from the queue.
 *
 * This message should be extended by the queue backends to store their native
 * message objects and implement backend specific functionality.
 */
class QueueMessage
{
    /**
     * The queue that the messages is published to / consumed from.
     *
     * @var QueueAbstract
     */
    protected $_queue;

    /**
     * The collection containing the item that is scheduled for indexing.
     *
     * @var CollectionAbstract
     */
    protected $_collection;

    /**
     * The message body.
     *
     * @var string
     */
    protected $_body = '';

    /**
     * A boolean flagging whether there was an error fetching the message from
     * the queue.
     *
     * This flag is useful for times when there is an error fetching a message
     * from the queue but consuming should still continue.
     *
     * @var boolean
     */
    protected $_error = false;

    /**
     * The unique identifier of the consumed message.
     *
     * @var int|string
     */
    protected $_id;

    /**
     * Constructs a QueueAbstract object.
     *
     * @param QueueAbstract $queue
     *   The queue that the messages is published to / consumed from.
     */
    public function __construct(QueueAbstract $queue)
    {
        $this->_queue = $queue;
    }

    /**
     * Returns the queue that the messages is published to / consumed from.
     *
     * @return CollectionAbstract
     */
    public function getQueue()
    {
        return $this->_queue;
    }

    /**
     * Sets the collection containing the item being published to or consumed
     * from the queue.
     *
     * @param CollectionAbstract $collection
     *   The collection containing the item being published to or consumed from
     *   the queue.
     *
     * @return QueueMessage
     */
    public function setCollection(CollectionAbstract $collection)
    {
        $this->_collection = $collection;
        return $this;
    }

    /**
     * Returns the collection containing the item being published to or consumed
     * from the queue.
     *
     * @return CollectionAbstract
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Sets the message body.
     *
     * @param string $body
     *   The message body.
     *
     * @return QueueMessage
     */
    public function setBody($body)
    {
        $this->_body = $body;
        return $this;
    }

    /**
     * Returns the message body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * Sets the error flag for the consumed message.
     *
     * This flag should not be set for messages that are being published to a
     * queue.
     *
     * @param boolean $error
     *   Whether there was an error fetching the message from the queue,
     *   defaults to true.
     *
     * @return QueueMessage
     */
    public function setError($error = true)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * Returns whether there was an error fetching the message from the queue.
     *
     * @return boolean
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * Optionally set the message's unique identifier after it is consumed from
     * the queue.
     *
     * The identifier should not be set for messages that are being published to
     * a queue.
     *
     * @param int|string $id
     *   The unique identifier of the consumed message.
     *
     * @return QueueMessage
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * The unique identifier of the consumed message.
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Helper function that publishes this message to the queue.
     *
     * @see SearchQueueAdapter::publish()
     */
    public function publish()
    {
        $this->_queue->publish($this);
    }

    /**
     * Returns the message body.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_body;
    }
}
