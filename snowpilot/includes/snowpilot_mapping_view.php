<?php
$nid = arg(1);
$node = node_load($nid);
$longitude = ''; $latitude = '';


if ( ($node->field_coordinate_type['und'][0]['value'] == 'UTM') && isset( $node->field_east['und'][0]['value'] ) 
&& isset( $node->field_north['und'][0]['value'] ) && isset ( $node->field_utm_zone['und'][0]['value'] )){
	
	$latlong = Toll( $node->field_north['und'][0]['value'] , $node->field_east['und'][0]['value'], $node->field_utm_zone['und'][0]['value'] );
	
	$latitude = $latlong['lat'];
	$longitude = $latlong['lon'];
}elseif ( isset ( $node->field_latitude['und'][0]['value'] ) && isset( $node->field_longitude['und'][0]['value']) ){ // if the user set the gmap location ( lat long ) on an older snowpit, this will still work. 
		$latitude = $node->field_latitude['und'][0]['value'];
		$longitude = $node->field_longitude['und'][0]['value'];
	
}
?>


<link rel="stylesheet" href="https://google-developers.appspot.com/maps/documentation/javascript/demos/demos.css">

<div id="map" style= "height: 450px; width: 600px;"></div>
    <script>
      function initMap() {
        var myLatLng = {lat: <?php echo $latitude  ?>, lng: <?php  echo $longitude; ?>};
        var myLatLng2 = {lat: 45.63, lng: -110.74};
        
        // Create a map object and specify the DOM element for display.
        var map = new google.maps.Map(document.getElementById('map'), {
          center: myLatLng,
          scrollwheel: false,
          zoom: 10,
          mapTypeId: 'terrain'

        });

        // Create a marker and set its position.
        var marker = new google.maps.Marker({
          map: map,
          position: myLatLng,
          title: '<?php echo $node->nid; ?>'
        });
      }

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCIqPh8YaNVnoRZex5LfxLUPnYbFrCaQN0&callback=initMap" async defer></script>

