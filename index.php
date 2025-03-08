<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="static/libs/ll/leaflet.css"/>
    <script src="static/libs/ll/leaflet.js"></script>
    <script src="static/libs/chartjs/chart_v3.9.1.js"></script>
    <script src="static/libs/chartjs/chartBackground.js"></script>

    <script src="static/js/Class/Cl_statistics.js"></script>
    <script src="static/js/Class/Cl_camera.js"></script>
    <script src="static/js/Class/Cl_parkinglot.js"></script>
    <script src="static/js/Class/Cl_pl_history.js"></script>
    <script src="static/js/Class/DateMK2.js"></script>

    <script src="static/js/globals.js"></script>
    <script src="static/js/dataLoader/dataHolderBase.js"></script>
    <script src="static/js/dataLoader/dataRequester.js"></script>
    <script src="static/js/dataLoader/fetchAPI.js"></script>
    <script src="static/js/dataHolderStatistics.js"></script>
    <script src="static/js/statsChart.js"></script>

    <link rel="icon" href="static/favicon.ico" type="image/x-icon">

    <!-- Extra Markers -->
    <link rel="stylesheet" href="static/libs/ll_extra/css/leaflet.extra-markers.min.css" />
    <script src="static/libs/ll_extra/js/leaflet.extra-markers.min.js"></script>

    <script src="static/js/maps.js"></script>

    <title>Park Sense</title>
    <style>
        :root {
            --color1: #72BF78;
            --color2: #A0D683;
            --color3: #D3EE98;
            --color4: #FEFF9F;

            --color5: #8C3061;
            --color6: #C63C51;

            --color7: #7E60BF;
            --color8: #E4B1F0;

            --search_background: #FFFFFF;
            --serach_frame: #aaaaaa;
            --map_frame: #bbbbbb;
            --button_text_color: #333;
        }


        body{
            background-color: #fff;
        }
        #mainContainer {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 100%;
        }

        #mapDivContainer {
            width: 600px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            border: 2px solid #ccc;
            border-radius: 12px;
        }

        #searchContainer {
            background-color: var(--search_background);
            width: calc(100% - 140px);
            display: flex;
            position: absolute;
            top: 10px;
            left: 60px;
            z-index: 1000;
            border: 1px solid #ccc;
            border-radius: 10px; /* Increased border-radius */
        }

        #searchBar {
            width: calc(100% - 50px);
            background: var(--search_background);
            padding: 10px;
            box-sizing: border-box;
            border: none;
            border-radius: 10px 0 0 10px; /* Increased border-radius */
            font-size: 18px;
            text-align: center;
        }

        #searchBar::placeholder {
            color: #555; /* Darker color for placeholder text */
            font-weight: bold;
        }

        #searchButton {
            padding: 10px;
            background: var(--search_background);
            border: none;
            border: 1px solid #ccc;
            border-radius: 0 10px 10px 0; /* Increased border-radius */
            cursor: pointer;
            font-size: 20px; /* icon size */
        }

        #map {
            height: 100%;
            width: 100%;
            border-radius: 12px;
        }

        #buttonContainer {
            display: flex;
            flex-direction: row;
            justify-content: space-around;
            width: 100%;
            padding: 10px;
        }

        #chartCanvas {
            border-radius: 20px; /* Round the corners */
        }

        #chartContainer {
            display: flex;
            flex-direction: column;
            width: calc(100% - 20px);
            /*height: 100%;*/
            position: absolute;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .wideButton {
            flex: 1;
            padding: 10px;
            margin: 5px;
            background: white;
            border: 1px solid #ccc;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
        }

        .colorZebraCl{
            font-size: 18px; /* icon size */
            font-weight: 550;
            color:#000;
        }

        .colorZebraCl:nth-child(even) {
            /* time chart */
            background-color: #ffffff;
            border: 1px solid #ccc;
        }

        .colorZebraCl:nth-child(odd) {
            /* navigate */
             background-color: #ffffff;
            border: 1px solid #ccc;
        }

        @media (max-width: 800px) {
            #mapDivContainer {
                width: 100%;
            }
        }

        #aboutUsModal {
            display: none;
            position: fixed;
            font-family: 'Poppins', sans-serif;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        #aboutUsContent {
            background-color: white;
            font-family: 'Poppins', sans-serif;
            margin: 10% auto;
            padding: 20px;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
            height: 60%;
        }

        #closeAboutUs {
            float: right;
            cursor: pointer;
        }

        #closeChart {
            float: right;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div id="mainContainer">
        <div id="mapDivContainer">
            <div id="searchContainer">
                <input type="text" id="searchBar" placeholder="Search...">
                <button type="button" id="searchButton">🔍</button>
            </div>
            <div id="map"></div>
                <div id="chartContainer" style="display: none;">
                    <canvas id="chartCanvas"></canvas>
                </div>
            <div id="buttonContainer">
                <button type="button" class="wideButton colorZebraCl" id="navigate">Navigovat 🌍</button>
                <button type="button" class="wideButton colorZebraCl" id="occupancy">Vytíženost 🚗</button>
                <button type="button" class="wideButton colorZebraCl" id="in_time">Vytížení v čase 📊</button>
                <button type="button" class="wideButton colorZebraCl" id="aboutUsBtn">O nás 📜</button>
            </div>
        </div>
    </div>

    <!-- Modální okno O nás -->
    <div id="aboutUsModal">
        <div id="aboutUsContent">
            <span id="closeAboutUs">❌</span>
            <h2>O nás</h2>
            <img src="static/team.jpg" alt="Týmová fotka" style="width:100%; border-radius: 10px">
            <h3>Náš tým</h3>
            <p><strong>Jan Novák</strong> - Vedoucí vývoje</p>
            <p><strong>Petra Malá</strong> - Designérka UX/UI</p>
            <p><strong>Martin Dvořák</strong> - Backend vývojář</p>
            <p><strong>Eva Kovářová</strong> - Datový analytik</p>
            <h3>O aplikaci</h3>
            <p>Park Sense je moderní nástroj pro optimalizaci parkování ve městech.</p>
            <h3>Použité datové sady</h3>
            <ul>
                <li><a href="#">Dataset 1</a></li>
                <li><a href="#">Dataset 2</a></li>
            </ul>
        </div>
    </div>

    <script>
        document.getElementById("aboutUsBtn").onclick = function() {
            document.getElementById("aboutUsModal").style.display = "block";
        };
        document.getElementById("closeAboutUs").onclick = function() {
            document.getElementById("aboutUsModal").style.display = "none";
        };
    </script>
    </body>
</html>