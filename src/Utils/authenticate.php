<?php

declare(strict_types=1);
namespace ImageRepository\Utils;

use ImageRepository\Exception\UnauthorizedUserException;
use ImageRepository\Model\{Database, User};
use PHPAuth\{Auth as PHPAuth, Config as PHPAuthConfig};

function getAuth(Database $db): PHPAuth {
    $config = new PHPAuthConfig($db->conn);

    return new PHPAuth($db->conn, $config);
}

function isUserAuthorized(Database $db, int $authorizationLevel): bool {
    switch ($authorizationLevel) {
        /* If endpoint is authenticated anyone can use it */
        case UNAUTHENTICATED:
            return true;
        case AUTHORIZED_USER:
            return isUserLoggedIn($db);
        case AUTHORIZED_ADMIN:
            return isUserLoggedIn($db) && isUserAnAdmin($db);
        default:
            return false;
    }
}

/*Make sure user is validated */
function isUserLoggedIn(Database $db): bool {
    $auth = getAuth($db);

    return $auth->isLogged();
}

function getUserID(Database $db) {
    $auth = getAuth($db);

    return $auth->getCurrentUID();
}

function getUser(Database $db, int $userId): array {
    $auth = getAuth($db);

    return $auth->getUser($userId);
}

function getCurrentUserInfo(Database $db): array {
    $auth = getAuth($db);

    return $auth->getCurrentUser();
}

function isUserAnAdmin(Database $db): bool {
    return getCurrentUserInfo($db)['isAdmin'];
}

/* Login function a bit ugly because we have multiple domains*/
function login($loginInfo, Database $db): array {
    $config = new PHPAuthConfig($db->conn);
    $auth = new PHPAuth($db->conn, $config);
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

function registerUser(Database $db, User $user): array {
    $auth = getAuth($db);
    $params = [
        'firstName' => $user->firstName,
        'lastName' => $user->lastName,
        'isAdmin' => (int)$user->isAdmin
    ];

    return $auth->register($user->email, $user->password, $user->password, $params);
}

function logout(Database $db): bool {
    $auth = getAuth($db);

    return $auth->logout($auth->getCurrentSessionHash());
}

function resetPassword(string $email, Database $db): array {
    $auth = getAuth($db);

    return $auth->requestReset($email, true);
}

/**
 * @throws UnauthorizedUserException
 */
function unauthorizedExit() {
    header('HTTP/1.0 401 Unauthorized');
    throw new UnauthorizedUserException();
}


