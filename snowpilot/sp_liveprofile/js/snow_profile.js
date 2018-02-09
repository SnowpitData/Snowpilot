/**
 * @file Defines the primary functions of the snow profile editor.
 * @copyright Walt Haas <haas@xmission.com>
 * @license {@link http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GPLv2}
 * Modified by Joe DeBruycker for snowpilot.org
 */

(function($) {
  "use strict";
  
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
  SnowProfile.totalDepth = SnowProfile.Cfg.DEFAULT_PIT_DEPTH;
  
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
  * Snowpit Stability Tests
  *
  * An array to hold Stability Test objects that represent
  * the stability tests performed on a snowpit.  Each test is indexed in the array
  * by the associated test number from the SnowPilot form.  
  *
  * @memberof SnowProfile
  * @type {Array}
  */
  SnowProfile.stabilityTests = [];
  
  /**
  * Snowpit Temperature Profile
  *
  * An array to hold Temperature measurements taken at different depths in the 
  * snowpit.  Each temperature object contains a temperature and a depth.
  *
  * @memberof SnowProfile
  * @type {Array}
  */
  SnowProfile.temperatureData = [];

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
        var tmp = (SnowProfile.CAAML_HARD.length - 1) - i;
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
    var code = 'F-',
      leftSide,
      bandLeft,
      bandRight;

    for (var i = 0; i < SnowProfile.CAAML_HARD.length; i++) {
      var tmp = (SnowProfile.CAAML_HARD.length - 2) - i;
      leftSide = SnowProfile.Cfg.DEPTH_LABEL_WD + 1;
      bandLeft = leftSide + (SnowProfile.Cfg.HARD_BAND_WD * tmp) +
          (SnowProfile.Cfg.HANDLE_SIZE / 2);
      bandRight = leftSide + (SnowProfile.Cfg.HARD_BAND_WD * (tmp + 1)) +
          (SnowProfile.Cfg.HANDLE_SIZE / 2);
      if ((x >= (bandLeft )) &&
         (x < (bandRight ))) {
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

      // Draw the line below the bottom of the features description, except on hidden layer
      if (i < (SnowProfile.snowLayers.length - 1)) {
        SnowProfile.snowLayers[i].features().lineBelowY(featureBottom);
      }

      // Draw the diagonal line from layerBottom to lineBelow, except on hidden layer
      if (i < (SnowProfile.snowLayers.length - 1)) {
        SnowProfile.snowLayers[i].setDiagLine();
      }

      // It's possible that the bottom of the features description area is
      // below the bottom of the SVG drawing area, in which case we need to
      // expand the size of the drawing appropriately.
      SnowProfile.setDrawingHeight();

      // Position the features description in the center of its area.
      SnowProfile.snowLayers[i].features().layout(featureTop, featureBottom);
    }
  };
  
  /**
   * Used to create data object for feature descriptions from 
   * the SnowPilot web form 
   *
   * @method
   * @memberof SnowProfile
   * @param {number} [layernum] The snow profile layer index we are interested in
   * @returns {object} data object for use with featObj.describe(data) method.
   */
  SnowProfile.getSnowPilotData = function (layerNum) {
	var primaryShapes = translateShape($('select[id^="edit-field-layer-und-' + layerNum + '-field-grain-type-und"]').val());
    var primaryShape = primaryShapes[0];
    var primarySubShape = primaryShapes[1];
    //var secondaryShape = translateShape($("div[class*=form-item-field-layer-und-" + layerNum + "-field-grain-type-secondary-] > div > select")[0].value);
    //var secondarySubShape = translateSubShape($("[id^=edit-field-layer-und-" + layerNum + "-field-grain-type-secondary-]").val());
	var secondaryShapes = translateShape($('select[id^="edit-field-layer-und-' + layerNum + '-field-grain-type-secondary-und"]').val());
		
    var secondaryShape = secondaryShapes[0];
	  var secondarySubShape = secondaryShapes[1];
	
	var sizeMin = $("select[id^=edit-field-layer-und-" + layerNum + "-field-grain-size-]").val();
    if (sizeMin === "_none") {
      sizeMin = "";
    }
    var sizeMax = $("select[id^=edit-field-layer-und-" + layerNum + "-field-grain-size-max-]").val();
    if (sizeMax === "_none") {
      sizeMax = "";
    }
    
    // Build array of stability test objects that fit into this layer 
    var stabTests = [];
    for (var i = 0; i < (SnowProfile.stabilityTests.length) ; i++) {
      var layerTopDepth = SnowProfile.snowLayers[layerNum].depth();
      var layerBotDepth = SnowProfile.snowLayers[layerNum + 1].depth();
      var testDepth = SnowProfile.stabilityTests[i].depth;
      if (SnowProfile.depthRef === "g") {
        testDepth = SnowProfile.pitDepth - testDepth;
      }
      
      if (testDepth >= layerTopDepth && testDepth < layerBotDepth) {
        stabTests.push(SnowProfile.stabilityTests[i]);
      }
      if (testDepth == SnowProfile.pitDepth && testDepth == layerBotDepth) {
        stabTests.push(SnowProfile.stabilityTests[i]);
      }
    }
    
    var tempData = {
              primaryGrainShape: primaryShape,
              primaryGrainSubShape: primarySubShape,
              secondaryGrainShape: secondaryShape,
              secondaryGrainSubShape: secondarySubShape,
              grainSizeMin: sizeMin,
              grainSizeMax: sizeMax,
              comment: stabTests
    };
    
    return tempData;
  };
  
  function translateShape(shapeCode) {
    switch (shapeCode) {
      case "33":
        return ["PP",""];
        break;
      case "34":
        return ["DF",""];
        break;
      case "35":
        return ["RG",""];
        break;
      case "36":
        return ["FC",""];
        break;
      case "37":
        return ["DH",""];
        break;
      case "38":
        return ["SH",""];
        break;
      case "39":
        return ["MF",""];
        break;
      case "40":
        return ["IF",""];
        break;
      case "41":
        return ["MM",""];
        break;
	  // Start subshapes
	  case "42":
        return ["PP","PPco"];
        break;
      case "43":
        return ["PP","PPnd"];
        break;
      case "44":
        return ["PP","PPpl"];
        break;
      case "45":
        return ["PP","PPsd"];
        break;
      case "46":
        return ["PP","PPir"];
        break;
      case "47":
        return ["PP","PPgp"];
        break;
      case "48":
        return ["PP","PPhl"];
        break;
      case "49":
        return ["PP","PPip"];
        break;
      case "50":
        return ["PP","PPrm"];
        break;
      case "78":
        return ["DF","DFbk"];
        break;
      case "79":
        return ["RG","RGsr"];
        break;
      case "80":
        return ["RG","RGlr"];
        break;
      case "81":
        return ["RG","RGwp"];
        break;
      case "82":
        return ["RG","RGxf"];
        break;
      case "83":
        return ["FC","FCsf"];
        break;
      case "84":
        return ["FC","FCxr"];
        break;
      case "85":
        return ["DH","DHcp"];
        break;
      case "86":
        return ["DH","DHpr"];
        break;
      case "87":
        return ["DH","DHch"];
        break;
      case "88":
        return ["DH","DHla"];
        break;
      case "89":
        return ["DH","DHxr"];
        break;
      case "90":
        return ["", ""];
        break;
      case "91":
        return ["SH","SHcv"];
        break;
      case "92":
        return ["SH","SHxr"];
        break;
      case "93":
        return ["MF","MFcl"];
        break;
      case "94":
        return ["MF","MFpc"];
        break;
      case "95":
        return ["MF","MFsl"];
        break;
      case "96":
        return ["MF","MFcr"];
        break;
      case "97":
        return ["IF","IFil"];
        break;
      case "98":
        return ["IF","IFic"];
        break;
      case "99":
        return ["IF","IFbi"];
        break;
      case "100":
        return ["IF","IFrc"];
        break;
      case "101":
        return ["IF","IFsc"];
        break;
      case "102":
        return ["MM","MMrp"];
        break;
      case "103":
        return ["MM","MMci"];
        break;
      case "104":
        return ["DF","DFdc"];
        break;
      case "105":
        return ["FC","FCso"];
        break;
      default:
        return ["",""];
    }
  }
  
  /**
   * Checks the fields of one of the stability tests to see if there is 
   * enough information to construct a complete stability test string,
   * and if there is places it in SnowProfile.stabilityTests array
   *
   * @method
   * @memberof SnowProfile
   * @param {number} [testNum] The stability test number, starting at 0 for first test
   */
  SnowProfile.addStabilityTest = function (testNum) {
    var scoreType, scoreValue, testString;
    var testType = $("select[id^=edit-field-test-und-" + testNum + "-field-stability-test-type]").val();
    var shearQuality = $('select[id^=edit-field-test-und-' + testNum + '-field-shear-quality]').val();
    if (typeof shearQuality == "undefined") {
      // must be fracture character instead of shear quality
      shearQuality = $('select[id^=edit-field-test-und-' + testNum + '-field-fracture-character]').val();
    }
    if (shearQuality === "_none") {
      shearQuality = "";
    }
    var testDepthString = $('input[id^=edit-field-test-und-' + testNum + '-field-depth]').val();
    // Convert comma to decimal for EU style
    testDepthString = testDepthString.replace(/,/g,".");
    var testDepth = Number(testDepthString);
    // Set anything with no depth yet input to the top
    if (!testDepthString.length) {
      testDepth = ((SnowProfile.depthRef === "s") ? 0 : SnowProfile.pitDepth);
      testDepthString = "";
    }
    
    switch(testType){
      case "ECT":
        scoreType = $('select[id^=edit-field-test-und-' + testNum + '-field-stability-test-score-ect]').val();
        scoreValue = $('input[id^=edit-field-test-und-' + testNum + '-field-ec-score]').val();
        if (scoreType === "_none") {
          testString = testType + " @ " + testDepthString;
        } else if (scoreType === "ECTPV") {
          testString = scoreType + " @ " + testDepthString;
        } else if (scoreType === "ECTX") {
          testDepth = ((SnowProfile.depthRef === "s") ? 0 : SnowProfile.pitDepth);
          testString = scoreType;
        } else {
          testString = scoreType + scoreValue + " @ " + testDepthString;
        }
        break;
      case "CT":
        scoreType = $('select[id^=edit-field-test-und-' + testNum + '-field-stability-test-score-ct]').val();
        scoreValue = $('input[id^=edit-field-test-und-' + testNum + '-field-ct-score]').val();
        if (scoreType === "_none") {
          testString = testType + " @ " + testDepthString;
        } else if (scoreType === "CTV") {
          testString = scoreType + ", " + shearQuality + " @ " + testDepthString;
        } else if (scoreType === "CTN") {
          testDepth = ((SnowProfile.depthRef === "s") ? 0 : SnowProfile.pitDepth);
          testString = scoreType;
        } else {
          testString = scoreType + scoreValue + ", " + shearQuality + " @ " + testDepthString;
        }
        break;
      case "DT":
        scoreType = $('select[id^=edit-field-test-und-' + testNum + '-field-deep-tap-test-score]').val();
        scoreValue = $('input[id^=edit-field-test-und-' + testNum + '-field-dt-score]').val();
        if (scoreType === "_none") {
          testString = testType + " @ " + testDepthString;
        } else if (scoreType === "DTV") {
          testString = scoreType + ", " + shearQuality + " @ " + testDepthString;
        } else if (scoreType === "DTN") {
          testDepth = ((SnowProfile.depthRef === "s") ? 0 : SnowProfile.pitDepth);
          testString = scoreType;
        } else {
          testString = scoreType + scoreValue + ", " + shearQuality + " @ " + testDepthString;
        }
        break;
      case "RB":
        scoreType = $('select[id^=edit-field-test-und-' + testNum + '-field-stability-test-score-rb]').val();
        scoreValue = "";
        if (scoreType === "_none") {
          testString = testType + " @ " + testDepthString;
        } else if (scoreType === "RB7") {
          testDepth = ((SnowProfile.depthRef === "s") ? 0 : SnowProfile.pitDepth);
          testString = scoreType;
        } else {
          testString = scoreType + ", " + shearQuality + " @ " + testDepthString;
        }
        break;
      case "PST":
        scoreType = $('select[id^=edit-field-test-und-' + testNum + '-field-data-code-pst]').val();
        var sawCutLength = $('input[id^=edit-field-test-und-' + testNum + '-field-length-of-saw-cut]').val();
        if (sawCutLength.length === 0) {
          sawCutLength = "_";
        }
        var columnLength = $('input[id^=edit-field-test-und-' + testNum + '-field-length-of-isolated-col]').val();
        if (columnLength.length === 0) {
          columnLength = "_";
        }
        if (scoreType === "_none") {
          testString = "PST @ " + testDepthString;
        } else {
          testString = "PST " + sawCutLength + "/" + columnLength + "(" + scoreType + ") @ " + testDepthString; 
        }
        break;
      case "SB":
        scoreType = $('select[id^=edit-field-test-und-' + testNum + '-field-stability-test-score-sb]').val();
        scoreValue = "";
        if (scoreType === "_none") {
          testString = testType + " @ " + testDepthString;
        } else if (scoreType === "SBN") {
          testDepth = ((SnowProfile.depthRef === "s") ? 0 : SnowProfile.pitDepth);
          testString = scoreType;
        } else {
          testString = scoreType + ", " + shearQuality + " @ " + testDepthString;
        }
        break;
      case "ST":
        scoreType = $('select[id^=edit-field-test-und-' + testNum + '-field-stability-test-score-st]').val();
        scoreValue = "";
        if (scoreType === "_none") {
          testString = testType + " @ " + testDepthString;
        } else if (scoreType === "STN") {
          testDepth = ((SnowProfile.depthRef === "s") ? 0 : SnowProfile.pitDepth);
          testString = scoreType;
        } else {
          testString = scoreType + ", " + shearQuality + " @ " + testDepthString;
        }
        break;
    }
    
    // Build the object to store in SnowProfile.stabilityTests
    var testObj = { description: testString, depth: testDepth };
    
    // Add object to SnowProfile.stabilityTests, or overwrite if it already exists
    if (SnowProfile.stabilityTests.length > testNum) {
      SnowProfile.stabilityTests[testNum] = testObj;
    } else SnowProfile.stabilityTests.push(testObj);
    
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
    SnowProfile.handlesGroup.front();
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
   *
  SnowProfile.blockSubmit = function(event) {
    if (event.type === "keydown" && event.which === 13) {
      event.preventDefault();
      $(event.target).trigger("change");
    }
  };*/

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
    
    // Add the reference grid to the SVG drawing
    new SnowProfile.Grid();
    
  };  // function SnowProfile.init();
})(jQuery);

// Configure Emacs for Drupal JavaScript coding standards
// Local Variables:
// js2-basic-offset: 2
// indent-tabs-mode: nil
// fill-column: 78
// show-trailing-whitespace: t
// End:
