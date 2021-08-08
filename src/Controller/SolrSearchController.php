<?php

namespace Drupal\ajax_solr_search\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class SolrSearchController.
 */
class SolrSearchController extends ControllerBase {

  /**
   * Getsearchform.
   *
   * @return string
   *   Return Hello string.
   */
  public function getSearchForm() {

    $search_form = '<div id="wrap">
      <div class="right">
        <div id="result">
          <div id="navigation">
            <ul id="pager"></ul>
            <div id="pager-header"></div>
          </div>
          <div id="docs" data-content=""></div>
        </div>
      </div>

      <div class="left">
        <h2>Current Selection</h2>
        <ul id="selection"></ul>

        <h2>Search</h2>
        <span id="search_help">(press ESC to close suggestions)</span>
        <ul id="search">
          <input type="text" id="query" name="query" autocomplete="off"
                 data-content="ss_federated_title,site,ss_federated_terms,content">
        </ul>

        <input id="facets-fields" name="facets-fields" type="hidden" data-content="site,ss_federated_terms,ss_federated_type" />
        <h2>Sites</h2>
        <div class="tagcloud" id="site"></div>

        <h2>Tag</h2>
        <div class="tagcloud" id="ss_federated_terms"></div>

        <h2>Content type</h2>
        <div class="tagcloud" id="ss_federated_type"></div>

        <div class="clear"></div>
      </div>
      <div class="clear"></div>
    </div>';

    return [
      '#type' => 'markup',
      '#markup' => $this->t($search_form),
      '#attached' => [
        'library' => [
          'ajax_solr_search/ajax_solr_search',
        ],
      ],
    ];
  }

}
