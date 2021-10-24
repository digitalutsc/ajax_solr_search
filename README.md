# Ajax Solr for Drupal

## Introduction
Drupal 8/9 module provide a user interface for searching indexed content directly against Solr endpoint without using Views and Search API Solr moddule.

At Digital Scholarship Unit, University of Toronto Scarborough, we use this module for Federated Search content amount our sites.


## Federated Search
We have developed software to support search and retrieval using a core that is shared by multiple individual Drupal sites. This allows us to have a federated search across Drupal sites where an end user can conduct a search and see relevant results from multiple sites.
![Federated Search](https://github.com/digitalutsc/islandora_lite_docs/raw/main/Islandora%20Lite%20Solr%20Setup.svg "Federated Search")

## Requirements
* A Solr endpoint
* Multiple sites indexing contents to the only Solr endpoint above.
* [Ajax Solr library](https://github.com/evolvingweb/ajax-solr) as dependency library.
* In `admin/config/search/search-api` each of your sites, setup With fields which following schema:
  * TBA

## Installation:
* Download the module by cloning this module to your module directory
  * `git clone https://github.com/digitalutsc/drupal_ajax_solr_search.git`
* Clone the dependency [Ajax Solr library](https://github.com/evolvingweb/ajax-solr) with `islandora_lite` branch:
  * `git clone -b islandora_lite https://github.com/digitalutsc/ajax-solr.git`
* Enable the module
