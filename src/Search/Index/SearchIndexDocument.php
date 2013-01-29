<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Index;

use Search\Index\SearchIndexField;
use Search\Server\SearchServerAbstract;

/**
 * Models a document containing the source data being indexed.
 *
 * This class is usually extended by the backend in order to provide backend
 * specific functionality such as document level boosting.
 */
class SearchIndexDocument implements \IteratorAggregate
{
    /**
     * An array of SearchIndexFields attached to this document.
     *
     * @var array
     */
    protected $_fields = array();

    /**
     * The server that this document is being prepared to index to.
     *
     * @var SearchServerAbstract
     */
    protected $_server;

    /**
     * Constructs a SearchIndexDocument object.
     *
     * @param SearchServerAbstract $server
     *   The server that this document is being prepared to index to.
     */
    public function __construct(SearchServerAbstract $server)
    {
        $this->_server = $server;
    }

    /**
     * Adds a field to the document.
     *
     * @param SearchIndexField $field
     *   The field being added to this document.
     *
     * @return SearchIndexDocument
     */
    public function addField(SearchIndexField $field)
    {
        // $dispatcher->dispatch(); search.field.enhance
        $id = $field->getId();
        $this->_fields[$id] = $field;
        return $this;
    }

    /**
     * Returns a field that is attached to this document.
     *
     * @param string $id
     *   The unique identifier of the field.
     *
     * @return SearchCollectionField
     *
     * @throws \InvalidArgumentException
     *
     * @todo Should we really throw an Exception?
     */
    public function getField($id)
    {
        if (!isset($this->_fields[$id])) {
            throw new \InvalidArgumentException('Field "' . $id . '" not attached to document.');
        }
        return $this->_fields[$id];
    }

    /**
     * Returns the list of fields attached to this document.
     *
     * @return array
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * Checks whether a field is attached to this document.
     *
     * @param string $id
     *   The unique identifier of the field being checked.
     *
     * @return boolean
     */
    public function fieldExists($id)
    {
        return isset($this->_fields[$id]);
    }

    /**
     * Removes a field to the document.
     *
     * @param string $name
     *   The unique identifier of the field being removed from the document.
     *
     * @return SearchIndexDocument
     */
    public function removeField($id)
    {
        unset($this->_fields[$id]);
        return $this;
    }

    /**
     * Implements \IteratorAggregate::getIterator().
     */
    public function getIterator()
    {
        return new \ArrayIterator($this);
    }

    /**
     * Instiantiates and adds a field to this document.
     *
     * @param string $id
     *   The unique identifier of this field that the name of the field as
     *   stored in the index defaults to.
     * @param string|array $value
     *   The field's value extracted form the source text.
     */
    public function __set($id, $value)
    {
        $field = new SearchIndexField($id, $value);
        $this->addField($field);
    }

    /**
     * Returns a field's normalized value that is attached to this document.
     *
     * @see SearchIndexDocument::getField()
     * @see SearchIndexField::getNormalizedValue()
     */
    public function __get($id)
    {
        return $this->getField($id)->getNormalizedValue($this->_server);
    }

    /**
     * Checks whether a field is attached to this document.
     *
     * @see SearchIndexDocument::fieldExists()
     */
    public function __isset($id)
    {
        return $this->fieldExists($id);
    }
}
