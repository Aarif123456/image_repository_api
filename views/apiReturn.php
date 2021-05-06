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
const COMMAND_FAILED = '{"error":"Query failed to execute, ensure you use the correct values"}';
const EMAIL_EXISTS = '{"emailTaken":true}';
const EMAIL_NOT_IN_TABLE = '{"emailTaken":false}';
const FILE_SIZE_LIMIT_EXCEEDED = '{"error":"Exceeded file size limit."}';
const INTERNAL_SERVER_ERROR = '{"error":"something went wrong:("}';
const INVALID_PARAMETERS = '{"error":"Parameter do no have expected type"}';
const INVALID_PASSWORD = '{"success":false}';
const MISSING_PARAMETERS = '{"error":"Missing value"}';
const NO_FILE_SENT = '{"error":"No file sent."}';
const NO_ROWS_RETURNED = '{"error":"No rows were found in database"}';
const UNAUTHORIZED_NO_LOGIN = '{"error":"user is not logged in!"}';
const USER_LOGGED_OUT = '{"message":"User has successfully logged out"}';
const INVALID_ACCESS_TYPE = '{"error":"Invalid file access policy."}';
const INVALID_FILE_FORMAT = '{"error":"Invalid file format."}';
const INVALID_SEARCH_METHOD = '{"error":"Invalid search method."}';

/* error as functions*/
/* We HTML entities any data coming back from the user before printing */
function invalidUserType($userType): string {
    $userType = htmlentities($userType);

    return "'$userType' is not a recognized userType";
}

function authenticatedSuccessfully($userType): string {
    $userType = htmlentities($userType);
    $return = (object)[
        'success' => true,
        'userType' => $userType
    ];

    return json_encode($return);
}

function passwordReset($uID): string {
    $printableUserID = htmlentities($uID);

    return "{\"message\":\"Password has been reset for user with id $printableUserID\"}";
}

function userCreated($userID): string {
    return "{\"message\":\"Created user with id: $userID\"}";
}

function verifyUserType($userType): bool {
    switch ($userType) {
        case 'user':       // INTENTIONAL FALLTHROUGH
        case 'admin':    // INTENTIONAL FALLTHROUGH
            return true;
        default:
            return false;
    }
}

function createQueryJSON($arr, $noRowReturn = NO_ROWS_RETURNED) {
    if (!$arr) {
        exit($noRowReturn);
    }

    return json_encode($arr);
}

/* Required header */
function getHeader() {
    // header('Access-Control-Allow-Origin: https://abdullaharif.tech');
    // header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Origin: https://localhost:3000');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: X-Requested-With,content-type');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
    header('Content-Type: application/json'); // entire application will always return JSON back
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

