<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

use Search\Framework\Event\SearchSchemaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Abstract class extended by search backend libraries.
 */
abstract class SearchServiceAbstract implements EventSubscriberInterface, SearchConfigurableInterface
{
    /**
     * The unique identifier of the service class.
     */
    protected static $_id = '';

    /**
     * Object populated with configuration options set for this instance.
     *
     * @var SearchConfig
     */
    protected $_config;

    /**
     * An array of collections that are associated with this search service.
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
    public function addCollection(SearchCollectionAbstract $collection)
    {
        $this->_schema = null;
        $this->_collections[] = $collection;
        return $this;
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
     * Returns the merged schema for all collections associated with this search
     * service.
     *
     * @throws \InvalidArgumentException
     *   Thrown when there are schema incompatibilities.
     */
    public function getSchema()
    {
        if (!$this->_schema) {

            $schema_options = array();
            foreach ($this->_collections as $collection) {

                // Loads schema and throws the SearchEvents::SCHEMA_ALTER event.
                $schema = clone $collection->getSchema();
                $event = new SearchSchemaEvent($this, $collection, $schema);
                $this->_dispatcher->dispatch(SearchEvents::SCHEMA_ALTER, $event);
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
     * Iterates over all collections associated with this search service and
     * processes the items enqueued for indexing.
     *
     * @param int|null $limit
     *   The maximum number of items to process, defaults to null which uses the
     *   default setting.
     *
     * @see SearchCollectionAbstract::index()
     */
    public function index($limit = SearchIndexer::NO_LIMIT)
    {
        $indexer = new SearchIndexer($this);
        $indexer->indexCollections($limit);
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
