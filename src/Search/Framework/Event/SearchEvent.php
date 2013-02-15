<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework\Event;

use Search\Framework\CollectionAgentAbstract;
use Symfony\Component\EventDispatcher\Event;

/**
 * Base class for searhc related events.
 */
class SearchEvent extends Event
{
    /**
     * The collection agent performing the operation.
     *
     * @var CollectionAgentAbstract
     */
    protected $_agent;

    /**
     * Sets the collection agent.
     *
     * @param CollectionAgentAbstract $agent
     *   The collection agent performing the operation.
     *
     * @return SearchEvent
     */
    public function setCollectionAgent(CollectionAgentAbstract $agent)
    {
        $this->_agent = $agent;
        return $this;
    }

    /**
     * Returns the collection agent performing the operation.
     *
     * @return CollectionAgentAbstract
     */
    public function getCollectionAgent()
    {
        return $this->_agent;
    }
}
