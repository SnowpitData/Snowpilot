(function ($) {
  
  Drupal.behaviors.snowpilot1 = {
    
    attach: function (context, settings) {
      
      $('input[name=field_layer_add_more]', context).once( function () {
          $('input[name=field_layer_add_more]', context).mousedown(function() {
              var maxIndex = SnowProfile.snowLayers.length - 1;
              var spaceBelow = SnowProfile.pitDepth - SnowProfile.snowLayers[maxIndex].depth();
              SnowProfile.newLayer(SnowProfile.snowLayers[maxIndex].depth() + (spaceBelow / 2));
              
            });
      });
      
    } // end attach
  }; // end behaviors.snowpilot.liveeditor


  Drupal.behaviors.snowpilot2 = {

    attach: function (context, settings) {
      
			//
			//  hide "depth 0 measured from" field
			//
			$('#edit-field-depth-0-from select', context).hide();
			
			//
			//  reset label field to show which option is chosen
			//
			$('#edit-field-depth-0-from label', context).text( function() {
				return "measure from: " + $( "#edit-field-depth-0-from select option:selected").val();
			});
      
			//
			//  everytime the select option is changed, hide the dropdown and reset the label to relect !!
			//
			$('#edit-field-depth-0-from select', context).once( function () {
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
			$('#edit-field-depth-0-from select', context).blur( function () {
				
				$('#edit-field-depth-0-from label', context).text( function() {
					return "measure from: " + $( "#edit-field-depth-0-from select option:selected").val();	
				});
				//  hide select is here
				$('#edit-field-depth-0-from select', context).hide();
			});
					
			
			$('#edit-field-depth-0-from label', context).once('open', function () {
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
			
			//If anything gets checked, hide the others; and vice versa
			
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
			/*$('button#save-button', context).click(function(e) {
			    e.preventDefault();  //stop the browser from following
					//alert('hello world!');
			    window.location.href = '/sites/default/files/snowpit-profiles/graph-41.jpg?1464807785';
			});*/
			
			
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
      
        
      // Temp collection:
        $('#edit-field-temp-collection .temp_num_0 h3.collapsible-handle', context).once('open', function () { 
            $('#edit-field-temp-collection .temp_num_0 h3.collapsible-handle', context).click(function () {
                $('.temp_num_0 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-temp-collection .temp_num_1 h3.collapsible-handle', context).once('open', function () { 
            $('#edit-field-temp-collection .temp_num_1 h3.collapsible-handle', context).click(function () {
                $('.temp_num_1 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-temp-collection .temp_num_2 h3.collapsible-handle', context).once('open', function () { 
            $('#edit-field-temp-collection .temp_num_2 h3.collapsible-handle', context).click(function () {
                $('.temp_num_2 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-temp-collection .temp_num_3 h3.collapsible-handle', context).once('open', function () { 
            $('#edit-field-temp-collection .temp_num_3 h3.collapsible-handle', context).click(function () {
                $('.temp_num_3 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-temp-collection .temp_num_4 h3.collapsible-handle', context).once('open', function () { 
            $('#edit-field-temp-collection .temp_num_4 h3.collapsible-handle', context).click(function () {
                $('.temp_num_4 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-temp-collection .temp_num_5 h3.collapsible-handle', context).once('open', function () { 
            $('#edit-field-temp-collection .temp_num_5 h3.collapsible-handle', context).click(function () {
                $('.temp_num_5 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-temp-collection .temp_num_6 h3.collapsible-handle', context).once('open', function () { 
            $('#edit-field-temp-collection .temp_num_6 h3.collapsible-handle', context).click(function () {
                $('.temp_num_6 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-temp-collection .temp_num_7 h3.collapsible-handle', context).once('open', function () { 
            $('#edit-field-temp-collection .temp_num_7 h3.collapsible-handle', context).click(function () {
                $('.temp_num_7 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-temp-collection .temp_num_8 h3.collapsible-handle', context).once('open', function () { 
            $('#edit-field-temp-collection .temp_num_8 h3.collapsible-handle', context).click(function () {
                $('.temp_num_8 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-temp-collection .temp_num_9 h3.collapsible-handle', context).once('open', function () { 
            $('#edit-field-temp-collection .temp_num_9 h3.collapsible-handle', context).click(function () {
                $('.temp_num_9 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-temp-collection .temp_num_10 h3.collapsible-handle', context).once('open', function () { 
            $('#edit-field-temp-collection .temp_num_10 h3.collapsible-handle', context).click(function () {
                $('.temp_num_10 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });

            
// DENSITY collection:
        $('#edit-field-density-profile .density_num_0 h3.collapsible-handle', context).once('open', function () {
            $('#edit-field-density-profile .density_num_0 h3.collapsible-handle', context).click(function () {
                $('.density_num_0 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-density-profile .density_num_1 h3.collapsible-handle', context).once('open', function () {
            $('#edit-field-density-profile .density_num_1 h3.collapsible-handle', context).click(function () {
                $('.density_num_1 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-density-profile .density_num_2 h3.collapsible-handle', context).once('open', function () {
            $('#edit-field-density-profile .density_num_2 h3.collapsible-handle', context).click(function () {
                $('.density_num_2 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-density-profile .density_num_3 h3.collapsible-handle', context).once('open', function () {
            $('#edit-field-density-profile .density_num_3 h3.collapsible-handle', context).click(function () {
                $('.density_num_3 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-density-profile .density_num_4 h3.collapsible-handle', context).once('open', function () {
            $('#edit-field-density-profile .density_num_4 h3.collapsible-handle', context).click(function () {
                $('.density_num_4 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-temp-collection .density_num_5 h3.collapsible-handle', context).once('open', function () {
            $('#edit-field-temp-collection .density_num_5 h3.collapsible-handle', context).click(function () {
                $('.density_num_5 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
                    
// Stability Tests:
        $('#edit-field-test .stability_test_num_0 h3.collapsible-handle', context).once('open', function () {
            $('#edit-field-test .stability_test_num_0 h3.collapsible-handle', context).click(function () {
                $('.stability_test_num_0 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-test .stability_test_num_1 h3.collapsible-handle', context).once('open', function () {
            $('#edit-field-test .stability_test_num_1 h3.collapsible-handle', context).click(function () {
                $('.stability_test_num_1 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-test .stability_test_num_2 h3.collapsible-handle', context).once('open', function () {
            $('#edit-field-test .stability_test_num_2 h3.collapsible-handle', context).click(function () {
                $('.stability_test_num_2 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-test .stability_test_num_3 h3.collapsible-handle', context).once('open', function () {
            $('#edit-field-test .stability_test_num_3 h3.collapsible-handle', context).click(function () {
                $('.stability_test_num_3 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-test .stability_test_num_4 h3.collapsible-handle', context).once('open', function () {
            $('#edit-field-test .stability_test_num_4 h3.collapsible-handle', context).click(function () {
                $('.stability_test_num_4 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
            
        $('#edit-field-temp-collection .stability_test_num_5 h3.collapsible-handle', context).once('open', function () {
            $('#edit-field-temp-collection .stability_test_num_5 h3.collapsible-handle', context).click(function () {
                $('.stability_test_num_5 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
        $('#edit-field-temp-collection .stability_test_num_6 h3.collapsible-handle', context).once('open', function () {
            $('#edit-field-temp-collection .stability_test_num_6 h3.collapsible-handle', context).click(function () {
                $('.stability_test_num_6 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });        $('#edit-field-temp-collection .stability_test_num_7 h3.collapsible-handle', context).once('open', function () {
            $('#edit-field-temp-collection .stability_test_num_7 h3.collapsible-handle', context).click(function () {
                $('.stability_test_num_7 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
        $('#edit-field-temp-collection .stability_test_num_8 h3.collapsible-handle', context).once('open', function () {
            $('#edit-field-temp-collection .stability_test_num_8 h3.collapsible-handle', context).click(function () {
                $('.stability_test_num_8 .collapsible-content').toggle('slow', function () {
                    // Animation complete.
                });
                //add css class to H2 title when clicked//
                //$(this).toggleClass('open');
            });
        });
        
        }    // end of attach 
    };  //end of Drupal.behavior.snowpilot.formlayers
}) (jQuery);

