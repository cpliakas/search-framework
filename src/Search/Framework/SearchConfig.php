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
 * Loads and stores configuration options.
 *
 * This method also contains some static methods than can be used globally to
 * perfrom various actions such as merging configurations.
 */
class SearchConfig
{
    /**
     * An associative array of configuration options usually passed through the
     * constructor and optionally loaded from a YAML configuration file.
     *
     * The options passed at runtime should have priority over configs loaded
     * from files or external sources.
     *
     * @var array
     */
    protected $_options;

    /**
     * An associative array of base directories that will be scanned for
     * configuration files.
     *
     * The configuration files exist in subdirectories if the directories based
     * on the SearchConfigurableInterface class. See the self::mapSubDirectory()
     * method for the class mappings.
     *
     * Directories are iterated over in a FILO manner, meaning that the
     * directories at the end of the array are scanned first. This allows the
     * application to add more granular configuration directories that are
     * scanned before the generic default directory.
     *
     * @var array
     */
    protected static $_configDirs = array();

    /**
     * A static cache of parsed configuration options keyed by filename.
     *
     * This prevents the configuration files from being read and parsed multiple
     * times on a single page load.
     *
     * @var array
     */
    protected static $_configCache = array();

    /**
     * Constructs a SearchConfig object.
     *
     * Sets the runtime configuration options.
     *
     * @param array $options
     *   Configuration options passed at runtime that will override any
     *   configurations loaded via YAML files or an external sources.
     */
    public function __construct(array $options = array())
    {
        $this->_options = $options;
    }

    /**
     * Sets or overwrites a configuration option.
     *
     * @param string $index
     *   The unique name of the configuration option.
     * @param mixed $value
     *   The configuration option's value.
     *
     * @return SearchConfig
     */
    public function setOption($index, $value)
    {
        $this->_options[$index] = $value;
        return $this;
    }

    /**
     * Returns a configuration option's value.
     *
     * @param string $index
     *   The unique name of the configuration option.
     * @param mixed $default
     *   The default value returned if the configuration option is not set,
     *   defaults to null.
     *
     * @return mixed
     */
    public function getOption($index, $default = null)
    {
        return isset($this->_options[$index]) ? $this->_options[$index] : $default;
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

    /**
     * Sets the directories that will be scanned for YAML configuration files.
     *
     * Directories are stored in a FILO manner, meaning the directories at the
     * end of the array are scanned first.
     *
     * @param array $directories
     *   The directories that will be scanned for YAML configuration files.
     */
    public static function setConfigDirs(array $directories)
    {
        foreach ($directories as $directory) {
            self::addConfigDir($directory);
        }
    }

    /**
     * Adds a directory that will be scanned for YAML configuration files.
     *
     * Directories are added in a FILO manner, meaning the directories added
     * last are scanned first.
     *
     * @param array $directory
     *   Adds a directory that will be scanned for YAML configuration files.
     */
    public static function addConfigDir($directory)
    {
        if ($realpath = realpath($directory)) {
            self::$_configDirs[] = $realpath;
        }
    }

    /**
     * Returns the directories that will be scanned for YAML configuration
     * files.
     *
     * @return array
     */
    public static function getConfigDirs()
    {
        return self::$_configDirs;
    }

    /**
     * Returns a project's root directory.
     *
     * @param SearchConfigurableInterface $configurable
     *   An instance of the configurable class.
     *
     * @return string
     *   The relative path to the root directory.
     */
    public function getRootDir(SearchConfigurableInterface $configurable)
    {
        $reflection = new \ReflectionClass($configurable);
        $class_dir = dirname($reflection->getFileName());
        return $class_dir . '/../../../..';
    }

    /**
     * Returns the path to the directory containing the default configuration
     * file for the configurable class.
     *
     * The following directory structure is expected in the library's root:
     *
     * `{src-dir}/conf/{mapped-subdir}`
     *
     * - {src-dir}: The name of the top level directory containing the code.
     * - {mapped-subdir}: The subdirectory mapped from the class. See the
     *   self::mapSubDirectory() method for the class mappings.
     *
     * @param SearchConfigurableInterface $configurable
     *   An instance of the configurable class.
     *
     * @return string|false
     *   The absolute path to the `{src-dir}/conf` directory, false if the
     *   directory doesn't exist or couldn't be resolved.
     */
    public function getDefaultConfigDir(SearchConfigurableInterface $configurable)
    {
        $root_dir = self::getRootDir($configurable);
        return realpath($root_dir . '/conf');
    }

    /**
     * Helper method for dispatching a configuration related event.
     *
     * @param string $event_name
     *   The name of the event being thrown.
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
     * Maps a class to a subdirectory containing the configuration files.
     *
     * @param SearchConfigurableInterface $configurable
     *   The configurable object that configurations are being retrieved for.
     *
     * @return string
     */
    public function mapSubDirectory(SearchConfigurableInterface $configurable)
    {
        switch (true) {
            case $configurable instanceof SearchCollectionAbstract:
               return '/collection';

            case $configurable instanceof SearchServiceAbstract:
               return '/service';

            default:
                return '';
        }
    }

    /**
     * Parses configuration options from YAML configuration files related to the
     * configurable object.
     *
     * This method throws the SearchEvents::CONFIG_LOAD event. If configurations
     * are loaded during this event, processing stops and the directories are
     * not scanned for configuration files.
     *
     * @param SearchConfigurableInterface $configurable
     *   The configurable object that configurations are being parsed for.
     *
     * @return SearchConfig
     *
     * @throws ParseException
     */
    public function load(SearchConfigurableInterface $configurable)
    {
        $conf_dir = $this->getDefaultConfigDir($configurable);
        $subdir = $this->mapSubDirectory($configurable);
        $filename = $configurable->getId() . '.yml';

        // Prepend the default directory to the stack of of directories.
        $config_dirs = self::getConfigDirs();
        array_unshift($config_dirs, $conf_dir);

        // Add the subdirectory to each directory in the stack.
        foreach ($config_dirs as $key => $config_dir) {
            $config_dirs[$key] = $config_dir . $subdir;
        }

        // Throw the SearchEvents::CONFIG_LOAD event to load the configurations
        // from some other source.
        $event = new SearchConfigEvent($this, $filename, $config_dirs);
        $this->dispatchEvent(SearchEvents::CONFIG_LOAD, $event);
        $options = $event->getOptions();

        // If no options were loaded, scan the files in the config directories.
        if (!$options) {
            $options = self::scanConfigDirs($filename, $config_dirs);
        }

        // In this case, the runtime configs are passed as the "new" argument
        // since they should always override the options that were loaded form
        // the config files.
        $this->_options = self::mergeConfigs($options, $this->_options);
        return $this;
    }

    /**
     * Scans the directories for configuration files and parses the first one it
     * encounters.
     *
     * The directories are scanned in the reverse order that they are passed.
     * Once a file is found, it is parsed and returned at which point the
     * scanning stops.
     *
     * @param string $filename
     *   The name of the YAML configuration file being scanned for.
     * @param array $config_dirs
     *   An array of directories that that will be scanned for the configuration
     *   file. The directories are the return array of the self::getConfigDirs()
     *   method with the mapped subdirectory appended.
     *
     * @return array
     *   The parsed configuraitons, and empty array if no configuration files
     *   were found.
     *
     * @throws ParseException
     */
    public static function scanConfigDirs($filename, array $config_dirs)
    {
        foreach (array_reverse($config_dirs) as $config_dir) {
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
     * Merges config arrays.
     *
     * This is a half-assed approach that will allow us to move forward. We can
     * boil the ocean at a later point in time.
     *
     * @param array $current
     *   The current set of configs being merged into.
     * @param array $new
     *   The new configs being merged into the current.
     *
     * @return array
     *   The merged configs.
     *
     * @todo Implement a better merging system.
     */
    public static function mergeConfigs(array $current, array $new)
    {
        return array_merge($current, $new);
    }
}
