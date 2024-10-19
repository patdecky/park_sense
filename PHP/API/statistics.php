<?php

require_once __DIR__ . '/apiPrep.php';
require_once __DIR__ . '/../DB/DBH_statistics.php';

$DS = DBH_statistics::getInstance();


switch ($_GET['req']) {
    case "getStatsForDay":
        $day_w = intFilter('day_w');
        if ($day_w <= 0 || $day_w > 7) {
            quitExe(400, QE_INPUT_INVALID);
        }

        $stats = $DS->searchByDayW($day_w);
        responseOK($stats);

    default:
        quitExe(400, QE_NO_KNOWN_REQ);
}