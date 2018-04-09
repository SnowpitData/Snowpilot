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
		$existing_node = TRUE ; 
	}elseif ( isset ( $node->field_latitude['und'][0]['value'] ) && isset( $node->field_longitude['und'][0]['value']) ){ // if the user set the gmap location ( lat long ) on an older snowpit, this will still work. 
		$latitude = $node->field_latitude['und'][0]['value'];
		$longitude = $node->field_longitude['und'][0]['value'];
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
elevator = new google.maps.ElevationService();
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
	 
	 var elevation = document.getElementById('edit-field-elevation-und-0-value');
	 
	 var latlng = new google.maps.LatLng (parseFloat(lat),parseFloat(lng));
	 	var obj=new Object();
	 	obj.latLng=latlng;
	 	getElevation(latlng);
		
		
		
	 //
	 //
	 // start calculating utm and mgrs field values
	 //
 	var lat_long_pos = new LatLon(lat, lng);
 	var utm_pos = lat_long_pos.toUtm();
	
 	var mgrs_pos = utm_pos.toMgrs();
	
	var mgrs_pos_string = mgrs_pos.toString();
	var mgrs_pos_zone =  mgrs_pos_string.match(/^[0-9]+(\w)/g);
	var mgrs_pos =  /^([0-9]+\w) (\w\w) (\d+) (\d+)/g.exec(mgrs_pos_string);
	
	var utm_pos_string = utm_pos.toString();
	var utm_pos_array = /^([0-9]+) (\w) (\d+) (\d+)/g.exec(utm_pos_string);
	
 	console.log( 'in create marker UTM: '+ utm_pos + ' utm array: ' + utm_pos_array + ' MGRS string: ' + mgrs_pos_string[0] );
	//console.log( mgrs_pos_lat_ident.exec())
	
	// UTM zone is included for both MRGS and UTM coors system
	var zone_id = document.getElementById('edit-field-utm-zone-und');
	zone_id.value = mgrs_pos[1];
	
	// MGRS-specific settings
	//
	var mgrs_grid_id = document.getElementById('edit-field-100-km-grid-square-id-und-0-value');
	var mgrs_easting = document.getElementById('edit-field-mgrs-easting-und-0-value');
	var mgrs_northing = document.getElementById('edit-field-mgrs-northing-und-0-value');
	
	mgrs_grid_id.value = mgrs_pos[2];
	mgrs_easting.value = mgrs_pos[3];
	mgrs_northing.value = mgrs_pos[4];
	//
	//  UTM field settings
	//
	var utm_east = document.getElementById('edit-field-east-und-0-value');
	var utm_north = document.getElementById('edit-field-north-und-0-value');
	
	utm_east.value = utm_pos_array[3];
	utm_north.value = utm_pos_array[4];

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

function updatePositionUtm(){
  var lat = document.getElementById('edit-field-latitude-und-0-value');
  var lon = document.getElementById('edit-field-longitude-und-0-value');
	
	var east = parseFloat(document.getElementById('edit-field-east-und-0-value').value);
	var north = parseFloat(document.getElementById('edit-field-north-und-0-value').value);
	var utm_zone = parseFloat(document.getElementById('edit-field-utm-zone-und').value);
	
	if ( east && north && utm_zone) {
	  utm_pos = new Utm(utm_zone, 'north', east, north );
		
		var lat_long_pos = utm_pos.toLatLonE();
		
		var lat_long_string = utm_pos.toLatLonE().toString('d',6);
		var lat_long_pos_array = /^([0-9\.]+°[NS]), ([0-9\.]+°[EW])/g.exec(lat_long_string);
		
		var lat_pos_decimal = Dms.parseDMS(lat_long_pos_array[1]);
		var long_pos_decimal = Dms.parseDMS(lat_long_pos_array[2]);
		console.log('updatepositionUTM: '+ lat_long_string + ' decimal: '+ lat_pos_decimal + '  ' + long_pos_decimal);
		lat.value = lat_pos_decimal;
		lon.value = long_pos_decimal;
		updatePosition();
	
	}
}

function updatePositionMgrs(){
  var lat = document.getElementById('edit-field-latitude-und-0-value');
  var lon = document.getElementById('edit-field-longitude-und-0-value');
	
	var easting = document.getElementById('edit-field-mgrs-easting-und-0-value').value;
	var northing = document.getElementById('edit-field-mgrs-northing-und-0-value').value;
	var utm_zone = document.getElementById('edit-field-utm-zone-und').value;
	var grid_id = document.getElementById('edit-field-100-km-grid-square-id-und-0-value').value;
	console.log( 'got to update postion MGRS' + easting + ' ' +  northing +' ' +  grid_id + ' '+ utm_zone );
	
	if ( easting && northing && utm_zone && grid_id ) {
		
		//var mgrs_pos_array = /^([0-9]{1,2})([A-Z]) ([A-Z])([A-Z]) (\d{0,5}) (\d{0,5})$/g.exec();
		var band_array = /^([0-9]{1,2})([A-Z])/g.exec(utm_zone);
		var zone = band_array[1];
		var band = band_array[2];
		
		var grid_id_array = /^([A-Z])([A-Z])$/.exec(grid_id);
		var grid_id_e = grid_id_array[1];
		var grid_id_n = grid_id_array[2];
		
	  var mgrs_pos = new Mgrs(zone, band ,grid_id_e, grid_id_n, easting, northing );
		var utm_pos = mgrs_pos.toUtm();
		
		var lat_long_pos = utm_pos.toLatLonE();
		
		var lat_long_string = utm_pos.toLatLonE().toString('d',6);
		var lat_long_pos_array = /^([0-9\.]+°[NS]), ([0-9\.]+°[EW])/g.exec(lat_long_string);
		
		var lat_pos_decimal = Dms.parseDMS(lat_long_pos_array[1]);
		var long_pos_decimal = Dms.parseDMS(lat_long_pos_array[2]);
		console.log('updateposition MGRS: '+ lat_long_string + ' decimal: '+ lat_pos_decimal + '  ' + long_pos_decimal);
		lat.value = lat_pos_decimal;
		lon.value = long_pos_decimal;
		updatePosition();
	
	}
}

function getElevation(clickedLocation) 
{
	var locations = [];
		
	locations.push(clickedLocation);
	
	// Create a LocationElevationRequest object using the array's one value
	var positionalRequest = {'locations': locations};
	console.log("IN get elevation "+clickedLocation);
	console.log(positionalRequest);
	
	// Initiate the location request
	elevator.getElevationForLocations(positionalRequest, function(results, status) 
	{
		
		if (status == google.maps.ElevationStatus.OK) 
		{
			// Retrieve the first result
			if (results[0]) 
			{
				// Open an info window indicating the elevation at the clicked position
			console.log( "elevation: " + results[0].elevation.toFixed(3) );
				
				//add the marker
	
				
				output_lat.push(clickedLocation.lat());
				output_lng.push(clickedLocation.lng());
				output_f.push((results[0].elevation*3.2808399).toFixed(3));
				output_m.push(results[0].elevation.toFixed(3));
				
			} 
			
	  	} 
	  	
	});
}

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
