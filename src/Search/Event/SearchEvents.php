<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Event;

/**
 * A list of events throws by the Search Framework library.
 */
class SearchEvents
{
    /**
     * Event thrown after a field is extracted from the source data.
     *
     * This event is intended to add metadata or pull additional information
     * from external sources, whether that source is a related piece of content
     * or data from an external source.
     *
     * @var string
     */
    const FIELD_ENRICH = 'search.field.enrich';

    /**
     * Event thrown after a enrichment.
     *
     * This event is intended to be used to clean and normalize the content in
     * order to prepare it for indexing. When possible, the native normalization
     * mechanisms provided by the backend should be used in favor of this event.
     *
     * @var string
     */
    const FIELD_NORMALIZE = 'search.field.normalize';

    /**
     * Event thrown just prior to indexing a document.
     *
     * @var string
     */
    const FIELD_NORMALIZE = 'search.docuemnt.alter';
}
