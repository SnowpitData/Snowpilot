	var latitude = Drupal.settings.snowpilot.marker.latitude;
	var longitude = Drupal.settings.snowpilot.marker.longitude;
	var attribution = Drupal.settings.snowpilot.marker.attribution;
	var basemap = Drupal.settings.snowpilot.marker.basemap;
	var zoom = Drupal.settings.snowpilot.marker.zoom;
	var existing = Drupal.settings.snowpilot.marker.existing;
	
	var snowpilotmap = L.map('snowpilot-map').setView([latitude, longitude], zoom);
	    snowpilotmap.invalidateSize();
	var BaseMap = L.tileLayer(basemap, {
	    attribution: attribution ,
	    maxZoom: 18
	});

	
   // Add baselayers and overlays to groups
   var baseLayers = {
       "Mapbox" : BaseMap,
   };

   BaseMap.addTo(snowpilotmap);

	var marker = L.marker([latitude, longitude], {draggable:'true'} );
	  if ( existing == 'true'){
		  marker.addTo(snowpilotmap);
	  }
		 marker.on('dragend', function (e) {
		   updatePosition(marker.getLatLng().lat, marker.getLatLng().lng);
		 });
		 snowpilotmap.on('click', function (e) {
		   marker.addTo(snowpilotmap);
		   marker.setLatLng(e.latlng);
		   updatePosition(marker.getLatLng().lat, marker.getLatLng().lng);
		 });

// Add listeners to form for latitude and longitude inputs
		 window.onload = function() {
		   jQuery("[id^=edit-field-latitude]").blur(updatePositionreverse );
		   jQuery("[id^=edit-field-longitude]").blur(updatePositionreverse );
		 };
		 

		 function updatePosition(lat, lng ){	
		 	document.getElementById('edit-field-latitude-und-0-value').value = marker.getLatLng().lat.toFixed(6);
		 	document.getElementById('edit-field-longitude-und-0-value').value = marker.getLatLng().lng.toFixed(6);
		 	snowpilotmap.panTo([lat,lng]);
		 	marker.setLatLng([lat,lng]);
			fetch_elevation(lat, lng);
      getCoords(lat, lng);
		 }
		 function fetch_elevation(lat, lng){
			 jQuery.ajax({url: "https://elevation-api.io/api/elevation?key=0u6Ymc8JF8jg8uxEVP8Gu4c-669v3G&resolution=30-interpolated&points=("+lat+","+lng+")", success: function(result){
				 	 var elevation_field = document.getElementById('edit-field-elevation-und-0-value');
					 elev_units = document.getElementById('edit-field-elevation-units-und').value;
					 if ( elev_units == 'ft'){ 
						 var simple_elev = result.elevations[0].elevation * 3.2808399 ;
					 }else{ 
						 var simple_elev = result.elevations[0].elevation; }
					elevation_field.value =  simple_elev.toFixed(0);
			 }});
		 }

		 function updatePositionreverse(){
		 	lat = document.getElementById('edit-field-latitude-und-0-value').value;
		 	lng = document.getElementById('edit-field-longitude-und-0-value').value;
		 	if ( lat && lng){
		 	  marker.setLatLng([lat,lng]);
		 		marker.addTo(snowpilotmap);
		 	  snowpilotmap.panTo([lat,lng]);
		   }
		 }
		 setTimeout(function () {
		     snowpilotmap.invalidateSize();
		 }, 0);
// This function updates text boxes values.
function getCoords(lat, lng) {

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
		updatePosition(lat_pos_decimal,long_pos_decimal );
	
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
		updatePosition( lat_pos_decimal ,long_pos_decimal);
	
	}
}

// Add listeners to SnowPilot form for latitude and longitude inputs
jQuery("[id^=edit-field-latitude]").blur(updatePositionreverse);
jQuery("[id^=edit-field-longitude]").blur(updatePositionreverse);

// Add listeners for UTM coords
jQuery("[id^=edit-field-east]").blur(updatePositionUtm);
jQuery("[id^=edit-field-north]").blur(updatePositionUtm);
jQuery("[id^=edit-field-utm-zone-und]").blur(updatePositionUtm);

// add listeners for MGRS coords

jQuery("[id^=edit-field-mgrs-easting]").blur(updatePositionMgrs);
jQuery("[id^=edit-field-mgrs-northing]").blur(updatePositionMgrs);
jQuery("[id^=edit-field-utm-zone]").blur(updatePositionMgrs);
jQuery("[id^=edit-field-100-km-grid-square-id]").blur(updatePositionMgrs);

