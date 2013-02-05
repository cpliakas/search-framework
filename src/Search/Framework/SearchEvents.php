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
     * Event thrown prior to loading configurations from files.
     *
     * This is most often implemented by caching backends to prevent reading the
     * config files on every pageload.
     */
    const CONFIG_LOAD = 'search.config.load';

    /**
     * Event thrown that allows for the altering of each collection's schema
     * prior to searching and indexing.
     *
     * This is most often implemented in order to reconcile the differences
     * between incompatible schema.
     */
    const SCHEMA_ALTER = 'search.schema.alter';

    /**
     * Event thrown prior to processing a collection's queue.
     *
     * @var string
     */
    const COLLECTION_PRE_INDEX = 'search.collection.pre_index';

    /**
     * Event thrown after a field is extracted from the source data and added to
     * the document.
     *
     * This event is intended to add metadata to the field or add additional
     * fields based on the field data being processed. If can also be used to
     * change the name of the field as it is stored in the index.
     *
     * @var string
     */
    const FIELD_ENRICH = 'search.field.enrich';

    /**
     * Event thrown when the backend retrieves the value for indexing.
     *
     * This event is intended to be used to clean and normalize the content in
     * order to prepare it for indexing. When possible, the native normalization
     * mechanisms provided by the backend should be used in favor of this event.
     *
     * @var string
     */
    const FIELD_NORMALIZE = 'search.field.normalize';

    /**
     * Event thrown after a document was populated with fields and prior to it
     * being processed for indexing.
     *
     * @var string
     */
    const DOCUMENT_PRE_INDEX = 'search.docuemnt.pre_index';

    /**
     * Event thrown after a document was processed for indexing.
     *
     * @var string
     */
    const DOCUMENT_POST_INDEX = 'search.docuemnt.post_index';

    /**
     * Event thrown after processing a collection's queue has completed.
     *
     * @var string
     */
    const COLLECTION_POST_INDEX = 'search.collection.post_index';
}
