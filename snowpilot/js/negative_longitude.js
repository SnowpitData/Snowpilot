(function ($) {

  // Behaviors related to Snowpit Profile Forms
  Drupal.behaviors.snowpilot2 = {

    attach: function (context, settings) {
	$("input#edit-field-longitude-und-0-value").blur(function(){
		if (document.getElementById('edit-field-longitude-und-0-value').value > 0 ){
		  document.getElementById('edit-field-longitude-und-0-value').value = 0 - document.getElementById('edit-field-longitude-und-0-value').value ;
		}
	});
}
}
}) (jQuery);