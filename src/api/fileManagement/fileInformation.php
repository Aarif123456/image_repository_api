<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

use ImageRepository\Exception\{NoSuchFileException};
use ImageRepository\Model\{FileLocationInfo, User};
use PDO;

use function ImageRepository\Model\FileManagement\getImageDetailWithId;

/**
 * Helper class to get file info from request
 *
 * @throws NoSuchFileException
 */
function getFileInformation(User $user, PDO $conn): FileLocationInfo {
    $filePath = $_REQUEST['filePath'] ?? '';
    $fileName = $_REQUEST['fileName'] ?? '';
    $fileId = $_REQUEST['fileId'] ?? null;
    $file = null;
    if (!empty($fileId)) {
        $file = getImageDetailWithId($fileId, $user, $conn);
    } elseif (!empty($fileName)) {
        $file = new FileLocationInfo([
            'name' => $fileName,
            'path' => $filePath,
            'ownerId' => $user->id
        ]);
    }

    return $file;
}