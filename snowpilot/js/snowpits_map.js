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

var markers = Drupal.settings.snowpilot;
var marker = {};
for (var  key in markers) {
	console.log(markers[key].nid);
	var markername = 'marker'+markers[key].nid;
	console.log(markername);
        marker[markername] = Allmarkers.addLayer(L.marker(
				[ markers[key].lat, markers[key].long ],
				{
	        title: markers[key].title,
					pid: markers[key].nid
        }));
			  marker[markername].options.pid = markers[key].nid; // makes a pid for use later in the div show	
  }		
		
		
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
