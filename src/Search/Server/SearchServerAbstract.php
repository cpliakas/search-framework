<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Server;

use Search\Collection\SearchCollectionAbstract;

/**
 *
 */
abstract class SearchServerAbstract
{
    /**
     * An array of SearchCollectionAbstract objects keyed by machine name.
     */
    protected $_collections = array();

    /**
     * Add a collection that is associated with this server.
     */
    public function addCollection($name, SearchCollectionAbstract $collection)
    {

    }
}
