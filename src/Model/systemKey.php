<?php

declare(strict_types=1);
namespace ImageRepository\Model;

/* Function to get key the public key (used to encrypt) and master key(used to generate decryption keys)*/
use PDO;

function getSystemKeys(PDO $conn): array {
    $stmt = $conn->prepare(
        'SELECT keysName, keyData FROM systemKeys WHERE keysName=:masterKey OR keysName=:publicKey'
    );
    $stmt->bindValue(':publicKey', 'publicKey');
    $stmt->bindValue(':masterKey', 'masterKey');
    $rows = getExecutedResult($stmt);
    $result = [];
    foreach ($rows as $row) {
        $result[$row['keysName']] = $row['keyData'];
    }

    return $result;
}

/* Helper function to get users private key*/
function getUserKey(User $user, PDO $conn): string {
    $stmt = $conn->prepare(
        'SELECT privateKey FROM userKeys WHERE memberID=:id'
    );
    $stmt->bindValue(':id', $user->id);
    $result = getExecutedResult($stmt);

    return $result[0]['privateKey'];
}