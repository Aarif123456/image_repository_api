<?php

declare(strict_types=1);
namespace ImageRepository\Api\User;

use ImageRepository\Model\Database;

use function ImageRepository\Utils\logout;
use function ImageRepository\Views\{createQueryJSON, safeApiRun};

use const ImageRepository\Utils\AUTHORIZED_USER;

function logoutApi(Database $db, bool $debug) {
    echo createQueryJSON(['error' => !logout($db)]);
}

safeApiRun(AUTHORIZED_USER, '/logoutApi');
