<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

use Search\Framework\Event\SearchFieldEvent;

/**
 * Models a document containing the source data being indexed.
 *
 * This class is usually extended by the backend in order to provide backend
 * specific functionality, for example document level boosting for Lucene-based
 * backends.
 */
class SearchIndexDocument implements \IteratorAggregate
{
    /**
     * An array of SearchIndexFields objects attached to this document.
     *
     * @var array
     */
    protected $_fields = array();

    /**
     * The server that is indexing this document.
     *
     * @var SearchServerAbstract
     */
    protected $_server;

    /**
     * Constructs a SearchIndexDocument object.
     *
     * @param SearchServerAbstract $server
     *   The server that is indexing this document.
     */
    public function __construct(SearchServerAbstract $server)
    {
        $this->_server = $server;
    }

    /**
     * Implements \IteratorAggregate::getIterator().
     *
     * Returns an object that iterates over the fields that are attached to this
     * document. The keys are the unique identifier of the field, and the values
     * are the field's normalized value(s) that are returned by the
     * SearchIndexDocument::getNormalizedFieldValue() method.
     *
     * @returns SearchIndexFieldIterator
     */
    public function getIterator()
    {
        $iterator = new SearchIndexFieldIterator($this->_fields);
        $iterator->setDocument($this);
        return $iterator;
    }

    /**
     * Returns the server that is indexing this document.
     *
     * @return SearchServerAbstract
     */
    public function getServer()
    {
        return $this->_server;
    }

    /**
     * Adds a field to the document.
     *
     * This method throws the SearchEvents::FIELD_ENRICH event and stores the
     * enriched value as the field's value.
     *
     * @param SearchIndexField $field
     *   The field being added to this document.
     *
     * @return SearchIndexDocument
     */
    public function addField(SearchIndexField $field)
    {
        // Throw the SearchEvents::FIELD_ENRICH event, reset the field's value
        // with the enriched value.
        $event = new SearchFieldEvent($this->_server, $field);
        $this->_server->getDispatcher()->dispatch(SearchEvents::FIELD_ENRICH, $event);
        $field->setValue($event->getValue());

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
     * @return SearchIndexField
     *
     * @throws \InvalidArgumentException
     */
    public function getField($id)
    {
        if (!isset($this->_fields[$id])) {
            throw new \InvalidArgumentException('Field "' . $id . '" not attached to document.');
        }
        return $this->_fields[$id];
    }

    /**
     * Returns all fields attached to this document.
     *
     * @return array
     *   An associative array keyed by the unique identifier of the field to the
     *   corresponding SearchIndexDocument object.
     *
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * Checks whether a field is attached to this document given it's unique
     * identifier.
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
     * Removes a field to the document given it's unique identifier.
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
     * Returns the name of the field as it is stored in the index given it's
     * unique identifier.
     *
     * @param string $id
     *   The unique identifier of the field.
     *
     * @return string
     */
    public function getFieldName($id)
    {
        return $this->getField($id)->getName();
    }

    /**
     * Returns a field's normalized value.
     *
     * This method throws the SearchEvents::FIELD_NORMALIZE event and returns
     * the normalized value from the event object.
     *
     * @param string $id
     *   The unique identifier of the field.
     *
     * @return string|array
     *   The field's normalized value(s).
     */
    public function getNormalizedFieldValue($id)
    {
        $field = $this->getField($id);

        $event = new SearchFieldEvent($this->_server, $field);
        $this->_server->getDispatcher()->dispatch(SearchEvents::FIELD_NORMALIZE, $event);

        return $event->getValue();
    }

    /**
     * Instiantiates and adds a field to this document.
     *
     * @param string $id
     *   The unique identifier of the field that its index name defaults to.
     * @param string|array $value
     *   The field's raw value extracted form the source text.
     */
    public function __set($id, $value)
    {
        $field = new SearchIndexField($id, $value);
        $this->addField($field);
    }

    /**
     * Returns a field's normalized value.
     *
     * @see SearchIndexDocument::getNormalizedFieldValue()
     */
    public function __get($id)
    {
        return $this->getNormalizedFieldValue($id);
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
