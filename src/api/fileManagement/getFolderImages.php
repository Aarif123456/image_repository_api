<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

/* TODO: remove "get" from file name */
use ImageRepository\Model\User;
use PDO;

use function ImageRepository\Model\FileManagement\getFolderDetail;
use function ImageRepository\Utils\getCurrentUserInfo;
use function ImageRepository\Views\{createQueryJSON, safeApiRun};

use const ImageRepository\Utils\{AUTHORIZED_USER};

function folderDetail(PDO $conn, bool $debug) {
    $user = new User(getCurrentUserInfo($conn));
    $filePath = $_REQUEST['filePath'] ?? '';
    $result = getFolderDetail($filePath, $user, $conn);
    echo createQueryJSON($result);
}

safeApiRun(AUTHORIZED_USER, '/folderDetail');