		 
(function ($) {

  // Behaviors related to Snowpit Profile Forms
  Drupal.behaviors.snowpilot3 = {

	attach: function (context, settings) {
		
		$( '.form-field-type-field-collection table.field-multiple-table > tbody').accordion({
 			 header: "h3",
 			 collapsible: true,
 			 active: false,
			 heightStyle: "content"
 		});
		 
		$('.horizontal-tabs-panes').once('accordion_ajax_toggle', function() {
			$( '.form-field-type-field-collection table.field-multiple-table > tbody').accordion('option', 'active', -1);
			$(document).ajaxComplete(function() {
				$( '.form-field-type-field-collection table.field-multiple-table > tbody').accordion('option', 'active', -1);
			});
		});
		
	} // end attach 
  } // end Drupal.behaviors.snowpilot3
}) (jQuery);