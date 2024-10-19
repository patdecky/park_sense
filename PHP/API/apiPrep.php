<?php

//includes for later
require_once __DIR__ . '/../globals.php';
require_once __DIR__ . '/../authorization.php';
require_once __DIR__ . '/../DB/DBH_connection.php';
require_once __DIR__ . '/responses.php';

//check if authorized
//if (!Authorization::getInstance()->getIsAuthorized()) {
//    quitExe(400, QE_NOT_AUTH);
//}

if (empty($_REQUEST['req']) || !is_string($_REQUEST['req'])) {
    quitExe(400, QE_NO_KNOWN_REQ);
}

/**
 * converts external var into int if possible
 * WARNING: check if exists by "empty" method
 * this might return false when variable is 0 or ""
 * @param string $reqStr $_REQUEST input string
 * @return int|bool return ext var number, false on failure
 */
function intFilter(string $reqStr): int|bool{
    if (!empty($_REQUEST[$reqStr]) && is_numeric($_REQUEST[$reqStr])) {
        return intval($_REQUEST[$reqStr]);
    } else
        return false;
}

function floatFilter(string $reqStr): float|bool{
    if (!empty($_REQUEST[$reqStr]) && is_float($_REQUEST[$reqStr])) {
        return $_REQUEST[$reqStr];
    } else
        return false;
}

/**
 * converts external var into string if possible
 * WARNING: check if exists by "empty" method
 * this might return false when variable is 0 or ""
 * @param string $reqStr $_REQUEST input string
 * @return string|bool return ext var string, false on failure
 */
function stringFilter(string $reqStr): string|bool {
    if (!empty($_REQUEST[$reqStr]) && is_string($_REQUEST[$reqStr])) {
        return $_REQUEST[$reqStr];
    } else
        return false;
}

function fromToChecks(int &$from, int &$to): void {
    // unix timestamp from 2000
    $twoK = mktime(0, 0, 0, 1, 1, 2000);

    //boundaries 
    $from = max(min($from, time()), $twoK);
    $to = max(min($to, time()), $twoK, $from);
}

/**
 * checks if two timestamps are $daysLimit too far from each other
 * @param int $from timestamp
 * @param int $to timestamp
 * @param int $daysLimit max days between two timestamps
 * @return boolean
 */
function limitDays(int $from, int $to, int $daysLimit): bool {
    if (abs($to - $from) > 86400 * $daysLimit) {
        return false;
    }
    return true;
}

function jsonReciever(string $reqName = 'json', ?bool $associative = null): array|null {
    try {
        $rawJson = stringFilter($reqName);
        $json = json_decode($rawJson, $associative);
        return $json;
    } catch (Exception $exc) {
        error_log(__METHOD__ . "    Could not parse JSON recieved!");
        error_log($exc->getTraceAsString());
        return null;
        //quitExe(400, QE_INPUT_INVALID);
    }
    return null;
}
