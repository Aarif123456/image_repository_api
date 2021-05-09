<?php

declare(strict_types=1);

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPAuth\Auth as PHPAuth;
use PHPAuth\Config as PHPAuthConfig;

function getAuth($conn): PHPAuth {
    $config = new PHPAuthConfig($conn);

    return new PHPAuth($conn, $config);
}

/*Make sure user is validated */
function validateUser($conn): bool {
    $auth = getAuth($conn);

    return $auth->isLogged();
}

function getUserID($conn) {
    $auth = getAuth($conn);

    return $auth->getCurrentUID();
}

function getUserInfo($userID, $conn): array {
    $auth = getAuth($conn);

    return $auth->getUser($userID);
}

function verifyUserAdmin($userID, $conn): bool {
    return getUserInfo($userID, $conn)['isAdmin'];
}

function login($loginInfo, $conn): array {
    $auth = getAuth($conn);

    return $auth->login($loginInfo->email, $loginInfo->password, $loginInfo->remember); 
}

function logout($conn): bool {
    $auth = getAuth($conn);

    return $auth->logout($auth->getCurrentSessionHash());
}

/* logout function */
function redirectToLogin() {
    header('HTTP/1.0 403 Forbidden');
    exit(UNAUTHORIZED_NO_LOGIN_JSON);
}


