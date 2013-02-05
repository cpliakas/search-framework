<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * A single threaded, non-persistent queue system that serves as the producer,
 * broker and consumer. In other words, it just iterates over the items that are
 * scheduled for indexing.
 */
class SearchQueueIteratorQueue extends SearchQueueAbstract
{
    /**
     * An array of "published" messages.
     *
     * This array acts as our super-sophisticated queue broker.
     *
     * @var array
     */
    protected $_messages = array();

    /**
     * Implements SearchQueueAbstract::publish().
     *
     * Publishing is simply appending the message to the array iterator.
     */
    public function publish(SearchQueueMessage $message)
    {
        $this->_messages[] = $message;
    }

    /**
     * Implements SearchQueueAbstract::consume().
     *
     * Set the zero-based key as the unique identifier prior to returning.
     */
    public function consume()
    {
        list($id, $message) = each($this->_messages);
        if ($id !== null) {
            $message->setId($id);
            return $message;
        }
        return false;
    }

    /**
     * Implements SearchQueueAbstract::acknowledge().
     *
     * On success, remove the consumed messages from the iterator.
     */
    public function acknowledge($success = true)
    {
        if ($success) {
            foreach ($this->getConsumedMessages() as $message) {
                $id = $message->getId();
                unset($this->_messages[$id]);
            }
            $this->clearConsumedMessages();
        }
    }
}
