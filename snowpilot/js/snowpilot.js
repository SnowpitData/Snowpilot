/*if (Drupal.jsEnabled) {
  $(document).ready(function () {
    $('div.collapsible-container').find('div.collapsible-content').hide().end().find('div.collapsible-handle').click(function() {
      $(this).next().slideToggle(); 
    });
  });
}
*/
 (function($) {
  // This jQuery function is called when the document is ready
  $(function() {
    $('.collapsible-content.collapsed').hide();
     $('#edit-field-layer .layer_num_0 h3.collapsible-handle').click(function() {
        $('.layer_num_0 .collapsible-content').toggle('slow', function() {
          // Animation complete.
        });
        //add css class to H2 title when clicked//
        $(this).toggleClass("open");
      });
      $('#edit-field-layer .layer_num_1 h3.collapsible-handle').click(function() {
         $('.layer_num_1 .collapsible-content').toggle('slow', function() {
           // Animation complete.
         });
         //add css class to H2 title when clicked//
         $(this).toggleClass("open");
       });
			 //repeat through layer number 20 ...
  });
})(jQuery);