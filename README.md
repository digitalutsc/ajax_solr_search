# Ajax Solr for Drupal

## Introduction
Drupal 8/9 module provides a user interface for the Federated Search for multiple Drupal sites which index their content into a single shared Solr Core.

## Based project:
* [Ajax Solr](https://github.com/evolvingweb/ajax-solr) is the JavaScript library which provide web based user interface to Apache Solr. The module integrates the library into Drupal framework.

## Federated Search
At Digital Scholarship Unit - UTSC Library, we have developed this module to support search and retrieval using a core that is shared by multiple individual Drupal sites. This allows us to have a federated search across Drupal sites where an end user can conduct a search and see relevant results from multiple sites.
![Federated Search](https://github.com/digitalutsc/islandora_lite_docs/raw/main/Islandora%20Lite%20Solr%20Setup.svg "Federated Search")

## Requirements
* A Solr endpoint
* A shared Solr core which multisite sites indexing their contents into.
* [Ajax Solr library](https://github.com/digitalutsc/ajax-solr) as dependency library.
* At `/admin/config/search/search-api/index/YOUR_SOLR_INDEX/fields` in each of exhibit sites, setup with Search API fields with the same `MACHINE NAME` and `TYPE`. For example:

| Field      | Machine Name | Type | In Solr |
| ----------- | ----------- | ----------- |----------- |
| Thumbnail      | thumbnail       | String       | ss_thumbnail       |
| Title   | title        | Fulltext       | tm_title       |
| Description   | description        | Fulltext       | tm_description       |
| URI (search_api_url)   | url        | String       | sm_url       |
| Other optional fields to add...   | ...        | ...       | ...       |

## Installation and configuration
* Cloning this module to your module directory of your Drupal site:
  * `git clone https://github.com/digitalutsc/drupal_ajax_solr_search.git`
* Clone the dependency [Ajax Solr library](https://github.com/digitalutsc/ajax-solr) to Libraries directory (ie. drupal/web/libraries):
  * `git clone https://github.com/digitalutsc/ajax-solr.git`
* Enable the module.
* Visit `/admin/config/search/ajax_solr`, setting up with:
  * The URL for Search Results Page, default is `/federated-search`.
  * Select Solr field(s) to search against.
  * Select Solr field(s) for Facets
  * Select 4 mandatory Solr fields for Search Result rows: Thumbnail, title, description, and URL. Other options field can also be added.

![Config Screenshot!](docs/ajax_solr_config.png "Config Screenshot")

## Usage
* Visit the search page at `/federated-search`.
* Live demo: https://find.digital.utsc.utoronto.ca/federated-search

## Please note:

This module won't work if its Drupal site and Solr Endpoint don't have the same Hypertext Transfer Protocol (HTTP). It means that both of Drupal site and Solr endpoint must be either in `https://` or `http://`.

## Maintainers
* [Kyle Huynh](https://github.com/kylehuynh205)
