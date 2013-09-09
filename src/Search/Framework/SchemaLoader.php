<?php

/**
 * Search Framework
 *
 * @author    Chris Pliakas <opensource@chrispliakas.com>
 * @copyright 2013 Chris Pliakas
 * @license   http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License (LGPL)
 * @link      https://github.com/cpliakas/search-framework
 */

namespace Search\Framework;

use Search\Framework\Event\SchemaLoaderEvent;
use Symfony\Component\Yaml\Yaml;

/**
 *
 */
class SchemaLoader
{
    /**
     *
     *
     * @var CollectionAgentAbstract
     */
    protected $_agent;

    /**
     *
     *
     * @var CollectionAbstract
     */
    protected $_collection;

    /**
     * Constructs a SchemaLoader object.
     *
     * @parma CollectionAgentAbstract $agent
     *
     * @param CollectionAbstract $collection
     *   The configurable object that the config file is being read for.
     */
    public function __construct(CollectionAgentAbstract $agent, CollectionAbstract $collection)
    {
        $this->_agent = $agent;
        $this->_collection = $collection;
    }

    /**
     *
     * @return string
     */
    public function getConfDir()
    {
        $reflection = new \ReflectionClass($this->_collection);
        $class_dir = dirname($reflection->getFileName());
        return realpath($class_dir . '/../../../../conf');
    }

    /**
     * Parses configuration options from YAML configuration files related to the
     * configurable object.
     *
     * This method throws the SearchEvents::CONFIG_LOAD event. If configurations
     * are loaded during this event, processing stops and the directories are
     * not scanned for configuration files.
     *
     * @return array
     *   An associative array of options.
     *
     * @throws ParseException
     */
    public function load()
    {
        $log = $this->_agent->getLogger();
        $context = array('collection' => $this->_collection->getId());

        // Allow the schema to be loaded from another source.
        $event = new SchemaLoaderEvent($this->_agent, $this->_collection);
        $this->_agent->dispatchEvent(SearchEvents::SCHEMA_LOAD, $event);

        if (!$options = $event->getOptions()) {
            if ($conf_dir = $this->getConfDir()) {

                $filename = $this->_collection->getConfigBasename() . '.yml';
                $filepath = $conf_dir . '/' . $filename;
                $context['filepath'] = $filepath;

                $log->debug('Attempting to parse schema configuration file', $context);
                $options = Yaml::parse($filepath);
                $log->debug('Schema options parsed from configuration file', $context);

            } else {
                $log->notice('Path to conf directory could not be resolved', $context);
            }
        } else {
            $log->debug('Schema configuration loaded from an external source.', $context);
        }

        $schema = new Schema();
        $schema->build($options);
        return $schema;
    }
}
