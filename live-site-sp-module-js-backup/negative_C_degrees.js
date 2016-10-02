(function ($) {

  // Behaviors related to Snowpit Profile Forms
  Drupal.behaviors.snowpilot2 = {
		alert( "here: "+ document.getElementById('.field-name-field-temp-temp :input').value );

    attach: function (context, settings) {			
	$(".field-name-field-temp-temp :input").keyup(function(){
		if (document.getElementById(".field-name-field-temp-temp :input").value > 0 ){
		  document.getElementById(".field-name-field-temp-temp :input").value = 0 - document.getElementById(".field-name-field-temp-temp :input").value ;
		}
	});
}
}
}) (jQuery);