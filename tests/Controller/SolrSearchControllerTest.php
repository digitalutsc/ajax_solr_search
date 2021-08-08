<?php

namespace Drupal\ajax_solr_search\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the ajax_solr_search module.
 */
class SolrSearchControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "ajax_solr_search SolrSearchController's controller functionality",
      'description' => 'Test Unit for module ajax_solr_search and controller SolrSearchController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests ajax_solr_search functionality.
   */
  public function testSolrSearchController() {
    // Check that the basic functions of module ajax_solr_search.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
