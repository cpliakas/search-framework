<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Index;

use Search\Index\SearchIndexDocument;

/**
 * Models a field in the source data being indexed.
 */
class SearchIndexFieldIterator extends \ArrayIterator
{
    /**
     * The document that the fields are attached to.
     *
     * @var SearchIndexDocument
     */
    protected $_document;

    /**
     * Sets the document that the fields are attached to.
     *
     * @param SearchIndexDocument $document
     */
    public function setDocument($document)
    {
        $this->_document = $document;
        return $this;
    }

    /**
     * Returns the document that the fields are attached to.
     *
     * @return SearchIndexDocument
     */
    public function getDocument()
    {
        return $this->_document;
    }

    /**
     * Overrides ArrayIterator::current().
     *
     * Returns the field's normalized value instead of the field itself.
     *
     * @return string|array
     */
    public function current()
    {
        $field = parent::current();
        $id = $field->getId();
        return $this->_document->getNormalizedFieldValue($id);
    }
}