<?php
$nid = arg(1);
$node = node_load($nid);
if ( isset ( $node->field_latitude['und'][0]['value'])){
     $latitude = $node->field_latitude['und'][0]['value'];
     if ( $node->field_latitude_type['und'][0]['value'] == 'S' && $latitude > 0) $latitude = 0 - $latitude ;
} else { $latitude = ''; }
if ( isset ( $node->field_longitude['und'][0]['value'])){
     $longitude = $node->field_longitude['und'][0]['value'];
     if ( $node->field_longitude_type['und'][0]['value'] == 'W' && $longitude > 0) $longitude = 0 - $longitude ;
}else { $longitude = '';  }
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
          title: 'Hello World!'
        });
      }

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCIqPh8YaNVnoRZex5LfxLUPnYbFrCaQN0&callback=initMap" async defer></script>
