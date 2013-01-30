<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework\Event;

use Search\Framework\SearchIndexDocument;
use Search\Framework\SearchServerAbstract;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for document related events.
 */
class SearchDocumentEvent extends Event
{
    /**
     * The search server that is indexing the document.
     *
     * @var SearchServerAbstract
     */
    protected $_server;

    /**
     * The document modeling the source data being indexed.
     *
     * @var SearchIndexDocument
     */
    protected $_document;

    /**
     * The source data being indexed.
     *
     * @var mixed
     */
    protected $_data;

    /**
     * Constructs a SearchDocumentEvent object.
     *
     * @param SearchServerAbstract $server
     *   The search server that is indexing the document.
     * @param SearchIndexDocument $document
     *   The document modeling the source data being indexed.
     * @param mixed $data
     *   The source data being indexed.
     */
    public function __construct(SearchServerAbstract $server, SearchIndexDocument $document, $data)
    {
        $this->_server = $server;
        $this->_document = $document;
        $this->_data = $data;
    }

    /**
     * Returns the search server that is indexing the document.
     *
     * @return SearchServerAbstract
     */
    public function getServer()
    {
        return $this->_server;
    }

    /**
     * Returns the document modeling the source data being indexed.
     *
     * @return SearchIndexDocument
     */
    public function getDocument()
    {
        return $this->_document;
    }

    /**
     * The source data being indexed.
     *
     * @return mixed
     */
    public function getSourceData()
    {
        return $this->_data;
    }
}
