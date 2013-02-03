<?php

/**
 * Search Framework
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace Search\Framework;

use Search\Framework\Event\SearchDocumentEvent;
use Search\Framework\Event\SearchCollectionEvent;
use Symfony\Component\Yaml\Yaml;

/**
 * Adapter class extended by search collections.
 *
 * Collections are datasources that are being indexed. Examples are files on a
 * filesystem, RSS feeds, or the content in a CMS.
 */
abstract class SearchCollectionAbstract
{
    /**
     * Statically cached configurations keyed by filepath.
     *
     * @var array
     */
    protected static $_config = array();

    /**
     * The unique identifier of this collection.
     *
     * The identifier should be unique across all collection classes. It is also
     * used to determine the name of the collectionspecific configuration file
     * located in the `conf/collections` directory. For example, a collection
     * with an identifier of "feed" would read the `conf/collections/feed.yml`
     * configuration file.
     *
     * @var string
     */
    protected static $_id = '';

    /**
     * An associative array of configuration options for this collection.
     *
     * Extending classes may expose collection-specific configuration options.
     * for example, a feed collection might define a "url" option to specify
     * the feed being consumed.
     *
     * @var array
     */
    protected $_options = array();

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
     * The schema modeled after the field definitions in the collection.yml
     * configuration file.
     *
     * @var SearchSchema
     */
    protected $_schema;

    /**
     * Constructs a SearchCollectionAbstract object.
     *
     * @param array $options
     *   An associative array of configuration options that override that values
     *   read from the configuration file.
     *
     * @throws ParseException
     */
    public function __construct(array $options = array())
    {
        $this->_options = $options;

        if ($config = $this->getConfig()) {
            $this->_options = array_merge($config, $this->_options);
        }

        if (isset($this->_options['type'])) {
            $this->_type = $this->_options['type'];
        }

        $schema_options = !empty($this->_options['schema']) ? $this->_options['schema'] : array();
        $this->_schema = new SearchSchema($schema_options);

        $this->init();
    }

    /**
     * Hook that allows the collection object to initialize itself.
     *
     * This is most often implemented to instantiate or set a backend specific
     * class.
     */
    abstract public function init();

    /**
     * Returns an object containing the items in the collection that are
     * enqueued for indexing.
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
     *   The document object instantiated by the service.
     * @param mixed $data
     *   The source data being indexed.
     */
    abstract public function buildDocument(SearchIndexDocument $document, $data);

    /**
     * Loads the source data, defaults to returning the enqueued item passed to
     * it.
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
     * Returns the directory of the class.
     *
     * If this base class is overridden, the method  should get the directory of
     * the overriding class which is why we cannot use __DIR__.
     *
     * @return string
     */
    public function getClassDir()
    {
        $reflection = new \ReflectionClass($this);
        return dirname($reflection->getFileName());
    }

    /**
     * Returns the path to the .collection.yml file.
     *
     * The method assumes that the follwing directory structure is used:
     * `{src-dir}/Search/Collection/{collection-name}/{collection-class}.php`
     *
     * @return string|false
     *   The absolute path to the configuration file, false if the file does not
     *   exist or could not be resolved.
     */
    public function getConfigFile()
    {
        $config_dir = $this->getClassDir() . '/../../../../conf/collection';
        return realpath($config_dir . '/' . self::id() . '.yml' );
    }

    /**
     * Returns the parsed configuration file.
     *
     * If the configuration file could not be found, an empty array is returned.
     * An exception is thrown if the YAML file can not be parsed.
     *
     * @return array
     *
     * @throws ParseException
     */
    public function getConfig()
    {
        $config = array();
        $config_file = $this->getConfigFile();
        if ($config_file) {
            if (!isset(self::$_config[$config_file])) {
                $config = Yaml::parse($config_file);
            } else {
                $config = self::$_config[$config_file];
            }
        }
        return $config;
    }

    /**
     * Returns the unqiue identifier of this collection.
     *
     * @return string
     */
    public static function id()
    {
        return static::$_id;
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
        return $this->setOption('label', $label);
    }

    /**
     * Returns the label of this collection.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->getOption('label', '');
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
        return $this->setOption('description', $description);
    }

    /**
     * Returns the description of this collection.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getOption('description', '');
    }

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
     * Sets or overrides a configuration option.
     *
     * @param string $option
     *   The unique name of the configuration option.
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
     * Processes the items in this collection that are enqueued for indexing.
     *
     * @param SearchServiceAbstract $service
     *   The search service that is indexing the collection.
     * @param int $limit
     *   The maximum number of documents to process. Defaults to -1, which
     *   means there is no limit on the number of documents processed.
     */
    public function index(SearchServiceAbstract $service, $limit = SearchCollectionQueue::NO_LIMIT)
    {
        $queue = $this->getQueue($limit);
        $dispatcher = $service->getDispatcher();

        $collection_event = new SearchCollectionEvent($service, $this, $queue);
        $dispatcher->dispatch(SearchEvents::COLLECTION_PRE_INDEX, $collection_event);

        // Iterate over items enqueued for indexing.
        foreach ($queue as $item) {

            // Get the document object and load the source data.
            $document = $service->newDocument();
            $data = $this->loadSourceData($item);

            // Allow the collection to populate the docuemnt with fields.
            $this->buildDocument($document, $data);

            // Instantiate and throw document related events, allow the backend
            // to process the document enqueued for indexing.
            $document_event = new SearchDocumentEvent($service, $document, $data);
            $dispatcher->dispatch(SearchEvents::DOCUMENT_PRE_INDEX, $document_event);
            $service->indexDocument($this, $document);
            $dispatcher->dispatch(SearchEvents::DOCUMENT_POST_INDEX, $document_event);
        }

        $dispatcher->dispatch(SearchEvents::COLLECTION_POST_INDEX, $collection_event);
    }
}
