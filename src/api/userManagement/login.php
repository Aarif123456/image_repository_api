<?php

declare(strict_types=1);
namespace ImageRepository\Api\UserManagement;

use ImageRepository\Api\EndpointValidator;
use ImageRepository\Exception\{MissingParameterException, UnauthorizedAdminException};
use ImageRepository\Model\Database;
use ImageRepository\Utils\Auth;
use ImageRepository\Views\{ErrorHandler, JsonFormatter};

use const ImageRepository\Utils\UNAUTHENTICATED;

const LOGIN_API_OUTPUT_VAR = ['error' => null, 'message' => null, 'loggedIn' => null];
/**
 * @throws UnauthorizedAdminException
 * @throws MissingParameterException
 */
function loginApi(Database $db, Auth $auth, bool $debug) {
    /* Logout any account they are logged in */
    if ($auth->isUserLoggedIn()) $auth->logout();
    /* Make sure request has all the required attributes*/
    EndpointValidator::checkMissingPostVars(['email', 'password']);
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
    $result = $auth->login($loginInfo);
    $output = array_intersect_key($result, LOGIN_API_OUTPUT_VAR);
    $output['loggedIn'] = !$result['error'];
    /* Make sure user is actually an admin*/
    if ($admin && !($auth->isUserAnAdmin())) {
        $auth->logout();
        header('HTTP/1.0 403 Forbidden');
        /* Exit and tell the client that their user type is they are not admin */
        throw new UnauthorizedAdminException();
    }
    JsonFormatter::printArray($output);
}

ErrorHandler::safeApiRun(UNAUTHENTICATED, '/loginApi');
