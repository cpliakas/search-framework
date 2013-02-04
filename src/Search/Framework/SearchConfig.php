<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

use Search\Framework\Event\SearchConfigEvent;
use Symfony\Component\Yaml\Yaml;

/**
 * Reads and stores configuration options.
 */
class SearchConfig
{
    /**
     * Config type for collections.
     */
    const COLLECTION = 'collection';

    /**
     * A static cache of parsed configurations keyed by filename.
     *
     * @var array
     */
    protected static $_configCache = array();

    /**
     * An associative array keyed by type, e.g. "collection", to an array of
     * directories that will be scanned for configuration files.
     *
     * @var array
     */
    protected static $_configDirs = array();

    /**
     * An associative array of configuration options read from the file.
     *
     * @var array
     */
    protected $_options;

    /**
     * Constructs a SearchConfig object.
     *
     * @param array $options
     *   The configuration options.
     */
    public function __construct(array $options = array())
    {
        $this->_options = $options;
    }

    /**
     * Helper method for dispatching a config event.
     *
     * @param string $event_name
     *   The name of the event to dispatch.
     * @param SearchConfigEvent $event
     *   The SearchConfigEvent event object passed to the handlers / listeners.
     *
     * @see Symfony::Component::EventDispatcher::EventDispatcher::dispatch().
     */
    public function dispatchEvent($event_name, SearchConfigEvent $event)
    {
        SearchRegistry::getDispatcher()->dispatch($event_name, $event);
    }

    /**
     * Merges config arrays.
     *
     * @param array $current
     *   The current set of configs being merged into.
     * @param array $new
     *   The new configs being merged into the current.
     *
     * @return array
     *   The merged configs.
     *
     * @todo Implement a better system.
     */
    public static function mergeConfigs(array $current, array $new)
    {
        return array_merge($current, $new);
    }

    /**
     * Loads configuration options.
     *
     * @param string $type
     *   The type of configuration, e.g. "collection".
     * @param string $filepath
     *   The path to the configuration file being read. The basename is
     *   extracted from the filepath which is used to find other config files in
     *   the specified directories.
     *
     * @return SearchConfig
     *
     * @throws ParseException
     */
    public function load($type, $filepath)
    {
        $filename = basename($filepath);
        $config_dirs = isset(self::$_configDirs[$type]) ? self::$_configDirs[$type] : array();
        $config_dirs[] = dirname($filepath);

        $event = new SearchConfigEvent($this, $type, $filename, $config_dirs);
        $this->dispatchEvent(SearchEvents::CONFIG_LOAD, $event);
        $options = $event->getOptions();

        if (!$options) {
            $options = self::loadFromFile($filename, $config_dirs);
        }

        $this->_options = self::mergeConfigs($this->_options, $options);
        return $this;
    }

    /**
     * Scans for the appropriate configuration file, returns parsed options.
     */
    public static function loadFromFile($filename, array $config_dirs)
    {
        foreach ($config_dirs as $config_dir) {
            $filepath = $config_dir . '/' . $filename;
            if (isset(self::$_configCache[$filepath])) {
                return self::$_configCache[$filepath];
            } elseif (file_exists($filepath)) {
                return Yaml::parse($filepath);
            }
        }
        return array();
    }

    /**
     * Sets or overrides a configuration option.
     *
     * @param string $option
     *   The unique name of the configuration option.
     * @param mixed $value
     *   The configuration option's value.
     *
     * @return SearchCollectionAbstract
     */
    public function setOption($option, $value)
    {
        $this->_options[$option] = $value;
        return $this;
    }

    /**
     * Returns a configuration option's value.
     *
     * @param string $option
     *   The name of the configuration option.
     * @param mixed $default
     *   The default value returned if the configuration option is not set,
     *   defaults to null.
     *
     * @return mixed
     */
    public function getOption($option, $default = null)
    {
        return isset($this->_options[$option]) ? $this->_options[$option] : $default;
    }

    /**
     * Returns the associative array of configuration options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
}
