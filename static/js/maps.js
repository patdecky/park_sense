
//global scope
var map;
var marker;
var park_place_markers = [];
var place_selected = false;
var selected_latitude;
var selected_longitude;


window.addEventListener('load', () => {
    // Initialize the map
    initializeMap()

    document.getElementById('navigate').addEventListener('click', () => {
        openCoordinatesInGoogleMaps()
    });

    document.getElementById('in_time').addEventListener('click', () => {
        // change URL to #in_time
        location.hash = 'in_time';
    });
    
    document.getElementById('searchButton').addEventListener('click', () => {
        const inputValue = document.getElementById('searchBar').value
        geocodePlace(inputValue)
    });

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
        setParkPlaceMarkerToMap(51.505 - 0.001, -0.09, "1", true);
        setParkPlaceMarkerToMap(51.505 , -0.09- 0.001, "2", true);
        setParkPlaceMarkerToMap(51.505 + 0.001, -0.09, "3", true);
        setParkPlaceMarkerToMap(51.505 , -0.09+ 0.001, "4", true);
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
        }
    }
   
    function searchAndPlaceMarkers(latitude, longitude, description){
        removeParkPlaceMarkersFromMap()
        setMarkerToMap(latitude, longitude, description)
        setParkPlaceMarkerToMap(latitude - 0.001, longitude, "1", true);
        setParkPlaceMarkerToMap(latitude, longitude - 0.001, "2", true);
        setParkPlaceMarkerToMap(latitude + 0.001, longitude, "3", true);
        setParkPlaceMarkerToMap(latitude, longitude + 0.001, "4", true);
    }

    function geocodePlace(place) {
        const nominatimUrl = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(place)}&format=json&limit=1`;

        fetch(nominatimUrl)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const location = data[0];
                    const lat = parseFloat(location.lat);
                    const lon = parseFloat(location.lon);

                    setMapCenter(lat, lon, 18)
                    searchAndPlaceMarkers(lat, lon, "Dest")
                    place_selected = true
                    selected_latitude = lat
                    selected_longitude = lon
                } else {
                    alert('Place not found!');
                    place_selected = false
                }
            })
            .catch(error => console.error('Error fetching geocode:', error));
    }

    function openCoordinatesInGoogleMaps() {
        if (place_selected)
        {
            if (selected_latitude && selected_latitude) {
                const url = `https://www.google.com/maps?q=${selected_latitude},${selected_longitude}`;
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

    function setMapCenter(latitude, longitude, zoom){
        map.setView([latitude, longitude], zoom);
    }

});

