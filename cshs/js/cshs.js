/**
 * @file
 * Behavior which initializes the simplerSelect jQuery Plugin.
 */

(function ($) {
  'use strict';
  Drupal.behaviors.cshs = {
    attach: function (context, settings) {
      $('select.simpler-select-root', context)
        .once('cshs')
        .each(function(idx, element) {
          var $field = $(element);
          var default_settings = {};

          var id =  $(this).attr('id');

          // See if we got settings from Drupal for this field.
          if (typeof settings.cshs !== 'undefined' && typeof settings.cshs[id] !== 'undefined') {
            default_settings = settings.cshs[id];
          }

          // Initialize the jQuery plugin.
          $field.simplerSelect(default_settings);
        }
      );
    }
  };
}(jQuery));
