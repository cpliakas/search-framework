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
 * Base class for field plugins.
 *
 * All field plugins must extend this class, as it also acts as a registry so
 * that plugins are only instantiated once.
 *
 * @package    Search
 * @subpackage Schema
 */
class Search_Schema_Field_Plugin
{
    /**
     * An array keyed by class name to Search_Schema_Field_Plugin objects.
     *
     * @var array
     */
    static protected $_registry = array();

    /**
     * Factory method for field plugins.
     *
     * @param string $class
     *   The name of the plugin class.
     *
     * @return Search_Schema_Field_Plugin
     *
     * @throws Search_Exception
     */
    static public function factory($class)
    {
        if (!isset(self::$_registry[$class])) {
            if (is_subclass_of($class, 'Search_Schema_Field_Plugin')) {
                self::$_registry[$class] = new $class();
            } else {
                throw new Search_Exception('Not valid plugin.');
            }
        }
        return self::$_registry[$class];
    }
}
