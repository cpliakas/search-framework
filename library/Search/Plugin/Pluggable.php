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
 * Implementation of the Search_Plugin_Pluggable_Interface interface.
 *
 * This class should be the base class for all packages that are pluggable.
 *
 * @package    Search
 * @subpackage Plugin
 */
class Search_Plugin_Pluggable implements Search_Plugin_Pluggable_Interface
{
    /**
     * An array of registered plugin class names.
     *
     * @var array
     */
    protected $_registeredPlugins = array();

    /**
     * The weight of the next plugin.
     *
     * @var int
     */
    protected $_nextWeight = 0;

    /**
     * Registers a plugin.
     *
     * @param string $class
     *   The plugin's class name.
     * @param int|null $weight
     *   The weight of the plugin, pass null to use the next increment.
     *
     * @return Search_Plugin_Pluggable_interface
     */
    public function registerPlugin($class, $weight = null)
    {
        if (null === $weight) {
            $weight = $this->_nextWeight++;
        }
        $this->_registeredPlugins[$class] = array(
          'class' => $class,
          'weight' => (int) $weight,
        );
        return $this;
    }

    /**
     * Removes a plugin from the registry.
     *
     * @param string $class
     *   The plugin's class name.
     *
     * @return Search_Plugin_Pluggable_interface
     */
    public function unregisterPlugin($class)
    {
        unset($this->_registeredPlugins[$class]);
        return $this;
    }

    /**
     * Returns all registered plugins.
     *
     * @return array
     */
    public function getRegisteredPlugins()
    {
        return $this->_registeredPlugins;
    }

    /**
     * Gets a plugin's weight.
     *
     * @param string $class
     *   The plugin's class name.
     *
     * @return int|false
     *   The weight, false if the class is not registered.
     */
    public function getPluginWeight($class)
    {
        if (isset($this->_registeredPlugins[$class])) {
            return $this->_registeredPlugins[$class];
        } else {
            return false;
        }
    }

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
    public function setPluginWeight($class, $weight)
    {
        if (isset($this->_registeredPlugins[$class])) {
            $this->_registeredPlugins[$class] = $weight;
        }
        return $this;
    }

    /**
     * Gets a queue of plugins that implement the passed hooks.
     *
     * @param array $hooks
     *   An array of hooks being invoked in the order they will be invoked.
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
    public function getPluginQueue($hooks, $baseClasses = array(), $skipInvalid = false)
    {
        // Initialized plugin queue keyed by hooks to empty arrays.
        $pluginQueue = array_combine($hooks, array_fill(0, count($hooks), array()));

        // Gets plugins, sorts by weight.
        $plugins = $this->getRegisteredPlugins();
        uasort($plugins, array($this, 'sortWeight'));

        // Iterates over the registered plugins and builds the queue.
        foreach ($plugins as $pluginInfo) {

            // Checks if plugin is valid.
            if ($baseClasses) {
                $isValid = false;
                foreach ($baseClasses as $baseClass) {
                    if (is_subclass_of($pluginInfo['class'], $baseClass)) {
                        $isValid = true;
                        break;
                    }
                }
                if (!$isValid) {
                    // Continue on or throw Exception?
                    if ($skipInvalid) {
                        continue;
                    } else {
                        throw new Search_Exception('Invalid plugin');
                    }
                }
            }

            // Checks which hooks the plugin implements.
            foreach ($hooks as $hook) {
                if (method_exists($pluginInfo['class'], $hook)) {
                    $pluginQueue[$hook][] = $pluginInfo['class'];
                }
            }
        }

        // Returns the plugin queue.
        return $pluginQueue;
    }

    /**
     * Sorts a list of plugins by theie weight property.
     *
     * Useful as a callback to uasort().
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    public function sortWeight(array $a, array $b)
    {
        $a_weight = isset($a['weight']) ? $a['weight'] : 0;
        $b_weight = isset($b['weight']) ? $b['weight'] : 0;
        if ($a_weight == $b_weight) {
            return 0;
        }
        return ($a_weight < $b_weight) ? -1 : 1;
    }
}
