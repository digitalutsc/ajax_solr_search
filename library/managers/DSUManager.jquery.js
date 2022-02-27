(function (callback) {
  if (typeof define === 'function' && define.amd) {
    define(['core/AbstractManager'], callback);
  } else {
    callback();
  }
}(function () {

  /**
   * @see http://wiki.apache.org/solr/SolJSON#JSON_specific_parameters
   * @class Manager
   * @augments AjaxSolr.AbstractManager
   */
  AjaxSolr.Manager = AjaxSolr.AbstractManager.extend(
    /** @lends AjaxSolr.Manager.prototype */
    {
      executeRequest: function (servlet, string, handler, errorHandler, disableJsonp) {
        var self = this,
          options = {dataType: 'json'};
        string = string || this.store.string();
        handler = handler || function (data) {
          self.handleResponse(data);
        };
        errorHandler = errorHandler || function (jqXHR, textStatus, errorThrown) {
          self.handleError(textStatus + ', ' + errorThrown);
        };

        if (!string.includes("q=*%3A*")) {
          string = "defType=dismax&" + string;
        }

        // add for set access control as sub query
        string = string.replace("access.control", "fq")


        if (this.proxyUrl) {
          options.url = this.proxyUrl + '/' + servlet;
          options.data = {query: string};
          options.type = 'POST';
        } else {
          disableJsonp = true;
          options.url = this.solrUrl + servlet + '?' + string + '&wt=json' + (disableJsonp ? '' : '&json.wrf=?');
        }
        jQuery.ajax(options).done(handler).fail(errorHandler);
      }
    });

}));
