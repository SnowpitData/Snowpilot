(function ($) {

  // Behaviors related to Snowpit Profile Forms
  Drupal.behaviors.snowpilot3 = {

    attach: function (context, settings) {
	$("input#edit-field-latitude-und-0-value").keyup(function(){
		if (document.getElementById('edit-field-latitude-und-0-value').value > 0 ){
			console.log("Your user preferences indicate that you are using southern hemisphere default, but your latitude as entered is positive. Just verify that your location is correct.");
		  //document.getElementById('edit-field-latitude-und-0-value').value = 0 - document.getElementById('edit-field-latitude-und-0-value').value ;
		}
	});
}
}
}) (jQuery);