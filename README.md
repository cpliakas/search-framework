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

// @see https://github.com/cpliakas/feed-collection
use Search\Collection\Feed\FeedCollection;
// @see https://github.com/cpliakas/solarium-search-server
use Search\Service\Solr\SolrSearchService;

require 'vendor/autoload.php';

// Define the collection, or source data, being indexed / searched.
$drupal_planet = new FeedCollection();
$drupal_planet->setFeedUrl('http://drupal.org/planet/rss.xml');

// Associate the collection with the Solr server.
// $options = array(...); @see http://wiki.solarium-project.org/index.php/V3:Basic_usage
$solr = new SolrSearchService($options);
$solr->addCollection($drupal_planet);

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

// @see https://github.com/cpliakas/elastica-search-server
use Search\Service\Elasticsearch\ElasticsearchSearchService;

// Associate the collection with the Elasticsearch service.
// $options = array(...); @see http://ruflin.github.com/Elastica/#section-connect
$elasticsearch = new ElasticsearchSearchService($options);
$elasticsearch->addCollection($drupal_planet);

// Once you have created the index and mappings, index the content.
$elasticsearch->index();

// When the documents are committed, execute a search.
$elasticsearch->search('drupal');

// Delete the index.
$elasticsearch->delete();
```
