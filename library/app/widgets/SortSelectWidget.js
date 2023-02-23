(function ($) {
  AjaxSolr.SortSelectWidget = AjaxSolr.AbstractWidget.extend({
    formatOption: function (value, label) {
      return '<option value="' + value + '">' + label + "</option>";
    },

    template: function (id, criteria) {

      if (criteria.length == 0) {
        return;
      }
      
      var options = [];
      
      // creates options for sorting field in ascending and descending order
      // NOTE: when sorting in ascending order, entries missing the field will be displayed first
      //       this can be changed by modifying the solr schema file
      //       see: https://www.drupalconnect.com/blog/articles/how-control-sorting-nodes-missing-values-sort-field-apache-solr
      for (var i = 0; i < criteria.length; i++) {
        var asc = criteria[i]["fname"] + " asc";
        var desc = criteria[i]["fname"] + " desc";
        var label = criteria[i]["label"];

        if (label === "Relevant" || label === "Relevance") {
          options.push(
            this.formatOption(desc, "Most Relevant"),
            // this.formatOption(asc, "Least Relevant")
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
            this.formatOption(desc, "Newest"),
            this.formatOption(asc, "Oldest")
          );
        }
        else {
          options.push(
            this.formatOption(desc, label + " ↓"),
            this.formatOption(asc, label + " ↑")
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
      $(this.target).append(self.template("select", self.sort_criteria));

      // existing sort criteria selection -> update the select element text
      if (self.manager.store.values("sort").length > 0) {
        $(this.target).find("select").val(self.manager.store.values("sort")[0]);
      } else {
        self.updateSortQuery($(this.target).find("select").val());
      }

      $(this.target)
        .find("select")
        .change(function () {
          self.updateSortQuery($(this).val());
        });
    },
  });
})(jQuery);
