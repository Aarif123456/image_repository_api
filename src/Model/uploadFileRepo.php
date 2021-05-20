<?php

declare(strict_types=1);
namespace ImageRepository\Model\FileManagement;

use ImageRepository\Exception\{DebugPDOException,
    EncryptedFileNotCreatedException,
    EncryptionFailureException,
    InvalidAccessException,
    PDOWriteException};
use ImageRepository\Model\{Database, EncryptionKeyReader, File, User};
use ImageRepository\Model\Encryption\FileEncrypter;

/**
 * @throws DebugPDOException
 * @throws EncryptedFileNotCreatedException
 * @throws InvalidAccessException
 * @throws EncryptionFailureException
 * @throws PDOWriteException
 */
function insertFile(File $file, User $user, Database $db, bool $debug = false): bool {
    /* TODO: Move this to the worker class */
    $file->access ??= PolicySelector::PRIVATE_ACCESS;
    $sql = 'INSERT INTO files (memberID, filePath, fileName, fileSize, accessID, mime) VALUES (:memberID, :filePath, :fileName, :fileSize, :accessID, :mime)';
    $params = [
        ':memberID' => $user->id,
        ':filePath' => $file->path,
        ':fileName' => $file->name,
        ':fileSize' => $file->size,
        ':accessID' => $file->access,
        ':mime' => $file->type
    ];
    $policy = PolicySelector::getPolicy($file->access, $user);
    $publicKey = EncryptionKeyReader::publicKey($db);
    FileEncrypter::run($file, $policy, $publicKey);

    return $db->write($sql, $params, $debug);
}
