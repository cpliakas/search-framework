<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework\Event;

use Search\Framework\Index\SearchIndexField;
use Search\Framework\Server\SearchServerAbstract;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for enhancing / normalizing the value of a field.
 */
class SearchFieldEvent extends Event
{
    /**
     * The server that the field is being prepared for indexing to.
     *
     * @var SearchServerAbstract
     */
    protected $_server;

    /**
     * The field containing the value being normalized.
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
     * @param SearchServerAbstract $server
     *   The server that the field is being prepared for indexing to.
     * @param SearchIndexField $field
     *   The field containing the value being normalized.
     */
    public function __construct(SearchServerAbstract $server, SearchIndexField $field)
    {
        $this->_server = $server;
        $this->_field = $field;
        $this->_value = $field->getValue();
    }

    /**
     * Returns the server that the field is being prepared for indexing to.
     *
     * @return SearchServerAbstract
     */
    public function getServer()
    {
        return $this->_server;
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
     * Returns the value being normalized or enriched.
     *
     * @return string|array
     */
    public function getValue()
    {
        return $this->_value;
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
}
