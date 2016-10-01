		 
(function ($) {

		   // Behaviors related to Snowpit Profile Forms
	Drupal.behaviors.snowpilot3 = {

	attach: function (context, settings) {
		

	  $('.form-field-type-field-collection table.field-multiple-table > tbody').accordion({
	 	 header: "h3",
	 	 collapsible: true,
	 	 active: false,
	 	 heightStyle: "fill"
	  });		
     
    $('.horizontal-tabs-panes').once('accordion_ajax_toggle', function() {
        $( '.form-field-type-field-collection table.field-multiple-table > tbody').accordion('option', 'active', ":last");
      $(document).ajaxComplete(function() {
        $( '.form-field-type-field-collection table.field-multiple-table > tbody').accordion('option', 'active', ":last");
      });
    });
	
  } // end attach 
	} // end behaviors 
}) (jQuery);