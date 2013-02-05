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
     * Publishes items scheduled for indexing to the queue.
     *
     * @todo Implement timeout.
     */
    public function publishScheduledItems()
    {
        foreach ($this->getScheduledItems() as $item) {
            $message = $this->_collection->getMessage($item);
        }
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
     * @param array $documents
     *   An array of SearchIndexDocument objects that were processed.
     * @param boolean $success
     *   Whether the documents were successfully processed, defaults to true.
     */
    abstract public function acknowledge(array $documents, $success = true);
}
