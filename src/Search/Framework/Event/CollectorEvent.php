<?php

/**
 * Search Framework
 *
 * @author    Chris Pliakas <opensource@chrispliakas.com>
 * @copyright 2013 Chris Pliakas
 * @license   http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License (LGPL)
 * @link      https://github.com/cpliakas/search-framework
 */

namespace Search\Framework\Event;

use Search\Framework\CollectionAgentAbstract;

/**
 * Event object for collection related events.
 */
class CollectorEvent extends SearchEvent
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
