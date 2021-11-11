<?php

namespace Drupal\ajax_solr_search\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Class AjaxSolrSearchRoutes definition.
 */
class AjaxSolrSearchRoutes implements ContainerInjectionInterface {

  /**
   * The Saved configuration.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
    );
  }

  /**
   * Process and return Route Info.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   Route Objects.
   */
  public function routes() {
    $routes = [];

    $app_config = $this->config->get('ajax_solr_search.ajaxsolrsearchconfig');

    $path = $app_config->get('search-results-path') ?: '/federated-search';

    $args = [
      '_controller' => 'Drupal\ajax_solr_search\Controller\SolrSearchController::getSearchForm',
      '_title' => 'Search',
    ];

    $routes['ajax_solr_search.federated_search_page'] = new Route($path, $args, ['_permission' => 'access content']);

    return $routes;
  }

}
