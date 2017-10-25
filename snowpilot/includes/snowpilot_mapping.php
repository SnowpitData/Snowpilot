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
	
	if ( $node->field_coordinate_type['und'][0]['value'] == 'UTM' && isset( $node->field_east['und'][0]['value'] ) && isset( $node->field_north['und'][0]['value'] ) && isset ( $node->field_utm_zone['und'][0]['value'] )){
		
		$latlong = Toll( $node->field_north['und'][0]['value'] , $node->field_east['und'][0]['value'], $node->field_utm_zone['und'][0]['value'] );
		
		$latitude = $latlong['lat'];
		$longitude = $latlong['lon'];
	}elseif ( isset ( $node->field_latitude['und'][0]['value'] ) && isset( $node->field_longitude['und'][0]['value']) ){ // if the user set the gmap location ( lat long ) on an older snowpit, this will still work. 
		$latitude = $node->field_latitude['und'][0]['value'];
		$longitude = $node->field_longitude['und'][0]['value'];
	}
	$existing_node = TRUE ; 
	
}


?>


<script>


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
	 if ( $existing_node ){  ?>
		 createMarker(<?php echo $latitude ; ?>,<?php  echo $longitude; ?> );
		 <?php } ?>

}
google.maps.event.addDomListener(window, 'load', initialize);


// Add listeners to SnowPilot form for latitude and longitude inputs
jQuery("[id^=edit-field-latitude]").blur(updatePosition);
jQuery("[id^=edit-field-longitude]").blur(updatePosition);
// Add listeners for UTM coords
jQuery("[id^=edit-field-east]").blur(updatePositionUtm);
jQuery("[id^=edit-field-north]").blur(updatePositionUtm);
jQuery("[id^=edit-field-utm-zone-und]").blur(updatePositionUtm);
// add listeners for MGRS coords
jQuery("[id^=edit-field-mgrs-easting]").blur(updatePositionMgrs);
jQuery("[id^=edit-field-mgrs-northing]").blur(updatePositionMgrs);
jQuery("[id^=edit-field-utm-zone]").blur(updatePositionMgrs);
jQuery("[id^=edit-field-100-km-grid-square-id]").blur(updatePositionMgrs);

</script>
