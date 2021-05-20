<?php

declare(strict_types=1);
namespace App\Api\FileManagement;

/* TODO: remove "get" from file name */
use App\Model\User;
use PDO;

use function App\Model\FileManagement\getFolderDetail;
use function App\Utils\getCurrentUserInfo;
use function App\Views\{createQueryJSON, safeApiRun};

use const App\Utils\{AUTHORIZED_USER};

function folderDetail(PDO $conn, bool $debug) {
    $user = new User(getCurrentUserInfo($conn));
    $filePath = $_REQUEST['filePath'] ?? '';
    $result = getFolderDetail($filePath, $user, $conn);
    echo createQueryJSON($result);
}

safeApiRun(AUTHORIZED_USER, '/folderDetail');