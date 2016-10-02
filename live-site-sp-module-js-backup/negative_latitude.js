(function ($) {

  // Behaviors related to Snowpit Profile Forms
  Drupal.behaviors.snowpilot3 = {

    attach: function (context, settings) {
	$("input#edit-field-latitude-und-0-value").keyup(function(){
		if (document.getElementById('edit-field-latitude-und-0-value').value > 0 ){
		  document.getElementById('edit-field-latitude-und-0-value').value = 0 - document.getElementById('edit-field-latitude-und-0-value').value ;
		}
	});
}
}
}) (jQuery);