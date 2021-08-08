<?php

namespace Drupal\ajax_solr_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AjaxSolrSearchConfigForm.
 */
class AjaxSolrSearchConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ajax_solr_search.ajaxsolrsearchconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ajax_solr_search_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ajax_solr_search.ajaxsolrsearchconfig');

    $form =  parent::buildForm($form, $form_state);

    $form['container'] = array(
      '#type' => 'container',
    );

    $form['container']['solr-config'] = array(
      '#type' => 'details',
      '#title' => 'Solr Settings',
      '#open' => true,

    );

    $form['container']['solr-config']['server-url'] = array(
      '#type' => 'textfield',
      '#name' => 'solr-url',
      '#title' => $this
        ->t('Solr Endpoint URL:'),
      '#default_value' => ($config->get("solr-server-url") !== null) ? $config->get("solr-server-url") : "",
      '#description' => $this->t('For example: <code>http://localhost:8983/solr/multisite</code>')
    );

    $form['container']['solr-config']['searchable-fields'] = array(
      '#type' => 'textarea',
      '#name' => 'searchable-fields',
      '#title' => $this
        ->t('Enter Solr field(s) to search:'),
      '#default_value' => ($config->get("solr-searchable-fields") !== null) ? $config->get("solr-searchable-fields") : "",
      '#description' => $this->t('For example: <code>ss_title</code>. For multiple, enter each field in each line')
    );

    $form['container']['solr-config']['facets-fields'] = array(
      '#type' => 'textarea',
      '#name' => 'facets-fields',
      '#title' => $this
        ->t('Enter Solr field(s) for Facets:'),
      '#default_value' => ($config->get("solr-facets-fields") !== null) ? $config->get("solr-facets-fields") : "",
      '#description' => $this->t('For example: <code>ss_content_type</code>. For multiple, enter each field in each line')
    );

    $form['container']['solr-config']['results-html'] = array(
      '#type' => 'textarea',
      '#name' => 'results-html',
      '#title' => $this
        ->t('Result HTML code:'),
      '#default_value' => ($config->get("solr-results-html") !== null) ? $config->get("solr-results-html") : "",
    );


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    //$this->config('ajax_solr_search.ajaxsolrsearchconfig')->save();
    $configFactory = $this->configFactory->getEditable('ajax_solr_search.ajaxsolrsearchconfig');

    $configFactory->set("solr-server-url", $form_state->getValues()['server-url']);
    $configFactory->set("solr-searchable-fields", $form_state->getValues()['searchable-fields']);
    $configFactory->set("solr-facets-fields", $form_state->getValues()['facets-fields']);
    $configFactory->set("solr-results-html", $form_state->getValues()['results-html']);
    $configFactory->save();

    parent::submitForm($form, $form_state);
  }

}
