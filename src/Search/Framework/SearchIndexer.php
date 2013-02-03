<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

use Search\Framework\Event\SearchDocumentEvent;
use Search\Framework\Event\SearchCollectionEvent;

/**
 * Performs the indexing operation for a service.
 */
class SearchIndexer
{
    /**
     * Flags that there is no limit to the number of documents processed when
     * indexing a collection.
     */
    const NO_LIMIT = -1;

    /**
     * The search service that is performing the indexing operation.
     *
     * @var SearchServiceAbstract
     */
    protected $_service;

    /**
     * Constructs a SearchIndexer object.
     *
     * @param SearchServiceAbstract $service
     *   The search service that is performing the indexing operation.
     */
    public function __construct(SearchServiceAbstract $service, $limit = self::NO_LIMIT)
    {
        $this->_service = $service;
        $this->_limit = $limit;
    }

    /**
     * Iterates over all collections associated with the service and performs
     * the indexing operation on the.
     */
    public function indexCollections()
    {
        foreach ($this->_service->getCollections() as $collection) {
            $this->indexCollection($collection);
        }
    }

    /**
     * Performs the indexing operation on a collection.
     *
     * @param SearchCollectionAbstract $collection
     *   The collection being indexed.
     */
    public function indexCollection(SearchCollectionAbstract $collection)
    {
        $queue = $collection->getQueue($this->_limit);
        $dispatcher = $this->_service->getDispatcher();

        $collection_event = new SearchCollectionEvent($this->_service, $collection, $queue);
        $dispatcher->dispatch(SearchEvents::COLLECTION_PRE_INDEX, $collection_event);

        // Iterate over items enqueued for indexing.
        foreach ($queue as $item) {

            // Get the document object and load the source data.
            $document = $this->_service->newDocument();
            $data = $collection->loadSourceData($item);

            // Allow the collection to populate the docuemnt with fields.
            $collection->buildDocument($document, $data);

            // Instantiate and throw document related events, allow the backend
            // to process the document enqueued for indexing.
            $document_event = new SearchDocumentEvent($this->_service, $document, $data);
            $dispatcher->dispatch(SearchEvents::DOCUMENT_PRE_INDEX, $document_event);
            $this->_service->indexDocument($collection, $document);
            $dispatcher->dispatch(SearchEvents::DOCUMENT_POST_INDEX, $document_event);
        }

        $dispatcher->dispatch(SearchEvents::COLLECTION_POST_INDEX, $collection_event);
    }
}
