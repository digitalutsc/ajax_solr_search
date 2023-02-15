<?php

namespace Drupal\ajax_solr_search\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class SolrSearchController definition.
 */
class SolrSearchController extends ControllerBase {

  /**
   * Getsearchform.
   *
   * @return array
   *   Return Hello string.
   */
  public function getSearchForm() {
    $config = \Drupal::config('ajax_solr_search.ajaxsolrsearchconfig');

    $facets_htmls = '';
    foreach ($config->get("solr-facets-fields") as $f) {
      $facets_htmls .= '<div style="margin-top: 20px"><h2>' . $f['label'] . '</h2><div class="tagcloud" id="' . $f['fname'] . '"></div></div>';
    }

    $year_range_field = $config->get("solr-year-field");
    $year_range_htmls = '';
    if ($year_range_field['fname'] && $year_range_field['fname'] != '-1') {
      $year_range_htmls = '<div id="year-range" style="margin-top: 20px">
        <h2><label for "start-year">'. $year_range_field['label'] .'</label></h2>
        <input type="number" id="start-year" name="start-year" placeholder="From" autocomplete="off" />
        <input type="number" id="end-year" name="end-year" placeholder="To" autocomplete="off" />
        <button class="button" type="submit" id="year-range-submit" name="year-range-submit">Refine</button>
      </div>';
    }
    
    $search_form = '<div id="wrap">
      <div class="right">
        <div id="result">
          <div id="navigation">
            <div id="pager-header"></div>
            <div class="pager">
                <ul id="pager"></ul>
            </div>
            <div id="sort-by" name="sort-by"></div>
          </div>

          <div id="docs" data-content=""></div>

          <div id="navigation">
            <div class="pager">
                <ul id="bottom-pager"></ul>
            </div>
          </div>
        </div>
      </div>

      <div class="left">
        <div id="search">
          <div class="form-group">
            <h2><label for="query">Search</label></h2>
            <input type="text" id="query" name="query" autocomplete="off" />
          </div> 
          <!--<div class="form-group">
            <button class="btn btn-primary" id="federated-search-submit" name="federated-search-submit">Search</button>
          </div>-->
        </div>
        <span id="search_help">
          <p>(press ENTER to search)</p>
          '. $config->get("search-instruction") .'
          </span>
        <!-- Current Selection -->
         <ul id="selection"></ul>

       ' . $facets_htmls . $year_range_htmls . '
        <div class="clear"></div>
      </div>
      <div class="clear"></div>
    </div>';

    $proxy_url = "";
    if ($config->get("solr-proxy-enabled") === 1) {
      global $base_url;
      $proxy_url = $config->get("solr-server-url");
      $solr_host = parse_url($proxy_url, PHP_URL_HOST);

      $solr_port = parse_url($proxy_url, PHP_URL_PORT);
      if (!empty($solr_port)) {
        $proxy_url = str_replace($solr_host . ":" . $solr_port, parse_url($base_url, PHP_URL_HOST), $proxy_url);
      }
      else {
        $proxy_url = str_replace($solr_host, $base_url, $proxy_url);
      }
    }

    return [
      '#type' => 'markup',
      '#markup' => new FormattableMarkup($search_form, []),
      '#attached' => [
        'library' => [
          'ajax_solr_search/ajax_solr_search',
        ],
        'drupalSettings' => [
          'ajax_solr_search' => [
            'solr_url' => $config->get("solr-server-url"),
            'proxy_url' => $proxy_url,
            'searchable_fields' => implode(",", $config->get("solr-searchable-fields")),
            //'access_control' => $config->get("sub-query-field-name") . ":" . $config->get("sub-query-value"),
            'condition_fields' => json_encode($config->get("solr-condition-fields")),
            'facets_fields' => json_encode($config->get("solr-facets-fields")),
            'year_field' => $config->get("solr-year-field"),
            'sort_fields' => json_encode($config->get("solr-sort-fields")),
            'results_html' => json_encode($config->get("solr-results-html")),
            'output_template' => $config->get("output-template"),
            'search_instruction' => $config->get("search-instruction"),
            'items_per_page' => $config->get("items-per-page"),
          ],
        ],
      ],
    ];
  }

}
