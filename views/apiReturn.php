<?php

/* Define the strings the api will return  */
declare(strict_types=1);
/* Stores the return of the API, created to make localization easier
Some API return that come from SQL related error are located in repository/error.php
*/
/* Manually turn on error reporting */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
/*TODO: turn off in production */
error_reporting(E_ALL);
/* constants */
const USER_LOGGED_OUT_JSON = '{"message":"User has successfully logged out"}';
/* We HTML entities any data coming back from the user before printing */
function createQueryJSON($arr) {
    return json_encode($arr ?? []);
}

/* Required header */
function getHeader() {
    header('Access-Control-Allow-Origin: https://abdullaharif.tech');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: X-Requested-With, X-PINGOTHER, content-type');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
    header('Content-Type: application/json; charset=UTF-8'); // most endpoints application will always return JSON
}

function requiredHeaderAndSessionStart() {
    getHeader();
    if (empty($_REQUEST)) {
        $_REQUEST = json_decode(file_get_contents('php://input'), true);
        $_POST = json_decode(file_get_contents('php://input'), true);
    }
}