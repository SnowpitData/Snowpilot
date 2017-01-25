/**
 * @file Define the object that describes the features of a snow layer
 * @copyright Walt Haas <haas@xmission.com>
 * @license {@link http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GPLv2}
 * @contributor Joe DeBruycker
 */

/* global SnowProfile */

(function($) {
  "use strict";

  /**
   * Object describing the features of a single snow stratigraphy layer.
   *
   * This object describes the features (grain shape, grain size etc.)
   * defined in Section 1 of the
   * [IACS 2009 Standard]{@link http://www.cryosphericsciences.org/products/snowClassification/snowclass_2009-11-23-tagged-highres.pdf}.
   * Each Layer object has one Features object, and each Features object
   * is owned by one Layer object.  The separation is made to improve
   * the organization and readability of the code.  The Layer object has been
   * constructed and spliced into the snowLayers array before this constructor
   * is called.
   * @constructor
   * @param {Object} layerArg The Layer object that owns this object
   */
  SnowProfile.Features = function(layerArg) {

    // Reference this object inside an event handler
    var self = this;

    /**
     * The Layer object that owns this Features object
     */
    var layerObj = layerArg;

    /**
     * "Edit" button
     *
     * When the user clicks this button, a modal dialogue appears which allows
     * the user to describe the features of this layer.
     * @type {Object}
     */
    //var editButton = new SnowProfile.Button("edit");
    var i = layerObj.getIndex();
    //var thisEdit = SnowProfile.editGroup.get(i);
    //if (thisEdit === undefined) {
    //  SnowProfile.editGroup.add(editButton.getButton());
    //}
    //else {
    //  thisEdit.before(editButton.getButton());
    //}

    /**
     * Grain shape of this snow layer.
     *
     * Two- or four-character code from the
     * [IACS 2009 Standard]{@link http://www.cryosphericsciences.org/products/snowClassification/snowclass_2009-11-23-tagged-highres.pdf}
     * Appendix A.1 table as stored in {@link SnowProfile.CAAML_SHAPE} or
     * {@link SnowProfile.CAAML_SUBSHAPE}.
     * @type {string}
     */
    var primaryGrainShape = "";
    var primaryGrainSubShape = "";
    var secondaryGrainShape = "";
    var secondaryGrainSubShape = "";

    /**
     * Grain size of this snow layer in mm.
     *
     * A single grain size is stored in grainSizeMin.  A range of grain sizes
     * is stored in grainSizeMin and grainSizeMax.
     * @type {string}
     */
    var grainSizeMin = "",
      grainSizeMax = "";

    /**
     * User's comment about this snow layer - using this for stability tests.
     * Array of Objects that describe the stability tests.
     *
     * @type {Array}
     */
    var comment;

    /**
     * SVG group to hold all displayable components of the feature description.
     *
     * This group is positioned as a unit.  It includes:
     * + Grain shape
     * + Grain size
     * + Comment
     * @type {object}
     */
    var featDescr = SnowProfile.drawing.group(SnowProfile.Cfg.FEAT_DESCR_WD,
      SnowProfile.Cfg.DESCR_HEIGHT)
      .addClass('snow_profile_feat_descr')
      .x(SnowProfile.Cfg.FEAT_DESCR_LEFT)
      .y(SnowProfile.depth2y(layerObj.depth()) +
        (SnowProfile.Cfg.HANDLE_SIZE / 2));
    SnowProfile.mainGroup.add(featDescr);

    // For debugging, show the bounding box
    // var fdBox = SnowProfile.drawing.rect(0, 0)
    //   .addClass('snow_profile_fdbox')
    //   .style({
    //      "fill-opacity": 0,
    //      stroke: 'blue'
    //   });
    // featDescr.add(fdBox);

     /**
     * Text for the comment.
     *
     * [SVG.Text]{@link http://documentup.com/wout/svg.js#text/text}
     * object for text describing the user's comment on this snow layer.  It
     * contains the character string entered by the user plus additional
     * information to format this string on the browser window.
     * @type {Object}
     */
    var commentDescr = SnowProfile.drawing.text("")
      .addClass('snow_profile_comment_descr')
      .font({
        size: SnowProfile.Cfg.FEAT_DESCR_FONT_SIZE,
        family: 'sans-serif',
        fill: '#000'
      })
      .x(SnowProfile.Cfg.COMMENT_LEFT)
      .y(-5);
    featDescr.add(commentDescr);

    // For debugging, show the bounding box
    //var cdBox = SnowProfile.drawing.rect(0, 0)
    //  .addClass('snow_profile_cdbox')
    //  .style({
    //     "fill-opacity": 0,
    //     stroke: 'red'
    //  });
    //featDescr.add(cdBox);

    /**
     * Hardness of this snow layer.
     *
     * A string code from the {@link SnowProfile.CAAML_HARD} table.
     * The initial value of null indicates the handle for this snow layer has not
     * yet been touched by the user.
     * @type {string}
     */
    var hardnessCode = null;
    var hardnessCode2 = null;

    /**
     * Text for the grain size
     *
     * [SVG.Text]{@link http://documentup.com/wout/svg.js#text/text}
     * object for text giving the grain size of this snow layer.
     * @type {Object}
     */
    var grainSizeText = SnowProfile.drawing.text("")
      .addClass('snow_profile_grain_size')
      .font({
        size: SnowProfile.Cfg.FEAT_DESCR_FONT_SIZE,
        family: 'sans-serif',
        fill: '#000'
      })
      .x(SnowProfile.Cfg.GRAIN_SIZE_LEFT)
      .y(-5);
    featDescr.add(grainSizeText);

    // For debugging, show the bounding box
    // var gsBox = SnowProfile.drawing.rect(0, 0)
      // .addClass('snow_profile_gsbox')
      // .style({
         // "fill-opacity": 0,
         // stroke: 'red'
      // });
    // featDescr.add(gsBox);

    /**
     * Group to hold the icons describing this layer's grains.
     *
     * [SVG.G]{@link http://documentup.com/wout/svg.js#parent-elements/groups}
     * object holding icons describing the grain shape of this snow layer.
     * The icon symbols are defined in
     * [IACS 2009 Standard]{@link http://www.cryosphericsciences.org/products/snowClassification/snowclass_2009-11-23-tagged-highres.pdf}
     * Appendix A.
     * @type {Object}
     */
    var grainIcons = featDescr.group()
      .addClass('snow_profile_grain_icons')
      .x(SnowProfile.Cfg.GRAIN_ICON_LEFT)
      .y(5);

    // For debugging, show the bounding box
    // var giBox = SnowProfile.drawing.rect(0, 0)
      // .addClass('snow_profile_gibox')
      // .style({
         // "fill-opacity": 0,
         // stroke: 'red'
      // });
    // featDescr.add(giBox);

    /**
     * Y position of top of bounding box
     */
    var yPos;

    /**
     * Height needed by the feature description, in pixels.
     *
     * Based on the bounding box of the description.
     * Zero if the description is empty.
     * @type {number}
     */
    this.height = 0;

    /**
     * Get or set Y value of the feature description.
     *
     * @param {number} yArg Y value of the top of the bounding box of features.
     */
    // this.y = function(yArg) {
    //   if (yArg === undefined) {
    //     return yPos;
    //   }
    //   else {
    //     console.log('y =', yArg);
    //     yPos = yArg;
    //     editButton.setY(yArg + SnowProfile.Cfg.MIN_FEAT_PAD);
    //     featDescr.y(yArg + SnowProfile.Cfg.MIN_FEAT_PAD);
    //  }
    //};

    /**
     * Get or set the layer hardness
     * @param {string} [code] A CAAML hardness code from the CAAML_HARD table.
     * @returns {string} Hardness code if param omitted.
     */
    this.hardness = function(code) {
      if (code === undefined) {
        return hardnessCode;
      }
      else {
        hardnessCode = code;
      }
    };
    
    this.hardness2 = function(code) {
        if (code === undefined) {
            return hardnessCode2;
        }
        else {
            hardnessCode2 = code;
        }
    };

    /**
     * Generate a text description of grain shapes from symbols
     *
     * Accept grain shape symbols selected by the user, if any, and
     * return a text description of the form.  The description is
     * constructed by looking up the symbols in CAAML_SHAPE and
     * CAAML_SUBSHAPE to find the equivalent text.
     * @param {string} primaryShape Primary grain shape symbol
     * @param {string} primarySubShape Primary grain subshape symbol
     * @param {string} secondaryShape Secondary grain shape symbol
     * @param {string} secondarySubShape Secondary grain subshape symbol
     * @returns {string} Text description of the grain shapes
     */
    function sym2text(primaryShape, primarySubShape, secondaryShape,
      secondarySubShape) {

      var result = "";
      if (primaryShape !== "") {

        // Grain shape information is available
        if (secondaryGrainShape !== "") {

          // Both primary and secondary shapes so identify them
          result += "Primary Grain Shape:\n";
        }
        result += SnowProfile.CAAML_SHAPE[primaryShape].text;
        if (primarySubShape !== "") {

          // Primary subshape available, add to text description
          result += "\n" +
            SnowProfile.CAAML_SUBSHAPE[primaryShape][primarySubShape].text;
        }
        if (secondaryGrainShape !== "") {

          // Secondary shape information available
          result += "\nSecondary Grain Shape:\n" +
            SnowProfile.CAAML_SHAPE[secondaryShape].text;
          if (secondarySubShape !== "") {
            result += "\n" +
              SnowProfile.CAAML_SUBSHAPE[secondaryShape][
              secondarySubShape].text;
          }
        }
      }
      return result;
    } // function sym2text

    /**
     * Generate an icon description for Melt-freeze crust
     *
     * Generate the appropriate Melt-freeze crust icon for the
     * the specified secondary shape, if any
     * @param {string} secondaryShape Secondary grain shape symbol
     * @param {string} secondarySubShape Secondary grain subshape symbol
     * @param {Object} container SVG.G group object to hold icons
     */
    function sym2iconsMFcr(secondaryShape, secondarySubShape, container) {
      var primaryIcon,
        secondaryIcon;

      // Special Melt-freeze crust icon allowing space on the right
      // side to insert a secondary form
      var image = "iVBORw0KGgoAAAANSUhEUgAAADQAAAAdCAYAAADl208VAAAABHNCSVQICAgIfAhkiAAAAAlwSFlz" +
  "AAAT9gAAE/YBIx4x4QAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAQDSURB" +
  "VFiFxdhbiFVlFAfw3z6TQg46NVbeMiqpxGrsZqUGlkgmCfngQ/Vg0UMPQS8hhFGgkW8VvUhFF3qq" +
  "QBAi7QIiRURgmWKFl1BQIbOLpjWZt3YPe21mz+nss/dxxnHBx8fea63/+tZ3XWslaZqqoiRJerAI" +
  "i3EtJuFS/IH9OIDP8X6apr9UAg7GvgwPYh6m4gpchF9xELuxHh+naXq0EjBN09KGy/E2TiKt0U7h" +
  "I8xphxvYc0L2VE3sk3gLU9rilhjrwvM4XgD8AauxEH2YEP1SPIOtBdl/8SbGt8C+GK+HTC6/NTCW" +
  "NmHfFzZ3FGT78RwatRwKg58WAPbgnqoZD9352FDQPYRbCvw+/FTgb8D8mtj3Yl9Bdz3GtXUIvdgZ" +
  "CmfwCsbUMdiE83DMZCo7Z3Njix2Jf3/hobPAHRurmzv1HXpaOoRR2BSC+zC3U4NNxmdib2Gb5A7u" +
  "xg1DxF6AnwPvE3S1cujFEDiN2RWAXZiOsRVyE5u2yV5cMhRnmrZgjvvCIIdwNU4Ec3UJwAV4Cp/h" +
  "z8K23I41mFii1xfyx3D9cDhTwF4T4/gHVxYdei8Y32JUyfbZUpiRNA538To/jGUlhhfj/uF0JnDH" +
  "YFfYfzf+6ZW9BWdazSBmxAzkN94ScR1jNG7DuoJjy4d74BVOzTbwBvbCsvjxZclZ2Rz8dehuA/xY" +
  "YfmHdWvVcGp72F4Ga+Pj6RaCTwbvgKbrsQT4nbLJOccOrQy7axuYJqNv/J8WRL8qrRNHsTyA70yS" +
  "ZFwN+eGir6Of1pAFmmSHvJlujf6rOqhpmv4me5gbsrM1UpSPfVJDHCT8XpRIkqQbU2Qz/mMH4Dui" +
  "nz6UEXZI+dh7G7LrFsYXJdI07Zd5nuCaDsBnRL9zKCPskPKxH27Icg6Y3EJwS/Rz66AmSTIB18lW" +
  "tdWZPFeUj/1gQ/a20HrPb4p+ZZIkvS34zfSybEU3p2l6bGhj7IhmRb+H9u/QKAN5zodahOsF2SdC" +
  "7gRmns93qCpS6DMQ5+2XpctTgteNuwzOgVaMsDODI4X4WRXLzZLlHsVY7nBMQv59FI+XGB25WC4Y" +
  "daLt0bI0+QsD0fZpfI83MLVEb+Sj7WB2kg8luEpFNut85UMh0JyxVlZuKgzebCBjPRYtf6T7hog9" +
  "X1XGGoLNNYWXcOFZGHzUQMXoCO7A7bIXPcXfeOQscLtjm+UVo/KaQkGpueqzq+5qyUpcGwu6h3BT" +
  "gX+jwVWfjVhYE3ue7J3JdaurPgXlLqyKmcwBtsjqYfNk1dMeWby2BCuwrSDbaV1uW2AsCcyesHF3" +
  "2CzW/PrxrLp1uSbjk/GawQXHdu1cVk6P41UltYu8JQHeliLyXoQHZLHaSNa2d+EDWW27vwrvP/t3" +
  "1UVx0/hpAAAAAElFTkSuQmCC";

      if (secondaryShape === "") {
        // There is no secondary shape so we just use the normal MFcr icon
        primaryIcon = SnowProfile.drawing.image("data:image/png;base64," +
          SnowProfile.CAAML_SUBSHAPE.MF.MFcr.icon.image,
          SnowProfile.CAAML_SUBSHAPE.MF.MFcr.icon.height,
          SnowProfile.CAAML_SUBSHAPE.MF.MFcr.icon.width)
          .y(-5)
          .attr('alt', 'MFcr');
        new Opentip('#' + primaryIcon.node.id,
          SnowProfile.CAAML_SUBSHAPE.MF.MFcr.text, "", {target: true});
        container.add(primaryIcon);
      }
      else {

        // There is a secondary shape, so use the alternative MFcr icon
        primaryIcon = SnowProfile.drawing.image(
          "data:image/png;base64," + image, 52, 29)
          .y(-5)
          .attr('alt', 'MFcr');
        new Opentip('#' + primaryIcon.node.id,
          SnowProfile.CAAML_SUBSHAPE.MF.MFcr.text, "", {target: true});
        container.add(primaryIcon);
        if (secondarySubShape === "") {
          // User did not specify a secondary subshape
          secondaryIcon = SnowProfile.drawing.image("data:image/png;base64," +
            SnowProfile.CAAML_SHAPE[secondaryShape].icon.image,
            SnowProfile.CAAML_SHAPE[secondaryShape].icon.width,
            SnowProfile.CAAML_SHAPE[secondaryShape].icon.height)
            .attr('alt', secondaryShape)
            .cx(((SnowProfile.CAAML_SHAPE[secondaryShape]
              .icon.width) / 2) + 30)
            .cy(10);
        new Opentip('#' + secondaryIcon.node.id,
          SnowProfile.CAAML_SHAPE[secondaryShape].text,
          "", {target: true});
        }
        else {
          // User specified a secondary subshape
          secondaryIcon = SnowProfile.drawing.image("data:image/png;base64," +
            SnowProfile.CAAML_SUBSHAPE[secondaryShape][secondarySubShape].
            icon.image,
            SnowProfile.CAAML_SUBSHAPE[secondaryShape][secondarySubShape].
            icon.width,
            SnowProfile.CAAML_SUBSHAPE[secondaryShape][secondarySubShape].
            icon.height)
            .attr('alt', secondarySubShape)
            .cx(((SnowProfile.CAAML_SUBSHAPE[secondaryShape]
              [secondarySubShape].icon.width) / 2) + 30)
            .cy(10);
          new Opentip('#' + secondaryIcon.node.id,
            SnowProfile.CAAML_SUBSHAPE[secondaryShape][secondarySubShape].text,
            "", {target: true});
        }
        container.add(secondaryIcon);
      }
    } // function sym2iconsMFcr()

    /**
     * Generate an icon description for normal case
     *
     * Accept grain shape symbols selected by the user, if any, and
     * return an icon description of the form.  The description is
     * constructed by looking up the symbols in
     * {@link SnowProfile.CAAML_SHAPE} and
     * {@link SnowProfile.CAAML_SUBSHAPE} to find the equivalent icons.  It
     * has been determined that the primary subshape is NOT Melt-freeze
     * crust.
     * @param {string} primaryShape Primary grain shape symbol
     * @param {string} primarySubShape Primary grain subshape symbol
     * @param {string} secondaryShape Secondary grain shape symbol
     * @param {string} secondarySubShape Secondary grain subshape symbol
     * @param {Object} container SVG.G group object to hold icons
     */
    function sym2iconsNormal(primaryShape, primarySubShape, secondaryShape,
      secondarySubShape, container) {

      var primaryIcon,
        secondaryIcon;
      var iconCursor = 0;

      if (primaryShape !== "") {

        // User specified a primary grain shape

        if (primarySubShape !== "") {

          // User specified both primary grain shape and subshape
          // Add the icon for the primary grain subshape
          primaryIcon = SnowProfile.drawing.image("data:image/png;base64," +
            SnowProfile.CAAML_SUBSHAPE[primaryShape][primarySubShape].
            icon.image,
            SnowProfile.CAAML_SUBSHAPE[primaryShape][primarySubShape].
            icon.width,
            SnowProfile.CAAML_SUBSHAPE[primaryShape][primarySubShape].
            icon.height)
            .attr('alt', primarySubShape);
          iconCursor += SnowProfile.CAAML_SUBSHAPE[primaryShape]
            [primarySubShape].icon.width;
          new Opentip('#' + primaryIcon.node.id,
            SnowProfile.CAAML_SUBSHAPE[primaryShape][primarySubShape].text,
            "", {target: true});
        }
        else {

          // User specified a primary grain shape but no subshape
          // Add the icon for the primary grain shape
          primaryIcon = SnowProfile.drawing.image("data:image/png;base64," +
            SnowProfile.CAAML_SHAPE[primaryShape].icon.image,
            SnowProfile.CAAML_SHAPE[primaryShape].icon.width,
            SnowProfile.CAAML_SHAPE[primaryShape].icon.height)
            .attr('alt', primaryShape);
          iconCursor += SnowProfile.CAAML_SHAPE[primaryShape].icon.width;
          new Opentip('#' + primaryIcon.node.id,
            SnowProfile.CAAML_SHAPE[primaryShape].text, "", {target: true});
        }
        container.add(primaryIcon);
        if (secondaryShape !== "") {

          // User specified a secondary grain shape
          // Add left paren to the icons
          iconCursor += 3;
          container.add(SnowProfile.drawing.text("(")
            .font({
              size: 18,
              family: 'sans-serif',
              fill: '#000'
            })
            .x(iconCursor).y(-5));
          iconCursor += 7;

          // Add secondary grain shape icon
          if (secondarySubShape !== "") {

            // User specified both secondary grain shape and subshape
            // Add the icon for the secondary grain subshape

            secondaryIcon = SnowProfile.drawing.image("data:image/png;base64," +
              SnowProfile.CAAML_SUBSHAPE[secondaryShape][secondarySubShape].
              icon.image,
              SnowProfile.CAAML_SUBSHAPE[secondaryShape][secondarySubShape].
              icon.width,
              SnowProfile.CAAML_SUBSHAPE[secondaryShape][secondarySubShape].
              icon.height)
              .x(iconCursor)
              .attr('alt', secondarySubShape);
            iconCursor += SnowProfile.CAAML_SUBSHAPE[secondaryShape]
              [secondarySubShape].icon.width;
            new Opentip('#' + secondaryIcon.node.id,
              SnowProfile.CAAML_SUBSHAPE[secondaryShape][secondarySubShape].text,
              "", {target: true});
          }
          else {

            // User specified a secondary grain shape but no subshape
            // Add the icon for the primary grain shape
            secondaryIcon = SnowProfile.drawing.image("data:image/png;base64," +
              SnowProfile.CAAML_SHAPE[secondaryShape].icon.image,
              SnowProfile.CAAML_SHAPE[secondaryShape].icon.width,
              SnowProfile.CAAML_SHAPE[secondaryShape].icon.height)
              .x(iconCursor)
              .attr('alt', secondaryShape);
            iconCursor += SnowProfile.CAAML_SHAPE[secondaryShape].icon.width;
            new Opentip('#' + secondaryIcon.node.id,
              SnowProfile.CAAML_SHAPE[secondaryShape].text,
              "", {target: true});
          }
          container.add(secondaryIcon);

          // Add right paren to the icons
          iconCursor += 3;
          container.add(SnowProfile.drawing.text(")")
            .font({
              size: 18,
              family: 'sans-serif',
              fill: '#000'
            })
            .x(iconCursor).y(-5));
        }
      }
    } // function sym2iconsNormal()

    /**
     * Generate an icon description of grain shapes from symbols
     *
     * Accept grain shape symbols selected by the user, if any, and
     * return an icon description of the form.  The description is
     * constructed by looking up the symbols in
     * {@link SnowProfile.CAAML_SHAPE} and
     * {@link SnowProfile.CAAML_SUBSHAPE} to find the equivalent icons.
     * There are two cases:
     *   + If the primary subshape is Melt-freeze crust, then the
     *     secondary shape is incorporated into the MFcr icon.
     *   + In all other cases, the secondary shape in parentheses follows
     *     the primary shape.
     * @param {string} primaryShape Primary grain shape symbol
     * @param {string} primarySubShape Primary grain subshape symbol
     * @param {string} secondaryShape Secondary grain shape symbol
     * @param {string} secondarySubShape Secondary grain subshape symbol
     * @param {Object} container SVG.G group object to hold icons
     */
    function sym2icons(primaryShape, primarySubShape, secondaryShape,
      secondarySubShape, container) {

      if (primaryShape !== "") {
        // UNCOMMENT FOR SPECIAL MELT-FREEZE ICON
        /*if (primarySubShape === "MFcr") {

          // Case 1) Melt-freeze crust, secondary goes inside
          sym2iconsMFcr(secondaryShape, secondarySubShape, container);
        }
        else { */

          // Case 2) secondary follows primary in parentheses
          sym2iconsNormal(primaryShape, primarySubShape, secondaryShape,
            secondarySubShape, container);
        //}
      }
    } // function sym2icons

    /**
     * Set the grain description.
     *
     * Accept grain shape symbols selected by the user, if any, and
     * describe the shape in text and icons.  The description is
     * constructed by looking up the symbols in
     * {@link SnowProfile.CAAML_SHAPE} and
     * {@link SnowProfile.CAAML_SUBSHAPE} to find the equivalent icons.
     *
     * @param {string} primaryShape Primary grain shape symbol
     * @param {string} primarySubShape Primary grain subshape symbol
     * @param {string} secondaryShape Secondary grain shape symbol
     * @param {string} secondarySubShape Secondary grain subshape symbol
     */
    function setGrainDescr(primaryShape, primarySubShape, secondaryShape,
      secondarySubShape) {

      // Empty the grain shape icon group and text description
      grainIcons.clear();
      if (primaryGrainShape !== "") {

        // The user gave us grain shape information.
        // Build a text description of grain shape from what we have
        var text = sym2text(primaryShape, primarySubShape,
          secondaryShape, secondarySubShape);
        text = text; // to suppress JSHint "unused" error

        // Build an iconic description of grain shape from what we have
        sym2icons(primaryShape, primarySubShape,
          secondaryShape, secondarySubShape, grainIcons);
      }
    }

    /**
     * Set the comment text from comment data provided by user.
     *
     * @param {string} comment User comment string
     */
    function setCommentDescr(comment) {

      var words = [],
        testLine = [];
      var testLineDescr = SnowProfile.drawing.text('')
        .width(SnowProfile.Cfg.COMMENT_WD)
        .addClass("snow_profile_comment_descr")
        .build(false);
      commentDescr.text("");
      commentDescr.build(false);

      // Remove leading/trailing whitespace if any.
      comment = comment.trim();
      if (comment !== "") {

        // The user gave us a comment.  Split into words.
        words = comment.split(' ');
        //NB zero-length word for multiple spaces
        words.forEach(function(word, i) {
          if (word.length !== 0) {
            testLine.push(word);
            testLineDescr.text(testLine.join(' '));
            if (testLineDescr.bbox().width >= SnowProfile.Cfg.COMMENT_WD) {
              if (testLine.length === 1) {
                // Special case - single word wider than comment field
                commentDescr.tspan(word).newLine();
                commentDescr.build(true);
                testLine = [];
              }
              else {
                // Normal case - latest word overflowed so save for next line
                testLine.pop();
                commentDescr.tspan(testLine.join(' ')).newLine();
                commentDescr.build(true);
                testLine = [];
                testLine.push(word);
              }
            }
          }
        }); // words.forEach(function(word, i) {
        if (testLine.length > 0) {
          // Words left over after last line filled.
          commentDescr.tspan(testLine.join(' ')).newLine();
        }

        // Blank the test buffer
        testLineDescr.text("");
      }
    } // function setCommentDescr(comment)
    
    /**
     * Set the stability test text for SnowPilot, which uses the original "commentDescr" area 
     *
     * @param {Array} sTests Array of objects representing stability tests
     */
    function setStabTest(sTests) {

      var words = [];
      commentDescr.text("");
      commentDescr.build(false);
      
      // Sort stability tests based on depth
      sTests.sort(function (a, b) {
        if (SnowProfile.depthRef === "s") {
          return a.depth - b.depth;
        } else {
          return b.depth - a.depth;
        }
      });

      // Iterate through all stability tests
      for (var i = 0; i < sTests.length; i++) {
        var testText = sTests[i].description;
        var testDepth = sTests[i].depth;
        
        // Add the text to the stability test column on live graph
        commentDescr.tspan(testText).newLine();
        //commentDescr.tspan(testText).newLine().y(SnowProfile.depth2y(testDepth));
        commentDescr.build(true);
        
      }
    } // function setCommentDescr(comment)

    /**
     * Get or set description of this snow layer
     *
     * Called by jQuery listeners attached to SnowPilot web form
     * @callback
     * @param {Object} [data] - Object describing the snow layer.
     * @returns {Object} Object describing the snow layer if param omitted.
     */
    this.describe = function(data) {
      console.log("INFO: In SnowProfile.features.describe(data)");

      var cdBbox, giBbox, gsBbox, fdBbox;

      // Main body of this.describe function
      if (data === undefined) {

        // Called with no argument, return an object with the values
        return {
          primaryGrainShape: primaryGrainShape,
          primaryGrainSubShape: primaryGrainSubShape,
          secondaryGrainShape: secondaryGrainShape,
          secondaryGrainSubShape: secondaryGrainSubShape,
          grainSizeMin: grainSizeMin,
          grainSizeMax: grainSizeMax,
          comment: comment,
          featObj: self,
          layerObj: layerObj,
          numLayers: SnowProfile.snowLayers.length
        };
      } // if (data === undefined)
      else {

        // Called with an argument so set values for layer
        primaryGrainShape = data.primaryGrainShape;
        primaryGrainSubShape = data.primaryGrainSubShape;
        secondaryGrainShape = data.secondaryGrainShape;
        secondaryGrainSubShape = data.secondaryGrainSubShape;
        grainSizeMin = data.grainSizeMin;
        grainSizeMax = data.grainSizeMax;
        comment = data.comment;

        // Set the grain icon description
        setGrainDescr(primaryGrainShape, primaryGrainSubShape,
          secondaryGrainShape, secondaryGrainSubShape);
        giBbox = grainIcons.bbox();

        // For debugging show the grain shape icon bounding box
        // giBox.width(giBbox.width);
        // giBox.height(giBbox.height);
        // giBox.x(giBbox.x);
        // giBox.y(giBbox.y);

        // Empty the grain size text description
        grainSizeText.text("");
        if ((grainSizeMin !== "") || (grainSizeMax !== "")) {

          // The user gave us grain size information.
          // Build a text description of grain size from what we have.
          grainSizeText.text(grainSizeMin +
            (grainSizeMax !== '' ? (' - ' + grainSizeMax) : ''));
        }
        gsBbox = grainSizeText.bbox();

        // For debugging show the grain size bounding box
        // gsBox.width(gsBbox.width);
        // gsBox.height(gsBbox.height);
        // gsBox.x(gsBbox.x);
        // gsBox.y(gsBbox.y);

        // Comment description
        //setCommentDescr(comment);
        setStabTest(comment);
        cdBbox = commentDescr.bbox();

        // For debugging show the comment description bounding box
        // cdBox.width(cdBbox.width);
        // cdBox.height(cdBbox.height);
        // cdBox.x(cdBbox.x);
        // cdBox.y(cdBbox.y);

        // Form a layer description bounding box by merging any of
        // (giBbox, gsBbox, cdBbox) that are not empty.
        fdBbox = null;
        var boxes = [giBbox, gsBbox, cdBbox];
        for (i in boxes) {
          if (boxes[i].height !== 0) {
            // This box must be merged
            if (fdBbox === null) {
              fdBbox = boxes[i];
            }
            else {
              fdBbox = fdBbox.merge(boxes[i]);
            }
          }
        }

        // Make height of the feature description bounding box public
        if (fdBbox === null) {
          self.height = 0;
        }
        else {
          console.log("feature.height is set to " + fdBbox.height);
          self.height = fdBbox.height;
        }

        // For debugging, make bounding box visible
        // if (fdBbox === null) {
        //   fdBox.width(0);
        //   fdBox.height(0);
        //   fdBox.x(0);
        //   fdBox.y(0);
        // }
        // else {
        //   fdBox.width(fdBbox.width);
        //   fdBox.height(fdBbox.height);
        //   fdBox.x(fdBbox.x);
        //   fdBox.y(fdBbox.y);
        // }
      } // if (data === undefined) ... else

      // Re-draw the diagram with the updated information
      SnowProfile.layout();

    }; // this.describe = function(data) {

    /**
     * Lay out the feature description in its area
     *
     * @param {number} top Y value of top of area
     * @param {number} bottom Y value of bottom of area
     */
    this.layout = function(top, bottom) {
      console.log("INFO: In SnowProfile.features.layout()");
      console.log("Top: " + top);
      console.log("Bottom: " + bottom);

      // Midpoint of space for description
      var spaceMidY = top + ((bottom - top) / 2);

      // Edit button straddles midpoint
      //editButton.setY(spaceMidY - 11);

      if (self.height === 0) {
        // No feature description to lay out, forget it
        console.log("feature.height is zero!");
        //return;
      }
      featDescr.y(spaceMidY - (self.height / 2) +
        (3 * SnowProfile.Cfg.MIN_FEAT_PAD));
      console.log("featDescr.y set to " + (spaceMidY - (self.height / 2) +
        (3 * SnowProfile.Cfg.MIN_FEAT_PAD)));
    };

    /**
     * Horizontal line below the features description
     *
     * [SVG.Line]{@link http://documentup.com/wout/svg.js#line}
     * object for a horizontal line below the text description of this snow
     * layer.  This line visually separates the descriptions of the various
     * snow layers.  Its position is set independently of the position of the
     * feature description, so it is not part of the featDescr group.
     * @type {Object}
     */
    var lineBelow = SnowProfile.drawing.line(0, 0, 0, 0)
      .addClass('snow_profile_line_below')
      .stroke({
        color: SnowProfile.Cfg.GRID_COLOR,
        width: 1
      });
    SnowProfile.mainGroup.add(lineBelow);

    /**
     * Y value of the line below the description of a layer.
     */
    var lineBelowYvalue = SnowProfile.Cfg.HANDLE_MIN_Y + self.height;

    /**
     * Get or set the Y axis value for the line below the description of a layer.
     * @param {number} [yArg] Y axis value of the line below the area designated
     *   for the feature description.
     * @returns {number} Y axis value of the line if param omitted.
     */
    this.lineBelowY = function(yArg) {
      if (yArg === undefined) {
        return lineBelowYvalue;
      }
      else {
        lineBelowYvalue = yArg;
        lineBelow.plot(
          SnowProfile.Cfg.FEAT_DESCR_LEFT - 3,
          yArg + (SnowProfile.Cfg.HANDLE_SIZE / 2),
          SnowProfile.Cfg.FEAT_DESCR_LEFT + SnowProfile.Cfg.FEAT_DESCR_WD,
          yArg + (SnowProfile.Cfg.HANDLE_SIZE / 2));
      }
    };

    /**
     * Remove and destroy all SVG objects belonging to this Features object.
     */
    this.destroy = function() {
      //editButton.destroy();
      lineBelow.remove();
      featDescr.clear();
    };

    // When Edit button clicked, pop up a modal dialog form.
    /*$(document).bind("SnowProfileButtonClick", function(evt, extra) {
      if (extra.buttonObj === editButton) {
        SnowProfile.PopUp(self.describe());
      }
    });*/

  };  //SnowProfile.Features = function()
})(jQuery);

// Configure Emacs for Drupal JavaScript coding standards
// Local Variables:
// js2-basic-offset: 2
// indent-tabs-mode: nil
// fill-column: 78
// show-trailing-whitespace: t
// End:
