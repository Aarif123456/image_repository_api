<?php

declare(strict_types=1);
namespace ImageRepository\Model\FileManagement;

/* Get information about the image */
use ImageRepository\Exception\{EncryptionFailureException, NoSuchFileException};
use ImageRepository\Model\{Database, EncryptionKeyReader, FileLocationInfo, User};
use ImageRepository\Model\Encryption\FileDecrypter;

function viewImageDetail(FileLocationInfo $file, User $user, Database $db): array {
    $sql =
        'SELECT * FROM files WHERE fileName=:fileName AND filePath=:filePath AND memberID=:id';
    $params = [
        ':fileName' => $file->name,
        ':filePath' => $file->path,
        ':id' => $user->id
    ];

    return $db->read($sql, $params);
}

/**
 * Wrapper function to get file information using the file id
 *
 * @throws NoSuchFileException
 */
function getImageDetailWithId(int $fileId, User $user, Database $db): FileLocationInfo {
    $sql =
        'SELECT fileName as \'name\', filePath as \'path\', memberID as \'ownerId\' FROM files WHERE fileID=:fileId AND memberID=:id';
    $params = [
        ':fileId' => $fileId,
        ':id' => $user->id
    ];
    $rows = $db->read($sql, $params);
    if (empty($rows)) {
        throw new NoSuchFileException();
    }

    return new FileLocationInfo($rows[0]);
}

/* Helper function to get the mime type of the file */
function getFileMimeType(FileLocationInfo $file, User $user, Database $db) {
    return viewImageDetail($file, $user, $db)[0]['mime'];
}

/**
 * Get back the information needed to display the image
 *
 * @throws EncryptionFailureException
 * @throws NoSuchFileException
 */
function getImage(FileLocationInfo $file, User $user, Database $db): array {
    $privateKey = EncryptionKeyReader::userKey($user, $db);
    $publicKey = EncryptionKeyReader::publicKey($db);

    return [
        'data' => FileDecrypter::run($file, $privateKey, $publicKey),
        'mime' => getFileMimeType($file, $user, $db)
    ];
}
