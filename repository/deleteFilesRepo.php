<?php

declare(strict_types=1);
/* Imports */
require_once __DIR__ . '/error.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/File.php';
require_once __DIR__ . '/viewImageRepo.php';
/**
 * Function to delete file
 *
 * @throws DebugPDOException
 * @throws PDOWriteException
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

