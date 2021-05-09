<?php

declare(strict_types=1);
/* Stores the return of the API, created to make localization easier
Some API return that come from SQL related error are located in repository/error.php
*/

/* Manually turn on error reporting */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('session.cookie_secure', '1');
error_reporting(E_ALL);
/* Define the strings the api will return  */

/* constants */
const COMMAND_FAILED = 'Query failed to execute, ensure you use the correct values';
const FILE_SIZE_LIMIT_EXCEEDED = 'Exceeded file size limit';
const INTERNAL_SERVER_ERROR = 'Something went wrong:(';
const INTERNAL_SERVER_ERROR_JSON = '{"error":"Something went wrong:("}';
const INVALID_FILE_FORMAT = 'Invalid file format.';
const INVALID_PASSWORD_JSON = '{"loggedIn":false}';
const MISSING_PARAMETERS = 'Missing value';
const NO_FILE_SENT = 'No file sent.';
const NO_FILE_SENT_JSON = '{"error":"No file sent."}';
const NO_ROWS_RETURNED_JSON = '{"error":"No rows were found in database"}';
/*TODO: move to another file so you can import it into authenticate.php*/
const UNAUTHORIZED_NO_LOGIN_JSON = '{"error":"user is not logged in!"}';
const USER_LOGGED_OUT_JSON = '{"message":"User has successfully logged out"}';
const USER_NOT_ADMIN = 'User is not an admin.';

/* error as functions*/
/* We HTML entities any data coming back from the user before printing */

function createQueryJSON($arr, $noRowReturn = NO_ROWS_RETURNED_JSON) {
    if (!$arr) {
        exit($noRowReturn);
    }

    return json_encode($arr);
}

function exitWithError($error, $fallbackMessage = INTERNAL_SERVER_ERROR_JSON) {
    $errorMessage = createQueryJSON(['error' => $error]);
    exit($errorMessage);

}

/* Required header */
function getHeader() {
    // header('Access-Control-Allow-Origin: https://abdullaharif.tech');
    // header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Origin: https://localhost:3000');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: X-Requested-With, X-PINGOTHER, content-type');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
    header('Content-Type: application/json'); // most endpoints application will always return JSON
}

function startSession() {
    $status = session_status();
    if (PHP_SESSION_DISABLED === $status) {
        // That's why you cannot rely on sessions!
        return;
    }

    if (PHP_SESSION_NONE === $status) {
        session_cache_limiter('private_no_expire');
        session_start();
    }
}

function requiredHeaderAndSessionStart() {
    getHeader();
    startSession();
    $_POST = json_decode(file_get_contents('php://input'), true);
}

/* utility function for post, get and session if enough function this will go to it's own file*/
function isValidPostVar($varName): bool {
    return isset($_POST[$varName]) && $_POST[$varName];
}

function isValidRequestVar($varName): bool {
    return isset($_REQUEST[$varName]) && $_REQUEST[$varName];
}

function isValidFileVar($fileName): bool {
    return isset($_FILES[$fileName]['error']);
}

