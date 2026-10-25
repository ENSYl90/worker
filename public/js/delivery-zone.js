const CLOSE_VERTEX_PIXEL_THRESHOLD = 10;

function pixelDistance(mapInstance, latLngA, latLngB) {
    var projection = mapInstance.getProjection();
    var scale = Math.pow(2, mapInstance.getZoom());
    var pointA = projection.fromLatLngToPoint(latLngA);
    var pointB = projection.fromLatLngToPoint(latLngB);
    return Math.sqrt(
        Math.pow((pointA.x - pointB.x) * scale, 2) +
        Math.pow((pointA.y - pointB.y) * scale, 2)
    );
}

function distanceFrom(lat1, lng1, lat2, lng2) {
    var lat = [lat1, lat2]
    var lng = [lng1, lng2]
    var R = 6378137;
    var dLat = (lat[1] - lat[0]) * Math.PI / 180;
    var dLng = (lng[1] - lng[0]) * Math.PI / 180;
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat[0] * Math.PI / 180) * Math.cos(lat[1] * Math.PI / 180) *
        Math.sin(dLng / 2) * Math.sin(dLng / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var d = R * c;
    return Math.round(d);
}

// Each map instance (the "new zone" map and the "edit zone" map) gets its own
// independent controller so drawing state never leaks between the two widgets.
function createZoneMapController(elementId) {
    var map = new google.maps.Map(document.getElementById(elementId), {
        center: { lat: 0.00, lng: 0.00 },
        zoom: 8,
    });

    var state = {
        map: map,
        lastPolygon: null,
        drawVertices: [],
        drawMarkers: [],
        drawPolygon: null,
        mapClickListener: null,
    };

    function clearDrawingState() {
        state.drawMarkers.forEach((marker) => marker.setMap(null));
        state.drawMarkers = [];
        state.drawVertices = [];
        if (state.drawPolygon != null) {
            state.drawPolygon.setMap(null);
            state.drawPolygon = null;
        }
    }

    function emitCoordinates(polygon) {
        var coordinates = polygon.getPath().getArray();
        livewire.emit('selectedCoordinates', coordinates);
    }

    function makePolygonEditable(polygon) {
        polygon.setOptions({ editable: true, draggable: true });
        // 'mouseup' fires once when a vertex handle or the whole polygon is released,
        // unlike path 'set_at'/'insert_at' which fire continuously while dragging.
        google.maps.event.addListener(polygon, 'mouseup', () => emitCoordinates(polygon));
        google.maps.event.addListener(polygon, 'dragend', () => emitCoordinates(polygon));
    }

    function finishActiveDrawing() {
        if (state.drawPolygon == null || state.drawVertices.length < 3) {
            return;
        }
        makePolygonEditable(state.drawPolygon);
        if (state.lastPolygon != null && state.lastPolygon !== state.drawPolygon) {
            state.lastPolygon.setMap(null);
        }
        state.lastPolygon = state.drawPolygon;
        state.drawMarkers.forEach((marker) => marker.setMap(null));
        state.drawMarkers = [];
        state.drawPolygon = null;
        state.drawVertices = [];
        emitCoordinates(state.lastPolygon);
    }

    function startFreehandDrawing() {
        if (state.mapClickListener != null) {
            google.maps.event.removeListener(state.mapClickListener);
        }
        clearDrawingState();

        state.mapClickListener = google.maps.event.addListener(map, 'click', function (event) {
            var clickedLatLng = event.latLng;

            if (state.drawVertices.length >= 3) {
                var firstVertex = state.drawVertices[0];
                var distance = pixelDistance(map, clickedLatLng, firstVertex);
                if (distance <= CLOSE_VERTEX_PIXEL_THRESHOLD) {
                    finishActiveDrawing();
                    return;
                }
            }

            state.drawVertices.push(clickedLatLng);

            var marker = new google.maps.Marker({
                position: clickedLatLng,
                map: map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 6,
                    fillColor: '#FF0000',
                    fillOpacity: 1,
                    strokeWeight: 1,
                    strokeColor: '#ffffff',
                },
            });
            state.drawMarkers.push(marker);

            if (state.drawMarkers.length === 1) {
                google.maps.event.addListener(marker, 'click', function () {
                    if (state.drawVertices.length >= 3) {
                        finishActiveDrawing();
                    }
                });
            }

            if (state.drawPolygon == null) {
                state.drawPolygon = new google.maps.Polygon({
                    paths: state.drawVertices,
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#FF0000',
                    fillOpacity: 0.1,
                    editable: false,
                    map: map,
                });
            } else {
                state.drawPolygon.setPath(state.drawVertices);
            }
        });
    }

    function loadCoordinates(coordinates) {
        clearDrawingState();
        if (state.lastPolygon != null) {
            state.lastPolygon.setMap(null);
        }

        state.lastPolygon = new google.maps.Polygon({
            paths: coordinates,
            strokeColor: "#FF0000",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#FF0000",
            fillOpacity: 0.1,
        });
        state.lastPolygon.setMap(map);
        makePolygonEditable(state.lastPolygon);

        var polygonBounds = new google.maps.LatLngBounds();
        state.lastPolygon.getPaths().forEach(function (path) {
            path.forEach(function (latlng) {
                polygonBounds.extend(latlng);
                map.fitBounds(polygonBounds);
            });
        });

        var boundsCenter = polygonBounds.getCenter();
        if (boundsCenter) {
            map.setCenter(boundsCenter);

            const startLat = boundsCenter.lat();
            const startLng = boundsCenter.lng();
            const endLat = coordinates[0].lat;
            const endLng = coordinates[0].lng;

            const distance = distanceFrom(startLat, startLng, endLat, endLng) / 1000;
            var zoomLevel = Math.log2(40000 * Math.cos(startLat * Math.PI / 180) / distance);
            if (zoomLevel) {
                if (zoomLevel > 0) {
                    zoomLevel = zoomLevel - 1;
                }
                map.setZoom(zoomLevel);
            }
        }

        startFreehandDrawing();
    }

    function reset() {
        if (state.lastPolygon != null) {
            state.lastPolygon.setMap(null);
            state.lastPolygon = null;
        }
        startFreehandDrawing();
    }

    function centerOnCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position) => {
                map.setCenter({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                });
            });
        }
    }

    return {
        startFreehandDrawing,
        loadCoordinates,
        reset,
        centerOnCurrentLocation,
    };
}

var newZoneMapController = null;
var editZoneMapController = null;

function initMap() {
    newZoneMapController = createZoneMapController("map");
    newZoneMapController.centerOnCurrentLocation();
    newZoneMapController.startFreehandDrawing();
}

function initEditMap(coordinates) {
    if (editZoneMapController == null) {
        editZoneMapController = createZoneMapController("editMap");
    }
    editZoneMapController.loadCoordinates(coordinates);
}

//
livewire.on("initiateEditMap", (data) => {
    initEditMap(data);
});

//
livewire.on("resetMap", (data) => {
    if (newZoneMapController != null) {
        newZoneMapController.reset();
    }
    if (editZoneMapController != null) {
        editZoneMapController.reset();
    }
});

//
livewire.on("resetDrawing", () => {
    if (newZoneMapController != null) {
        newZoneMapController.reset();
    }
    if (editZoneMapController != null) {
        editZoneMapController.reset();
    }
});
