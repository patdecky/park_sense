<?php

require_once __DIR__ . '/apiPrep.php';
require_once __DIR__ . '/../DB/DBH_parkinglot.php';
require_once __DIR__ . '/../DB/DBH_camera.php';
require_once __DIR__ . '/../DB/DBH_pl_history.php';

$DPL = DBH_parkinglot::getInstance();
$DPLH = DBH_pl_history::getInstance($DPL);
$DC = DBH_camera::getInstance($DPL);


switch ($_GET['req']) {
    case "getNearestParkingLots":
        $lat = floatFilter('lat');
        $long = floatFilter('long');
        $radius = intFilter('radius') || 500;
        $limit = intFilter('limit') || $DPL::MAX_PER_SELECT;

        if ($lat === false || $long === false) {
            quitExe(400, QE_INPUT_INVALID);
        }
        $parkingLots = $DPL->searchNearestFreeParking([$lat, $long], $radius, $limit);
        responseOK($parkingLots);
    case "getRecentHistoryForParkingLot":
        $parkingLotID = intFilter('parkingLotID');
        $hoursBack = intFilter('hoursBack') || 24;
        if ($parkingLotID === false) {
            quitExe(400, QE_INPUT_INVALID);
        }
        try {
            $history = $DPLH->getRecentHistory($parkingLotID);
            responseOK($history);
        } catch (Exception $e) {
            quitExe(500, QE_DB_ERROR);
        }


    case "getParkingIntervalHistory":
        $parkingLotID = intFilter('parkingLotID');
        $from = intFilter('from');
        $to = intFilter('to');
        if ($parkingLotID === false || $from === false || $to === false) {
            quitExe(400, QE_INPUT_INVALID);
        }
        fromToChecks($from, $to);
        try {
            $history = $DPLH->getHistoryInterval($parkingLotID, $from, $to);
            responseOK($history);
        } catch (Exception $e) {
            quitExe(500, QE_DB_ERROR);
        }

    default:
        quitExe(400, QE_NO_KNOWN_REQ);
}