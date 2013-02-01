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
class SearchCollectionField
{
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
     * Whether the field is indexed.
     *
     * @var boolean
     */
    protected $_isIndexd = true;

    /**
     * Whether the source data is stored in the index.
     *
     * @var boolean
     */
    protected $_isStored = false;

    /**
     * Whether the source data is multivalued.
     *
     * @var boolean
     */
    protected $_isMultiValued = false;

    /**
     * Constructs a SearchCollectionSchema object.
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
     * Sets the unique identifier of the field.
     *
     * @param string $id
     *   The unique identifier of the field.
     *
     * @return SearchCollectionField
     */
    public function setId($id)
    {
        $this->_id = $id;
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
     * @param string $name
     *   The name of the field as stored in the index.
     *
     * @return SearchCollectionField
     */
    public function setName($name)
    {
        $this->_name = $name;
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
     * @return SearchCollectionField
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
     * @return SearchCollectionField
     */
    public function setDescription($description)
    {
        $this->_description = $description;
        return $this;
    }

    /**
     * Returns whether the field is indexed.
     *
     * @return boolean
     */
    public function isIndexed()
    {
        return $this->_isIndexd;
    }

    /**
     * Sets whether the field is indexed.
     *
     * @param boolean $index
     *   A flag that determines whether the field is indexed.
     *
     * @return SearchCollectionField
     */
    public function indexData($index = true)
    {
        $this->_isIndexd = $index;
        return $this;
    }

    /**
     * Returns whether the field is stored in the index.
     *
     * @return boolean
     */
    public function isStored()
    {
        return $this->_isStored;
    }

    /**
     * Sets whether the field is stored in the index.
     *
     * @param boolean $store
     *   A flag that determines whether the field is stored in the indexed.
     *
     * @return SearchCollectionField
     */
    public function storeData($store = true)
    {
        $this->_isStored = $store;
        return $this;
    }

    /**
     * Returns whether the source data is multivalued.
     *
     * @return boolean
     */
    public function isMultiValued()
    {
        return $this->_isMultiValued;
    }

    /**
     * Sets whether the source data is multivalued.
     *
     * @param boolean $multivalue
     *   A flag that determines whether the source data is multivalued.
     *
     * @return SearchCollectionField
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
        return array(
            'name' => $this->_name,
            'label' => $this->_label,
            'description' => $this->_description,
            'store' => $this->_isStored,
            'index' => $this->_isIndexd,
            'multivalue' => $this->_isMultiValued,
        );
    }
}
