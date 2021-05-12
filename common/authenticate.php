<?php

declare(strict_types=1);

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/../views/errorHandling.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPAuth\Auth as PHPAuth;
use PHPAuth\Config as PHPAuthConfig;

function getAuth(PDO $conn): PHPAuth {
    $config = new PHPAuthConfig($conn);

    return new PHPAuth($conn, $config);
}

/*Make sure user is validated */
function validateUser(PDO $conn): bool {
    $auth = getAuth($conn);

    return $auth->isLogged();
}

function getUserID(PDO $conn) {
    $auth = getAuth($conn);

    return $auth->getCurrentUID();
}

function getUserInfo(int $userID, PDO $conn): array {
    $auth = getAuth($conn);

    return $auth->getUser($userID);
}

function getCurrentUserInfo(PDO $conn): array {
    $auth = getAuth($conn);

    return $auth->getCurrentUser();
}

function verifyUserAdmin(PDO $conn): bool {
    return getCurrentUserInfo($conn)['isAdmin'];
}

function login($loginInfo, PDO $conn): array {
    $auth = getAuth($conn);

    return $auth->login($loginInfo->email, $loginInfo->password, $loginInfo->remember);
}

function logout(PDO $conn): bool {
    $auth = getAuth($conn);

    return $auth->logout($auth->getCurrentSessionHash());
}

function resetPassword(string $email, PDO $conn): array {
    $auth = getAuth($conn);

    return $auth->requestReset($email, true);
}

/*TODO: create custom exception */
/* logout function */
function redirectToLogin() {
    header('HTTP/1.0 403 Forbidden');
    throw new Exception(UNAUTHORIZED_NO_LOGIN_JSON);
}


