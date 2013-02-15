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
 * Collections are objects that reference a resource containing the data being
 * indexed. Some examples are be files on a filesystem, RSS feeds, the content
 * in a CMS, or anything else imaginable having data that can be indexed.
 *
 * This object also acts as an on-demand scheduler agent that interacts with the
 * collection's job scheduler to fetch the items that are due for indexing.
 */
abstract class CollectionAbstract
{
    /**
     * The type of content in the collection.
     *
     * This property is intended to be overridden by the extending class but
     * can be modified at runtime. It is also acceptable for instances of
     * different collections classes to share the same type, however their
     * schemata should be compatible and near-identical. For example, a
     * collection of rss feeds and a collection of podcasts might both use the
     * "feeds" type if they share the same schema structure.
     *
     * It is best practice to use only lowercase letters, numbers, dots (.),
     * and underscores (_). Examples might be "feeds", "database.db_name".
     *
     * This value is used by backends such as Elasticsearch to determine the
     * mapping that is applied to the document being indexed, hence why it is
     * important that the schemas are compatible when this value is shared
     * across multiple instances.
     *
     * @var string
     */
    protected $_type = '';

    /**
     * The basename of the configuration file with the ".yml" extension
     * excluded.
     *
     * This property is intended to be overridden by the extending class and
     * should be shared across all instances of the same collection class. It is
     * returned by the CollectionAbstract::getConfigBasename() method.
     *
     * @var string
     */
    protected static $_configBasename = '';

    /**
     * The unique identifier of the collection instance.
     *
     * This value must be unique across all collection instances. An Exception
     * is thrown if an attempt is made to attach multiple collections with the
     * same identifier to the collection agent.
     *
     * It is best practice to use only lowercase letters, numbers, dots (.),
     * and underscores (_). Examples might be "feed", "feed.tech", "feed.news",
     * or "drupal.entity.node".
     *
     * @var string
     */
    protected $_id;

    /**
     * Constructs a CollectionAbstract object.
     *
     * @param string $id
     *   The unique identifier of the collection instance.
     * @param array $options
     *   An associative array of collection specific configuration options.
     */
    public function __construct($id, array $options = array())
    {
        $this->_id = $id;
        $this->init($options);
    }

    /**
     * Method that allows the extending collection to initialize itself.
     *
     * This is most often used to instantiate a backend library, for example a
     * database connection, ORM, feed parser, etc.
     *
     * @param array $options
     *   An associative array of collection specific option.
     */
    abstract public function init(array $options);

    /**
     * Fetches the items that are scheduled for indexing.
     *
     * This method is effectively the worker for the scheduler agent that
     * interacts with the collection's job scheduler to fetch items that are due
     * for indexing. Although this sounds complex, it could be as simple as a
     * feed parser fetching items from an RSS feed.
     *
     * @param int $limit
     *   The maximum number of items to fetch.
     *
     * @return \Iterator
     */
    abstract public function fetchScheduledItems($limit = CollectionAgent::NO_LIMIT);

    /**
     * Builds a queue message for the item scheduled for indexing.
     *
     * This hook is usually invoked by the QueueProducer::valid() method prior
     * to publishing the item to the indexing queue.
     *
     * @param QueueMessage $message
     *   The message that will be published to the queue.
     * @param mixed $item
     *   The item that is scheduled for indexing. An item is something that is
     *   fetched from the CollectionAbstract::fetchScheduledItems() method.
     */
    abstract public function buildQueueMessage(QueueMessage $message, $item);

    /**
     * Loads the source data from the message fetched from the indexing queue.
     *
     * This method is useful for lazy-loading the source data given a unique
     * identifier. For example, when loading data from a CMS, the item will
     * often be an identifier of the content being indexed.
     *
     * @param SearchQueueMessage $message
     *   The message consumed from the queue containing the item scheduled for
     *   indexing.
     *
     * @return mixed
     *   The source data being indexed in some native data structure.
     */
    abstract public function loadSourceData(QueueMessage $message);

    /**
     * Populates an IndexDocument object with fields extracted from the the
     * source data that is loaded in CollectionAbstract::loadSourceData() hook.
     *
     * @param IndexDocument $document
     *   The document object that is populated with fields by this method.
     * @param mixed $data
     *   The source data returned by SearchCollectionAbstract::loadSourceData()
     *   that is being indexed.
     */
    abstract public function buildDocument(IndexDocument $document, $data);

    /**
     * Returns the unique identifier of the collection instance.
     *
     * @return string
     *
     * @see CollectionAbstract::_id
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Returns the basename of the configuration file with the ".yml" extension
     * excluded.
     *
     * @return string
     *
     * @see CollectionAbstract::_configBasename
     */
    public function getConfigBasename()
    {
        return static::$_configBasename;
    }

    /**
     * Sets or replaces the type of content in the collection.
     *
     * @param string $type
     *   The type of content in the collection.
     *
     * @return SearchCollectionAbstract
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * Returns the type of content contained in the collection.
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }
}
