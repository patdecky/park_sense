window.addEventListener('load', () => {
    // Initialize the map
    var map = L.map('map').setView([51.505, -0.09], 13);

    // Add a tile layer to the map
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Add a numbered marker to the map
    var marker = L.marker([51.505, -0.09]).addTo(map);
    marker.bindPopup("<b>5/10</b><br>šantofka").openPopup();


    document.getElementById('navigate').addEventListener('click', () => {
        // todo
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

    onHashChange();
});

