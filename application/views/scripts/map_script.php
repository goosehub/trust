<!-- Google Map Element -->
<div id="map"></div>

<!-- Start Script -->
<script>
// Create overlay that will be removed when map tiles are loaded
loading = function() {
    var over = '<div id="overlay"><p>Loading...</p></div>';
    $(over).appendTo('body');
};
loading();

// Marker options
// https://sites.google.com/site/gmapsdevelopment/
var classic_marker_img = 'https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi.png';
var green_marker_img = 'https://maps.google.com/mapfiles/ms/icons/green-dot.png';
var blue_marker_img = 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png';
var red_marker_img = 'https://maps.google.com/mapfiles/ms/icons/red-dot.png';
var purple_marker_img = 'https://maps.google.com/mapfiles/ms/icons/purple-dot.png';
var yellow_marker_img = 'https://maps.google.com/mapfiles/ms/icons/yellow-dot.png';
var orange_marker_img = 'https://maps.google.com/mapfiles/ms/icons/orange-dot.png';
var light_blue_marker_img = 'https://maps.google.com/mapfiles/ms/icons/ltblue-dot.png';
var pink_marker_img = 'https://maps.google.com/mapfiles/ms/icons/pink-dot.png';

// Map room polling
var map_room_polling_seconds = <?php echo MAP_ROOM_POLLING_SECONDS; ?>;
var current_last_activity_slug = '<?php echo $current_last_activity_filter['slug']; ?>';

// Constants for markers
var default_marker_img = blue_marker_img;
var current_marker_img = classic_marker_img;
var favorite_marker_img = green_marker_img;
var crew_marker_img = purple_marker_img;
var joined_crew_marker_img = orange_marker_img;
// Google Map Created Callback
var map;
var messages_load_interval_id;
var current_marker = false;
var markers = [];
var map_default_zoom = <?php echo MAP_DEFAULT_ZOOM; ?>;
var map_default_focus_zoom = <?php echo MAP_DEFAULT_FOCUS_ZOOM; ?>;
var map_default_lat = <?php echo MAP_DEFAULT_LAT; ?>;
var map_default_lng = <?php echo MAP_DEFAULT_LNG; ?>;
var smart_lat = <?php echo $smart_lat ? $smart_lat : 'false'; ?>;
var smart_lng = <?php echo $smart_lng ? $smart_lng : 'false'; ?>;
var smart_zoom = <?php echo $smart_zoom ? $smart_zoom : 'false'; ?>;
if (is_mobile) {
    map_default_lat = <?php echo MAP_MOBILE_DEFAULT_LAT; ?>;
    map_default_lng = <?php echo MAP_MOBILE_DEFAULT_LNG; ?>;
}
if (smart_lat && smart_lng) {
    map_default_lat = smart_lat;
    map_default_lng = smart_lng;
}
if (smart_zoom) {
    map_default_zoom = smart_zoom;
}
function initMap() {
    geocoder = new google.maps.Geocoder();

    // var defaultMapType = google.maps.MapTypeId.TERRAIN
    var defaultMapType = google.maps.MapTypeId.HYBRID;
    // var defaultMapType = google.maps.MapTypeId.SATELLITE

    // Create map init
    map = new google.maps.Map(document.getElementById('map'), {
        center: {
            lat: getParameterByName('lat') ? parseFloat(getParameterByName('lat')) : map_default_lat,
            lng: getParameterByName('lng') ? parseFloat(getParameterByName('lng')) : map_default_lng
        },
        zoom: getParameterByName('zoom') ? parseInt(getParameterByName('zoom')) : map_default_zoom,
        minZoom: 1,
        mapTypeId: getParameterByName('map_type') ? getParameterByName('map_type') : defaultMapType
    });

    // Remove overlay when map tiles are loaded
    google.maps.event.addListenerOnce(map, 'tilesloaded', function() {
        $('#overlay').fadeOut();
    });

    // Add markers to map
    // This loop might be thousands large
    // Keep this performant and minimize bytes transfered
    <?php foreach ($rooms as $room) { ?>
    // Create marker
    var location = {};
    location.lat = <?php echo $room['lat']; ?>;
    location.lng = <?php echo $room['lng']; ?>;
    marker = new google.maps.Marker({
        position: location,
        map: map,
        title: '<?php echo addcslashes($room['name'], "'"); ?>',
        room_id: <?php echo $room['id'] ?>,
        is_base: <?php echo $room['is_base'] ?>,
        icon: find_icon_to_use(<?php echo $room['id'] ?>, <?php echo $room['is_base'] ?>)
    });

    // Open room on click
    marker.addListener('click', marker_clicked);

    // Add to marker array
    markers[<?php echo $room['id'] ?>] = marker;
    <?php } ?>

    // If hash exists, zoom on room
    if (window.location.hash) {
        // Get room id
        var room_id = window.location.hash.replace('#', '');

        // Zoom
        // map.setZoom(17);
        // map.panTo(markers[room_id].position);
    }

    // Trigger event on click
    google.maps.event.addListener(map, 'click', function(event) {
        open_create_room_block(event.latLng);
    });

    // Map zoom in and out
    $('#zoom_in_button').click(function(){
        $('#zoom_in_button').hide();
        $('#zoom_out_button').show();

        // Center and zoom
        map.setCenter({
            lat: current_marker.getPosition().lat(),
            lng: current_marker.getPosition().lng()
        });
        map.setZoom(map_default_focus_zoom);
    });

    // Map zoom in and out
    $('#zoom_out_button').click(function(){
        $('#zoom_out_button').hide();
        $('#zoom_in_button').show();

        // Center and zoom
        map.setCenter({
            lat: map_default_lat,
            lng: map_default_lng
        });
        map.setZoom(map_default_zoom);

        // Reload markers
        load_map_rooms();
    });

    $('#address_search_button').click(function(){
        zoomInOnAddress($('#address_search_input').val());
    });

    $('#address_search_input').on('keypress',function(e) {
        if (e.which == 13) {
            zoomInOnAddress($('#address_search_input').val());
        }
    });

    // Load map rooms on interval
    setInterval(function(){
        load_map_rooms();
    }, map_room_polling_seconds * 1000);

    function load_map_rooms() {
        var url = 'main/load_map_rooms?last_activity=' + current_last_activity_slug;
        ajax_get(url, function(result){
            // Remove existing markers
            Object.keys(markers).forEach(function(key) {
                markers[key].setMap(null);
            });

            // current_marker_room_id = current_marker.room_id;

            $.each(result, function(i, room) {
                var location = {};
                location.lat = parseFloat(room.lat);
                location.lng = parseFloat(room.lng);
                marker = new google.maps.Marker({
                    position: location,
                    map: map,
                    title: room.name,
                    room_id: parseInt(room.id),
                    is_base: parseInt(room.is_base),
                    icon: default_marker_img
                });
                if (room.id == current_marker.room_id) {
                    marker.setIcon(current_marker_img);
                    current_marker = marker;
                }
                else if (room.id) {
                    marker.setIcon(find_icon_to_use(room.id, room.is_base));
                }

                // Open room on click
                marker.addListener('click', marker_clicked);

                // Add to marker array
                markers[parseInt(room.id)] = marker;
            });
        });
    }

    function marker_clicked(event) {
        // var lat = event.latLng.lat();
        // var lng = event.latLng.lng();

        // Hide any center blocks
        $('.center_block').hide();
        // Load room
        load_room(this.room_id);
    }

    // Place marker
    function open_create_room_block(location) {
        $('.center_block').hide();
        $('#create_room_block').show();
        $('#input_room_name').val('');
        $('#input_room_name').focus();

        // Create room on enter key
        $('#input_room_name').off().on('keyup', function (e) {
            if (e.keyCode == 13 && $('#input_room_name').val()) {
                create_room(location);
            }
        });

        // Create room on button click
        $('#create_room_submit').off().click(function(){
            create_room(location);
        });
    }

    function create_room(location) {
        // if (!$('#input_room_name').val()) {
        //     alert('Room name required');
        //     return false;
        // }
        // if ($('#input_is_base').val() && !$('#input_room_passcode').val()) {
        //     alert('Crew rooms require a passcode');
        //     return false;
        // }
        var url = 'room/create';
        var data = {};
        data.lat = location.lat();
        data.lng = location.lng();
        data.room_name = $('#input_room_name').val();
        data.is_base = $('#input_is_base').val();
        data.room_passcode = $('#input_room_passcode').val();
        ajax_post(url, data, function(result){
            if (result.error) {
                alert(result.error_message);
                return false;
            }
            $('.center_block').hide();
            // Create marker
            var marker = new google.maps.Marker({
                position: location,
                map: map,
                title: result.name,
                room_id: result.id,
                is_base: result.is_base,
            });

            // Open room on click
            marker.addListener('click', marker_clicked);

            // Add to marker array
            markers[result.id] = marker;

            // Switch marker icons
            if (current_marker) {
                current_marker.setIcon(find_icon_to_use(current_marker.room_key, current_marker.is_base));
            }
            markers[result.id].setIcon(current_marker_img);
            current_marker = markers[result.id];

            if (data.is_base) {
                joined_room_keys.push(result.id);
            }

            // Load room
            load_room(result.id);
        });
    }

    function zoomInOnAddress(address) {
        geocoder.geocode( { 'address' : address }, function( results, status ) {
            if (status == google.maps.GeocoderStatus.OK) {
                map.setCenter(results[0].geometry.location);
                map.setZoom(map_default_focus_zoom);
            } else {
                console.error('Geocode was not successful for the following reason: ' + status);
            }
        } );
    }

}

function find_icon_to_use(room_id, room_is_base) {
    room_id = parseInt(room_id);
    room_is_base = parseInt(room_is_base);
    if (favorite_room_keys.includes(room_id)) {
        return favorite_marker_img;
    }
    if (joined_room_keys.includes(room_id)) {
        return joined_crew_marker_img;
    }
    if (room_is_base) {
        return crew_marker_img;
    }
    return default_marker_img;
}
</script>

<!-- Google Maps Script -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD_lT8RkN6KffGEfJ3xBcBgn2VZga-a05I&callback=initMap" async defer>
</script>