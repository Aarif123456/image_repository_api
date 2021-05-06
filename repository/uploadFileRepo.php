<?php

declare(strict_types=1);

require_once __DIR__ . '/error.php';

/**
 * @throws Exception
 */
function insertFile($file, $user, $conn, $debug): bool {
    $stmt = $conn->prepare(
        'INSERT INTO files (memberID, filePath, fileName, fileSize, accessID) VALUES (:memberID, :filePath, :fileName, :fileSize, :accessID)'
    );
    $stmt->bindValue(':memberID', $user->ID, PDO::PARAM_INT);
    $stmt->bindValue(':filePath', $file->path, PDO::PARAM_STR);
    $stmt->bindValue(':fileName', $file->name, PDO::PARAM_STR);
    $stmt->bindValue(':fileSize', $file->size, PDO::PARAM_INT);
    $stmt->bindValue(':accessID', $file->access, PDO::PARAM_INT);

    return safeWriteQueries($stmt, $conn, $debug);
}
