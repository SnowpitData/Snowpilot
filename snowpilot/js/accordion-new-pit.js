		 
(function ($) {

		   // Behaviors related to Snowpit Profile Forms
	Drupal.behaviors.snowpilot3 = {

	attach: function (context, settings) {
		

	  $('.group-layers table.field-multiple-table > tbody').accordion({
	 	 header: "h3",
	 	 collapsible: true,
	 	 active: false,
	 	 heightStyle: "fill"
	  });		
     
    $('.horizontal-tabs-panes').once('accordion_ajax_toggle', function() {
        $( '.group-layers table.field-multiple-table > tbody').accordion('option', 'active', ":last");
      $(document).ajaxComplete(function() {
        $( '.group-layers table.field-multiple-table > tbody').accordion('option', 'active', ":last");
      });
    });
		$('ul.horizontal-tabs-list li.horizontal-tab-button-1 a' ).click( function() {
        $( '.group-layers table.field-multiple-table > tbody').accordion('option', 'active', ":last");
      $(document).ajaxComplete(function() {
        $( '.group-layers table.field-multiple-table > tbody').accordion('option', 'active', ":last");
      });
    });
	
  } // end attach 
	} // end behaviors 
}) (jQuery);