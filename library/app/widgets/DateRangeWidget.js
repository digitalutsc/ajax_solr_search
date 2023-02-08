(function ($) {
  AjaxSolr.DateRangeWidget = AjaxSolr.AbstractFacetWidget.extend({
    afterRequest: function () {
      var self = this;

      $(this.target).on("click", "button", function () {
        // get date range input
        var start_date = $($(self.target).find("input")[0]).val();
        var end_date = $($(self.target).find("input")[1]).val();

        // if no end date given, use start date
        if (!end_date) {
          end_date = start_date;
          $($(self.target).find("input")[1]).val(end_date);
        }

        // query parameters
        var min = start_date ? start_date : "*";
        var max = end_date ? end_date : "*";
        var query = "[" + min + " TO " + max + "]";

        self.clear(); // clear existing filter query for date range
        if (self.add(query)) {
          self.doRequest();
        }
      });
    },
  });
})(jQuery);
