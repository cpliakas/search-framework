<?php

/**
 * Search Tools
 *
 * LICENSE
 *
 * This source file is subject to the GNU Lesser General Public License that is
 * bundled with this package in the file LICENSE.txt. It is also available for
 * download at http://www.gnu.org/licenses/lgpl-3.0.txt.
 *
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt
 * @copyright  Copyright (c) 2012 Chris Pliakas <cpliakas@gmail.com>
 */

/**
 * Base class for collection plugins.
 *
 * All collection plugins must extend this class.
 *
 * @package    Search
 * @subpackage Collection
 */
class Search_Collection_Plugin
{
    /**
     * The collection the plugin is registered with.
     *
     * @var Search_Collection_Abstract
     */
    protected $_collection;

    /**
     * Sets the collection the plugin is registered with.
     *
     * @param Search_Collection_Abstract $collection
     *   The collection the plugin is registered with.
     */
    public function __construct(Search_Collection_Abstract $collection)
    {
        $this->_collection = $collection;
    }

    /**
     * Convenience method to get the environment.
     *
     * @return Search_Environment_Abstract
     */
    public function getEnvironment()
    {
        return $this->_collection->getEnvironment();
    }

    /**
     * Convenience method to get schema.
     *
     * @return Search_Schema
     */
    public function getSchema()
    {
        return $this->_collection->getSchema();
    }
}
