/*

   Use this file for js functions related to , e.g., hiding select dropdown



// Primary grain type needs to be changed to 'select list' from 'Hierarchical select' widget to properly hide using this-
//
//
//  Also, this only works for first load, and on the first layer; 
//  Needs to be generalized.
*/

(function ($) {
  // Behaviors related to Grain types dropdown
  Drupal.behaviors.snowpilot3 = {
    attach: function (context, settings) {
      $('#edit-field-layer-und-0-field-grain-type-und').hide();
    }    // end of attach

  };  //end of Drupal.behavior.snowpilot3
}) (jQuery);

/////////////////////////////
