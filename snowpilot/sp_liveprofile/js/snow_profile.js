/**
 * @file Defines the namespace and configuration for the snow profile editor.
 * @copyright Walt Haas <haas@xmission.com>
 * @license {@link http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GPLv2}
 * Modified by Joe DeBruycker Q1 2016
 */

/* global SVG */

/**
 * The svg.js library
 * @external SVG
 * @see {@link http://http://documentup.com/wout/svg.js svg.js}
 */

/**
 * Constants and common functions
 * @type {object}
 * @namespace
 */
var SnowProfile = {};

/**
 * Layout of the snow profile SVG drawing:
 *
 *       | Top Labels
 * ___________________________________________________________________
 *       |             |          |       | |       |
 * Depth |             |          | Grain | | Grain |
 * Label | Graph       | Controls | Shape | | Size  | Comment
 *       |             |          |       | |       |
 *____________________________________________________________________
 *       | Hardness    |
 *       | Label       |
 */
(function($) {
  "use strict";

  SnowProfile.Cfg = {

    /**
     * Maximum snow depth in cm that can be plotted on the graph.
     * @const {number}
     * @memberof SnowProfile
     * @see SnowProfile.Grid~depthScaleGrp
     */
    MAX_DEPTH: 300,

    /**
     * Minimum snow depth in cm that can be set by the user.
     * @const {number}
     * @memberof SnowProfile
     * @see SnowProfile.Grid~depthScaleGrp
     */
    MIN_DEPTH: 10,

    /**
     * Default snow pit depth in cm
     * @const {number}
     * @memberof SnowProfile
     * @see SnowProfile.Grid~depthScaleGrp
     */
   DEFAULT_PIT_DEPTH: 150,

    /**
     * Horizontal width in pixels of the depth (vertical) axis label.
     * @const {number}
     * @memberof SnowProfile
     * @see SnowProfile.Grid~depthScaleGrp
     */
    DEPTH_LABEL_WD: 70,

    /**
     * Depth interval in cm between horizontal grid lines
     * @memberof SnowProfile
     * @const {number}
     */
    DEPTH_LINE_INT: 10,

    /**
     * Width in pixels available for plotting data.
     * @memberof SnowProfile
     * @const {number}
     */
    GRAPH_WIDTH: 350,

    /**
     * Width in pixels of the area used by buttons and
     * connectors (diagonal lines).
     * @memberof SnowProfile
     * @const {number}
     */
    CTRLS_WD: 130,

    /**
     * Width in pixels of the area used by snow grain shape
     * @memberof SnowProfile
     * @const {number}
     */
    GRAIN_SHAPE_WD: 60,

    /**
     * Width in pixels of the space between grain shape and size
     * @memberof SnowProfile
     * @const {number}
     */
    GRAIN_SPACE_WD: 5,

    /**
     * Width in pixels of the area used by snow grain size
     * @memberof SnowProfile
     * @const {number}
     */
    GRAIN_SIZE_WD: 70,

    /**
     * Font size for feature description text
     */
    FEAT_DESCR_FONT_SIZE: 14,

    /**
     * Width in pixels of the space between grain size and comment
     * @memberof SnowProfile
     * @const {number}
     */
    COMMENT_SPACE_WD: 5,

    /**
      Width in pixels of the area used by snow layer comment
     * @memberof SnowProfile
      @const {number}
     */
    COMMENT_WD: 240,

    /**
     * Vertical height in pixels of the temperature (horizontal) axis label.
     * @memberof SnowProfile
     * @const {number}
     */
    TOP_LABEL_HT: 40,

    /**
     * Minimum height in pixels of the features area for one snow layer.
     * @memberof SnowProfile
     * @const {number}
     */
    DESCR_HEIGHT: 40,

    /**
     * Vertical height in pixels of the hardness (horizontal) axis label.
     * @memberof SnowProfile
     * @const {number}
     */
    HARD_LABEL_HT: 60,

    /**
     * Size in pixels of the handle square
     * @memberof SnowProfile
     * @const {number}
     */
    HANDLE_SIZE: 11,

    /**
     * Color of the background of the graph
     * @memberof SnowProfile
     * @const {string}
     */
    BACKGROUND_COLOR: '#FFF',

    /**
     * Color of the labels and axis lines
     * @memberof SnowProfile
     * @const {string}
     */
    LABEL_COLOR: '#000',

    /**
     * Color of the outline of the chart grid
     * @memberof SnowProfile
     * @const {string}
     */
    OUTLINE_GRID_COLOR: '#000',

    /**
     * Color of the inside lines of the chart grid
     * @memberof SnowProfile
     * @const {string}
     */
    INSIDE_GRID_COLOR: '#AAA',

    /**
     * Color of the outlines of a snow layer
     * @memberof SnowProfile
     * @const {string}
     */
    LAYER_OUTLINE_COLOR: '#5A54AA',

    /**
     * Fill color inside a layer
     * @memberof SnowProfile
     * @const {string}
     */
    LAYER_FILL_COLOR: '#9A99D5',

    /**
     * Opacity of Fill color inside a layer
     * @memberof SnowProfile
     * @const {string}
     */
    LAYER_FILL_OPACITY: .85,

    /**
     * Color of a button that is not under the mouse
     * @memberof SnowProfile
     * @const {string}
     */
    BUTTON_BLUR_COLOR: "#AAA",

    /**
     * Color of a button that is under the mouse
     * @memberof SnowProfile
     * @const {string}
     */
    BUTTON_FOCUS_COLOR: "#000",

    /**
      Depth scale in pixels per cm
      @const {number}
      @memberof SnowProfile
     */
    DEPTH_SCALE: 5,

    /**
     * Minimum height of the feature description area in pixels.
     *
     * The minimum height is in effect when the feature description is
     * empty or has a height less than the minimum.
     * @const {number}
     * @memberof SnowProfile
     */
    MIN_FEAT_HEIGHT: 25,

    /**
     * Minimum number of pixels of padding above and below features desc.
     * @const {number}
     * @memberof SnowProfile
     */
    MIN_FEAT_PAD: 2,

    /**
     * Number of layers initially shown on a fresh copy of the page.
     */
    NUM_INIT_LAYERS: 1,

    /**
     * Depth interval in cm of layers initially shown on a
     * fresh copy of the page.
     */
    INT_INIT_LAYERS: 20,

    /**
     * Width in pixels of the image to be generated
     * @memberof SnowProfile
     * @const {number}
     */
    IMAGE_WD: 800
  }; // SnowProfile.Cfg = {

  /**
   * Pit depth (cm)
   *
   * Maximum depth of the pit in cm from the snow surface.  Must
   * be an integer between MIN_DEPTH and MAX_DEPTH. Default MAX_DEPTH.
   * @type {!number}
   * @see SnowProfile.Grid~depthScaleGrp
   */
  SnowProfile.pitDepth = SnowProfile.Cfg.DEFAULT_PIT_DEPTH;

    /**
     * Total depth of the snow pack (cm)
     *
     * Distance in cm from the snow surface to the ground, as measured
     *   with a calibrated probe or by digging to the ground.  Null
     *   if this distance is not known.
     * @memberof SnowProfile
     * @type {?number}
     */
  SnowProfile.totalDepth = null;

    /**
     * Maximum Y value allowed for any handle (bottom of graph area)
     *
     * @type {number}
     * @memberof SnowProfile
     * @see SnowProfile.Grid~adjustGrid
     */
  SnowProfile.handleMaxY = null;

    /**
     * Depth reference (snow surface or ground)
     *
     * A single letter indicating whether snow depth is referenced
     * to the snow surface ("s") or ground ("g").  Must be one or the
     * other.  Default is "s".  Ground reference may be used only if
     * the value of total snow depth (totalDepth) is known.
     * @memberof SnowProfile
     * @type {!string}
     * @see SnowProfile.Grid~depthScaleGrp
     */
  SnowProfile.depthRef = "s";

  /**
   * Depth increment in cm to allow when inserting a layer above
   * or below another layer.
   * @memberof SnowProfile
   * @const {number}
   */
  SnowProfile.Cfg.INS_INCR = (SnowProfile.Cfg.HANDLE_SIZE + 1) /
    SnowProfile.Cfg.DEPTH_SCALE;

  /**
   * Central x of the data plotting area.
   *
   * @const {number}
   * @memberof SnowProfile
   */
  SnowProfile.Cfg.GRAPH_CENTER_X = SnowProfile.Cfg.DEPTH_LABEL_WD + 1 +
    (SnowProfile.Cfg.GRAPH_WIDTH / 2);

  /**
   * Maximum x value allowed for a handle (hardness 'F-').
   *
   * @const {number}
   * @memberof SnowProfile
   */
  SnowProfile.Cfg.HANDLE_MAX_X = SnowProfile.Cfg.DEPTH_LABEL_WD + 
    SnowProfile.Cfg.GRAPH_WIDTH - (SnowProfile.Cfg.HANDLE_SIZE * 1.5);

  /**
   * Minimum x value allowed for a handle (hardness 'I').
   *
   * @const {number}
   * @memberof SnowProfile
   */
  SnowProfile.Cfg.HANDLE_MIN_X = SnowProfile.Cfg.DEPTH_LABEL_WD -
    (SnowProfile.Cfg.HANDLE_SIZE / 2);

  /**
   * Minimum Y value allowed for a handle (top of graph area)
   *
   * @const
   * @memberof SnowProfile
   */
  SnowProfile.Cfg.HANDLE_MIN_Y = SnowProfile.Cfg.TOP_LABEL_HT + 1;

  /**
   * Width in pixels of one hardness band in the CAAML_HARD table
   *
   * Calculation depends on knowing there are 21 entries in the table
   * @const {number}
   * @memberof SnowProfile
   */
  SnowProfile.Cfg.HARD_BAND_WD = (SnowProfile.Cfg.GRAPH_WIDTH -
    SnowProfile.Cfg.HANDLE_SIZE) / 20;

  /**
   * Horizontal width in pixels of the SVG drawing
   *
   * @const {number}
   * @memberof SnowProfile
   */
   SnowProfile.Cfg.DRAWING_WD = SnowProfile.Cfg.DEPTH_LABEL_WD + 1 +
     SnowProfile.Cfg.GRAPH_WIDTH + 1 + SnowProfile.Cfg.CTRLS_WD + 1 +
     SnowProfile.Cfg.GRAIN_SHAPE_WD + SnowProfile.Cfg.GRAIN_SPACE_WD +
     SnowProfile.Cfg.GRAIN_SIZE_WD + SnowProfile.Cfg.COMMENT_SPACE_WD +
     SnowProfile.Cfg.COMMENT_WD;

  /**
   * Initial X position of the layer handle
   *
   * This X position centers the handle over the Right edge of the grid
   */
  SnowProfile.Cfg.HANDLE_INIT_X = SnowProfile.Cfg.DEPTH_LABEL_WD + 1 + SnowProfile.Cfg.GRAPH_WIDTH -
    (SnowProfile.Cfg.HANDLE_SIZE / 2);

  /**
   * X position of the center line of the insert buttons in the control area
   */
  SnowProfile.Cfg.INS_BUTTON_X = SnowProfile.Cfg.DEPTH_LABEL_WD + 1 +
    SnowProfile.Cfg.GRAPH_WIDTH + 65;

  /**
   * X position of the center line of the edit buttons in the control area
   */
  SnowProfile.Cfg.EDIT_BUTTON_X = SnowProfile.Cfg.DEPTH_LABEL_WD + 1 +
    SnowProfile.Cfg.GRAPH_WIDTH + 90;

  /**
   * X position of the left edge of the layer description
   *
   * @const {number}
   * @memberof SnowProfile
   */
  SnowProfile.Cfg.FEAT_DESCR_LEFT = SnowProfile.Cfg.DEPTH_LABEL_WD + 1 +
    SnowProfile.Cfg.GRAPH_WIDTH + 1 + SnowProfile.Cfg.CTRLS_WD;

  SnowProfile.Cfg.FEAT_DESCR_WD = SnowProfile.Cfg.GRAIN_SHAPE_WD +
    SnowProfile.Cfg.GRAIN_SPACE_WD + SnowProfile.Cfg.GRAIN_SIZE_WD +
    SnowProfile.Cfg.COMMENT_SPACE_WD + SnowProfile.Cfg.COMMENT_WD;


  /**
   * X position of the left edge of the Grain icons area
   * within the layer description group
   *
   * @const {number}
   * @memberof SnowProfile
   */
  SnowProfile.Cfg.GRAIN_ICON_LEFT = 0;

  /**
   * X position of the left edge of the Grain size area
   * within the layer description group
   *
   * @const {number}
   * @memberof SnowProfile
   */
  SnowProfile.Cfg.GRAIN_SIZE_LEFT = SnowProfile.Cfg.GRAIN_ICON_LEFT +
    SnowProfile.Cfg.GRAIN_SHAPE_WD + SnowProfile.Cfg.GRAIN_SPACE_WD;

  /**
   * X position of the left edge of the Comment text area
   * @const {number}
   * @memberof SnowProfile
   */
  SnowProfile.Cfg.COMMENT_LEFT = SnowProfile.Cfg.GRAIN_SIZE_LEFT +
    SnowProfile.Cfg.GRAIN_SIZE_WD + SnowProfile.Cfg.COMMENT_SPACE_WD;

  /**
   * Snow stratigraphy snow layers.
   *
   * The SnowProfile.Layer objects are referenced by this array which is
   * ordered by the depth of the layer.  The top layer(depth == 0) is always
   * referenced by snowLayers[0].  When a Layer object is created or
   * removed, it is spliced into the array so that the order of depths is
   * maintained, and the indexes of the layers below increment by 1.  When
   * the depth of a layer is changed by the user, the new depth is
   * constrained to be less than the depth of the layer above and more than
   * the depth of the layer below in the snow pack.
   * @memberof SnowProfile
   * @type {Array.<SnowProfile.Layer>}
   */
  SnowProfile.snowLayers = [];

  /**
   * Make the handle visible if it has not been touched.
   * @memberof SnowProfile
   * @type {boolean}
   */
  SnowProfile.showHandle = null;

  /**
   * Previous state of showHandle.
   * @memberof SnowProfile
   * @type {boolean}
   */
  SnowProfile.oldShowHandle = null;

  /**
   * Vertical height in pixels of the grid (left side) of the SVG drawing.
   *
   * This height is the pixel equivalent of the pit depth set by the user,
   * plus the height of the various labels.
   * @method
   * @memberof SnowProfile
   * @returns {number} Drawing height in pixels.
   */
  SnowProfile.gridHeight = function() {
    var height = SnowProfile.Cfg.TOP_LABEL_HT + 1 + (SnowProfile.pitDepth *
      SnowProfile.Cfg.DEPTH_SCALE) + 1 + SnowProfile.Cfg.HARD_LABEL_HT;
    return height;
  };

  /**
   * Vertical height in pixels of the features (right side) of the drawing.
   *
   * This height is the sum of the pixel heights of the features of each
   * snow layer.
   * @method
   * @memberof SnowProfile
   * @returns {number} Drawing height in pixels.
   */
  SnowProfile.featuresHeight = function() {

    var i,
      sum = SnowProfile.Cfg.TOP_LABEL_HT + 1 + SnowProfile.Cfg.DESCR_HEIGHT;
    for (i = 0; i < SnowProfile.snowLayers.length; i++) {
      sum += SnowProfile.snowLayers[i].features().height;
    }
    return sum;
  };

  /**
   * Vertical height in pixels of the SVG drawing
   *
   * @method
   * @memberof SnowProfile
   * @returns {number} Drawing height in pixels.
   */
  SnowProfile.setDrawingHeight = function() {
    var max,
      numLayers = SnowProfile.snowLayers.length;

    if (numLayers === 0) {
      // No snow layers so drawing height set by configuration constants
      max = SnowProfile.gridHeight();
    }
    else {
      // Drawing height is max of configuration constants and space
      // needed to store feature descriptions
      max = Math.max(SnowProfile.gridHeight(),
        SnowProfile.snowLayers[numLayers - 1].features().lineBelowY());
    }
    SnowProfile.drawing.size(SnowProfile.Cfg.DRAWING_WD, max);
    SnowProfile.diagram.setAttribute('height', max + 10);
  };

  /**
   * Convert a hardness code to an X axis position.
   * @param {string} code A CAAML hardness code from the CAAML_HARD table.
   * @returns {number} X axis position
   */
  SnowProfile.code2x = function(code) {
    var x = SnowProfile.Cfg.DEPTH_LABEL_WD + SnowProfile.Cfg.GRAPH_WIDTH - (SnowProfile.Cfg.HANDLE_SIZE / 2);
    if (code !== null) {
      for (var i = 0; i < SnowProfile.CAAML_HARD.length; i++) {
        var tmp = 20 - i;
        if (code === SnowProfile.CAAML_HARD[i][0]) {
          x = SnowProfile.Cfg.DEPTH_LABEL_WD +
            (SnowProfile.Cfg.HARD_BAND_WD * tmp) -
            (SnowProfile.Cfg.HANDLE_SIZE / 2);
          break;
        }
      }
    }
    return x;
  };

  /**
   * Convert an X axis position to a hardness code
   * @param {number} x X axis position.
   * @returns {string} CAAML hardness code.
   */
  SnowProfile.x2code = function(x) {
    var code = 'I',
      leftSide,
      bandLeft,
      bandRight;

    for (var i = 0; i < SnowProfile.CAAML_HARD.length - 1; i++) {
      var tmp = 19 - i;
      leftSide = SnowProfile.Cfg.DEPTH_LABEL_WD + 1;
      bandLeft = leftSide + (SnowProfile.Cfg.HARD_BAND_WD * tmp) +
          (SnowProfile.Cfg.HANDLE_SIZE / 2);
      bandRight = leftSide + (SnowProfile.Cfg.HARD_BAND_WD * (tmp + 1)) +
          (SnowProfile.Cfg.HANDLE_SIZE / 2);
      if ((x >= (bandLeft - (SnowProfile.Cfg.HARD_BAND_WD / 2))) &&
         (x < (bandRight - (SnowProfile.Cfg.HARD_BAND_WD / 2)))) {
        code = SnowProfile.CAAML_HARD[i][0];
        break;
      }
    }
    return code;
  };

  /**
   * Convert a Y axis position to a depth in cm.
   * @param {number} y Y axis position.
   * @returns {number} Depth of this layer in cm.
   */
  SnowProfile.y2depth = function(y) {
    return (y - SnowProfile.Cfg.HANDLE_MIN_Y) / SnowProfile.Cfg.DEPTH_SCALE;
  };

  /**
   * Convert a snow layer depth value to a drawing Y axis position
   *
   * @method
   * @memberof SnowProfile
   * @param {number} depth Depth from the snow surface in cm.
   * @returns {number} Y position.
   */
  SnowProfile.depth2y = function(depthArg) {
    var y = (depthArg * SnowProfile.Cfg.DEPTH_SCALE) +
      SnowProfile.Cfg.HANDLE_MIN_Y;
    return y;
  };

  /**
   * Position the feature description and connecting lines on the drawing.
   *
   * Start at the snow surface (layer 0).  Position that layer based on the
   * position of the handle and the size of the feature description.  With
   * that fixed, iterate down the snowpack.
   */
  SnowProfile.layout = function() {
    var height,
      i,
      featureBottom,
      featureTop,
      layerBottom,
      layerTop;

    // Iterate through snow layers from top down
    for (i = 0; i < SnowProfile.snowLayers.length; i++) {
      // Y value of the top of this layer
      layerTop = SnowProfile.depth2y(SnowProfile.snowLayers[i].depth());

      // Y value of the bottom of this layer
      if (i === (SnowProfile.snowLayers.length - 1)) {
        // This layer is the bottom layer, so the bottom
        // of this layer is the bottom of the pit.
        layerBottom = SnowProfile.depth2y(SnowProfile.pitDepth);
      }
      else {
        // This layer is NOT the bottom layer, so the bottom
        // of this layer is the top of the layer below.
        layerBottom = SnowProfile.depth2y(
          SnowProfile.snowLayers[i + 1].depth());
      }

      // Y value of the top of the layer feature description
      if (i === 0) {
        // This layer is the top layer, so the top of the feature
        // description area is the top of the grid.
        featureTop = SnowProfile.Cfg.TOP_LABEL_HT + 1;
      }
      else {
        // This layer is NOT the top layer, so the top of the feature
        // description area is the line below the feature description
        // for the layer above.
        featureTop = SnowProfile.snowLayers[i - 1].features().lineBelowY();
      }

      // The bottom of the features description area is the lower of the
      // bottom of the layer and the space needed for the features description
      // bounding box (greater Y value is lower on the drawing).
      height = SnowProfile.snowLayers[i].features().height;
      if ((height + (2 * SnowProfile.Cfg.MIN_FEAT_PAD)) <
        SnowProfile.Cfg.MIN_FEAT_HEIGHT) {
        height = SnowProfile.Cfg.MIN_FEAT_HEIGHT;
      }
      else {
        height += 2 * SnowProfile.Cfg.MIN_FEAT_PAD;
      }
      // height is the number of pixels to allocate for feature description
      featureBottom = Math.max(layerBottom, (featureTop + height));

      // Center the layer's insert button on the top line
      //SnowProfile.snowLayers[i].insertButton.setCy(
      //  Math.max(layerTop, featureTop));

      // Draw the line below the bottom of the features description.
      SnowProfile.snowLayers[i].features().lineBelowY(featureBottom);

      // Draw the diagonal line from layerBottom to lineBelow
      SnowProfile.snowLayers[i].setDiagLine();

      // It's possible that the bottom of the features description area is
      // below the bottom of the SVG drawing area, in which case we need to
      // expand the size of the drawing appropriately.
      SnowProfile.setDrawingHeight();

      // Position the features description in the center of its area.
      SnowProfile.snowLayers[i].features().layout(featureTop, featureBottom);
    }
  };

  /**
   * Tell listeners to hide anything that should not appear on
   * the final image.
   *
   * This event is fired just before the image is generated
   * from the drawing.
   * @event SnowProfileHideControls
   * @memberof SnowProfile
   * @type {string}
   */

  /**
   * Tell listeners to show controls hidden from the image.
   *
   * This event is fired after the image has been generated from
   * the canvas.  It tells listeners they should show anything that was
   * hidden from the image.
   * @event SnowProfileShowControls
   * @memberof SnowProfile
   * @type {string}
   */

  /**
   * Tell listeners the reference grid has changed.
   *
   * This event is fired when the user changes a parameter that
   * governs the reference grid.  It tells listeners to respond to the
   * new grid parameters by adjusting their data display.
   * @event SnowProfileDrawGrid
   * @memberof SnowProfile
   * @type {string}
   * @see SnowProfile.Grid
   */

  /**
   * Tell listeners that a SnowProfile.Button has been clicked
   *
   * @event SnowProfileButtonClick
   * @memberof SnowProfile
   * @type {Object}
   */

  /**
   * Produce a preview PNG in a new window
   *
   * @method
   * @memberof SnowProfile
   * @fires ShowProfileHideControls
   * @fires ShowProfileShowControls
   */
  SnowProfile.preview = function() {

    var saveWidth, saveHeight;

    // Hide the controls so they won't show in the PNG
    $.event.trigger("SnowProfileHideControls");

    // Scale the drawing to desired image size.
    var scaleFactor = SnowProfile.Cfg.IMAGE_WD / SnowProfile.drawing.width();
    saveWidth = SnowProfile.drawing.width();
    saveHeight = SnowProfile.drawing.height();
    SnowProfile.mainGroup.scale(scaleFactor);
    SnowProfile.drawing.size(SnowProfile.Cfg.DRAWING_WD * scaleFactor,
      SnowProfile.drawing.height() * scaleFactor);
    var svg = SnowProfile.diagram.firstChild;
    svg.toDataURL("image/png", {
      callback: function(data) {

        // Open a new window and show the PNG in it
        var newWin = window.open(data, "_blank");
        if (newWin === null) {
          alert("You must enable pop-ups for this site to use" +
            " the Preview button");
        }

        // Restore normal drawing scale.
        SnowProfile.drawing.width(saveWidth);
        SnowProfile.drawing.height(saveHeight);
        SnowProfile.mainGroup.scale(1);
        $.event.trigger("SnowProfileShowControls");
      }
    });

    // Prevent bubbling of this event.
    return false;
  };

  /**
   * Create a new snow layer with associated Features object
   */
  SnowProfile.newLayer = function(depth) {
    var layer = new SnowProfile.Layer(depth);
    var features = new SnowProfile.Features(layer);
    layer.features(features);
    layer.draw();
    SnowProfile.layout();
    SnowProfile.ctrlsGroup.front();
  };

  /**
   * Export the snow profile data into the form.
   *
   * Called when the "Save" button is clicked.
   * FIXME deal with other than 3 layers
   */
  SnowProfile.export = function() {
    var i;
    $("input[name='pit_depth']").val(SnowProfile.pitDepth);
    $("input[name='total_depth']").val(SnowProfile.totalDepth);
    $("input[name='depth_ref']").val(SnowProfile.depthRef);
    $("input[name='num_layers']").val(3);
    for (i = 0; i < 3; i++) {
      $("input[name='layer[" + i +"][depth]']").val(SnowProfile.snowLayers[i].depth());
      var describe = SnowProfile.snowLayers[i].features().describe();
      $("input[name='layer[" + i +"][hardness]']")
        .val(SnowProfile.snowLayers[i].features().hardness());
      var describe = SnowProfile.snowLayers[i].features().describe();
      $("input[name='layer[" + i +"][primaryGrainShape]']")
        .val(describe.primaryGrainShape);
      $("input[name='layer[" + i +"][primaryGrainSubShape]']")
        .val(describe.primaryGrainSubShape);
      $("input[name='layer[" + i +"][secondaryGrainShape]']")
        .val(describe.secondaryGrainShape);
      $("input[name='layer[" + i +"][secondaryGrainSubShape]']")
        .val(describe.secondaryGrainSubShape);
      $("input[name='layer[" + i +"][grainSizeMin]']")
        .val(describe.grainSizeMin);
      $("input[name='layer[" + i +"][grainSizeMax]']")
        .val(describe.grainSizeMax);
      $("input[name='layer[" + i +"][comment]']").val(describe.comment);
    }
  };

  /**
   * Intercept an ENTER key and replace SUBMIT with a change event
   *
   * This is used to prevent the ENTER key in certain text input fields
   * from submitting the form.  The change event causes whatever was typed
   * in the input field to take effect.
   *
   * @method
   * @memberof SnowProfile
   * @fires change
   * @param {object} event Event representing what was entered in the
   *   input field.
   */
  SnowProfile.blockSubmit = function(event) {
    if (event.type === "keydown" && event.which === 13) {
      event.preventDefault();
      $(event.target).trigger("change");
    }
  };

  /**
   * Initialize the SVG drawing and the grid group
   *
   * @method
   * @memberof SnowProfile
   * @fires SnowProfileHideControls
   * @listens SnowProfileButtonClick
   */
  SnowProfile.init = function() {

    /**
     * Default tooltip style
     */
    Opentip.defaultStyle = "glass";

    /**
     * SVG drawing
     *
     * @see  {@link http://http://documentup.com/wout/svg.js#usage/create-a-svg-document Create a SVG document}
     * @type {Object}
     * @memberof SnowProfile
     */
    SnowProfile.diagram = $('#snow_profile_diagram').get(0);
    SnowProfile.drawing = SVG("snow_profile_diagram");
    SnowProfile.diagram.setAttribute('width', SnowProfile.Cfg.DRAWING_WD + 10);
    SnowProfile.mainGroup = SnowProfile.drawing.group()
      .attr('id', 'snow_profile_main_g');

    // For debugging, show the bounding box
    // SnowProfile.drawingBox = SnowProfile.drawing.rect(0, 0)
    //   .style({
    //      "fill-opacity": 0,
    //      stroke: 'red'
    //   });
    // SnowProfile.mainGroup.add(SnowProfile.drawingBox);

    /**
     * SnowProfile drawing controls group
     *
     * This SVG group holds the controls which the user uses to manipulate the
     * drawing.  SVG doesn't have a Z axis, and an element in front can block
     * access to an element behind.  The only way to guarantee access to the
     * controls is to re-order the elements in the document to bring the contols
     * to the front without reordering individual controls, hence we put them
     * all in a group that can be brought to the front as a unit.
     * @see  {@link http://http://documentup.com/wout/svg.js#parent-elements/groups Groups}
     * @type {object}
     * @memberof SnowProfile
     */
    SnowProfile.ctrlsGroup = SnowProfile.drawing.group()
      .addClass('snow_profile_ctrls');
    SnowProfile.mainGroup.add(SnowProfile.ctrlsGroup);

    /**
     * SnowProfile drawing handles group
     *
     * Handles, ordered as the snow layers are.
     * @see  {@link http://http://documentup.com/wout/svg.js#parent-elements/groups Groups}
     * @type {object}
     * @memberof SnowProfile
     */
    SnowProfile.handlesGroup = SnowProfile.drawing.group()
      .addClass('snow_profile_ctrls_handles');
    SnowProfile.ctrlsGroup.add(SnowProfile.handlesGroup);

    /**
     * SnowProfile drawing edit buttons group
     *
     * Edit buttons, ordered as the snow layers are.
     * @see  {@link http://http://documentup.com/wout/svg.js#parent-elements/groups Groups}
     * @type {object}
     * @memberof SnowProfile
     */
    SnowProfile.editGroup = SnowProfile.drawing.group()
      .addClass('snow_profile_ctrls_edit');
    SnowProfile.ctrlsGroup.add(SnowProfile.editGroup);

    /**
     * Pencil symbol used by the edit button.
     * @memberof SnowProfile
     */
    SnowProfile.pencil = SnowProfile.drawing.defs()
      .path("M 16.875,4.4 C 18.60063,4.4 20,5.7993755 20,7.525 20,8.2287506 19.7675,8.8774995 19.375,9.4 L 18.125,10.65 13.75,6.275 15,5.025 C 15.5225,4.6325 16.171251,4.4 16.875,4.4 z M 1.25,18.775 0,24.4 5.625,23.15 17.1875,11.587506 12.8125,7.2125 1.25,18.775 z m 12.726251,-7.273755 -8.750001,8.75 -1.0775,-1.07749 8.749999,-8.750001 1.077502,1.077491 z")
      .addClass('snow_profile_ctrls_edit');

    /**
     * Plus symbol used by the insert button.
     * @memberof SnowProfile
     */
    SnowProfile.plus = SnowProfile.drawing.defs()
      .path("M 19.375,13.805085 H 12.5 v -6.875 c 0,-0.345 -0.28,-0.625 -0.625,-0.625 H 8.1249998 c -0.3449999,0 -0.6249999,0.28 -0.6249999,0.625 v 6.875 H 0.62499999 c -0.345,0 -0.62499999,0.28 -0.62499999,0.625 v 3.75 c 0,0.345 0.27999999,0.625 0.62499999,0.625 H 7.4999999 v 6.875 c 0,0.344999 0.28,0.625 0.6249999,0.625 H 11.875 c 0.345,0 0.625,-0.280001 0.625,-0.625 v -6.875 h 6.875 c 0.344999,0 0.625,-0.28 0.625,-0.625 v -3.75 c 0,-0.345 -0.280001,-0.625 -0.625,-0.625 z")
      .addClass('snow_profile_ctrls_insert');

    /**
     * SnowProfile drawing insert buttons group
     *
     * Insert buttons, ordered as the snow layers are.
     * @see  {@link http://http://documentup.com/wout/svg.js#parent-elements/groups Groups}
     * @type {object}
     * @memberof SnowProfile
     */
    SnowProfile.insertGroup = SnowProfile.drawing.group()
      .addClass('snow_profile_ctrls_insert');
    SnowProfile.ctrlsGroup.add(SnowProfile.insertGroup);

    /**
     * SnowProfile drawing grid group
     *
     * This SVG group holds the grid to which the snow layers are referenced.
     * Whenever the pit depth or total snow depth are changed by the user, this
     * group is cleared and recreated.
     * @see  {@link http://http://documentup.com/wout/svg.js#parent-elements/groups Groups}
     * @type {object}
     * @memberof SnowProfile
     */
    SnowProfile.gridGroup = SnowProfile.drawing.group()
      .addClass("snow_profile_grid");
    SnowProfile.mainGroup.add(SnowProfile.gridGroup);

    /**
     * Populate the selects from the CAAML tables
     * @function
     */

    var code;

    // Populate the grain shape <select>s in the layer description pop-up
    for (code in SnowProfile.CAAML_SHAPE) {
      if (SnowProfile.CAAML_SHAPE.hasOwnProperty(code)) {
        $("#snow_profile_primary_grain_shape").append("<option value=\"" +
          code + "\">" + SnowProfile.CAAML_SHAPE[code].text + "</option>");
        $("#snow_profile_secondary_grain_select").append("<option value=\"" +
          code + "\">" + SnowProfile.CAAML_SHAPE[code].text + "</option>");
      }
    }

    // Create the <select>s for the grain subshape from the CAAML_SUBSHAPE
    // table.
    var primary_opts = "",
      secondary_opts = "";
    for (var shape in SnowProfile.CAAML_SUBSHAPE) {
      if (SnowProfile.CAAML_SUBSHAPE.hasOwnProperty(shape)) {
        primary_opts += "<select id=\"snow_profile_primary_grain_subshape_" +
          shape + "\" style=\"display: none\"><option value=\"\"" +
          " selected=\"selected\"></option>";
        secondary_opts +=
          "<select id=\"snow_profile_secondary_grain_subshape_" +
          shape + "\" style=\"display: none\"><option value=\"\"" +
          " selected=\"selected\"></option>";
        for (var subShape in SnowProfile.CAAML_SUBSHAPE[shape]) {
          if (SnowProfile.CAAML_SUBSHAPE[shape].hasOwnProperty(subShape)) {
            primary_opts += "<option value=\"" + subShape + "\">" +
              SnowProfile.CAAML_SUBSHAPE[shape][subShape].text + "</option>";
            secondary_opts += "<option value=\"" + subShape + "\">" +
              SnowProfile.CAAML_SUBSHAPE[shape][subShape].text + "</option>";
          }
        }
        primary_opts += "</select>";
        secondary_opts += "</select>";
      }
    }
    $("#snow_profile_primary_grain_subshape").append(primary_opts);
    $("#snow_profile_secondary_grain_subshape").append(secondary_opts);

    // Add the reference grid to the SVG drawing
    new SnowProfile.Grid();

    $(document).ready(function() {

      // When a character is entered into the snow profile total depth
      // field, replace ENTER with a change event.
      $("#snow_profile_total_depth")
        .bind("keydown", function(event) {SnowProfile.blockSubmit(event)});

      // When a character is entered into the snow profile pit depth
      // field, replace ENTER with a change event.
      $("#snow_profile_pit_depth")
        .bind("keydown", function(event) {SnowProfile.blockSubmit(event)});

      // When the "Preview" button is clicked, generate a preview.
      $("#snow_profile_preview").click(function(event) {
        SnowProfile.preview(event);

        // Prevent this event from bubbling up the stack.
        return false;
      });

      // When the "Save" button is clicked, call SnowProfile.export()
      // to copy data to the form before submission.
      $("#edit-submit").click(function() {
        SnowProfile.export();
      });
      
      $('input[name=field_layer_add_more]').mousedown(function() {
        var maxIndex = SnowProfile.snowLayers.length - 1;
        var spaceBelow = SnowProfile.pitDepth - SnowProfile.snowLayers[maxIndex].depth();
        SnowProfile.newLayer(SnowProfile.snowLayers[maxIndex].depth() + (spaceBelow / 2));
        //alert(SnowProfile.snowLayers[0].depth());
      });
      
      // Testing event handlers for updating editor from text input 
      /*$("#my_test_input").change(function () {
          SnowProfile.snowLayers[1].depth($(this).val());
          SnowProfile.snowLayers[1].draw();
          //alert($(this).val());  
      });*/

    });


  };  // function SnowProfile.init();
})(jQuery);

// Configure Emacs for Drupal JavaScript coding standards
// Local Variables:
// js2-basic-offset: 2
// indent-tabs-mode: nil
// fill-column: 78
// show-trailing-whitespace: t
// End:
