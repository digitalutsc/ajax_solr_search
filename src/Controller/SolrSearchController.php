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

    $search_form = '<div id="wrap">
      <div class="right">
        <div id="result">
          <div id="navigation">
            <div id="pager-header"></div>
            <div class="pager">
                <ul id="pager"></ul>
            </div>
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
        <h2>Search</h2>

        <div id="search">
          <input type="text" id="query" name="query" autocomplete="off" />
        </div>
        <span id="search_help">
          <p>(press ENTER to search)</p>
          '. $config->get("search-instruction") .'
          </span>
        <!-- Current Selection -->
         <ul id="selection"></ul>

       ' . $facets_htmls . '
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
