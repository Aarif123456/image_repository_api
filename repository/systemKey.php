<?php

declare(strict_types=1);

/* Imports */
require_once __DIR__ . '/error.php';


function getSystemKeys($conn) {
    $stmt = $conn->prepare(
        'SELECT keysName, keyData FROM systemKeys WHERE keysName=:masterKey OR keysName=:publicKey'
    );
    $stmt->bindValue(':publicKey', 'publicKey', PDO::PARAM_STR);
    $stmt->bindValue(':masterKey', 'masterKey', PDO::PARAM_STR);
    $rows = getExecutedResult($stmt);
    $result = [];
    foreach ($rows as $row) {
        $result[$row["keysName"]] = $row["keyData"];
    }
    return $result;
}
