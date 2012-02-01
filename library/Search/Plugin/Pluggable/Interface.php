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
 * Interface for pluggable packages.
 *
 * @package    Search
 * @subpackage Plugin
 */
interface Search_Plugin_Pluggable_Interface
{
    /**
     * Registers a plugin.
     *
     * @param string $class
     *   The plugin's class name.
     * @param int|null $weight
     *   The weight of the plugin, pass null to use the next increment.
     *
     * @return Search_Plugin_Pluggable_Interface
     */
    public function registerPlugin($class, $wieght = null);

    /**
     * Removes a plugin from the registry.
     *
     * @param string $class
     *   The plugin's class name.
     *
     * @return Search_Plugin_Pluggable_Interface
     */
    public function unregisterPlugin($class);

    /**
     * Returns all registered plugins.
     *
     * @return array
     */
    public function getRegisteredPlugins();

    /**
     * Sets the plugin weight.
     *
     * @param string $class
     *   The plugin's class name.
     * @param int|null $weight
     *   The weight of the plugin.
     *
     * @return Search_Plugin_Pluggable_interface
     */
    public function setPluginWeight($class, $weight);

    /**
     * Gets a plugin's weight.
     *
     * @param string $class
     *   The plugin's class name.
     *
     * @return int|false
     *   The weight, false if the class is not registered.
     */
    public function getPluginWeight($class);

    /**
     * Gets a queue of plugins that implement the passed hooks.
     *
     * @param array $hooks
     *   An array of hooks being invoked in the order they are invoked.
     * @param array $baseClasses
     *   The plugins must be an instance of at least one these base classes.
     * @param bool $skipInvalid
     *   If a plugin is not an instance of a base class, skip and move onto the
     *   next one or throw an Exception?
     *
     * @return array
     *   An array of plugins keyed by the hooks they implement.
     *
     * @throws Search_Exception
     */
    public function getPluginQueue($hooks, $baseClasses = array(), $skipInvalid = false);
}
