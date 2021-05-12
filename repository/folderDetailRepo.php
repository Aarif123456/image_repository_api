<?php

declare(strict_types=1);

/* Imports */
require_once __DIR__ . '/error.php';
require_once __DIR__ . '/User.php';

function getFolderDetail(string $filePath, User $user, PDO $conn): array {
    $stmt = $conn->prepare(
        'SELECT * FROM files WHERE filePath=:filePath AND memberID=:id'
    );
    $stmt->bindValue(':filePath', $filePath);
    $stmt->bindValue(':id', $user->id);

    return getExecutedResult($stmt);
}