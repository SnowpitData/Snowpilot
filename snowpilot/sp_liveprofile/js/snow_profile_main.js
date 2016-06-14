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
      for (i = 0; i < SnowProfile.Cfg.NUM_INIT_LAYERS; i++) {
        SnowProfile.newLayer(i * SnowProfile.Cfg.INT_INIT_LAYERS);
      }
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
              var spaceBelow = SnowProfile.pitDepth - SnowProfile.snowLayers[maxIndex].depth();
              SnowProfile.newLayer(SnowProfile.snowLayers[maxIndex].depth() + (spaceBelow / 2));
              
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
