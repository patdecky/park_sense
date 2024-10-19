
//global scope
let map;
let marker;
let park_place_markers = [];


window.addEventListener('load', () => {
    // Initialize the map
    initializeMap()

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
    
    document.getElementById('searchButton').addEventListener('click', () => {
        const inputValue = document.getElementById('searchBar').value
        geocodePlace(inputValue)
    });

    onHashChange();
    if (location.hash === '#in_time') {

    }

    function initializeMap(){
        map = L.map('map')

        // Add a tile layer to the map
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        setMapCenter(51.505, -0.09, 13)
        setMarkerToMap(51.505, -0.09, null)
    }

    function setMarkerToMap(latitude, longitude, description){
        if (marker) {
            map.removeLayer(marker); // Remove the existing marker
        }
        // Add the new marker at the new location
        marker = L.marker([latitude, longitude]).addTo(map);
        if (description != null)
        {
            marker.bindPopup(description).openPopup();
        }
    }

    function removeParkPlaceMarkersFromMap(){
        park_place_markers.forEach(element => {
            map.removeLayer(element);
        });
        park_place_markers = []
    }


    function setParkPlaceMarkerToMap(latitude, longitude, description, main = false){
        park_place_markers.forEach(element => {
            element.closePopup();
        });
        ppmarker = L.marker([latitude, longitude]).addTo(map);
        park_place_markers.push(ppmarker);
        ppmarker.bindPopup(description);
        if (main){
            ppmarker.openPopup();
            // Check if a callback is provided for when the popup is opened
            if (onPopupOpenCallback && typeof onPopupOpenCallback === 'function') {
                ppmarker.on('popupopen', function (event) {
                    onPopupOpenCallback(event.target); // Pass the marker (event.target is the marker)
                });
            }
        }
    }

    function onPopupOpenCallbackMain(){
        park_place_markers.forEach(element => {
            map.removeLayer(element);
            element.closePopup()
        });
    }

    function onPopupOpenCallback(map_marker){
        marker.closePopup()
        for (let i = 0; i < park_place_markers.length; i++){
            if (park_place_markers[i] !== map_marker){
                park_place_markers[i].closePopup()
            }
        }
        //close all the popups and open this one

    }
   
    async function searchAndPlaceMarkers(latitude, longitude, description){
        removeParkPlaceMarkersFromMap()
        setMarkerToMap(latitude, longitude, description)
        let nearestParkingLots = await findNearestParkingLots(latitude, longitude)
        let is_first = true
        nearestParkingLots.forEach(element => {
            setParkPlaceMarkerToMap(element.geopos_x, element.geopos_y, element.name, is_first)
            if (is_first){
                is_first = false
            }
        })
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

                    setMapCenter(lat, lon, 18)
                    await searchAndPlaceMarkers(lat, lon, "Dest")
                } else {
                    alert('Place not found!');
                    place_selected = false
                }
            })
            .catch(error => console.error('Error fetching geocode:', error));
    }

    async function findNearestParkingLots(lat, lon){

        return result = await (new dataRequester()).loadParkingLots(lat, lon, 500, 5)
    }

    function openCoordinatesInGoogleMaps() {
        open_marker = getActiveMarker()
        if (open_marker)
        {
            lat_lon = getMarkerCoordinance(open_marker)
            if (lat_lon[0] && lat_lon[1]) {
                const url = `https://www.google.com/maps?q=${lat_lon[0]},${lat_lon[1]}`;
                window.open(url, '_blank');
            }
            else{
                alert("Missing latitude or longitude!")
            }
        }
        else{
            alert("Please search and select a place to navigate.")
        }
       
    }

    function getActiveMarker(){
        if (marker.getPopup().isOpen()){
            return marker
        }
        for (let i = 0; i < park_place_markers.length; i++) {
            if (park_place_markers[i].getPopup().isOpen()){
                return park_place_markers[i]
            }
        }
    }

    function getMarkerCoordinance(marker){
        const latLng = marker.getLatLng();
        return [latLng.lat, latLng.lng]
    }

    function setMapCenter(latitude, longitude, zoom){
        map.setView([latitude, longitude], zoom);
    }


});

