<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * Interface implemented by configurable classes.
 *
 * The term configurable means that an associative array of options can be
 * passed through the constructor as well as reading the configurations from a
 * YAML file.
 */
interface SearchConfigurableInterface
{
    /**
     * Returns the unique identifier of the configurable class.
     *
     * The identifier should be unique across all children of a parent class.
     * For example, all SearchCollectionAbstract classes should have unique
     * identifiers, but it is valid for a SearchCollectionAbstract class to have
     * the same identifier as a SearchServiceAbstract class.
     *
     * @return string
     */
    public function getId();

    /**
     * Returns an instance of SearchConfig containing the configuration options
     * set for an instance of the configurable class.
     *
     * @return SearchConfig
     */
    public function getConfig();
}
