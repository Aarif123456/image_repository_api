<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

/* TODO: remove "get" from file name */
use ImageRepository\Model\{Database, User};

use function ImageRepository\Model\FileManagement\getFolderDetail;
use function ImageRepository\Utils\getCurrentUserInfo;
use function ImageRepository\Views\{createQueryJSON, safeApiRun};

use const ImageRepository\Utils\{AUTHORIZED_USER};

function folderDetail(Database $db, bool $debug) {
    $user = new User(getCurrentUserInfo($db));
    $filePath = $_REQUEST['filePath'] ?? '';
    $result = getFolderDetail($filePath, $user, $db);
    echo createQueryJSON($result);
}

safeApiRun(AUTHORIZED_USER, '/folderDetail');