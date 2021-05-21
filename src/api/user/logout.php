<?php

declare(strict_types=1);
namespace ImageRepository\Api\User;

use ImageRepository\Model\Database;
use ImageRepository\Utils\Auth;
use ImageRepository\Views\{ErrorHandler, JsonFormatter};

use const ImageRepository\Utils\AUTHORIZED_USER;

function logoutApi(Database $_db, Auth $auth, bool $debug) {
    JsonFormatter::printArray(['error' => !$auth->logout()]);
}

ErrorHandler::safeApiRun(AUTHORIZED_USER, '/logoutApi');
