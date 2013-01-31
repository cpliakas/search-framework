Overview
========

This library is a search framework that facilitates reusable code and concepts
across search backend libraries.

Its purpose is to provide common nomenclature and concepts to facilitate code
reuse when building search enabled applications. The following example
demonstrates how a collection of source data can be indexed using the Search
Framework library.

Usage
=====

```php

// Indexes an RSS feed into Solr.

// @see https://github.com/cpliakas/feed-collection
use Search\Collection\Feed\FeedCollection;
// @see https://github.com/cpliakas/solarium-search-server
use Search\Server\Solarium\SolariumSearchServer;

require 'vendor/autoload.php';

// Define the collection, or source data, being indexed / searched.
$drupal_planet = new FeedCollection();
$drupal_planet->setFeedUrl('http://drupal.org/planet/rss.xml');

// Associate the collection with the Solr server.
// $options = array(...);
$solr = new SolariumSearchServer($options);
$solr->addCollection($drupal_planet);

// Index the feeds into Solr.
$solr->index();

```