(function ($) {
  AjaxSolr.YearRangeWidget = AjaxSolr.AbstractFacetWidget.extend({
    afterRequest: function () {
      var self = this;

      $(this.target)
        .find("button")
        .off()
        .on("click", function () {
          // get year range input
          var start_year = $($(self.target).find("input")[0]).val();
          var end_year = $($(self.target).find("input")[1]).val();

          // if no end year or start year is after end year
          if (!end_year || end_year < start_year) {
            end_year = start_year;
            $($(self.target).find("input")[1]).val(end_year);
          }

          // query parameters
          var min = start_year ? start_year : "*";
          var max = end_year ? end_year : "*";
          var query = "[" + min + " TO " + max + "]";

          self.clear(); // clear existing filter query for year range
          if (self.add(query)) {
            self.doRequest();
          }
        });
    },
  });
})(jQuery);
