<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Collection;

use Search\Server\SearchServerAbstract;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Adapter for search collections.
 *
 * Collections are datasources that are being indexed. Examples are filesystems,
 * RSS feeds, or the content in a CMS.
 */
abstract class SearchCollectionAbstract
{
    /**
     * Flags that there is no limit of the number of documents processed when
     * running the queue.
     */
    const NO_LIMIT = -1;

    /**
     * An associative array of configuration options. Options are specific to
     * to each collection. For example, an RSS collection might require an
     * option that specifies the source URL.
     *
     * @var array
     */
    protected $_options;

    /**
     * The event dispatcher used by this collection to throw events.
     *
     * @var EventDispatcher
     */
    protected $_dispatcher;

    /**
     * Constructs a SearchCollectionAbstract object.
     *
     * @param array $options
     *   An associative array of configuration options. Common options available
     *   to all collections are the following:
     *   - dispatcher: Optionally pass an EventDispatcher object. This option is
     *     most often used to set a global event dispatcher.
     *   - limit: The maximum number of documents
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
     * Returns a list of items enqueued for indexing.
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
    abstract public function getQueue($limit = self::NO_LIMIT);

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
     * Sets the event dispatcher used by this collection to throw events.
     *
     * @param EventDispatcher $dispatcher
     *   The event dispatcher.
     *
     * @return SearchCollectionAbstract
     */
    public function setDispatcher(EventDispatcher $dispatcher)
    {
        $this->_dispatcher = $dispatcher;
        return $this;
    }

    /**
     * Sets the event dispatcher used by this collection to throw events.
     *
     * If no event dispatcher is set, then one is instantiated automatically.
     *
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        if (!isset($this->_dispatcher)) {
            $this->_dispatcher = new EventDispatcher();
        }
        return $this->_dispatcher;
    }

    /**
     * @param int $limit
     *   The maximum number of documents to process. Defaults to -1, which
     *   mean there is no limit on the number of documents processed.
     */
    public function index(SearchServerAbstract $server, $limit = self::NO_LIMIT)
    {
        $queue = $this->getQueue($limit);
        $queue->processQueue($server, $this, $limit);
    }
}
