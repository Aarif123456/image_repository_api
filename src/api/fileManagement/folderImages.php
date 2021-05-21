<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

use ImageRepository\Model\{Database, FileManagement\FolderReader, User};
use ImageRepository\Utils\Auth;

use function ImageRepository\Views\{createQueryJSON, safeApiRun};

use const ImageRepository\Utils\{AUTHORIZED_USER};

function folderDetail(Database $db, Auth $auth, bool $debug) {
    $user = new User($auth->getCurrentUserInfo());
    $filePath = $_REQUEST['filePath'] ?? '';
    $result = FolderReader::listFiles($filePath, $user, $db);
    echo createQueryJSON($result);
}

safeApiRun(AUTHORIZED_USER, '/folderDetail');