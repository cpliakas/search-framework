<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

use Search\Framework\Event\SearchDocumentEvent;
use Search\Framework\Event\SearchServiceEvent;
use Search\Framework\Event\SearchSchemaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Abstract class extended by search backend libraries.
 */
abstract class SearchServiceAbstract implements EventSubscriberInterface, SearchConfigurableInterface
{
    /**
     * The unique identifier of the service class.
     *
     * It is best practice to use only lowercase letters, numbers, dots (.),
     * and underscores (_).
     *
     * @var string
     */
    protected static $_id = '';

    /**
     * Object populated with configuration options set for this instance.
     *
     * @var SearchConfig
     */
    protected $_config;

    /**
     * An array of SearchServiceCollection objects that are associated with this
     * search service.
     *
     * @var array
     */
    protected $_collections = array();

    /**
     * The merged schema of all collections associated with this search service.
     *
     * @var SearchSchema
     */
    protected $_schema;

    /**
     * Constructs a SearchServiceAbstract object.
     *
     * @param SearchServiceEndpoint|array $endpoints
     *   The endpoint(s) that the client library will use to communicate with
     *   the search service.
     * @param array $options
     *   An associative array of search service specific options.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($endpoints, array $options = array())
    {
        if (!is_array($endpoints)) {
            $endpoints = array($endpoints);
        } elseif (empty($endpoints)) {
            $message = 'Argument 1 passed to ' . __METHOD__ . ' is required.';
            throw new \InvalidArgumentException($message);
        }

        foreach ($endpoints as $endpoint) {
            if (!$endpoint instanceof SearchServiceEndpoint) {
                $message = 'Argument 1 passed to ' . __METHOD__ . ' must be an array of SearchServiceEndpoint objects.';
                throw new \InvalidArgumentException($message);
            }
        }

        $this->_config = new SearchConfig($options);
        $this->_config->load($this);

        $this->init($endpoints, $options);
    }

    /**
     * Implements EventSubscriberInterface::getSubscribedEvents().
     *
     * The implementing search service class should override this method to
     * register itself as a subscriber. This class is added as a subscriber by
     * the indexer only for the duration of it's own indexing process.
     */
    public static function getSubscribedEvents()
    {
        return array();
    }

    /**
     * Implements SearchConfigurableInterface::id().
     */
    public function getId()
    {
        return static::$_id;
    }

    /**
     * Implements SearchConfigurableInterface::getConfig().
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Hook invoked during object construction.
     *
     * @param array $endpoints
     *   The endpoint(s) that the client library will use to communicate with
     *   the search service.
     * @param array $options
     *   An associative array of search service specific options.
     */
    abstract public function init(array $endpoints, array $options);

    /**
     * Returns a search index document object specific to the extending backend.
     *
     * @return SearchIndexDocument
     */
    public function newDocument()
    {
        return new SearchIndexDocument($this);
    }

    /**
     * Returns a search index field object specific to the extending backend.
     *
     * @param string $id
     *   The unique identifier of the field that the index name defaults to.
     * @param string|array $value
     *   The field's value extracted form the source text.
     * @param string|null $name
     *   The name of this field as stored in the index, defaults to null which
     *   uses the unique identifier.
     *
     * @return SearchIndexField
     */
    public function newField($id, $value, $name = null)
    {
        return new SearchIndexField($id, $value, $name);
    }

    /**
     * Associates a collection with this search service.
     *
     * Resets the cached schema object.
     *
     * @param SearchCollectionAbstract $collection
     *   The collection being associated with this search service.
     *
     * @return SearchServiceAbstract
     */
    public function attachCollection(SearchCollectionAbstract $collection)
    {
        $this->_schema = null;
        $id = $collection->getId();
        $this->_collections[$id] = $collection;
        return $this;
    }

    /**
     * Returns a collection given it's unique identifier.
     *
     * @param string $id
     *   The unique identifier of the collection.
     *
     * @return SearchCollectionAbstract
     *
     * @throws \InvalidArgumentException
     */
    public function getCollection($id)
    {
        if (!isset($this->_collections[$id])) {
            throw new \InvalidArgumentException();
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
     * @return SearchServiceAbstract
     */
    public function removeCollection($id)
    {
        unset($this->_collections[$id]);
        return $this;
    }

    /**
     * Returns the merged schema for all collections associated with this search
     * service.
     *
     * @throws \InvalidArgumentException
     *   Thrown when there are schema incompatibilities.
     */
    public function getSchema()
    {
        if (!$this->_schema) {
            $dispatcher = SearchRegistry::getDispatcher();

            $schema_options = array();
            foreach ($this->_collections as $collection) {

                // Loads schema and throws the SearchEvents::SCHEMA_ALTER event.
                $schema = clone $collection->getSchema();
                $event = new SearchSchemaEvent($this, $collection, $schema);
                $dispatcher->dispatch(SearchEvents::SCHEMA_ALTER, $event);
                $cur_options = $schema->toArray();

                // Just set the schema options on the first pass.
                if (!$schema_options) {
                    $schema_options = $cur_options;
                    continue;
                }

                // The unique field must be the same across collections.
                if ($cur_options['unique_field'] != $schema_options['unique_field']) {
                    $message = 'Collections must have the same unique field.';
                    throw new \InvalidArgumentException($message);
                }

                // Define the field or check for field incompatibilities.
                foreach ($cur_options['fields'] as $field_id => $field_options) {
                    if (!isset($schema_options['fields'][$field_id])) {
                        $schema_options['fields'][$field_id] = $field_options;
                    } elseif ($schema_options['fields'][$field_id] != $field_options) {
                        $message = 'Field definitions for "'. $field_id . '"must match.';
                        throw new \InvalidArgumentException($message);
                    }
                }
            }

            $this->_schema = new SearchSchema($schema_options);
        }

        return $this->_schema;
    }

    /**
     * Creates an index based off of each collection's schema.
     *
     * @param string $name
     *   The name of the index.
     * @param array $options
     *   Backend-specific options related to creating the index.
     */
    abstract public function createIndex($name, array $options = array());

    /**
     * Iterates over all collections attached to this search service and queues
     * the content scheduled for indexing. After queuing is complete, the search
     * service processes the queue and
     */
    public function index()
    {
        foreach ($this->_collections as $collection) {
            $collection->queueScheduledItems();
        }
        $this->indexQueuedItems();
    }

    /**
     * Processes the queue and indexes the items in the queue.
     */
    public function indexQueuedItems()
    {
        $queue = SearchRegistry::getQueue();
        $dispatcher = SearchRegistry::getDispatcher();

        try {

            // Adds the service instance as a subscriber only for the duration
            // of the indexing process.
            $dispatcher->addSubscriber($this);

            $service_event = new SearchServiceEvent($this);
            $dispatcher->dispatch(SearchEvents::SERVICE_PRE_INDEX, $service_event);

            foreach ($queue as $message) {

                $collection = $message->getCollection();
                $document = $this->newDocument();
                $data = $collection->loadSourceData($message);

                $document_event = new SearchDocumentEvent($this, $document, $data);
                $dispatcher->dispatch(SearchEvents::DOCUMENT_PRE_INDEX, $document_event);
                $this->indexDocument($collection, $document);
                $dispatcher->dispatch(SearchEvents::DOCUMENT_POST_INDEX, $document_event);
            }

            $dispatcher->dispatch(SearchEvents::SERVICE_POST_INDEX, $service_event);

            // The service should only listen to events throws during it's own
            // indexing operation.
            $dispatcher->removeSubscriber($this);

        } catch (Exception $e) {
            // Make sure this service is removed as a subscriber. See above.
            $dispatcher->removeSubscriber($this);
            throw $e;
        }
    }

    /**
     * Processes a document for indexing.
     *
     * @param SearchCollectionAbstract $collection
     *   The collection that the source data was extracted from.
     * @param SearchIndexDocument $document
     *   The document being indexed.
     */
    abstract public function indexDocument(SearchCollectionAbstract $collection, SearchIndexDocument $document);

    /**
     * Executes a search against the backend.
     *
     * @param string $keywords
     *   The raw keyowrds usually passed by a user through a search form.
     * @param array $options
     *   An associative array of backend-specific options.
     *
     * @return mixed
     *   The backend specific result.
     */
    abstract public function search($keywords, array $options = array());

    /**
     * Deletes all indexed data on the search service.
     *
     * @return mixed
     *   The backend's native response object.
     */
    abstract public function delete();
}
