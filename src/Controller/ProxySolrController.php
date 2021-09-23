<?php

namespace Drupal\ajax_solr_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProxySolrController.
 */
class ProxySolrController extends ControllerBase {

  /**
   * Select.
   *
   * @return string
   *   Return Hello string.
   */
  public function select($core) {
    // get Solr URL saved in config form
    $config = \Drupal::config('ajax_solr_search.ajaxsolrsearchconfig');

    $curl = curl_init();
    $solr_url = $config->get("solr-server-url") . '/select?'. $_POST['query'];

    curl_setopt_array($curl, array(
      CURLOPT_URL => $solr_url ,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return new JsonResponse(json_decode($response));
  }

}
