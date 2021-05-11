<?php

declare(strict_types=1);

require_once __DIR__ . '/error.php';
require_once __DIR__ . '/systemKey.php';
require_once __DIR__ . '/encryption/policy.php';
require_once __DIR__ . '/encryption/encryptFile.php';
require_once __DIR__ . '/databaseConstants.php';

function getPolicy(int $fileAccess, User $user): string {
    switch ($fileAccess) {
        case PRIVATE_ACCESS:
            return getPrivatePolicy($user);
        case PUBLIC_ACCESS:
            return getPublicPolicy($user);
        default:
            throw new Exception(INVALID_ACCESS_TYPE);
    }
}

/**
 * @throws Exception
 */
function insertFile($file, $user, $conn, $debug): bool {
    $file->access ??= PRIVATE_ACCESS;
    $stmt = $conn->prepare(
        'INSERT INTO files (memberID, filePath, fileName, fileSize, accessID) VALUES (:memberID, :filePath, :fileName, :fileSize, :accessID)'
    );
    $stmt->bindValue(':memberID', $user->id, PDO::PARAM_INT);
    $stmt->bindValue(':filePath', $file->path, PDO::PARAM_STR);
    $stmt->bindValue(':fileName', $file->name, PDO::PARAM_STR);
    $stmt->bindValue(':fileSize', $file->size, PDO::PARAM_INT);
    $stmt->bindValue(':accessID', $file->access, PDO::PARAM_INT);
    $policy = getPolicy($file->access, $user);
    encryptFile($file, $policy, $conn);

    return safeWriteQueries($stmt, $conn, $debug);
}
