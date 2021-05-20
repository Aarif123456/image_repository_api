<?php

declare(strict_types=1);
namespace App\Api\User;

use PDO;

use function App\Utils\logout;
use function App\Views\{createQueryJSON, safeApiRun};

use const App\Utils\AUTHORIZED_USER;

function logoutApi(PDO $conn, bool $debug) {
    echo createQueryJSON(['error' => !logout($conn)]);
}

safeApiRun(AUTHORIZED_USER, '/logoutApi');
