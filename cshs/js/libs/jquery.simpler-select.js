/**
 * jQuery Plugin which renders a standard select with hierarchical options as
 * a set of selects, one for each level of the hierarchy.
 *
 */
;
(function ($, window, document, undefined) {

  // Create the defaults once
  var pluginName = "simplerSelect",
    defaults = {
      noneLabel: "- Please choose -",
      noneValue: "_none",
      labels: []
    };

  // The actual plugin constructor
  function Plugin(element, options) {
    this.element = element;
    this.$element = $(element);

    // vars
    this.settings = $.extend({}, defaults, options);
    this._selectOptions = [];
    this._tree = null;

    this._defaults = defaults;
    this._name = pluginName;

    // start!
    this.init();
  }

  Plugin.prototype = {
    init: function () {
      // parse options
      var that = this;
      $('option', this.element).each(function () {
        var $this = $(this);
        var parent = $this.data('parent');
        parent = (typeof parent !== 'undefined') ? parent : 0;

        var value = $this.val();

        that._selectOptions.push({
          'value': value,
          'parent': parent,
          'label': $this.text(),
          'children': []
        });
      });
//      console.log(this._selectOptions );

      this._tree = this.buildTree(this._selectOptions);

      if (this._tree == null) {
        return;
      }

      var initialValue = this.$element.val();
      var initialParents = [];
      if (initialValue) {
        // console.log(typeof initialValue);
        if (typeof initialValue !== 'string') {
          // if array, flatten it
          initialValue = initialValue.shift();
        }
        // get all parents, starting from the initial value
        initialParents = this.getAllParents(initialValue);

        // reverse the parents, that they start from the root
        initialParents.reverse();

        // add the current value as the last leave.
        initialParents.push(initialValue);
        //console.log(initialParents);
      }

      // create the root select
      var $select = this.createSelect(this._tree);
      this.$element.after($select);

      // create the other selects
      var $currentSelect = $select;
      $.each(initialParents, function (idx, value) {
        that.selectSetValue($currentSelect, value);
        var optionInfo = that.getOptionInfoByValue(value);
        var $nextSelect = that.createSelect(optionInfo.children, value, idx+1);
        $currentSelect.after($nextSelect);
        $currentSelect = $nextSelect;
      });

      //hide the original
      this.$element.hide();
    },

    /**
     * onChange handler of a select element
     *
     * @param $currentSelect
     */
    onChange: function ($currentSelect) {
      // remove deeper selects
      this.selectRemoveNext($currentSelect);

      // get the selected value and also set the original drop-down
      var $selected = $currentSelect.find('option:selected');
      var selectedValue = $selected.val();
      var parentValue = $selected.data('parent-value');

      if (typeof parentValue == 'undefined') {
        parentValue = selectedValue;
      }

      this.$element.val(parentValue);
      this.$element.change();

      if (selectedValue == this.settings.noneValue) {
        return;
      }

      // build new child select
      var optionInfo = this.getOptionInfoByValue(selectedValue);
      if (typeof optionInfo.children !== 'undefined') {
        var currentLevel = this.selectGetLevel($currentSelect);
        $newSelect = this.createSelect(optionInfo.children, selectedValue, currentLevel);
        this.addSelectAfter($currentSelect, $newSelect);
      }
    },
    /**
     * Given an array of options, build an HTML select element
     *
     * @param options
     * @returns {*|HTMLElement}
     */
    createSelect: function (options, parent, level) {
      if (options.length < 1) return;

      var that = this;
      var parent = typeof parent !== 'undefined' ? parent : that.settings.noneValue;
      var level = typeof level !== 'undefined' ? level : 0;
      var label = typeof this.settings.labels[level] !== 'undefined' ? this.settings.labels[level] : '';
      var $select = $('<select class="form-select simpler-select">');

      var isError = that.$element.hasClass('error');
      if (isError) {
        $select.addClass('error');
      }

      // Add the _none option always.
      $select.append('<option value="' + that.settings.noneValue + '" data-parent-value="' + parent + '">' + that.settings.noneLabel + '</option>');

      $.each(options, function (idx, option) {
        if (option.value == that.settings.noneValue) {
          // Do not add _none options (Already added by code above).
          return true;
        }
        $option = $('<option>');
        $option.val(option.value);

        // remove dashes from the beginning, then set the label
        var label = option.label;
        label = label.replace(/(- )*/, '');
        $option.text(label);

        if (option.children.length) {
          $option.addClass('has-children');
        }

        $select.append($option);
      });
      
      $select.change(function (event) {
        var $select = $(this);
        that.onChange($select);
      });

      // wrap it
      var $wrapper = $('<div class="select-wrapper"><label>' + label + '</label></div>');
      $wrapper.find('label').after($select);

      return $wrapper;
    },
    /**
     * Given an flat array an tree is built
     */
    buildTree: function (array, parent, tree) {
      var that = this;
      var tree = typeof tree !== 'undefined' ? tree : null;
      var parent = typeof parent !== 'undefined' ? parent : { value: 0 };

      var children = [];
      $.each(array, function (idx, child) {
        if (typeof child !== 'undefined') {
          if (child.parent == parent.value) {
            children.push(child);
          }
        }
      });

      if (children.length) {
        if (parent.value == 0) {
          tree = children;
        } else {
          parent['children'] = children;
        }
        $.each(children, function (idx, child) {
          that.buildTree(array, child, tree);
        });
      }
      return tree;
    },
    /**
     * Set the value of a select to the given.
     */
    selectSetValue: function($select, value) {
      //$select.val(value);
      $select.find('select').val(value);
    },
    /**
     * Remove all following selects.
     */
    selectRemoveNext: function($currentSelect) {
      if (typeof $currentSelect.parents('.select-wrapper').nextAll('.select-wrapper') !== 'undefined') {
        $currentSelect.parents('.select-wrapper').nextAll('.select-wrapper').remove();
      }
    },
    /**
     * Add a newSelect after the currentSelect.
     */
    addSelectAfter: function($currentSelect, $newSelect) {
      $currentSelect.parents('.select-wrapper').after($newSelect);
    },
    /**
     * Get the hierarchy level of given select.
     */
    selectGetLevel: function($currentSelect) {
      return $currentSelect.parents('.select-wrapper').index();
    },
    /**
     * Given a value build an array of all parents (from leave to root)
     */
    getAllParents: function (value, parents) {
      if (value == this.settings.noneValue) {
        return [];
      }

      var parents = typeof parents !== 'undefined' ? parents : [];

      var option = this.getOptionByValue(value);
      if (typeof option.data('parent') !== 'undefined' && option.data('parent') != '0') {
        var parent = option.data('parent');
        parents.push(parent);
        this.getAllParents(this.getOptionByValue(parent).val(), parents);
      }
      return parents;
    },
    /**
     * Tiny helper to get the option jQuery object
     * @param value
     * @returns {*}
     */
    getOptionByValue: function (value) {
      return this.$element.find('option[value="' + value + '"]');
    },
    /**
     * Helper to get the info-object which corresponds to an option value.
     * @param value
     * @returns {{}}
     */
    getOptionInfoByValue: function(value) {
      var that = this;
      var optionInfo = {};
      $.each(that._selectOptions, function (idx, option) {
        if (option.value == value) {
          optionInfo = option;
        }
      });
      return optionInfo;
    }
  };

  // A really lightweight plugin wrapper around the constructor,
  // preventing against multiple instantiations
  $.fn[ pluginName ] = function (options) {
    this.each(function () {
      if (!$.data(this, "plugin_" + pluginName)) {
        $.data(this, "plugin_" + pluginName, new Plugin(this, options));
      }
    });

    // chain jQuery functions
    return this;
  };

})(jQuery, window, document);
