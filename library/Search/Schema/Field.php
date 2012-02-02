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
 * A field that may be attached to a schema.
 *
 * Fields are objects that contain information about the source object being
 * indexed. Field plugins can be registered with field classes to extract the
 * data from the source object, enhance the source with other text, and
 * normalize the text before it is passed to the backend for indexing.
 *
 * @package    Search
 * @subpackage Schema
 */
class Search_Schema_Field extends Search_Plugin_Pluggable
{
    /**
     * An array of properties.
     *
     * @var array
     */
    protected $_properties = array();

    /**
     * Constructor, sets class vars.
     *
     * @param string $name
     *   The machine name of the field used to identify the data in the source
     *   object. For example, if the schema is mapping an RSS feed, names might
     *   be "title", "description", or "link".
     * @param string $type
     *   The data type of the field, for example "fulltext" or "int". See the
     *   Search_Schema::TYPE_* class constants. Defaults to "fulltext".
     * @param string $language
     *   The subtag as specified in the IANA Language Subtag Registry at
     *   http://www.iana.org/assignments/language-subtag-registry. Defaults to
     *   "und".
     */
    public function __construct($name, $type = Search_Schema::TYPE_FULLTEXT, $language = Search_Schema::LANGUAGE_NEUTRAL)
    {
        $this
            ->setProperty('name', $name)
            ->setProperty('type', $type)
            ->setProperty('language', $language)
            ->setProperty('field', $name);
    }

    /**
     * Gets a field property.
     *
     * @param string $name
     *   The property name.
     * @param mixed $value
     *   The property value.
     *
     * @return mixed
     *   The property value, null if the property doesn't exist.
     */
    public function getProperty($name)
    {
        return isset($this->_properties[$name]) ? $this->_properties[$name] : null;
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
}
