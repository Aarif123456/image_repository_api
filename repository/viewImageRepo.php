<?php

declare(strict_types=1);

/* Imports */
require_once __DIR__ . '/error.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/systemKey.php';
require_once __DIR__ . '/encryption/decryptFile.php';
require_once __DIR__ . '/encryption/encryptionExceptionConstants.php';

/* Get information about the image */
function viewImageDetail(FileLocationInfo $file, User $user, PDO $conn): array {
    $stmt = $conn->prepare(
        'SELECT * FROM files WHERE fileName=:fileName AND filePath=:filePath AND memberID=:id'
    );
    $stmt->bindValue(':fileName', $file->name);
    $stmt->bindValue(':filePath', $file->path);
    $stmt->bindValue(':id', $user->id);

    return getExecutedResult($stmt);
}

/* Wrapper function to get file information using the file id */
function getImageDetailWithId(int $fileId, User $user, PDO $conn): FileLocationInfo {
    $stmt = $conn->prepare(
        'SELECT fileName, filePath, memberID FROM files WHERE fileID=:fileId'
    );
    $stmt->bindValue(':fileId', $fileId);
    $rows = getExecutedResult($stmt);
    if (empty($rows)) {
        throw new NoSuchFileException();
    }
    $result = $rows[0];

    return new FileLocationInfo($rows[0]);
}


/* Helper function to get the mime type of the file */
function getFileMimeType(FileLocationInfo $file, User $user, PDO $conn) {
    return viewImageDetail($file, $user, $conn)[0]['mime'];
}

/* Get back the information needed to display the image */
function getImage(FileLocationInfo $file, User $user, PDO $conn): array {
    $privateKey = getUserKey($user, $conn);
    $systemKeys = getSystemKeys($conn);
    $publicKey = $systemKeys['publicKey'];

    return [
        'data' => getFileDecrypted($file, $privateKey, $publicKey),
        'mime' => getFileMimeType($file, $user, $conn)
    ];

}
