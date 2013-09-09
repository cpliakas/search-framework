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

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Search\Framework\Event\SchemaEvent;
use Search\Framework\Event\SearchEvent;

/**
 * Extended by objects that perform collection related operations.
 */
abstract class CollectionAgentAbstract implements LoggerAwareInterface, \Countable
{
    /**
     * Value that specifies a constraint has no limit, for example an operation
     * timeout or document limit.
     *
     * @var int
     */
    const NO_LIMIT = -1;

    /**
     * The dispatcher used to throw indexing related events.
     *
     * @var EventDispatcherInterface
     */
    protected $_dispatcher;

    /**
     * An array of CollectionAbstract objects keyed by unique identifier.
     *
     * @var array
     */
    protected $_collections = array();

    /**
     * The backend that queues items for indexing.
     *
     * @var QueueAbstract
     */
    protected $_queue;

    /**
     * The fused schema for all collections attached to this agent.
     *
     * @var Schema
     */
    protected $_schema;

    /**
     * A key / value cache of field identifiers to data types.
     *
     * This variable is populated when building the schema.
     *
     * @var array
     */
    protected $_fieldTypes = array();

    /**
     * The PSR-3 compliant logger that logs messages for this agent.
     *
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * The maximum number of documents that are processed during an operation.
     *
     * @var int
     */
    protected $_limit = self::NO_LIMIT;

    /**
     * The timeout in seconds for an operation.
     *
     * @var int
     */
    protected $_timeout = self::NO_LIMIT;

    /**
     * Returns the dispatcher used to throw events.
     *
     * @return EventDispatcherInterface
     */
    abstract public function getDispatcher();

    /**
     * Dispatches an event, wraps with debug messages.
     *
     * @param string $name
     *   The name of the event to dispatch.
     * @param SearchEvent $event
     *   The event to pass to the event handlers/listeners.
     * @param array $context
     *   Optional context to pass to the logger.
     */
    public function dispatchEvent($name, SearchEvent $event, array $context = array())
    {
        $log = $this->getLogger();
        $context['event'] = $name;
        $log->debug('Throwing event', $context);
        $this->getDispatcher()->dispatch($name, $event);
        $log->debug('Event thrown', $context);
    }

    /**
     * Returns a true if a collection with the passed identifier is attached.
     *
     * @param string $id
     *   The unique identifier of the collection.
     *
     * @return boolean
     */
    public function hasCollection($id)
    {
        return isset($this->_collections[$id]);
    }

    /**
     * Relates a collection to the object.
     *
     * @param SearchCollectionAbstract $collection
     *   The collection being related to this object.
     *
     * @return CollectionAgentAbstract
     *
     * @throws \InvalidArgumentException
     *   Thrown when a collection with the same identifier is already attached.
     */
    public function attachCollection(CollectionAbstract $collection)
    {
        $id = $collection->getId();

        if (isset($this->_collections[$id])) {
            throw new \InvalidArgumentException('Collection already attached: ' . $id);
        }

        $this->_collections[$id] = $collection;
        $this->clearSchema();

        $this->getLogger()->debug('Collection attached', array('collection' => $id));

        return $this;
    }

    /**
     * Removes all collections atthed to the object and attaches the passed
     * collections.
     *
     * @param array $collections
     *   An array of CollectionAbstract objects.
     *
     * @return CollectionAgentAbstract
     */
    public function setCollections(array $collections)
    {
        foreach ($collections as $collection) {
            $this->attachCollection($collection);
        }
        return $this;
    }

    /**
     * Returns a collection given it's unique identifier.
     *
     * @param string $id
     *   The unique identifier of the collection.
     *
     * @return CollectionAbstract
     *
     * @throws \InvalidArgumentException
     *   Thrown when the collection is not attached.
     */
    public function getCollection($id)
    {
        if (!isset($this->_collections[$id])) {
            throw new \InvalidArgumentException('Collection not attached: ' . $id);
        }
        return $this->_collections;
    }

    /**
     * Returns all collections associated with this search service.
     *
     * @return array
     */
    public function getCollections()
    {
        return $this->_collections;
    }

    /**
     * Removes a collection from this server.
     *
     * @param string $id
     *   The unique identifier of the collection.
     *
     * @return CollectionAgentAbstract
     */
    public function removeCollection($id)
    {
        unset($this->_collections[$id]);
        $this->clearSchema();

        $this->getLogger()->debug('Collection removed', array('collection' => $id));

        return $this;
    }

    /**
     * Implements Countable::count().
     *
     * Returns the nuber of collections that are attached to the agent.
     */
    public function count()
    {
        return count($this->_collections);
    }

    /**
     * Helper function that clears the schema and field type cache.
     */
    public function clearSchema()
    {
        unset($this->_schema);
        $this->_fieldTypes = array();
    }

    /**
     * Returns the fused schema for all collections attached to this agent.
     *
     * @return Schema
     */
    public function getSchema()
    {
        if (!isset($this->_schema)) {
            $this->_schema = $this->loadSchemata();
        }
        return $this->_schema;
    }

    /**
     * Loads and fuses the schemata for all collections attached to this agent.
     *
     * @return Schema
     */
    public function loadSchemata()
    {
        $fused_options = array();
        foreach ($this->_collections as $collection) {
            $context = array('collection' => $collection->getId());

            $schema_options = $this->loadCollectionSchema($collection)->toArray();

            // Just set the schema options on the first pass.
            if (!$fused_options) {
                $fused_options = $schema_options;
                continue;
            }

            // The unique field must be the same across collections.
            if ($schema_options['unique_field'] != $fused_options['unique_field']) {
                $message = 'Collections must have the same unique field.';
                throw new \InvalidArgumentException($message);
            }

            // Define the field or check for field incompatibilities.
            foreach ($schema_options['fields'] as $field_id => $field_options) {
                if (!isset($fused_options['fields'][$field_id])) {
                    $fused_options['fields'][$field_id] = $field_options;
                } elseif ($fused_options['fields'][$field_id] != $field_options) {
                    $message = 'Field definitions for "'. $field_id . '"must match.';
                    throw new \InvalidArgumentException($message);
                }
            }
        }

        // Build the fused schema.
        $schema = new Schema();
        $schema->build($fused_options);

        // Populate the field type cache.
        foreach ($schema as $id => $field) {
            $this->_fieldTypes[$id] = $field->getType();
        }

        return $schema;
    }

    /**
     *
     * @param CollectionAbstract $collection
     *
     * @return Schema
     *
     * @throws
     */
    public function loadCollectionSchema(CollectionAbstract $collection)
    {
        $loader = new SchemaLoader($this, $collection);
        $schema = $loader->load();

        $context = array('collection' => $collection->getId());
        $event = new SchemaEvent($this, $collection, $schema);
        $this->dispatchEvent(SearchEvents::SCHEMA_ALTER, $event, $context);

        return $schema;
    }

    /**
     * Sets the backend that queues items for indexing.
     *
     * @param QueueAbstract $queue
     *   The backend that queues items for indexing.
     *
     * @return CollectionAgentAbstract
     */
    public function setQueue(QueueAbstract $queue)
    {
        $this->_queue = $queue;
        return $this;
    }

    /**
     * Returns the backend that queues items for indexing.
     *
     * If no queue backend is set, a IteratorQueue class is instantiated and
     * stored as the queue backend.
     *
     * @return QueueAbstract
     */
    public function getQueue()
    {
        if (!isset($this->_queue)) {
            $this->_queue = new IteratorQueue();
        }
        return $this->_queue;
    }

    /**
     * Implements LoggerAwareInterface::setLogger().
     *
     * @return CollectionAgentAbstract
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * Returns the PSR-3 compliant logger that logs messages for this agent.
     *
     * If no logger is set, a NullLogger class is instantiated and set as the
     * logger.
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if (!isset($this->_logger)) {
            $this->_logger = new NullLogger();
        }
        return $this->_logger;
    }

    /**
     * Sets the maximum number of documents that are processed during an
     * operation.
     *
     * @param int $limit
     *   The maximum number of documents that are processed during an operation.
     *
     * @return CollectionAgentAbstract
     */
    public function setLimit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * Returns the maximum number of documents that are processed during an
     * operation.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->_limit;
    }

    /**
     * Sets the timeout in seconds for an operation.
     *
     * @param int $timeout
     *   The the timeout in seconds for an operation.
     *
     * @return CollectionAgentAbstract
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
        return $this;
    }

    /**
     * Returns the timeout in seconds for an operation.
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }
}
