<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework\Event;

use Search\Framework\SearchCollectionAbstract;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for collection related events.
 */
class SearchCollectionEvent extends Event
{
    /**
     * The collection being processed.
     *
     * @var SearchCollectionAbstract
     */
    protected $_collection;

    /**
     * Constructs a SearchCollectionEvent object.
     *
     * @param SearchCollectionAbstract $collection
     *   The collection being processed.
     */
    public function __construct(SearchCollectionAbstract $collection)
    {
        $this->_collection = $collection;
    }

    /**
     * Returns the collection being processed.
     *
     * @return SearchCollectionAbstract
     */
    public function getCollection()
    {
        return $this->_collection;
    }
}
