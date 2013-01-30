<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Collection;

use Search\Index\SearchIndexDocument;
use Search\Server\SearchServerAbstract;

/**
 * Adapter for search collections.
 *
 * Collections are datasources that are being indexed. Examples are filesystems,
 * RSS feeds, or the content in a CMS.
 */
abstract class SearchCollectionAbstract
{
    /**
     * An associative array of configuration options. Options are specific to
     * to each collection. For example, an RSS collection might require an
     * option that specifies the source URL.
     *
     * @var array
     */
    protected $_options;

    /**
     * Constructs a SearchCollectionAbstract object.
     *
     * @param array $options
     *   An associative array of configuration options. Common options available
     *   to all collections are the following:
     *   - dispatcher: Optionally pass an EventDispatcher object. This option is
     *     most often used to set a global event dispatcher.
     */
    public function __construct(array $options = array())
    {
        $this->_options = $options;

        if (!empty($options['dispatcher'])) {
            $this->setDispatcher($options['dispatcher']);
        }

        $this->init();
    }

    /**
     * Hook that allows the collection object to initialize itself.
     */
    abstract public function init();

    /**
     * Returns an object containing the items enqueued for indexed.
     *
     * In this instance, a queue is simply a collection that can be iterated
     * over using `foreach()`. Items in the queue could be a unique identifier
     * or fully populated object.
     *
     * @param int $limit
     *   The maximum number of documents to process. Defaults to -1, which
     *   mean there is no limit on the number of documents processed.
     *
     * @return SearchCollectionQueue
     */
    abstract public function getQueue($limit = SearchCollectionQueue::NO_LIMIT);

    /**
     * Populates the document with fields extracted from the the source data.
     *
     * @param SearchIndexDocument $document
     *   The document object instantiated by the server.
     * @param mixed $data
     *   The source data being indexed.
     */
    abstract public function buildDocument(SearchIndexDocument $document, $data);

    /**
     * Loads the source data, defaults to returning the item passed to it.
     *
     * This method is useful for lazy-loading the source data given a unique
     * identifier. For example, when loading data from a CMS, the item will
     * often be an identifier of the content being indexed.
     *
     * @param mixed $item
     *   The item being indexed. An item is usually a unique identifier but
     *   could also be a fully populated object containing the source data.
     *
     * @return mixed
     *   The source data being indexed.
     */
    public function loadSourceData($item)
    {
        return $item;
    }

    /**
     * Sets or resets a configuration option.
     *
     * @param string $option
     *   The name of the configuration option.
     * @param mixed $value
     *   The configuration option's value.
     *
     * @return SearchCollectionAbstract
     */
    public function setOption($option, $value)
    {
        $this->_options[$option] = $value;
        return $this;
    }

    /**
     * Returns a configuration option's value.
     *
     * @param string $option
     *   The name of the configuration option.
     * @param mixed $default
     *   The default value returned if the configuration option is not set,
     *   defaults to null.
     *
     * @return mixed
     */
    public function getOption($option, $default = null)
    {
        return isset($this->_options[$option]) ? $this->_options[$option] : $default;
    }

    /**
     * Returns the associative array of configuration options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Indexes items in a collection that are queued for indexing.
     *
     * @param int $limit
     *   The maximum number of documents to process. Defaults to -1, which
     *   mean there is no limit on the number of documents processed.
     */
    public function index(SearchServerAbstract $server, $limit = SearchCollectionQueue::NO_LIMIT)
    {
        $queue = $this->getQueue($limit);
        $queue->processQueue($server, $this, $limit);
    }
}
