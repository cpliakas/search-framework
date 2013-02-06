<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * A static variable registry for components that are used across classes.
 */
class SearchRegistry
{
    /**
     * Registry key for the event dispatcher used throughout this library.
     */
    const DISPATCHER = 'dispatcher';

    /**
     * Registry key for the queue used throughout this library.
     */
    const QUEUE = 'queue';

    /**
     * An instance of this class.
     *
     * @var SearchRegistry
     */
    private static $_instance = null;

    /**
     * An associative array of registered variables.
     *
     * @var array
     */
    private static $_registry = array();

    /**
     * Returns a statically cached instance of this class.
     *
     * @return SearchRegistry
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Returns a variable in the registry.
     *
     * @param string $index
     *   The name of the registered variable.
     *
     * @return mixed
     *   A reference to the registered variable.
     *
     * @throws \InvalidArgumentException
     */
    public static function get($index)
    {
        if (!isset(self::$_registry[$index])) {
            throw new \InvalidArgumentException('Variable "' . $index .'" is not registered.');
        }
        return self::$_registry[$index];
    }

    /**
     * Returns a variable in the registry.
     *
     * @param string $index
     *   The name of the registered variable.
     * @param mixed &$value
     *   The value of the variable being registered.
     */
    public static function set($index, $value)
    {
        self::$_registry[$index] = $value;
    }

    /**
     * Removes a variable in the registry.
     *
     * @param string $index
     *   The name of the registered variable.
     */
    public static function remove($index)
    {
        unset(self::$_registry[$index]);
    }

    /**
     * Returns true if variable is registered.
     *
     * @return boolean
     */
    public static function isRegistered($index)
    {
        return isset(self::$_registry[$index]);
    }

    /**
     * Sets the dispatcher used throughout the Search Framework library.
     *
     * @param EventDispatcher $dispatcher
     *   The dispatcher used by the Search Framework library.
     */
    public static function setDispatcher(EventDispatcher $dispatcher)
    {
        self::$_registry[self::DISPATCHER] = $dispatcher;
    }

    /**
     * Returns the dispatcher used throughout the Search Framework library.
     *
     * If the dispatcher is not set, one is instantiated and set.
     *
     * @return EventDispatcher
     */
    public static function getDispatcher()
    {
        if (!isset(self::$_registry[self::DISPATCHER])) {
            self::$_registry[self::DISPATCHER] = new EventDispatcher();
        }
        return self::$_registry[self::DISPATCHER];
    }

    /**
     *
     *
     * @param SearchQueueAbstract $queue
     *
     */
    public static function setQueue(SearchQueueAbstract $queue)
    {
        self::$_registry[self::QUEUE] = $queue;
    }

    /**
     *
     * 
     * @return SearchQueueAbstract
     */
    public static function getQueue()
    {
        if (!isset(self::$_registry[self::QUEUE])) {
            self::$_registry[self::QUEUE] = new SearchQueueIteratorQueue();
        }
        return self::$_registry[self::QUEUE];
    }
}
