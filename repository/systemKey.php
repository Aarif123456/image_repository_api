<?php

declare(strict_types=1);

/* Imports */
require_once __DIR__ . '/error.php';


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
