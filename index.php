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
    <script src="static/js/Class/Cl_pl_prediction.js"></script>
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
            --primary-color: #4CAF50;
            --secondary-color: #388E3C;
            --background-light: #f4f4f4;
            --text-dark: #333;
            --button-hover: #66BB6A;
        }

        h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: bold;
        }

        h3 {
            color: var(--secondary-color);
            margin-bottom: 18px;
            font-weight: bold;
        }

        body{
            background-color: #fff;
            font-family: Arial, sans-serif;
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
            display: flex;
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border-radius: 20px;
            padding: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        #searchBar {
            border: none;
            padding: 10px;
            outline: none;
            font-size: 16px;
            border-radius: 20px 0 0 20px;
            flex-grow: 1;
        }

        #searchBar::placeholder {
            color: #555; /* Darker color for placeholder text */
            font-weight: bold;
        }
        #searchButton {
            border: none;
            background: var(--primary-color);
            color: white;
            padding: 10px;
            border-radius: 0 20px 20px 0;
            cursor: pointer;
            cursor: pointer;
            transition: background-color 0.3s;
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
            /* left: 0; */
            left: 0;
            top: 5%;
            /* transform: translateY(-50%); */
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            text-align: justify;
            z-index: 1000;
        }
        #aboutUsContent {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: justify;
            max-width: 500px;
            margin: auto;
        }
        #closeAboutUs {
            float: right;
            cursor: pointer;
        }

        #closeChart {
            float: right;
            cursor: pointer;
        }

        .container-custom {
            max-width: 800px;
            margin-top: 20px;
            animation: fadeIn 1s ease-in-out;
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
                <button type="button" class="wideButton colorZebraCl" id="occupancy">Aktuální vytíženost 🚗</button>
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
            <p>Jsme tým „náhodných“ lidí, které všechny spojuje absolvování brigády či pracovního poměru ve společnosti ATEsystem.
                 Přestože většina z nás v této společnosti již nevykonává žádné aktivity, tak jsme stále dobrý kolektiv a rádi se účastníme různých výzev. </p>
            <img src="static/team.jpg" alt="Týmová fotka" style="width:100%; border-radius: 10px">
            <h2>Náš tým zleva</h2>
            <p><strong>Patrik Děcký</strong> - SW Architekt</p>
            <p><strong>Alexandra Bodzás</strong> - Data Analyst</p>
            <p><strong>Lukáš Malík</strong> - Camera Enthusiast</p>
            <p><strong>Pavel Kodytek</strong> - Project Lead</p>
            <p><strong>Boris Pustějovský</strong> - SW Developer</p>
            <p><strong>Přemysl Bílek</strong> - Database Expert</p>
            <h2>O aplikaci</h2>
            <p>ParkSense nyní přináší inovativní řešení optimalizace parkování zaměřené speciálně na hlavní město Prahu. 
                Aplikace využívá kamerové záznamy k monitorování veřejných parkovišť v reálném čase, které jsou vyhodnocovány pomocí umělé inteligence. 
                Dále je aplikace obohacena i o integraci s offline daty parkovišť, které jsou dostupné v otevřených datových sadách. Díky této kombinaci poskytuje uživatelům ještě přesnější a aktuálnější informace o dostupnosti parkovacích míst.
                 Mobilní a webová aplikace nadále spolupracuje s Google Maps API, umožňuje vyhledávat nejbližší parkoviště, zobrazit počet volných míst a umožňuje snadnou navigaci přes aplikace jako Google Maps. 
                 ParkSense tak přispívá k hladšímu parkování a zlepšuje mobilitu v dynamickém prostředí Prahy.</p>
            <h2>Použité datové sady</h2>
            <ul>
                <li><a href="https://golemio.cz/data/doprava">Golemio - Doprava v datech</a></li>
                <li><a href="https://data.praha.eu/dashboardy/obsazenost-pr-parkovist">Obsazenost P+R parkovišť v Praze</a></li>
                <li><a href="https://www.windy.com/cs/-Webkamery-Praha-Horn%C3%AD-M%C4%9Bcholupy-Petrovice/webcams/1268410152?50.050,14.546,5">Windy.com - webkamery</a></li>
                <li><a href="https://bezpecnost.praha.eu/mapy/kamery">Veřejné kamery v Praze</a></li>
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