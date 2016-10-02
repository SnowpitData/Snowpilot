		 
(function ($) {

		   // Behaviors related to Snowpit Profile Forms
	Drupal.behaviors.snowpilot3 = {

	attach: function (context, settings) {
 		 $('.form-field-type-field-collection table#field-layer-values > tbody').accordion({
 			 header: "h3",
 			 collapsible: true,
 			 active: false,
			 heightStyle: "content"
 		 });
		 
     
     $('.horizontal-tabs-panes').once('accordion_ajax_toggle', function() {
         $( '.form-field-type-field-collection table#field-layer-values > tbody').accordion('option', 'active', ":last");
       $(document).ajaxComplete(function() {
         $( '.form-field-type-field-collection table#field-layer-values > tbody').accordion('option', 'active', ":last");
       });
     });
	
	}
	}
}) (jQuery);