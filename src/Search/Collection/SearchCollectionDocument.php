<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Collection;

/**
 *
 */
class SearchCollectionDocument
{
    /**
     *
     * @var array
     */
    protected $_fields = array();

    /**
     * Returns a field in the document.
     *
     * @param string $name
     *   The name of the field.
     *
     * @return SearchCollectionField
     *
     * @throws \InvalidArgumentException
     */
    public function getField($name)
    {
        if (!isset($this->_fields[$name])) {
            throw new \InvalidArgumentException('Field "' . $name . '" not in source.');
        }
        return $this->_fields[$name];
    }

}
