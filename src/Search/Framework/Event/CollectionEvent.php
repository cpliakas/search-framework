<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework\Event;

use Search\Framework\CollectionAbstract;
use Search\Framework\CollectionAgentAbstract;

/**
 * Event object for collection related events.
 */
class CollectionEvent extends SearchEvent
{
    /**
     * The collection being processed.
     *
     * @var CollectionAbstract
     */
    protected $_collection;

    /**
     * Constructs a CollectionEvent object.
     *
     * @param CollectionAgentAbstract $agent
     *   The collection agent performing the operation.
     * @param CollectionAbstract $collection
     *   The collection being processed.
     */
    public function __construct(CollectionAgentAbstract $agent, CollectionAbstract $collection)
    {
        $this->_agent = $agent;
        $this->_collection = $collection;
    }

    /**
     * Returns the collection being processed.
     *
     * @return CollectionAbstract
     */
    public function getCollection()
    {
        return $this->_collection;
    }
}
