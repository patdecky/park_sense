<?php

use DB\DBH_occupancy_community;

require_once __DIR__ . '/apiPrep.php';
require_once __DIR__ . '/../DB/DBH_parkinglot.php';
require_once __DIR__ . '/../DB/DBH_camera.php';
require_once __DIR__ . '/../DB/DBH_pl_history.php';
require_once __DIR__ . '/../DB/DBH_pl_prediction.php';
require_once __DIR__ . '/../DB/DBH_occupancy_community.php';

$DPL = DBH_parkinglot::getInstance();
$DPLH = DBH_pl_history::getInstance($DPL);
$DPLPV = DBH_pl_prediction::getInstance($DPL);
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


    case "getNearestParkingLotsWithInfo":
        $lat = floatFilter('lat');
        $long = floatFilter('long');
        $radius = intFilter('radius');
        $limit = intFilter('limit');
        $day=intFilter('day');
        $day_timestamp = intFilter('day_timestamp');

        if ($lat === false || $long === false) {
            quitExe(400, QE_INPUT_INVALID);
        }

        $parkingLots = $DPL->searchNearestFreeParkingWithInfo([$lat, $long], $radius, $limit, $day, $day_timestamp);

        # for each parking lot find available vacancies, predicted vacancies and 

        responseOK($parkingLots);

    case "getRecentHistoryForParkingLot":
        $parkingLotID = intFilter('parkingLotID');
        $hoursBack = intFilter('hoursBack');
        if ($parkingLotID === false) {
            quitExe(400, QE_INPUT_INVALID);
        }
        if ($hoursBack === false) {
            quitExe(400, QE_INPUT_INVALID);
        }
        try {
            $history = $DPLH->getRecentHistory($parkingLotID);
            responseOK($history);
        } catch (Exception $e) {
            quitExe(500, QE_DB_ERROR);
        }

    case "getPredictedVacancyForParkingLot":
        $parkingLotID = intFilter('parkingLotID');
        $day = intFilter('day');
        $day_timestamp = intFilter('day_timestamp');

        if ($parkingLotID === false) {
            quitExe(400, QE_INPUT_INVALID);
        }
        if ($day === false) {
            quitExe(400, QE_INPUT_INVALID);
        }
        if ($day_timestamp === false) {
            quitExe(400, QE_INPUT_INVALID);
        }
        try {
            $history = $DPLPV->selectByDayAndSecondsInterval($parkingLotID, $day, $day_timestamp);
            responseOK($history);
        } catch (Exception $e) {
            quitExe(500, QE_DB_ERROR);
        }

    case "getPredictedVacancyForParkingLotWholeDay":
        $parkingLotID = intFilter('parkingLotID');
        $day = intFilter('day');

        if ($parkingLotID === false) {
            quitExe(400, QE_INPUT_INVALID);
        }
        if ($day === false) {
            quitExe(400, QE_INPUT_INVALID);
        }
        try {
            $history = $DPLPV->selectByDayAndSecondsIntervalWholeDay($parkingLotID, $day);
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
        responseOK($oc);

    default:
        quitExe(400, QE_NO_KNOWN_REQ);


}