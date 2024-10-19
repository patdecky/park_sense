<?php

use JetBrains\PhpStorm\NoReturn;

require_once __DIR__ . '/../DB/DBH_connection.php';

//header responses
$GLOBALS['retHeader'] = array(
    200 => 'HTTP/1.1 200 OK',
    400 => 'HTTP/1.1 400 Bad Request',
    401 => 'HTTP/1.1 401 Unauthorized ',
    403 => 'HTTP/1.1 403 Forbidden',
    500 => 'HTTP/1.1 500 Internal Server Error',
    501 => 'HTTP/1.1 501 Not Implemented',
    503 => 'HTTP/1.1 503 Service Unavailable');

const QE_NOT_AUTH = 0;
const QE_DB_ERROR = 1;
const QE_INPUT_INVALID = 2;
const QE_NO_KNOWN_REQ = 3;
const QE_NOTHING_TO_RET = 4;
const QE_INTERVAL_TOO_WIDE = 5;
const QE_FILTER_NOT_SUPPORTED = 6;
const QE_NOT_IMPLEMENTED = 7;
const QE_OK = 200;

//visible response
$GLOBALS['retArray'] = array(
    QE_NOT_AUTH => 'Not authorized',
    QE_DB_ERROR => 'DB Error',
    QE_INPUT_INVALID => 'Invalid user input',
    QE_NO_KNOWN_REQ => 'No known request',
    QE_NOTHING_TO_RET => 'Empty response',
    QE_INTERVAL_TOO_WIDE => 'Time interval provided is too wide for the server to handle',
    QE_FILTER_NOT_SUPPORTED => 'Selected filter does not work with this table',
    QE_NOT_IMPLEMENTED => 'Not implemented',
    QE_OK => 'OK'
);

#[NoReturn]
function quitExe(int $header, int $emsg): never {
    header($GLOBALS['retHeader'][$header]);
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode(array("code" => $emsg, "msg" => $GLOBALS['retArray'][$emsg])));
}

#[NoReturn]
function responseOK(mixed $toEncode): never {
    header($GLOBALS['retHeader'][200]);
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode(array("code" => QE_OK, "msg" => $GLOBALS['retArray'][200], "payload" => $toEncode)));
}

#[NoReturn]
function dbErrorEmptyOkResponse($ret): never {
    if ($ret === DBH_connection::ERROR) {
        quitExe(500, QE_DB_ERROR);
    } elseif ($ret === DBH_connection::IS_EMPTY) {
        quitExe(200, QE_NOTHING_TO_RET);
    }
    responseOK($ret);
}