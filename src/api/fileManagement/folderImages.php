<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

use ImageRepository\Model\{Database, FileManagement\FolderReader, User};

use function ImageRepository\Utils\getCurrentUserInfo;
use function ImageRepository\Views\{createQueryJSON, safeApiRun};

use const ImageRepository\Utils\{AUTHORIZED_USER};

function folderDetail(Database $db, bool $debug) {
    $user = new User(getCurrentUserInfo($db));
    $filePath = $_REQUEST['filePath'] ?? '';
    $result = FolderReader::listFiles($filePath, $user, $db);
    echo createQueryJSON($result);
}

safeApiRun(AUTHORIZED_USER, '/folderDetail');