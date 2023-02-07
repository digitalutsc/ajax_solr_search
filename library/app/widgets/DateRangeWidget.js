(function ($) {
  AjaxSolr.DateRangeWidget = AjaxSolr.AbstractFacetWidget.extend({
    afterRequest: function () {
      var self = this;
      $(this.target);
    },
  });
})(jQuery);
