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
    public function __construct(SearchServiceAbstract $service)
    {
        $this->_service = $service;
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
        $dispatcher = SearchRegistry::getDispatcher();
        $dispatcher->addSubscriber($this->_service);

        try {

            $queue = $collection->getQueue();
            $collection_event = new SearchCollectionEvent($this->_service, $collection, $queue);
            $dispatcher->dispatch(SearchEvents::COLLECTION_PRE_INDEX, $collection_event);

            // Iterate over items enqueued for indexing.
            foreach ($queue as $item) {

                // Get the document object and load the source data.
                $document = $this->_service->newDocument();
                $data = $collection->loadSourceData($item);

                // Allow the collection to populate the docuemnt with fields.
                $collection->buildDocument($document, $data);

                // Sandwich the indexing op with pre / post indexing events.
                $document_event = new SearchDocumentEvent($this->_service, $document, $data);
                $dispatcher->dispatch(SearchEvents::DOCUMENT_PRE_INDEX, $document_event);
                $this->_service->indexDocument($collection, $document);
                $dispatcher->dispatch(SearchEvents::DOCUMENT_POST_INDEX, $document_event);
            }

            $dispatcher->dispatch(SearchEvents::COLLECTION_POST_INDEX, $collection_event);

        } catch (Exception $e) {
            // Make sure this service is removed as a subscriber. See the
            // comment below for an explanation why.
            $dispatcher->removeSubscriber($this->_service);
            throw $e;
        }

        // The service should only listen to events throws during it's own
        // indexing operation. Otherwie it would have to implement logic that
        // checks whether it is the service class that is doing the indexing.
        $dispatcher->removeSubscriber($this->_service);
    }
}
