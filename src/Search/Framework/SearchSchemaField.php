<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * Models a field in the collection's schema.
 */
class SearchSchemaField
{

    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_DATE = 'date';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_BINARY = 'binary';
    const TYPE_LOCATION = 'location';

    const SIZE_INTEGER_BYTE = 'byte';
    const SIZE_INTEGER_SHORT = 'short';
    const SIZE_INTEGER_LONG = 'long';

    const SIZE_DECIMAL_FLOAT = 'float';
    const SIZE_DECIMAL_DOUBLE = 'double';

    /**
     * The unique identifier of the field.
     *
     * @var string
     */
    protected $_id;

    /**
     * The name of the field as stored in the index, defaults to the identifier.
     *
     * @var string
     */
    protected $_name;

    /**
     * The field's human readable label.
     *
     * @var string
     */
    protected $_label = '';

    /**
     * The field's long description.
     *
     * @var string
     */
    protected $_description = '';

    /**
     * The field's data type.
     *
     * @var string
     */
    protected $_type = '';

    /**
     * The size that is related to the data type. Not all data types have a
     * size.
     *
     * @var string|null
     */
    protected $_size;

    /**
     * Whether or not the value is analyzed by the backend.
     */
    protected $_analyze = false;

    /**
     * Whether the field's data is indexed.
     *
     * @var boolean
     */
    protected $_isIndexd = true;

    /**
     * Whether the field's data is stored in the index.
     *
     * @var boolean
     */
    protected $_isStored = false;

    /**
     * Whether the field's data is multivalued.
     *
     * @var boolean
     */
    protected $_isMultiValued = false;

    /**
     * Constructs a SearchSchemaField object.
     *
     * @param string $id
     *   The unique identifier of this field. The name of the field stored in
     *   the index defaults to this value unless otherwise specified in the
     *   field options array.
     * @param array $field_options
     *   The raw options parsed form the configuration file related to the
     *   field being attached to the schema.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($id, array $field_options)
    {
        $this->_id = $id;
        $this->_name = isset($field_options['name']) ? (string) $field_options['name'] : $id;

        if (isset($field_options['label'])) {
            $this->_label = (string) $field_options['label'];
        }

        if (isset($field_options['description'])) {
            $this->_description = (string) $field_options['description'];
        }

        if (isset($field_options['type'])) {
            $this->_type = (string) $field_options['type'];
        }

        if (isset($field_options['size'])) {
            $this->_size = (string) $field_options['size'];
        }

        if (isset($field_options['index'])) {
            $this->_isIndexd = !empty($field_options['index']);
        }

        if (isset($field_options['store'])) {
            $this->_isStored = !empty($field_options['store']);
        }

        if (isset($field_options['multivalue'])) {
            $this->_isMultiValued = !empty($field_options['multivalue']);
        }
    }

    /**
     * Returns the unique identifier of the field.
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Resets the unique identifier of the field.
     *
     * The schema is required so that its internal hash table can be updated to
     * reflect the field's new identifier.
     *
     * @param SearchSchema $schema
     *   The schema that this field is attached to.
     * @param string $id
     *   The unique identifier of the field.
     *
     * @return SearchSchemaField
     */
    public function setId(SearchSchema $schema, $id)
    {
        $reset_unique_field = ($schema->getUniqueFieldId() === $this->_id);

        // Reset the identifier, update the hash tables by re-adding the field.
        $schema->removeField($this->_id);
        $this->_id = $id;
        $schema->attachField($this);

        // Update the schema's unique field.
        if ($reset_unique_field) {
            $schema->setUniqueField($id);
        }

        return $this;
    }

    /**
     * Returns the name of the field as stored in the index.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Sets the name of the field as stored in the index.
     *
     * The schema is required so that its internal hash table can be updated to
     * reflect the field's new identifier.
     *
     * @param SearchSchema $schema
     *   The schema that this field is attached to.
     * @param string $name
     *   The name of the field as stored in the index.
     *
     * @return SearchSchemaField
     */
    public function setName(SearchSchema $schema, $name)
    {
        $schema->removeField($this->_id);
        $this->_name = $name;
        $schema->attachField($this);
        return $this;
    }

    /**
     * Returns the field's human readable label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * Sets the field's human readable label.
     *
     * @param string $label
     *   The field's human readable label.
     *
     * @return SearchSchemaField
     */
    public function setLabel($label)
    {
        $this->_label = $label;
        return $this;
    }

    /**
     * Returns the field's long description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Sets the field's long description.
     *
     * @param string $description
     *   The field's long description.
     *
     * @return SearchSchemaField
     */
    public function setDescription($description)
    {
        $this->_description = $description;
        return $this;
    }

    /**
     * Sets the field's data type.
     *
     * @param string $type
     *   The field's data type.
     *
     * @return SearchSchemaField
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * Returns the field's data type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Returns the size that is related to the data type.
     *
     * @param string $size
     *    The size that is related to the data type.
     *
     * @return SearchSchemaField
     */
    public function setSize($size)
    {
        $this->_size = $size;
        return $this;
    }

    /**
     * Returns the size that is related to the data type.
     *
     * @return string
     */
    public function getSize()
    {
        return $this->_size;
    }

    /**
     * Sets whether or not the value is analyzed by the backend.
     *
     * @param boolean $analyze
     *   Whether or not the value is analyzed by the backend, defaults to true.
     *
     * @return boolean
     */
    public function analyze($analyze = true)
    {
        $this->_analyze = $analyze;
        return $this;
    }

    /**
     * Returns whether or not the value is analyzed by the backend.
     *
     * @return boolean
     */
    public function isAnalyzed()
    {
        return $this->_analyze;
    }

    /**
     * Returns whether the field's data is indexed.
     *
     * @return boolean
     */
    public function isIndexed()
    {
        return $this->_isIndexd;
    }

    /**
     * Sets whether the field's data is indexed.
     *
     * @param boolean $index
     *   A flag that determines whether the field's data is indexed.
     *
     * @return SearchSchemaField
     */
    public function indexData($index = true)
    {
        $this->_isIndexd = $index;
        return $this;
    }

    /**
     * Returns whether the field's data is stored in the index.
     *
     * @return boolean
     */
    public function isStored()
    {
        return $this->_isStored;
    }

    /**
     * Sets whether the field's data is stored in the index.
     *
     * @param boolean $store
     *   A flag that determines whether the field's data is stored in the index,
     *   defaults to true.
     *
     * @return SearchSchemaField
     */
    public function storeData($store = true)
    {
        $this->_isStored = $store;
        return $this;
    }

    /**
     * Returns whether the field's data is multivalued.
     *
     * @return boolean
     */
    public function isMultiValued()
    {
        return $this->_isMultiValued;
    }

    /**
     * Sets whether the field's data is multivalued.
     *
     * @param boolean $multivalue
     *   A flag that determines whether the field's data is multivalued.
     *
     * @return SearchSchemaField
     */
    public function allowMultipleValues($multivalue = true)
    {
        $this->_isMultiValued = $multivalue;
        return $this;
    }

    /**
     * Returns the array of field options.
     *
     * @return array
     */
    public function toArray()
    {
        $schema = array(
            'name' => $this->_name,
            'label' => $this->_label,
            'description' => $this->_description,
            'type' => $this->_type,
            'store' => $this->_isStored,
            'index' => $this->_isIndexd,
            'multivalue' => $this->_isMultiValued,
        );

        if (isset($this->_size)) {
            $schema['size'] = $this->_size;
        }

        return $schema;
    }
}
