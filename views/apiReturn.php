<?php

declare(strict_types=1);
require_once __DIR__ . '/../common/constants.php';
/* Report errors depending on if we are in production mode */
if (DEBUG) {
    ini_set('display_errors', 'stderr');
    ini_set('display_startup_errors', '1');
    ini_set('ignore_repeated_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ERROR);
}
/* constants */
function createQueryJSON(array $arr) {
    return json_encode($arr ?? []);
}

/* Required header */
function getHeader() {
    header('Access-Control-Allow-Origin: https://abdullaharif.tech');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: X-Requested-With, X-PINGOTHER, content-type');
    header('Content-Type: application/json; charset=UTF-8'); // most endpoints application will always return JSON
    // header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');
    // header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

function requiredHeaderAndSessionStart() {
    getHeader();
    if (empty($_REQUEST)) {
        $_REQUEST = json_decode(file_get_contents('php://input'), true);
        $_POST = json_decode(file_get_contents('php://input'), true);
    }
}