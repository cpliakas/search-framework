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
 * A wrapper around the field object used when indexing documents.
 *
 * This wrapper allows for setting document specific field properties that fall
 * back to the properties set in the Search_Schema_Field object. This class also
 * implements magic methods for more terse coding in indexing logic.
 *
 * @package    Search
 * @subpackage Index
 */
class Search_Index_Field
{
    /**
     * The schema's feild definition.
     *
     * @var Search_Schema_Field
     */
    protected $_schemaField;

    /**
     * The source data being indexed.
     *
     * @var mixed
     */
    protected $_source;

    /**
     * An array of properties.
     *
     * @var array
     */
    protected $_properties = array();

    /**
     * Sets the field and the source data.
     *
     * @param Search_Schema_Field $field
     *   The field object as it applies to a document.
     * @param $source
     *   The source data being indexed.
     */
    public function __construct(Search_Schema_Field $field, $source)
    {
        $this->_schemaField = $field;
        $this->_source = $source;
    }

    /**
     * Returns the field's schema definition.
     *
     * @return Search_Schema_Field
     */
    public function getField()
    {
        return $this->_schemaField;
    }

    /**
     * Returns the source data.
     *
     * @return mixed
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Gets a field property.
     *
     * @param string $name
     *   The property name.
     *
     * @return mixed|null
     *   The property value, null if the property doesn't exist.
     */
    public function getProperty($name)
    {
        if (isset($this->_properties[$name])) {
            return $this->_properties[$name];
        } else {
            return $this->getField()->getProperty($name);
        }
    }

    /**
     * Sets a field property.
     *
     * @param string $name
     *   The property name.
     * @param mixed $value
     *   The property value.
     *
     * @return Search_Schema_Field
     */
    public function setProperty($name, $value)
    {
        $this->_properties[$name] = $value;
        return $this;
    }

    /**
     * Removes a field property.
     *
     * @param string $name
     *   The property name.
     *
     * @return Search_Schema_Field
     */
    public function removeProperty($name)
    {
        unset($this->_properties[$name]);
        return $this;
    }

    /**
     * Extracts the text from the source and processes it.
     *
     * Extractor plugins are invoked first to get the test from the source. Then
     * then enhancer and normalizer plugins are invoked to process the text for
     * indexing.
     *
     * @param mixed $source
     *   The source data being indexed.
     *
     * @return string
     *
     * @throws Search_Exception
     */
    public function getProcessedText($source)
    {
        $text = null;

        // What hooks are we invoking, and what are the valid base classes?
        $hooks = array('extract', 'enhance', 'normalize');
        $baseClasses = array('Search_Schema_Field_Plugin');

        // Get our plugin queue.
        $pluginQueue = $this->getField()->getPluginQueue($hooks, $baseClasses);

        // Extracts the text then removes extractors form the plugin queue.
        foreach ($pluginQueue['extract'] as $class) {
            $text = Search_Schema_Field_Plugin::factory($class)->extract($this, $source);
        }
        unset($pluginQueue['extract']);

        // Bail if we have no text.
        if (null === $text) {
            throw new Search_Exception('No text.');
        }

        // Executes enhancer and normalizer plugins.
        foreach ($pluginQueue as $hook => $classes) {
            foreach ($classes as $class) {
                $text = Search_Schema_Field_Plugin::factory($class)->$hook($this, $text);
            }
        }

        // Returns processed text.
        return $text;
    }

    /**
     * Gets a field property.
     *
     * @param string $name
     *   The property name.
     *
     * @return mixed|null
     *   The property value, null if the property doesn't exist.
     */
    public function __get($name)
    {
        return $this->getProperty($name);
    }

    /**
     * Sets a field property.
     *
     * @param string $name
     *   The property name.
     * @param mixed $value
     *   The property value.
     */
    public function __set($name, $value)
    {
        $this->setProperty($name, $value);
    }

    /**
     * Removes a field property.
     *
     * @param string $name
     *   The property name.
     */
    public function __unset($name)
    {
        $this->removeProperty($name);
    }
}
