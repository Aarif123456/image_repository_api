<?php

declare(strict_types=1);
namespace ImageRepository\Model\FileManagement;

use ImageRepository\Exception\{DebugPDOException, PDOWriteException};
use ImageRepository\Model\{FileLocationInfo, User};
use PDO;

use function ImageRepository\Model\safeWriteQueries;

/**
 * Function to delete file
 *
 * @throws DebugPDOException
 * @throws PDOWriteException
 */
function deleteImage(FileLocationInfo $file, User $user, PDO $conn, bool $debug = false): bool {
    $stmt = $conn->prepare(
        'DELETE FROM files WHERE fileName=:fileName AND filePath=:filePath AND memberID=:id'
    );
    $stmt->bindValue(':fileName', $file->name);
    $stmt->bindValue(':filePath', $file->path);
    $stmt->bindValue(':id', $user->id);
    $filePath = $file->getEncryptedFilePath();

    return file_exists($filePath) && safeWriteQueries($stmt, $conn, $debug) && unlink($filePath);
}

