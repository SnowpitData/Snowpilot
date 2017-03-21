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
		// Hide 'use multiple grain types' checkbox 
		//$('.field-name-field-use-multiple-grain-type').hide();
		$('#edit-field-layer-und-0-field-use-multiple-grain-type-und').change(function(){
		  if (this.checked) {
				$('#grain-types-secondary-modal').modal();
		  }else{
		  	// unset the form item select list
				// similar to setting it, but we don't know layer number ....
				
				// help joe
				
		  }
		});
		// Attach listener to save layer number when modal is opened
		$('#edit-field-layer', context).once('grain_modal_layer', function () {       
			$('#edit-field-layer', context).delegate( 'a#modal-trigger', 'click', function (event) {
				var layerString = $(this).parents("div[class*='layer_num_']")[0].className.split(" ")[1].split("_")[2];
				layerNum = parseInt(layerString, 10);
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
				var tid = parseInt(selected_tid, 10);
				
				// Set value in old primary grain type select
				var selector = 'select[id^="edit-field-layer-und-' + layerNum + '-field-grain-type-und"]';
				$(selector).val(tid);
				
				// Fire event to update live profile
				$(selector).trigger('change');
				
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
				// Parse TID from class attribute
				var selected_tid = $(this).attr('class').split(" ")[1].split("-")[1];
				var tid = parseInt(selected_tid, 10);
				
				// Set value in old secondary grain type select
				var selector = 'select[id^="edit-field-layer-und-' + layerNum + '-field-grain-type-secondary-und"]';
				$(selector).val(tid);
				
				// Fire event to update live profile
				$(selector).trigger('change');
				
				// Close modal window after click
				$.modal.close();
				
			});
		});

		
    }    // end of attach
  };  //end of Drupal.behavior.snowpilot4
}) (jQuery);