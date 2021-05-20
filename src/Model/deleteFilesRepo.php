<?php

declare(strict_types=1);
namespace ImageRepository\Model\FileManagement;

use ImageRepository\Exception\{DebugPDOException, PDOWriteException};
use ImageRepository\Model\{Database, FileLocationInfo, User};

/**
 * Function to delete file
 *
 * @throws DebugPDOException
 * @throws PDOWriteException
 */
function deleteImage(FileLocationInfo $file, User $user, Database $db, bool $debug = false): bool {
    $sql = 'DELETE FROM files WHERE fileName=:fileName AND filePath=:filePath AND memberID=:id';
    $params = [
        ':fileName' => $file->name,
        ':filePath' => $file->path,
        ':id' => $user->id,
    ];
    $filePath = $file->getEncryptedFilePath();
    if (file_exists($filePath)) {
        /* If we delete from database then delete locally */
        if ($db->write($sql, $params, $debug)) return unlink($filePath);
    }

    return false;
}

