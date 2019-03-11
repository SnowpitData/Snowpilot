<?php
$nid = arg(1);
$node = node_load($nid);
$longitude = ''; $latitude = '';

$zoom = '11';

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



<div id="map" style= "height: 450px; width: 600px;"></div>
    <script>
			var gnfacmap = L.map('map').setView([<?php echo $latitude; ?>, <?php  echo $longitude; ?>], <?php  echo $zoom; ?>);
      var attribution = 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)';
			var BaseMap = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
			    attribution: attribution,
			    maxZoom: 18
			});
			
	    // Add baselayers and overlays to groups
	    var baseLayers = {
	        "Mapbox" : BaseMap,
	    };

	    BaseMap.addTo(gnfacmap);
      var marker = L.marker([<?php echo $latitude; ?>, <?php  echo $longitude; ?>]).addTo(gnfacmap);

    </script>

