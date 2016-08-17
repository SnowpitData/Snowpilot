/**
 * @file Contains main program
 * @copyright Walt Haas <haas@xmission.com>
 * @license {@link http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GPLv2}
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
  
  // Initialize the live editor one time on document ready 
  var isInitialized, promptedForConcern;
  $(document).ready(function() {
    if(!isInitialized) 
    {
      SnowProfile.main();
      isInitialized = true;
      promptedForConcern = false;
      
      // Run initialization code for snowpits with already existing information
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
      // Add layers to live graph to match form - first layer added at 0, change later 
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
      }
      // Final Layout
      SnowProfile.layout();
      // Features
      for (var i = 0; i < layers; i++) {
        SnowProfile.snowLayers[i].features().describe(SnowProfile.getSnowPilotData(i));
      }
    }
    
    // Testing form for new layers to add new layers to live profile
    $(document).ajaxComplete(function() {
      // get the next index to test if AJAX resulted in that layer being added to form
      var nextIndex = SnowProfile.snowLayers.length - 1;
      var maxIndex = nextIndex - 1;
      // test for existence of some element in that layer of the form...in this case bottom depth is fine
      if ($("[id^=edit-field-layer-und-" + nextIndex + "-field-bottom-depth-und-0-value]").length) {
        // add new layer if the form updated, use different depth values depending on depthRef
        var newDepthNumber = Number($("[id^=edit-field-layer-und-" + maxIndex + "-field-bottom-depth-und-0-value]").val());
        if (SnowProfile.depthRef === 's'){
          SnowProfile.newLayer(newDepthNumber + 20);
        }
        else if (SnowProfile.depthRef === 'g'){
          SnowProfile.newLayer(SnowProfile.pitDepth - newDepthNumber + 20);
        }
      }
    });
  });
  
  
  // Behaviors related to Live Graph Editor
  Drupal.behaviors.sp_livegraph = {
      
    attach: function (context, settings) {
      
      // Overriding the prototype beforeSubmit function in drupal ajax.js (maybe a bad idea?)
      /*
      Drupal.ajax.prototype.beforeSubmit = function (form_values, element, options) {
        var elementName = options.extraData._triggering_element_name;
        var elementText = options.extraData._triggering_element_value;
        
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
        }
      } */
      
      // Listen for text changes to form and update live graph appropriately
      $('#edit-field-layer', context).once('livegraph_connected', function () {        
        // Input delegation
        $('#edit-field-layer', context).delegate( 'input', 'change', function (event) {
          // Find layer number - starts at 0, corresponds directly to SnowProfile.snowLayers[] index but not to .length
          var layerString = $(this).parents("div[class*='layer_num_']")[0].className.split(" ")[1].split("_")[2];
          var layerNum = parseInt(layerString, 10);
            
          // Top Depth was changed
          if($(this).parents('.field-name-field-height').length)
          {
            // Update layer depth value
            if (SnowProfile.depthRef === "s") 
              SnowProfile.snowLayers[layerNum].depth($(this).val());
            else if (SnowProfile.depthRef === "g")
              SnowProfile.snowLayers[layerNum].depth(SnowProfile.pitDepth - $(this).val());
            
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
                SnowProfile.snowLayers[(layerNum + 1)].depth($(this).val());
              else if (SnowProfile.depthRef === "g")
                SnowProfile.snowLayers[(layerNum + 1)].depth(SnowProfile.pitDepth - $(this).val());
              // Draw
              if((layerNum + 2) == SnowProfile.snowLayers.length) {
                // Working with last visible layer,so keep bottom slope handle hidden
                SnowProfile.snowLayers[(layerNum + 1)].handleTouchState(true, true);
              }
              SnowProfile.snowLayers[(layerNum + 1)].draw();
              SnowProfile.snowLayers[layerNum].draw();
            }
            SnowProfile.layout();
          }
          // Stop Event 
          event.stopPropagation();
        });
        // Select delegation
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
        // Stability Tests delegation
        $('#edit-field-test', context).delegate( 'select', 'change', function (event) {
          // Get Test number 
          var testString = $(this).parents("div[class*='stability_test_num_']")[0].className.split(" ")[1].split("_")[3];
          var testNum = parseInt(testString, 10);
          // Check if we can display a stability test 
          SnowProfile.checkStabilityTest(testNum);
          // Update live profile
          SnowProfile.snowLayers[0].features().describe(SnowProfile.getSnowPilotData(0));
        });
        $('#edit-field-test', context).delegate( 'input', 'blur', function (event) {
          // Get Test number 
          var testString = $(this).parents("div[class*='stability_test_num_']")[0].className.split(" ")[1].split("_")[3];
          var testNum = parseInt(testString, 10);
          // Check if we can display a stability test 
          SnowProfile.checkStabilityTest(testNum);
          // Update live profile
          SnowProfile.snowLayers[0].features().describe(SnowProfile.getSnowPilotData(0));
        });
        
      });
      
    } // end attach
    
  }; // end behaviors.snowpilot.sp_livegraph    
	
})(jQuery);

// Configure Emacs for Drupal JavaScript coding standards
// Local Variables:
// js2-basic-offset: 2
// indent-tabs-mode: nil
// fill-column: 78
// show-trailing-whitespace: t
// End:
