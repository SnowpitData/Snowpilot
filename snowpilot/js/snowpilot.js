(function ($) {

  // Behaviors related to Snowpit Profile Forms
  Drupal.behaviors.snowpilot = {

    attach: function (context, settings) {
			
			//$('edit-field-latitude-und-0-value', context).blur( updatePosition() );
      //
			// place focus on last tr element in layers part of form
			//
			$('table.field-multiple-table tr:last .field-name-field-bottom-depth input', context).focus();
			
      // Detect blur event for SnowPilot form inputs 
      $('#edit-field-layer', context).once('snowpilot_module', function () {
        $('#edit-field-layer', context).delegate( 'input', 'blur', function (event) {

          // Find layer number
          var layerString = $(this).parents("div[class*='layer_num_']")[0].className.split(" ")[1].split("_")[2];
          var layerNum = parseInt(layerString, 10);
 
          // Bottom Depth was blurred
          if($(this).parents('.field-name-field-bottom-depth').length) {
            // Add error class to any bottom depth field without a value 
            if($(this).val() == '') {
              $(this).addClass('error');
              console.log('error class added');
            } else {
              $(this).removeClass('error');
              console.log('error class removed');
            }
            
            // When bottom depth is changed, update next layers top depth
            $('div.layer_num_' + (layerNum + 1) + ' input[id*="-height-"]').val($(this).val());
          }
          
          // Top Depth was blurred
          if($(this).parents('.field-name-field-height').length) {
            // When top depth is changed, update previous layers bottom depth
            $('div.layer_num_' + (layerNum - 1) + ' input[id*="-bottom-depth-"]').val($(this).val());
          }
          
          event.stopPropagation();
        });
      });
      
	    $(".save.warn .ctools-dropdown-container-wrapper a").click(function(event) {
	        if( !confirm('You are about to save your finished snowpit to the snowpilot AvScience database, after which you will no longer be able to make changes. Continue?') ) 
	            event.preventDefault();
	    });
		  /*$('table.field-multiple-table #edit-field-layer-und-2-field-bottom-depth-und-0-value', context).blur( function() { 
				if($('#edit-field-layer-und-2-field-bottom-depth-und-0-value', context).val() == '')
					{ $(this).addClass('error');
						//alert("this is the alert");
					}
			});*/
			
    /*  $('#edit-field-layer', context).once('open', function () {
        $('#edit-field-layer', context).delegate( 'h3.collapsible-handle', 'click', function (event) {
          $(this).parent().find('div.collapsible-content').toggle('slow');
          event.stopPropagation();
        });
      });
			
/*			$( "table.field-multiple-table #edit-field-layer-und-2-field-bottom-depth-und-0-value" ).rules( "add", {
			  minlength: 2,
			  messages: {
			    minlength: jQuery.validator.format("Please, at least {0} characters are necessary")
			  }
			});
			
			/*	
				$("#edit-field-test-und-0-field-stability-comments-und-0-value").validate({
				  rules: {
				    // simple rule, converted to {required:true}
						field_test[und][0][field_stability_comments][und][0][value]: "required",
				    // compound rule
				    email: {
				      required: true,[]
				      email: true
				    }
				  }
				});
				
				jQuery.validator.addMethod("ctscore", function(value, element) {
				  return this.optional(element) || /5/.test(value);
				}, "Please specify the correct value for ctscore ( try 5 )");
*/
		
			// If the layer count is greater than 1
			// hide the select 
			//
			if ($('#snowpit-profile-node-form #edit-field-layer table tbody tr').length > 1 ){
				//  hide select is here
				$('#snowpit-profile-node-form #edit-field-depth-0-from').hide();
			}

			//
			//  end tweaks for "hide depth from ..." field
			//
			
			//
			//  start tweaks for "this is my layer of greatest concern"
			//////////////////////////////////////////////////////////
			// Hide initially if anything is checked
			$('div.field-name-field-this-is-my-layer-of-greate input.form-checkbox:checked', context).each(function() {
        if ($(this).is(':checked')) {
          $('div.field-name-field-this-is-my-layer-of-greate input.form-checkbox', context).not(this).each(function() {
            $(this).parent().hide();
          });
        } else {
          $('div.field-name-field-this-is-my-layer-of-greate input.form-checkbox', context).parent().show();
        }
			});
			
			//If anything gets checked, hide the other boxes; and vice versa
			
      $('div.field-name-field-this-is-my-layer-of-greate input.form-checkbox', context).change(function() {
        if ($(this).is(':checked')) {
          $('div.field-name-field-this-is-my-layer-of-greate input.form-checkbox', context).not(this).each(function() {
             $(this).parent().hide();
          });
        } else {
          $('div.field-name-field-this-is-my-layer-of-greate input.form-checkbox', context).parent().show();
        }
      });
				
			//////////////////////////////////////
			// Hide live Profile initially, and on clicking "Core Info" tab (...-button-0 a ), but show on clicking all other tabs
			$('ul.horizontal-tabs-list li.horizontal-tab-button-0.selected' ).each( function() {
				$('#edit-field-graph-canvas', context).hide();
				
			});		
			
			$('ul.horizontal-tabs-list li.horizontal-tab-button-1 a' ).click( function() {
				$('#edit-field-graph-canvas', context).show();
				
			});
			$('ul.horizontal-tabs-list li.horizontal-tab-button-2 a' ).click( function() {
				$('#edit-field-graph-canvas', context).show();
				
			});
			$('ul.horizontal-tabs-list li.horizontal-tab-button-3 a' ).click( function() {
				$('#edit-field-graph-canvas', context).show();
				
			});			
			$('ul.horizontal-tabs-list li.horizontal-tab-button-4 a' ).click( function() {
				$('#edit-field-graph-canvas', context).show();
				
			});		
			$('ul.horizontal-tabs-list li.horizontal-tab-button-0 a' ).click( function() {
				$('#edit-field-graph-canvas', context).hide();
				
			});			 
			
			/////////////////////////////
			// hide the Measurement Unit Prefs fieldset
			$('#snowpit-profile-node-form fieldset.group-measurement-prefs').hide();
			
			// save button at top of 'view' page
			$('button#save-button', context).click(function(e) {
			    e.preventDefault();  //stop the browser from following
					//alert('hello world!');
			    window.location.href = '/sites/default/files/snowpit-profiles/graph-'+ $(this).attr("nid") +'.jpg?345';
			});
			//
			// Experimenting with accordian styles
			
	  	/* $("#accordian", context).accordion({
			    header: "h3"
			  });
		 */
			
			//// show / hide layers on click
      $('.collapsible-content.collapsed', context).hide();
      
      // REFACTOR TO USE EVENT DELEGATES: 
      // Snowpack Layers
      $('#edit-field-layer', context).once('open', function () {
        $('#edit-field-layer', context).delegate( 'h3.collapsible-handle', 'click', function (event) {
          $(this).parent().find('div.collapsible-content').toggle('slow');
          event.stopPropagation();
        });
      });
     
        
      // TEMP collection:
      $('#edit-field-temp-collection', context).once('open', function () {
        $('#edit-field-temp-collection', context).delegate( 'h3.collapsible-handle', 'click', function (event) {
          $(this).parent().find('div.collapsible-content').toggle('slow');
          event.stopPropagation();
        });
      });
      
            
      // DENSITY collection:
      $('#edit-field-density-profile', context).once('open', function () {
        $('#edit-field-density-profile', context).delegate( 'h3.collapsible-handle', 'click', function (event) {
          $(this).parent().find('div.collapsible-content').toggle('slow');
          event.stopPropagation();
        });
      });

      // Stability Tests:
      $('#edit-field-test', context).once('open', function () {
        $('#edit-field-test', context).delegate( 'h3.collapsible-handle', 'click', function (event) {
          $(this).parent().find('div.collapsible-content').toggle('slow');
          event.stopPropagation();
        });
      });
        
    }    // end of attach 
  };  //end of Drupal.behavior.snowpilot.formlayers
}) (jQuery);

