<?php

declare(strict_types=1);
namespace ImageRepository\Api\User;

use PDO;

use function ImageRepository\Utils\logout;
use function ImageRepository\Views\{createQueryJSON, safeApiRun};

use const ImageRepository\Utils\AUTHORIZED_USER;

function logoutApi(PDO $conn, bool $debug) {
    echo createQueryJSON(['error' => !logout($conn)]);
}

safeApiRun(AUTHORIZED_USER, '/logoutApi');
