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

      var output = '<div>';
      for (var i = 0; i < this.result_html.length; i++ ) {
        if (doc[this.result_html[i].fname] !== undefined) {
          // process value
          var value = replaceURLs(doc[this.result_html[i].fname]);
          if (i == 0) {
            value = "<h2>" + replaceURLs(doc[this.result_html[i].fname]) + "</h2>";
          }
          if (doc[this.result_html[i].fname].length > 280) {
            value = doc[this.result_html[i].fname].substring(0, 280) + " ...";
          }

          if (this.result_html[i].label) {
            output += "<p><strong>"+this.result_html[i].label+"</strong>: " + value + "</p>";
          }
          else {
            output += "<p>" + value + "</p>";
          }
        }
      }
      output += '<hr /></div>';
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

  /**
   * https://www.cluemediator.com/find-urls-in-string-and-make-a-link-using-javascript
   * @param message
   * @returns {*}
   */
  function replaceURLs(message) {
    if(!message) return;

    var urlRegex = /(((https?:\/\/)|(www\.))[^\s]+)/g;
    return message.toString().replace(urlRegex, function (url) {
      var hyperlink = url;
      if (!hyperlink.match('^https?:\/\/')) {
        hyperlink = 'http://' + hyperlink;
      }
      return '<a href="' + hyperlink + '" target="_blank" rel="noopener noreferrer">' + url + '</a>'
    });
  }

})(jQuery);
