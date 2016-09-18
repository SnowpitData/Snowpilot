<?php
global $user;
global $node;
$nid = arg(1);
$node = node_load($nid);
$existing_node = FALSE;

drupal_add_js('https://maps.googleapis.com/maps/api/js?key=AIzaSyCIqPh8YaNVnoRZex5LfxLUPnYbFrCaQN0');

// default location:
$latitude = 46.2938;
$longitude = -112.01;
$zoom = 6;
if (  !isset($node->nid) || isset($node->is_new) ){
  $account = user_load ( $user->uid);

  if ( isset ( $account->field_loaction['und'][1])){
     $default_location = taxonomy_term_load($account->field_loaction['und'][1]['tid']);
  }elseif ( isset ( $account->field_loaction['und'][0])){
          $default_location = taxonomy_term_load($account->field_loaction['und'][0]['tid']);
  }else{
     $default_location = taxonomy_term_load(1);
  // Montana is the default - default location if NONE other is given.
  } 
	if ( isset ( $default_location->field_lat_center['und'][0]['value']) && isset ( $default_location->field_lng_center['und'][0]['value']) && isset( $default_location->field_zoom_level['und'][0]['value'])){
	  $latitude = $default_location->field_lat_center['und'][0]['value'];
	  $longitude = $default_location->field_lng_center['und'][0]['value'];
	  $zoom = $default_location->field_zoom_level['und'][0]['value'];
	}
} else {  // existing node, let's check for existing lat /long settings and put the map and marker there
	if ( isset ( $node->field_latitude['und'][0]['value'] ) && isset( $node->field_longitude['und'][0]['value']) && isset($node->field_zoom_level['und'][0]['value'] )){
		$latitude = $node->field_latitude['und'][0]['value'];
		$longitude = $node->field_longitude['und'][0]['value'];
		$zoom = $node->field_zoom_level['und'][0]['value']; // TODO : reset zoom so it is appropriate for whatever region the pit is located at
		$existing_node = TRUE ; 
	}
}


?>
<script>
var map;
var marker;

function initialize() {
   var mapOptions = {
      center: new google.maps.LatLng(<?php echo $latitude ; ?>,<?php  echo $longitude; ?> ),
      zoom: <?php echo isset( $zoom ) ? $zoom : '9' ; ?> ,
      mapTypeId: 'terrain'
   };

   map = new google.maps.Map(document.getElementById('google-map'), mapOptions);

   // This event detects a click on the map.
   google.maps.event.addListener(map, "click", function(event) {

      // Get lat lng coordinates.
      // This method returns the position of the click on the map.
      var lat = event.latLng.lat().toFixed(6);
      var lng = event.latLng.lng().toFixed(6);

      // Call createMarker() function to create a marker on the map.
      createMarker(lat, lng);

      // getCoords() function inserts lat and lng values into text boxes.
      getCoords(lat, lng);

   });
   <?php   /// If this is an existing node with already-set lat / long, place the marker in appropriate location
	 if ( $existing_node ){
   	 ?>
		 createMarker(<?php echo $latitude ; ?>,<?php  echo $longitude; ?> );
		 <?php
   }
	 ?>

}
google.maps.event.addDomListener(window, 'load', initialize);

// Function that creates the marker.
function createMarker(lat, lng) {

   // The purpose is to create a single marker, so
   // check if there is already a marker on the map.
   // With a new click on the map the previous
   // marker is removed and a new one is created.

   // If the marker variable contains a value
   if (marker) {
      // remove that marker from the map
      marker.setMap(null);
      // empty marker variable
      marker = "";
   }

   // Set marker variable with new location
   marker = new google.maps.Marker({
      position: new google.maps.LatLng(lat, lng),
      draggable: true, // Set draggable option as true
      map: map
   });


   // This event detects the drag movement of the marker.
   // The event is fired when left button is released.
   google.maps.event.addListener(marker, 'dragend', function() {
      
      // Updates lat and lng position of the marker.
      marker.position = marker.getPosition();

      // Get lat and lng coordinates.
      var lat = marker.position.lat().toFixed(6);
      var lng = marker.position.lng().toFixed(6);

      // Update lat and lng values into text boxes.
      getCoords(lat, lng);

   });
}

// This function updates text boxes values.
function getCoords(lat, lng) {

   // Reference input html element with id="lat".
   var coords_lat = document.getElementById('edit-field-latitude-und-0-value');

   // Update latitude text box.
   coords_lat.value = lat;

   // Reference input html element with id="lng".
   var coords_lng = document.getElementById('edit-field-longitude-und-0-value');

   // Update longitude text box.
   coords_lng.value = lng;
}

function updatePosition(){
    var lat = parseFloat(document.getElementById('edit-field-latitude-und-0-value').value);
    var lon = parseFloat(document.getElementById('edit-field-longitude-und-0-value').value);
    if (lat && lon) {
        //var newPosition = new google.maps.LatLng(lat,lon);
        //placeMarker(newPosition);
        createMarker(lat, lon);
				var latLng = new google.maps.LatLng(lat, lon);
				map.panTo(latLng);
    }
}

// Add listeners to SnowPilot form for latitude and longitude inputs
jQuery("[id^=edit-field-latitude]").blur(updatePosition);
jQuery("[id^=edit-field-longitude]").blur(updatePosition);

</script>