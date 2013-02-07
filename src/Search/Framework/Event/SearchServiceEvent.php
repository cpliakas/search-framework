<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework\Event;

use Search\Framework\SearchServiceAbstract;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for service related events.
 */
class SearchServiceEvent extends Event
{
    /**
     * The search service executing the indexing operation.
     *
     * @var SearchServiceAbstract
     */
    protected $_service;

    /**
     * Constructs a SearchServiceEvent object.
     *
     * @param SearchServiceAbstract $service
     *   The search service that is indexing the document.
     */
    public function __construct(SearchServiceAbstract $service)
    {
        $this->_service = $service;
    }

    /**
     * Returns the search service executing the indexing operation.
     *
     * @return SearchServiceAbstract
     */
    public function getService()
    {
        return $this->_service;
    }
}
