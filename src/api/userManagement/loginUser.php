<?php

declare(strict_types=1);
namespace ImageRepository\Api\UserManagement;

/* TODO: rename file to login and update read me */
use ImageRepository\Exception\{MissingParameterException, UnauthorizedAdminException};
use ImageRepository\Model\Database;

use function ImageRepository\Api\checkMissingPostVars;
use function ImageRepository\Utils\{isUserAnAdmin, isUserLoggedIn, login, logout};
use function ImageRepository\Views\{createQueryJSON, safeApiRun};

use const ImageRepository\Utils\UNAUTHENTICATED;

const LOGIN_API_OUTPUT_VAR = ['error' => null, 'message' => null, 'loggedIn' => null];
/**
 * @throws UnauthorizedAdminException
 * @throws MissingParameterException
 */
function loginApi(Database $db, bool $debug) {
    /* Logout any account they are logged in */
    if (isUserLoggedIn($db)) logout($db);
    /* Make sure request has all the required attributes*/
    checkMissingPostVars(['email', 'password']);
    /* TODO: remove loggedIn return value and just use error */
    $admin = $_POST['admin'] ?? false;
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = $_POST['remember'] ?? true;
    $loginInfo = (object)[
        'email' => $email,
        'password' => $password,
        'remember' => $remember,
        'admin' => $admin
    ];
    /* validate login info */
    $result = login($loginInfo, $db);
    $output = array_intersect_key($result, LOGIN_API_OUTPUT_VAR);
    $output['loggedIn'] = !$result['error'];
    /* Make sure user is actually an admin*/
    if ($admin && !(isUserAnAdmin($db))) {
        logout($db);
        header('HTTP/1.0 403 Forbidden');
        /* Exit and tell the client that their user type is they are not admin */
        throw new UnauthorizedAdminException();
    }
    echo createQueryJSON($output);
}

safeApiRun(UNAUTHENTICATED, '/loginApi');
