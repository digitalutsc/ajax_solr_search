<?php

namespace Drupal\ajax_solr_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Class AjaxSolrSearchConfigForm.
 */
class AjaxSolrSearchConfigForm extends ConfigFormBase {

  const OUTPUT_TEMPLATE = '<div class="node">
      <div class="thumb"><img src="{{ thumbnail }}" alt="thumbnail"/></div>
      <div class="others">
        <p><h2><a href="{{ url }}" target="_blank">{{ title }}</a></h2></p>
        <p>{{ description }}</p>
        {{ others }}
      </div>
    </div>
    <hr />
   ';

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
    $form = parent::buildForm($form, $form_state);

    $form['container'] = [
      '#type' => 'container',
    ];

    $form['container']['solr-config'] = [
      '#type' => 'details',
      '#title' => 'Solr Settings',
      '#open' => TRUE,
    ];

    $form['container']['solr-config']['server-url'] = [
      '#type' => 'textfield',
      '#name' => 'server-url',
      '#title' => $this
        ->t('Solr Endpoint URL:'),
      '#default_value' => ($config->get("solr-server-url") !== NULL) ? $config->get("solr-server-url") : "",
      '#description' => $this->t('For example: <code>http://localhost:8983/solr/multisite</code>'),
    ];

    $form['container']['solr-config']['enable-proxy'] = [
      '#type' => 'checkbox',
      '#title' => $this
        ->t('Enable Proxy (if the above Solr endpoint is behind VPN or firewall)'),
      '#default_value' => ($config->get("solr-proxy-enabled") !== NULL) ? $config->get("solr-proxy-enabled") : 0,
    ];

    if (!empty($config->get("solr-server-url"))) {
      if ($config->get("solr-proxy-enabled") == 1) {
        global $base_url;
        $mappedFields = $this->getMappedFieldsOptions($this->requestMappedFieldsFromSolr("$base_url/solr/multisite"));
      }
      else {
        $mappedFields = $this->getMappedFieldsOptions($this->requestMappedFieldsFromSolr($config->get("solr-server-url")));
      }

      // Only return $form if the Solr URL is active.
      if ($mappedFields === FALSE) {
        \Drupal::messenger()->addMessage("Unable to connect with Solr Endpoint. Please check the SORL Endpoint URL again.", MessengerInterface::TYPE_ERROR);
        return $form;
      }

      $form['container']['sarch-results-page'] = [
        '#type' => 'details',
        '#title' => 'Search Results Page',
        '#open' => TRUE,
      ];

      $form['container']['sarch-results-page']['sarch-results-page-path'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Search results page path'),
        '#default_value' => (!empty($config->get('search-results-path')) ? $config->get('search-results-path') : '/federated-search'),
        '#description' => $this
          ->t('Default: "/federated-search"'),
      ];

      $num_searchable_fields = $form_state->get('num_searchable_fields');
      if ($num_searchable_fields === NULL) {
        if ($config->get("solr-searchable-fields") !== NULL && count($config->get("solr-searchable-fields")) > 0) {
          $num_searchable_fields = count($config->get("solr-searchable-fields"));
          $form_state->set('num_searchable_fields', $num_searchable_fields);
        }
        else {
          $form_state->set('num_searchable_fields', 1);
          $num_searchable_fields = 1;
        }
      }

      $form['container']['searchable'] = [
        '#type' => 'details',
        '#title' => 'Searchable Fields',
        '#open' => TRUE,
        '#tree' => TRUE,
        '#prefix' => '<div id="searchable-fields-wrapper">',
        '#suffix' => '</div>',
      ];
      for ($i = 0; $i < $num_searchable_fields; $i++) {
        $form['container']['searchable']['searchable-fields-' . $i] = [
          '#type' => 'select',
          '#title' => $this->t('Select Solr Search field #' . ($i + 1) . ':'),
          '#options' => $mappedFields,
          '#default_value' => (!empty($config->get("solr-searchable-fields")[$i])) ? $config->get("solr-searchable-fields")[$i] : '',
        ];
      }
      $form['container']['searchable']['actions'] = [
        '#type' => 'actions',
      ];
      $form['container']['searchable']['actions']['add_searchable_field'] = [
        '#type' => 'submit',
        '#name' => 'add_searchable_field',
        '#value' => $this->t('Add a field'),
        '#submit' => ['::addOneSearchField'],
        '#ajax' => [
          'callback' => '::addSearchFieldCallback',
          'wrapper' => 'searchable-fields-wrapper',
        ],
      ];
      // If there is more than one name, add the remove button.
      if ($num_searchable_fields > 1) {
        $form['container']['searchable']['actions']['remove_searchable_field'] = [
          '#type' => 'submit',
          '#name' => 'remove_searchable_field',
          '#value' => $this->t('Remove'),
          '#submit' => ['::removeSearchFieldCallback'],
          '#ajax' => [
            'callback' => '::addSearchFieldCallback',
            'wrapper' => 'searchable-fields-wrapper',
          ],
        ];
      }

      // Gather the number of names in the form already.
      $num_facets_fields = $form_state->get('num_facets_fields');
      // We have to ensure that there is at least one name field.
      if ($num_facets_fields === NULL) {
        if ($config->get("solr-facets-fields") !== NULL && count($config->get("solr-facets-fields")) > 0) {
          $num_facets_fields = count($config->get("solr-facets-fields"));
          $form_state->set('num_facets_fields', $num_facets_fields);
        }
        else {
          $form_state->set('num_facets_fields', 1);
          $num_facets_fields = 1;
        }
      }
      $form['container']['facets'] = [
        '#type' => 'details',
        '#title' => 'Facets',
        '#open' => TRUE,
        '#prefix' => '<div id="facets-fields-wrapper">',
        '#suffix' => '</div>',
        '#tree' => TRUE,
      ];

      for ($i = 0; $i < $num_facets_fields; $i++) {
        $form['container']['facets']['facets-field-name-' . $i] = [
          '#type' => 'select',
          '#options' => $mappedFields,
          '#title' => $this->t('Solr Field Name #' . ($i + 1)),
          '#prefix' => '<div class="form--inline clearfix"><div class="form-item">',
          '#suffix' => '</div>',
          '#default_value' => (!empty($config->get("solr-facets-fields")[$i]['fname'])) ? $config->get("solr-facets-fields")[$i]['fname'] : '',
        ];
        $form['container']['facets']['facets-field-label-' . $i] = [
          '#type' => 'textfield',
          '#title' => $this->t('Solr Field Label #' . ($i + 1)),
          '#description' => $this->t('Leave it empty to hide the label'),
          '#prefix' => '<div class="form-item">',
          '#suffix' => '</div></div>',
          '#default_value' => (!empty($config->get("solr-facets-fields")[$i]['label'])) ? $config->get("solr-facets-fields")[$i]['label'] : '',
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

      $num_searchresults_fields = $form_state->get('num_searchresults_fields');
      if ($num_searchresults_fields === NULL) {
        if ($config->get("solr-facets-fields") !== NULL && count($config->get("solr-results-html")) > 0) {
          $num_searchresults_fields = count($config->get("solr-results-html"));
          if ($num_searchresults_fields < 4) {
            $num_searchresults_fields = 4;
          }
          $name_field = $form_state->set('num_searchresults_fields', $num_searchresults_fields);
        }
        else {
          // First time loaded.
          $name_field = $form_state->set('num_searchresults_fields', 1);
          $num_searchresults_fields = 4;
        }

      }
      $form['container']['search-results'] = [
        '#type' => 'details',
        '#title' => 'Search Results',
        '#open' => TRUE,
        '#prefix' => '<div id="search-results-fields-wrapper">',
        '#suffix' => '</div>',
        '#tree' => TRUE,
      ];

      for ($i = 0; $i < $num_searchresults_fields; $i++) {
        // Mandatory fields: title, thumbnail, and description.
        if ($i == 0) {
          $field_label = 'Thumbnail Field (mandatory)';
          $require = TRUE;
        }
        elseif ($i == 1) {
          $field_label = 'Title Field (mandatory)';
          $require = TRUE;
        }
        elseif ($i == 2) {
          $field_label = 'Description Field (mandatory)';
          $require = TRUE;
        }
        elseif ($i == 3) {
          $field_label = 'URL Field (mandatory)';
          $require = TRUE;
        }
        else {
          $field_label = 'Solr Field Name #' . ($i + 1) . " (optional)";
          $require = FALSE;
        }
        $form['container']['search-results']['results-field-name-' . $i] = [
          '#type' => 'select',
          '#options' => $mappedFields,
          '#title' => $this->t($field_label),
          '#prefix' => '<div class="form--inline clearfix"><div class="form-item">',
          '#suffix' => '</div>',
          '#default_value' => !empty($config->get("solr-results-html")[$i]['fname']) ? $config->get("solr-results-html")[$i]['fname'] : '',
          '#required' => $require,
          '#description' => !empty($config->get("solr-results-html")[$i]['fname']) ? "Token: {{ " . $config->get("solr-results-html")[$i]['fname'] . " }}" : '',
        ];
        $form['container']['search-results']['results-field-label-' . $i] = [
          '#type' => 'textfield',
          '#title' => $this->t('Solr Field Label #' . ($i + 1)),
          '#description' => $this->t('Leave it empty to hide the label'),
          '#prefix' => '<div class="form-item">',
          '#suffix' => '</div></div>',
          '#default_value' => (!empty($config->get("solr-results-html")[$i]['label'])) ? $config->get("solr-results-html")[$i]['label'] : '',
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

      if ($num_searchresults_fields > 4) {
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

      // Override.
      $form['container']['search-results']['textfields_container'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'textfields-container'],
        '#weight' => 101,
      ];
      $form['container']['search-results']['textfields_container']['rewrite-search-results-output'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Override Search Results Template'),
        '#default_value' => ($config->get("rewrite-search-results-output") !== NULL) ? $config->get("rewrite-search-results-output") : 0,
        '#ajax' => [
          'callback' => '::textfieldsCallback',
          'wrapper' => 'textfields-container',
          'effect' => 'fade',
        ],
      ];

      if (isset($form_state->getValues()['search-results']['textfields_container']['rewrite-search-results-output'])) {
        $override_output = $form_state->getValues()['search-results']['textfields_container']['rewrite-search-results-output'];
      }
      $override_config = $config->get("rewrite-search-results-output");

      if ((!empty($override_output) && $override_output == 1) || (!empty($override_config) && $override_config == 1)) {
        $form['container']['search-results']['textfields_container']['rewrite-template'] = [
          '#type' => 'textarea',
          '#title' => $this->t('Override with HTML Template:'),
          '#required' => TRUE,
          '#default_value' => ($config->get("output-template") !== NULL) ? $config->get("output-template") : self::OUTPUT_TEMPLATE,
        ];
      }
      else {
        $form['container']['search-results']['output'] = [
          '#type' => 'details',
          '#title' => 'Default Results Ouput',
          '#weight' => 100,
        ];
        $form['container']['search-results']['output']['template'] = [
          '#type' => 'textarea',
          '#attributes' => ['readonly' => 'readonly'],
          "#default_value" => ($config->get("output-template") !== NULL) ? $config->get("output-template") : self::OUTPUT_TEMPLATE,
        ];
      }

    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // $this->config('ajax_solr_search.ajaxsolrsearchconfig')->save();
    $configFactory = $this->configFactory->getEditable('ajax_solr_search.ajaxsolrsearchconfig');

    $configFactory->set("solr-server-url", $form_state->getValues()['server-url']);
    $configFactory->set("solr-proxy-enabled", $form_state->getValues()['enable-proxy']);

    $configFactory->set("search-results-path", $form_state->getValues()['sarch-results-page-path']);

    $searchable_fields = [];
    for ($i = 0; $i < $form_state->get('num_searchable_fields'); $i++) {
      array_push($searchable_fields, $form_state->getValues()['searchable']['searchable-fields-' . $i]);
    }
    $configFactory->set("solr-searchable-fields", $searchable_fields);

    // $configFactory->set("solr-facets-fields", $form_state->getValues()['facets-fields']);
    // $configFactory->set("solr-results-html", $form_state->getValues()['results-html']);
    $facets_fields = [];
    for ($i = 0; $i < $form_state->get('num_facets_fields'); $i++) {
      array_push($facets_fields,
        [
          "fname" => $form_state->getValues()['facets']['facets-field-name-' . $i],
          'label' => $form_state->getValues()['facets']['facets-field-label-' . $i],
        ]);
    }

    $configFactory->set("solr-facets-fields", $facets_fields);

    if (isset($form_state->getValues()['container']['search-results']['actions']['textfields_container']['rewrite-template'])) {
      $template = $form_state->getValues()['container']['search-results']['actions']['textfields_container']['rewrite-template'];
    }
    $template = (isset($template) && !empty($template)) ? $template : self::OUTPUT_TEMPLATE;
    $results_fields = [];
    $others = "";
    for ($i = 0; $i < $form_state->get('num_searchresults_fields'); $i++) {
      array_push($results_fields,
        [
          "fname" => $form_state->getValues()['search-results']['results-field-name-' . $i],
          'label' => $form_state->getValues()['search-results']['results-field-label-' . $i],
        ]);

      // Replace token with field name.
      if ($i == 0) {
        // For thumbnail.
        $template = str_replace("{{ thumbnail }}", "{{ " . $form_state->getValues()['search-results']['results-field-name-' . $i] . " }}", $template);
      }
      elseif ($i == 1) {
        // For title.
        $template = str_replace("{{ title }}", "{{ " . $form_state->getValues()['search-results']['results-field-name-' . $i] . " }}", $template);
      }
      elseif ($i == 2) {
        // For url.
        $template = str_replace("{{ description }}", "{{ " . $form_state->getValues()['search-results']['results-field-name-' . $i] . " }}", $template);
      }
      elseif ($i == 3) {
        // For description.
        $template = str_replace("{{ url }}", "{{ " . $form_state->getValues()['search-results']['results-field-name-' . $i] . " }}", $template);
      }
      else {
        // For others.
        $others .= "<p>";
        $others .= "<strong>" . "{{ " . $form_state->getValues()['search-results']['results-field-label-' . $i] . " }}" . ": </strong>" . "{{ " . $form_state->getValues()['search-results']['results-field-name-' . $i] . " }}";
        $others .= "</p>";
      }
    }
    $template = str_replace("{{ others }}", $others, $template);
    $configFactory->set("output-template", $template);
    $configFactory->set("solr-results-html", $results_fields);

    // If override mode is enabled, save the custom template instead.
    $configFactory->set("rewrite-search-results-output", $form_state->getValues()['search-results']['textfields_container']['rewrite-search-results-output']);
    if ($form_state->getValues()['search-results']['textfields_container']['rewrite-search-results-output'] === 1) {
      $configFactory->set("output-template", $form_state->getValues()['search-results']['textfields_container']['rewrite-template']);
    }

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

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addSearchResultsFieldCallback(array &$form, FormStateInterface $form_state) {
    return $form['container']['search-results'];
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

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addSearchFieldCallback(array &$form, FormStateInterface $form_state) {
    return $form['container']['searchable'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOneSearchField(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_searchable_fields');
    $add_button = $name_field + 1;
    $form_state->set('num_searchable_fields', $add_button);
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
  public function removeSearchFieldCallback(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_searchable_fields');
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set('num_searchable_fields', $remove_button);
    }
    // Since our buildForm() method relies on the value of 'num_facets_fields' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }

  /**
   * Get Indexed fields from Solr.
   */
  public function requestMappedFieldsFromSolr(string $solr_url) {
    $curl = curl_init();
    global $base_url;

    if (strpos($solr_url, $base_url) !== FALSE) {
      $solr_url = $solr_url . '/fields?q=*:*&wt=csv&rows=0&facet';
    }
    else {
      $solr_url = $solr_url . '/select?q=*:*&wt=csv&rows=0&facet';
    }

    curl_setopt_array($curl, [
      CURLOPT_URL => $solr_url,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => 'q=*:*&wt=csv&rows=0&facet',
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
  }

  /**
   * Convert rest response to options array.
   */
  public function getMappedFieldsOptions(string $response) {
    if (stripos($response, "HTTP ERROR") !== FALSE) {
      return FALSE;
    }

    $response = str_replace('"', "", $response);
    $response = str_replace('\n', "", $response);
    $fields = explode(",", $response);
    sort($fields);
    $options = [-1 => '--- Select a field ---'];
    foreach ($fields as $field) {
      if (!empty($field) && strlen(trim($field)) !== 0) {
        $options[$field] = $field;
      }
    }
    return $options;
  }

  /**
   * Callback for ajax_example_autotextfields.
   *
   * Selects the piece of the form we want to use as replacement markup and
   * returns it as a form (renderable array).
   */
  public function textfieldsCallback($form, FormStateInterface $form_state) {
    return $form['container']['search-results']['textfields_container'];
  }

}
