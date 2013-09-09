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

/**
 * Models the schema from the field definitions in the collection.yml file.
 */
class Schema implements \IteratorAggregate
{
    /**
     * An associative array of fields keyed by their unique identifier.
     */
    protected $_fields = array();

    /**
     * A key value mapping of field's name as stored in the index to it's unique
     * identifier.
     *
     * @var array
     */
    protected $_fieldNameMap = array();

    /**
     * The field containing the unique identifiers of the indexed items.
     *
     * @var string
     */
    protected $_uniqueField = '';

    /**
     * Associative array of fields that are searched by default.
     *
     * @var array
     */
    protected $_defaultFields = array();

    /**
     * Implements IteratorAggregate::getIterator().
     *
     * Returns the array of fields.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_fields);
    }

    /**
     * Builds the schema from an array of options.
     *
     * @param array $options
     *   The raw options parsed form the configuration file related to the
     *   schema.
     *
     * @throws \InvalidArgumentException
     */
    public function build(array $options)
    {
        if (isset($options['fields'])) {
            if (!is_array($options['fields'])) {
                $message = 'Argument 1 passed to ' . __METHOD__ . ' must be an array.';
                throw new \InvalidArgumentException($message);
            }
            foreach ($options['fields'] as $id => $field_options) {
                $this->attachField(new SchemaField($id, $field_options));
            }
        }

        if (isset($options['unique_field'])) {
            $this->setUniqueField($options['unique_field']);
        }
    }

    /**
     * Associates a field with this schema.
     *
     * @param SchemaField $field
     *   The field being associated with the schema.
     *
     * @return Schema
     */
    public function attachField(SchemaField $field)
    {
        $id = $field->getId();
        $name = $field->getName();
        $this->_fields[$id] = $field;
        $this->_fieldNameMap[$name] = $id;
        return $this;
    }

    /**
     * Returns a field by its unique identifier.
     *
     * @param string $id
     *   The unique identifier of the field.
     *
     * @return SchemaField
     *
     * @throw \InvalidArgumentException()
     */
    public function getField($id)
    {
        if (!isset($this->_fields[$id])) {
            $message = 'Field "' . $id . '" not associated with this schema.';
            throw new \InvalidArgumentException($message);
        }
        return $this->_fields[$id];
    }

    /**
     * Returns all fields associated with this schema.
     *
     * @return array
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * Returns a field by its name as stored in the index.
     *
     * @param string $name
     *   The name of the field as stored in the index.
     *
     * @return SchemaField
     *
     * @throws \InvalidArgumentException()
     */
    public function getFieldByName($name)
    {
        if (!isset($this->_fieldNameMap[$name])) {
            $message = 'Field name "' . $name . '" not associated to this schema.';
            throw new \InvalidArgumentException($message);
        }
        return $this->getField($this->_fieldNameMap[$name]);
    }

    /**
     * De-references a field from the schema.
     *
     * @param SchemaField $field
     *   The field being detached.
     *
     * @return Schema
     */
    public function detachField(SchemaField $field)
    {
        return $this->removeField($field->getId());
    }

    /**
     * De-references a field from the schema.
     *
     * @param string $id
     *   The unique identifier of the field.
     *
     * @return Schema
     */
    public function removeField($id)
    {
        if (isset($this->_fields[$id])) {
            $name = $this->_fields[$id]->getName();
            unset($this->_fields[$id], $this->_fieldNameMap[$name]);
        }
        return $this;
    }

    /**
     * Sets the field containing the unique identifiers of the indexed items.
     *
     * @param string $id
     *   The unique identifier of the field.
     *
     * @return Schema
     */
    public function setUniqueField($id)
    {
        $this->_uniqueField = $id;
        return $this;
    }

    /**
     * Returns true if the unique field is defined.
     *
     * @return boolean
     */
    public function hasUniqueField()
    {
        return $this->_uniqueField !== '';
    }

    /**
     * Gets the field containing the unique identifiers of the indexed items.
     *
     * @param string $id
     *   The unique identifier of the field.
     *
     * @return Schema
     */
    public function getUniqueField()
    {
        return $this->getField($this->_uniqueField);
    }

    /**
     * Returns the id of the field containing the unique identifiers of the
     * indexed items.
     *
     * @return string
     */
    public function getUniqueFieldId()
    {
        return $this->_uniqueField;
    }

    /**
     * Returns the names of all fields
     */
    public function getFieldNames()
    {
        return array_keys($this->_fieldNameMap);
    }

    /**
     * Check whether a field with the passed identifier is set.
     *
     * @param string $id
     *   The unique identifier of the field.
     */
    public function __isset($id)
    {
        return isset($this->_fields[$id]);
    }

    /**
     * Returns a field by its unique identifier.
     *
     * @see Schema::getField()
     */
    public function __get($id)
    {
        return $this->getField($id);
    }

    /**
     * Returns the array of schema options.
     *
     * @return
     */
    public function toArray()
    {
        $schema_options = array(
            'unique_field' => $this->_uniqueField,
            'fields' => array(),
        );
        foreach ($this->_fields as $id => $field) {
            $schema_options['fields'][$id] = $field->toArray();
        }
        return $schema_options;
    }
}
