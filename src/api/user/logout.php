<?php

declare(strict_types=1);
namespace ImageRepository\Api\User;

use ImageRepository\Model\Database;
use ImageRepository\Utils\Auth;

use function ImageRepository\Views\{createQueryJSON, safeApiRun};

use const ImageRepository\Utils\AUTHORIZED_USER;

function logoutApi(Database $_db, Auth $auth, bool $debug) {
    echo createQueryJSON(['error' => !$auth->logout()]);
}

safeApiRun(AUTHORIZED_USER, '/logoutApi');
