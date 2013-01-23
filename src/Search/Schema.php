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
 * Maps the data being indexed to the search backend.
 *
 * A schema is a group of fields which contain information about the collection
 * being indexed. Most often this object will be a 1 to 1 mapping of the source
 * data to the schema defined by the backend, however there may be instances
 * where this class is used as a more advanced data mapper.
 *
 * @package    Search
 * @subpackage Schema
 */
class Search_Schema implements Iterator
{
    /**
     * A subtag that specifies a language neutral field.
     */
    const LANGUAGE_NEUTRAL = 'und';

    /**
     * Constants for standard datatypes that should be recognized and handled by
     * all environment adapters.
     */
    const TYPE_FULLTEXT = 'fulltext';
    const TYPE_STRING = 'string';
    const TYPE_INT = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOL = 'boolean';
    const TYPE_BINARY = 'binary';
    const TYPE_DATE = 'date';

    /**
     * An associative array keyed by field name to Search_Schema_Field objects.
     *
     * @var array
     */
    protected $_schemaFields = array();

    /**
     * An associative array keyed by field name to Search_Schema_Field objects.
     *
     * @var array
     */
    protected $_defaultOptions = array();

    /**
     * The machine name of field containing the unique identifiers.
     *
     * @var string|false
     */
    protected $_uniqueIdentifier = false;

    /**
     * Optionally adds fields to the schema it options were passed.
     *
     * @param array $options
     *   An asociative array of options containing:
     *   - schema: The $options parameter passed to Search_Schema::build().
     *   - schemaDefaults: The $options parameter passed to
     *     Search_Schema::setDefaults().
     */
    public function __construct(array $options = array())
    {
        $options += array(
            'schema' => array(),
            'schemaDefaults' => array(),
        );

        $this
            ->setDefaultOptions($options['schemaDefaults'])
            ->build($options['schema']);
    }

    /**
     * Sets the default options for all fields attached to this schema.
     *
     * @param array $defaults
     *   An associative array of defaults containing:
     *   - type: The data type of the field, for example "text" or "int". See
     *     the Search_Schema::TYPE_* class constants. Defaults to "text".
     *   - field: The name of the field in the index. Defaults to null meaning
     *     the machine name of the field is used.
     *   - language: The subtag as specified in the IANA Language Subtag
     *     Registry at http://www.iana.org/assignments/language-subtag-registry.
     *   - plugins: An array of field plugin class names.
     *
     * @return Search_Schema
     */
    public function setDefaultOptions(array $defaults)
    {
        $this->_defaultOptions = $defaults + array(
            'type' => self::TYPE_FULLTEXT,
            'field' => null,
            'language' => self::LANGUAGE_NEUTRAL,
            'unique' => false,
            'plugins' => array(),
        );

        return $this;
    }

    /**
     * Returns the default options.
     *
     * @return array
     *
     * @see Search_Schema::setDefaultOptions()
     */
    public function getDefaultOptions()
    {
        return $this->_defaultOptions;
    }

    /**
     * Builds the schema's fields via the passed options.
     *
     * @param array $options
     *   An associative array of options containing:
     *   - name: The machine name of the field used to identify the data in
     *     the source object. For example, if the schema is mapping an RSS
     *     feed, names might be "title", "description", or "link".
     *   - field: The name of the field in the index. Defaults to null meaning
     *     the machine name of the field is used.
     *   - type: The data type of the field, for example "text" or "int". See
     *     the Search_Schema::TEXT_* class constants. Defaults to "text".
     *   - language: The subtag as specified in the IANA Language Subtag
     *     Registry at http://www.iana.org/assignments/language-subtag-registry.
     *   - plugins: An array of field plugin class names.
     *
     * @return Search_Schema
     *
     * @throws Search_Exception
     */
    public function build(array $options)
    {
        $defaults = $this->getDefaultOptions();

        // Iterates over configuration options and adds fields to shcema.
        foreach ($options as $name => $config) {
            // Strings are treated as the name.
            if (!is_array($config)) {
                $config = array('name' => (string) $config);
            }

            // Merges in defaults.
            $config += $defaults;

            // The machine name defaults to the array key but can be explicitly
            // specified in the config array as well.
            if (!isset($config['name'])) {
               $config['name'] = $name;
            }

            // If this field is unique, set it as the unique identifier.
            if ($config['unique']) {
                $this->setUniqueIdentifier($config['name']);
            }

            // Gets initial set of plugins, keys by plugin class.
            if ($defaults['plugins']) {
                $plugins = array_combine($defaults['plugins'], $defaults['plugins']);
            } else {
                $plugins = array();
            }

            // Gets plugins from config, makes sure field-specific plugins are
            // added to the end of the array so they are executed last.
            foreach ($config['plugins'] as $class) {
                if (!isset($plugins[$class])) {
                    $plugins[$class] = $class;
                }
            }

            // Instantiates field object, registers plugins, and adds to schema.
            $field = new Search_Schema_Field($config['name'], $config['field'], $config['type'], $config['language']);
            foreach ($plugins as $class) {
                $field->registerPlugin($class);
            }
            $this->addField($field);

            // Adds any additional properties.
            $properties = array_diff_key($defaults, $config);
            foreach ($properties as $property => $value) {
                $field->setProperty($property, $value);
            }
        }

        return $this;
    }

    /**
     * Attaches a field to this schema.
     *
     * @param Search_Schema_Field $field
     *
     * @return Search_Schema
     */
    public function addField(Search_Schema_Field $field)
    {
        $name = $field->getProperty('name');
        $this->_schemaFields[$name] = $field;
        return $this;
    }

    /**
     * Returns a field attached to this schema.
     *
     * @param string $name
     *   The machine name of the field.
     *
     * @return Search_Schema_Field|null
     *   The field object if the field is attached, null otherwise,
     */
    public function getField($name)
    {
        return isset($this->_schemaFields[$name]) ? $this->_schemaFields[$name] : null;
    }

    /**
     * Removes a field from the schema.
     *
     * @param string $name
     *   The machine name of the field.
     *
     * @return Search_Schema
     */
    public function removeField($name)
    {
        unset($this->_schemaFields[$name]);
        return $this;
    }

    /**
     * Sets the machine name of the field containing the unique identifier.
     *
     * @param string $name
     *   The machine name of the field.
     *
     * @return Search_Schema
     */
    public function setUniqueIdentifier($name)
    {
        $this->_uniqueIdentifier = $name;
        return $this;
    }

    /**
     * Gets the machine name of the field containing the unique identifier.
     *
     * @return string|false
     */
    public function getUniqueIdentifier()
    {
        return $this->_uniqueIdentifier;
    }

    /**
     * Implements Iterator::rewind().
     */
    function rewind()
    {
        reset($this->_schemaFields);
    }

    /**
     * Implements Iterator::current().
     */
    function current()
    {
        return current($this->_schemaFields);
    }

    /**
     * Implements Iterator::key().
     */
    function key()
    {
        return key($this->_schemaFields);
    }

    /**
     * Implements Iterator::next().
     */
    function next()
    {
        next($this->_schemaFields);
    }

    /**
     * Implements Iterator::valid().
     */
    function valid()
    {
        return (null !== key($this->_schemaFields));
    }
}
