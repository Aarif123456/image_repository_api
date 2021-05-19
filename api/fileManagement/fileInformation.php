<?php

declare(strict_types=1);
require_once __DIR__ . '/../../repository/File.php';
require_once __DIR__ . '/../../repository/User.php';
/* Get image detail from database */
require_once __DIR__ . '/../../repository/viewImageRepo.php';
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