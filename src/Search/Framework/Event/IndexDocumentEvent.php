<?php

/**
 * Search Framework
 *
 * @author    Chris Pliakas <opensource@chrispliakas.com>
 * @copyright 2013 Chris Pliakas
 * @license   http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License (LGPL)
 * @link      https://github.com/cpliakas/search-framework
 */

namespace Search\Framework\Event;

use Search\Framework\CollectionAgentAbstract;
use Search\Framework\IndexDocument;

/**
 * Event object for document related events.
 */
class IndexDocumentEvent extends SearchEvent
{
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
     * @param CollectionAgentAbstract $agent
     *   The collection agent performing the operation.
     * @param IndexDocument $document
     *   The document modeling the source data being indexed.
     * @param mixed $data
     *   The source data being indexed.
     */
    public function __construct(CollectionAgentAbstract $agent, IndexDocument $document, $data)
    {
        $this->_agent = $agent;
        $this->_document = $document;
        $this->_data = $data;
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
