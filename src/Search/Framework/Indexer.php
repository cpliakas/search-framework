<?php

/**
 * Search Framework
 *
 * @author    Chris Pliakas <opensource@chrispliakas.com>
 * @copyright 2013 Chris Pliakas
 * @license   http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License (LGPL)
 * @link      https://github.com/cpliakas/search-framework
 */

namespace Search\Framework;

use Search\Framework\Event\IndexDocumentEvent;
use Search\Framework\Event\SearchEngineEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Indexes collections.
 */
class Indexer extends CollectionAgentAbstract
{
    /**
     * The search engine that is indexing the source data.
     *
     * @var SearchEngineAbstract
     */
    protected $_searchEngine;

    /**
     * Constructs an Indexer object.
     *
     * @param EventDispatcherInterface $dispatcher
     *   The dispatcher used to throw indexing related events.
     * @param SearchEngineAbstract $search_engine
     *   The search engine that is indexing the source data.
     */
    public function __construct(SearchEngineAbstract $search_engine)
    {
        $this->_searchEngine = $search_engine;
    }

    /**
     * Sets the dispatcher used to throw indexing related events.
     *
     * @param EventDispatcher $dispatcher
     *   The dispatcher used to throw indexing related events.
     *
     * @return Indexer
     */
    public function setDispatcher(EventDispatcher $dispatcher)
    {
        $this->_dispatcher = $dispatcher;
        return $this;
    }

    /**
     * Implements CollectionAgentAbstract::getDispatcher().
     *
     * If a dispatcher is not set, then one is instantiated.
     */
    public function getDispatcher()
    {
        if (!isset($this->_dispatcher)) {
            $this->_dispatcher = new EventDispatcher();
        }
        return $this->_dispatcher;
    }

    /**
     * Returns the dispatcher used to throw indexing related events.
     *
     * @return SearchEngine
     */
    public function getSearchEngine()
    {
        return $this->_searchEngine;
    }

    /**
     * Returns a Collector object that the Indexer class injects itself as a
     * service into.
     *
     * @return Collector
     */
    public function newCollector()
    {
        $collector = new Collector($this->getDispatcher());

        // Pass services and settings to collector.
        $collector
            ->setCollections($this->_collections)
            ->setLogger($this->getLogger())
            ->setQueue($this->getQueue())
            ->setTimeout($this->_timeout)
            ->setLimit($this->_limit);

        return $collector;
    }

    /**
     * Apply the serivce specific normalizers to a field's value.
     *
     * @param IndexField $field
     *   The field being normalized.
     *
     * @return string|array
     */
    public function normalizeField(IndexField $field)
    {
        $id = $field->getId();
        $value = $field->getValue();

        $log = $this->getLogger();
        $context = array('field' => $id);

        if (isset($this->_fieldTypes[$id])) {
            if ($this->_searchEngine->hasNormalizer($this->_fieldTypes[$id])) {
                $normalizer = $this->_searchEngine->getNormalizer($this->_fieldTypes[$id]);
                $context['normalizer'] = get_class($normalizer);
                $value = $normalizer->normalize($value);
                $log->debug('Normalizer applied to field', $context);
            }
        } else {
            $log->debug('Data type could not be determined for field', $context);
        }

        return $value;
    }

    /**
     * Perform the index creation operation.
     */
    public function createIndex(array $options = array())
    {
        $this->_searchEngine->createIndex($this, $options);
    }

    /**
     * Fetches the items scheduled for indexing and indexes them via the search
     * engine.
     *
     * @see CollectorAbstract::queue()
     * @see Indexer::indexQueuedItems()
     */
    public function index()
    {
        $this->newCollector()->queue();
        $this->indexQueuedItems();
    }

    /**
     * Indexes the items that are queued for indexing into the search engine.
     */
    public function indexQueuedItems()
    {
        $dispatcher = $this->getDispatcher();

        $log = $this->getLogger();
        $context = array('engine' => get_class($this->_searchEngine));
        $log->info('Indexing operation started', $context);

        try {

            // Ensure the schema object is populated. This routine will also
            // throw the SearchEvents::SCHEMA_ALTER event as well as detect
            // incompatible schemata.
            $this->getSchema();

            // Adds the search engine instance  as a subscriber only for the
            // duration of this indexing process.
            $dispatcher->addSubscriber($this->_searchEngine);

            $event = new SearchEngineEvent($this);
            $this->dispatchEvent(SearchEvents::SEARCH_ENGINE_PRE_INDEX, $event);

            // Consume messages from the queue that correspond with items that
            // are scheduled for indexing.
            $consumer = new QueueConsumer($this);
            foreach ($consumer as $message) {
                $context['item'] = $message->getBody();
                $log->debug('Consumed item scheduled for indexing from queue', $context);
                $this->indexQueuedItem($message);
            }

            unset($context['item']); // The item is no longer in context.
            $this->dispatchEvent(SearchEvents::SEARCH_ENGINE_POST_INDEX, $event);

            // The search engine should only listen to events throws during it's
            // own indexing operation.
            $dispatcher->removeSubscriber($this->_searchEngine);

        } catch (Exception $e) {
            // Make sure this service is removed as a subscriber. See above.
            $dispatcher->removeSubscriber($this->_searchEngine);
            throw $e;
        }

        $context['indexed'] = count($consumer);
        $log->info('Indexing operation completed', $context);
    }

    /**
     * Indexes an item that is queued for indexing into the search engine.
     */
    public function indexQueuedItem(QueueMessage $message)
    {
        $log = $this->getLogger();
        $context = array(
            'engine' => get_class($this->_searchEngine),
            'item' => $message->getBody(),
        );

        // Load the source data form the message. The message usually contains a
        // unique identifier in the body. Skip processing if false is returned
        // as the source data.
        $collection = $message->getCollection();
        $data = $collection->loadSourceData($message);

        if ($data) {
            $context['collection'] = $collection->getId();
            $log->debug('Data fetched from source', $context);

            // Build an index document from the source data.
            $document = $this->_searchEngine->newDocument($this);
            $collection->buildDocument($document, $data);
            $log->debug('Document prepared for indexing', $context);

            // Index the document, sandwich indexing with events.
            $event = new IndexDocumentEvent($this, $document, $data);
            $this->dispatchEvent(SearchEvents::DOCUMENT_PRE_INDEX, $event);
            $this->_searchEngine->indexDocument($collection, $document);
            $this->dispatchEvent(SearchEvents::DOCUMENT_POST_INDEX, $event);

            $log->debug('Document processed for indexing', $context);

        } else {
            $log->critical('Data could not be loaded from source', $context);
        }
    }
}
