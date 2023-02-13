(function ($) {
  AjaxSolr.SortSelectWidget = AjaxSolr.AbstractWidget.extend({

    template: function (id, criteria) {
      var options = [];
      for (var i = 0; i < criteria.length; i++) {
        options.push('<option value="' + criteria[i]['fname'] +'">' + criteria[i]['label'] + '</option>');
      }
      return '<select id="' + id + '" name="' + id + '">' + options.join('\n') + '</select>';
    },

    afterRequest: function () {
      var self = this;
  
      $(this.target).empty();
      $(this.target).append(this.template('sort-select', self.sort_criteria));
    
      $(this.target).find('#sort-select').change(function () {
        var value = $(this).val();

      });
    }
    
  });
})(jQuery);
