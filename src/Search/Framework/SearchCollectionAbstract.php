<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

/**
 * Adapter class extended by search collections.
 *
 * Collections are data sources containing the content being indexed. Examples
 * are files on a filesystem, RSS feeds, the content in a CMS, or anything else
 * imaginable that can be indexed.
 */
abstract class SearchCollectionAbstract implements SearchConfigurableInterface
{
    /**
     * Flags that the specified constraint has no limit.
     *
     * @var int
     */
    const NO_LIMIT = -1;

    /**
     * The unique identifier of the collection class.
     *
     * It is best practice to use only lowercase letters, numbers, dots (.),
     * and underscores (_).
     *
     * @var string
     */
    protected static $_id = '';

    /**
     * The default "limit" option for this collection.
     *
     * This limit is the maximum number of documents that are processed during
     * indexing and queuing operations.
     *
     * @var int
     */
    protected static $_defaultLimit = self::NO_LIMIT;

    /**
     * The default "timeout" option for this collection.
     *
     * The timeout is the maximum amount of time in seconds allowed for the
     * indexing and queuing operations.
     *
     * @var int
     */
    protected static $_defaultTimeout = self::NO_LIMIT;

    /**
     * The type of content in this collection.
     *
     * It is best practice to use only lowercase letters, numbers, dots (.),
     * and underscores (_). Examples might be "feeds", "database.db_name".
     *
     * Types can be shared by multiple collection classes, but their defined
     * schemas should be compatible.
     *
     * This value is used by backends such as Elasticsearch to determine the
     * mapping that is applied to the document being indexed.
     *
     * @var string
     */
    protected $_type = '';

    /**
     * Object populated with configuration options set for this instance.
     *
     * @var SearchConfig
     */
    protected $_config;

    /**
     * The schema modeled after the field definitions in the collection.yml
     * configuration file.
     *
     * @var SearchSchema
     */
    protected $_schema;

    /**
     * The object that interacts with the indexing queue.
     *
     * @var SearchQueueInterface
     */
    protected $_queue;

    /**
     * Constructs a SearchCollectionAbstract object.
     *
     * Reads configuration file and instantiates the SearchSchema object from
     * the configs loaded in the "schema" key.
     *
     * @param array $options
     *   An associative array of configuration options that override that values
     *   read from the configuration file.
     *
     * @throws ParseException
     */
    public function __construct(array $options = array())
    {
        $this->_config = new SearchConfig($options);
        $this->_config->load($this);

        if ($type = $this->_config->getOption('type')) {
            $this->_type = $type;
        }

        $schema_options = $this->_config->getOption('schema', array());
        $this->_schema = new SearchSchema($schema_options);

        $this->init();
    }

    /**
     * Implements SearchConfigurableInterface::getId().
     */
    public function getId()
    {
        return static::$_id;
    }

    /**
     * Implements SearchConfigurableInterface::getConfig().
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Hook that allows the collection object to initialize itself.
     *
     * This is most often implemented to instantiate and set a backend library,
     * for example a feed parser or ORM.
     */
    abstract public function init();

    /**
     * Fetches the items that are scheduled for indexing.
     *
     * This method acts as the worker that interacts with the index scheduler to
     * retrieve the items that are scheduled for indexing. Often times the index
     * scheduler can be as simple as a backend client library querying the data
     * source to get the most recently updated items.
     *
     * @return \Traversable|array
     *   The items that are scheduled to be indexed.
     */
    abstract public function fetchScheduledItems();

    /**
     * Loads the source data from the item fetched from the indexing queue.
     *
     * This method is useful for lazy-loading the source data given a unique
     * identifier. For example, when loading data from a CMS, the item will
     * often be an identifier of the content being indexed.
     *
     * @param mixed $item
     *   The item that is scheduled for indexing.
     *
     * @return mixed
     *   The source data being indexed.
     */
    public function loadSourceData($item)
    {
        return $item;
    }

    /**
     * Builds a queue message for the item scheduled for indexing.
     *
     * This hook is invoked just prior to sending the item to the index queue.
     *
     * @param SearchQueueMessage $message
     *   The queue message being built.
     *
     * @param mixed $item
     *   The item that is scheduled for indexing.
     */
    abstract public function buildQueueMessage(SearchQueueMessage $message, $item);

    /**
     * Populates a SearchIndexDocument object with fields extracted from the the
     * source data.
     *
     * This hook is invoked prior to processing the document for indexing.
     *
     * @param SearchIndexDocument $document
     *   The document object instantiated by the service.
     * @param mixed $data
     *   The source data being indexed.
     */
    abstract public function buildDocument(SearchIndexDocument $document, $data);

    /**
     * Returns this collection's schema.
     *
     * @return SearchSchema
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Sets the object that interacts with the indexing queue.
     *
     * @param SearchQueueAbstract $queue
     *   The object that interacts with the indexing queue.
     *
     * @return SearchCollectionAbstract
     */
    public function setQueue(SearchQueueAbstract $queue)
    {
        $this->_queue = $queue;
        return $this;
    }

    /**
     * Returns the object that interacts with the indexing queue.
     *
     * If no queue is set, an instance of SearchIteratorQueue is set as the
     * queue class.
     *
     * @return SearchQueueAbstract
     */
    public function getQueue()
    {
        if (!$this->_queue) {
            $this->_queue = new SearchQueueIteratorQueue($this, 'default');
        }
        return $this->_queue;
    }

    /**
     * Sets the type of content in this collection.
     *
     * @param string $type
     *   The type of content in this collection.
     *
     * @return SearchCollectionAbstract
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * Returns the type of content in this collection.
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Sets the label of this collection.
     *
     * @param string $name
     *   The label of this collection.
     *
     * @return SearchCollectionAbstract
     */
    public function setLabel($label)
    {
        $this->_config->setOption('label', $label);
        return $this;
    }

    /**
     * Returns the label of this collection.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_config->getOption('label', '');
    }

    /**
     * Sets the description of this collection.
     *
     * @param string $name
     *   The description of this collection.
     *
     * @return SearchCollectionAbstract
     */
    public function setDescription($description)
    {
        $this->_config->setOption('description', $description);
        return $this;
    }

    /**
     * Returns the description of this collection.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_config->getOption('description', '');
    }

    /**
     * Sets the maximum number of documents that are processed during indexing
     * and queuing operations.
     *
     * @param int $limit
     *   The maximum number of documents to process
     *
     * @return SearchCollectionAbstract
     */
    public function setLimit($limit)
    {
        $this->_config->setOption('limit', $limit);
        return $this;
    }

    /**
     * Gets that maximum number of documents that are processed during indexing
     * and queuing operations.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->_config->getOption('limit', static::$_defaultLimit);
    }

    /**
     * Sets the timeout in seconds for the indexing and queuing operations.
     *
     * @param int $timeout
     *   The the maximum amount of time in seconds allowed for the indexing and
     *   queuing operations.
     *
     * @return SearchCollectionAbstract
     */
    public function setTimeout($timeout)
    {
        $this->_config->setOption('timeout', $timeout);
        return $this;
    }

    /**
     * Returns the timeout in seconds for the indexing and queuing operations.
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->_config->getOption('timeout', static::$_defaultTimeout);
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
        return $this->_config->getOption($option, $default);
    }
}
