/**
 * @file Defines the namespace and configuration for the snow profile editor.
 * @copyright Walt Haas <haas@xmission.com>
 * @license {@link http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GPLv2}
 * Modified by Joe DeBruycker for snowpilot.org
 */
 
 /**
 * The svg.js library
 * @external SVG
 * @see {@link http://http://documentup.com/wout/svg.js svg.js}
 */
/* global SVG */
 
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
     * Default min temperature in Fahrenheit 
     * @const {number}
     * @memberof SnowProfile
     * @see SnowProfile.temperatureGroup
     */
    DEFAULT_MIN_TEMP_F: 20,
    
    /**
     * Default min temperature in Celcius 
     * @const {number}
     * @memberof SnowProfile
     * @see SnowProfile.temperatureGroup
     */
    DEFAULT_MIN_TEMP_C: -10,
    
    /**
     * Default max temperature in Fahrenheit 
     * @const {number}
     * @memberof SnowProfile
     * @see SnowProfile.temperatureGroup
     */
    DEFAULT_MAX_TEMP_F: 32,
    
    /**
     * Default max temperature in Celcius 
     * @const {number}
     * @memberof SnowProfile
     * @see SnowProfile.temperatureGroup
     */
    DEFAULT_MAX_TEMP_C: 0,
    
    /**
     * Absolute minimum temperature acceptable for drawing on live profile 
     *
     * @const {number} 
     * @memberof SnowProfile 
     * @see SnowProfile.temperatureGroup
     */
    ABSOLUTE_MIN_TEMP: -40,

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
     // prev value = 120
    CTRLS_WD: 60,

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
    GRAIN_SPACE_WD: 15,

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
     // prev value = 240
    COMMENT_WD: 300,

    /**
     * Vertical height in pixels of the temperature (top horizontal) axis label.
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
     * Vertical height in pixels of the hardness (bottom horizontal) axis label.
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
     *  Diameter in pixels of the temperature points
     *  @memeber of SnowProfile.Cfg 
     *  @const {number} 
     */
    TEMPERATURE_SIZE: 8,

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
     *  Color of temperature plot 
     *  @memeber of SnowProfile.Cfg 
     *  @const {string}
     */
    TEMPERATURE_COLOR: "#ff0000",

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
   * Minimum x value allowed for a handle (hardness 'I+').
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
   * Calculation depends on knowing there are 18 entries in the table
   * @const {number}
   * @memberof SnowProfile
   */
  SnowProfile.Cfg.HARD_BAND_WD = (SnowProfile.Cfg.GRAPH_WIDTH -
    SnowProfile.Cfg.HANDLE_SIZE) / 17;

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
    
})(jQuery);