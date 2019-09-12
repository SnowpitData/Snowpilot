/*
   Use this file for js functions related to , e.g., hiding select dropdown
// Primary grain type needs to be changed to 'select list' from 'Hierarchical select' widget to properly hide using this-
//  Also, this only works for first load, and on the first layer; Needs to be generalized.
*/

(function ($) {
	var layerNum;
  // Behaviors related to Grain types dropdown
  Drupal.behaviors.snowpilot4 = {
    attach: function (context, settings) {
			
		// Hide the old primary grain type select 
		$('.field-name-field-grain-type').hide();
		// Hide the secondary grain type select dropdown
		$('.field-name-field-grain-type-secondary').hide();
		// Hide surface grain type dropdown 
		$('#edit-field-surface-grain-type').hide();
		
		// Attach listener to save layer number when modal is opened
		$('#edit-field-layer', context).once('grain_modal_layer_listeners', function () {       
			$('#edit-field-layer', context).delegate( 'a#modal-trigger', 'click', function (event) {
				var layerString = $(this).parents("div[class*='layer_num_']")[0].className.split(" ")[1].split("_")[2];
				layerNum = parseInt(layerString, 10);
			});
			
			
      // Listener for secondary, also opens model if checkbox is checked
      $('#edit-field-layer', context).delegate( "input[id*='field-use-multiple-grain-type-und']", 'change', function (event) {
        var layerString = $(this).parents("div[class*='layer_num_']")[0].className.split(" ")[1].split("_")[2];
				layerNum = parseInt(layerString, 10);
        if (this.checked) {
          $('#grain-types-secondary-modal').modal();
        } else {
          // Clear grain type image from the form
          $('div.layer_num_' + layerNum + ' span.grain-type-secondary-display').html('');
          // Clear grain type from live profile 
          var selector = 'select[id^="edit-field-layer-und-' + layerNum + '-field-grain-type-secondary-und"]';
          $(selector).val("_none");
          // Fire event to update live profile
          $(selector).trigger('change');
        }
      });
		});
		
		// Attach listener to detect user clicks in grain type modal popup
		$('#grain-types-modal', context).once('modal_click_listener', function () {       
			$('#grain-types-modal', context).delegate( 'a.parent, a.child', 'click', function (event) {
				
				// Set div image in Layers Form
				var selected_grain = $(this).children("div.grain-types").eq(0).html();
				$('div.layer_num_' + layerNum + ' span.grain-type-primary-display').html(selected_grain);
								
				// Parse TID from class attribute
				var selected_tid = $(this).attr('class').split(" ")[1].split("-")[1];
				// REmoving this since parseInt seems superflous, and makes it so it doesn't work with '_none'
				//console.log(selected_tid);
				//var tid = parseInt(selected_tid, 10);
				//console.log('tid: '+tid);
				// Set value in old primary grain type select
				var selector = 'select[id^="edit-field-layer-und-' + layerNum + '-field-grain-type-und"]';
				
				$(selector).val(selected_tid);
				// Fire event to update live profile
				$(selector).trigger('change');
				// don't try to go to the #id-value
				event.preventDefault();
				
				// Close modal window after click
				$.modal.close();
				
			});
		});
    //
		// Secondary grain type
		$('#grain-types-secondary-modal', context).once('modal_click_listener_secondary', function () {       
			$('#grain-types-secondary-modal', context).delegate( 'a.parent, a.child', 'click', function (event) {
				
				// Set div image in Layers Form
				var selected_grain = $(this).children("div.grain-types").eq(0).html();
        $('div.layer_num_' + layerNum + ' span.grain-type-secondary-display').html(selected_grain);		
				
				// Set Secondary grain types checkbox		
				
				$('#edit-field-layer-und-' + layerNum + '-field-use-multiple-grain-type-und').prop('checked' , true);
				
        // Parse TID from class attribute
				var selected_tid = $(this).attr('class').split(" ")[1].split("-")[1];
			//	var tid = parseInt(selected_tid, 10);
				
				// Set value in old secondary grain type select
				var selector = 'select[id^="edit-field-layer-und-' + layerNum + '-field-grain-type-secondary-und"]';
				$(selector).val(selected_tid);
				
				// Fire event to update live profile
				$(selector).trigger('change');
				// don't follow link to the #id-value
				event.preventDefault();
				
				// Close modal window after click
				$.modal.close();
				
			});
		});
		
		// Surface grain type
		$('#grain-types-surface-modal', context).once('modal_click_listener', function () {       
			$('#grain-types-surface-modal', context).delegate( 'a.parent, a.child', 'click', function (event) {
				
				// Set div image in Layers Form
				var selected_grain = $(this).children("div.grain-types").eq(0).html();
				$('span.grain-type-surface-display').html(selected_grain);
								
				// Parse TID from class attribute
				var selected_tid = $(this).attr('class').split(" ")[1].split("-")[1];
				//var tid = parseInt(selected_tid, 10);
				
				// Set value in old primary grain type select
				var selector = 'select[id^="edit-field-surface-grain-type-und"]';
				$(selector).val(selected_tid);
				// Fire event to update live profile
				$(selector).trigger('change');
				// don't try to go to the #id-value
				event.preventDefault();
				
				// Close modal window after click
				$.modal.close();
				
			});
		});
		
    }    // end of attach
  };  //end of Drupal.behavior.snowpilot4
}) (jQuery);