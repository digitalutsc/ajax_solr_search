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
      '#name' => 'server-url',
      '#title' => $this
        ->t('Solr Endpoint URL:'),
      '#default_value' => ($config->get("solr-server-url") !== null) ? $config->get("solr-server-url") : "",
      '#description' => $this->t('For example: <code>http://localhost:8983/solr/multisite</code>')
    );

    $form['container']['solr-config']['enable-proxy'] = array(
      '#type' => 'checkbox',
      '#title' => $this
        ->t('Enable Proxy (if the above Solr endpoint is behind VPN or firewall)'),
      '#default_value' => ($config->get("solr-proxy-enabled") !== null) ? $config->get("solr-proxy-enabled") : 0,
    );

    $form['container']['searchable-fields'] = array(
      '#type' => 'details',
      '#title' => 'Searchable Fields',
      '#open' => true,
    );
    $form['container']['searchable-fields']['searchable-fields'] = array(
      '#type' => 'textarea',
      '#name' => 'searchable-fields',
      '#title' => $this
        ->t('Enter Solr field(s) to search on:'),
      '#default_value' => ($config->get("solr-searchable-fields") !== null) ? $config->get("solr-searchable-fields") : "",
      '#description' => $this->t('For example: <code>ss_title</code>. For multiple, separated with comma')
    );

    // Gather the number of names in the form already.
    $num_facets_fields = $form_state->get('num_facets_fields');
    // We have to ensure that there is at least one name field.
    if ($num_facets_fields === NULL) {
      if ($config->get("solr-facets-fields") !== null && count($config->get("solr-facets-fields"))  > 0 ) {
        $num_facets_fields = count($config->get("solr-facets-fields"));
        $name_field = $form_state->set('num_facets_fields', $num_facets_fields);
      }
      else {
        $name_field = $form_state->set('num_facets_fields', 1);
        $num_facets_fields = 1;
      }
    }
    $form['container']['facets'] = array(
      '#type' => 'details',
      '#title' => 'Facets',
      '#open' => true,
      '#prefix' => '<div id="facets-fields-wrapper">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    );


    for ($i = 0; $i < $num_facets_fields; $i++) {
      $form['container']['facets']['facets-field-name-'.$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Field Name #'. ($i+ 1)),
        '#prefix' => '<div class="form--inline clearfix"><div class="form-item">',
        '#suffix' => '</div>',
        '#default_value' => (!empty($config->get("solr-facets-fields")[$i]['fname'])) ? $config->get("solr-facets-fields")[$i]['fname'] : ''
      ];
      $form['container']['facets']['facets-field-label-'.$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Field Label #' . ($i +1)),
        '#description' => $this->t('Leave it empty to hide the label'),
        '#prefix' => '<div class="form-item">',
        '#suffix' => '</div></div>',
        '#default_value' => (!empty($config->get("solr-facets-fields")[$i]['label'])) ? $config->get("solr-facets-fields")[$i]['label'] : ''
      ];
    }

    $form['container']['facets']['actions'] = [
      '#type' => 'actions',
    ];
    $form['container']['facets']['actions']['add_facets_field'] = [
      '#type' => 'submit',
      '#name' => 'add_facets_field',
      '#value' => $this->t('Add a field'),
      '#submit' => ['::addOneFacetsField'],
      '#ajax' => [
        'callback' => '::addFacetsFieldCallback',
        'wrapper' => 'facets-fields-wrapper',
      ],
    ];
    // If there is more than one name, add the remove button.
    if ($num_facets_fields > 1) {
      $form['container']['facets']['actions']['remove_facets_field'] = [
        '#type' => 'submit',
        '#name' => 'remove_facets_field',
        '#value' => $this->t('Remove'),
        '#submit' => ['::removeFacetsFieldCallback'],
        '#ajax' => [
          'callback' => '::addFacetsFieldCallback',
          'wrapper' => 'facets-fields-wrapper',
        ],
      ];
    }


////////////////////////////////////////////////////////////////
    $num_searchresults_fields = $form_state->get('num_searchresults_fields');
    if ($num_searchresults_fields === NULL) {
      if ($config->get("solr-facets-fields") !== null && count($config->get("solr-results-html"))  > 0 ) {
        $num_searchresults_fields = count($config->get("solr-results-html"));
        $name_field = $form_state->set('num_searchresults_fields', $num_searchresults_fields);
      }
      else {
        $name_field = $form_state->set('num_searchresults_fields', 1);
        $num_searchresults_fields = 2;
      }

    }
    $form['container']['search-results'] = array(
      '#type' => 'details',
      '#title' => 'Search Results',
      '#open' => true,
      '#prefix' => '<div id="search-results-fields-wrapper">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    );
    for ($i = 0; $i < $num_searchresults_fields; $i++) {
        if ($i == 0) {
            $field_label = 'Title Field';
        }
        else if ($i == 1) {
            $field_label = 'Thumbnail Field';
        }
        else {
            $field_label = 'Field Name #'. ($i+ 1);
        }
      $form['container']['search-results']['results-field-name-'.$i] = [
        '#type' => 'textfield',
        '#title' => $this->t($field_label),
        '#prefix' => '<div class="form--inline clearfix"><div class="form-item">',
        '#suffix' => '</div>',
        '#default_value' => !empty($config->get("solr-results-html")[$i]['fname']) ? $config->get("solr-results-html")[$i]['fname'] : ''
      ];
      $form['container']['search-results']['results-field-label-'.$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Field Label #' . ($i +1)),
        '#description' => $this->t('Leave it empty to hide the label'),
        '#prefix' => '<div class="form-item">',
        '#suffix' => '</div></div>',
        '#default_value' => (!empty($config->get("solr-results-html")[$i]['label'])) ? $config->get("solr-results-html")[$i]['label']: ''
      ];
    }

    $form['container']['search-results']['actions'] = [
      '#type' => 'actions',
    ];
    $form['container']['search-results']['actions']['add_search-results-field'] = [
      '#type' => 'submit',
      '#name' => 'add_search-results-field',
      '#value' => $this->t('Add a field'),
      '#submit' => ['::addOneResultsField'],
      '#ajax' => [
        'callback' => '::addSearchResultsFieldCallback',
        'wrapper' => 'search-results-fields-wrapper',
      ],
    ];

    if ($num_searchresults_fields > 1) {
      $form['container']['search-results']['actions']['remove_search-results-field'] = [
        '#type' => 'submit',
        '#name' => 'remove_search-results-field',
        '#value' => $this->t('Remove'),
        '#submit' => ['::removeResultsCallback'],
        '#ajax' => [
          'callback' => '::addSearchResultsFieldCallback',
          'wrapper' => 'search-results-fields-wrapper',
        ],
      ];
    }





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
    $configFactory->set("solr-proxy-enabled", $form_state->getValues()['enable-proxy']);

    $configFactory->set("solr-searchable-fields", $form_state->getValues()['searchable-fields']);
    //$configFactory->set("solr-facets-fields", $form_state->getValues()['facets-fields']);
    //$configFactory->set("solr-results-html", $form_state->getValues()['results-html']);



    $facets_fields = [];
    for ($i = 0; $i < $form_state->get('num_facets_fields'); $i++) {
      array_push($facets_fields,
        [
          "fname" => $form_state->getValues()['facets']['facets-field-name-'.$i],
          'label' =>  $form_state->getValues()['facets']['facets-field-label-'.$i]
        ]);
    }

    $configFactory->set("solr-facets-fields", $facets_fields);

    $results_fields = [];
    for ($i = 0; $i < $form_state->get('num_searchresults_fields'); $i++) {
      array_push($results_fields,
        [
          "fname" => $form_state->getValues()['search-results']['results-field-name-'.$i],
          'label' =>  $form_state->getValues()['search-results']['results-field-label-'.$i]
        ]);
    }
    $configFactory->set("solr-results-html", $results_fields);
    $configFactory->save();
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addFacetsFieldCallback(array &$form, FormStateInterface $form_state) {
    return $form['container']['facets'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOneFacetsField(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_facets_fields');
    $add_button = $name_field + 1;
    $form_state->set('num_facets_fields', $add_button);
    // Since our buildForm() method relies on the value of 'num_facets_fields' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove one" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removeFacetsFieldCallback(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_facets_fields');
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set('num_facets_fields', $remove_button);
    }
    // Since our buildForm() method relies on the value of 'num_facets_fields' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }


/////////////////////////////////////////////
  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addSearchResultsFieldCallback(array &$form, FormStateInterface $form_state) {
    return $form['container']['search-results'] ;
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOneResultsField(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_searchresults_fields');
    $add_button = $name_field + 1;
    $form_state->set('num_searchresults_fields', $add_button);
    // Since our buildForm() method relies on the value of 'num_searchresults_fields' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove one" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removeResultsCallback(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_searchresults_fields');
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set('num_searchresults_fields', $remove_button);
    }
    // Since our buildForm() method relies on the value of 'num_searchresults_fields' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }

}
