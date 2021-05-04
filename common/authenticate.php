<?php

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPAuth\Auth as PHPAuth;
use PHPAuth\Config as PHPAuthConfig;

function getAuth($conn) {
    $config = new PHPAuthConfig($conn);

    return new PHPAuth($conn, $config);
}

/*Make sure user is validated */
function validateUser($conn): bool {
    $auth = getAuth($conn);
    if ($auth->isLogged()) {
        return (int)$auth->getCurrentUID() === (int)($_SESSION['userID'] ?? -1);
    }

    return false;
}

function getUserID($conn): int {
    $auth = getAuth($conn);

    return $auth->getCurrentUID();
}

function login($loginInfo, $conn) {
    $auth = getAuth($conn);
    $result = $auth->login($loginInfo->email, $loginInfo->password, $loginInfo->remember);

    return !$result['error'];
}

function logout($conn): bool {
    $auth = getAuth($conn);

    return $auth->logout($auth->getCurrentSessionHash());
}

function checkSessionInfo(): bool {
    return isset($_SESSION['userID']) && isset($_SESSION['userType']);
}

/* logout function */
function redirectToLogin() {
    header('HTTP/1.0 403 Forbidden');
    exit(UNAUTHORIZED_NO_LOGIN);
}

/* Utility functions to check user's type */
function isAdmin(): bool {
    return strcmp(trim($_SESSION['userType'] ?? ''), 'admin') == 0;
}


/* utility function to make sure user has the correct permission*/
function validateAdmin($conn): bool {
    return isAdmin() && validateUser($conn);
}
