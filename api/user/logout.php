<?php

/*TODO: manual test function from front-end */
declare(strict_types=1);
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../views/errorHandling.php';
require_once __DIR__ . '/../../common/authenticate.php';
function logoutApi(PDO $conn, bool $debug) {
    echo createQueryJSON(['error' => !logout($conn)]);
}

safeApiRun(AUTHORIZED_USER, 'logoutApi');
