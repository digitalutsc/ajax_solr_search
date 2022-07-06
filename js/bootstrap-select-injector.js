(function ($, Drupal) {
    Drupal.behaviors.ajax_solr_search_admin_behavior = {
        attach: function (context, settings) {
			console.log("bootstrap-select injected");
			$(".form-select").selectpicker();
        }
    }
})(jQuery, Drupal);
