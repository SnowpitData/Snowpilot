/*
   Use this file for js functions related to , e.g., hiding select dropdown

// Primary grain type needs to be changed to 'select list' from 'Hierarchical select' widget to properly hide using this-
//  Also, this only works for first load, and on the first layer; Needs to be generalized.
*/

(function ($) {
  // Behaviors related to Grain types dropdown
  Drupal.behaviors.snowpilot4 = {
    attach: function (context, settings) {
			
		// Hide the old primary grain type select 
		$('.field-name-field-grain-type').hide();
				
		
		$('.layer_num_0 .parent-39 a.parent').click(function(){
			var selected_tid = 39;
			var selected_grain = 'x0799';
			// Set div image in Layers Form
			$('.layer_num_0 .grain-type-display').html('&#'+ selected_grain +';');
			// Set value in old primary grain type select
			$('#edit-field-layer-und-0-field-grain-type-und').val(selected_tid);
			// Fire event to update live profile
			$('#edit-field-layer-und-0-field-grain-type-und').trigger('change');
		});
		
    }    // end of attach
  };  //end of Drupal.behavior.snowpilot4
}) (jQuery);
