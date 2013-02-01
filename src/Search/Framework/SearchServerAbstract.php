<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Abstract class extended by search backend libraries.
 */
abstract class SearchServerAbstract
{
    /**
     * The event dispatcher used by this search server to throw events.
     *
     * @var EventDispatcher
     */
    protected $_dispatcher;

    /**
     * An array of collections that are associated with this search server.
     *
     * @var array
     */
    protected $_collections = array();

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
     * Sets the event dispatcher used by this search server to throw events.
     *
     * @param EventDispatcher $dispatcher
     *   The event dispatcher.
     *
     * @return SearchServerAbstract
     */
    public function setDispatcher(EventDispatcher $dispatcher)
    {
        $this->_dispatcher = $dispatcher;
        return $this;
    }

    /**
     * Sets the event dispatcher used by this search server to throw events.
     *
     * If no event dispatcher is set, one is instantiated automatically.
     *
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        if (!isset($this->_dispatcher)) {
            $this->_dispatcher = new EventDispatcher();
        }
        return $this->_dispatcher;
    }

    /**
     * Associates a collection with this search server.
     *
     * @param SearchCollectionAbstract $collection
     *   The collection being associated with this search server.
     *
     * @return SearchServerAbstract
     */
    public function addCollection(SearchCollectionAbstract $collection)
    {
        $this->_collections[] = $collection;
        return $this;
    }

    /**
     * Returns all collections associated with this search server.
     *
     * @return array
     */
    public function getCollections()
    {
        return $this->_collections;
    }

    /**
     * Returns the merged schema for all collections associated with this search
     * server.
     *
     * @throws \RuntimeException
     */
    public function getSchema()
    {
        $schema_options = array();
        foreach ($this->_collections as $collection) {
            $cur_options = $collection->getSchema()->toArray();

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

        return new SearchCollectionSchema($schema_options);
    }

    /**
     * Iterates over all collections associated with this search server and
     * processes the items enqueued for indexing.
     *
     * @param int|null $limit
     *   The maximum number of items to process, defaults to null which uses the
     *   default setting.
     *
     * @see SearchCollectionAbstract::index()
     */
    public function index($limit = SearchCollectionQueue::NO_LIMIT)
    {
        foreach ($this->_collections as $collection) {
            $collection->index($this, $limit);
        }
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
     * Deletes all indexed data on the search server.
     *
     * @return mixed
     *   The backend's native response object.
     */
    abstract public function delete();
}
