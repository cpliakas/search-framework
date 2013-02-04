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
class SearchRegistry extends \ArrayObject
{
    /**
     * Registry key for the event dispatcher used throughout this library.
     */
    const DISPATCHER = 'dispatcher';

    /**
     * An instance of this class.
     *
     * @var SearchRegistry
     */
    private static $_registry = null;

    /**
     * Returns a statically cached instance of this class.
     *
     * @return SearchRegistry
     */
    public static function getInstance()
    {
        if (null === self::$_registry) {
            self::$_registry = new self(array(), parent::ARRAY_AS_PROPS);
        }
        return self::$_registry;
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
        $registry = self::getInstance();
        if (!$registry->offsetExists($index)) {
            throw new \InvalidArgumentException('Variable "' . $index .'" is not registered.');
        }
        return $registry->offsetGet($index);
    }

    /**
     * Returns a variable in the registry.
     *
     * @param string $index
     *   The name of the registered variable.
     * @param mixed $value
     *   The value of the variable being registered.
     */
    public static function set($index, &$value)
    {
        $registry = self::getInstance();
        $registry->offsetSet($index, $value);
    }

    /**
     * Returns true if variable is registered.
     *
     * @return boolean
     */
    public static function isRegistered($index)
    {
        $registry = self::getInstance();
        return $registry->offsetExists($index);
    }

    /**
     * Sets the dispatcher used throughout the Search Framework library.
     *
     * @param EventDispatcher $dispatcher
     *   The dispatcher used by the Search Framework library.
     */
    public static function setDispatcher(EventDispatcher $dispatcher)
    {
        self::set(self::DISPATCHER, $dispatcher);
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
        $registry = self::getInstance();
        if (!$registry->offsetExists(self::DISPATCHER)) {
            $dispatcher = new EventDispatcher();
            $registry->offsetSet(self::DISPATCHER, $dispatcher);
        }
        return $registry->offsetGet(self::DISPATCHER);
    }
}
