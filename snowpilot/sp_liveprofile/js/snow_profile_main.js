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
      /*for (i = 0; i < SnowProfile.Cfg.NUM_INIT_LAYERS; i++) {
        SnowProfile.newLayer(i * SnowProfile.Cfg.INT_INIT_LAYERS);
      }*/
      SnowProfile.newLayer(0);
    } else {
      alert('Your browser does not support SVG, required by the snow profile editor');
    }
  };
  
  // Initialize the live editor one time on document ready 
  var isInitialized;
  $(document).ready(function() {
    if(!isInitialized) 
    {
      SnowProfile.main();
      isInitialized = true;
    }
  });
  
  
  // Behaviors related to Live Graph Editor
  Drupal.behaviors.sp_livegraph = {
      
    attach: function (context, settings) {
      
      // Add new layer to graph when 'Add Layer' button is clicked on the form 
      $('input[name=field_layer_add_more]', context).once( function () {
          $('input[name=field_layer_add_more]', context).mousedown(function() {
              var maxIndex = SnowProfile.snowLayers.length - 1;
              
              // Field validation?  If there is a value in the bottom depth...
              if($("[id^=edit-field-layer-und-" + maxIndex + "-field-bottom-depth-und-0-value]").val()) {
                SnowProfile.newLayer($("[id^=edit-field-layer-und-" + maxIndex + "-field-bottom-depth-und-0-value]").val());
              
              } 
              // If there's no value in bottom depth or top depth...
              /*else if(!$("#edit-field-layer-und-" + maxIndex + "-field-height-und-0-value").val()) {
                var spaceBelow = SnowProfile.pitDepth - SnowProfile.snowLayers[maxIndex].depth();
                SnowProfile.newLayer(SnowProfile.snowLayers[maxIndex].depth() + (spaceBelow / 2));
              }*/
          });
      });
      
      // Listen for text changes to form and update live graph appropriately
      $('#edit-field-layer', context).once('livegraph_connected', function () {
        $('#edit-field-layer', context).delegate( 'input', 'change', function (event) {
          // Find layer number - starts at 0, corresponds directly to SnowProfile.snowLayers[] index but not to .length
          var layerString = $(this).parents("div[class*='layer_num_']")[0].className.split(" ")[1].split("_")[2];
          var layerNum = parseInt(layerString, 10);
            
          // Top Depth was changed
          if($(this).parents('.field-name-field-height').length)
          {
            // Update layer depth value
            SnowProfile.snowLayers[layerNum].depth($(this).val());
            SnowProfile.snowLayers[layerNum].draw();
            // If not the top layer, update the layer above
            if(layerNum != 0){
              SnowProfile.snowLayers[layerNum - 1].draw();
            }
            SnowProfile.layout();
          }
          // Bottom Depth was changed
          if($(this).parents('.field-name-field-bottom-depth').length)
          {
            // If not last layer, update the layer below depth value
            if((layerNum + 1) != SnowProfile.snowLayers.length){
              SnowProfile.snowLayers[(layerNum + 1)].depth($(this).val());
              SnowProfile.snowLayers[(layerNum + 1)].draw();
              SnowProfile.snowLayers[layerNum].draw();
            }
            SnowProfile.layout();
          }
          // Stop Event 
          event.stopPropagation();
        });
        $('#edit-field-layer', context).delegate( 'select', 'change', function (event) {
          var layerString = $(this).parents("div[class*='layer_num_']")[0].className.split(" ")[1].split("_")[2];
          var layerNum = parseInt(layerString, 10);
          // Primary Hardness Selector
          if($(this).parents('.field-name-field-hardness').length)
          {
            SnowProfile.snowLayers[layerNum].handleTouchState(true);
            SnowProfile.snowLayers[layerNum].features().hardness($(this).val());
            if(!(SnowProfile.snowLayers[layerNum].slopeHandleTouchState())){
              SnowProfile.snowLayers[layerNum].features().hardness2($(this).val());
            }
            SnowProfile.snowLayers[layerNum].draw();
          }
          // Secondary Hardness Selector
          if($(this).parents('.field-name-field-hardness2').length)
          {
            SnowProfile.snowLayers[layerNum].slopeHandleTouchState(true);
            SnowProfile.snowLayers[layerNum].features().hardness2($(this).val());
            SnowProfile.snowLayers[layerNum].draw();
          }
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
