<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

use ImageRepository\Model\{Database, FileManagement\FolderReader, User};
use ImageRepository\Utils\Auth;
use ImageRepository\Views\{ErrorHandler, JsonFormatter};

use const ImageRepository\Utils\{AUTHORIZED_USER};

function folderDetail(Database $db, Auth $auth, bool $debug) {
    $user = new User($auth->getCurrentUserInfo());
    $filePath = $_REQUEST['filePath'] ?? '';
    $result = FolderReader::listFiles($filePath, $user, $db);
    JsonFormatter::printArray($result);
}

ErrorHandler::safeApiRun(AUTHORIZED_USER, '/folderDetail');