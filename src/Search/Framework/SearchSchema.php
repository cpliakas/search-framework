<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * Models the schema from the field definitions in the collection.yml file.
 */
class SearchSchema implements \IteratorAggregate
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
     * Constructs a SearchSchema object.
     *
     * @param array $schema_options
     *   The raw options parsed form the configuration file related to the
     *   schema.
     */
    public function __construct(array $schema_options)
    {
        if (isset($schema_options['fields'])) {
            if (!is_array($schema_options['fields'])) {
                throw new \InvalidArgumentException('Schema fields must be an array.');
            }
            foreach ($schema_options['fields'] as $id => $field_options) {
                $this->addField(new SearchCollectionField($id, $field_options));
            }
        }

        if (isset($schema_options['unique_field'])) {
            $this->_uniqueField = (string) $schema_options['unique_field'];
        }
    }

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
     * Associates a field with this schema.
     *
     * @param SearchCollectionField $field
     *   The field being associated with the schema.
     *
     * @return SearchSchema
     */
    public function addField(SearchCollectionField $field)
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
     * @return SearchCollectionField
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
     * @return SearchCollectionField
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
     * Disassociates a field from the schema.
     *
     * @param string $id
     *   The unique identifier of the field.
     *
     * @return SearchSchema
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
     * @return SearchSchema
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
     * @return SearchSchema
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
     * @see SearchSchema::getField()
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
