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
     * Returns the basename of the configuration file with the ".yml" excluded.
     *
     * For example, returning "feed" will scan for "feed.yml" files.
     *
     * @return string
     */
    public function getConfigBasename();

    /**
     * Returns an instance of SearchConfig containing the configuration options
     * set for an instance of the configurable class.
     *
     * @return SearchConfig
     */
    public function getConfig();
}
