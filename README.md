Overview
========

The Search Framework library is a standards compliant PHP project that aims to
share concepts, nomenclature, and code across search applications. Its mission
is to simplify the process of building best-in-breed search experiences
regardless of the source data being collected, search engine being used, or PHP
framework the application is built with. The library operates under the
assumption that it can't and shouldn't solve all use cases on its own, so it is
architected to have pluggable components and extendable events that allow
developers write reusable code and optionally contribute the solutions back to
the community. It also assumes that PHP is not always the best fit for various
operations related to search, so it is designed to integrate with non-PHP tools
where appropriate.

What does this library do?
==========================

The primary focus of the Search Framework library from a technical standpoint is
on the **document processing methodology**, in other words facilitating data
retrieval and preparing it for indexing. The library is architected to support
**reusable data collections** so that the information retrieval and processing
code is backend agnostic and able to be shared. It also provides an API to
**integrate best-in-breed client libraries** and a system to achieve **parallel
indexing**. Finally, the library standardizes the search results so that theming
systems can handle the responses from search engines in a consistent manner.

Arguably more important than the code, the Search Framework library defines
**nomenclature** and **concepts** that are intended to be used independent of
the underlying technology. The main goals are to help bridge the communication
gaps between search projects and facilitate writing interoperable code by
adhering to best practices and techniques.

Is the library also a search abstraction layer?
===============================================

No, absolutely not. Unlike database management systems that are similar enough
to abstract 80% of the most common use cases, search engines such as Solr,
Elasticsearch, Sphinx, and various proprietary solutions have vastly different
capabilities and paradigms making them extremely difficult to abstract. The
danger of abstracting complex systems like search engines is the tendency to
force all interactions to fit the mold of abstraction layer. At that point the
focus is on the tool as opposed to the search related problems the application
is trying to solve while at the same time masking the benefits of the search
engine. The Search Framework library abstracts only the most basic search
operations while allowing the backend clients to do what they do best.

Basic Usage
===========

The code below indexes the "Drupal Planet" RSS feed into Elasticsearch.

```php

use Search\Framework\Indexer;
use Search\Framework\SearchServiceEndpoint;

use Search\Collection\Feed\FeedCollection;     // @see https://github.com/cpliakas/feed-collection
use Search\Engine\Elasticsearch\Elasticsearch; // @see https://github.com/cpliakas/elasticsearch-engine

require 'vendor/autoload.php';

// Instantiate a collection that references the Drupal Planet feed. Collections
// are simply connectors to and models of the source data being indexed.
$drupal_planet = new FeedCollection('feed.drupal');
$drupal_planet->setFeedUrl('http://drupal.org/planet/rss.xml');

// Connect to an Elasticsearch server.
$elasticsearch = new Elasticsearch(new SearchEngineEndpoint('local', 'localhost', 'feeds', 9200));

// Instantiate an indexer, attach the collection, and index it.
$indexer = new Indexer($elasticsearch);
$indexer->attachCollection($drupal_planet);
$indexer->createIndex();
$indexer->index();

```

How about indexing the data into Solr? Simply swap out the search engine in the
code above.

```php

use Search\Engine\Solr\Solr;  // @see https://github.com/cpliakas/solr-search-engine

// Connect a  Solr server and pass it to the indexer.
$solr = new Solr(new SearchEngineEndpoint('local', 'http://localhost', '/solr', 8983));

$indexer = new Indexer($solr);

```

Installation
============

To install the required libraries, execute the following commands in the
directory where this library is extracted.

    curl -s https://getcomposer.org/installer | php
    php composer.phar install

If curl is not available, replace the first command with the one below:

    php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"

Please refer to the [Composer](http://getcomposer.org/) tool's
[installation documentation](http://getcomposer.org/doc/00-intro.md#installation-nix)
for more information.
