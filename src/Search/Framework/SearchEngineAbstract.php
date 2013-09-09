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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Abstract class extended by search backend libraries.
 */
abstract class SearchEngineAbstract implements EventSubscriberInterface
{
    /**
     * Associative array of normalizers keyed by data type.
     *
     * @var array
     */
    protected $_normalizers = array();

    /**
     * Constructs a SearchEngineAbstract object.
     *
     * @param SearchEngineEndpoint|array $endpoints
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
            if (!$endpoint instanceof SearchEngineEndpoint) {
                $message = 'Argument 1 passed to ' . __METHOD__ . ' must be an array of SearchEngineEndpoint objects.';
                throw new \InvalidArgumentException($message);
            }
        }

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
     * @param Indexer $indexer
     *   The indexer that is perfomring the operation.
     *
     * @return IndexDocument
     */
    public function newDocument(Indexer $indexer)
    {
        return new IndexDocument($indexer);
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
     * @return IndexField
     */
    public function newField($id, $value, $name = null)
    {
        return new IndexField($id, $value, $name);
    }

    /**
     * Attaches a mormalizer that is applied to fields of the given data type.
     *
     * @param string $data_type
     *   The data type the normalizer is applied to.
     * @param NormalizerInterface $normalizer
     *   The normaizer that is applied to fields of the given data type.
     *
     * @return SearchEngineAbstract
     */
    public function attachNormalizer($data_type, NormalizerInterface $normalizer)
    {
        $this->_normalizers[$data_type] = $normalizer;
        return $this;
    }

    /**
     * Returns whether there is a normalizer registered for the data type.
     *
     * @param string $data_type
     *   The data type the normalizer is applied to.
     *
     * @return NormalizerInterface
     */
    public function hasNormalizer($data_type)
    {
        return isset($this->_normalizers[$data_type]);
    }

    /**
     * Returns a mormalizer that is applied to fields of the given data type.
     *
     * @param string $data_type
     *   The data type the normalizer is applied to.
     *
     * @return NormalizerInterface
     */
    public function getNormalizer($data_type)
    {
        if (!isset($this->_normalizers[$data_type])) {
            $message = 'Normalizer not attached for data type: ' . $data_type;
            throw new \InvalidArgumentException($message);
        }
        return $this->_normalizers[$data_type];
    }

    /**
     * Removes a normaizer that is applied to fields of the given data type.
     *
     * @param string $data_type
     *   The data type the normalizer is applied to.
     *
     * @return SearchEngineAbstract
     */
    public function removeNormalizer($data_type)
    {
        unset($this->_normalizers[$data_type]);
        return $this;
    }

    /**
     * Creates an index based off of each collection's schema.
     *
     * @param Indexer $indexer
     *   The indexer object creating the index.
     * @param array $options
     *   Backend-specific options related to creating the index.
     */
    abstract public function createIndex(Indexer $indexer, array $options = array());

    /**
     * Processes a document for indexing.
     *
     * @param CollectionAbstract $collection
     *   The collection that the source data was extracted from.
     * @param IndexDocument $document
     *   The document being indexed.
     */
    abstract public function indexDocument(CollectionAbstract $collection, IndexDocument $document);

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
