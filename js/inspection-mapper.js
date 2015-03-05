var map;
var json;
var oms;

function initialize() {
    var mapOptions = {
        center: { lat: 51.044308, lng: -114.063091},
        zoom: 12
    };
    map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

    oms = new OverlappingMarkerSpiderfier(map);

    var iw = new google.maps.InfoWindow();
    oms.addListener('click', function(marker, event) {
        console.log(marker.ahs_id);
        getBusiness(marker, iw);
    });
    getData();
}

// Grab the data with an AJAX call
function getData(){
    $.getJSON('/api.php', {all: true})
        .done(function(data){
            json = data;
            //this is here because we need to wait until the AJAX is done before trying to place the markers
            placeMarkers(map);
        })
        .fail(function(jqxhr, textStatus, error) {
            console.log('ERROR: ' + textStatus + ', ' + error);
        });
}
//Make an AJAX call to get the business violations and show them in an info window
function getBusiness(marker, iw){
    var content = '';
            $.getJSON('/api.php',
                {ahsID: marker.ahs_id})
                .done(function(data) {
                    var index = 1;
                    for(var j in data){
                        var v = data[j];
                        content = content + '<b>' + index + ': </b>' + v.comments + '<br />';
                        if(v.critical == 1){
                            content += '<b class="critical">CRITICAL VIOLATION</b><br />';
                        }
                        index++;
                    }
                    index--;
                    if(index == 0){
                        content = '<h1>' + marker.title + '</h1><h2>Yay! No violations<b>';

                    } else {
                        content ='<h1>' + marker.title + '</h1><h2>Total Violations <b>' + index + '</b></h2><br/>' + content;
                    }
                    iw.setContent(content);
                    iw.open(map, marker);
                })
                .fail(function(jqxhr, textStatus, error) {
                    console.log('ERROR: ' + textStatus + ', ' + error);
                });
}
//Put the markers on the map and setup their info windows
function placeMarkers(myMap){
    var infoWindow = new google.maps.InfoWindow();
    var count = 0;
    for(var i in json){
        var item = json[i];
        var lat = item.lat;
        var lng = item.lng;
        var name = item.name;
        var ahs_id = item.ahs_id;
        var latlng = new google.maps.LatLng(lat,lng);
        infoWindow = new google.maps.InfoWindow({
            content: name
        });
        var marker = new google.maps.Marker({
            position: latlng,
            map: map,
            title: name,
            infowindow: infoWindow,
            ahs_id: ahs_id,
            details: '<b>' + name + '</b>'
        });

        oms.addMarker(marker);

        count++;
    }
}

google.maps.event.addDomListener(window, 'load', initialize);