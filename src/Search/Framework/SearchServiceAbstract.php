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
     * Associative array of normalizers keyed by the data type.
     *
     * @var array
     */
    protected $_normalizers = array();

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
     * A key / value cache of field IDs to data types.
     *
     * @var array
     */
    protected $_fieldTypes = array();

    /**
     * The global dispatcher object.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $_dispatcher;

    /**
     * The global queue object.
     *
     * @var SearchQueueAbstract
     */
    protected $_queue;

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

        $this->_dispatcher = SearchRegistry::getDispatcher();
        $this->_queue = SearchRegistry::getQueue();

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
     */
    abstract public function init(array $endpoints);

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
     * Attaches a mormalizer that is applied to fields of the given data type.
     *
     * @param string $type
     *   The data type the normalizer is applied to.
     * @param SearchNormalizerInterface $normalizer
     *   The normaizer that is applied to fields of the given data type.
     *
     * @return SearchServiceAbstract
     */
    public function attachNormalizer($type, SearchNormalizerInterface $normalizer)
    {
        $this->_normalizers[$type] = $normalizer;
        return $this;
    }

    /**
     * Returns a mormalizer that is applied to fields of the given data type.
     *
     * @param string $type
     *   The data type the normalizer is applied to.
     *
     * @return SearchNormalizerInterface|false
     */
    public function getNormalizer($type)
    {
        return isset($this->_normalizers[$type]) ? $this->_normalizers[$type] : false;
    }

    /**
     * Removes a normaizer that is applied to fields of the given data type.
     *
     * @param string $type
     *   The data type the normalizer is applied to.
     *
     * @return SearchServiceAbstract
     */
    public function removeNormalizer($type)
    {
        unset($this->_normalizers[$type]);
        return $this;
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
     * This method also populates the SearchServiceAbstract::_fieldTypes
     * property.
     *
     * @throws \InvalidArgumentException
     *   Thrown when there are schema incompatibilities.
     */
    public function getSchema()
    {
        if (!$this->_schema) {

            $fused_options = array();
            foreach ($this->_collections as $collection) {

                // Loads schema and throws the SearchEvents::SCHEMA_ALTER event.
                $schema = clone $collection->getSchema();
                $event = new SearchSchemaEvent($this, $collection, $schema);
                $this->_dispatcher->dispatch(SearchEvents::SCHEMA_ALTER, $event);
                $schema_options = $schema->toArray();

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

            // Set the fused schema.
            $this->_schema = new SearchSchema($fused_options);

            // Populate the field type cache.
            foreach ($this->_schema as $id => $field) {
                $this->_fieldTypes[$id] = $field->getType();
            }
        }

        return $this->_schema;
    }

    /**
     * Apply the serivce specific normalizers to a field's value.
     *
     * @param SearchIndexField $field
     *   The field being normalized.
     *
     * @return string
     */
    public function normalizeFieldValue(SearchIndexField $field)
    {
        $id = $field->getId();
        $value = $field->getValue();

        // Check if we can determine the data type of the field and that a
        // normalizer is associated with the type.
        if (isset($this->_fieldTypes[$id]) && isset($this->_normalizers[$this->_fieldTypes[$id]])) {
            $normalizer = $this->_normalizers[$this->_fieldTypes[$id]];
            $value = $normalizer->normalize($value);
        }

        return $value;
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
     * service processes the queue and indexes the items.
     */
    public function index()
    {
        foreach ($this->_collections as $collection) {
            $collection->queueScheduledItems();
        }
        $this->indexQueuedItems();
    }

    /**
     * Processes the queue and indexes the queued items.
     */
    public function indexQueuedItems()
    {
        try {
            // Ensure the schema object is populated. This routine will also
            // throw the SearchEvents::SCHEMA_ALTER event and detect
            // incompatible collection schemata.
            $this->getSchema();

            // Adds the service instance as a subscriber only for the duration
            // of the indexing process.
            $this->_dispatcher->addSubscriber($this);

            $service_event = new SearchServiceEvent($this);
            $this->_dispatcher->dispatch(SearchEvents::SERVICE_PRE_INDEX, $service_event);

            // Consume messages from the queue that correspond with items that
            // are scheduled for indexing.
            foreach ($this->_queue as $message) {

                // Load the source data form the message. The message usually
                // contains a unique identifier in the body. Skip processing if
                // false is returned as the source data.
                $collection = $message->getCollection();
                $data = $collection->loadSourceData($message);

                if ($data !== false) {

                    // Build an index document from the source data.
                    $document = $this->newDocument();
                    $collection->buildDocument($document, $data);

                    // Index the document, sandwich indexing with events.
                    $document_event = new SearchDocumentEvent($this, $document, $data);
                    $this->_dispatcher->dispatch(SearchEvents::DOCUMENT_PRE_INDEX, $document_event);
                    $this->indexDocument($collection, $document);
                    $this->_dispatcher->dispatch(SearchEvents::DOCUMENT_POST_INDEX, $document_event);
                }
            }

            $this->_dispatcher->dispatch(SearchEvents::SERVICE_POST_INDEX, $service_event);

            // The service should only listen to events throws during it's own
            // indexing operation.
            $this->_dispatcher->removeSubscriber($this);

        } catch (Exception $e) {
            // Make sure this service is removed as a subscriber. See above.
            $this->_dispatcher->removeSubscriber($this);
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
