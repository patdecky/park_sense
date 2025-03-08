<?php

use DB\DBH_occupancy_community;

require_once __DIR__ . '/apiPrep.php';
require_once __DIR__ . '/../DB/DBH_parkinglot.php';
require_once __DIR__ . '/../DB/DBH_camera.php';
require_once __DIR__ . '/../DB/DBH_pl_history.php';
require_once __DIR__ . '/../DB/DBH_occupancy_community.php';

$DPL = DBH_parkinglot::getInstance();
$DPLH = DBH_pl_history::getInstance($DPL);
$DC = DBH_camera::getInstance($DPL);
$DOC = DBH_occupancy_community::getInstance($DPL);


switch ($_GET['req']) {
    case "getNearestParkingLots":
        $lat = floatFilter('lat');
        $long = floatFilter('long');
        $radius = intFilter('radius');
        $limit = intFilter('limit');

        if ($lat === false || $long === false) {
            quitExe(400, QE_INPUT_INVALID);
        }
        $parkingLots = $DPL->searchNearestFreeParking([$lat, $long], $radius, $limit);
        responseOK($parkingLots);
    case "getRecentHistoryForParkingLot":
        $parkingLotID = intFilter('parkingLotID');
        $hoursBack = intFilter('hoursBack');
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

    case "setCommunityOccupancy":
        $occupancy = intFilter('occupancy');
        $parkingLotID = intFilter('parkingLotID');
        if ($occupancy === false || $parkingLotID === false) {
            quitExe(400, QE_INPUT_INVALID);
        }
        $oc = new \PHPClass\CL_occupancy_community(0, $parkingLotID, $occupancy, new DateTime());
        $ret = $DOC->insert($oc);
        if ($ret === DBH_connection::ERROR) {
            quitExe(500, QE_DB_ERROR);
        }
        responseOK($ret);

    case "getCommunityOccupancy":
        $parkingLotID = intFilter('parkingLotID');
        if ($parkingLotID === false) {
            quitExe(400, QE_INPUT_INVALID);
        }
        $oc = $DOC->getRecentHistory($parkingLotID);

    default:
        quitExe(400, QE_NO_KNOWN_REQ);
}