<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * A single threaded, blocking queue system that serves as the producer, broker
 * and consumer.
 *
 * In other words, it just iterates over the items scheduled for indexing.
 */
class SearchIteratorQueue implements SearchQueueInterface, \IteratorAggregate
{
      /**
       * The items that are scheduled for indexing.
       *
       * @var \Iterator
       */
      protected $_scheduledItems;

      /**
       * Implements \IteratorAggregate::getIterator().
       */
      public function getIterator()
      {
          return $this->_scheduledItems;
      }

      /**
       * Implements SearchQueueProducerInterface::publish().
       */
      public function publish(SearchCollectionAbstract $collection)
      {
          $this->_scheduledItems = $collection->getScheduledItems();
          return $this;
      }

      /**
       * Implements SearchQueueConsumerInterface::consume().
       */
      public function consume(SearchCollectionAbstract $collection)
      {
          // Nothing to do ...
          return $this;
      }

      /**
       * Implements SearchQueueConsumerInterface::acknowledge().
       */
      public function acknowledge(array $documents, $success = true)
      {
          // Nothing to do ...
          return $this;
      }
}
