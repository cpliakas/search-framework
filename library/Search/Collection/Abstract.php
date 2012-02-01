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
 * Adapter for search collections.
 *
 * Collections are some dat that is being indexed. Examples are filesystems, RSS
 * feeds, or the data model of the CMS you are using. Each collection is passed
 * an environemnt instance and a schema instance to it can be reused across
 * multiple applications.
 *
 * @package    Search
 * @subpackage Collection
 */
abstract class Search_Collection_Abstract extends Search_Plugin_Pluggable
{
    /**
     * The environment indexing the collection.
     *
     * @var Search_Environment_Abstract
     */
    protected $_environment;

    /**
     * The schema mapping the collection being indexed.
     *
     * @var Search_Schema
     */
    protected $_schema;

    /**
     * An array of options.
     *
     * @var array
     */
    protected $_options = array();

    /**
     * A registry of instantiated plugins keyed by class name.
     *
     * @var array
     */
    protected $_plugins = array();

    /**
     * Sets the environment and schema.
     *
     * @param Search_Environment_Abstract $environment
     *   The environment indexing the collection.
     * @param Search_Schema $schema
     *   The schema mapping the collection being indexed.
     * @param array $options
     *   An array of configuration options.
     */
    public function __construct(Search_Environment_Abstract $environment, Search_Schema $schema, array $options = array())
    {
        $this->_environment = $environment;
        $this->_schema = $schema;
        $this->_options = $options;
        $this->init();
    }

    /**
     * Initializes the collection.
     */
    public function init()
    {
        // Initialize collection.
    }

    /**
     * Returns an option.
     *
     * @param string $name
     *   The option key.
     *
     * @return mixed|null
     */
    public function getOption($name)
    {
        return isset($this->_options[$name]) ? $this->_options[$name] : null;
    }

    /**
     * Returns the environment indexing the collection.
     *
     * @return Search_Environment_Abstract
     */
    public function getEnvironment()
    {
        return $this->_environment;
    }

    /**
     * Returns the schema mapping the collection being indexed.
     *
     * @return Search_Schema
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Returns the data queued for indexing.
     *
     * This either returns the raw data structure of the content being indexed
     * or unique IDs that the Search_Environment_Abstract::loadSource() method
     * can use to load the full data structure.
     *
     * @return Iterator
     */
    abstract public function getIndexQueue();

    /**
     * Indexes the collection and invokes the collection plugin hooks.
     */
    public function index()
    {
        $environment = $this->getEnvironment();
        $schema = $this->getSchema();

        // Gets the plugin queue.
        $hooks = array(
            'preIndexCollection',
            'preIndexDocument',
            'postIndexDocument',
            'postIndexCollection',
        );
        $baseClasses = array('Search_Collection_Plugin');
        $pluginQueue = $this->getPluginQueue($hooks, $baseClasses);

        // Invokes each plugin's preIndexCollection hook.
        $environment->preIndexCollection();
        foreach ($pluginQueue['preIndexCollection'] as $class) {
            $this->pluginFactory($class)->preIndexCollection();
        }

        // Iterates over the queue of content to be indexed.
        foreach ($this->getIndexQueue() as $source) {

            // If the queue returns ID's, load the actual source.
            $source = $environment->loadSource($source);

            // Initializes the document.
            $document = $environment->initDocument($source, $schema);

            // Invoke each plugin's preIndexDocument hook.
            foreach ($pluginQueue['preIndexDocument'] as $class) {
                $this->pluginFactory($class)->preIndexDocument($document, $source);
            }

            // Perform actual indexing operations.
            $environment->indexDocument($document, $source, $schema);

            // Invoke each plugin's postIndexDocument hook.
            foreach ($pluginQueue['postIndexDocument'] as $class) {
                $this->pluginFactory($class)->postIndexDocument($document, $source);
            }
        }

        // Invokes each plugin's postIndexCollection hook.
        foreach ($pluginQueue['postIndexCollection'] as $class) {
            $this->pluginFactory($class)->postIndexCollection();
        }
        $environment->postIndexCollection();
    }

    /**
     * Factory for collection plugins.
     *
     * Caches plugin instances in a class property so we only instantiate them
     * once per collection.
     *
     * @param string $class
     *   The name of the class being instantiated.
     *
     * @return Search_Collection_Plugin
     */
    public function pluginFactory($class)
    {
        if (!isset($_plugins[$class])) {
            $_plugins[$class] = new $class($this);
        }
        return $_plugins[$class];
    }
}
