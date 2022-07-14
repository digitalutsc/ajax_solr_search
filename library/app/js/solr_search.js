(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.d8_scholarship_frontBehavior = {
    attach: function (context, settings) {

      $(document).ready(function () {
          var Manager;
          Manager = new AjaxSolr.Manager({
            solrUrl: (drupalSettings.ajax_solr_search.solr_url.substr(-1) !== '/') ? (drupalSettings.ajax_solr_search.solr_url + '/') : drupalSettings.ajax_solr_search.solr_url,
            proxyUrl: drupalSettings.ajax_solr_search.proxy_url,
            outputTemplate: drupalSettings.ajax_solr_search.output_template,
          });
          Manager.addWidget(new AjaxSolr.ResultWidget({
            id: 'result',
            target: '#docs',
            highlighting: true,
            no_init_results: false,
            result_html: JSON.parse(drupalSettings.ajax_solr_search.results_html)
          }));
          Manager.addWidget(new AjaxSolr.PagerWidget({
            id: 'pager',
            target: '#pager',
            no_init_results: false,
            prevLabel: '&lt;',
            nextLabel: '&gt;',
            innerWindow: 1,
            renderHeader: function (perPage, offset, total) {
              $('#pager-header').html($('<p><strong></strong></p>').text('Displaying ' + Math.min(total, offset + 1) + ' - ' + Math.min(total, offset + perPage) + ' of ' + total));
            }
          }));

          // Search textfield
          Manager.addWidget(new AjaxSolr.CurrentSearchWidget({
            id: 'currentsearch',
            target: '#selection'
          }));

          var searchable_fields = drupalSettings.ajax_solr_search.searchable_fields.replace(/\s/g,'').split(',');
          Manager.addWidget(new AjaxSolr.AutocompleteWidget({
            id: 'text',
            target: '#search',
            fields: searchable_fields
          }));

          /* Facets */
          //var facets_fields = drupalSettings.ajax_solr_search.facets_fields.replace(/\s/g,'').split(',');
          var facets_fields = JSON.parse(drupalSettings.ajax_solr_search.facets_fields);
          var facets = [];
          for (var i = 0, l = facets_fields.length; i < l; i++) {
            Manager.addWidget(new AjaxSolr.MultiSelectWidget({ //MultiSelectWidget instead of Tagcloudwidget
              id: facets_fields[i].fname,
              target: '#' + facets_fields[i].fname,
              field: facets_fields[i].fname,
              max_show: 10,
              max_facets: 20,
              sort_type: 'count'
            }));
            facets.push(facets_fields[i].fname);
          }

          /* Condition */
          var condition_fields = JSON.parse(drupalSettings.ajax_solr_search.condition_fields);
          var condition = [];
          var fq='';
          for (var i = 0, l = condition_fields.length; i < l; i++) {
            if (condition_fields[i].op == '!null') {
              condition.push(condition_fields[i].fname + ":[* TO *]");
            }
            else if (condition_fields[i].op == 'null') {
              condition.push("-" + condition_fields[i].fname + ":[* TO *]");
            }
            else if (condition_fields[i].op == '!=') {
              condition.push("-" + condition_fields[i].fname + ':"' + condition_fields[i].label + '"');
            }
            else {
              condition.push(condition_fields[i].fname + ':"' + condition_fields[i].label + '"');
            }
          }


          Manager.init();
          Manager.store.addByValue('q', '*:*');


          var params = {
            facet: true,
            'qf' : searchable_fields.join(" "),
            'fq' : condition.join(" AND "),
            //'fq' : fq,
            'facet.field': facets,
            'facet.limit': 20,
            'facet.mincount': 1,
            'f.site.facet.limit': 50,
            'f.countryCodes.facet.limit': -1,
            'facet.date': 'date',
            'facet.date.start': '1987-02-26T00:00:00.000Z/DAY',
            'facet.date.end': '1987-10-20T00:00:00.000Z/DAY+1DAY',
            'facet.date.gap': '+1DAY',
            'json.nl': 'map',
            'hl': true,
            'hl.fl': 'tm_X3b_en_description',//searchable_fields.join(" "),
            'hl.snippets': 4,
            /*'hl.simple.pre': '<span style="background:#FFFF99">',
            'hl.simple.post': '</span>'*/
            //'access.control': drupalSettings.ajax_solr_search.access_control,
            'rows': 25
          };

          //console.log(drupalSettings.ajax_solr_search.access_control);
          /*if (drupalSettings.ajax_solr_search.access_control !== ':') {
            params['access.control'] = drupalSettings.ajax_solr_search.access_control;
          }*/
          console.log(params);
          for (var name in params) {
            Manager.store.addByValue(name, params[name]);
          }
          Manager.doRequest();
      });

      $.fn.showIf = function (condition) {
        if (condition) {
          return this.show();
        } else {
          return this.hide();
        }
      }

    }
  }
})(jQuery, Drupal, drupalSettings);
