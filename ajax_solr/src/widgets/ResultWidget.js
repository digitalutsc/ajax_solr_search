(function ($) {

  AjaxSolr.ResultWidget = AjaxSolr.AbstractWidget.extend({
    start: 0,

    beforeRequest: function () {
      $(this.target).html($('<img>').attr('src', '../images/ajax-loader.gif'));
    },

    facetLinks: function (facet_field, facet_values) {
      var links = [];
      if (facet_values) {
        for (var i = 0, l = facet_values.length; i < l; i++) {
          if (facet_values[i] !== undefined) {
            links.push(
              $('<a href="#"></a>')
                .text(facet_values[i])
                .click(this.facetHandler(facet_field, facet_values[i]))
            );
          } else {
            links.push('no items found in current selection');
          }
        }
      }
      return links;
    },

    facetHandler: function (facet_field, facet_value) {
      var self = this;
      return function () {
        self.manager.store.remove('fq');
        self.manager.store.addByValue('fq', facet_field + ':' + AjaxSolr.Parameter.escapeValue(facet_value));
        self.doRequest(0);
        return false;
      };
    },

    afterRequest: function () {
      $(this.target).empty();
      for (var i = 0, l = this.manager.response.response.docs.length; i < l; i++) {
        var doc = this.manager.response.response.docs[i];
        $(this.target).append(this.template(doc));

        var items = [];
        items = items.concat(this.facetLinks('site', doc.site));
        //items = items.concat(this.facetLinks('organisations', doc.organisations));
        //items = items.concat(this.facetLinks('exchanges', doc.exchanges));

        /*var $links = $('#links_' + doc.id);
        $links.empty();
        for (var j = 0, m = items.length; j < m; j++) {
          $links.append($('<li></li>').append(items[j]));
        }*/
      }
    },

    template: function (doc) {
      /*var output = '<div><a href="' + doc.sm_urls + '" target="_blank"><h2>' + doc.ss_federated_title + '</h2></a>';
      if (doc.content)
        output += '<p>' + doc.content.substring(0, 350) + '...</p>';
      output += '<p><strong>Date created:</strong> ' + doc.ds_federated_date + '<br /><strong>Site:</strong> ' + doc.site + '<br /><strong>Content type:</strong> ' + doc.ss_federated_type + '</p></div><hr/>';*/
      var output = eval(this.result_html);
      return output;
    },

    init: function () {

      $(document).on('click', 'a.more', function () {
        var $this = $(this),
          span = $this.parent().find('span');

        if (span.is(':visible')) {
          span.hide();
          $this.text('more');
        } else {
          span.show();
          $this.text('less');
        }

        return false;
      });
    }
  });

})(jQuery);
