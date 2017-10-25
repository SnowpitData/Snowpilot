(function ($) {
  
  // variables for layer of greatest concern alert 
  var lgcWarningRequired = true;
  var layerTabSelected = false;

  // Behaviors related to Snowpit Profile Forms
  Drupal.behaviors.snowpilot = {

    attach: function (context, settings) {
      
      // Begin auto-scroll for SnowPilot form 
      $('#edit-field-layer', context).once('auto_scroll', function () {
        // Ensure 'Add layer' button is visible on new layer 
        $(document).ajaxStop(function () {
          if ($(window).scrollTop() > 150) {
            // Layers Tab
            if ($('#active-horizontal-tab').parents('.horizontal-tab-button-1').length > 0) {
              var diff = $(window).height() - $('#edit-field-layer').height() - 7// for some reason ajaxStop triggers a bit too early, -7 makes this consistent with autoscroll code
              if (diff < 0) {
                $('#edit-field-layer').css({
                  "position": "fixed",
                  "top": diff
                });
              }
            // Stability Tests Tab
            } else if ($('#active-horizontal-tab').parents('.horizontal-tab-button-2').length > 0) {
              var diff = $(window).height() - $('#edit-field-test').height() - 7;
              if (diff < 0) {
                $('#edit-field-test').css({
                  "position": "fixed",
                  "top": diff
                });
              }
            // Temp Profile Tab
            } else if ($('#active-horizontal-tab').parents('.horizontal-tab-button-3').length > 0) {
              var diff = $(window).height() - $('#edit-field-temp-collection').height() - 7;
              if (diff < 0) {
                $('#edit-field-temp-collection').css({
                  "position": "fixed",
                  "top": diff
                });
              }
            // Density Tab
            } else if ($('#active-horizontal-tab').parents('.horizontal-tab-button-4').length > 0) {
              var diff = $(window).height() - $('#edit-field-density-profile').height() - 7;
              if (diff < 0) {
                $('#edit-field-density-profile').css({
                  "position": "fixed",
                  "top": diff
                });
              }
            }
          }
        });
        // Change CSS on SnowPilot form based on scroll position 
        $(window).scroll(function () {
          // Layers Tab
          if ($('#active-horizontal-tab').parents('.horizontal-tab-button-1').length > 0) {
            if ($(window).scrollTop() > 150 ) {
              var diff = $(window).height() - $('#edit-field-layer').height();
              if (diff < 0) {
                $('#edit-field-layer').css({
                  "position": "fixed",
                  "top": diff
                });
              } else {
                $('#edit-field-layer').css({
                  "position": "fixed",
                  "top": "5px"
                });
              }
            } else {
              $('#edit-field-layer').css({
                "position": "static"
              });
            }
          // Stability Tests Tab
          } else if ($('#active-horizontal-tab').parents('.horizontal-tab-button-2').length > 0) {
            if ($(window).scrollTop() > 150 ) {
              var diff = $(window).height() - $('#edit-field-test').height();
              if (diff < 0) {
                $('#edit-field-test').css({
                  "position": "fixed",
                  "top": diff
                });
              } else {
                $('#edit-field-test').css({
                  "position": "fixed",
                  "top": "5px"
                });
              }
            } else {
              $('#edit-field-test').css({
                "position": "static"
              });
            }
          // Temp Profile Tab
          } else if ($('#active-horizontal-tab').parents('.horizontal-tab-button-3').length > 0) {
            if ($(window).scrollTop() > 150 ) {
              var diff = $(window).height() - $('#edit-field-temp-collection').height();
              if (diff < 0) {
                $('#edit-field-temp-collection').css({
                  "position": "fixed",
                  "top": diff
                });
              } else {
                $('#edit-field-temp-collection').css({
                  "position": "fixed",
                  "top": "5px"
                });
              }
            } else {
              $('#edit-field-temp-collection').css({
                "position": "static"
              });
            }
          } else if ($('#active-horizontal-tab').parents('.horizontal-tab-button-4').length > 0) {
          // Density Tab
            if ($(window).scrollTop() > 150 ) {
              var diff = $(window).height() - $('#edit-field-density-profile').height();
              if (diff < 0) {
                $('#edit-field-density-profile').css({
                  "position": "fixed",
                  "top": diff
                });
              } else {
                $('#edit-field-density-profile').css({
                  "position": "fixed",
                  "top": "5px"
                });
              }
            } else {
              $('#edit-field-density-profile').css({
                "position": "static"
              });
            }
          }
        });
      });
			
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
			// Hide initially if anything is checked and disable LGC warning
			$('div.field-name-field-this-is-my-layer-of-greate input.form-checkbox:checked', context).each(function() {
        if ($(this).is(':checked')) {
          $('div.field-name-field-this-is-my-layer-of-greate input.form-checkbox', context).not(this).each(function() {
            $(this).parent().hide();
          });
          lgcWarningRequired = false;
        } else {
          $('div.field-name-field-this-is-my-layer-of-greate input.form-checkbox', context).parent().show();
          lgcWarningRequired = true;
        }
			});
			
			//If anything gets checked, hide the other boxes and disable warning; and vice versa
			
      $('div.field-name-field-this-is-my-layer-of-greate input.form-checkbox', context).change(function() {
        if ($(this).is(':checked')) {
          lgcWarningRequired = false;
          $('div.field-name-field-this-is-my-layer-of-greate input.form-checkbox', context).not(this).each(function() {
             $(this).parent().hide();
          });
        } else {
          lgcWarningRequired = true;
          $('div.field-name-field-this-is-my-layer-of-greate input.form-checkbox', context).parent().show();
        }
      });
      
      // Horizontal tab listeners for Layer of Greatest Concern Warning
      $('#content', context).once('lgc_warning', function () {
        $('ul.horizontal-tabs-list').delegate('li', 'mousedown', function (event) {
          if($(this).hasClass("horizontal-tab-button-1")) {
            layerTabSelected = true;
          } else if(layerTabSelected && lgcWarningRequired) {
            alert("Reminder:  You have not yet selected a layer of greatest concern");
            lgcWarningRequired = false;
          }
        });
        // Save and Preview listeners for LGC warning
        $('#edit-submit').mousedown(function (event) {
          if(layerTabSelected && lgcWarningRequired) {
            alert("Reminder:  You have not yet selected a layer of greatest concern");
            lgcWarningRequired = false;
          }
        });
        $('#edit-submit--2').mousedown(function (event) {
          if(layerTabSelected && lgcWarningRequired) {
            alert("Reminder:  You have not yet selected a layer of greatest concern");
            lgcWarningRequired = false;
          }
        });
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

    }    // end of attach 
  };  //end of Drupal.behavior.snowpilot.formlayers
}) (jQuery);

