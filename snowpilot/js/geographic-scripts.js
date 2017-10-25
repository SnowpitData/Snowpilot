/*
   Use this file for js functions related to conversions between geographic reference types
// UTM to Lat/Long, MGRS, etc
//  
*/

var map;
var marker;

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

		lat.value = lat_pos_decimal;
		lon.value = long_pos_decimal;
		updatePosition();

	}
}


