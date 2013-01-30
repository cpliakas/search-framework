<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework\Index;

/**
 * Models a field in the source data being indexed.
 */
class SearchIndexField
{
    /**
     * The unique identifier of the field that its name as stored in the index
     * defaults to.
     *
     * @var string
     */
    protected $_id;

    /**
     * The name of this field as stored in the index.
     *
     * This value defaults to the unique identifier of the field, however it is
     * perfectly valid to change the name to something else.
     *
     * @var string
     */
    protected $_name;

    /**
     * The field's values extracted form the source text.
     *
     * @var string|array
     */
    protected $_value;

    /**
     * Constructs a SearchIndexField object.
     *
     * @param string $id
     *   The unique identifier of the field that its name as stored in the index
     *   defaults to.
     * @param string|array $value
     *   The field's value extracted form the source text.
     */
    public function __construct($id, $value)
    {
        $this->_id = $id;
        $this->_name = $id;
        $this->_value = $value;
    }

    /**
     * Returns the unique identifier of this field.
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Sets the name of this field as stored in the index.
     *
     * @param string $name
     *   The name of this field as stored in the index.
     *
     * @return SearchIndexField
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * Returns the name of this field as stored in the index.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Sets the field's value extracted form the source text.
     *
     * @param string|array $value
     *   The field's value extracted form the source text.
     *
     * @return SearchIndexField
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    /**
     * Returns the field's value extracted form the source text.
     *
     * @return string|array
     */
    public function getValue()
    {
        return $this->_value;
    }
}
