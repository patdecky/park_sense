//global scope
let map;
let marker;
let park_place_markers = [];

const shapes = ['circle', 'square', 'star', 'penta'];
const colors = ['red', 'orange-dark', 'orange', 'yellow', 'blue-dark', 'cyan', 'purple', 'violet', 'pink', 'green-dark', 'green', 'white', 'black'];


window.addEventListener('load', () => {
    // Initialize the map
    initializeMap()

    if (window.matchMedia("(min-width: 800px)").matches) {
        document.getElementById('searchBar').focus();
    }

    document.getElementById('navigate').addEventListener('click', () => {
        openCoordinatesInGoogleMaps()
    });


    function onHashChange() {
        let cc = document.getElementById('chartContainer');
        let it = document.getElementById('in_time');
        if (location.hash === '#in_time') {
            cc.style.display = '';
            return;
        }
        cc.style.display = 'none';
    }

    document.getElementById('in_time').addEventListener('click', () => {
        // change URL to #in_time
        if (location.hash === '#in_time') {
            location.hash = '';
        } else {
            location.hash = 'in_time';
        }
        onHashChange();
    });


    document.getElementById('searchBar').addEventListener('keydown', function (event) {
        if (event.key === "Enter") {
            const inputValue = event.target.value; // Get the value of the inputf
            geocodePlace(inputValue)
        }
    });

    document.getElementById('searchButton').addEventListener('click', () => {
        const inputValue = document.getElementById('searchBar').value
        geocodePlace(inputValue)
    });

    onHashChange();
    if (location.hash === '#in_time') {

    }

    function initializeMap() {
        map = L.map('map')

        // Base layer: OpenStreetMap
        var openStreetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });

        // Satellite layer: Esri
        var satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
        });

        // Terrain layer: Esri
        var terrainLayer = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data: &copy; OpenStreetMap contributors, SRTM | Map style: &copy; OpenTopoMap (CC-BY-SA)'
        });

        // Add OpenStreetMap as the default layer
        openStreetMap.addTo(map);

        // Layer control for switching between base layers
        var baseMaps = {
            "OpenStreetMap": openStreetMap,
            "Satellite": satelliteLayer,
            "Terrain": terrainLayer
        };



        // Layer control for switching between base layers
        var baseMaps = {
            "OpenStreetMap": openStreetMap,
            "Satellite": satelliteLayer,
            "Terrain": terrainLayer
        };

        // Add layer control to the map
        L.control.layers(baseMaps).addTo(map);

        setMapCenter(49.5940567,17.251143, 13)
        // setMarkerToMap(51.505, -0.09, null)
        /*betterMarker(L.ExtraMarkers.icon({
            icon: 'fa-number',
            markerColor: colors[0],
            shape: 'square',
            number: '11',
            prefix: 'fa'
        }), [49.5876267,17.2553681], 'skibbidy toilet')*/
    }

    function setMarkerToMap(latitude, longitude, description) {
        if (marker) {
            map.removeLayer(marker); // Remove the existing marker
        }
        // Add the new marker at the new location

        marker = L.marker([latitude, longitude], {icon: L.ExtraMarkers.icon({
                        icon: 'fa-number',
                        markerColor: "red",
                        prefix: 'fa'})}).addTo(map)
        if (description != null) {
            marker.bindPopup(description).openPopup();
            if (onPopupOpenCallbackMain && typeof onPopupOpenCallbackMain === 'function') {
                marker.on('popupopen', function (event) {
                    onPopupOpenCallbackMain(event.target); // Pass the marker (event.target is the marker)
                });
            }
        }
    }

    function removeParkPlaceMarkersFromMap() {
        park_place_markers.forEach(element => {
            map.removeLayer(element);
        });
        park_place_markers = []
    }


    function setParkPlaceMarkerToMap(latitude, longitude, description,vacancy, capacity, main = false) {
        park_place_markers.forEach(element => {
            element.closePopup();
        });
        ppmarker = betterMarkerUse(latitude, longitude, description, vacancy, capacity, main)
        park_place_markers.push(ppmarker);
        ppmarker.openPopup();
        // Check if a callback is provided for when the popup is opened
        if (onPopupOpenCallback && typeof onPopupOpenCallback === 'function') {
            ppmarker.on('popupopen', function (event) {
                onPopupOpenCallback(event.target); // Pass the marker (event.target is the marker)
            });
        }
    }

    function onPopupOpenCallbackMain() {
        park_place_markers.forEach(element => {
            element.closePopup()
        });
    }

    function onPopupOpenCallback(map_marker) {
        marker.closePopup()
        for (let i = 0; i < park_place_markers.length; i++) {
            if (park_place_markers[i] !== map_marker) {
                park_place_markers[i].closePopup()
            }
        }
        //close all the popups and open this one

    }

    async function searchAndPlaceMarkers(latitude, longitude, description) {
        removeParkPlaceMarkersFromMap()
        setMarkerToMap(latitude, longitude, description)
        let nearestParkingLots = await findNearestParkingLots(latitude, longitude)
        let is_first = true
        if (nearestParkingLots){
        nearestParkingLots.forEach(async (element) => {
            vacancy_element = await findVacancy(element.id)
            setParkPlaceMarkerToMap(element.geopos_x, element.geopos_y, element.name, vacancy_element.vacancy, element.car_capacity, is_first)
            if (is_first) {
                is_first = false
            }
        })}
        // setParkPlaceMarkerToMap(latitude - 0.001, longitude, "1", true);
        // setParkPlaceMarkerToMap(latitude, longitude - 0.001, "2", true);
        // setParkPlaceMarkerToMap(latitude + 0.001, longitude, "3", true);
        // setParkPlaceMarkerToMap(latitude, longitude + 0.001, "4", true);
    }

    function geocodePlace(place) {
        const nominatimUrl = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(place)}&format=json&limit=1`;

        fetch(nominatimUrl)
            .then(response => response.json())
            .then(async (data) => {
                if (data.length > 0) {
                    const location = data[0];
                    const lat = parseFloat(location.lat);
                    const lon = parseFloat(location.lon);
                    const displayName = location.display_name;
                    const shortName = displayName.split(',')[0];

                    const inputValue = document.getElementById('searchBar').value = shortName

                    setMapCenter(lat, lon, 17)
                    await searchAndPlaceMarkers(lat, lon, displayName)
                } else {
                    alert('Place not found!');
                    place_selected = false
                }
            })
            .catch(error => console.error('Error fetching geocode:', error));
    }

    async function findNearestParkingLots(lat, lon) {

        return result = await (new dataRequester()).loadParkingLots(lat, lon, 1500, 5)
    }

    async function findVacancy(parkinglot_id) {

        return result = await (new dataRequester()).loadParkingLotsVacancy(parkinglot_id)
    }

    function openCoordinatesInGoogleMaps() {
        open_marker = getActiveMarker()
        if (open_marker) {
            lat_lon = getMarkerCoordinance(open_marker)
            if (lat_lon[0] && lat_lon[1]) {
                const url = `https://www.google.com/maps?q=${lat_lon[0]},${lat_lon[1]}`;
                window.open(url, '_blank');
            } else {
                alert("Missing latitude or longitude!")
            }
        } else {
            alert("Please search and select a place to navigate.")
        }

    }

    function getActiveMarker() {
        if (marker.getPopup().isOpen()) {
            return marker
        }
        for (let i = 0; i < park_place_markers.length; i++) {
            if (park_place_markers[i].getPopup().isOpen()) {
                return park_place_markers[i]
            }
        }
    }

    function getMarkerCoordinance(marker) {
        const latLng = marker.getLatLng();
        return [latLng.lat, latLng.lng]
    }

    function setMapCenter(latitude, longitude, zoom) {
        map.setView([latitude, longitude], zoom);
    }

//////////////////////
    // Setup map

function betterMarker(options, pos, name) {
    const marker = L.marker(pos, {icon: options}).addTo(map);
    marker.bindPopup(name);
    return marker
}
    

function betterMarkerUse(latitude, longitude,description, vacancy, capacity, main = false){
    my_marker = betterMarker(L.ExtraMarkers.icon({
        icon: 'fa-number',
        markerColor: "blue",
        shape: 'square',
        number: vacancy,
        prefix: 'fa'
    }), [latitude, longitude], vacancy + "/" + capacity + "<br>" + description)

    if (main){
        my_marker.openPopup()
    }
    return my_marker
}

});

