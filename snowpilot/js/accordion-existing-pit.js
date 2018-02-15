		 
(function ($) {

  // Behaviors related to Snowpit Profile Forms
  Drupal.behaviors.snowpilot3 = {

	attach: function (context, settings) {
		// Activate accordian functionality on all 4 tables
		$( '#edit-field-layer table.field-multiple-table > tbody').accordion({
 			 header: "h3",
 			 collapsible: true,
 			 active: false,
			 heightStyle: "content"
 		});
    
    $( '#edit-field-test table.field-multiple-table > tbody').accordion({
 			 header: "h3",
 			 collapsible: true,
 			 active: false,
			 heightStyle: "content"
 		});
    
    /*
    $( '#edit-field-temp-collection table.field-multiple-table > tbody').accordion({
 			 header: "h3",
 			 collapsible: true,
 			 active: false,
			 heightStyle: "content"
 		});
    */
    
    /*
    $( '#edit-field-density-profile table.field-multiple-table > tbody').accordion({
 			 header: "h3",
 			 collapsible: true,
 			 active: false,
			 heightStyle: "content"
 		});
    */
		 
    // Set the final layer of each table as active, and attach ajax listener to keep it that way
		$('.horizontal-tabs-panes').once('accordion_ajax_toggle', function() {
			$( '#edit-field-layer table.field-multiple-table > tbody').accordion('option', 'active', -1);
      $( '#edit-field-test table.field-multiple-table > tbody').accordion('option', 'active', -1);
      //$( '#edit-field-temp-collection table.field-multiple-table > tbody').accordion('option', 'active', -1);
      //$( '#edit-field-density-profile table.field-multiple-table > tbody').accordion('option', 'active', -1);
      // AJAX listener
			$(document).ajaxComplete(function() {
				$( '#edit-field-layer table.field-multiple-table > tbody').accordion('option', 'active', -1);
        $( '#edit-field-test table.field-multiple-table > tbody').accordion('option', 'active', -1);
        //$( '#edit-field-temp-collection table.field-multiple-table > tbody').accordion('option', 'active', -1);
        //$( '#edit-field-density-profile table.field-multiple-table > tbody').accordion('option', 'active', -1);
			});
		});
		
	} // end attach 
  } // end Drupal.behaviors.snowpilot3
}) (jQuery);