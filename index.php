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
    <script src="static/js/Class/Cl_parkinglotwithinfo.js"></script>
    <script src="static/js/Class/Cl_pl_history.js"></script>
    <script src="static/js/Class/CL_pl_prediction.js"></script>
    <script src="static/js/Class/CL_occupancy_community.js"></script>
    <script src="static/js/Class/DateMK2.js"></script>

    <script src="static/js/globals.js"></script>
    <script src="static/js/dataLoader/dataHolderBase.js"></script>
    <script src="static/js/dataLoader/dataRequester.js"></script>
    <script src="static/js/dataLoader/fetchAPI.js"></script>
    <script src="static/js/dataHolderStatistics.js"></script>
    <script src="static/js/statsChart.js"></script>

    <link rel="icon" href="static/favicon.ico" type="image/x-icon">

    <!-- Extra Markers -->
    <link rel="stylesheet" href="static/libs/ll_extra/css/leaflet.extra-markers.min.css"/>
    <script src="static/libs/ll_extra/js/leaflet.extra-markers.min.js"></script>

    <script src="static/js/maps.js"></script>

    <title>Park Sense</title>
    <style>

        h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: bold;
        }

        body {
            background-color: #fff;
            font-family: Arial, sans-serif;
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

        /*
                #aboutUsModal {
                    display: none;
                    position: fixed;
                    /* left: 0;
                    left: 0;
                    top: 5%;
                    /* transform: translateY(-50%);
                    width: 100%;
                    height: 100%;
                    /* background-color: rgba(0, 0, 0, 0.5);
                    align-items: center;
                    justify-content: center;
                    text-align: justify;
                    z-index: 1000;
                } */
        /* #aboutUsContent {
            height: 100%;
            width: 100%;
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: justify;
            max-width: 500px;
            /* background-color: rgba(255, , 0, 0.5); 
            
            margin: auto;
        } */
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

        :root {
            --primary-color: #1A73E8;
            --secondary-color: #1967D2;
            --background-light: #F1F3F4;
            --text-dark: #202124;
            --button-hover: #4285F4;
        }

        body {
            background-color: #fff;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-image: radial-gradient(circle, #eee 2px, transparent 2px);
            background-size: 40px 40px;
            background-position: 0 0;
        }

        #mainContainer {
            /* display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100vh;
            padding: 10px; */

            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 100%;
        }

        #mapDivContainer {
            /* width: 90%;
            max-width: 1000px;
            height: 80vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            border: 1px solid #ccc;
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); */

            width: 600px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            /* border: 2px solid #ccc; */
            border-radius: 10px;
        }

        #searchContainer {
            display: flex;
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border-radius: 24px;
            padding: 5px;
            width: 75%;
            /* box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); */
            z-index: 1000;
        }

        #searchBar {
            border: none;
            padding: 10px;
            outline: none;
            font-size: 16px;
            border-radius: 24px 0 0 24px;
            flex-grow: 1;
        }

        #searchButton {
            border: none;
            background: var(--primary-color);
            color: white;
            padding: 10px;
            border-radius: 0 24px 24px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #searchButton:hover {
            background: var(--button-hover);
        }

        #map {
            height: 100%;
            width: 100%;
        }

        #buttonContainer {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
            padding: 10px;
            gap: 10px;
        }

        .wideButton {
            flex: 1;
            min-width: 150px;
            padding: 15px;
            background: var(--primary-color);
            color: var(--primary-color);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s;
            font-weight: bold;
            font-size: 16px;
        }


        #chartCanvas {
            border-radius: 10px; /* Round the corners */
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


        .wideButton:hover {
            background: var(--button-hover);
            color: white;
        }

        #aboutUsModal {
            display: none;
            position: absolute;
            /* left: 0; */
            /* left: 0; */
            top: 6%;
            /* transform: translateY(-50%); */
            /* width: calc(100% - 20px); */
            height: 75%;
            background: white;
            align-items: center;
            justify-content: center;
            text-align: justify;
            z-index: 1000;
            overflow-y: auto;
            border-bottom: white solid 15px;
            border-radius: 10px;
            border-top: white solid 5px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }

        #aboutUsContent {
            height: 100%;
            /* width: 100%; */
            /* background: white; */
            padding: 20px;
            border-radius: 10px;
            text-align: justify;
            max-width: 500px;
            margin: auto;
        }


        .buttonContainer {
            display: flex;
            justify-content: space-between;
            padding: 10px 20px;
            background-color: #ffffff;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.4);
            border-radius: 10px;
            margin-bottom: 20px;
            flex-direction: column;
            width: 100%;
            box-sizing: border-box;
        }


        #loginButton {
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 10px;
        }
        #navigate{
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        #loginModal, #userMenu, #voteModal {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            color: var(--primary-color);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .hidden {
            display: none;
        }

        /* .userIcon {
            font-size: 24px;
            cursor: pointer;
        } */

        .userIcon {
            /* font-size: 40px; */
            flex: 1;
            text-align: center;
            vertical-align: middle;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            /* display: inline-block; */
        }

        .modal {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        .modal h2 {
            color: var(--primary-color);
        }

        .modal input {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 10px;
        }

        .modal button {
            background: var(--primary-color);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin: 5px;
        }

        .modal button:hover, .userIcon:hover {
            background: var(--primary-color);
            color: #ffffff;
        }

        .modal-close {
            float: right;
            cursor: pointer;
            font-size: 20px;
            color: var(--primary-color);
        }

        .wideButton {
            flex: 1;
            min-width: 30%; /* Zajištění stejné šířky tlačítek */
            padding: 2%;
            background: var(--primary-color);
            color: var(--primary-color);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s;
            font-weight: bold;
            font-size: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 4vh; /* Zajištění stejné výšky tlačítek */
            width: 100%; /* Plná šířka uvnitř kontejneru */
        }

        .mapButtons {
            display: flex;
            gap: 2%;
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
            padding-bottom: 2%;
        }

        @media (max-width: 600px) {
            .mapButtons > button {
                height: 6vh;
            }

            #searchContainer {
                width: 68%;
                left: 48%;
            }

            #searchBar{
                padding: 0;
            }

            .buttonContainer {
                margin-bottom: 2px;
            }

            #mapDivContainer {
                width: 100%;
                /*height: 80vh;*/
            }
        }
    </style>
</head>
<body>
<div id="mainContainer">
    <div id="mapDivContainer">
        <div id="searchContainer">
            <input type="text" id="searchBar" placeholder="Search...">
            <button type="button" id="searchButton">Hledat</button>
        </div>
        <div id="map"></div>
        <div id="chartContainer" style="display: none;">
            <canvas id="chartCanvas"></canvas>
        </div>
        <div class="buttonContainer">
            <div id="subMenu" class="mapButtons">
                <!-- <button type="button" class="wideButton colorZebraCl" id="navigate">Navigovat 🌍</button>
                <button type="button" class="wideButton colorZebraCl" id="occupancy">Obsazení 🚗</button>
                <button type="button" class="wideButton colorZebraCl" id="in_time">Vytížení v čase 📊</button> -->

                
                <button type="button" class="wideButton colorZebraCl" id="occupancy">Zadat stav</button>
                <button type="button" class="wideButton colorZebraCl" id="in_time">Vytížení v čase</button>
                <button type="button" class="wideButton colorZebraCl" id="navigate">Navigovat</button>
            </div>
            <div class="mapButtons">
                <!-- <button type="button" class="wideButton colorZebraCl" id="aboutUsBtn">O nás 📜</button> -->
                <button type="button" class="wideButton colorZebraCl" id="aboutUsBtn">O nás</button>
                <button id="loginButton" class="wideButton colorZebraCl">Přihlásit se</button>
                <span id="userIcon" class="userIcon hidden">👤 admin</span>
            </div>
        </div>
    </div>
    <div id="aboutUsModal">
        <div id="aboutUsContent">
            <span id="closeAboutUs">❌</span>
            <h2>O nás</h2>
            <p>Jsme tým „náhodných“ lidí, které všechny spojuje absolvování brigády či pracovního poměru ve společnosti
                ATEsystem.
                Přestože většina z nás v této společnosti již nevykonává žádné aktivity, tak jsme stále dobrý kolektiv a
                rádi se účastníme různých výzev. </p>
            <img src="static/team.jpg" alt="Týmová fotka" style="width:100%; border-radius: 10px">
            <h2>Náš tým zleva</h2>
            <p><strong>Patrik Děcký</strong> - SW Architekt</p>
            <p><strong>Alexandra Bodzás</strong> - Data Analyst</p>
            <p><strong>Lukáš Malík</strong> - Camera Enthusiast</p>
            <p><strong>Pavel Kodytek</strong> - Project Lead</p>
            <p><strong>Boris Pustějovský</strong> - SW Developer</p>
            <p><strong>Přemysl Bílek</strong> - Database Expert & SW Developer</p>
            <h2>O aplikaci</h2>
            <p>ParkSense nyní přináší inovativní řešení optimalizace parkování zaměřené speciálně na hlavní město Prahu.
                Aplikace využívá kamerové záznamy k monitorování veřejných parkovišť v reálném čase, které jsou
                vyhodnocovány pomocí umělé inteligence.
                Dále je aplikace obohacena i o integraci s offline daty parkovišť, které jsou dostupné v otevřených
                datových sadách. Díky této kombinaci poskytuje uživatelům ještě přesnější a aktuálnější informace o
                dostupnosti parkovacích míst.
                Mobilní a webová aplikace nadále spolupracuje s Google Maps API, umožňuje vyhledávat nejbližší
                parkoviště, zobrazit počet volných míst a umožňuje snadnou navigaci přes aplikace jako Google Maps.
                ParkSense tak přispívá k hladšímu parkování a zlepšuje mobilitu v dynamickém prostředí Prahy. <p> <b>Do budoucna může být aplikace integrována například s kontrolními vozy TSV Praha a.s.</b></p>
            <h2>Použité datové sady</h2>
            <ul>
                <li><a href="https://golemio.cz/data/doprava">Golemio - Doprava v datech</a></li>
                <li><a href="https://data.praha.eu/dashboardy/obsazenost-pr-parkovist">Obsazenost P+R parkovišť v
                        Praze</a></li>
                <li>
                    <a href="https://www.windy.com/cs/-Webkamery-Praha-Horn%C3%AD-M%C4%9Bcholupy-Petrovice/webcams/1268410152?50.050,14.546,5">Windy.com
                        - webkamery</a></li>
                <li><a href="https://bezpecnost.praha.eu/mapy/kamery">Veřejné kamery v Praze</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- <div id="loginModal">
    <h2>Přihlášení</h2>
    <label>Uživatel:</label>
    <input type="text" id="username"><br>
    <label>Heslo:</label>
    <input type="password" id="password"><br>
    <button onclick="login()">Přihlásit</button>
    <button onclick="closeLogin()">Zrušit</button>
</div> -->

<div id="userMenu" , class="modal">
    <h2>Menu uživatele</h2>
    <p><strong>Jméno: </strong>admin</p>
    <button onclick="openVote()">Je tady volno?</button>
    <button onclick="logout()">Odhlásit se</button>
</div>

<div id="loginModal" class="modal">
    <span class="modal-close" onclick="closeLogin()">&times;</span>
    <h2>Přihlášení</h2>
    <input type="text" id="username" placeholder="Uživatel">
    <input type="password" id="password" placeholder="Heslo">
    <button onclick="login()">Přihlásit</button>
    <button onclick="closeLogin()">Zrušit</button>
</div>

<div id="voteModal" class="modal">
    <span class="modal-close" onclick="closeVote()">&times;</span>
    <h2>Je zde volné místo?</h2>
    <button onclick="vote('yes')">👍 Ano</button>
    <button onclick="vote('no')">👎 Ne</button>
    <p id="voteMessage"></p>
</div>

<script>
    document.getElementById("aboutUsBtn").onclick = function () {
        document.getElementById("aboutUsModal").style.display = "unset";
    };
    document.getElementById("closeAboutUs").onclick = function () {
        document.getElementById("aboutUsModal").style.display = "none";
    };


    document.getElementById("loginButton").onclick = function () {
        document.getElementById("loginModal").style.display = "unset";
    };
    document.getElementById("userIcon").onclick = function () {
        document.getElementById("userMenu").style.display = "unset";
    };

    function closeLogin() {
        document.getElementById("loginModal").style.display = "none";
    }    function closeVote() {
        document.getElementById("voteModal").style.display = "none";
    }

    function login() {
        var user = document.getElementById("username").value;
        var pass = document.getElementById("password").value;
        if (user === "admin" && pass === "admin") {
            document.getElementById("loginButton").style.display = "none";
            document.getElementById("userIcon").classList.remove("hidden");
            closeLogin();
        } else {
            alert("Nesprávné přihlašovací údaje");
        }
    }

    function logout() {
        document.getElementById("loginButton").style.display = "inline-block";
        document.getElementById("userIcon").classList.add("hidden");
        document.getElementById("userMenu").style.display = "none";
    }

    function openVote() {
        document.getElementById("voteModal").style.display = "unset";
    }

    function vote(option) {
        document.getElementById("voteMessage").innerText = "Děkujeme za vaši informaci!";
        setTimeout(() => {
            document.getElementById("voteModal").style.display = "none";
            document.getElementById("userMenu").style.display = "None";
            document.getElementById("voteMessage").innerText = "";
        }, 1000);
    }
</script>
</body>
</html>