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
 * Adapter for search environments.
 *
 * Environments establish a connection to a backend. The are also usually tied
 * to a single index, so instances where multiple indexed are hosted by the same
 * backend would warrant two different environment instances.
 *
 * @package    Search
 * @subpackage Environment
 */
abstract class Search_Environment_Abstract
{
    /**
     * An array of options.
     *
     * @var array
     */
    protected $_options = array();

    /**
     * The object or resource that communicates with the backend.
     *
     * @var mixed
     */
    protected $_backend;

    /**
     * Passes options to the Search_Environment_Abstract::init() hook.
     */
    public function __construct(array $options = array())
    {
        $this->_options = $options;
        $this->initEnvironment();
    }

    /**
     * Initializes environment, usually establishes a connection to backend.
     *
     * It is best practice to store the connection in the $this->_backend
     * property.
     */
    public function initEnvironment()
    {
        // Initialize environment.
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
     * Returns the object or resource that communicates with the backend.
     *
     * @return mixed
     */
    public function getBackend()
    {
        return $this->_backend;
    }

    /**
     * Hook that allows for per-environment schema alterations.
     */

    /**
     * Hook that allows the environment to take action before indexing.
     *
     * This hook is invoked before the collection plugins' preIndexCollection
     * hook.
     */
    public function preIndexCollection()
    {
        // Override this method to implement this hook.
    }

    /**
     * Loads the source data from the data returned by the index queue.
     *
     * @param mixed $source
     *   The original source object loaded from the queue.
     *
     * @return mixed
     *   The fully loaded source object.
     */
    public function loadSource($source)
    {
        // By default we assume that the data returned by the index queue is the
        // full source data being indexed.
        return $source;
    }

    /**
     * Initializes the document object.
     *
     * @param mixed $source
     *   The source data being indexed.
     * @param Search_Schema $schema
     *   The schema mapping the data being indexed.
     *
     * @return Search_Index_Document
     */
    public function initDocument($source, Search_Schema $schema)
    {
        $document = new Search_Index_Document();
        foreach ($schema as $schemaField) {
            $document->addField(new Search_Index_Field($schemaField, $source));
        }
        return $document;
    }

    /**
     * Convert's the document into the backend's native API for indexing.
     *
     * @param Search_Index_Document $document
     *   The document being indexed.
     * @param mixed $source
     *   The source data being indexed.
     * @param Search_Schema $schema
     *   The schema mapping the data being indexed.
     */
    abstract public function indexDocument(Search_Index_Document $document, $source, Search_Schema $schema);

    /**
     * Hook that allows the environment to take action after indexing.
     *
     * This hook is invoked before the collection plugins' postIndexCollection
     * hook.
     */
    public function postIndexCollection()
    {
        // Override this method to implement this hook.
    }
}
