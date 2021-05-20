<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

use ImageRepository\Exception\{NoSuchFileException};
use ImageRepository\Model\{Database, FileLocationInfo, User};

use function ImageRepository\Model\FileManagement\getImageDetailWithId;

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
        $file = getImageDetailWithId($fileId, $user, $db);
    } elseif (!empty($fileName)) {
        $file = new FileLocationInfo([
            'name' => $fileName,
            'path' => $filePath,
            'ownerId' => $user->id
        ]);
    }

    return $file;
}