

require([
    "esri/map",
    "esri/geometry/Extent",
    "esri/SpatialReference",
    "dojo/domReady!"
], function(Map, Extent, SpatialReference) {
    var map;

    var latLow = document.getElementById('event_bounds_latLow');
    var latHigh = document.getElementById('event_bounds_latHigh');
    var lngLow = document.getElementById('event_bounds_lngLow');
    var lngHigh = document.getElementById('event_bounds_lngHigh');

    var beginExtent = new Extent(latLow.value, lngLow.value,latHigh.value, lngHigh.value, new SpatialReference({ wkid:4326 }));


    map = new Map("map", {
        basemap: "hybrid",
        center: [4.53681008, 51.88391507],
        zoom: 10
    });

    $(document).ready(function() {
        map.on("load", function(){

            if(latLow.hasAttribute("value")) map.setExtent(beginExtent);
            map.on("extent-change", function(){


                var geo = map.geographicExtent;
                latLow.value = geo.xmin;
                latHigh.value = geo.xmax;
                lngLow.value = geo.ymin;
                lngHigh.value = geo.ymax;
            });

        });

    })
});

$(document).ready(function() {
    $("#event_startDate").datetimepicker({
        step:15
    });
    $("#event_endDate").datetimepicker({
        step:15
    });
});


