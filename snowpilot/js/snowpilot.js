(function ($) {

  // Behaviors related to Snowpit Profile Forms
  Drupal.behaviors.snowpilot = {

    attach: function (context, settings) {
      //
			// place focus on last tr element in layers part of form
			//
			$('table.field-multiple-table tr:last .field-name-field-bottom-depth input', context).focus();
			
			/**
			 * It speaks for itself that Clientside Validation cannot provide javascript
			 * validation codes for custom validation rules defined in php. So if you want
			 * to support Clientside Validation you will have to code the javascript
			 * equivalent of your custom php validation rule and make it available for
			 * Clientside validation. Below is an example of how you would do this.
			 *
			 * The second block of code is an example of how to define a custom errorplacement function.
			 * You can set this option in admin/config/validation/clientside_validation
			 */
			      //Add an eventlistener to the document reacting on the
			      //'clientsideValidationAddCustomRules' event.
			      $(document).bind('blur', function(event){
			        //Add your custom method with the 'addMethod' function of jQuery.validator
			        //http://docs.jquery.com/Plugins/Validation/Validator/addMethod#namemethodmessage
			       /*
							 jQuery.validator.addMethod("myCustomMethod", function(value, element, param) {
			          //let an element match an exact value defined by the user
			          return value === param;
			          //Enter a default error message, numbers between {} will be replaced
			          //with the matching value of that key in the param array, enter {0} if
			          //param is a value and not an array.
			        }, jQuery.format('Value must be equal to {0}'));
							 */
							jQuery.validator.addMethod("ctscore", function(value, element, params) {
							  return this.optional(element) || $('#edit-field-test-und-2-field-ct-score-und-0-value'.val())>10;
							}, jQuery.validator.format("CT Score must be correct"));
  
			      });

			      // According to this example you would fill in 'mycustomerrorplacement' for the custom
			      // error placement function at admin/config/validation/clientside_validation
			      // The declaration of this function needs to be within the attach of a Drupal behavior.
			      //Drupal.clientsideValidation.prototype.mycustomerrorplacement = function (error, element) {
			        // error placement code here.
							//};

			

			
			//
			//  These functions all have to do with the 
			//  hide "depth 0 measured from" field
			//
			//
			//  default: hide the field
			$('#snowpit-profile-node-form #edit-field-depth-0-from select', context).hide();
			//  reset label field to show which option is chosen
			$('#snowpit-profile-node-form #edit-field-depth-0-from label', context).text( function() {
				return "measure from: " + $( "#edit-field-depth-0-from select option:selected").val();
			});
      
			//
			//  everytime the select option is changed, hide the dropdown and reset the label to relect !!
			//
			
			$('#snowpit-profile-node-form #edit-field-depth-0-from select', context).once( function () {
				$('#edit-field-depth-0-from select', context).change( function () {
					$('#edit-field-depth-0-from label', context).text( function() {
						return "measure from: " + $( "#edit-field-depth-0-from select option:selected").val();	
					});
				//  hide select is here
					$('#edit-field-depth-0-from select', context).hide();
				} );
			});
      
			// If the user just navigates away from the field ( blur ), also
			// hide the select and set the label
			//
			$('#snowpit-profile-node-form #edit-field-depth-0-from select', context).blur( function () {
				
				$('#edit-field-depth-0-from label', context).text( function() {
					return "measure from: " + $( "#edit-field-depth-0-from select option:selected").val();	
				});
				//  hide select is here
				$('#edit-field-depth-0-from select', context).hide();
			});
					
			
			$('#snowpit-profile-node-form #edit-field-depth-0-from label', context).once('open', function () {
					$('#edit-field-depth-0-from label', context).click(function () {
						$('#edit-field-depth-0-from select', context).toggle('200', function() { 
						}); // done
					}); //
			});
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

