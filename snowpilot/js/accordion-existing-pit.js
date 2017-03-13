		 
(function ($) {

  // Behaviors related to Snowpit Profile Forms
  Drupal.behaviors.snowpilot3 = {

	attach: function (context, settings) {
		
		var tableSelector = "table[id^='field-layer-values'] > tbody";
		
 		//$('.form-field-type-field-collection table#field-layer-values > tbody').accordion({
		$(tableSelector).accordion({
 			 header: "h3",
 			 collapsible: true,
 			 active: false,
			 heightStyle: "content"
 		});
		 
		$('.horizontal-tabs-panes').once('accordion_ajax_toggle', function() {
			//$( '.form-field-type-field-collection table#field-layer-values > tbody').accordion('option', 'active', -1);
			$(tableSelector).accordion('option', 'active', -1);
			
			$(document).ajaxComplete(function() {
				 //$( '.form-field-type-field-collection table#field-layer-values > tbody').accordion('option', 'active', -1);
				 $(tableSelector).accordion('option', 'active', -1);
			});
			
		});	
		
	} // end attach 
  } // end Drupal.behaviors.snowpilot3
}) (jQuery);