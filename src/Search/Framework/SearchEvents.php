<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * Events thrown by the Search Framework library.
 */
final class SearchEvents
{
    /**
     * Event thrown prior to the collector executing the queuing operation for
     * all collections that are attached to it.
     */
    const COLLECTOR_PRE_QUEUE = 'search.collector.pre_queue';

    /**
     * Event thrown prior to loading a schema configuration.
     *
     * This method can be used to load the configs from an alternate source.
     */
    const SCHEMA_LOAD = 'search.schema.load';

    /**
     * Event thrown that allows for the altering of each collection's schema
     * prior to searching and indexing.
     *
     * This is most often implemented in order to reconcile the differences
     * between incompatible schema.
     */
    const SCHEMA_ALTER = 'search.schema.alter';

    /**
     * Event thrown prior to queuing the items in a collection that are
     * scheduled for indexing.
     */
    const COLLECTION_PRE_QUEUE = 'search.collection.pre_queue';

    /**
     * Event thrown after to the items in a collection that are scheduled for
     * indexing have been queued.
     */
    const COLLECTION_POST_QUEUE = 'search.collection.post_queue';

    /**
     * Event thrown prior after the collector has finished executing the queuing
     * operation for all collections that are attached to it.
     */
    const COLLECTOR_POST_QUEUE = 'search.collector.post_queue';

    /**
     * Event thrown prior to a service consuming items in the queue for
     * indexing.
     */
    const SEARCH_ENGINE_PRE_INDEX = 'search.search_engine.pre_index';

    /**
     * Event thrown after a document was populated with fields and prior to it
     * being processed for indexing.
     *
     * @var string
     */
    const DOCUMENT_PRE_INDEX = 'search.document.pre_index';

    /**
     * Event thrown after a document was processed for indexing.
     *
     * @var string
     */
    const DOCUMENT_POST_INDEX = 'search.document.post_index';

    /**
     * Event thrown after the indexing operation has completed.
     */
    const SEARCH_ENGINE_POST_INDEX = 'search.search_engine.post_index';
}
