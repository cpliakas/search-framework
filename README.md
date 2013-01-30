Overview
========

This library is a search framework that facilitates reusable code and concepts
across search backend libraries.

Its purpose is to abstract backend agnostic tasks to facilitate code reuse when
building search enabled applications. The following example demonstrates how a
collection of source data can be indexed using the Search Framework library.

Usage
=====

```php

// Classes used to index an RSS feed into Solr.
use Search\Collection\Feed\FeedCollection;
use Search\Server\Solarium\SolariumSearchServer;

require 'vendor/autoload.php';

// Define collection, or source data, being indexed / searched.
$drupal_planet = new FeedCollection();
$drupal_planet->setFeedUrl('http://drupal.org/planet/rss.xml');

// Associate the collection with the Solr server.
$solr = new SolariumSearchServer();
$solr->addCollection($drupal_planet);

// Index the feeds into Solr.
$solr->index();

```