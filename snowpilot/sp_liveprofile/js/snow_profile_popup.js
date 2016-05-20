/**
  @file File to hold the modal dialog object to describe a snow layer
  @copyright Walt Haas <haas@xmission.com>
  @license {@link http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GPLv2}
 */

/* global SnowProfile */

(function($) {
  "use strict";

  /**
   * Object for a jQueryUI modal dialog to enter data describing a snow layer.
   *
   * The modal dialog popup offers the user the opportunity to enter data about:
   * + Grain shape
   * + Grain size
   * + comments
   *
   * Grain shape is entered by &lt;select&gt;s which offer options for primary
   * and secondary grain shape and subshape according to the
   * [IACS 2009 Standard]{@link http://www.cryosphericsciences.org/products/snowClassification/snowclass_2009-11-23-tagged-highres.pdf}.
   * The primary grain subshape and secondary grain &lt;select&gt;s are
   * available only when a primary grain shape is selected.  The secondary grain
   * subshape &lt;select&gt; is available only when a secondary grain shape is
   * selected.
   * @constructor
   * @param {Object} data Initial values to display.  The object is the value
   * returned by SnowProfile.Features#describe.
   */
  SnowProfile.PopUp = function(data) {

    // Initialize grain shape selectors to blank.
    $("#snow_profile_primary_grain_shape option[value='']")
      .attr("selected", true);
    $("#snow_profile_primary_grain_subshape select option[value='']")
      .attr("selected", true);
    $("#snow_profile_secondary_grain_select option[value='']")
      .attr("selected", true);
    $("#snow_profile_secondary_grain_subshape select option[value='']")
      .attr("selected", true);

    // Hide the primary subshape and secondary shape selectors.
    $("#snow_profile_primary_grain_subshape select")
      .css("display", "none");
    $("#snow_profile_secondary_grain_shape")
      .css("display", "none");

    // Fill in the pop-up HTML form with information
    // passed to constructor
    if (data.primaryGrainShape) {

      // Primary grain shape is provided so show it in select value
      $("#snow_profile_primary_grain_shape option[value=" +
        data.primaryGrainShape + "]")
        .attr("selected", true);

      // Show the appropriate subshape select for this primary shape
      $("#snow_profile_primary_grain_subshape_" + data.primaryGrainShape)
        .css("display", "block");
      if (data.primaryGrainSubShape) {

        // Primary grain subshape is provided so show it in the select value
        $("#snow_profile_primary_grain_subshape_" + data.primaryGrainShape +
          " option[value=" + data.primaryGrainSubShape + "]")
          .attr("selected", true);
      }

      // Show the secondary grain shape select
      $("#snow_profile_secondary_grain_shape")
        .css("display", "block");
      if (data.secondaryGrainShape) {

        // Secondary grain shape is provided so show it in select value
        $("#snow_profile_secondary_grain_select option[value=" +
          data.secondaryGrainShape + "]").attr("selected", true);

        // Show the secondary grain subshape select
        $("#snow_profile_secondary_grain_subshape_" +
          data.secondaryGrainShape)
          .css("display", "block");
        if (data.secondaryGrainSubShape) {

          // Secondary grain subshape is provided so show it in select value
          $("#snow_profile_secondary_grain_subshape option[value=" +
            data.secondaryGrainSubShape + "]")
            .attr("selected", true);
        }
      }
    }
    $("#snow_profile_grain_size_min")
      .val(data.grainSizeMin);
    $("#snow_profile_grain_size_max")
      .val(data.grainSizeMax);
    $("#snow_profile_comment")
      .val(data.comment);
    var editArgs = {
      modal: true,
      width: 400,
      height: 600,
      buttons: [
        {
          // Done button on description pop-up saves values from
          // the pop-up form into the layer description
          text: "Done",
          click: function() {
            // Store description in layer
            data.featObj.describe({
              primaryGrainShape: $("#snow_profile_primary_grain_shape")
                .val(),
              primaryGrainSubShape: $("#snow_profile_primary_grain_subshape_" +
                $("#snow_profile_primary_grain_shape").val()).val(),
              secondaryGrainShape: $("#snow_profile_secondary_grain_select")
                .val(),
              secondaryGrainSubShape: $(
                "#snow_profile_secondary_grain_subshape_" +
                $("#snow_profile_secondary_grain_select").val()).val(),
              grainSizeMin: $("#snow_profile_grain_size_min").val(),
              grainSizeMax: $("#snow_profile_grain_size_max").val(),
              comment: $("#snow_profile_comment").val()
            });

            // Close the popup
            $(this).dialog("close");
          }
        },
        {
          // Cancel button on description pop-up throws away changes
          text: "Cancel",
          tabindex: -1,
          click: function() {$(this).dialog("close");}
        }
      ]
    };
    if (data.numLayers > 1) {

      // If there is more than one layer, add a "Delete" button to the pop-up
      editArgs.buttons.push({
        text: "Delete",
        tabindex: -1,
        click: function() {
          // Not reversible so ask for confirmation
          if (confirm("This action cannot be reversed.  Delete?")) {

            // Delete this layer
            data.layerObj.deleteLayer();
          }

          // Close the popup
          $(this).dialog("close");
        }
      });
    }
    $("#snow_profile_popup").dialog(editArgs);

    // Listen for changes to the primary grain shape
    $("#snow_profile_primary_grain_shape").change(function() {

      // Primary grain shape selection has changed, so adjust sub-shape <select>
      // and re-initialize secondary shape.
      $("#snow_profile_primary_grain_subshape select").attr(
        "style", "display:none;");
      $("#snow_profile_primary_grain_subshape option").attr("selected", false);
      $("#snow_profile_primary_grain_subshape option[value='']").attr(
        "selected", true);
      $("#snow_profile_secondary_grain_shape")
        .attr("style", "display:none;");
      $("#snow_profile_secondary_grain_select option").attr("selected", false);
      $("#snow_profile_secondary_grain_select option[value='']").attr(
        "selected", true);
      $("#snow_profile_secondary_grain_subshape select").attr(
        "style", "display:none;");
      $("#snow_profile_secondary_grain_subshape option").attr("selected", false);
      $("#snow_profile_secondary_grain_subshape option[value='']").attr(
        "selected", true);
      if ($("#snow_profile_primary_grain_shape").val()) {

        // A non-null primary grain shape has been selected.
        // Display the primary sub-shape.
        $("#snow_profile_primary_grain_subshape_" +
          $("#snow_profile_primary_grain_shape").val()).attr(
         "style", "display:block;");
        $("#snow_profile_secondary_grain_shape")
          .attr("style", "display:block;");
      }
    });

    // Listen for changes to the secondary grain shape
    $("#snow_profile_secondary_grain_select").change(function() {

      // Secondary grain shape selection has changed, so adjust
      // secondary sub-shape <select>.
      $("#snow_profile_secondary_grain_subshape select").attr(
        "style", "display:none;");
      $("#snow_profile_secondary_grain_subshape option").attr("selected", false);
      $("#snow_profile_secondary_grain_subshape option[value='']").attr(
        "selected", true);
      if ($("#snow_profile_secondary_grain_select").val()) {

        // A non-null secondary grain shape has been selected.
        // Display the secondary sub-shape.
        $("#snow_profile_secondary_grain_subshape_" +
          $("#snow_profile_secondary_grain_select").val()).attr(
         "style", "display:block;");
      }
    });

  };
})(jQuery);
// Configure Emacs for Drupal JavaScript coding standards
// Local Variables:
// js2-basic-offset: 2
// indent-tabs-mode: nil
// fill-column: 78
// show-trailing-whitespace: t
// End:
