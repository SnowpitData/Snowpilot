/**
 * @file Contains main program, plus setup for SnowPilot.com Drupal Website
 * @copyright Walt Haas <haas@xmission.com>
 * @license {@link http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GPLv2}
 * Modified by Joe DeBruycker
 */

/* global SnowProfile */
/* global SVG */

(function($) {
  "use strict";

  /**
   * Main program
   */
  SnowProfile.main = function() {
    if (SVG.supported) {
      var i;
      SnowProfile.init();
    } else {
      alert('Your browser does not support SVG, required by the snow profile editor');
    }
  };
  
  // Initialize a snowpit in progress from the SnowPilot web form 
  function SnowPilotInit () {
    // Loop and check for existence of snowpack layers and count them, break when finished
    var layers = 0;
    while (true) {
      // special case for first layer, which exist even on new pits, so we check for a value
      if (layers === 0){
        if ($.trim($("[id^=edit-field-layer-und-" + layers + "-field-bottom-depth-und-0-value]").val()).length) {
          layers++;
        } else {
          break;
        }
      } else {
        // otherwise we check for field existance
        if ($("[id^=edit-field-layer-und-" + layers + "-field-bottom-depth-und-0-value]").length) {
          layers++;
        } else {
          break;
        }
      }
    }
    // Add layers to live graph to match form, first layer added at depth 0
    SnowProfile.newLayer(0);
    if (layers > 0){
      // Set up hardness values for first layer 
      SnowProfile.snowLayers[0].handleTouchState(true, false);
      SnowProfile.snowLayers[0].features().hardness($("[id^=edit-field-layer-und-0-field-hardness-und]").val());
      if ($("[id^=edit-field-layer-und-0-field-use-multiple-hardnesses-und]").is(":checked")) {
          SnowProfile.snowLayers[0].slopeHandleTouchState(true);
          SnowProfile.snowLayers[0].features().hardness2($("[id^=edit-field-layer-und-0-field-hardness2-und]").val());
        }
      // Draw Layer
      SnowProfile.snowLayers[0].draw();
      // Initialize any additional layers 
      for (var i = 1; i < layers; i++) {
        if (SnowProfile.depthRef === 's'){
          SnowProfile.newLayer($("[id^=edit-field-layer-und-" + i + "-field-height-und-0-value]").val());
        }
        else if (SnowProfile.depthRef === 'g'){
          SnowProfile.newLayer(SnowProfile.pitDepth - $("[id^=edit-field-layer-und-" + i + "-field-height-und-0-value]").val());
        }
        SnowProfile.snowLayers[i].handleTouchState(true, false);
        // Set up hardness values
        SnowProfile.snowLayers[i].features().hardness($("[id^=edit-field-layer-und-" + i + "-field-hardness-und]").val());
        if ($("[id^=edit-field-layer-und-" + i + "-field-use-multiple-hardnesses-und]").is(":checked")) {
          SnowProfile.snowLayers[i].slopeHandleTouchState(true);
          SnowProfile.snowLayers[i].features().hardness2($("[id^=edit-field-layer-und-" + i + "-field-hardness2-und]").val());
        }
        // Draw Layer
        SnowProfile.snowLayers[i].draw();
        SnowProfile.snowLayers[i-1].draw();
      }
      // Set up final hidden layer with height equal to the final bottom depth
      var finalLayer = layers - 1;
      if (SnowProfile.depthRef === 's'){
        SnowProfile.newLayer($("[id^=edit-field-layer-und-" + finalLayer + "-field-bottom-depth-und-0-value]").val());
      }
      else if (SnowProfile.depthRef === 'g'){
        SnowProfile.newLayer(SnowProfile.pitDepth - $("[id^=edit-field-layer-und-" + finalLayer + "-field-bottom-depth-und-0-value]").val());
      }
      // Draw Layer 
      SnowProfile.snowLayers[layers].handleTouchState(true, true);
      SnowProfile.snowLayers[layers].draw();
      SnowProfile.snowLayers[finalLayer].draw();
    } else {
      // This is a new snowpit so initialize hidden layer with depth 20
      SnowProfile.newLayer(20);
      SnowProfile.snowLayers[1].handleTouchState(true, true);
      SnowProfile.snowLayers[1].draw();
      SnowProfile.snowLayers[0].draw();
      // Prepopulate first layer Top Depth 
      var currentDepth = SnowProfile.pitDepth;
      if (SnowProfile.depthRef === "s") {
        var roundedDepth = 0.0;
        $('div.layer_num_0 input[id*="-height-"]').val(roundedDepth);
      }
      else if (SnowProfile.depthRef === "g") {
        var roundedDepth = Math.round(currentDepth * 10) / 10;
        $('div.layer_num_0 input[id*="-height-"]').val(roundedDepth);
      }
    }
    // Initialize Stability Tests:
    // Loop and check for existence of stability tests and count them, break when finished
    var numTests = 0;
    while (true) {
      // special case for first test, which exist even on new pits, so we check for a value
      if (numTests === 0){
        if ($.trim($("[id^=edit-field-test-und-" + numTests + "-field-depth-und-0-value]").val()).length) {
          numTests++;
        } else {
          break;
        }
      } else {
        // otherwise we check for field existance
        if ($("[id^=edit-field-test-und-" + numTests + "-field-depth-und-0-value]").length) {
          numTests++;
        } else {
          break;
        }
      }
    }
    // Populate SnowProfile.stabilityTests with existing test information
    for (var i = 0; i < numTests; i++) {
      SnowProfile.addStabilityTest(i);
    }
    // Features
    for (var i = 0; i < layers; i++) {
      SnowProfile.snowLayers[i].features().describe(SnowProfile.getSnowPilotData(i));
    }
  }
  
  // Initialize the live editor one time on document ready 
  var isInitialized;
  SnowProfile.snowpackHeightSet = false;
  
  $(document).ready(function() {
    if(!isInitialized) 
    {
      SnowProfile.main();
      // Boolean to prevent reinitialization
      isInitialized = true;
      
      // Run initialization code for SnowPilot snowpits with already existing information
      SnowPilotInit();
    }
    
    // Testing form for new layers to add new layers to live profile
    $(document).ajaxComplete(function() {
      // get the next index to test if AJAX resulted in that layer being added to form
      var nextIndex = SnowProfile.snowLayers.length - 1;
      var maxIndex = nextIndex - 1;
      // test for existence of some element in that layer of the form...in this case bottom depth is fine
      if ($("[id^=edit-field-layer-und-" + nextIndex + "-field-bottom-depth-und-0-value]").length) {
        // add new layer if the form updated, use different depth values depending on depthRef
        var prevBottomDepth = $("[id^=edit-field-layer-und-" + maxIndex + "-field-bottom-depth-und-0-value]").val();
        // convert commas to decimals for EU style
        prevBottomDepth = prevBottomDepth.replace(/,/g, ".");
        var newDepthNumber = Number(prevBottomDepth);
        if (SnowProfile.depthRef === 's'){
          newDepthNumber += 20;
        }
        else if (SnowProfile.depthRef === 'g'){
          newDepthNumber = SnowProfile.pitDepth - newDepthNumber + 20;
        }
        // check if new layer would fall below graph
        if (newDepthNumber >= SnowProfile.pitDepth) {
          // if HoS is set in SnowPilot, new layer appears at bottom
          if (SnowProfile.snowpackHeightSet) {
            newDepthNumber = SnowProfile.pitDepth;
          } else {
            // if no HoS, extend and redraw grid by triggering event 
            SnowProfile.totalDepth += 20;
            $("#edit-field-total-height-of-snowpack-und-0-value").trigger("change");
            console.log("Extending grid");
          }
        }
        SnowProfile.newLayer(newDepthNumber);
      }
    });
  });
  
  
  // Behaviors related to Live Graph Editor
  Drupal.behaviors.sp_livegraph = {
      
    attach: function (context, settings) {
      
      // Overriding the prototype beforeSubmit function in drupal ajax.js (maybe a bad idea?)
      
      Drupal.ajax.prototype.beforeSubmit = function (form_values, element, options) {
        var elementName = options.extraData._triggering_element_name;
        var elementText = options.extraData._triggering_element_value;
        //console.log("Drupal Ajax triggered");
        //console.log("Element Name: " + elementName);
        //console.log("Element Text: " + elementText);
        
        // Remove stability tests
        if (elementText === "Remove Test") {
          // Find test number
          var testString = elementName.split("_")[3];
          var testNum = parseInt(testString, 10);
          
          // Remove that test from SnowProfile.stabilityTests
          SnowProfile.stabilityTests.splice(testNum, 1);
          
          for (var i = 0; i < (SnowProfile.snowLayers.length - 1); i++) {
            SnowProfile.snowLayers[i].features().describe(SnowProfile.getSnowPilotData(i));
          }
        }
        
        /*
        if (elementText === "Remove Layer"){
          // Find layer number
          var layerString = elementName.split("_")[3];
          var layerNum = parseInt(layerString, 10);
          
          // Confirm layer deletion
          var c = confirm("Really remove this layer???");
          // If user cancels, return false to cancel layer removal
          if (!c) {
            return false;
          }
          // If user confirms, must get value for surrounding layers top/bottom depths to close gap
          var newValue;
          var totalLayers = SnowProfile.snowLayers.length;
          if (layerNum === 0){
            // Top layer, so second layer becomes new top
            newValue = 0;
            $('div.layer_num_1 input[id*="-height-"]').val(newValue);
          } else if (layerNum > 0 && layerNum < (totalLayers - 2)) {
            // Some middle layer, so prompt user for new value (subtract 2 because of hidden layer)
            newValue = Number(prompt("Please enter the new layer depth value"));
            // Set SnowPilot form values
            $('div.layer_num_' + (layerNum + 1) + ' input[id*="-height-"]').val(newValue);
            $('div.layer_num_' + (layerNum - 1) + ' input[id*="-bottom-depth-"]').val(newValue);
            // Adjust live editor 
            SnowProfile.snowLayers[layerNum + 1].depth(newValue);
          } else {
            // Delete last layer, so only need to adjust live editor hidden layer 
            SnowProfile.snowLayers[layerNum + 1].depth(SnowProfile.snowLayers[layerNum].depth());
          }
          
          // Remove layer from live graph
          SnowProfile.snowLayers[layerNum].deleteLayer();
        } */
      } 
      
      // Auto scroll for SnowPilot form 
      $('#edit-field-layer', context).once('auto_scroll', function () {
        $(window).scroll(function () {
          //var scrollBot = $(document).height() - $(window).height() - $(window).scrollTop();
          // Layers Tab
          if ($('#active-horizontal-tab').parents('.horizontal-tab-button-1').length > 0) {
            var diff = $(window).height() - $('#edit-field-layer').height();
            if ($(window).scrollTop() > 160 ) {
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
            var diff = $(window).height() - $('#edit-field-test').height();
            if ($(window).scrollTop() > 160 ) {
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
            var diff = $(window).height() - $('#edit-field-temp-collection').height();
            if ($(window).scrollTop() > 160 ) {
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
            var diff = $(window).height() - $('#edit-field-density-profile').height();
            if ($(window).scrollTop() > 160 ) {
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
      
      // Listen for text changes to form and update live graph appropriately
      $('#edit-field-layer', context).once('livegraph_connected', function () {        
        // Layers input delegation
        $('#edit-field-layer', context).delegate( 'input', 'change', function (event) {
          // Find layer number - starts at 0, corresponds directly to SnowProfile.snowLayers[] index but not to .length
          var layerString = $(this).parents("div[class*='layer_num_']")[0].className.split(" ")[1].split("_")[2];
          var layerNum = parseInt(layerString, 10);
          // Convert commas to decimals for EU style
          var userInput = $(this).val();
          userInput = userInput.replace(/,/g, ".");
            
          // Top Depth was changed
          if($(this).parents('.field-name-field-height').length)
          {
            // Update layer depth value
            if (SnowProfile.depthRef === "s") 
              SnowProfile.snowLayers[layerNum].depth(userInput);
            else if (SnowProfile.depthRef === "g")
              SnowProfile.snowLayers[layerNum].depth(SnowProfile.pitDepth - userInput);
            
            // Draw
            SnowProfile.snowLayers[layerNum].draw();
            // If not the top layer, redraw the layer above
            if(layerNum != 0){
              SnowProfile.snowLayers[layerNum - 1].draw();
            }
            SnowProfile.layout();
          }
          // Bottom Depth was changed
          if($(this).parents('.field-name-field-bottom-depth').length)
          {
            // If not last (now hidden) layer, update the layer below depth value
            if((layerNum + 1) != SnowProfile.snowLayers.length){
              if (SnowProfile.depthRef === "s") 
                SnowProfile.snowLayers[(layerNum + 1)].depth(userInput);
              else if (SnowProfile.depthRef === "g")
                var invertedDepth = SnowProfile.pitDepth - userInput;
                SnowProfile.snowLayers[(layerNum + 1)].depth(invertedDepth);
              // If it's the last visible layer (2nd from last layer), keep bottom slope handle hidden
              if((layerNum + 2) == SnowProfile.snowLayers.length) {
                SnowProfile.snowLayers[(layerNum + 1)].handleTouchState(true, true);
              }
              // Draw
              SnowProfile.snowLayers[(layerNum + 1)].draw();
              SnowProfile.snowLayers[layerNum].draw();
            }
            SnowProfile.layout();
          }
          // Stop Event 
          event.stopPropagation();
        });
        // Layers input mouseover delegation for submit bug
        $('#edit-field-layer', context).delegate( 'input', 'mouseover', function (event) {
          if($(this).hasClass("field-add-more-submit")) {
            // When user hovers over Add Layer button, quickly blur and refocus element to trigger listeners
            var elem = document.activeElement;
            elem.blur();
            //elem.focus();
          }
          // Stop Event 
          event.stopPropagation();
        });
        // Layers select delegation
        $('#edit-field-layer', context).delegate( 'select', 'change', function (event) {
          
          var layerString = $(this).parents("div[class*='layer_num_']")[0].className.split(" ")[1].split("_")[2];
          var layerNum = parseInt(layerString, 10);
          
          // Primary Hardness Selector
          if($(this).parents('.field-name-field-hardness').length) {
            SnowProfile.snowLayers[layerNum].handleTouchState(true, false);
            SnowProfile.snowLayers[layerNum].features().hardness($(this).val());
            if(!(SnowProfile.snowLayers[layerNum].slopeHandleTouchState())){
              SnowProfile.snowLayers[layerNum].features().hardness2($(this).val());
            }
            SnowProfile.snowLayers[layerNum].draw();
          }
          
          // Secondary Hardness Selector
          if($(this).parents('.field-name-field-hardness2').length) {
            SnowProfile.snowLayers[layerNum].slopeHandleTouchState(true);
            SnowProfile.snowLayers[layerNum].features().hardness2($(this).val());
            SnowProfile.snowLayers[layerNum].draw();
          }
          
          // Primary Type Selector
          if($(this).parents('.field-name-field-grain-type').length) {
            SnowProfile.snowLayers[layerNum].features().describe(SnowProfile.getSnowPilotData(layerNum));
          }
          
          // Secondary Type Selector
          if($(this).parents('.field-name-field-grain-type-secondary').length) {
            SnowProfile.snowLayers[layerNum].features().describe(SnowProfile.getSnowPilotData(layerNum));
          }
          
          // Grain Size 1 Selector
          if($(this).parents('.field-name-field-grain-size').length) {
            SnowProfile.snowLayers[layerNum].features().describe(SnowProfile.getSnowPilotData(layerNum));
          }
          
          // Grain Size 2 Selector
          if($(this).parents('.field-name-field-grain-size-max').length) {
            SnowProfile.snowLayers[layerNum].features().describe(SnowProfile.getSnowPilotData(layerNum));
          }
          
          // Stop Event 
          event.stopPropagation();
        });
        
        // Stability Tests select delegation
        $('#edit-field-test', context).delegate( 'select', 'change', function (event) {
          // Get Test number 
          var testString = $(this).parents("div[class*='stability_test_num_']")[0].className.split(" ")[1].split("_")[3];
          var testNum = parseInt(testString, 10);
          // Try to add a stability test to the array
          SnowProfile.addStabilityTest(testNum);
          // Update live profile
          for (var i = 0; i < (SnowProfile.snowLayers.length - 1); i++) {
            SnowProfile.snowLayers[i].features().describe(SnowProfile.getSnowPilotData(i));
          }
        });
        // Stability Tests input delegation
        $('#edit-field-test', context).delegate( 'input', 'blur', function (event) {
          // Get Test number 
          var testString = $(this).parents("div[class*='stability_test_num_']")[0].className.split(" ")[1].split("_")[3];
          var testNum = parseInt(testString, 10);
          // Try to add a stability test to the array
          SnowProfile.addStabilityTest(testNum);
          // Update live profile
          for (var i = 0; i < (SnowProfile.snowLayers.length - 1); i++) {
            SnowProfile.snowLayers[i].features().describe(SnowProfile.getSnowPilotData(i));
          }
        });
        // Layers input mouseover delegation for submit bug
        $('#edit-field-test', context).delegate( 'input', 'mouseover', function (event) {
          if($(this).hasClass("field-add-more-submit")) {
            // When user hovers over Add Layer button, quickly blur and refocus element to trigger listeners
            var elem = document.activeElement;
            elem.blur();
            elem.focus();
          }
          // Stop Event 
          event.stopPropagation();
        });
        
      });
      
    } // end attach
    
  }; // end behaviors.snowpilot.sp_livegraph    
	
})(jQuery);