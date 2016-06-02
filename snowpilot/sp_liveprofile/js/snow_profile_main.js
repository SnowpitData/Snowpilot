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
})(jQuery);

// Configure Emacs for Drupal JavaScript coding standards
// Local Variables:
// js2-basic-offset: 2
// indent-tabs-mode: nil
// fill-column: 78
// show-trailing-whitespace: t
// End:
