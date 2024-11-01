<?php $this->view('partials/head', array(
	"scripts" => array(
		"clients/client_list.js"
	)
)); ?>

<div class="container">

  <div class="row" style="height: calc(100vh - 136px);">

    <div class="col-md-12" id="map" style="height:100%"></div>

  </div>

</div>  <!-- /container -->

<?php if(env('GOOGLE_MAPS_API_KEY')):?> <?php // Quick fix, should use the conf() system ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?=env('GOOGLE_MAPS_API_KEY')?>"></script>
<?php else:?>
<script src="https://maps.googleapis.com/maps/api/js"></script>
<?php endif?>
<script src="<?php echo rtrim(url(), '/'); ?>/module/location/js/markerclusterer"></script>
<script src="<?php echo conf('subdirectory'); ?>assets/js/munkireport.autoupdate.js"></script>

<script>

var mapObj = {};

mapObj.machines = null;
mapObj.map = null;
mapObj.markerClusterer = null;
mapObj.markers = [];
mapObj.infoWindow = null;
mapObj.options = {gridSize: 30, maxZoom: 20};

mapObj.init = function() {
    var latlng = new google.maps.LatLng(39.91, 116.38);
    var options = {
            'zoom': 2,
            'center': latlng,
            'mapTypeId': google.maps.MapTypeId.ROADMAP
        };

    mapObj.map = new google.maps.Map($('#map')[0], options);

    $.getJSON( appUrl + '/module/location/get_map_data', function( data ) {
        mapObj.machines = data;
        mapObj.infoWindow = new google.maps.InfoWindow();
        mapObj.showMarkers();
    });

};

mapObj.showMarkers = function() {
    mapObj.markers = [];

    if (mapObj.markerClusterer) {
        mapObj.markerClusterer.clearMarkers();
    }

    var numMarkers = mapObj.machines.length;

	var bounds = new google.maps.LatLngBounds();

    for (var i = 0; i < numMarkers; i++) {
        var titleText = mapObj.machines[i].serial_number;
        if (titleText === '') {
          titleText = 'No title';
        }



        var latLng = new google.maps.LatLng(mapObj.machines[i].latitude,
            mapObj.machines[i].longitude);

        var imageUrl = 'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png';
        var markerImage = new google.maps.MarkerImage(imageUrl,
            new google.maps.Size(24, 32));

        var marker = new google.maps.Marker({
          'position': latLng,
          'icon': markerImage
        });

        var fn = mapObj.markerClickFunction(mapObj.machines[i], latLng);
        google.maps.event.addListener(marker, 'click', fn);
        mapObj.markers.push(marker);

		bounds.extend(latLng);
    }

	// Set center and zoom
	var center = bounds.getCenter();
	mapObj.map.fitBounds(bounds);
	mapObj.map.setCenter(center);

    mapObj.markerClusterer = new MarkerClusterer(mapObj.map, mapObj.markers, mapObj.options);
};

// Show popup
mapObj.markerClickFunction = function(machine, latlng) {
    return function(e) {
    e.cancelBubble = true;
    e.returnValue = false;
    if (e.stopPropagation) {
      e.stopPropagation();
      e.preventDefault();
    }
	    
	var machineCode = machine.machine_desc.replace(/\(.*/,'');
	var shortmachineCode = machineCode.replace(/\s+/g, '');
	var configCode = shortmachineCode + '/' + machine.machine_model;
	var iconUrlTemplate = "<?php echo conf('apple_hardware_icon_url');?>";
	var iconUrl = 'https://statici.icloud.com/fmipmobile/deviceImages-9.0/' + configCode +'/online-infobox__2x.png'	

    var infoHtml = '<div class="info">' +
	'<img style="width:120px; height: auto" src="'+iconUrl+'" />' +
      '<div class="info-body">' +
      machine.long_username +
      '<br/>' +
      '<a href="' + appUrl + '/clients/detail/' + machine.serial_number + '#tab_location-tab">' + machine.computer_name +
      '</a></div></div>';

    mapObj.infoWindow.setContent(infoHtml);
    mapObj.infoWindow.setPosition(latlng);
    mapObj.infoWindow.open(mapObj.map);
    };
};

google.maps.event.addDomListener(window, 'load', mapObj.init);

</script>

<?php $this->view('partials/foot'); ?>
