Overview
========

This library is a search framework that facilitates reusable code and concepts
across search backend libraries.

Its purpose is to provide common nomenclature and concepts to facilitate code
reuse when building search enabled applications. The following example
demonstrates how a collection of source data can be indexed and searched using
the Search Framework library.

Usage
=====

```php

// Indexes an RSS feed into Solr.

use Search\Framework\SearchServiceEndpoint;
use Search\Collection\Feed\FeedCollection;  // @see https://github.com/cpliakas/feed-collection
use Search\Service\Solr\SolrSearchService;  // @see https://github.com/cpliakas/solr-search-service

require 'vendor/autoload.php';

// Connect to a Solr server.
$endpoint = new SearchServiceEndpoint('local', 'http://localhost', '/solr', 8983);
$solr = new SolrSearchService($endpoint);

// Associate a collection, or source data being indexed, with the Solr service.
$drupal_planet = new FeedCollection();
$drupal_planet->setFeedUrl('http://drupal.org/planet/rss.xml');
$solr->attachCollection($drupal_planet);

// Index the feeds into Solr.
$solr->index();

// Solr generally has an indexing delay, but after the documents are committed
// then search the collection.
$solr->search('drupal');

// Wipe the index.
$solr->delete();

```

How about indexing the data into Elasticsearch? Modify the example above
slightly to use the library that integrates with the Elasticsearch project.

```php

// @see https://github.com/cpliakas/elasticsearch-service
use Search\Service\Elasticsearch\ElasticsearchSearchService;

// Associate the collection with the Elasticsearch service.
$endpoint = new SearchServiceEndpoint('local', 'localhost', 'feeds', 9200);
$elasticsearch = new ElasticsearchService($endpoint);
$elasticsearch->attachCollection($drupal_planet);

// Create the index and put the mappings.
$elasticsearch->createIndex();

// Index the feeds into Elasticsearch.
$elasticsearch->index();

// When the documents are committed, execute a search.
$elasticsearch->search('drupal');

// Delete the index.
$elasticsearch->delete();
```
