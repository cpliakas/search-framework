<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

use Search\Framework\Event\SearchCollectionEvent;

/**
 * Adapter class extended by search collections.
 *
 * Collections are objects that reference a resource containing the data being
 * indexed. Examples could be files on a filesystem, RSS feeds, the content in a
 * CMS, or anything else imaginable having data that can be indexed.
 *
 * This object also acts as an on-demand scheduler agent that interacts with the
 * resource's index scheduler to fetch the items that are due to be indexed. The
 * object then passes the fetched items to a queue for processing by the index
 * worker contained in the search service class.
 *
 * Iterating over this class fetches the items scheduled for indexing and
 * returns the messages that will be published to the queue.
 */
abstract class SearchCollectionAbstract implements SearchConfigurableInterface, \IteratorAggregate
{
    /**
     * Value that specifies a constraint has no limit, for example an operation
     * timeout or document limit.
     *
     * @var int
     */
    const NO_LIMIT = -1;

    /**
     * The unique identifier of the extending collection class.
     *
     * It is best practice to use only lowercase letters, numbers, dots (.),
     * and underscores (_). Examples might be "feed", "drupal.entity.node".
     *
     * @var string
     */
    protected static $_id = '';

    /**
     * The type of content contained in this resource that this collection
     * references.
     *
     * It is best practice to use only lowercase letters, numbers, dots (.),
     * and underscores (_). Examples might be "feeds", "database.db_name".
     *
     * Types can be shared by multiple collection classes and instances, but
     * their defined schemas should be compatible.
     *
     * This value is also used by backends such as Elasticsearch to determine
     * the mapping that is applied to the document being indexed, hence why it
     * is important that the schemas are compatible when this value is shared
     * across multiple instances.
     *
     * @var string
     */
    protected $_type = '';

    /**
     * The default limit on how many items are processed during an operation.
     *
     * This limit is the maximum number of documents that are processed during
     * indexing and queuing operations.
     *
     * It is important to note that the limit are isolated to the individual
     * operations. For example if a limit of 50 is set, a queuing operation
     * could publish 50 items with a subsequent indexing operation processing 50
     * documents as well.
     *
     * @var int
     */
    protected static $_defaultLimit = self::NO_LIMIT;

    /**
     * The default timout for an operation.
     *
     * The timeout is the maximum amount of time in seconds allowed for an
     * operation, for example queuing or indexing.
     *
     * It is important to note that the timout is isolated to the individual
     * operations. For example if a timeout of 5 is set, a queuing operation
     * could last up to 5 seconds with a subsequent indexing operation lasting
     * up to 5 seconds as well.
     *
     * @var int
     */
    protected static $_defaultTimeout = self::NO_LIMIT;

    /**
     * Parses and stores the configuration options set for this collection
     * instance.
     *
     * @var SearchConfig
     */
    protected $_config;

    /**
     * The schema that models the collection's source data as it is stored in
     * the index.
     *
     * @var SearchSchema
     */
    protected $_schema;

    /**
     * The global dispatcher object.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $_dispatcher;

    /**
     * The global queue object.
     *
     * @var SearchQueueAbstract
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

        $this->_dispatcher = SearchRegistry::getDispatcher();
        $this->_queue = SearchRegistry::getQueue();

        $this->init();
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
     * This method acts as an on-demand scheduler agent that interacts with the
     * index scheduler to see which items are due for indexing. In some
     * instances, a scheduler agent can be as simple as a client library
     * fetching data from some backend, for example a feed parser consuming a
     * resource.
     *
     * @return \Traversable|array
     *   The items that are scheduled to be indexed.
     */
    abstract public function fetchScheduledItems();

    /**
     * Builds a queue message for the item scheduled for indexing.
     *
     * This hook is invoked just prior to sending the item to the index queue.
     *
     * @param SearchQueueMessage $message
     *   The message that will be published to the queue.
     * @param mixed $item
     *   The item that is scheduled for indexing.
     */
    abstract public function buildQueueMessage(SearchQueueMessage $message, $item);

    /**
     * Loads the source data from the message fetched from the indexing queue.
     *
     * This method is useful for lazy-loading the source data given a unique
     * identifier. For example, when loading data from a CMS, the item will
     * often be an identifier of the content being indexed.
     *
     * @param SearchQueueMessage $message
     *   The message consumed from the queue containing the items scheduled for
     *   indexing.
     *
     * @return mixed
     *   The source data being indexed.
     */
    abstract public function loadSourceData(SearchQueueMessage $message);

    /**
     * Populates a SearchIndexDocument object with fields extracted from the the
     * source data loaded in SearchCollectionAbstract::loadSourceData().
     *
     * @param SearchIndexDocument $document
     *   The document object that is populated with fields by this method.
     * @param mixed $data
     *   The source data returned by SearchCollectionAbstract::loadSourceData()
     *   that is being indexed.
     */
    abstract public function buildDocument(SearchIndexDocument $document, $data);

    /**
     * Publishes the items scheduled for indexing to the queue.
     */
    public function queueScheduledItems()
    {
        $event = new SearchCollectionEvent($this);
        $this->_dispatcher->dispatch(SearchEvents::COLLECTION_PRE_QUEUE, $event);

        foreach ($this as $message) {
            $message->publish();
        }

        $this->_dispatcher->dispatch(SearchEvents::COLLECTION_POST_QUEUE, $event);
    }

    /**
     * Implements \IteratorAggregate::getIterator().
     *
     * Iterates over the items scheduled for indexing and returns the populated
     * messages objects that can be sent to the queue.
     */
    public function getIterator()
    {
        return new SearchQueueProducerIterator($this->_queue, $this);
    }

    /**
     * Implements SearchConfigurableInterface::getId().
     *
     * @see SearchCollectionAbstract::_id
     */
    public function getId()
    {
        return static::$_id;
    }

    /**
     * Implements SearchConfigurableInterface::getConfig().
     *
     * @see SearchCollectionAbstract::_config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Returns the schema that models the collection's source data as it is
     * stored in the index.
     *
     * @return SearchSchema
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Sets or replaces the type of content contained in this resource that this
     * collection references.
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
     * Returns the type of content contained in this collection.
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
