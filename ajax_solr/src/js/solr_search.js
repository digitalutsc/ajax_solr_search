

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.d8_scholarship_frontBehavior = {
    attach: function (context, settings) {
      //console.log(drupalSettings.ajax_solr_search);
      $(document).ready(function() {
          var Manager;
          Manager = new AjaxSolr.Manager({
            solrUrl: (drupalSettings.ajax_solr_search.solr_url.substr(-1) !== '/') ? (drupalSettings.ajax_solr_search.solr_url + '/') : drupalSettings.ajax_solr_search.solr_url
          });
          Manager.addWidget(new AjaxSolr.ResultWidget({
            id: 'result',
            target: '#docs',
            result_html: drupalSettings.ajax_solr_search.results_html
          }));
          Manager.addWidget(new AjaxSolr.PagerWidget({
            id: 'pager',
            target: '#pager',
            prevLabel: '&lt;',
            nextLabel: '&gt;',
            innerWindow: 1,
            renderHeader: function (perPage, offset, total) {
              $('#pager-header').html($('<span></span>').text('displaying ' + Math.min(total, offset + 1) + ' to ' + Math.min(total, offset + perPage) + ' of ' + total));
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
          var facets_fields = drupalSettings.ajax_solr_search.facets_fields.replace(/\s/g,'').split(',');
          for (var i = 0, l = facets_fields.length; i < l; i++) {
            Manager.addWidget(new AjaxSolr.MultiSelectWidget({ //MultiSelectWidget instead of Tagcloudwidget
              id: facets_fields[i],
              target: '#' + facets_fields[i],
              field: facets_fields[i],
              max_show: 10,
              max_facets: 20,
              sort_type: 'range' //possible values: 'range', 'lex', 'count'
            }));
          }
          Manager.init();
          Manager.store.addByValue('q', '*:*');
          var params = {
            facet: true,
            'facet.field': facets_fields,
            'facet.limit': 20,
            'facet.mincount': 1,
            'f.site.facet.limit': 50,
            'f.countryCodes.facet.limit': -1,
            'facet.date': 'date',
            'facet.date.start': '1987-02-26T00:00:00.000Z/DAY',
            'facet.date.end': '1987-10-20T00:00:00.000Z/DAY+1DAY',
            'facet.date.gap': '+1DAY',
            'json.nl': 'map'
          };
          for (var name in params) {
            Manager.store.addByValue(name, params[name]);
          }
          Manager.doRequest();

          $.fn.showIf = function (condition) {
            if (condition) {
              return this.show();
            } else {
              return this.hide();
            }
          }
      });
    }
  }
})(jQuery, Drupal, drupalSettings);





