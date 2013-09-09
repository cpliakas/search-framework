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
 * Iterates over the fields attached to the passed docuemnt.
 *
 * The keys are the unique identifier of the field, and the values are the
 * field's normalized value(s) that are returned by the
 * SearchIndexDocument::getNormalizedFieldValue() method.
 */
class IndexFieldIterator implements \Iterator
{
    /**
     * The indexer performing the operation.
     *
     * @var Indexer
     */
    protected $_indexer;

    /**
     * The document that the fields are attached to.
     *
     * @var IndexDocument
     */
    protected $_document;

    /**
     * An array of IndexFields objects being iterated over.
     *
     * @var array
     */
    protected $_fields = array();

    /**
     * The identifier of the current field.
     *
     * @var string
     */
    protected $_fieldId;

    /**
     * The current field object.
     *
     * @var IndexField
     */
    protected $_field;

    /**
     * Constructs a IndexFieldIterator object.
     *
     * @param Indexer $indexer
     *   The indexer performing the operation.
     * @param IndexDocument $document
     *   The document that the fields are attached to.
     */
    public function __construct(Indexer $indexer, IndexDocument $document)
    {
        $this->_indexer = $indexer;
        $this->_document = $document;
        $this->_fields = $document->getFields();
    }

    /**
     * Implements \Iterator::rewind().
     */
    function rewind()
    {
        reset($this->_fields);
    }

    /**
     * Implements \Iterator::key().
     */
    function key()
    {
        return $this->_fieldId;
    }

    /**
     * Implements \Iterator::key().
     */
    function next() {}

    /**
     * Implements \Iterator::valid().
     */
    function valid()
    {
        list($this->_fieldId, $this->_field) = each($this->_fields);
        return $this->_fieldId !== null;
    }

    /**
     * Implements \Iterator::current().
     *
     * @return string|array
     */
    public function current()
    {
        return $this->_indexer->normalizeField($this->_field);
    }
}