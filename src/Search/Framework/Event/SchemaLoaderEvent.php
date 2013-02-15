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
 * Event object for config related events.
 */
class SchemaLoaderEvent extends SearchEvent
{
    /**
     *
     * @var CollectionAbstract
     */
    protected $_collection;

    /**
     * The configuration options loaded from another source.
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Constructs a SchemaLoaderEvent object.
     *
     * @param CollectionAgentAbstract $agent
     *
     * @param CollectionAbstract $collection
     *
     */
    public function __construct(CollectionAgentAbstract $agent, CollectionAbstract $collection)
    {
        $this->_agent = $agent;
        $this->_collection = $collection;
    }

    /**
     *
     *
     * @return CollectionAbstract
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Sets the configuration options loaded from another source.
     *
     * If this method is called, then the configuration files will not be
     * sourcesd and the array passed to this method will be used instead.
     *
     * @return SearchConfigEvent
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
        return $this;
    }

    /**
     * Returns the configuration options loaded from another source.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
}
