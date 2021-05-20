<?php

declare(strict_types=1);
namespace App\Utils;

use App\Model\User;
use App\Views\UnauthorizedUserException;
use PDO;
use PHPAuth\{Auth as PHPAuth, Config as PHPAuthConfig};

function getAuth(PDO $conn): PHPAuth {
    $config = new PHPAuthConfig($conn);

    return new PHPAuth($conn, $config);
}

function isUserAuthorized(PDO $conn, int $authorizationLevel): bool {
    switch ($authorizationLevel) {
        /* If endpoint is authenticated anyone can use it */
        case UNAUTHENTICATED:
            return true;
        case AUTHORIZED_USER:
            return isUserLoggedIn($conn);
        case AUTHORIZED_ADMIN:
            return isUserLoggedIn($conn) && isUserAnAdmin($conn);
        default:
            return false;
    }
}

/*Make sure user is validated */
function isUserLoggedIn(PDO $conn): bool {
    $auth = getAuth($conn);

    return $auth->isLogged();
}

function getUserID(PDO $conn) {
    $auth = getAuth($conn);

    return $auth->getCurrentUID();
}

function getUser(PDO $conn, int $userId): array {
    $auth = getAuth($conn);

    return $auth->getUser($userId);
}

function getCurrentUserInfo(PDO $conn): array {
    $auth = getAuth($conn);

    return $auth->getCurrentUser();
}

function isUserAnAdmin(PDO $conn): bool {
    return getCurrentUserInfo($conn)['isAdmin'];
}

/* Login function a bit ugly because we have multiple domains*/
function login($loginInfo, PDO $conn): array {
    $config = new PHPAuthConfig($conn);
    $auth = new PHPAuth($conn, $config);
    $loginInfo = $auth->login($loginInfo->email, $loginInfo->password, $loginInfo->remember);
    $arrCookieOptions = [
        'expires' => $loginInfo['expire'],
        'path' => $config->cookie_path,
        'domain' => $config->cookie_domain,
        'secure' => $config->cookie_secure,
        'httponly' => $config->cookie_http,
        'samesite' => $config->cookie_samesite
    ];
    if (!$loginInfo['error']) {
        setcookie($loginInfo['cookie_name'], $loginInfo['hash'], $arrCookieOptions);
    }

    return $loginInfo;
}

function registerUser(PDO $conn, User $user): array {
    $auth = getAuth($conn);
    $params = [
        'firstName' => $user->firstName,
        'lastName' => $user->lastName,
        'isAdmin' => (int)$user->isAdmin
    ];

    return $auth->register($user->email, $user->password, $user->password, $params);
}

function logout(PDO $conn): bool {
    $auth = getAuth($conn);

    return $auth->logout($auth->getCurrentSessionHash());
}

function resetPassword(string $email, PDO $conn): array {
    $auth = getAuth($conn);

    return $auth->requestReset($email, true);
}

/**
 * @throws UnauthorizedUserException
 */
function unauthorizedExit() {
    header('HTTP/1.0 401 Unauthorized');
    throw new UnauthorizedUserException();
}


