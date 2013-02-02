<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework\Event;

use Search\Framework\SearchIndexField;
use Search\Framework\SearchServiceAbstract;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for enhancing / normalizing the value of a field.
 */
class SearchFieldEvent extends Event
{
    /**
     * The search service that is indexing the document that this field is
     * attached to.
     *
     * @var SearchServiceAbstract
     */
    protected $_service;

    /**
     * The field containing the value being enriched or normalized.
     *
     * @var SearchIndexField
     */
    protected $_field;

    /**
     * The value being normalized or enriched.
     *
     * @var string|array
     */
    protected $_value;

    /**
     * Constructs a SearchFieldEvent object.
     *
     * @param SearchServiceAbstract $service
     *   The search service that is indexing the document that this field is
     *   attached to.
     * @param SearchIndexField $field
     *   The field containing the value being enriched or normalized.
     */
    public function __construct(SearchServiceAbstract $service, SearchIndexField $field)
    {
        $this->_service = $service;
        $this->_field = $field;
        $this->_value = $field->getValue();
    }

    /**
     * Returns the search service that is indexing the document that this field
     * is attached to.
     *
     * @return SearchServiceAbstract
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * Returns the field containing the value being normalized.
     *
     * @return SearchIndexField
     */
    public function getField()
    {
        return $this->_field;
    }

    /**
     * Sets the value being normalized or enriched.
     *
     * @return SearchFieldEvent
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    /**
     * Returns the value being normalized or enriched.
     *
     * @return string|array
     */
    public function getValue()
    {
        return $this->_value;
    }
}
