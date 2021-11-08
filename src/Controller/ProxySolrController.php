<?php

namespace Drupal\ajax_solr_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProxySolrController description.
 */
class ProxySolrController extends ControllerBase {

  /**
   * Select.
   *
   * @return string
   *   Return Hello string.
   */
  public function select($core) {
    if (isset(!$_POST['query'])) {
      return new JsonResponse(json_decode("Not a valid query"));
    }
    // Get Solr URL saved in config form.
    $config = \Drupal::config('ajax_solr_search.ajaxsolrsearchconfig');

    $curl = curl_init();
    $solr_url = $config->get("solr-server-url") . '/select?' . $_POST['query'];

    curl_setopt_array($curl, [
      CURLOPT_URL => $solr_url,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
    ]);
    $response = curl_exec($curl);
    curl_close($curl);
    return new JsonResponse(json_decode($response));
  }

  /**
   * Get Fields from Solr by Rest.
   */
  public function fields($core) {
    // Get Solr URL saved in config form.
    $config = \Drupal::config('ajax_solr_search.ajaxsolrsearchconfig');
    $curl = curl_init();
    $query = "";
    $i = 0;
    foreach ($_POST as $field => $value) {
      $value = (!empty($value)) ? ("=" . $value) : "";
      $query .= $field . $value;
      if ($i < (count($_POST) - 1)) {
        $query .= "&";
      }
      $i++;
    }
    $solr_url = $config->get("solr-server-url") . '/select?' . $query;
    curl_setopt_array($curl, [
      CURLOPT_URL => $solr_url,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
    ]);
    $response = curl_exec($curl);
    return new JsonResponse($response);
  }

}
