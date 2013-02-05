<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * A message sent to or fetched from the queue.
 */
class SearchQueueMessage
{
    /**
     * The message body.
     *
     * @var string
     */
    protected $_body;

    /**
     * A boolean flagging whether there was an error fetching the message from
     * the queue.
     *
     * This flag is useful for times when there is an error fetching a message
     * from the queue but consuming should still continue.
     *
     * @var boolean
     */
    protected $_error;

    /**
     * Constructs a SearchQueueMessage object.
     *
     * @param string $body
     *   The message body.
     * @param boolean $error
     *   Whether there was an error fetching the message from the queue,
     *   defaults to false.
     */
    public function __construct($body, $error = false)
    {
        $this->_body = $body;
        $this->_error = $error;
    }

    /**
     * Sets the message body.
     *
     * @param string $body
     *   The message body.
     *
     * @return SearchQueueMessage
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
     * Sets the error flag.
     *
     * @param boolean $error
     *   Whether there was an error fetching the message from the queue,
     *   defaults to true.
     *
     * @return SearchQueueMessage
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
     * Returns the message body.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_body;
    }
}
