<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework\Event;

use Search\Framework\CollectionAgentAbstract;

/**
 * Event object for service related events.
 */
class SearchEngineEvent extends SearchEvent
{
    /**
     * Constructs a CollectionEvent object.
     *
     * @param CollectionAgentAbstract $agent
     *   The collection agent performing the operation.
     */
    public function __construct(CollectionAgentAbstract $agent)
    {
        $this->_agent = $agent;
    }
}
