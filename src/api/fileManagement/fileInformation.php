<?php

declare(strict_types=1);
namespace ImageRepository\api\FileManagement;

use ImageRepository\Exception\NoSuchFileException;
use ImageRepository\Model\{Database, FileLocationInfo, User};

/**
 * Helper class to get file info from request
 *
 * @throws NoSuchFileException
 */
function getFileInformation(User $user, Database $db): FileLocationInfo {
    $filePath = $_REQUEST['filePath'] ?? '';
    $fileName = $_REQUEST['fileName'] ?? '';
    $fileId = $_REQUEST['fileId'] ?? null;
    $file = null;
    if (!empty($fileId)) {
        $file = FileLocationInfo::createFromId($fileId, $user, $db);
    } elseif (!empty($fileName)) {
        $file = new FileLocationInfo([
            'name' => $fileName,
            'path' => $filePath,
            'ownerId' => $user->id
        ]);
    }

    return $file;
}

