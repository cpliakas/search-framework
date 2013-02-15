<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

use Search\Framework\Event\CollectionEvent;
use Search\Framework\Event\CollectorEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Fetches the items in all collections attached to this object that are
 * scheduled for indexing and publishes them to the queue.
 */
class Collector extends CollectionAgentAbstract
{
    /**
     * Constructs a Collector object.
     *
     * @param EventDispatcherInterface $dispatcher
     *   The dispatcher used to throw queuing related events.
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->_dispatcher = $dispatcher;
    }

    /**
     * Implements CollectionAgentAbstract::getDispatcher().
     */
    public function getDispatcher()
    {
        return $this->_dispatcher;
    }

    /**
     * Queues the items scheduled for indexing for all collections attached to
     * the collector.
     *
     * @return int
     *   The number of items sent to the queue.
     */
    public function queue()
    {
        $log = $this->getLogger();
        $log->info('Queueing operation started', array('collections' => count($this)));

        $event = new CollectorEvent($this);
        $this->dispatchEvent(SearchEvents::COLLECTOR_PRE_QUEUE, $event);

        $num_queued = 0;
        foreach ($this->_collections as $collection) {
            $num_queued += $this->queueCollection($collection);
        }

        $this->dispatchEvent(SearchEvents::COLLECTOR_POST_QUEUE, $event);

        $log->info('Queueing operation completed', array('queued' => $num_queued));
        return $num_queued;
    }

    /**
     * Queues the items scheduled for indexing for the collection.
     *
     * @param CollectionAbstract $collection
     *   The collection that fetches the items scheduled for indexing.
     *
     * @return int
     *   The number of items sent to the queue.
     */
    public function queueCollection(CollectionAbstract $collection)
    {
        $log = $this->getLogger();
        $context = array('collection' => $collection->getId());
        $log->info('Begin fetching items that are scheduled for indexing', $context);

        $event = new CollectionEvent($this, $collection);
        $this->dispatchEvent(SearchEvents::COLLECTION_PRE_QUEUE, $event, $context);

        // The producer fetches the items scheduled for indexing from the
        // collection and publishes them to the indexing queue.
        $producer = new QueueProducer($this, $collection);
        foreach ($producer as $message) {
            // @todo Have this return a boolean suceess flag and modify the log
            // message accordingly?
            $message->publish();

            $context['item'] = $message->getBody();
            $log->debug('Published item scheduled for indexing to queue', $context);
        }

        unset($context['item']); // The item is no longer in context.
        $this->dispatchEvent(SearchEvents::COLLECTION_POST_QUEUE, $event, $context);

        // Get and log the number of items queued for this collection.
        $num_queued = count($producer);
        $context['queued'] = $num_queued;
        $log->info('Finished fetching and queueing items that are scheduled for indexing', $context);

        return $num_queued;
    }
}
