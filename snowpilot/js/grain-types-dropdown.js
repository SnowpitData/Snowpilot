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
  Drupal.behaviors.snowpilot4 = {
    attach: function (context, settings) {
			
			
      $('.field-name-field-grain-type').hide();
			
      $('ul.grain-types-groups').hide();
			
			//grainDropDown.appendChild(grainTypeSelectionContainer);

			//var myModal = new Modal({
			//  content: grainDropDown
				//});

			var triggerButton = document.getElementById('trigger');

			//triggerButton.addEventListener('click', function() {
			 // myModal.open();
			//});
			
    }    // end of attach
  };  //end of Drupal.behavior.snowpilot4
  
  // Modal Code adapted from https://scotch.io.tutorials/building-your-own-javascript-modal-plugin
  (function (){	  
    // Define our constructor 
    this.Modal = function() {

      // Create global element references
      this.closeButton = null;
      this.modal = null;
      this.overlay = null;

      // Determine proper prefix
      this.transitionEnd = transitionSelect();

      // Define option defaults 
      var defaults = {
        autoOpen: false,
        className: 'fade-and-drop',
        closeButton: true,
        content: "",
        maxWidth: 260,
        minWidth: 260,
        overlay: true
      }

      // Create options by extending defaults with the passed in arugments
      if (arguments[0] && typeof arguments[0] === "object") {
        this.options = extendDefaults(defaults, arguments[0]);
      }

      if(this.options.autoOpen === true) this.open();

    }

    // Public Methods

    Modal.prototype.close = function() {
      var _ = this;
      this.modal.className = this.modal.className.replace(" scotch-open", "");
      this.overlay.className = this.overlay.className.replace(" scotch-open",
        "");
      this.modal.addEventListener(this.transitionEnd, function() {
        _.modal.parentNode.removeChild(_.modal);
      });
      this.overlay.addEventListener(this.transitionEnd, function() {
        if(_.overlay.parentNode) _.overlay.parentNode.removeChild(_.overlay);
      });
    }

    Modal.prototype.open = function() {
      buildOut.call(this);
      initializeEvents.call(this);
      window.getComputedStyle(this.modal).height;
      this.modal.className = this.modal.className +
        (this.modal.offsetHeight > window.innerHeight ?
          " scotch-open scotch-anchored" : " scotch-open");
      this.overlay.className = this.overlay.className + " scotch-open";
    }

    // Private Methods

    function buildOut() {

      var content, contentHolder, docFrag;

      /*
       * If content is an HTML string, append the HTML string.
       * If content is a domNode, append its content.
       */

      if (typeof this.options.content === "string") {
        content = this.options.content;
      } else {
        content = this.options.content.innerHTML;
      }

      // Create a DocumentFragment to build with
      docFrag = document.createDocumentFragment();

      // Create modal element
      this.modal = document.createElement("div");
      this.modal.className = "scotch-modal " + this.options.className;
      this.modal.style.minWidth = this.options.minWidth + "px";
      this.modal.style.maxWidth = this.options.maxWidth + "px";

      // If closeButton option is true, add a close button
      if (this.options.closeButton === true) {
        this.closeButton = document.createElement("button");
        this.closeButton.className = "scotch-close close-button";
        this.closeButton.innerHTML = "&times;";
        this.modal.appendChild(this.closeButton);
      }

      // If overlay is true, add one
      if (this.options.overlay === true) {
        this.overlay = document.createElement("div");
        this.overlay.className = "scotch-overlay " + this.options.className;
        docFrag.appendChild(this.overlay);
      }

      // Create content area and append to modal
      contentHolder = document.createElement("div");
      contentHolder.className = "scotch-content dropdown";
      contentHolder.innerHTML = content;
      this.modal.appendChild(contentHolder);

      // Append modal to DocumentFragment
      docFrag.appendChild(this.modal);

      // Append DocumentFragment to body
      document.body.appendChild(docFrag);

    }

    function extendDefaults(source, properties) {
      var property;
      for (property in properties) {
        if (properties.hasOwnProperty(property)) {
          source[property] = properties[property];
        }
      }
      return source;
    }

    function initializeEvents() {

      if (this.closeButton) {
        this.closeButton.addEventListener('click', this.close.bind(this));
      }

      if (this.overlay) {
        this.overlay.addEventListener('click', this.close.bind(this));
      }

    }

    function transitionSelect() {
      var el = document.createElement("div");
      if (el.style.WebkitTransition) return "webkitTransitionEnd";
      if (el.style.OTransition) return "oTransitionEnd";
      return 'transitionend';
    }

  }()); // end modal definition

  
}) (jQuery);

/////////////////////////////
var CAAML_SHAPE = {
    "Precipitation Particles":  "iVBORw0KGgoAAAANSUhEUgAAAA0AAAANCAYAAABy6+R8AAAABHNC" +
          "SVQICAgIfAhkiAAAAAlwSFlzAAARagAAEWoBAFXniAAAABl0RVh0U29md" +
          "HdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAABPSURBVCiRY/j//z8DOm" +
          "ZgYEhgYGD4z8DAkIFNnomBDDDINbEwMjJmYBG3hNK2jIyMGJKMDJBQIgk" +
          "wMjAw4LIpjoGBYRkDA8NhDNnReIIAAEJwQ7TYUolnAAAAAElFTkSuQmCC",
    "Machine Made": "iVBORw0KGgoAAAANSUhEUgAAAAsAAAALCAYAAACprHcmAAAABHNC" +
          "SVQICAgIfAhkiAAAAAlwSFlzAAAN/wAADf8B9IqyiQAAABl0RVh0U29md" +
          "HdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAADcSURBVBiVbdGhSsNRGI" +
          "bx37yBsTBYGsgYMqfBbFrxBhZN3oOgyeSQgU0Ek8kgGr0ILTIsWozCYFj" +
          "GwKbH4LvxDx444cDzfrzfc5RSlFKggTGeMMcjzlBfMQEHmAa6wynuscAH" +
          "dsNpYpaJbfQwRBfreE6gDpeZ0MYIPyj4xjE6+EolE9xiO9A1+rhJoIuH7" +
          "GCBExwE7qRfP+8hzjFfwzs28eLvHNVqtR4OA79iA29wVel8EWB5R5XOY2" +
          "jhs2JjB/vYqtiYorH0vJfAf55nGKw+JYFWKk0CTaK1uWR+Aai+ZHz0ufc" +
          "/AAAAAElFTkSuQmCC",
	"Decomposing/Fragmented": "iVBORw0KGgoAAAANSUhEUgAAABMAAAASCAYAAAC5DOVpAAAABHNC" +
          "SVQICAgIfAhkiAAAAAlwSFlzAAAV6wAAFesBBGcFMAAAABl0RVh0U29md" +
          "HdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAADxSURBVDiNpdKrSkRRFA" +
          "bgzwsKIngBMRgmGAwWo+bp+gD6AOIDCD6AYJ9gFqwG44g2g8lgESyCQbB" +
          "PGBh0WfaRrc7tnBN2Wj/fgrV/EaHOww7usFwXaqKDwE0daA/dBL1jsyp0" +
          "gF6CXrEeEapAR/hK0DPWfmYloZOEBB6x8mteAjrLoHss/MuMgUziPIPam" +
          "OubHQFN4zKDrjAzMD8EmsV1Bl1gaujyAdB8anUBtTAx8iR9oCU8ZNDp2J" +
          "/0B1rFUwYdl6pOBjXwkpBPHJYudII28JagHvbLQsmxhY8EdbFbBSqwdoI" +
          "6aFaFCmwRt9iuA0WEb9vULhTE9LrXAAAAAElFTkSuQmCC",
	"Rounded Grains": "iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAYAAABWdVznAAAABHNC" +
          "SVQICAgIfAhkiAAAAAlwSFlzAAATEQAAExEBOWB/8AAAABl0RVh0U29md" +
          "HdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAACASURBVCiRldLRCcJAEE" +
          "XRk00LQsCqtIOsJQYVDPphAeki2Mb6swkRMWYH3t+9zMA8KSVTENHhldO" +
          "h/WAy2OCG9CNX7JZCvwLPUmbFDfCUNuBg+xxhLNgwBtQFG+qAoUAYAi4F" +
          "whkCnv7ff0c1/WGPxwrco5kfl6UKJ9/ViMtqvAGD/nbwFTl/UgAAAABJR" +
          "U5ErkJggg==",
	"Faceted Crystals": "iVBORw0KGgoAAAANSUhEUgAAAA0AAAANCAYAAABy6+R8AAAABHNC" +
          "SVQICAgIfAhkiAAAAAlwSFlzAAARagAAEWoBAFXniAAAABl0RVh0U29md" +
          "HdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAABPSURBVCiR7dExCoBAEE" +
          "PRt2IlXsPK+9/BUrDyImI3Fq61O42VgSFNPhNIQUiqr35ib8hPGNRPa0R" +
          "4OyyILlsNfuhzqLjHPbA15GeMD5TSBUq1JZC/MAMeAAAAAElFTkSuQmCC",
	"Depth Hoar": "iVBORw0KGgoAAAANSUhEUgAAABEAAAAQCAYAAADwMZRfAAAABHNC" +
          "SVQICAgIfAhkiAAAAAlwSFlzAAAStQAAErUB1jM0ZgAAABl0RVh0U29md" +
          "HdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAD1SURBVDiNndMvS0NRGA" +
          "fgZ0GYCoaloRhMi6sGP8NA2wxm4/JAEJtFWF9RMBmXF8XgJ1icmFemIoL" +
          "O8hPm2P9wuLzv+zvPgXvuNRqNzFu4WZhZANQxQn0tBEX0g/RRXAdpBvhb" +
          "zZUQlDHED87zHKK8CtLO6bep71K3l0JQxTfesJveHt7Try6DdHPqxUT/M" +
          "v3uXAS1BF+wOTHbwmvmtakINtBL6HTGuzrLvIeNaUgjgScUZiAFPCfX+I" +
          "eghEGu8nDBV3wUZIDSONLK4H7Rf5L8Q/Kt1Cr4wgf2l0QO8Jl9FehEvVo" +
          "GGIOus68DJ3jE9orITi7h+BfPSUFU8uzJMwAAAABJRU5ErkJggg==",
	"Surface Hoar": "iVBORw0KGgoAAAANSUhEUgAAABEAAAAQCAYAAADwMZRfAAAABHNC" +
          "SVQICAgIfAhkiAAAAAlwSFlzAAAStQAAErUB1jM0ZgAAABl0RVh0U29md" +
          "HdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAEDSURBVDiNldIxTkJBFI" +
          "Xhj4qgiSUJGgsXQENlYa0LoKOwdgVUmBg7K2sqoomVS6Aimli4BhrjBkj" +
          "UWBjG5j6C8B6MxS3mnHP/ezMzcIYn7KaU5Bb28IxTGCPh+p+Qm+gbQxs/" +
          "+MJhJuAI39HXLsRhUB8yIY+RH6aUFGITM8xxvAVwEoAZmgtImP0wX1CrA" +
          "NTwGrn+Ql8K1DGNQK8Cch7+FPU1SIS6EXpDY8XbwXv43T9eybRJBC9X9K" +
          "vQJ2s9JZBOXPAH9kM7wGfona2QaBrF1Ls438d5VJqvgLRikzkuljZrZUM" +
          "CNIjpRQ0qsxsgjXil0tfKggSot+nf5EJquK36wUX9Amy9O8ZyCXfaAAAA" +
          "AElFTkSuQmCC",
	"Melt Forms": "iVBORw0KGgoAAAANSUhEUgAAAA8AAAAPCAYAAAA71pVKAAAABHNC" +
          "SVQICAgIfAhkiAAAAAlwSFlzAAATOQAAEzkBj8JWAQAAABl0RVh0U29md" +
          "HdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAEESURBVCiRndMtS4RBFA" +
          "Xg532DzSDoyiaTitEPxGLS4oLFtn9C8B+Y/RfmBUEMIiIGixi0KWgyycq" +
          "Cot1rmVlGcJfVC5fh3jnnzJmvKiKUUVVVC1tYxQLucY2TiLj4AY4ISaCB" +
          "DmJIHmKiz0nEJroJ8IpdLGMcK9hDL80/Z4FMPk4TZ5jMymViGpfZQd7uT" +
          "mp0BxELgSbeEn6jTocD+xHRMyQi4gUHqdyusZSKq2HEIjJuDT7xhbFhlg" +
          "vrU8n2R40nVJgbceX5ND7UuEvF+ojkjLuBtv+ddis3z/3tnjvlI5nBu9F" +
          "eWBeNPrkQyA4G5TGamVP98qva2MQiZvGIW5xGxFGJ/QaoLuxwkopfZwAA" +
          "AABJRU5ErkJggg==",
	"Ice Formations": "iVBORw0KGgoAAAANSUhEUgAAAA0AAAAICAYAAAAiJnXPAAAABHNC" +
          "SVQICAgIfAhkiAAAAAlwSFlzAAARagAAEWoBAFXniAAAABl0RVh0U29md" +
          "HdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAAYSURBVBiVY2RgYPjPQC" +
          "JgIlXDqCZKNQEAsVMBD3cM+XcAAAAASUVORK5CYII="
  };
	
var primaryGrainTypeList = ["Precipitation Particles", "Machine Made", "Decomposing/Fragmented", 
	"Rounded Grains", "Faceted Crystals", "Depth Hoar", "Surface Hoar", "Melt Forms", "Ice Formations"];
	
/* 
*	Create Dropdown Style List
*/
var grainDropDown = document.createElement("div");



var grainTypeSelectionContainer = document.createElement("div");
	grainTypeSelectionContainer.className = "dropdown-content";

primaryGrainTypeList.forEach(function(e) {
  var d = document.createElement("div");
	d.className = "dropdown-item";
	
  var a = document.createElement("a");
	a.textContent = e;
	a.href = "#";
  
  var symbol = document.createElement("img");
	symbol.setAttribute("src", "data:image/png;base64," + CAAML_SHAPE[e] );
	
  var subTypeContainer = document.createElement("div");
	subTypeContainer.className = "dropright-content";
	
	/* Replace this with subtype array */
	for (i = 0; i < 3; i++) {
		var subA = document.createElement("a");
			subA.textContent = "Subtype here";
			subA.href = "#";
		subTypeContainer.appendChild(subA);
	}
  
  a.appendChild(symbol);
  d.appendChild(a);
  d.appendChild(subTypeContainer);
  grainTypeSelectionContainer.appendChild(d);
});





