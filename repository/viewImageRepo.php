<?php

declare(strict_types=1);

/* Imports */
require_once __DIR__ . '/error.php';
require_once __DIR__ . '/systemKey.php';
require_once __DIR__ . '/encryption/decryptFile.php';


function getFolderDetail(string $filePath, User $user, PDO $conn): array {
    $stmt = $conn->prepare(
        'SELECT * FROM files WHERE filePath=:filePath AND memberID=:id'
    );
    $stmt->bindValue(':filePath', $filePath);
    $stmt->bindValue(':id', $user->id);

    return getExecutedResult($stmt);
}

function viewImageDetail(FileLocationInfo $file, User $user, PDO $conn): array {
    $stmt = $conn->prepare(
        'SELECT * FROM files WHERE fileName=:fileName AND filePath=:filePath AND memberID=:id'
    );
    $stmt->bindValue(':fileName', $file->name);
    $stmt->bindValue(':filePath', $file->getRealPath());
    $stmt->bindValue(':id', $user->id);

    return getExecutedResult($stmt);
}

function getUserKey(User $user, PDO $conn): string {
    $stmt = $conn->prepare(
        'SELECT privateKey FROM userKeys WHERE memberID=:id'
    );
    $stmt->bindValue(':id', $user->id);
    $result = getExecutedResult($stmt);

    return $result[0]['privateKey'];
}

function getFileMimeType(FileLocationInfo $file, User $user, PDO $conn){
    return viewImageDetail($file, $user, $conn)[0]['mime'];
}

function getImage(FileLocationInfo $file, User $user, PDO $conn): array {
    $privateKey = getUserKey($user, $conn);
    $systemKeys = getSystemKeys($conn);
    $publicKey = $systemKeys['publicKey'];
    return [
        'data' => getFileDecrypted($file, $privateKey, $publicKey),
        'mime' => getFileMimeType($file, $user, $conn)
    ];

}
