(function ($) {
  AjaxSolr.SortSelectWidget = AjaxSolr.AbstractWidget.extend({
    formatOption: function (value, label) {
      return '<option value="' + value + '">' + label + "</option>";
    },

    template: function (id, criteria) {
      var options = ['<option value="" hidden>Sort By</option>'];
      
      // creates options for sorting field in ascending and descending order
      // NOTE: when sorting in ascending order, entries missing the field will be displayed first
      //       this can be changed by modifying the solr schema file
      //       see: https://www.drupalconnect.com/blog/articles/how-control-sorting-nodes-missing-values-sort-field-apache-solr
      for (var i = 0; i < criteria.length; i++) {
        var asc = criteria[i]["fname"] + " asc";
        var desc = criteria[i]["fname"] + " desc";
        var label = criteria[i]["label"];

        if (label === "Relevant") {
          options.push(
            this.formatOption(asc, "Least " + label),
            this.formatOption(desc, "Most " + label)
          );
        }
        else if (label === "Title") {
          options.push(
            this.formatOption(asc, label + " (A-Z)"),
            this.formatOption(desc, label + " (Z-A)")
          );
        }
        else if (label === "Date Created") {
          options.push(
            this.formatOption(asc, "Oldest"),
            this.formatOption(desc, "Newest")
          );
        }
        else {
          options.push(
            this.formatOption(asc, label + " ↑"),
            this.formatOption(desc, label + " ↓")
          );
        }
      }

      return ('<select id="' + id + '" name="' + id + '">' + options.join("\n") + "</select>");
    },

    updateSortQuery: function (value) {
      this.manager.store.remove("sort");
      this.manager.store.addByValue("sort", value);
      console.log("request made")
      this.doRequest();
    },

    afterRequest: function () {
      var self = this;

      $(this.target).empty();
      $(this.target).append(self.template("sort-select", self.sort_criteria));

      // existing sort criteria selection -> update the select element text
      if (self.manager.store.values("sort").length > 0) {
        $(this.target)
          .find("#sort-select")
          .val(self.manager.store.values("sort")[0]);
      }

      $(this.target)
        .find("#sort-select")
        .change(function () {
          self.updateSortQuery($(this).val());
        });
    },
  });
})(jQuery);
