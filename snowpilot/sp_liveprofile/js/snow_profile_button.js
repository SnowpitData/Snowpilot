/**
 * @file Holds code to define a button object constructed from SVG shapes
 * to get more control over the size and location of the button than we can
 * get by using an HTML <button>button</button>.
 * @copyright Walt Haas <haas@xmission.com>
 * @license {@link http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GPLv2}
 */

/* global SnowProfile */

(function($) {
  "use strict";

  /**
   * @classdesc Define a button constructed from SVG shapes.
   * @constructor
   * @param {string} type "edit" or "insert"
   * @listens SnowProfileHideControls
   * @listens SnowProfileShowControls
   * @fires SnowProfileButtonClick
  */
  SnowProfile.Button = function(type) {

    /**
     * Button symbol
     * @type {Object}
     * @private
     */
    var button;

    /**
     * Return a reference to button
     */
    function getButton() {
      return button;
    }

    // /**
    //  * Define the text of the button
    //  * @type {Object}
    //  * @private
    //  */
    // var text = SnowProfile.drawing.text(textArg)
    //   .font({
    //     family: "sans-serif",
    //     size: 12,
    //     fill: "#000",
    //     stroke: 1
    //   })
    //   .cx((textArg === 'Edit') ?
    //     SnowProfile.Cfg.EDIT_BUTTON_X : SnowProfile.Cfg.INS_BUTTON_X);
    // buttonGroup.add(text);

    // // Draw a rectangle around the text
    // var button = SnowProfile.drawing.rect(text.bbox().width + 4,
    //   text.bbox().height + 4)
    //   .cx((textArg === 'Edit') ?
    //     SnowProfile.Cfg.EDIT_BUTTON_X : SnowProfile.Cfg.INS_BUTTON_X)
    //   .style({
    //     "stroke-width": 1,
    //     stroke: "#000",
    //     "stroke-opacity": 1,
    //     fill: "#fff",
    //     "fill-opacity": 0
    //   })
    //   .radius(4);

    switch (type) {
      case 'edit':
        button = SnowProfile.drawing.use(SnowProfile.pencil)
          .addClass('snow_profile_button')
          .addClass('snow_profile_button_edit')
          .cx(SnowProfile.Cfg.EDIT_BUTTON_X);
          new Opentip('#' + button.node.id, "Edit description",
            "", {target: true, tipJoint: "right"});
          break;

      case 'insert':
        button = SnowProfile.drawing.use(SnowProfile.plus)
          .addClass('snow_profile_button')
          .addClass('snow_profile_button_insert')
          .cx(SnowProfile.Cfg.INS_BUTTON_X);
          new Opentip('#' + button.node.id, "Insert layer",
            "", {target: true, tipJoint: "right"});
        break;

      default:
        throw new Error('unknown button type ' + type);
    }
    button.style({
      "stroke-width": 1,
      stroke: SnowProfile.Cfg.BUTTON_BLUR_COLOR,
      fill: SnowProfile.Cfg.BUTTON_BLUR_COLOR,
      "fill-opacity": 1
    });

    /**
     * When mouse hovers over button, change cursor to pointer
     *
     * @callback
     */
    button.mouseover(function() {
      button.style({
        cursor: 'pointer',
        stroke: SnowProfile.Cfg.BUTTON_FOCUS_COLOR,
        fill: SnowProfile.Cfg.BUTTON_FOCUS_COLOR
      });
    });
    button.mouseout(function() {
      button.style({
        stroke: SnowProfile.Cfg.BUTTON_BLUR_COLOR,
        fill: SnowProfile.Cfg.BUTTON_BLUR_COLOR
      });
    });

    /**
     * Hide this button.
     * @private
     */
    function hideButton() {
      button.hide();
    }

    /**
     * Show this button.
     * @private
     */
    function showButton() {
      button.show();
    }

    /**
     * Reposition the button on the Y axis
     * @param {number} y - New vertical position of the center of the button
     * @public
     */
    function setCy(y) {

      // It seems that button.y() and button.cy() produce the same Y position
      // of the button, at least using Chrome and Firefox.  This ugly hack
      // relies on the 22px height of the button to acheive the effect desired
      // by calling button.cy().
      button.y(y - 11);
    }

    /**
     * Reposition the button on the Y axis
     * @param {number} y - New vertical position of the top of the button
     * @public
     */
    function setY(y) {
      button.y(y);
    }

    /**
     * Destroy the button
     * @public
     */
    function destroy() {
      button.off('click');
      $(document).unbind("SnowProfileHideControls", hideButton);
      $(document).unbind("SnowProfileShowControls", showButton);
      button.remove();
    }

    /**
     * Define the new object
     */
    var newObj = {
      destroy: destroy,
      setCy: setCy,
      setY: setY,
      getButton: getButton
    };

    // Listen for "SnowProfileHideControls" events
    $(document).bind("SnowProfileHideControls", hideButton);

    // Listen for "SnowProfileShowControls" events
    $(document).bind("SnowProfileShowControls", showButton);

    // Listen for mouse clicks on this button, then emit a custom event
    // which identifies which button was clicked.
    button.click(function(evt) {
      $.event.trigger("SnowProfileButtonClick", {buttonObj: newObj});
    });

    return newObj;
  };
})(jQuery);

// Configure Emacs for Drupal JavaScript coding standards
// Local Variables:
// js2-basic-offset: 2
// indent-tabs-mode: nil
// fill-column: 78
// show-trailing-whitespace: t
// End:
