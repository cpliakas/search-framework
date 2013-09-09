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
 * Base class for interacting with a queue.
 *
 * Iterating over an instance of this class fetches messages from the queue
 * until the thresholds set in the collection are met or there are no more
 * messages left in the queue.
 *
 * @see http://www.rabbitmq.com/tutorials/amqp-concepts.html
 */
abstract class QueueAbstract
{
    /**
     * The name of the default queue.
     *
     * @var string
     */
    protected static $_defaultQueue = 'default';

    /**
     * The name of the indexing queue that messages are being published to and
     * consumed from.
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
     * Constructs an QueueAbstract object.
     *
     * @param string $name
     *   The name of the indexing queue that messages are being published to and
     *   consumed from.
     */
    public function __construct($name = null)
    {
        $this->_name = (null === $name) ? static::$_defaultQueue : $name;
    }

    /**
     * Sets the name of the indexing queue that messages are being published to
     * and consumed from.
     *
     * @param string $name
     *   The name of the indexing queue that messages are being published to and
     *   consumed from.
     */
    public static function setDefaultQueue($name)
    {
        static::$_defaultQueue = $name;
    }

    /**
     * Returns the name of the indexing queue that messages are being published
     * to and consumed from.
     *
     * @return string
     */
    public static function getDefaultQueue()
    {
        return static::$_defaultQueue;
    }

    /**
     * Adds a message that was consumed after the last time acknowledgements
     * were sent to the broker.
     *
     * @param QueueMessage $message
     *   The message that was consumed.
     *
     * @return QueueAbstract
     */
    public function attachConsumedMessage(QueueMessage $message)
    {
        $this->_consumedMessages[] = $message;
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
     * @return QueueAbstract
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
     * @return QueueMessage
     */
    public function newMessage()
    {
        return new QueueMessage($this);
    }

    /**
     * Sends an item that is scheduled to be indexed to the queue.
     *
     * @param QueueMessage $message
     *   The message being sent to the queue.
     */
    abstract public function publish(QueueMessage $message);

    /**
     * Fetches an item that is scheduled to be indexed from the queue.
     *
     * @return QueueMessage|false
     *   The message fetched from the queue, false if there are no more messages
     *   to retrieve.
     */
    abstract public function consume();

    /**
     * Allows the consumer to send acknowledgements to the broker, usually
     * notifying it about which documents were processed.
     *
     * Don't forget to call QueueAbstract::clearConsumedMessages() so that
     * we don't resend acknowledgements.
     *
     * @param boolean $success
     *   Whether the documents were successfully processed, defaults to true.
     */
    abstract public function acknowledge($success = true);
}
