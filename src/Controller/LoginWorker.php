<?php

declare(strict_types=1);
namespace ImageRepository\Controller;

use ImageRepository\Exception\{MissingParameterException, UnauthorizedAdminException};
use ImageRepository\Views\JsonFormatter;

const LOGIN_API_OUTPUT_VAR = ['error' => null, 'message' => null];
/**
 *CLass that handles logic of logging in user
 */
final class LoginWorker extends AbstractWorker
{
    /**
     * @throws UnauthorizedAdminException
     * @throws MissingParameterException
     */
    public function run() {
        /* Logout any account they are logged in */
        if ($this->auth->isUserLoggedIn()) $this->auth->logout();
        /* Make sure request has all the required attributes*/
        EndpointValidator::checkMissingPostVars(['email', 'password']);
        $admin = $_POST['admin'] ?? false;
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = $_POST['remember'] ?? false;
        $loginInfo = (object)[
            'email' => $email,
            'password' => $password,
            'remember' => $remember,
            'admin' => $admin
        ];
        /* validate login info */
        $result = $this->auth->login($loginInfo);
        $output = array_intersect_key($result, LOGIN_API_OUTPUT_VAR);
        /* Make sure user is actually an admin*/
        if ($admin && !($this->auth->isUserAnAdmin())) {
            $this->auth->logout();
            header('HTTP/1.0 403 Forbidden');
            /* Exit and tell the client that their user type is they are not admin */
            throw new UnauthorizedAdminException();
        }
        JsonFormatter::printArray($output);
    }
}