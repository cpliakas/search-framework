<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework\Event;

use Search\Framework\SearchConfig;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for collection related events.
 */
class SearchConfigEvent extends Event
{
    /**
     * The config object populated with the default options.
     *
     * @var SearchConfig
     */
    protected $_config;

    /**
     * The name of the configuration file being read.
     *
     * @var string
     */
    protected $_filename;

    /**
     * An array of directories that that will be scanned for the configuration
     * file. The directories are the return array of the self::getConfigDirs()
     * method with the mapped subdirectory appended.
     *
     * @var array
     */
    protected $_configDirs;

    /**
     * The configuration options loaded from another source.
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Constructs a SearchConfigEvent object.
     *
     * @param SearchConfig $config
     *   The config object populated with the default options.
     * @param string $filename
     *   The name of the configuration file being read.
     * @param array $config_dirs
     *   An array of directories that that will be scanned for the configuration
     *   file. The directories are the return array of the self::getConfigDirs()
     *   method with the mapped subdirectory appended.
     */
    public function __construct(SearchConfig $config, $filename, array $config_dirs)
    {
        $this->_config = $config;
        $this->_filename = $filename;
        $this->_configDirs = $config_dirs;
    }

    /**
     * Returns he config object populated with the default options.
     *
     * @return SearchConfig
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Returns the name of the config file being read.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * Returns the directories that will be scanned for the configuration files.
     *
     * @return array
     */
    public function getConfigDirs()
    {
        return $this->_configDirs;
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