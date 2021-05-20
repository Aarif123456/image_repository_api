<?php

declare(strict_types=1);
namespace App\Model\FileManagement;

use App\Model\{DebugPDOException, File, InvalidAccessException, PDOWriteException, User};
use App\Model\Encryption\{EncryptedFileNotCreatedException, EncryptionFailureException};
use PDO;

use function App\Model\Encryption\{encryptFile, getPrivatePolicy, getPublicPolicy};
use function App\Model\safeWriteQueries;

use const App\Model\{PRIVATE_ACCESS, PUBLIC_ACCESS};

/**
 * @throws InvalidAccessException
 */
function getPolicy(int $fileAccess, User $user): string {
    switch ($fileAccess) {
        case PRIVATE_ACCESS:
            return getPrivatePolicy($user);
        case PUBLIC_ACCESS:
            return getPublicPolicy($user);
        default:
            throw new InvalidAccessException();
    }
}

/**
 * @throws DebugPDOException
 * @throws EncryptedFileNotCreatedException
 * @throws InvalidAccessException
 * @throws EncryptionFailureException
 * @throws PDOWriteException
 */
function insertFile(File $file, User $user, PDO $conn, bool $debug = false): bool {
    $file->access ??= PRIVATE_ACCESS;
    $stmt = $conn->prepare(
        'INSERT INTO files (memberID, filePath, fileName, fileSize, accessID, mime) VALUES (:memberID, :filePath, :fileName, :fileSize, :accessID, :mime)'
    );
    $stmt->bindValue(':memberID', $user->id, PDO::PARAM_INT);
    $stmt->bindValue(':filePath', $file->path, PDO::PARAM_STR);
    $stmt->bindValue(':fileName', $file->name, PDO::PARAM_STR);
    $stmt->bindValue(':fileSize', $file->size, PDO::PARAM_INT);
    $stmt->bindValue(':accessID', $file->access, PDO::PARAM_INT);
    $stmt->bindValue(':mime', $file->type, PDO::PARAM_STR);
    $policy = getPolicy($file->access, $user);
    encryptFile($file, $policy, $conn);

    return safeWriteQueries($stmt, $conn, $debug);
}