<?php

declare(strict_types=1);
namespace App\Api\UserManagement;

/* TODO: rename file to login and update read me */
use App\Views\{MissingParameterException, UnauthorizedAdminException};
use PDO;

use function App\Api\checkMissingPostVars;
use function App\Utils\{isUserAnAdmin, isUserLoggedIn, login, logout};
use function App\Views\{createQueryJSON, safeApiRun};

use const App\Utils\UNAUTHENTICATED;

const LOGIN_API_OUTPUT_VAR = ['error' => null, 'message' => null, 'loggedIn' => null];
/**
 * @throws UnauthorizedAdminException
 * @throws MissingParameterException
 */
function loginApi(PDO $conn, bool $debug) {
    /* Logout any account they are logged in */
    if (isUserLoggedIn($conn)) logout($conn);
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
    $result = login($loginInfo, $conn);
    $output = array_intersect_key($result, LOGIN_API_OUTPUT_VAR);
    $output['loggedIn'] = !$result['error'];
    /* Make sure user is actually an admin*/
    if ($admin && !(isUserAnAdmin($conn))) {
        logout($conn);
        header('HTTP/1.0 403 Forbidden');
        /* Exit and tell the client that their user type is they are not admin */
        throw new UnauthorizedAdminException();
    }
    echo createQueryJSON($output);
}

safeApiRun(UNAUTHENTICATED, '/loginApi');
