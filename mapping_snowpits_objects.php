<div id="snowpits_map" style="height: 650px; width: 650px; display:inline-block;" >&nbsp;</div>
<div id="marker0000" style="width: 450px; height: 380px; border: 2px solid rgb(22, 47, 80); display: inline-block; float: right;">
        <h3 class = "default-profile-text">Select a snowpit on the map to view the profile image</h3>
				</div>
     <script>
			var snowpilotmap = L.map('snowpits_map').setView([45.1000, -110.800], 2);

      var attribution = 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)';
			var tnmBaseMap = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
			    attribution: attribution,
				maxZoom: 14
			});
			
	    // Add baselayers and overlays to groups
	    var baseLayers = {
	        "The National Map (Viewer)" : tnmBaseMap,
	    };
	    var overlays = {
	      
	    };
	    var controlLayers = L.control.layers(baseLayers, overlays);
			
	    tnmBaseMap.addTo(snowpilotmap);
			
								
var Allmarkers = L.markerClusterGroup();  
<?php
		foreach( $results as $Pit ){
			if (($Pit->field_data_field_latitude_field_latitude_value <> '' ) && ($Pit->field_data_field_longitude_field_longitude_value <> '') ){
					?>

      var marker<?php echo  $Pit->nid; ?> = Allmarkers.addLayer(L.marker(
				[ <?php echo $Pit->field_data_field_latitude_field_latitude_value; ?>, <?php echo $Pit->field_data_field_longitude_field_longitude_value?> ],
				{
	        title: '<?php 
					echo str_replace( "'" ,"" ,  $Pit->node_title )/*. " ".date( 'n-j-y' ,$Pit['timestamp']/1000)*/; ?>',
					pid: '<?php echo  $Pit->nid; ?>'
        }));
			  marker<?php echo  $Pit->nid; ?>.options.pid = <?php echo  $Pit->nid; ?>; // makes a pid for use later in the div show	
		<?php
				$images_div_string .= '<div id = "marker'. $Pit->nid .'" style = "width:450px; height:380px;border: 2px solid #162f50; display: none; float: right;">
        <a href = "/sites/default/files/snowpit-profiles/graph-' . $Pit->nid. '.jpg" class = "colorbox" data-colorbox-gallery="gallery-all" >
				<img src = "/sites/default/files/snowpit-profiles/graph-' . $Pit->nid. '.jpg" width = 450px />
				</a>
				</div>';
			}
	  }

?>
Allmarkers.on('click', function (a) {
	var elements = document.querySelectorAll('[id^=marker]');
	// hide all the old graph divs
	elements.forEach(function(userItem) {
		userItem.style.display = 'none';
	});
	piddiv = a.layer.options.pid;
	document.getElementById('marker'+ piddiv).style.display = 'inline-block'; 
});

snowpilotmap.addLayer(Allmarkers);

snowpilotmap.fitBounds(Allmarkers.getBounds());

</script>
<?php
echo $images_div_string;
?>