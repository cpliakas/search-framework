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
 * Models the document being indexed.
 *
 * @package    Search
 * @subpackage Index
 */
class Search_Index_Document implements Iterator
{
    /**
     * An array of Search_Index_Field objects.
     *
     * @var array
     */
    protected $_indexFields = array();

    /**
     * Attaches a field to this schema.
     *
     * @param Search_Index_Field $field
     *
     * @return Search_Index_Document
     */
    public function addField(Search_Index_Field $field)
    {
        $name = $field->getProperty('name');
        $this->_indexFields[$name] = $field;
        return $this;
    }

    /**
     * Returns a field attached to this schema.
     *
     * @param string $name
     *   The machine name of the field.
     *
     * @return Search_Index_Field|null
     *   The field object if the field is attached, null otherwise,
     */
    public function getField($name)
    {
        return isset($this->_indexFields[$name]) ? $this->_indexFields[$name] : null;
    }

    /**
     * Returns all attached fields.
     *
     * @return array
     *   An array of Search_Index_Field objects.
     */
    public function getFields()
    {
        return $this->_indexFields;
    }

    /**
     * Removes a field from the schema.
     *
     * @param string $name
     *   The machine name of the field.
     *
     * @return Search_Index_Document
     */
    public function removeField($name)
    {
        unset($this->_indexFields[$name]);
        return $this;
    }

    /**
     * Implements Iterator::rewind().
     */
    function rewind()
    {
        reset($this->_indexFields);
    }

    /**
     * Implements Iterator::current().
     */
    function current()
    {
        return current($this->_indexFields);
    }

    /**
     * Implements Iterator::key().
     */
    function key()
    {
        return key($this->_indexFields);
    }

    /**
     * Implements Iterator::next().
     */
    function next()
    {
        next($this->_indexFields);
    }

    /**
     * Implements Iterator::valid().
     */
    function valid()
    {
        return (null !== key($this->_indexFields));
    }
}
