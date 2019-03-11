    <script>
			var snowpilotmap = L.map('snowpits_map').setView([45.1000, -110.800], 8);

      var attribution = 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)';
			var tnmBaseMap = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
			    attribution: attribution,
				maxZoom: 1
			});

	    var highways = L.tileLayer.wms("https://services.nationalmap.gov/ArcGIS/services/transportation/MapServer/WMSServer?", {
	        layers: '6',
	        format: 'image/png',
	        transparent: true,
	    });
			
	    // Add baselayers and overlays to groups
	    var baseLayers = {
	        "The National Map (Viewer)" : tnmBaseMap,
	    };
	    var overlays = {
	        "Highways": highways
	    };
	    var controlLayers = L.control.layers(baseLayers, overlays);
			
	    tnmBaseMap.addTo(snowpilotmap);
			
								
<?php				
			$State_string = "";
			$OBS_DATE_MIN = date( 'Y-m-d'  , time() - 30*24*60*60); // one month ago

  $path = "https://snowpilot.org/snowpilot-query-feed.xml?". $State_string ."OBS_DATE_MIN=" . $OBS_DATE_MIN . "&OBS_DATE_MAX&USERNAME=&AFFIL=none&testpit=0&per_page=100";		
	//$path = "https://snowpilot.org/snowpilot-query-feed.xml?PIT_NAME=&&OBS_DATE_MIN=2018-02-01&OBS_DATE_MAX&USERNAME=&testpit=0&per_page=200";
	
	$Data = @file_get_contents($path);
	

	$xml = simplexml_load_string($Data);
	$json = json_encode($xml);
	$snowpits_array = json_decode($json,TRUE);

	$images_div_string= '';
	?>var Allmarkers = L.markerClusterGroup();  <?php
		foreach($xml->Pit_Observation as $Pit ){
			if (($Pit->Location['lat'] <> '' ) && ($Pit->Location['longitude'] <> '') ){
					?>

      var marker<?php echo  $Pit['nid']; ?> = Allmarkers.addLayer(L.marker(
				[ <?php echo $Pit->Location['lat']; ?>, <?php echo $Pit->Location['longitude']?> ],
				{
	        title: '<?php 
					echo str_replace( "'" ,"" ,  $Pit->Location['name'] ). " ".date( 'n-j-y' ,$Pit['timestamp']/1000); ?>',
					pid: '<?php echo  $Pit['nid']; ?>'
        }));
			  marker<?php echo  $Pit['nid']; ?>.options.pid = <?php echo  $Pit['nid']; ?>; // makes a pid for use later in the div show
			;			
		<?php
				$images_div_string .= '<div id = "marker'. $Pit['nid'] .'" style = "width:450px; height:380px;border: 2px solid #162f50; display: none; float: right;">
        <a href = "https://snowpilot.org/sites/default/files/snowpit-profiles/graph-' . $Pit['nid']. '.jpg" class = "colorbox" data-colorbox-gallery="gallery-all" >
				<img src = "https://snowpilot.org/sites/default/files/snowpit-profiles/graph-' . $Pit['nid']. '.jpg" width = 450px />
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

</script>

	<?php 

// this is the default profile image for mapping; it may get overwritten if all of site/default/files/snowpit-profiles is wiped out and regenerated
$default_profile_img_string = '<div id="marker0000" style="width: 450px; height: 380px; border: 2px solid rgb(22, 47, 80); display: inline-block; float: right;">
        <h3 class = "default-profile-text">Select a snowpit on the map to view the profile image</h3>
				</div>';
echo $default_profile_img_string . $images_div_string; ?>

