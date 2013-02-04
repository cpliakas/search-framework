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
 * Collections are datasources that are being indexed. Examples are files on a
 * filesystem, RSS feeds, or the content in a CMS.
 */
abstract class SearchCollectionAbstract
{
    /**
     * Object populated with the loaded configuration options.
     *
     * @var SearchConfig
     */
    protected $_config;

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
        $this->_config->load(SearchConfig::COLLECTION, $this->getConfigFile());

        if ($type = $this->_config->getOption('type')) {
            $this->_type = $type;
        }

        $schema_options = $this->_config->getOption('schema', array());
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
    abstract public function getQueue($limit = SearchIndexer::NO_LIMIT);

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
     * Returns this collection's schema.
     *
     * @return SearchSchema
     */
    public function getSchema()
    {
        return $this->_schema;
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
